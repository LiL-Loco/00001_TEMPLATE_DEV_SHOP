<?php

declare(strict_types=1);

namespace Minify;

use Minify_CacheInterface;

class Config
{
    public bool $enableBuilder = false;

    public bool $enableStatic = false;

    public bool $concatOnly = false;

    public string $builderPassword = 'admin';

    /**
     * @var bool|object
     */
    public $errorLogger = false;

    public bool $allowDebugFlag = false;

    /**
     * @var string|Minify_CacheInterface
     */
    public $cachePath = '';

    /**
     * @var string
     */
    public string $documentRoot = '';

    /**
     * @var bool
     */
    public bool $cacheFileLocking = true;

    /**
     * @var array
     */
    public array $serveOptions = [];

    /**
     * @var array
     */
    public array $symlinks = [];

    /**
     * @var int
     */
    public int $uploaderHoursBehind = 0;

    /**
     * @var array
     */
    public array $envArgs = [];

    /**
     * @var callable[]
     */
    public array $factories = [];
}
