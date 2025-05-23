<?php

/**
 * Class BitBuffer
 *
 * @filesource   BitBuffer.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode;

class BitBuffer
{
    /**
     * @var array<int, int>
     */
    public array $buffer = [];

    public int $length = 0;

    public function clear(): void
    {
        $this->buffer = [];
        $this->length = 0;
    }

    public function put(float|int $num, int $length): void
    {
        $i = 0;
        while ($i < $length) {
            $this->putBit((($num >> ($length - $i - 1)) & 1) === 1);
            $i++;
        }
    }

    public function putBit(bool $bit): void
    {
        $bufIndex = \floor($this->length / 8);
        if (\count($this->buffer) <= $bufIndex) {
            $this->buffer[] = 0;
        }
        if ($bit) {
            $this->buffer[(int)$bufIndex] |= (0x80 >> ($this->length % 8));
        }
        $this->length++;
    }
}
