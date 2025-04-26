<?php

/**
 * Class AlphaNum
 *
 * @filesource   AlphaNum.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Data;

use qrcodegenerator\QRCode\BitBuffer;
use qrcodegenerator\QRCode\QRConst;

class AlphaNum extends QRDataAbstract
{
    /**
     * @var array<int, string>
     */
    public static array $CHAR_MAP = [
        36 => ' ',
        37 => '$',
        38 => '%',
        39 => '*',
        40 => '+',
        41 => '-',
        42 => '.',
        43 => '/',
        44 => ':',
    ];

    public int $mode = QRConst::MODE_ALPHANUM;

    /**
     * @var int[]
     */
    protected array $lengthBits = [9, 11, 13];

    public function write(BitBuffer $buffer): void
    {
        $i = 0;
        while ($i + 1 < $this->dataLength) {
            $buffer->put(self::getCharCode($this->data[$i]) * 45 + self::getCharCode($this->data[$i + 1]), 11);
            $i += 2;
        }
        if ($i < $this->dataLength) {
            $buffer->put(self::getCharCode($this->data[$i]), 6);
        }
    }

    /**
     * @throws QRCodeDataException
     */
    private static function getCharCode(string $string): int
    {
        $c = \ord($string);

        switch (true) {
            case \ord('0') <= $c && $c <= \ord('9'):
                return $c - \ord('0');
            case \ord('A') <= $c && $c <= \ord('Z'):
                return $c - \ord('A') + 10;
            default:
                foreach (self::$CHAR_MAP as $i => $char) {
                    if (\ord($char) === $c) {
                        return $i;
                    }
                }
        }

        throw new QRCodeDataException('illegal char: ' . $c);
    }
}
