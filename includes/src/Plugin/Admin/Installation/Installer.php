<?php

declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Exceptions\InvalidNamespaceException;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\Validation\ValidatorInterface;
use JTL\Plugin\BootstrapperInterface;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Update\IMigration;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use Monolog\Logger;
use stdClass;
use Throwable;

use function Functional\map;
use function Functional\select;

/**
 * Class Installer
 * @package JTL\Plugin\Admin\Installation
 */
final class Installer
{
    private ?string $dir = null;

    private ?PluginInterface $plugin = null;

    private JTLCacheInterface $cache;

    public function __construct(
        private readonly DbInterface $db,
        private readonly Uninstaller $uninstaller,
        private readonly ValidatorInterface $legacyValidator,
        private readonly ValidatorInterface $pluginValidator,
        ?JTLCacheInterface $cache = null
    ) {
        $this->cache = $cache ?? Shop::Container()->getCache();
    }

    public function getDir(): string
    {
        if ($this->dir === null) {
            throw new \InvalidArgumentException('Dir is not set');
        }
        return $this->dir;
    }

    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    public function getPlugin(): ?PluginInterface
    {
        return $this->plugin;
    }

    public function setPlugin(PluginInterface $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function prepare(?string $dir = null): int
    {
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        $validator  = $this->pluginValidator;
        $pluginPath = \PFAD_ROOT . \PLUGIN_DIR . \basename($this->dir);
        if (!\file_exists($pluginPath . '/' . \PLUGIN_INFO_FILE)) {
            $pluginPath = \PFAD_ROOT . \PFAD_PLUGIN . \basename($this->dir);
            $validator  = $this->legacyValidator;
            if (!\file_exists($pluginPath . '/' . \PLUGIN_INFO_FILE)) {
                if ($dir !== null && $dir !== $this->dir) {
                    // special case for EXS api
                    $this->dir = $dir;

                    return $this->prepare();
                }

                return InstallCode::INFO_XML_MISSING;
            }
        }
        $validator->setDir($pluginPath);
        $parser = new XMLParser();
        $xml    = $parser->parse($pluginPath . '/' . \PLUGIN_INFO_FILE);
        $code   = $validator->pluginPlausiIntern($xml, $this->plugin !== null);
        if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $this->plugin !== null && $this->plugin->getID() > 0) {
            $code = InstallCode::OK;
        }
        if ($code === InstallCode::OK || $code === InstallCode::OK_LEGACY) {
            $code = $this->install($xml);
        }

        return $code;
    }

