<?php

/**
 * Class Polynomial
 *
 * @filesource   Polynomial.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode;

class Polynomial
{
    /**
     * @var int[]
     */
    public array $num = [];

    /**
     * @var int[]
     */
    protected array $EXP_TABLE = [];

    /**
     * @var int[]
     */
    protected array $LOG_TABLE = [];

    /**
     * @param int[] $num
     */
    public function __construct(array $num = [1], int $shift = 0)
    {
        $this->setNum($num, $shift)->setTables();
    }

    /**
     * @param int[] $num
     */
    public function setNum(array $num, int $shift = 0): self
    {
        $offset   = 0;
        $numCount = \count($num);

        while ($offset < $numCount && $num[$offset] === 0) {
            $offset++;
        }

        $this->num = \array_fill(0, $numCount - $offset + $shift, 0);

        $i = 0;
        while ($i < $numCount - $offset) {
            $this->num[$i] = $num[$i + $offset];
            $i++;
        }

        return $this;
    }

    protected function setTables(): void
    {
        $this->LOG_TABLE = \array_fill(0, 256, 0);
        $this->EXP_TABLE = $this->LOG_TABLE;

        $i = 0;
        while ($i < 8) {
            $this->EXP_TABLE[$i] = 1 << $i;
            $i++;
        }

        $i = 8;
        while ($i < 256) {
            $this->EXP_TABLE[$i] = $this->EXP_TABLE[$i - 4]
                ^ $this->EXP_TABLE[$i - 5]
                ^ $this->EXP_TABLE[$i - 6]
                ^ $this->EXP_TABLE[$i - 8];
            $i++;
        }

        $i = 0;
        while ($i < 255) {
            $this->LOG_TABLE[$this->EXP_TABLE[$i]] = $i;
            $i++;
        }
    }

    /**
     * @param int[] $e
     * @throws QRCodeException
     */
    public function multiply(array $e): void
    {
        $n = \array_fill(0, \count($this->num) + \count($e) - 1, 0);
        foreach ($this->num as $i => $vi) {
            foreach ($e as $j => $vj) {
                $n[$i + $j] ^= $this->gexp($this->glog($vi) + $this->glog($vj));
            }
        }

        $this->setNum($n);
    }

    /**
     * @param int[] $e
     * @throws QRCodeException
     */
    public function mod(array $e): void
    {
        $n = $this->num;
        if (\count($n) - \count($e) < 0) {
            return;
        }

        $ratio = $this->glog($n[0]) - $this->glog($e[0]);
        foreach ($e as $i => $v) {
            $n[$i] ^= $this->gexp($this->glog($v) + $ratio);
        }

        $this->setNum($n)->mod($e);
    }

    /**
     * @throws QRCodeException
     */
    public function glog(int $n): int
    {
        if ($n < 1) {
            throw new QRCodeException('log(' . $n . ')');
        }

        return $this->LOG_TABLE[$n];
    }

    public function gexp(int $n): int
    {
        if ($n < 0) {
            $n += 255;
        } elseif ($n >= 256) {
            $n -= 255;
        }

        return $this->EXP_TABLE[$n];
    }
}
