<?php

/**
 * Class Kanji
 *
 * @filesource   Kanji.php
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

class Kanji extends QRDataAbstract
{
    public int $mode = QRConst::MODE_KANJI;

    /**
     * @var int[]
     */
    protected array $lengthBits = [8, 10, 12];

    /**
     * @throws QRCodeDataException
     */
    public function write(BitBuffer $buffer): void
    {
        $i = 0;
        while ($i + 1 < $this->dataLength) {
            $c = ((0xff & \ord($this->data[$i])) << 8) | (0xff & \ord($this->data[$i + 1]));

            if (0x8140 <= $c && $c <= 0x9FFC) {
                $c -= 0x8140;
            } elseif (0xE040 <= $c && $c <= 0xEBBF) {
                $c -= 0xC140;
            } else {
                throw new QRCodeDataException('illegal char at ' . ($i + 1) . ' (' . $c . ')');
            }

            $buffer->put((($c >> 8) & 0xff) * 0xC0 + ($c & 0xff), 13);
            $i += 2;
        }

        if ($i < $this->dataLength) {
            throw new QRCodeDataException('illegal char at ' . ($i + 1));
        }
    }
}
