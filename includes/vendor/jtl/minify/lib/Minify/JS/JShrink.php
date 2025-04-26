<?php

declare(strict_types=1);

/**
 * Class Minify\JS\JShrink
 *
 * @package Minify
 */

namespace Minify\JS;

use JShrink\Minifier;

/**
 * Wrapper to Javascript Minifier built in PHP http://www.tedivm.com
 *
 * @package Minify
 * @author  Elan Ruusamäe <glen@pld-linux.org>
 * @link    https://github.com/tedious/JShrink
 *
 */
class JShrink
{
    /**
     * Contains the default options for minification. This array is merged with
     * the one passed in by the user to create the request specific set of
     * options (stored in the $options attribute).
     *
     * @var string[]
     */
    protected static $defaultOptions = ['flaggedComments' => true];

    /**
     * Takes a string containing javascript and removes unneeded characters in
     * order to shrink the code without altering it's functionality.
     *
     * @param string $js The raw javascript to be minified
     * @param array  $options Various runtime options in an associative array
     *
     * @return string
     * @see Minifier::minify()
     */
    public static function minify($js, array $options = [])
    {
        $options = \array_merge(
            self::$defaultOptions,
            $options
        );

        return Minifier::minify($js, $options);
    }
}
