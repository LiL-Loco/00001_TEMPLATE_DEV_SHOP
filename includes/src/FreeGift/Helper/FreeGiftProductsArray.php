<?php

declare(strict_types=1);

namespace JTL\FreeGift\Helper;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\FreeGift\DomainObjects\FreeGiftProductDomainObject;
use ReturnTypeWillChange;

/**
 * @description Use a typed array to store FreeGiftProductDomainObjects and provide some array helper methods
 * @comment This class is useful for providing code linting and auto-completion in IDEs
 */
class FreeGiftProductsArray extends \ArrayObject
{
    /**
     * @param FreeGiftProductDomainObject[] $items
     * @throws Exception
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    /**
     * @return FreeGiftProductDomainObject[]
     * @since 5.4.0
     */
    public function getArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @return Artikel[]
     * @since 5.4.0
     */
    public function getProductArray(): array
    {
        $productArray = [];
        foreach ($this->getArray() as $freeGiftProduct) {
            $product = $freeGiftProduct->getProduct();
            if ($product === null) {
                continue;
            }
            $productArray[] = $product;
        }

        return $productArray;
    }

    /**
     * @since 5.4.0
     */
    public function getByProductID(int $productID): ?FreeGiftProductDomainObject
    {
        foreach ($this->getArray() as $freeGiftProduct) {
            if ($freeGiftProduct->productID === $productID) {
                return $freeGiftProduct;
            }
        }

        return null;
    }

    /**
     * @param float $basketSum
     * @return FreeGiftProductsArray
     * @since 5.4.0
     */
    public function setStillMissingAmounts(
        float $basketSum = 0.00,
    ): self {
        $result = [];
        foreach ($this->getArray() as $freeGiftProduct) {
            $result[] = new FreeGiftProductDomainObject(
                productID: $freeGiftProduct->productID,
                availableFrom: $freeGiftProduct->availableFrom,
                stillMissingAmount: \max($freeGiftProduct->availableFrom - $basketSum, 0),
                product: $freeGiftProduct->getProduct(),
            );
        }
        if ($this->count() === \count($result)) {
            $this->exchangeArray($result);
        }

        return $this;
    }

    /**
     * @since 5.4.0
     */
    public function sortByStillMissingAmount(string $direction = 'DESC'): self
    {
        $array = $this->getArray();
        \uasort($array, static function (
            FreeGiftProductDomainObject $a,
            FreeGiftProductDomainObject $b
        ) use ($direction) {
            if ($direction === 'DESC') {
                return (int)($a->getStillMissingAmount() < $b->getStillMissingAmount());
            }

            return (int)($a->getStillMissingAmount() > $b->getStillMissingAmount());
        });
        $this->exchangeArray($array);

        return $this;
    }

    /**
     * @since 5.4.0
     */
    public function sortByAvailability(bool $isAvailableFirst = true): self
    {
        $available    = [];
        $notAvailable = [];
        foreach ($this->getArray() as $index => $freeGiftProduct) {
            if ($freeGiftProduct->getStillMissingAmount() > 0.00) {
                $notAvailable[$index] = $freeGiftProduct;
            } else {
                $available[$index] = $freeGiftProduct;
            }
        }
        if ($isAvailableFirst) {
            $this->exchangeArray(\array_merge($available, $notAvailable));
        } else {
            $this->exchangeArray(\array_merge($notAvailable, $available));
        }

        return $this;
    }

    /**
     * Appends the value
     *
     * @link https://php.net/manual/en/arrayobject.append.php
     * @param FreeGiftProductDomainObject $value <p>
     * The value being appended.
     * </p>
     * @return FreeGiftProductDomainObject
     * @comment Check if the value is an instance of FreeGiftProductDomainObject before appending it.
     */
    #[ReturnTypeWillChange] public function append(mixed $value): FreeGiftProductDomainObject
    {
        if ($value instanceof FreeGiftProductDomainObject) {
            parent::append($value);
        }

        return $value;
    }
}
