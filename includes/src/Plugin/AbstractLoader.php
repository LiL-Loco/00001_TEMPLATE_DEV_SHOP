<?php

declare(strict_types=1);

namespace JTL\Plugin;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Manager;
use JTL\License\Struct\ExpiredExsLicense;
use JTL\Plugin\Data\AdminMenu;
use JTL\Plugin\Data\Cache;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Data\Hook;
use JTL\Plugin\Data\License;
use JTL\Plugin\Data\Links;
use JTL\Plugin\Data\Localization;
use JTL\Plugin\Data\MailTemplates;
use JTL\Plugin\Data\Meta;
use JTL\Plugin\Data\Paths;
use JTL\Plugin\Data\PaymentMethods;
use JTL\Plugin\Data\Widget;
use JTL\Router\Route;
use JTL\Shop;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class AbstractLoader
 * @package JTL\Plugin
 */
abstract class AbstractLoader implements LoaderInterface
{
    protected DbInterface $db;

    protected JTLCacheInterface $cache;

    protected ?string $cacheID = null;

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    protected function loadLinks(int $id): Links
    {
        $data = $this->db->getObjects(
            'SELECT tlink.kLink
                FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                WHERE tlink.kPlugin = :plgn
                GROUP BY tlink.kLink',
            ['plgn' => $id]
        );

        return (new Links())->load($data, $this->db);
    }

    protected function loadLocalization(int $id, string $currentLanguageCode): Localization
    {
        $data = $this->db->getObjects(
            'SELECT l.kPluginSprachvariable, l.kPlugin, l.cName, l.cBeschreibung, o.cISO,
                COALESCE(c.cName, o.cName) AS customValue, l.type
            FROM tpluginsprachvariable AS l
            JOIN tpluginsprachvariablesprache AS o
                ON o.kPluginSprachvariable = l.kPluginSprachvariable
            LEFT JOIN tpluginsprachvariablecustomsprache AS c
                ON c.kPluginSprachvariable = l.kPluginSprachvariable
                AND o.cISO = c.cISO
            WHERE l.kPlugin = :pid
            ORDER BY l.kPluginSprachvariable',
            ['pid' => $id]
        );

        return (new Localization($currentLanguageCode))->load($data);
    }

    protected function loadMetaData(stdClass $obj): Meta
    {
        return (new Meta())->loadDBMapping($obj);
    }

    protected function loadConfig(string $path, int $id): Config
    {
        $data = $this->db->getObjects(
            'SELECT c.kPluginEinstellungenConf AS id, c.cName AS name,
            c.cBeschreibung AS description, c.kPluginAdminMenu AS menuID, c.cConf AS confType,
            c.nSort, c.cInputTyp AS inputType, c.cSourceFile AS sourceFile,
            v.cName AS confNicename, v.cWert AS confValue, v.nSort AS confSort, e.cWert AS currentValue,
            c.cWertName AS confName
            FROM tplugineinstellungenconf AS c
            LEFT JOIN tplugineinstellungenconfwerte AS v
                ON c.kPluginEinstellungenConf = v.kPluginEinstellungenConf
            LEFT JOIN tplugineinstellungen AS e
                ON e.kPlugin = c.kPlugin AND e.cName = c.cWertName
            WHERE c.kPlugin = :pid
            GROUP BY id, confValue
            ORDER BY c.nSort',
            ['pid' => $id]
        );

        return (new Config($path))->load($data);
    }

    /**
     * @return Hook[]
     */
    protected function loadHooks(int $id): array
    {
        return \array_map(static function (stdClass $data): Hook {
            $hook = new Hook();
            $hook->setPriority((int)$data->nPriority);
            $hook->setFile($data->cDateiname);
            $hook->setID((int)$data->nHook);
            $hook->setPluginID((int)$data->kPlugin);

            return $hook;
        }, $this->db->selectAll('tpluginhook', 'kPlugin', $id));
    }

    protected function loadPaths(string $pluginDir, int $id): Paths
    {
        $shopURL  = Shop::getURL(true) . '/';
        $basePath = \PFAD_ROOT . \PLUGIN_DIR . $pluginDir . '/';
        $baseURL  = $shopURL . \PLUGIN_DIR . $pluginDir . '/';

        $paths = new Paths();
        $paths->setShopURL($shopURL);
        $paths->setBaseDir($pluginDir);
        $paths->setBasePath($basePath);
        $paths->setVersionedPath($basePath);
        $paths->setBaseURL($baseURL);
        $paths->setFrontendPath($basePath . \PFAD_PLUGIN_FRONTEND);
        $paths->setFrontendURL($baseURL . \PFAD_PLUGIN_FRONTEND);
        $paths->setAdminPath($basePath . \PFAD_PLUGIN_ADMINMENU);
        $paths->setAdminURL($baseURL . \PFAD_PLUGIN_ADMINMENU);
        $paths->setBackendURL(Shop::getAdminURL() . '/' . Route::PLUGIN . '/' . $id);
        $paths->setBackendRoute('/' . Route::PLUGIN . '/' . $id);
        $paths->setLicencePath($basePath . \PFAD_PLUGIN_LICENCE);
        $paths->setUninstaller($basePath . \PFAD_PLUGIN_UNINSTALL);
        $paths->setPortletsPath($basePath . \PFAD_PLUGIN_PORTLETS);
        $paths->setPortletsUrl($baseURL . \PFAD_PLUGIN_PORTLETS);
        $paths->setExportPath($basePath . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_EXPORTFORMAT);

        return $paths;
    }

