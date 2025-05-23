<?php

declare(strict_types=1);

namespace JTL\Template;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Struct\ExsLicense;
use JTL\Smarty\JTLSmarty;

/**
 * Class Bootstrapper
 * @package JTL\Plugin
 */
abstract class Bootstrapper implements BootstrapperInterface
{
    private Model $template;

    private JTLSmarty $smarty;

    final public function __construct(private DbInterface $db, private JTLCacheInterface $cache)
    {
    }

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function installed(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
    }

    /**
     * @inheritdoc
     */
    public function enabled(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function disabled(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): Model
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(Model $template): void
    {
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function getSmarty(): ?JTLSmarty
    {
        return $this->smarty;
    }

    /**
     * @inheritdoc
     */
    public function setSmarty(JTLSmarty $smarty): void
    {
        $this->smarty = $smarty;
    }

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

    /**
     * @inheritdoc
     */
    public function licenseExpired(ExsLicense $license): void
    {
    }
}
