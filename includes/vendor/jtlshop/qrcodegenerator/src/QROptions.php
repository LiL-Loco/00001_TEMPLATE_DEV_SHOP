<?php

/**
 * Class QROptions
 *
 * @filesource   QROptions.php
 * @created      08.12.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode;

class QROptions
{
    public int $errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_M;

    public ?int $typeNumber = null;
}