    protected function loadLicense(stdClass $data): License
    {
        $license = new License();
        if (\strlen($data->cLizenzKlasse) > 0 && !\str_starts_with($data->cLizenzKlasse, 'Plugin\\')) {
            $namespace           = $data->cPluginID . '\\' . \trim(\PFAD_PLUGIN_LICENCE, '\\/');
            $data->cLizenzKlasse = \sprintf('Plugin\\%s\\%s', $namespace, $data->cLizenzKlasse);
        }
        $license->setClass($data->cLizenzKlasse ?? '');
        $license->setClassName($data->cLizenzKlasseName ?? '');
        $license->setKey($data->cLizenz ?? '');
        if (!empty($data->exsID)) {
            $manager    = new Manager($this->db, $this->cache);
            $exsLicense = $manager->getLicenseByExsID($data->exsID);
            if ($exsLicense === null) {
                $exsLicense = new ExpiredExsLicense();
                $exsLicense->initFromPluginData($data);
            }
            $license->setExsLicense($exsLicense);
        }

        return $license;
    }

    protected function loadCacheData(PluginInterface $plugin): Cache
    {
        $cache = new Cache();
        $cache->setGroup(\CACHING_GROUP_PLUGIN . '_' . $plugin->getID());
        $cache->setID($cache->getGroup() . '_' . $plugin->getMeta()->getVersion());

        return $cache;
    }

    protected function loadAdminMenu(PluginInterface $plugin): AdminMenu
    {
        $i     = -1;
        $menus = \array_map(static function (stdClass $menu) use (&$i): stdClass {
            $menu->name             = $menu->cName;
            $menu->cName            = \__($menu->cName);
            $menu->displayName      = $menu->cName;
            $menu->kPluginAdminMenu = (int)$menu->kPluginAdminMenu;
            $menu->id               = $menu->kPluginAdminMenu;
            $menu->kPlugin          = (int)$menu->kPlugin;
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = (int)$menu->nSort;
            $menu->sort             = $menu->nSort;
            $menu->nConf            = (int)$menu->nConf;
            $menu->configurable     = (bool)$menu->nConf;
            $menu->file             = $menu->cDateiname;
            $menu->isMarkdown       = false;
            $menu->idx              = ++$i;
            $menu->html             = '';
            $menu->tpl              = '';

            return $menu;
        }, $this->db->selectAll('tpluginadminmenu', 'kPlugin', $plugin->getID(), '*', 'nSort'));
        $menus = \collect($menus);
        $this->addMarkdownToAdminMenu($plugin, $menus);
        $this->addLicenseInfo($plugin, $menus);

        $adminMenu = new AdminMenu();
        $adminMenu->setItems($menus);
        $plugin->setAdminMenu($adminMenu);

        return $adminMenu;
    }

