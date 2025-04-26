<?php

/**
 * Class QRString
 *
 * @filesource   QRString.php
 * @created      05.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode\Output;

use qrcodegenerator\QRCode\QRCode;

class QRString extends QROutputAbstract
{
    /**
     * @throws QRCodeOutputException
     */
    public function __construct(protected QRStringOptions $options = new QRStringOptions())
    {
        if (
            !\in_array(
                $this->options->type,
                [QRCode::OUTPUT_STRING_TEXT, QRCode::OUTPUT_STRING_JSON, QRCode::OUTPUT_STRING_HTML],
                true
            )
        ) {
            throw new QRCodeOutputException('Invalid string output type!');
        }
    }

    public function dump(): string
    {
        return match ($this->options->type) {
            QRCode::OUTPUT_STRING_JSON => \json_encode($this->matrix, \JSON_THROW_ON_ERROR) ?: '',
            QRCode::OUTPUT_STRING_TEXT => $this->toString(),
            default                    => $this->toHTML(),
        };
    }

    protected function toString(): string
    {
        $str = '';
        foreach ($this->matrix as $row) {
            foreach ($row as $col) {
                $str .= $col
                    ? $this->options->textDark
                    : $this->options->textLight;
            }
            $str .= $this->options->eol;
        }

        return $str;
    }

    protected function toHTML(): string
    {
        $html = '';
        foreach ($this->matrix as $row) {
            // in order to not bloat the output too much, we use the shortest possible valid HTML tags
            $html .= '<' . $this->options->htmlRowTag . '>';
            foreach ($row as $col) {
                $tag = $col
                    ? 'b'  // dark
                    : 'i'; // light

                $html .= '<' . $tag . '></' . $tag . '>';
            }
            if (!$this->options->htmlOmitEndTag) {
                $html .= '</' . $this->options->htmlRowTag . '>';
            }
            $html .= $this->options->eol;
        }

        return $html;
    }
}