    /**
     * Installiert ein Plugin
     *
     * @param array $xml
     * @return int
     * @former installierePlugin()
     */
    public function install(array $xml): int
    {
        if ($this->dir === null) {
            return InstallCode::WRONG_PARAM;
        }
        $adminAccount       = Shop::Container()->getAdminAccount();
        $baseNode           = $this->getBaseNode($xml);
        $baseDir            = \basename($this->dir);
        $versionNode        = $baseNode['Install'][0]['Version'] ?? null;
        $xmlVersion         = (int)$baseNode['XMLVersion'];
        $basePath           = \PFAD_ROOT . \PFAD_PLUGIN . $baseDir . '/';
        $lastVersionKey     = null;
        $plugin             = new stdClass();
        $plugin->nStatus    = $this->plugin === null ? State::ACTIVATED : $this->plugin->getState();
        $plugin->bExtension = 0;
        if (\is_array($versionNode)) {
            $lastVersionKey = \count($versionNode) / 2 - 1;
            $version        = (int)$versionNode[$lastVersionKey . ' attr']['nr'];
            $versionedDir   = $basePath . \PFAD_PLUGIN_VERSION . $version . '/';
            $bootstrapper   = $versionedDir . \OLD_BOOTSTRAPPER;
        } else {
            $version            = $baseNode['Version'];
            $basePath           = \PFAD_ROOT . \PLUGIN_DIR . $baseDir . '/';
            $versionedDir       = $basePath;
            $versionNode        = [];
            $bootstrapper       = $versionedDir . \PLUGIN_BOOTSTRAPPER;
            $plugin->bExtension = 1;
        }
        if ($this->plugin !== null) {
            $loader = $this->plugin->isExtension() === true
                ? new PluginLoader($this->db, $this->cache)
                : new LegacyPluginLoader($this->db, $this->cache);
            if (($p = Helper::bootstrap($this->plugin->getID(), $loader)) !== null) {
                $p->preUpdate($this->plugin->getMeta()->getVersion(), $version);
            }
        }
        $plugin                       = $this->addLicenseData($baseNode, $plugin);
        $plugin->cName                = $baseNode['Name'];
        $plugin->cBeschreibung        = $baseNode['Description'];
        $plugin->cAutor               = $baseNode['Author'];
        $plugin->cURL                 = $baseNode['URL'];
        $plugin->cIcon                = $baseNode['Icon'] ?? null;
        $plugin->cVerzeichnis         = $baseDir;
        $plugin->cPluginID            = $baseNode['PluginID'];
        $plugin->exsID                = $baseNode['ExsID'] ?? '_DBNULL_';
        $plugin->cStoreID             = $baseNode['StoreID'] ?? null;
        $plugin->cFehler              = '';
        $plugin->nVersion             = $version;
        $plugin->nXMLVersion          = $xmlVersion;
        $plugin->nPrio                = 0;
        $plugin->dZuletztAktualisiert = 'NOW()';
        $plugin->dErstellt            = $lastVersionKey !== null
            ? $versionNode[$lastVersionKey]['CreateDate']
            : $baseNode['CreateDate'];
        $plugin->bBootstrap           = (int)\is_file($bootstrapper);
        $plugin                       = $this->checkLicense($versionedDir, $plugin);
        $plugin->dInstalliert         = ($this->plugin !== null && $this->plugin->getID() > 0)
            ? $this->plugin->getMeta()->getDateInstalled()->format('Y-m-d H:i:s')
            : 'NOW()';

        $continue = true;
        if ($this->plugin === null && $plugin->bBootstrap === 1 && $plugin->bExtension === 1) {
            $plugin->kPlugin = 0;
            $loader          = new PluginLoader($this->db, $this->cache);
            if (($languageID = Shop::getLanguageID()) === 0) {
                $languageID = Shop::Lang()->getDefaultLanguage()->getId();
            }
            $languageCode = Shop::Lang()->getIsoFromLangID($languageID)->cISO ?? 'ger';
            $instance     = $loader->loadFromObject($plugin, $languageCode);
            /** @var class-string<PluginInterface> $class */
            $class = \sprintf('Plugin\\%s\\%s', $plugin->cPluginID, 'Bootstrap');
            if (\class_exists($class)) {
                $bootstrapper = new $class($instance, $this->db, $this->cache);
                if ($bootstrapper instanceof BootstrapperInterface) {
                    $continue = $bootstrapper->preInstallCheck();
                }
            }
        }
        if ($continue === false) {
            return InstallCode::CANCELED;
        }
        $plugin->kPlugin = $this->db->insert('tplugin', $plugin);
        $this->flushCache($baseNode);
        if ($plugin->kPlugin <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $this->db->insert(
            'plugin_state_log',
            (object)[
                'adminloginID' => $adminAccount->getID(),
                'pluginID'     => $plugin->kPlugin,
                'pluginName'   => $plugin->cName,
                'stateOld'     => State::NONE,
                'stateNew'     => $plugin->nStatus,
                'timestamp'    => 'NOW()',
            ]
        );
        $factory = $plugin->bExtension === 0
            ? new LegacyPluginInstallerFactory($this->db, $xml, $plugin, $this->plugin)
            : new PluginInstallerFactory($this->db, $xml, $plugin, $this->plugin);
        $res     = $factory->install();
        if ($res !== InstallCode::OK) {
            $this->uninstaller->uninstall($plugin->kPlugin);

            return $res;
        }
        $res = $this->installSQL($plugin, $versionNode, $version, $versionedDir);
        $this->cache->flushTags([
            \CACHING_GROUP_CORE,
            \CACHING_GROUP_LICENSES,
            \CACHING_GROUP_LANGUAGE,
            \CACHING_GROUP_PLUGIN,
            \CACHING_GROUP_OPC,
            \CACHING_GROUP_SHIPPING
        ]);

        return $res;
    }

    /**
     * @param stdClass $plugin
     * @param array    $versionNode
     * @param mixed    $version
     * @param string   $versionedDir
     * @return int
     */
    private function installSQL(stdClass $plugin, array $versionNode, mixed $version, string $versionedDir): int
    {
        $loader      = $plugin->bExtension === 1
            ? new PluginLoader($this->db, $this->cache)
            : new LegacyPluginLoader($this->db, $this->cache);
        $hasSQLError = false;
        $code        = InstallCode::OK;
        foreach ($versionNode as $i => $versionData) {
            if (
                $version > 0
                && $this->plugin !== null
                && isset($versionData['nr'])
                && $this->plugin->getMeta()->getVersion() >= (int)$versionData['nr']
            ) {
                continue;
            }
            $i = (string)$i;
            \preg_match('/\d+\sattr/', $i, $hits1);

            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($i)) {
                continue;
            }
            $xy      = \trim(\str_replace('attr', '', $i));
            $sqlFile = $versionNode[$xy]['SQL'] ?? '';
            if ($sqlFile === '') {
                continue;
            }
            $code = $this->validateSQL($sqlFile, (int)$versionData['nr'], $plugin);
            if ($code !== InstallCode::OK) {
                $hasSQLError = true;
                break;
            }
        }
        if ($plugin->bExtension === 1) {
            try {
                $this->updateByMigration($plugin, $versionedDir, Version::parse($version));
            } catch (InvalidNamespaceException $e) {
                Shop::Container()->getLogService()->error($e->getMessage());
                $code        = InstallCode::INVALID_MIGRATION;
                $hasSQLError = true;
            } catch (Exception $e) {
                $hasSQLError = true;
                $code        = InstallCode::SQL_ERROR;
                Shop::Container()->getLogService()->error($e->getMessage());
            }
        }
        // Ist ein SQL Fehler aufgetreten? Wenn ja, deinstalliere wieder alles
        if ($hasSQLError) {
            try {
                $this->uninstaller->uninstall($plugin->kPlugin);
            } catch (Exception $e) {
                Shop::Container()->getLogService()->error($e->getMessage());
            }
        }
        if (
            $code === InstallCode::OK
            && $this->plugin === null
            && ($p = Helper::bootstrap($plugin->kPlugin, $loader)) !== null
        ) {
            $p->installed();
        }
        if ($this->plugin !== null && ($code === InstallCode::OK_LEGACY || $code === InstallCode::OK)) {
            $code = $this->syncPluginUpdate($plugin->kPlugin);
            if (($p = Helper::bootstrap($this->plugin->getID(), $loader)) !== null) {
                $p->updated($this->plugin->getMeta()->getVersion(), $version);
            }
        }

        return $code;
    }

