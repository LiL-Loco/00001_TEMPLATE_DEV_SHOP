<?php

/**
 * Class Byte
 *
 * @filesource   Byte.php
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

class Byte extends QRDataAbstract
{
    public int $mode = QRConst::MODE_BYTE;

    /**
     * @var int[]
     */
    protected array $lengthBits = [8, 16, 16];

    public function write(BitBuffer $buffer): void
    {
        $i = 0;
        while ($i < $this->dataLength) {
            $buffer->put(\ord($this->data[$i]), 8);
            $i++;
        }
    }
}
