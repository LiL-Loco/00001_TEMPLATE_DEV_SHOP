<?php

/**
 * Class Number
 *
 * @filesource   Number.php
 * @created      26.11.2015
 * @package      QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Data;

use qrcodegenerator\QRCode\BitBuffer;
use qrcodegenerator\QRCode\QRConst;

class Number extends QRDataAbstract
{
    public int $mode = QRConst::MODE_NUMBER;

    /**
     * @var int[]
     */
    protected array $lengthBits = [10, 12, 14];

    public function write(BitBuffer $buffer): void
    {
        $i = 0;
        while ($i + 2 < $this->dataLength) {
            $buffer->put(self::parseInt(\substr($this->data, $i, 3)), 10);
            $i += 3;
        }
        if ($i < $this->dataLength) {
            if ($this->dataLength - $i === 1) {
                $buffer->put(self::parseInt(\substr($this->data, $i, $i + 1)), 4);
            } elseif ($this->dataLength - $i === 2) {
                $buffer->put(self::parseInt(\substr($this->data, $i, $i + 2)), 7);
            }
        }
    }

    /**
     * @throws QRCodeDataException
     */
    private static function parseInt(string $string): int
    {
        $num = 0;
        $len = \strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $c    = \ord($string[$i]);
            $ord0 = \ord('0');
            if ($ord0 <= $c && $c <= \ord('9')) {
                $c -= $ord0;
            } else {
                throw new QRCodeDataException('illegal char: ' . $c);
            }
            $num = $num * 10 + $c;
        }

        return $num;
    }
}