    /**
     * @param array    $baseNode
     * @param stdClass $plugin
     * @return stdClass
     */
    private function addLicenseData(array $baseNode, stdClass $plugin): stdClass
    {
        $plugin->cLizenz           = '';
        $plugin->cLizenzKlasse     = '';
        $plugin->cLizenzKlasseName = '';
        if (
            isset($baseNode['LicenceClass'], $baseNode['LicenceClassFile'])
            && \mb_strlen($baseNode['LicenceClass']) > 0
            && \mb_strlen($baseNode['LicenceClassFile']) > 0
        ) {
            $plugin->cLizenzKlasse     = $baseNode['LicenceClass'];
            $plugin->cLizenzKlasseName = $baseNode['LicenceClassFile'];
            $plugin->nStatus           = State::LICENSE_KEY_MISSING;
        }

        return $plugin;
    }

    /**
     * @param array $baseNode
     */
    private function flushCache(array $baseNode): void
    {
        $tags        = empty($baseNode['Install'][0]['FlushTags'])
            ? []
            : \explode(',', $baseNode['Install'][0]['FlushTags']);
        $tagsToFlush = map(
            select($tags, static function ($e): bool {
                return \defined(\trim($e));
            }),
            static function ($e) {
                return \constant(\trim($e));
            }
        );
        if (\count($tagsToFlush) > 0) {
            $this->cache->flushTags($tagsToFlush);
        }
    }

    private function checkLicense(string $versionedDir, stdClass $plugin): stdClass
    {
        $licenceClassFile = $versionedDir . \PFAD_PLUGIN_LICENCE . $plugin->cLizenzKlasseName;
        if (
            $this->plugin !== null
            && \is_file($licenceClassFile)
            && $this->plugin->getState() > 0
            && $this->plugin->getLicense()->hasLicense()
        ) {
            require_once $licenceClassFile;
            $licenceClass  = $this->plugin->getLicense()->getClass();
            $pluginLicence = new $licenceClass();
            $licenceMethod = \PLUGIN_LICENCE_METHODE;
            if ($pluginLicence->$licenceMethod($this->plugin->getLicense()->getKey())) {
                $plugin->cLizenz = $this->plugin->getLicense()->getKey();
                $plugin->nStatus = $this->plugin->getState();
            }
        }

        return $plugin;
    }

    /**
     * @param array $xml
     * @return array
     */
    private function getBaseNode(array $xml): array
    {
        return $xml['jtlshopplugin'][0] ?? $xml['jtlshop3plugin'][0];
    }

