<?php

declare(strict_types=1);

namespace JTL\Plugin;

use Exception;
use InvalidArgumentException;
use JTL\Backend\AdminIO;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\IO\IOError;
use JTL\Plugin\Data\Config;
use JTL\Shop;
use Monolog\Logger;
use stdClass;

/**
 * Class Helper
 * @package JTL\Plugin
 */
class Helper
{
    /**
     * @var array<int, array<int, stdClass>>|null
     */
    private static ?array $hookList = null;

    /**
     * @var array<string, string>|null
     */
    private static ?array $templatePaths = null;

    /**
     * @var BootstrapperInterface[]
     */
    private static array $bootstrapper = [];

    /**
     * Holt ein Array mit allen Hooks die von Plugins benutzt werden.
     * Zu jedem Hook in dem Array, gibt es ein weiteres Array mit Plugins die an diesem Hook geladen werden.
     *
     * @return array<int, array<int, stdClass>>
     */
    public static function getHookList(): array
    {
        if (self::$hookList !== null) {
            return self::$hookList;
        }
        $cacheID = 'hook_list';
        /** @var array<int, array<int, stdClass>>|false $hooks
         */
        $hooks = Shop::Container()->getCache()->get($cacheID);
        if ($hooks !== false) {
            self::$hookList = $hooks;

            return $hooks;
        }
        $hook     = null;
        $hooks    = [];
        $hookData = Shop::Container()->getDB()->getObjects(
            'SELECT tpluginhook.nHook, tplugin.kPlugin, tplugin.nVersion, tpluginhook.cDateiname, tpluginhook.nPriority
                FROM tplugin
                JOIN tpluginhook
                    ON tpluginhook.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY tpluginhook.nPriority, tplugin.kPlugin',
            ['state' => State::ACTIVATED]
        );
        foreach ($hookData as $hook) {
            $hook->kPlugin   = (int)$hook->kPlugin;
            $hook->nVersion  = (int)$hook->nVersion;
            $hook->nPriority = (int)$hook->nPriority;
            $hook->nHook     = (int)$hook->nHook;

            $hooks[$hook->nHook][$hook->kPlugin] = $hook;
        }
        // Schauen, ob die Hookliste einen Hook als Frontende Link hat.
        // Falls ja, darf die Liste den Seiten Link Plugin Handler nur einmal ausführen bzw. nur einmal beinhalten
        if (isset($hooks[\HOOK_SEITE_PAGE_IF_LINKART])) {
            $exists = false;
            foreach ($hooks[\HOOK_SEITE_PAGE_IF_LINKART] as $i => $hookList) {
                if ($hookList->cDateiname === \PLUGIN_SEITENHANDLER) {
                    unset($hooks[\HOOK_SEITE_PAGE_IF_LINKART][$i]);
                    $exists = true;
                }
            }
            // Es war min. einmal der Seiten Link Plugin Handler enthalten um einen Frontend Link anzusteuern
            if ($hook !== null && $exists === true) {
                $plugin                                = new stdClass();
                $plugin->kPlugin                       = $hook->kPlugin;
                $plugin->nVersion                      = $hook->nVersion;
                $plugin->nPriority                     = $hook->nPriority;
                $plugin->cDateiname                    = \PLUGIN_SEITENHANDLER;
                $hooks[\HOOK_SEITE_PAGE_IF_LINKART][0] = $plugin;
            }
        }
        Shop::Container()->getCache()->set($cacheID, $hooks, [\CACHING_GROUP_PLUGIN]);
        self::$hookList = $hooks;

        return $hooks;
    }

    /**
     * @param array<int, array<int, stdClass>> $hookList
     * @return bool
     */
    public static function setHookList(array $hookList): bool
    {
        self::$hookList = $hookList;

        return true;
    }

