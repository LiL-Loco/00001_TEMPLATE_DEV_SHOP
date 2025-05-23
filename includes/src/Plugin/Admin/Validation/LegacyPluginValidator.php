<?php

declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation;

use InvalidArgumentException;
use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;
use JTL\XMLParser;
use JTLShop\SemVer\Version;

/**
 * Class LegacyPluginValidator
 * @package JTL\Plugin\Admin\Validation
 */
final class LegacyPluginValidator extends AbstractValidator
{
    protected const BASE_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    /**
     * @inheritdoc
     */
    public function validateByPluginID(int $pluginID, bool $forUpdate = false): int
    {
        $plugin = $this->db->select('tplugin', 'kPlugin', $pluginID);
        if ($plugin === null || empty($plugin->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        $dir  = self::BASE_DIR . $plugin->cVerzeichnis;
        $info = $dir . '/' . \PLUGIN_INFO_FILE;
        $this->setDir($dir);
        if (!\is_dir($dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        if (!\file_exists($info)) {
            return InstallCode::INFO_XML_MISSING;
        }
        $parser = new XMLParser();

        return $this->pluginPlausiIntern($parser->parse($info), $forUpdate);
    }

    /**
     * @inheritdoc
     */
    public function validateByPath(string $path, bool $forUpdate = false): int
    {
        $this->setDir($path);
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        if (!\is_dir($this->dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        $infoXML = $this->dir . '/' . \PLUGIN_INFO_FILE;
        if (!\file_exists($infoXML)) {
            return InstallCode::INFO_XML_MISSING;
        }
        $parser = new XMLParser();
        $xml    = $parser->parse($infoXML);

        return $this->pluginPlausiIntern($xml, $forUpdate);
    }

    /**
     * @inheritdoc
     */
    public function pluginPlausiIntern(?array $xml, bool $forUpdate): int
    {
        $isShop4Compatible = false;
        $shopVersion       = Version::parse(\APPLICATION_VERSION);
        $baseNode          = $xml['jtlshop3plugin'][0] ?? null;
        if ($baseNode === null) {
            return InstallCode::MISSING_PLUGIN_NODE;
        }
        if (!isset($baseNode['XMLVersion'])) {
            return InstallCode::INVALID_XML_VERSION;
        }
        \preg_match('/\d{3}/', $baseNode['XMLVersion'], $hits);
        if (
            \count($hits) === 0
            || (\mb_strlen($hits[0]) !== \mb_strlen($baseNode['XMLVersion']) && (int)$baseNode['XMLVersion'] >= 100)
        ) {
            return InstallCode::INVALID_XML_VERSION;
        }
        if (empty($baseNode['ShopVersion']) && empty($baseNode['Shop4Version'])) {
            return InstallCode::INVALID_SHOP_VERSION;
        }
        if (empty($baseNode['PluginID'])) {
            return InstallCode::INVALID_PLUGIN_ID;
        }
        if ($forUpdate === false) {
            $tmpPlugin = $this->db->select('tplugin', 'cPluginID', $baseNode['PluginID']);
            if ($tmpPlugin !== null && $tmpPlugin->kPlugin > 0) {
                return InstallCode::DUPLICATE_PLUGIN_ID;
            }
        }
        try {
            if (isset($baseNode['Shop4Version'])) {
                $parsedShopVersion = Version::parse($baseNode['Shop4Version']);
                $isShop4Compatible = true;
            } else {
                $parsedShopVersion = Version::parse($baseNode['MaxShopVersion'] ?? $baseNode['ShopVersion']);
            }
        } catch (InvalidArgumentException) {
            $parsedShopVersion = null;
        }
        if ($parsedShopVersion === null || $parsedShopVersion->greaterThan($shopVersion)) {
            return InstallCode::SHOP_VERSION_COMPATIBILITY;
        }

        $versionNumber = $this->getVersion($baseNode['Install'][0], $this->getDir());
        if (!\is_string($versionNumber)) {
            return $versionNumber;
        }
        $validation = new LegacyPluginValidationFactory();
        $checks     = $validation->getValidations($baseNode, $this->getDir(), $versionNumber, $baseNode['PluginID']);
        foreach ($checks as $check) {
            $res = $check->validate();
            if ($res !== InstallCode::OK) {
                return $res;
            }
        }

        return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_LEGACY;
    }

    /**
     * @param array  $node
     * @param string $dir
     * @return int|string
     */
    private function getVersion(array $node, string $dir): int|string
    {
        if (!GeneralObject::hasCount('Version', $node)) {
            return InstallCode::INVALID_VERSION_NUMBER;
        }
        if ((int)$node['Version']['0 attr']['nr'] !== 100) {
            return InstallCode::INVALID_VERSION_NUMBER;
        }
        $version = '';
        foreach ($node['Version'] as $i => $Version) {
            $i = (string)$i;
            \preg_match('/\d+\sattr/', $i, $hits1);
            \preg_match('/\d+/', $i, $hits2);
            if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($i)) {
                $version = $Version['nr'];
                \preg_match('/\d+/', $Version['nr'], $hits);
                if (\mb_strlen($hits[0]) !== \mb_strlen($Version['nr'])) {
                    return InstallCode::INVALID_VERSION_NUMBER;
                }
            } elseif (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                if (
                    isset($Version['SQL'])
                    && \mb_strlen($Version['SQL']) > 0
                    && !\file_exists(
                        $dir . '/' . \PFAD_PLUGIN_VERSION . $version . '/' . \PFAD_PLUGIN_SQL . $Version['SQL']
                    )
                ) {
                    return InstallCode::MISSING_SQL_FILE;
                }
                if (!\is_dir($dir . '/' . \PFAD_PLUGIN_VERSION . $version)) {
                    return InstallCode::MISSING_VERSION_DIR;
                }
                \preg_match('/\d{4}-[0-1]\d-[0-3]\d/', $Version['CreateDate'], $hits);
                if (!isset($hits[0]) || \mb_strlen($hits[0]) !== \mb_strlen($Version['CreateDate'])) {
                    return InstallCode::INVALID_DATE;
                }
            }
        }

        return $version;
    }
}
