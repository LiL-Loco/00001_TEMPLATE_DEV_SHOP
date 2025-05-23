<?php

declare(strict_types=1);

namespace JTL\Router;

use Exception;
use JTL\DB\DbInterface;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Request;
use JTL\Settings\Option\Globals;
use JTL\Settings\Settings;
use JTL\Shop;
use stdClass;

use function Functional\pluck;

/**
 * Class DefaultParser
 * @package JTL\Router
 */
class DefaultParser
{
    /**
     * @var array<string, int|bool|array<mixed>>
     */
    private array $params = [];

    protected ProductFilter $productFilter;

    public function __construct(protected DbInterface $db, protected State $state, ?ProductFilter $productFilter = null)
    {
        $this->productFilter = $productFilter ?? Shop::getProductFilter();
    }

    /**
     * @param string[] $hierarchy
     */
    protected function validateCategoryHierarchy(array $hierarchy): ?stdClass
    {
        $seo   = null;
        $left  = [];
        $right = [];
        foreach ($hierarchy as $item) {
            $seo = $this->db->getSingleObject(
                'SELECT tseo.cSeo AS slug, tkategorie.lft, tkategorie.rght 
                    FROM tseo
                    JOIN tkategorie
                        ON tseo.cKey = :keyname
                        AND tseo.kKey = tkategorie.kKategorie
                    WHERE tseo.cSeo = :slg',
                ['slg' => $item, 'keyname' => 'kKategorie']
            );
            if ($seo === null) {
                break;
            }
            $left[]  = (int)$seo->lft;
            $right[] = (int)$seo->rght;
        }
        if ($seo === null) {
            return null;
        }
        $test = \array_values($left);
        \sort($test, \SORT_NUMERIC);
        if ($test !== $left) {
            return null;
        }
        $test = \array_values($right);
        \sort($test, \SORT_NUMERIC);
        $test = \array_reverse($test);
        if ($test !== $right) {
            return null;
        }

        return $seo;
    }

    /**
     * @return array<string, int|bool|array<mixed>>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array<string, int|bool|array<mixed>> $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @param array{id?: int, name?: string, lang?: string}|null $replacements
     */
    public function parse(string $slug, ?array $replacements = null, ?string $type = null): string
    {
        $page = 0;
        $slug = $this->checkCustomFilters($slug);
        // change Opera Fix
        if (\mb_substr($slug, \mb_strlen($slug) - 1, 1) === '?') {
            $slug = \mb_substr($slug, 0, -1);
        }
        $match = \preg_match('/[^_](' . \SEP_SEITE . '(\d+))/', $slug, $matches, \PREG_OFFSET_CAPTURE);
        if ($match === 1) {
            $page = (int)$matches[2][0];
            $slug = \mb_substr($slug, 0, $matches[1][1]);
        }
        if ($page === 1 && \mb_strlen($slug) > 0) {
            $this->redirectPageOne($type, $replacements, (int)$matches[1][1], $slug);
        }
        if ($page > 0) {
            $_GET['seite']          = $page;
            $this->params['kSeite'] = $page;
            $this->state->pageID    = $page;
        }
        $slug = $this->checkCharacteristics($slug);
        $slug = $this->checkManufacturers($slug);
        $slug = $this->checkCategories($slug);

        return $this->checkCharacteristicValues($slug);
    }

