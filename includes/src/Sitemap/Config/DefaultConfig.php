<?php

declare(strict_types=1);

namespace JTL\Sitemap\Config;

use JTL\DB\DbInterface;
use JTL\Sitemap\Factories\Base;
use JTL\Sitemap\Factories\Category;
use JTL\Sitemap\Factories\LiveSearch;
use JTL\Sitemap\Factories\Manufacturer;
use JTL\Sitemap\Factories\NewsCategory;
use JTL\Sitemap\Factories\NewsItem;
use JTL\Sitemap\Factories\Page;
use JTL\Sitemap\Factories\Product;

/**
 * Class DefaultConfig
 * @package JTL\Sitemap\Config
 */
final class DefaultConfig implements ConfigInterface
{
    public function __construct(
        private readonly DbInterface $db,
        private readonly array $config,
        private readonly string $baseURL,
        private readonly string $baseImageURL
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getFactories(): array
    {
        $res = [
            new Base($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Product($this->db, $this->config, $this->baseURL, $this->baseImageURL)
        ];
        if ($this->config['sitemap']['sitemap_kategorien_anzeigen'] === 'Y') {
            $res[] = new Category($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_hersteller_anzeigen'] === 'Y') {
            $res[] = new Manufacturer($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_livesuche_anzeigen'] === 'Y') {
            $res[] = new LiveSearch($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_seiten_anzeigen'] === 'Y') {
            $res[] = new Page($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_newskategorien_anzeigen'] === 'Y') {
            $res[] = new NewsCategory($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_news_anzeigen'] === 'Y') {
            $res[] = new NewsItem($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }

        return $res;
    }
}
