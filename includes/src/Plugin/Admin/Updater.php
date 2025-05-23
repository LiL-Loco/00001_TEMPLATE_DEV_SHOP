<?php

declare(strict_types=1);

namespace JTL\Plugin\Admin;

use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;

/**
 * Class Updater
 * @package JTL\Plugin\Admin
 */
class Updater
{
    public function __construct(private readonly DbInterface $db, private readonly Installer $installer)
    {
    }

    /**
     * @former updatePlugin()
     */
    public function update(int $pluginID): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $loader = Helper::getLoaderByPluginID($pluginID, $this->db);
        try {
            $plugin = $loader->init($pluginID, true);
        } catch (InvalidArgumentException) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        $this->installer->setPlugin($plugin);
        $this->installer->setDir($plugin->getPaths()->getBaseDir());

        return $this->installer->prepare();
    }

    public function updateFromListingItem(ListingItem $item): int
    {
        if ($item->getID() === 0) {
            return InstallCode::WRONG_PARAM;
        }
        $loader = Helper::getLoaderByPluginID($item->getID(), $this->db);
        try {
            $plugin = $loader->init($item->getID(), true);
        } catch (InvalidArgumentException) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        $this->installer->setPlugin($plugin);
        $this->installer->setDir($item->getUpdateFromDir() ?? $plugin->getPaths()->getBaseDir());

        return $this->installer->prepare();
    }
}