    private function checkCustomFilters(string $slug): string
    {
        $customSeo = [];
        foreach ($this->productFilter->getCustomFilters() as $customFilter) {
            $seoParam = $customFilter->getUrlParamSEO();
            if (empty($seoParam)) {
                continue;
            }
            $customFilterArr = \explode($seoParam, $slug);
            if (\count($customFilterArr) <= 1) {
                continue;
            }
            [$slug, $customFilterSeo] = $customFilterArr;
            if (\str_contains($customFilterSeo, \SEP_HST)) {
                $arr             = \explode(\SEP_HST, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug            .= \SEP_HST . $arr[1];
            }
            if (
                ($idx = \mb_strpos($customFilterSeo, \SEP_KAT)) !== false
                && $idx !== \mb_strpos($customFilterSeo, \SEP_HST)
            ) {
                $manufacturers   = \explode(\SEP_KAT, $customFilterSeo);
                $customFilterSeo = $manufacturers[0];
                $slug            .= \SEP_KAT . $manufacturers[1];
            }
            if (\str_contains($customFilterSeo, \SEP_MERKMAL)) {
                $arr             = \explode(\SEP_MERKMAL, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug            .= \SEP_MERKMAL . $arr[1];
            }
            if (\str_contains($customFilterSeo, \SEP_MM_MMW)) {
                $arr             = \explode(\SEP_MM_MMW, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug            .= \SEP_MM_MMW . $arr[1];
            }
            if (\preg_match('/[^_](' . \SEP_SEITE . '(\d+))/', $customFilterSeo) === 1) {
                $arr             = \explode(\SEP_SEITE, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug            .= \SEP_SEITE . $arr[1];
            }

            $customSeo[$customFilter->getClassName()] = [
                'cSeo'  => $customFilterSeo,
                'table' => $customFilter->getTableName()
            ];
        }

        // custom filter
        $this->params['customFilters'] = [];
        foreach ($customSeo as $className => $data) {
            $seoData = $this->db->select($data['table'], 'cSeo', $data['cSeo']);
            if ($seoData !== null && isset($seoData->filterval)) {
                $this->params['customFilters'][$className] = (int)$seoData->filterval;
                $this->state->customFilters[$className]    = (int)$seoData->filterval;
            } else {
                $this->params['bKatFilterNotFound']  = true;
                $this->state->categoryFilterNotFound = true;
            }
            if ($seoData !== null && $seoData->kSprache > 0) {
                Shop::updateLanguage((int)$seoData->kSprache);
            }
        }

        return $slug;
    }

    private function checkCharacteristics(string $slug): string
    {
        $oriSlug         = $slug;
        $characteristics = \explode(\SEP_MERKMAL, $slug);
        $slug            = $characteristics[0];
        $caseMismatches  = [];
        foreach ($characteristics as $i => &$characteristic) {
            if ($i === 0) {
                continue;
            }
            if (
                ($idx = \mb_strpos($characteristic, \SEP_KAT)) !== false
                && $idx !== \mb_strpos($characteristic, \SEP_HST)
            ) {
                $arr            = \explode(\SEP_KAT, $characteristic);
                $characteristic = $arr[0];
                $slug           .= \SEP_KAT . $arr[1];
            }
            if (\str_contains($characteristic, \SEP_HST)) {
                $arr            = \explode(\SEP_HST, $characteristic);
                $characteristic = $arr[0];
                $slug           .= \SEP_HST . $arr[1];
            }
            if (\str_contains($characteristic, \SEP_MM_MMW)) {
                $arr            = \explode(\SEP_MM_MMW, $characteristic);
                $characteristic = $arr[0];
                $slug           .= \SEP_MM_MMW . $arr[1];
            }
            if (\preg_match('/[^_](' . \SEP_SEITE . '(\d+))/', $characteristic) === 1) {
                $arr            = \explode(\SEP_SEITE, $characteristic);
                $characteristic = $arr[0];
                $slug           .= \SEP_SEITE . $arr[1];
            }
        }
        unset($characteristic);
        // attribute filter
        if (\count($characteristics) <= 1) {
            return $slug;
        }
        if (!isset($_GET[\QUERY_PARAM_CHARACTERISTIC_FILTER])) {
            $_GET[\QUERY_PARAM_CHARACTERISTIC_FILTER] = [];
        } elseif (!\is_array($_GET[\QUERY_PARAM_CHARACTERISTIC_FILTER])) {
            $_GET[\QUERY_PARAM_CHARACTERISTIC_FILTER] = [(int)$_GET[\QUERY_PARAM_CHARACTERISTIC_FILTER]];
        }
        $this->params['bSEOMerkmalNotFound'] = false;
        $this->state->characteristicNotFound = false;

        $given = [];
        $real  = [];
        foreach ($characteristics as $i => $seoString) {
            if ($i <= 0) {
                continue;
            }
            $seoData = $this->db->select('tseo', 'cKey', 'kMerkmalWert', 'cSeo', $seoString);
            if ($seoData !== null && \strcasecmp($seoData->cSeo, $seoString) === 0) {
                // haenge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann - @todo?
                $_GET[\QUERY_PARAM_CHARACTERISTIC_FILTER][] = (int)$seoData->kKey;
                $this->state->characteristicFilterIDs[]     = (int)$seoData->kKey;
                $given[]                                    = $seoString;
                $real[]                                     = $seoData;
            } else {
                $this->params['bSEOMerkmalNotFound'] = true;
                $this->state->characteristicNotFound = true;
                break;
            }
        }
        try {
            $caseMismatches = $this->validateCase($given, $real);
        } catch (Exception) {
            $this->params['bSEOMerkmalNotFound'] = true;
            $this->state->characteristicNotFound = true;
        }
        if ($this->state->characteristicNotFound === false) {
            $this->updateCase($oriSlug, $caseMismatches);
        }

        return $slug;
    }

    private function checkManufacturers(string $slug): string
    {
        $oriSlug        = $slug;
        $allFound       = true;
        $manufSeo       = [];
        $manufacturers  = \explode(\SEP_HST, $slug);
        $caseMismatches = [];
        if (\count($manufacturers) > 1) {
            foreach ($manufacturers as $i => $manufacturer) {
                if ($i === 0) {
                    $slug = $manufacturer;
                } else {
                    $manufSeo[] = $manufacturer;
                }
            }
            foreach ($manufSeo as $i => $hstseo) {
                if (($idx = \mb_strpos($hstseo, \SEP_KAT)) !== false && $idx !== \mb_strpos($hstseo, \SEP_HST)) {
                    $manufacturers[] = \explode(\SEP_KAT, $hstseo);
                    $manufSeo[$i]    = $manufacturers[0];
                    $slug            .= \SEP_KAT . $manufacturers[1];
                }
                if (\str_contains($hstseo, \SEP_MERKMAL)) {
                    $arr          = \explode(\SEP_MERKMAL, $hstseo);
                    $manufSeo[$i] = $arr[0];
                    $slug         .= \SEP_MERKMAL . $arr[1];
                }
                if (\str_contains($hstseo, \SEP_MM_MMW)) {
                    $arr          = \explode(\SEP_MM_MMW, $hstseo);
                    $manufSeo[$i] = $arr[0];
                    $slug         .= \SEP_MM_MMW . $arr[1];
                }
                if (\preg_match('/[^_](' . \SEP_SEITE . '(\d+))/', $hstseo) === 1) {
                    $arr          = \explode(\SEP_SEITE, $hstseo);
                    $manufSeo[$i] = $arr[0];
                    $slug         .= \SEP_SEITE . $arr[1];
                }
            }
        } else {
            $slug = $manufacturers[0];
        }
        // manufacturer filter
        if (($seoCount = \count($manufSeo)) === 0) {
            return $slug;
        }
        if ($seoCount === 1) {
            $seoHits = $this->db->getObjects(
                'SELECT kKey, cSeo
                    FROM tseo
                    WHERE cKey = \'kHersteller\' AND cSeo = :seo',
                ['seo' => $manufSeo[0]]
            );
        } else {
            $bindValues = [];
            // PDO::bindValue() is 1-based
            foreach ($manufSeo as $i => $t) {
                $bindValues[$i + 1] = $t;
            }
            $seoHits  = $this->db->getObjects(
                "SELECT kKey, cSeo
                    FROM tseo
                    WHERE cKey = 'kHersteller'
                    AND cSeo IN (" . \implode(',', \array_fill(0, $seoCount, '?')) . ')',
                $bindValues
            );
            $allFound = \count($seoHits) === \count($manufSeo);
        }
        try {
            $caseMismatches = $this->validateCase($manufSeo, $seoHits);
        } catch (Exception) {
            $allFound = false;
        }
        $results = \count($seoHits);
        if ($results === 1 && $allFound === true) {
            $this->state->manufacturerFilterID = (int)$seoHits[0]->kKey;
            $this->params['kHerstellerFilter'] = $this->state->manufacturerFilterID;
        } elseif ($results === 0 || $allFound === false) {
            $this->params['bHerstellerFilterNotFound'] = true;
            $this->state->manufacturerFilterNotFound   = true;
        } else {
            $this->state->manufacturerFilterIDs    = \array_map('\intval', pluck($seoHits, 'kKey'));
            $this->params['manufacturerFilterIDs'] = $this->state->manufacturerFilterIDs;
        }
        if ($allFound === true) {
            $this->updateCase($oriSlug, $caseMismatches);
        }

        return $slug;
    }

    private function checkCategories(string $slug): string
    {
        $allFound       = true;
        $categorySeo    = [];
        $categories     = \explode(\SEP_KAT, $slug);
        $oriSlug        = $slug;
        $caseMismatches = [];
        if (\count($categories) > 1) {
            foreach ($categories as $i => $category) {
                $category = $this->getSlugFromHierarchy($category);
                if ($i === 0) {
                    $slug = $category;
                } else {
                    $categorySeo[] = $category;
                }
            }
            foreach ($categorySeo as $i => $catSeo) {
                if (($idx = \mb_strpos($catSeo, \SEP_HST)) !== false && $idx !== \mb_strpos($catSeo, \SEP_KAT)) {
                    $categories[]    = \explode(\SEP_HST, $catSeo);
                    $categorySeo[$i] = $categories[0];
                    $slug            .= \SEP_KAT . $categories[1];
                }
                if (\str_contains($catSeo, \SEP_MERKMAL)) {
                    $arr             = \explode(\SEP_MERKMAL, $catSeo);
                    $categorySeo[$i] = $arr[0];
                    $slug            .= \SEP_MERKMAL . $arr[1];
                }
                if (\str_contains($catSeo, \SEP_MM_MMW)) {
                    $arr             = \explode(\SEP_MM_MMW, $catSeo);
                    $categorySeo[$i] = $arr[0];
                    $slug            .= \SEP_MM_MMW . $arr[1];
                }
                if (\preg_match('/[^_](' . \SEP_SEITE . '(\d+))/', $catSeo) === 1) {
                    $arr             = \explode(\SEP_SEITE, $catSeo);
                    $categorySeo[$i] = $arr[0];
                    $slug            .= \SEP_SEITE . $arr[1];
                }
            }
        } elseif (\CATEGORIES_SLUG_HIERARCHICALLY === true && \str_contains($slug, '/')) {
            $slug = $this->getSlugFromHierarchy($slug);
        } else {
            $slug = $categories[0];
        }
        $seoCount = \count($categorySeo);
        if ($seoCount === 0) {
            return $slug;
        }
        if ($seoCount === 1) {
            $seoHits = $this->db->getObjects(
                'SELECT kKey, cSeo
                    FROM tseo
                    WHERE cKey = \'kKategorie\' AND cSeo = :seo',
                ['seo' => $categorySeo[0]]
            );
        } else {
            $bindValues = [];
            // PDO::bindValue() is 1-based
            foreach ($categorySeo as $i => $t) {
                $bindValues[$i + 1] = $t;
            }
            $seoHits  = $this->db->getObjects(
                "SELECT kKey, cSeo
                    FROM tseo
                    WHERE cKey = 'kKategorie'
                    AND cSeo IN (" . \implode(',', \array_fill(0, $seoCount, '?')) . ')',
                $bindValues
            );
            $allFound = \count($seoHits) === \count($categorySeo);
        }
        try {
            $caseMismatches = $this->validateCase($categorySeo, $seoHits);
        } catch (Exception) {
            $allFound = false;
        }
        if (!isset($_GET[\QUERY_PARAM_CATEGORY_FILTER])) {
            $_GET[\QUERY_PARAM_CATEGORY_FILTER] = [];
        } elseif (!\is_array($_GET[\QUERY_PARAM_CATEGORY_FILTER])) {
            $_GET[\QUERY_PARAM_CATEGORY_FILTER] = [(int)$_GET[\QUERY_PARAM_CATEGORY_FILTER]];
        }
        $results = \count($seoHits);
        if ($results === 1 && $allFound === true) {
            $this->state->categoryFilterID     = (int)$seoHits[0]->kKey;
            $this->params['categoryFilters'][] = $this->state->categoryFilterID;
        } elseif ($results === 0 || $allFound === false) {
            $this->params['bKatFilterNotFound']  = true;
            $this->state->categoryFilterNotFound = true;
        } else {
            foreach ($seoHits as $hit) {
                $id                                   = (int)$hit->kKey;
                $this->params['categoryFilters'][]    = $id;
                $this->state->categoryFilterIDs[]     = $id;
                $_GET[\QUERY_PARAM_CATEGORY_FILTER][] = $id;
            }
        }
        if ($allFound === true) {
            $this->updateCase($oriSlug, $caseMismatches);
        }

        return $slug;
    }

    private function checkCharacteristicValues(string $slug): string
    {
        // split attribute/attribute value
        $attributes = \explode(\SEP_MM_MMW, $slug);

        return \count($attributes) > 1 ? $attributes[1] : $slug;
    }

    /**
     * @param array<int, string> $givenSlugs
     * @param stdClass[]         $realSlugData
     * @return array<string, string>
     * @throws Exception
     */
    private function validateCase(array $givenSlugs, array $realSlugData): array
    {
        $caseMismatches = [];
        foreach ($givenSlugs as $givenItem) {
            $itemFound = false;
            foreach ($realSlugData as $hit) {
                if (\strcasecmp($hit->cSeo, $givenItem) !== 0) {
                    continue;
                }
                $itemFound = true;
                if ($hit->cSeo !== $givenItem) {
                    $caseMismatches[$givenItem] = $hit->cSeo;
                }
                break;
            }
            if ($itemFound === false) {
                throw new Exception('Item not found');
            }
        }

        return $caseMismatches;
    }

    /**
     * @param array<string, string> $caseMismatches
     */
    private function updateCase(string $oriSlug, array $caseMismatches): void
    {
        if (\count($caseMismatches) === 0) {
            return;
        }
        $fixedSlug                             = \str_replace(
            \array_keys($caseMismatches),
            \array_values($caseMismatches),
            $oriSlug
        );
        $this->state->caseMismatches[$oriSlug] = $fixedSlug;
    }

    /**
     * @param array<string, string>|null $replacements
     */
    public function redirectPageOne(?string $type, ?array $replacements, int $length, string $slug): never
    {
        $url = Shop::getURL() . '/';
        if ($type !== null && isset($replacements['name'])) {
            $replacements['name'] = \mb_substr($replacements['name'], 0, $length);
            $url                  = Shop::getRouter()->getURLByType($type, $replacements);
        } elseif (isset($replacements['lang'])) {
            $c1 = Settings::stringValue(Globals::ROUTING_DEFAULT_LANG);
            $c2 = Settings::stringValue(Globals::ROUTING_SCHEME);
            if ($c1 === 'L' || $c2 === 'L') {
                $url .= $replacements['lang'] . '/';
            }
            $url .= $slug;
        } else {
            $url .= $slug;
        }
        // Append query parameter to $url to ensure correct loading of mobile filters. SHOP-5652
        if (Request::hasGPCData('useMobileFilters')) {
            $url .= '?useMobileFilters=1';
        }
        \http_response_code(301);
        \header('Location: ' . $url);
        exit;
    }

    private function getSlugFromHierarchy(string $slug): string
    {
        if (\CATEGORIES_SLUG_HIERARCHICALLY === true && \str_contains($slug, '/')) {
            $valid = $this->validateCategoryHierarchy(\explode('/', $slug));
            if ($valid !== null) {
                return $valid->slug;
            }
        }

        return $slug;
    }
}
