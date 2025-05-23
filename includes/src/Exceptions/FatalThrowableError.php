<?php

declare(strict_types=1);

namespace JTL\Exceptions;

/**
 * Class FatalThrowableError
 * @package JTL\Exceptions
 * @author Nicolas Grekas <p@tchwork.com>
 */
class FatalThrowableError extends FatalErrorException
{
    private string $originalClassName;

    public function __construct(\Throwable $e)
    {
        $this->originalClassName = \get_class($e);

        if ($e instanceof \ParseError) {
            $severity = \E_PARSE;
        } elseif ($e instanceof \TypeError) {
            $severity = \E_RECOVERABLE_ERROR;
        } else {
            $severity = \E_ERROR;
        }

        \ErrorException::__construct(
            $e->getMessage(),
            $e->getCode(),
            $severity,
            $e->getFile(),
            $e->getLine(),
            $e->getPrevious()
        );

        $this->setTrace($e->getTrace());
    }

    public function getOriginalClassName(): string
    {
        return $this->originalClassName;
    }
}