    public static function getPluginById(string $pluginID): ?PluginInterface
    {
        if (\SAFE_MODE === true) {
            return null;
        }
        $db      = Shop::Container()->getDB();
        $cache   = Shop::Container()->getCache();
        $langID  = Shop::getLanguageID();
        $cacheID = 'plugin_id_list';
        /** @var stdClass[]|false $plugins */
        $plugins = $cache->get($cacheID);
        if ($plugins === false) {
            $plugins = $db->getObjects('SELECT kPlugin, cPluginID, bExtension FROM tplugin');
            $cache->set($cacheID, $plugins, [\CACHING_GROUP_PLUGIN]);
        }
        foreach ($plugins as $plugin) {
            if ($plugin->cPluginID !== $pluginID) {
                continue;
            }
            $loader = (int)$plugin->bExtension === 1
                ? new PluginLoader($db, $cache)
                : new LegacyPluginLoader($db, $cache);

            try {
                return $loader->init((int)$plugin->kPlugin, false, $langID);
            } catch (InvalidArgumentException) {
                return null;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    public static function getTemplatePaths(): array
    {
        if (self::$templatePaths !== null) {
            return self::$templatePaths;
        }
        $cacheID = 'template_paths';
        /** @var array<string, string>|false $templatePaths */
        $templatePaths = Shop::Container()->getCache()->get($cacheID);
        if ($templatePaths !== false) {
            self::$templatePaths = $templatePaths;

            return $templatePaths;
        }

        $templatePaths = [];
        try {
            $plugins = Shop::Container()->getDB()->selectAll(
                'tplugin',
                'nStatus',
                State::ACTIVATED,
                'cPluginID, cVerzeichnis, nVersion, bExtension',
                'nPrio'
            );
        } catch (InvalidArgumentException) {
            $plugins = Shop::Container()->getDB()->getObjects(
                'SELECT cPluginID, cVerzeichnis, nVersion, 0 AS bExtension
                    FROM tplugin
                    WHERE nStatus = :stt
                    ORDER BY nPrio',
                ['stt' => State::ACTIVATED]
            );
        }
        foreach ($plugins as $plugin) {
            $path = (int)$plugin->bExtension === 1
                ? \PFAD_ROOT . \PLUGIN_DIR . $plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE
                : \PFAD_ROOT . \PFAD_PLUGIN . $plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $plugin->nVersion . '/' . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE;
            if (\is_dir($path)) {
                $templatePaths[(string)$plugin->cPluginID] = $path;
            }
        }
        Shop::Container()->getCache()->set($cacheID, $templatePaths, [\CACHING_GROUP_PLUGIN]);

        return $templatePaths;
    }

    /**
     * @param PluginInterface $plugin
     * @param array           $params
     * @return bool
     * @former pluginLizenzpruefung()
     * @since 5.0.0
     */
    public static function licenseCheck(PluginInterface $plugin, array $params = []): bool
    {
        $license = $plugin->getLicense();
        if (!$license->hasLicenseCheck()) {
            return true;
        }
        require_once $plugin->getPaths()->getLicencePath() . $license->getClassName();
        $class    = $license->getClass();
        $instance = new $class();
        $method   = \PLUGIN_LICENCE_METHODE;

        if (!$instance->$method($license->getKey())) {
            $upd          = new stdClass();
            $upd->nStatus = State::LICENSE_KEY_INVALID;
            $upd->cFehler = 'Lizenzschlüssel ist ungültig';
            Shop::Container()->getDB()->update('tplugin', 'kPlugin', $plugin->getID(), $upd);
            $logger = Shop::Container()->getLogService();
            if ($logger instanceof Logger) {
                $logger = $logger->withName('kPlugin');
            }
            $logger->error(
                'Plugin Lizenzprüfung: Das Plugin "' . $plugin->getMeta()->getName() .
                '" hat keinen gültigen Lizenzschlüssel und wurde daher deaktiviert!',
                [$plugin->getID()]
            );
            if (isset($params['cModulId']) && \mb_strlen($params['cModulId']) > 0) {
                self::updatePaymentMethodState($plugin, 0);
            }
            Shop::Container()->getCache()->flush('hook_list');
            self::$hookList = null;
            Shop::set('oplugin_' . $plugin->getID(), null);

            return false;
        }

        return true;
    }

    /**
     * @former aenderPluginStatus()
     * @since 5.0.0
     */
    public static function updateStatusByID(int $state, int $id): bool
    {
        return Shop::Container()->getDB()->update('tplugin', 'kPlugin', $id, (object)['nStatus' => $state]) > 0;
    }

    /**
     * @former aenderPluginZahlungsartStatus()
     * @since 5.0.0
     */
    public static function updatePaymentMethodState(PluginInterface $plugin, int $state): void
    {
        $db = Shop::Container()->getDB();
        foreach (\array_keys($plugin->getPaymentMethods()->getMethodsAssoc()) as $moduleID) {
            $db->update(
                'tzahlungsart',
                'cModulId',
                $moduleID,
                (object)['nActive' => $state]
            );
        }
    }

    /**
     * @former gibPlugincModulId()
     * @since 5.0.0
     */
    public static function getModuleIDByPluginID(int $id, string $paymentMethodName): string
    {
        return $id > 0 && \mb_strlen($paymentMethodName) > 0
            ? 'kPlugin_' . $id . '_' . \mb_convert_case(
                \str_replace([' ', '-', '_'], '', $paymentMethodName),
                \MB_CASE_LOWER
            )
            : '';
    }

    /**
     * @former gibkPluginAuscModulId()
     * @since 5.0.0
     */
    public static function getIDByModuleID(string $moduleID): int
    {
        return \preg_match('/^kPlugin_(\d+)_/', $moduleID, $matches)
            ? (int)$matches[1]
            : 0;
    }

    /**
     * @former gibkPluginAuscPluginID()
     * @since 5.0.0
     */
    public static function getIDByPluginID(string $pluginID): int
    {
        return (int)(Shop::Container()->getDB()->select('tplugin', 'cPluginID', $pluginID)->kPlugin ?? 0);
    }

    /**
     * @former gibkPluginAuscPluginID()
     * @since 5.0.0
     */
    public static function getIDByExsID(string $exsID): int
    {
        return (int)(Shop::Container()->getDB()->select('tplugin', 'exsID', $exsID)->kPlugin ?? 0);
    }

    /**
     * @return array<string, string>
     * @former gibPluginSprachvariablen()
     * @since 5.0.0
     */
    public static function getLanguageVariablesByID(int $id, string $iso = ''): array
    {
        $return = [];
        $prep   = ['pid' => $id];
        $sql    = '';
        if (\mb_strlen($iso) > 0) {
            $prep['iso'] = \mb_convert_case($iso, \MB_CASE_UPPER);
            $sql         = ' AND tpluginsprachvariablesprache.cISO = :iso';
        }
        $langVars = Shop::Container()->getDB()->getArrays(
            'SELECT t.kPluginSprachvariable,
                t.kPlugin,
                t.cName,
                t.cBeschreibung,
                tpluginsprachvariablesprache.cISO,
                IF (c.cName IS NOT NULL, c.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable AS t
                LEFT JOIN tpluginsprachvariablesprache
                    ON  t.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache AS c
                    ON c.kPlugin = t.kPlugin
                    AND c.kPluginSprachvariable = t.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = c.cISO
                WHERE t.kPlugin = :pid' . $sql,
            $prep
        );
        if (\count($langVars) < 1) {
            $prep['iso'] = \mb_convert_case($iso, \MB_CASE_UPPER);
            $langVars    = Shop::Container()->getDB()->getArrays(
                "SELECT tpluginsprachvariable.kPluginSprachvariable,
                tpluginsprachvariable.kPlugin,
                tpluginsprachvariable.cName,
                tpluginsprachvariable.cBeschreibung,
                CONCAT('#', tpluginsprachvariable.cName, '#') AS customValue, :iso AS cISO
                    FROM tpluginsprachvariable
                    WHERE tpluginsprachvariable.kPlugin = :pid",
                $prep
            );
        }
        foreach ($langVars as $_sv) {
            $return[$_sv['cName']] = $_sv['customValue'];
        }

        return $return;
    }

    /**
     * Holt alle PluginSprachvariablen (falls vorhanden)
     *
     * @return stdClass[]
     * @former gibSprachVariablen()
     */
    public static function getLanguageVariables(int $pluginID): array
    {
        $langVars = Shop::Container()->getDB()->getArrays(
            'SELECT l.kPluginSprachvariable, l.kPlugin, l.cName, l.cBeschreibung,
            COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)  AS cISO,
            COALESCE(c.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable AS l
                LEFT JOIN tpluginsprachvariablecustomsprache AS c
                    ON c.kPluginSprachvariable = l.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = l.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
            WHERE l.kPlugin = :pid
            ORDER BY l.kPluginSprachvariable',
            ['pid' => $pluginID]
        );
        if (\count($langVars) === 0) {
            return [];
        }
        $new = [];
        foreach ($langVars as $lv) {
            $item = $new[$lv['kPluginSprachvariable']] ?? null;
            if ($item !== null) {
                $item->oPluginSprachvariableSprache_arr[$lv['cISO']] = $lv['customValue'];
            } else {
                $var                                   = new stdClass();
                $var->kPluginSprachvariable            = (int)$lv['kPluginSprachvariable'];
                $var->kPlugin                          = (int)$lv['kPlugin'];
                $var->cName                            = $lv['cName'];
                $var->cBeschreibung                    = $lv['cBeschreibung'];
                $var->oPluginSprachvariableSprache_arr = [$lv['cISO'] => $lv['customValue']];
                $new[$lv['kPluginSprachvariable']]     = $var;
            }
        }

        return \array_values($new);
    }

    /**
     * @return array<string, string|string[]>
     * @former gibPluginEinstellungen()
     * @since 5.0.0
     */
    public static function getConfigByID(int $id): array
    {
        $conf    = [];
        $cacheID = 'plgnh_cnfg_' . $id;
        /** @var array<string, string>|false $data */
        $data = Shop::Container()->getCache()->get($cacheID);
        if ($data !== false) {
            return $data;
        }
        $data = Shop::Container()->getDB()->getObjects(
            'SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
                FROM tplugin
                JOIN tplugineinstellungen 
                    ON tplugineinstellungen.kPlugin = tplugin.kPlugin
                LEFT JOIN tplugineinstellungenconf 
                    ON tplugineinstellungenconf.kPlugin = tplugin.kPlugin 
                    AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
                WHERE tplugin.kPlugin = :pid',
            ['pid' => $id]
        );
        foreach ($data as $item) {
            $conf[$item->cName] = $item->cConf === Config::TYPE_DYNAMIC
                ? \unserialize($item->cWert, ['allowed_classes' => false])
                : $item->cWert;
        }
        Shop::Container()->getCache()->set(
            $cacheID,
            $conf,
            [\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]
        );

        return $conf;
    }

    public static function bootstrap(int $id, LoaderInterface $loader): ?BootstrapperInterface
    {
        if (!isset(self::$bootstrapper[$id])) {
            try {
                $plugin = $loader->init($id);
            } catch (InvalidArgumentException) {
                return null;
            }
            if ($plugin->isBootstrap() === false) {
                return null;
            }
            if ($loader instanceof LegacyPluginLoader) {
                $file  = $plugin->getPaths()->getVersionedPath() . \OLD_BOOTSTRAPPER;
                $class = \sprintf('%s\\%s', $plugin->getPluginID(), 'Bootstrap');
                if (!\is_file($file)) {
                    return null;
                }
                require_once $file;
            } else {
                $class = \sprintf('Plugin\\%s\\%s', $plugin->getPluginID(), 'Bootstrap');
            }
            /** @var class-string<PluginInterface> $class */
            if (!\class_exists($class)) {
                return null;
            }
            $bootstrapper = new $class($plugin, $loader->getDB(), $loader->getCache());
            if (!$bootstrapper instanceof BootstrapperInterface) {
                return null;
            }
            self::$bootstrapper[$id] = $bootstrapper;
        }

        return self::$bootstrapper[$id];
    }

    /**
     * @return BootstrapperInterface[]
     * @since 5.1.2
     */
    public static function getBootstrappedPlugins(): array
    {
        return self::$bootstrapper;
    }

    public static function updatePluginInstance(PluginInterface $plugin): void
    {
        if (($bootstrapper = self::$bootstrapper[$plugin->getID()] ?? null) !== null) {
            $bootstrapper->getPlugin()->updateInstance($plugin);
        }
    }

    public static function getLoaderByPluginID(
        int $id,
        ?DbInterface $db = null,
        ?JTLCacheInterface $cache = null
    ): LoaderInterface {
        $cache = $cache ?? Shop::Container()->getCache();
        $db    = $db ?? Shop::Container()->getDB();
        $data  = $db->select('tplugin', 'kPlugin', $id);

        return self::getLoader((bool)($data->bExtension ?? false), $db, $cache);
    }

    public static function getLoader(
        bool $isExtension,
        ?DbInterface $db = null,
        ?JTLCacheInterface $cache = null
    ): LoaderInterface {
        $cache = $cache ?? Shop::Container()->getCache();
        $db    = $db ?? Shop::Container()->getDB();

        return $isExtension ? new PluginLoader($db, $cache) : new LegacyPluginLoader($db, $cache);
    }

    public static function ioTestLoading(int $pluginID): stdClass
    {
        $result = (object)[
            'code'    => InstallCode::WRONG_PARAM,
            'message' => '',
        ];
        if ($pluginID <= 0) {
            $result->code    = InstallCode::NO_PLUGIN_FOUND;
            $result->message = \__('errorPluginNotFound');

            return $result;
        }

        \register_shutdown_function(static function (): void {
            $err = \error_get_last();
            if ($err !== null) {
                \ob_get_clean();
                $io = AdminIO::getInstance();
                $io->respondAndExit(new IOError($err['message']));
            }
        });

        $db     = Shop::Container()->getDB();
        $cache  = Shop::Container()->getCache();
        $data   = $db->select('tplugin', 'kPlugin', $pluginID);
        $loader = (int)($data->bExtension ?? 0) === 1
            ? new PluginLoader($db, $cache)
            : new LegacyPluginLoader($db, $cache);
        try {
            $loader->init($pluginID);
            $boot = self::bootstrap($pluginID, $loader);
            if ($boot === null) {
                $result->code = InstallCode::OK;

                return $result;
            }
            if (($p = $boot->getPlugin()) !== null) {
                $result->code    = InstallCode::OK;
                $result->message = $p->getPluginID();

                return $result;
            }
        } catch (Exception $e) {
            $result->code    = InstallCode::NO_PLUGIN_FOUND;
            $result->message = $e->getMessage();
        }

        return $result;
    }
}
