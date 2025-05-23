<?php

declare(strict_types=1);

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class LegacyPlugin
 * @package JTL\Plugin,
 */
class LegacyPlugin extends PluginBC
{
    /**
     * @var array
     */
    public array $oPluginHook_arr = [];

    /**
     * @var array
     */
    public array $oPluginEinstellung_arr = [];

    /**
     * @var array
     */
    public array $oPluginEinstellungConf_arr = [];

    /**
     * @var array
     */
    public array $oPluginEinstellungAssoc_arr = [];

    public ?stdClass $oPluginUninstall = null;

    public string $cFehler = '';

    public function __construct(int $id = 0, bool $invalidateCache = false)
    {
        if ($id > 0) {
            $this->loadFromDB($id, Shop::Container()->getDB(), Shop::Container()->getCache(), $invalidateCache);
        }
    }

    /**
     * @return array<int, array<int, stdClass>>
     * @deprecated since 5.0.0
     */
    public static function getHookList(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getHookList();
    }

    /**
     * @param array<int, array<int, stdClass>> $hookList
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function setHookList(array $hookList): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::setHookList($hookList);
    }

    private function loadFromDB(int $id, DbInterface $db, JTLCacheInterface $cache, bool $invalidate = false): ?self
    {
        $loader = new LegacyPluginLoader($db, $cache);
        try {
            $res = $loader->setPlugin($this)->init($id, $invalidate);
            foreach (\get_object_vars($res) as $k => $v) {
                $this->$k = $v;
            }
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $this;
    }

    public function updateInDB(): int
    {
        $obj                       = new stdClass();
        $obj->kPlugin              = $this->getID();
        $obj->cName                = $this->getMeta()->getName();
        $obj->cBeschreibung        = $this->getMeta()->getDescription();
        $obj->cAutor               = $this->getMeta()->getAuthor();
        $obj->cURL                 = $this->getMeta()->getURL();
        $obj->cVerzeichnis         = $this->getPaths()->getBaseDir();
        $obj->cFehler              = $this->cFehler;
        $obj->cLizenz              = $this->getLicense()->getKey();
        $obj->cLizenzKlasse        = $this->getLicense()->getClass();
        $obj->cLizenzKlasseName    = $this->getLicense()->getClassName();
        $obj->nStatus              = $this->getState();
        $obj->nVersion             = $this->getMeta()->getVersion();
        $obj->nPrio                = $this->getPriority();
        $obj->dZuletztAktualisiert = $this->getMeta()->getDateLastUpdate()->format('d.m.Y H:i');
        $obj->dInstalliert         = $this->getMeta()->getDateInstalled()->format('d.m.Y H:i');
        $obj->bBootstrap           = $this->isBootstrap() ? 1 : 0;

        return Shop::Container()->getDB()->update('tplugin', 'kPlugin', $obj->kPlugin, $obj);
    }

    /**
     * @deprecated since 5.0.0
     */
    public function setConf(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @deprecated since 5.0.0
     */
    public function getConf(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @deprecated since 5.0.0
     */
    public static function getPluginById(string $pluginID): ?PluginInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getPluginById($pluginID);
    }

    /**
     * @inheritdoc
     */
    public function getCurrentVersion(): Version
    {
        $path = \PFAD_ROOT . \PFAD_PLUGIN . $this->getPaths()->getBaseDir();
        if (!\is_dir($path) || !\file_exists($path . '/' . \PLUGIN_INFO_FILE)) {
            return Version::parse('0.0.0');
        }
        $parser  = new XMLParser();
        $xml     = $parser->parse($path . '/' . \PLUGIN_INFO_FILE);
        $version = \count($xml['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;

        return Version::parse($xml['jtlshop3plugin'][0]['Install'][0]['Version'][$version . ' attr']['nr']);
    }

    /**
     * @deprecated since 5.0.0
     */
    public function mapPluginStatus(int $state): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Shop::Container()->getPluginState()->map($state);
    }

    /**
     * @return array<string, string>
     * @deprecated since 5.0.0
     */
    public static function getTemplatePaths(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getTemplatePaths();
    }
}
