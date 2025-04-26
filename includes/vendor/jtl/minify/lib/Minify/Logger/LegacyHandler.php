<?php

declare(strict_types=1);

namespace Minify\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class LegacyHandler extends AbstractProcessingHandler
{
    private $obj;

    public function __construct($obj)
    {
        if (!\is_callable([$obj, 'log'])) {
            throw new \InvalidArgumentException('$obj must have a public log() method');
        }
        $this->obj = $obj;
        parent::__construct();
    }

    protected function write(LogRecord $record): void
    {
        $this->obj->log((string)$record['formatted']);
    }
}