    /**
     * Geht die angegebene SQL durch und formatiert diese. Immer 1 SQL pro Zeile.
     * @return string[]
     */
    private function parseSQLFile(string $sqlFile, string $pluginName, int $pluginVersion): array
    {
        $file = \PFAD_ROOT . \PFAD_PLUGIN . $pluginName
            . '/' . \PFAD_PLUGIN_VERSION . $pluginVersion
            . '/' . \PFAD_PLUGIN_SQL . $sqlFile;

        if (!\file_exists($file)) {
            return [];// SQL Datei existiert nicht
        }
        $handle   = \fopen($file, 'rb');
        $sqlLines = [];
        $line     = '';
        if ($handle === false) {
            return $sqlLines;
        }
        while (($data = \fgets($handle)) !== false) {
            $data = \trim($data);
            if ($data !== '' && !\str_starts_with($data, '--')) {
                $line .= \trim($data);
                if (\mb_substr($data, \mb_strlen($data) - 1, 1) === ';') {
                    $sqlLines[] = $line;
                    $line       = '';
                }
            }
        }
        \fclose($handle);

        return $sqlLines;
    }

    /**
     * @return IMigration[]|Version
     * @throws Exception
     */
    private function updateByMigration(stdClass $plugin, string $pluginPath, Version $targetVersion): array|Version
    {
        $path              = $pluginPath . \PFAD_PLUGIN_MIGRATIONS;
        $manager           = new MigrationManager($this->db, $path, $plugin->cPluginID, $targetVersion);
        $pendingMigrations = $manager->getPendingMigrations();
        if (\count($pendingMigrations) === 0) {
            return $targetVersion;
        }

        return $manager->migrate();
    }

    private function validateSQL(string $sqlFile, int $version, stdClass $plugin): int
    {
        if (
            empty($sqlFile)
            || $version < 100
            || (int)$plugin->kPlugin <= 0
            || empty($plugin->cPluginID)
        ) {
            return InstallCode::SQL_MISSING_DATA;
        }
        $lines = $this->parseSQLFile($sqlFile, $plugin->cVerzeichnis, $version);
        if (\count($lines) === 0) {
            return InstallCode::SQL_INVALID_FILE_CONTENT;
        }
        foreach ($lines as $sql) {
            $sql = Text::removeNumerousWhitespaces($sql);
            if (\mb_stripos($sql, 'create table') !== false) {
                $table = $this->getTableName($sql);
                if ($table === false) {
                    return InstallCode::SQL_ERROR;
                }
                $exists = $this->db->select('tplugincustomtabelle', 'cTabelle', $table);
                if ($exists === null) {
                    $customTable           = new stdClass();
                    $customTable->kPlugin  = $plugin->kPlugin;
                    $customTable->cTabelle = $table;
                    $this->db->insert('tplugincustomtabelle', $customTable);
                }
            }
            $this->db->query($sql);
            if ($this->db->getErrorCode()) {
                $logger = Shop::Container()->getLogService();
                if ($logger instanceof Logger) {
                    $logger = $logger->withName('kPlugin');
                }
                $logger->error(
                    'SQL Fehler beim Installieren des Plugins (' . $plugin->cName . '): ' .
                    \str_replace("'", '', $this->db->getErrorMessage()),
                    [$plugin->kPlugin]
                );

                return InstallCode::SQL_ERROR;
            }
        }

        return InstallCode::OK;
    }

    private function getTableName(string $sql): false|string
    {
        \preg_match('/create table( if not exists)' . "? ([`']?)([a-z\d_]+)\\2/i", $sql, $matches);

        return \end($matches);
    }

