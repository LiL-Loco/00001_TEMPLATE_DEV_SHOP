<?php

declare(strict_types=1);

namespace JTL\Sitemap\ItemRenderers;

use JTL\Sitemap\Items\ItemInterface;

/**
 * Class AbstractItemRenderer
 * @package JTL\Sitemap\ItemRenderers
 */
abstract class AbstractItemRenderer implements RendererInterface
{
    protected string|int|null $lastID = null;

    /**
     * @var ItemInterface[]
     */
    protected array $queue = [];

    /**
     * @var array
     */
    protected array $config;

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function flush(): string
    {
        return '';
    }
}
