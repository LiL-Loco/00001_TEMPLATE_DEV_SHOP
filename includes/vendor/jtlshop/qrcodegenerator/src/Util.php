<?php

/**
 * Class Util
 *
 * @filesource   Util.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode;

class Util
{
    public static function isNumber(string $s): bool
    {
        $len = \strlen($s);
        $i   = 0;
        while ($i < $len) {
            $c = \ord($s[$i]);
            if (!(\ord('0') <= $c && $c <= \ord('9'))) {
                return false;
            }
            $i++;
        }

        return true;
    }

    public static function isAlphaNum(string $s): bool
    {
        $len = \strlen($s);
        $i   = 0;
        while ($i < $len) {
            $c = \ord($s[$i]);
            if (
                !\str_contains(' $%*+-./:', $s[$i])
                && !(\ord('0') <= $c && $c <= \ord('9'))
                && !(\ord('A') <= $c && $c <= \ord('Z'))
            ) {
                return false;
            }

            $i++;
        }

        return true;
    }

    public static function isKanji(string $s): bool
    {
        if (empty($s)) {
            return false;
        }
        $i   = 0;
        $len = \strlen($s);
        while ($i + 1 < $len) {
            $c = ((0xff & \ord($s[$i])) << 8) | (0xff & \ord($s[$i + 1]));
            if (!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)) {
                return false;
            }
            $i += 2;
        }

        return !($i < $len);
    }

    public static function getBCHTypeInfo(int $data): int
    {
        return (($data << 10) | self::getBCHT($data, 10, QRConst::G15)) ^ QRConst::G15_MASK;
    }

    public static function getBCHTypeNumber(int $data): int
    {
        return ($data << 12) | self::getBCHT($data, 12, QRConst::G18);
    }

    protected static function getBCHT(int $data, int $bits, int $mask): int
    {
        $d = $data << $bits;
        while (self::getBCHDigit($d) - self::getBCHDigit($mask) >= 0) {
            $d ^= ($mask << (self::getBCHDigit($d) - self::getBCHDigit($mask)));
        }

        return $d;
    }

    public static function getBCHDigit(int $data): int
    {
        $digit = 0;
        while ($data !== 0) {
            $digit++;
            $data >>= 1;
        }

        return $digit;
    }

    /**
     * @throws QRCodeException
     * @return array<int, array<int, int>>
     */
    public static function getRSBlocks(int $typeNumber, int $errorCorrectLevel): array
    {
        if (!\array_key_exists($errorCorrectLevel, QRConst::$RSBLOCK)) {
            throw new QRCodeException('$typeNumber: ' . $typeNumber . ' / $errorCorrectLevel: ' . $errorCorrectLevel);
        }
        $rsBlock = QRConst::$BLOCK_TABLE[($typeNumber - 1) * 4 + QRConst::$RSBLOCK[$errorCorrectLevel]];
        $list    = [];
        $length  = count($rsBlock) / 3;
        $j       = 0;
        $i       = 0;
        while ($i < $length) {
            while ($j < $rsBlock[$i * 3]) {
                $list[] = [$rsBlock[$i * 3 + 1], $rsBlock[$i * 3 + 2]];
                $j++;
            }
            $i++;
        }

        return $list;
    }

    /**
     * @throws QRCodeException
     */
    public static function getMaxLength(int $typeNumber, int $mode, int $ecLevel): int
    {
        if (!\array_key_exists($ecLevel, QRConst::$RSBLOCK)) {
            throw new QRCodeException('Invalid error correct level: ' . $ecLevel);
        }
        if (!\array_key_exists($mode, QRConst::$MODE)) {
            throw new QRCodeException('Invalid mode: ' . $mode);
        }

        return QRConst::$MAX_LENGTH[$typeNumber - 1][QRConst::$RSBLOCK[$ecLevel]][QRConst::$MODE[$mode]];
    }
}
