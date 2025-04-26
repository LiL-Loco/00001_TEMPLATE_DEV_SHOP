<?php

/**
 * Interface QROutputInterface,
 *
 * @filesource   QROutputInterface.php
 * @created      02.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Output;

interface QROutputInterface
{
    public function dump(): string;

    /**
     * @param array<int, int[]> $matrix
     * @throws QRCodeOutputException
     */
    public function setMatrix(array $matrix): void;
}
