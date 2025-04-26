<?php

/**
 * @package   Functional-php
 * @author    Lars Strojny <lstrojny@php.net>
 * @copyright 2011-2021 Lars Strojny
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/lstrojny/functional-php
 */

namespace Functional;

use ErrorException;

/**
 * Takes a function and returns a new function that wraps the callback and suppresses the PHP error
 *
 * @param callable $callback
 * @return mixed
 * @no-named-arguments
 * @throws ErrorException Throws exception if PHP error happened
 */
function suppress_error(callable $callback)
{
    return static function (...$arguments) use ($callback) {
        try {
            \set_error_handler(const_function(null));

            return $callback(...$arguments);
        } finally {
            \restore_error_handler();
        }
    };
}
