<?php

/**
 * Class QRDataAbstract
 *
 * @filesource   QRDataAbstract.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Data;

abstract class QRDataAbstract implements QRDataInterface
{
    public int $dataLength;

    /**
     * @var int[]
     */
    protected array $lengthBits = [0, 0, 0];

    public function __construct(public string $data)
    {
        $this->dataLength = \strlen($this->data);
    }

    /**
     * @throws QRCodeDataException
     * @see QRCode::createData()
     * @codeCoverageIgnore
     */
    public function getLengthInBits(int $type): int
    {
        return match (true) {
            $type >= 1 && $type <= 9 => $this->lengthBits[0],
            $type <= 26              => $this->lengthBits[1],
            $type <= 40              => $this->lengthBits[2],
            default                  => throw new QRCodeDataException('$type: ' . $type),
        };
    }
}
