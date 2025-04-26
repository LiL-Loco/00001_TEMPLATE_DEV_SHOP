<?php

/**
 * Interface QRDataInterface
 *
 * @filesource   QRDataInterface.php
 * @created      01.12.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Data;

use qrcodegenerator\QRCode\BitBuffer;

/**
 * @property string $data
 * @property int    $dataLength
 * @property int    $mode
 */
interface QRDataInterface
{
    /**
     * @param BitBuffer $buffer
     */
    public function write(BitBuffer $buffer): void;

    /**
     * @throws QRCodeDataException
     */
    public function getLengthInBits(int $type): int;
}