    /**
     * @param PluginInterface      $plugin
     * @param Collection<stdClass> $items
     * @return Collection<stdClass>
     */
    protected function addMarkdownToAdminMenu(PluginInterface $plugin, Collection $items): Collection
    {
        $meta = $plugin->getMeta();
        /** @var stdClass $lastItem */
        $lastItem = $items->last();
        $lastIdx  = $lastItem->idx ?? -1;
        if (!empty($meta->getReadmeMD())) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'md-' . $lastIdx;
            $menu->kPlugin          = $plugin->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->name             = 'docs';
            $menu->cName            = \__('Dokumentation');
            $menu->displayName      = $menu->cName;
            $menu->cDateiname       = $meta->getReadmeMD();
            $menu->file             = $menu->cDateiname;
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = true;
            $menu->tpl              = 'tpl_inc/plugin_documentation.tpl';
            $menu->html             = '';
            $items->push($menu);
        }
        if (!empty($meta->getLicenseMD())) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'md-' . $lastIdx;
            $menu->kPlugin          = $plugin->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->name             = 'license';
            $menu->cName            = \__('Lizenzvereinbarungen');
            $menu->displayName      = $menu->cName;
            $menu->cDateiname       = $meta->getLicenseMD();
            $menu->file             = $menu->cDateiname;
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = true;
            $menu->tpl              = 'tpl_inc/plugin_license.tpl';
            $menu->html             = '';
            $items->push($menu);
        }
        if (!empty($meta->getChangelogMD())) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'md-' . $lastIdx;
            $menu->kPlugin          = $plugin->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->name             = 'changelog';
            $menu->cName            = \__('Changelog');
            $menu->displayName      = $menu->cName;
            $menu->cDateiname       = $meta->getChangelogMD();
            $menu->file             = $menu->cDateiname;
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = true;
            $menu->tpl              = 'tpl_inc/plugin_changelog.tpl';
            $menu->html             = '';
            $items->push($menu);
        }

        return $items;
    }

    /**
     * @param PluginInterface      $plugin
     * @param Collection<stdClass> $items
     * @return Collection<stdClass>
     */
    protected function addLicenseInfo(PluginInterface $plugin, Collection $items): Collection
    {
        /** @var stdClass $lastItem */
        $lastItem = $items->last();
        $lastIdx  = $lastItem->idx ?? -1;
        $license  = $plugin->getLicense()->getExsLicense();
        if ($license !== null) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->data             = $license;
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'plugin-license-' . $lastIdx;
            $menu->kPlugin          = $plugin->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->name             = 'licenseinfo';
            $menu->cName            = \__('Lizenz');
            $menu->displayName      = $menu->cName;
            $menu->cDateiname       = '';
            $menu->file             = '';
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = false;
            $menu->tpl              = 'tpl_inc/plugin_license_info.tpl';
            $menu->html             = '';
            $items->push($menu);
        }

        return $items;
    }

    protected function loadMarkdownFiles(string $basePath, Meta $meta): self
    {
        if ($this->checkFileExistence($basePath . 'README.md')) {
            $meta->setReadmeMD($basePath . 'README.md');
        }
        if ($this->checkFileExistence($basePath . 'CHANGELOG.md')) {
            $meta->setChangelogMD($basePath . 'CHANGELOG.md');
        }
        foreach (['license.md', 'License.md', 'LICENSE.md'] as $licenseName) {
            if ($this->checkFileExistence($basePath . $licenseName)) {
                $meta->setLicenseMD($basePath . $licenseName);
                break;
            }
        }

        return $this;
    }

    /**
     * perform a "search for a particular file" only once
     *
     * @param string $canonicalFileName - full path of the file to check
     * @return bool
     */
    protected function checkFileExistence(string $canonicalFileName): bool
    {
        static $checked = [];
        if (!\array_key_exists($canonicalFileName, $checked)) {
            // only if we did not know that file (in our "remember-array"), we perform this check
            $checked[$canonicalFileName] = \file_exists($canonicalFileName); // do the actual check
        }

        return $checked[$canonicalFileName];
    }

    protected function loadWidgets(PluginInterface $plugin): Widget
    {
        $data = $this->db->selectAll(
            'tadminwidgets',
            'kPlugin',
            $plugin->getID()
        );
        foreach ($data as $item) {
            $item->namespace = '\\' . $plugin->getPluginID() . '\\';
        }

        return (new Widget())->load($data, $plugin->getPaths()->getAdminPath());
    }

    protected function loadMailTemplates(PluginInterface $plugin): MailTemplates
    {
        $data = $this->db->getObjects(
            'SELECT * FROM temailvorlage
                JOIN temailvorlagesprache AS loc
                    ON loc.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kPlugin = :id',
            ['id' => $plugin->getID()]
        );

        return (new MailTemplates())->load($data);
    }

    protected function loadPaymentMethods(PluginInterface $plugin): PaymentMethods
    {
        $methods = $this->db->getObjects(
            'SELECT *
                FROM tzahlungsart
                JOIN tpluginzahlungsartklasse
                    ON tpluginzahlungsartklasse.cModulID = tzahlungsart.cModulId
                WHERE tzahlungsart.cModulId LIKE :pid',
            ['pid' => 'kPlugin\_' . $plugin->getID() . '\_%']
        );
        foreach ($methods as $method) {
            $moduleID                                = Helper::getModuleIDByPluginID(
                $plugin->getID(),
                $method->cName
            );
            $method->oZahlungsmethodeEinstellung_arr = $this->db->getObjects(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE :val
                        AND cConf = 'Y'
                    ORDER BY nSort",
                ['val' => $moduleID . '\_%']
            );
            $method->oZahlungsmethodeSprache_arr     = $this->db->selectAll(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart
            );
        }

        return (new PaymentMethods())->load($methods, $plugin);
    }

    public function loadLogger(PluginInterface $plugin): LoggerInterface
    {
        $logger = Shop::Container()->getLogService();

        return $logger instanceof Logger
            ? $logger->withName($plugin->getPluginID())
            : $logger;
    }
}