    /**
     * Wenn ein Update erfolgreich mit neuer kPlugin in der Datenbank ist
     * wird der alte kPlugin auf die neue Version übertragen und
     * die alte Plugin-Version deinstalliert.
     *
     * 1 = Alles O.K.
     * 2 = Übergabeparameter nicht korrekt
     * 3 = Update konnte nicht installiert werden
     */
    public function syncPluginUpdate(int $pluginID): int
    {
        if ($this->plugin === null) {
            return InstallCode::WRONG_PARAM;
        }
        $newPluginID = $this->plugin->getID();
        $cronJobs    = $this->db->getObjects(
            'SELECT * 
                FROM tcron
                LEFT JOIN texportformat
                    ON texportformat.kExportformat = tcron.foreignKeyID
                WHERE tcron.foreignKey = \'kExportformat\'
                    AND texportformat.kPlugin = :pid',
            ['pid' => $newPluginID]
        );
        $res         = $this->uninstaller->uninstall($newPluginID, true, $pluginID);
        if ($res !== InstallCode::OK) {
            $this->uninstaller->uninstall($pluginID);

            return InstallCode::SQL_ERROR;
        }
        $upd = (object)['kPlugin' => $newPluginID];
        $this->db->update('tplugin', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginhook', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginadminmenu', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginsprachvariable', 'kPlugin', $pluginID, $upd);
        $this->db->update('tadminwidgets', 'kPlugin', $pluginID, $upd);
        $this->db->update('tplugin_resources', 'kPlugin', $pluginID, $upd);
        $this->db->update('tplugincustomtabelle', 'kPlugin', $pluginID, $upd);
        $this->db->update('tplugintemplate', 'kPlugin', $pluginID, $upd);
        $this->db->update('tpluginlinkdatei', 'kPlugin', $pluginID, $upd);
        $this->db->update('texportformat', 'kPlugin', $pluginID, $upd);
        $this->db->update('topcportlet', 'kPlugin', $pluginID, $upd);
        $this->db->update('portlet_input_type', 'plugin_id', $pluginID, (object)['plugin_id' => $newPluginID]);
        $this->db->update('topcblueprint', 'kPlugin', $pluginID, $upd);
        $this->db->update('tconsent', 'pluginID', $pluginID, (object)['pluginID' => $newPluginID]);
        $this->updateLangVars($newPluginID, $pluginID);
        $this->updateConfig($newPluginID, $pluginID);

        $this->updateMailTemplates($newPluginID, $pluginID);
        $this->cleanUpMailTemplates();
        $this->db->update('tlink', 'kPlugin', $pluginID, (object)['kPlugin' => $newPluginID]);
        // Ausnahme: Gibt es noch eine Boxenvorlage in der Pluginversion?
        // Falls nein -> lösche tboxen mit dem entsprechenden kPlugin
        $this->updateBoxes($newPluginID, $pluginID);

        $this->db->update('tcheckboxfunktion', 'kPlugin', $pluginID, $upd);
        $this->db->update('tspezialseite', 'kPlugin', $pluginID, $upd);
        $this->updatePaymentMethods($newPluginID, $pluginID);
        $this->updateCronJobs($cronJobs, $newPluginID);
        $this->deleteOldFiles();

        return InstallCode::OK;
    }

    private function deleteOldFiles(): void
    {
        if ($this->plugin === null) {
            return;
        }
        $fs             = Shop::Container()->get(Filesystem::class);
        $prefix         = \PLUGIN_DIR . \basename($this->dir ?? '') . '/';
        $srcFileCurrent = $prefix . PluginInterface::FILE_INVENTORY_CURRENT;
        $srcFileOld     = $prefix . PluginInterface::FILE_INVENTORY_OLD;
        $srcFileCustom  = $prefix . PluginInterface::FILE_INVENTORY_OLD_CUSTOM;
        $resultFile     = $prefix . '.deleted_files_' . $this->plugin->getCurrentVersion()->getOriginalVersion()
            . '.txt';
        if (!$fs->fileExists($srcFileCurrent) || !$fs->fileExists($srcFileOld)) {
            return;
        }
        $deletedFiles = [];
        $currentFiles = \array_filter(\explode(\PHP_EOL, $fs->read($srcFileCurrent)));
        $oldFiles     = \array_filter(\explode(\PHP_EOL, $fs->read($srcFileOld)));
        if ($fs->fileExists($srcFileCustom)) {
            $oldFiles = \array_unique(
                \array_merge(
                    $oldFiles,
                    \array_filter(\explode(\PHP_EOL, $fs->read($srcFileCustom)))
                )
            );
        }
        $filesToDelete = \array_diff($oldFiles, $currentFiles);
        foreach ($filesToDelete as $file) {
            $src = $prefix . $file;
            try {
                $src = $fs->normalizeToBasePath($src, $prefix);
            } catch (Exception $e) {
                Shop::Container()->getLogService()->warning($e->getMessage());
                continue;
            }
            if (!$fs->fileExists($src) && !$fs->directoryExists($src)) {
                continue;
            }
            $deleted = true;
            if ($fs->directoryExists($src)) {
                try {
                    $fs->deleteDirectory($src);
                } catch (Throwable) {
                    $deleted = false;
                }
            } else {
                try {
                    $fs->delete($src);
                } catch (Throwable) {
                    $deleted = false;
                }
            }
            if ($deleted === true) {
                $deletedFiles[] = \mb_substr($src, \mb_strlen($prefix));
            }
        }
        $fs->write($resultFile, \implode(\PHP_EOL, $deletedFiles));
        $fs->delete($srcFileOld);
    }

    /**
     * @param stdClass[] $cronJobs
     */
    private function updateCronJobs(array $cronJobs, int $pluginID): void
    {
        foreach ($cronJobs as $cronJob) {
            $match = $this->db->select('texportformat', ['kPlugin', 'cName'], [$pluginID, $cronJob->cName]);
            if ($match !== null && isset($match->kExportformat)) {
                $update = (object)['foreignKeyID' => (int)$match->kExportformat];
                $this->db->update('tcron', 'cronID', $cronJob->cronID, $update);
            }
        }
        $this->db->query(
            'DELETE tcron 
                FROM tcron
                    LEFT JOIN texportformat
                    ON texportformat.kExportformat = tcron.foreignKeyID
                WHERE tcron.jobType = \'exportformat\'
                    AND tcron.foreignKey = \'kExportformat\'
                    AND texportformat.kExportformat IS NULL'
        );
    }

    private function updateBoxes(int $oldPluginID, int $pluginID): void
    {
        $newBoxTemplates = $this->db->getObjects(
            "SELECT *
                FROM tboxvorlage
                WHERE kCustomID = :pid
                AND (eTyp = 'plugin' OR eTyp = 'extension')",
            ['pid' => $oldPluginID]
        );
        $oldBoxTemplates = $this->db->getObjects(
            "SELECT *
                FROM tboxvorlage
                WHERE kCustomID = :pid
                AND (eTyp = 'plugin' OR eTyp = 'extension')",
            ['pid' => $pluginID]
        );
        foreach ($newBoxTemplates as $template) {
            foreach ($oldBoxTemplates as $newBoxTemplate) {
                if ($template->cTemplate === $newBoxTemplate->cTemplate) {
                    $this->db->queryPrepared(
                        'UPDATE tboxen
                            SET kBoxvorlage = :bid, kCustomID = :pid
                            WHERE kBoxvorlage = :oid',
                        [
                            'bid' => $newBoxTemplate->kBoxvorlage,
                            'pid' => $oldPluginID,
                            'oid' => $template->kBoxvorlage
                        ]
                    );
                    break;
                }
            }
        }
        $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$oldPluginID, 'plugin']);
        $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$oldPluginID, 'extension']);
        $this->db->update(
            'tboxvorlage',
            ['kCustomID', 'eTyp'],
            [$pluginID, 'plugin'],
            (object)['kCustomID' => $oldPluginID]
        );
        $this->db->update(
            'tboxvorlage',
            ['kCustomID', 'eTyp'],
            [$pluginID, 'extension'],
            (object)['kCustomID' => $oldPluginID]
        );
        $this->db->queryPrepared(
            'DELETE FROM tboxen
                WHERE kCustomID = :pid 
                AND kBoxvorlage NOT IN (SELECT kBoxvorlage FROM tboxvorlage)',
            ['pid' => $oldPluginID]
        );
    }

    private function updateLangVars(int $oldPluginID, int $pluginID): void
    {
        $this->db->update(
            'tpluginsprachvariablecustomsprache',
            'kPlugin',
            $pluginID,
            (object)['kPlugin' => $oldPluginID]
        );
        $customLangVars = $this->db->getObjects(
            'SELECT DISTINCT tpluginsprachvariable.kPluginSprachvariable AS newID,
                tpluginsprachvariablecustomsprache.kPluginSprachvariable AS oldID, tpluginsprachvariable.cName
                FROM tpluginsprachvariablecustomsprache
                JOIN tpluginsprachvariable
                    ON tpluginsprachvariable.cName =  tpluginsprachvariablecustomsprache.cSprachvariable
                WHERE tpluginsprachvariablecustomsprache.kPlugin = :pid',
            ['pid' => $oldPluginID]
        );
        foreach ($customLangVars as $langVar) {
            $this->db->update(
                'tpluginsprachvariablecustomsprache',
                ['kPlugin', 'kPluginSprachvariable'],
                [$oldPluginID, $langVar->oldID],
                (object)['kPluginSprachvariable' => $langVar->newID]
            );
        }
    }

    private function updateConfig(int $oldPluginID, int $pluginID): void
    {
        $pluginConf = $this->db->getObjects(
            'SELECT *
                FROM tplugineinstellungen
                WHERE kPlugin IN (:opid, :pid)
                ORDER BY kPlugin',
            ['opid' => $oldPluginID, 'pid' => $pluginID]
        );
        if (\count($pluginConf) > 0) {
            $confData = [];
            foreach ($pluginConf as $conf) {
                /** @var string $name */
                $name = \str_replace(
                    ['kPlugin_' . $oldPluginID . '_', 'kPlugin_' . $pluginID . '_'],
                    '',
                    $conf->cName
                );
                if (isset($confData[$name])) {
                    continue;
                }
                $item          = new stdClass();
                $item->kPlugin = $oldPluginID;
                $item->cName   = \str_replace(
                    'kPlugin_' . $pluginID . '_',
                    'kPlugin_' . $oldPluginID . '_',
                    $conf->cName
                );
                $item->cWert   = $conf->cWert;

                $confData[$name] = $item;
            }
            $this->db->queryPrepared(
                'DELETE FROM tplugineinstellungen
                    WHERE kPlugin IN (:oid, :pid)',
                ['pid' => $pluginID, 'oid' => $oldPluginID]
            );

            foreach ($confData as $value) {
                $this->db->insert('tplugineinstellungen', $value);
            }
        }
        $this->db->queryPrepared(
            'UPDATE tplugineinstellungen
                SET kPlugin = :oid,
                    cName = REPLACE(cName, :ser, :rep)
                WHERE kPlugin = :pid',
            [
                'pid' => $pluginID,
                'oid' => $oldPluginID,
                'ser' => 'kPlugin_' . $pluginID . '_',
                'rep' => 'kPlugin_' . $oldPluginID . '_'
            ]
        );
        $this->db->queryPrepared(
            'UPDATE tplugineinstellungenconf
                SET kPlugin = :oid,
                    cWertName = REPLACE(cWertName, :ser, :rep)
                WHERE kPlugin = :pid',
            [
                'pid' => $pluginID,
                'oid' => $oldPluginID,
                'ser' => 'kPlugin_' . $pluginID . '_',
                'rep' => 'kPlugin_' . $oldPluginID . '_'
            ]
        );
    }

    private function updateMailTemplates(int $oldPluginID, int $pluginID): void
    {
        if ($this->plugin === null) {
            return;
        }
        $this->db->update('temailvorlage', 'kPlugin', $pluginID, (object)['kPlugin' => $oldPluginID]);
        $oldMailTpl = $this->db->select('temailvorlage', 'kPlugin', $oldPluginID);
        $newMailTpl = $this->db->select('temailvorlage', 'kPlugin', $pluginID);
        if (
            $newMailTpl !== null
            && $oldMailTpl !== null
            && isset($newMailTpl->kEmailvorlage, $oldMailTpl->kEmailvorlage)
        ) {
            $this->db->update(
                'tpluginemailvorlageeinstellungen',
                'kEmailvorlage',
                (int)$oldMailTpl->kEmailvorlage,
                (object)['kEmailvorlage' => (int)$newMailTpl->kEmailvorlage]
            );
        }
        foreach ($this->plugin->getMailTemplates()->getTemplatesAssoc() as $moduleID => $oldTpl) {
            $newTpl = $this->db->select(
                'temailvorlage',
                'kPlugin',
                $oldPluginID,
                'cModulId',
                $moduleID,
                null,
                null,
                false,
                'kEmailvorlage'
            );
            if ($newTpl !== null && $newTpl->kEmailvorlage > 0) {
                $newTplID = (int)$newTpl->kEmailvorlage;
                $oldTplID = (int)$oldTpl->kEmailvorlage;
                $this->db->delete('temailvorlagesprache', 'kEmailvorlage', $newTplID);
                $this->db->update(
                    'temailvorlagesprache',
                    'kEmailvorlage',
                    $oldTplID,
                    (object)['kEmailvorlage' => $newTplID]
                );
                $this->db->update(
                    'tpluginemailvorlageeinstellungen',
                    'kEmailvorlage',
                    $oldTplID,
                    (object)['kEmailvorlage' => $newTplID]
                );
            }
        }
    }

    private function cleanUpMailTemplates(): void
    {
        $this->db->query(
            'DELETE FROM tpluginemailvorlageeinstellungen
                WHERE NOT EXISTS (
                    SELECT 1 FROM temailvorlage
                    WHERE temailvorlage.kEmailvorlage = tpluginemailvorlageeinstellungen.kEmailvorlage
                )'
        );
    }

    private function updatePaymentMethods(int $oldPluginID, int $pluginID): void
    {
        $this->db->queryPrepared(
            'UPDATE tpluginzahlungsartklasse
                SET kPlugin = :oid,
                    cModulId = REPLACE(cModulId, :sea, :rep)
                WHERE kPlugin = :pid',
            [
                'oid' => $oldPluginID,
                'pid' => $pluginID,
                'sea' => 'kPlugin_' . $pluginID . '_',
                'rep' => 'kPlugin_' . $oldPluginID . '_'
            ]
        );
        $oldPaymentMethods = $this->db->getObjects(
            'SELECT kZahlungsart, cModulId, cBild, nSort, nMailSenden, nWaehrendBestellung
                FROM tzahlungsart
                WHERE cModulId LIKE :newID',
            ['newID' => 'kPlugin\_' . $oldPluginID . '\_%']
        );
        $newPaymentMethods = $this->db->getObjects(
            'SELECT kZahlungsart, cModulId, cName
                FROM tzahlungsart
                WHERE cModulId LIKE :newID',
            ['newID' => 'kPlugin\_' . $pluginID . '\_%']
        );
        $updatedMethods    = [];
        foreach ($oldPaymentMethods as $method) {
            $oldModuleID      = \str_replace(
                'kPlugin_' . $oldPluginID . '_',
                'kPlugin\_' . $pluginID . '\_',
                $method->cModulId
            );
            $newPaymentMethod = $this->db->getSingleObject(
                'SELECT kZahlungsart
                    FROM tzahlungsart
                    WHERE cModulId LIKE :oldID',
                ['oldID' => $oldModuleID]
            );
            $setSQL           = '';
            if ($newPaymentMethod !== null && isset($method->kZahlungsart, $newPaymentMethod->kZahlungsart)) {
                $this->db->queryPrepared(
                    'INSERT INTO tzahlungsartsprache
                        SELECT :newID, cISOSprache, cName, cGebuehrname, cHinweisText, cHinweisTextShop
                        FROM tzahlungsartsprache AS told
                        WHERE kZahlungsart = :oldID
                        ON DUPLICATE KEY UPDATE
                            cName            = told.cName,
                            cGebuehrname     = told.cGebuehrname,
                            cHinweisText     = told.cHinweisText,
                            cHinweisTextShop = told.cHinweisTextShop',
                    ['newID' => $newPaymentMethod->kZahlungsart, 'oldID' => $method->kZahlungsart]
                );
                $this->db->queryPrepared(
                    'DELETE tzahlungsart, tzahlungsartsprache
                        FROM tzahlungsart
                        JOIN tzahlungsartsprache
                            ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                        WHERE tzahlungsart.kZahlungsart = :pmid',
                    ['pmid' => $method->kZahlungsart]
                );
                $upd = (object)[
                    'cBild'               => $method->cBild,
                    'nSort'               => (int)$method->nSort,
                    'nMailSenden'         => (int)$method->nMailSenden,
                    'nWaehrendBestellung' => (int)$method->nWaehrendBestellung,
                ];
                $this->db->update('tzahlungsart', 'kZahlungsart', $newPaymentMethod->kZahlungsart, $upd);
                $upd = (object)['kZahlungsart' => (int)$method->kZahlungsart];
                $this->db->update('tzahlungsartsprache', 'kZahlungsart', $newPaymentMethod->kZahlungsart, $upd);
                $setSQL = ' , kZahlungsart = ' . (int)$method->kZahlungsart;
            }
            $this->db->queryPrepared(
                'UPDATE tzahlungsart
                    SET cModulId = :newID ' . $setSQL . '
                    WHERE cModulId LIKE :oldID',
                ['oldID' => $oldModuleID, 'newID' => $method->cModulId]
            );
        }
        foreach ($newPaymentMethods as $method) {
            $newModuleID      = Helper::getModuleIDByPluginID($oldPluginID, $method->cName);
            $updatedMethods[] = $newModuleID;
            $this->db->queryPrepared(
                'UPDATE tzahlungsart
                    SET cModulId = :newID
                    WHERE kZahlungsart = :pmid',
                ['pmid' => $method->kZahlungsart, 'newID' => $newModuleID]
            );
        }
        foreach ($oldPaymentMethods as $method) {
            if (\in_array($method->cModulId, $updatedMethods, true)) {
                continue;
            }
            $this->db->delete('tzahlungsart', 'kZahlungsart', $method->kZahlungsart);
            $this->db->queryPrepared(
                'DELETE FROM tplugineinstellungen
                    WHERE kPlugin = :pid AND cName LIKE :nm',
                ['pid' => $oldPluginID, 'nm' => \str_replace('_', '\_', $method->cModulId) . '\_%']
            );
        }
        $this->db->query(
            'DELETE FROM tzahlungsartsprache
                WHERE kZahlungsart NOT IN (
                    SELECT kZahlungsart FROM tzahlungsart
                )'
        );
        $this->db->query(
            'DELETE FROM tversandartzahlungsart
                WHERE kZahlungsart NOT IN (
                    SELECT kZahlungsart FROM tzahlungsart
                )'
        );
    }
}
