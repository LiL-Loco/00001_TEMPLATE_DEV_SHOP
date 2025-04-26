<?php

/**
 *
 * @filesource   QRImageOptions.php
 * @created      08.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Output;

use qrcodegenerator\QRCode\QRCode;

/**
 * Class QRImageOptions
 */
class QRImageOptions
{
    public string $type = QRCode::OUTPUT_IMAGE_PNG;

    public bool $base64 = true;

    public string|null $cachefile = null;

    public int $pixelSize = 5;
    public int $marginSize = 5;

    // not supported by jpg
    public bool $transparent = true;

    public int $fgRed = 0;
    public int $fgGreen = 0;
    public int $fgBlue = 0;

    public int $bgRed = 255;
    public int $bgGreen = 255;
    public int $bgBlue = 255;

    public int $pngCompression = -1;
    public int $jpegQuality = 85;
}
