<?php

declare(strict_types=1);

namespace JTL\Template\Admin\Installation;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Plugin\InstallCode;
use JTL\Template\Admin\Installation\Items\Consent;
use JTL\Template\Admin\Installation\Items\ItemInterface;
use JTL\Template\Model;
use SimpleXMLElement;

/**
 * Class TemplateInstallerFactory
 * @package JTL\Template\Admin\Installation
 */
class TemplateInstallerFactory
{
    public function __construct(
        protected DbInterface $db,
        protected SimpleXMLElement $xml,
        protected ?SimpleXMLElement $parentXml,
        protected Model $model
    ) {
    }

    /**
     * @return Collection<ItemInterface>
     */
    public function getItems(): Collection
    {
        $items = new Collection();
        $items->push(new Consent($this->db, $this->xml, $this->parentXml, $this->model));

        return $items;
    }

    /**
     * @return InstallCode::*
     */
    public function install(): int
    {
        foreach ($this->getItems() as $installationItem) {
            if (($code = $installationItem->install()) !== InstallCode::OK) {
                return $code;
            }
        }

        return InstallCode::OK;
    }
}
