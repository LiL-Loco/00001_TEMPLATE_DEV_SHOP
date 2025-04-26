<?php

/**
 * Class QROutputAbstract
 *
 * @filesource   QROutputAbstract.php
 * @created      09.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Output;

abstract class QROutputAbstract implements QROutputInterface
{
    /**
     * @var array<int, int[]>
     */
    protected array $matrix;

    protected int $pixelCount;

    /**
     * @inheritdoc
     */
    public function setMatrix(array $matrix): void
    {
        $this->pixelCount = \count($matrix);
        // specify valid range?
        if (
            $this->pixelCount < 2
            || !isset($matrix[$this->pixelCount - 1])
            || $this->pixelCount !== \count($matrix[$this->pixelCount - 1])
        ) {
            throw new QRCodeOutputException('Invalid matrix!');
        }
        $this->matrix = $matrix;
    }
}
