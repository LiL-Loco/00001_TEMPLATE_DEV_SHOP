<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use qrcodegenerator\QRCode\Output\QROutputAbstract;
use qrcodegenerator\QRCode\QRCode;

class MyCustomOutput extends QROutputAbstract
{
    public function dump(): string
    {
        $output = '';

        for ($row = 0; $row < $this->pixelCount; $row++) {
            for ($col = 0; $col < $this->pixelCount; $col++) {
                $output .= (string)(int)$this->matrix[$row][$col];
            }
        }

        return $output;
    }

}

$starttime = microtime(true);

echo (new QRCode('otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', new MyCustomOutput()))->output();

echo PHP_EOL . 'QRCode: ' . round((microtime(true) - $starttime), 5);
