<?php

/**
 * @package   Functional-php
 * @author    Lars Strojny <lstrojny@php.net>
 * @copyright 2011-2021 Lars Strojny
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/lstrojny/functional-php
 */

namespace Functional\Sequences;

use Functional\Exceptions\InvalidArgumentException;
use Iterator;

/** @internal */
class ExponentialSequence implements Iterator
{
    /** @var int */
    private $start;

    /** @var int */
    private $percentage;

    /** @var int */
    private $value;

    /** @var int */
    private $times;

    public function __construct($start, $percentage)
    {
        InvalidArgumentException::assertIntegerGreaterThanOrEqual($start, 1, __METHOD__, 1);
        InvalidArgumentException::assertIntegerGreaterThanOrEqual($percentage, 1, __METHOD__, 2);
        InvalidArgumentException::assertIntegerLessThanOrEqual($percentage, 100, __METHOD__, 2);

        $this->start      = $start;
        $this->percentage = $percentage;
    }

    public function current(): mixed
    {
        return $this->value;
    }

    public function next(): void
    {
        $this->value = (int)\round(($this->start * (1 + $this->percentage / 100)) ** $this->times);
        $this->times++;
    }

    public function key(): mixed
    {
        return null;
    }

    public function valid(): bool
    {
        return true;
    }

    public function rewind(): void
    {
        $this->times = 1;
        $this->value = $this->start;
    }
}
