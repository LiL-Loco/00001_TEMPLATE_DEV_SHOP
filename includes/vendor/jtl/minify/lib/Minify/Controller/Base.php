<?php

/**
 * Class Minify_Controller_Base
 * @package Minify
 */

declare(strict_types=1);

use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Base class for Minify controller
 *
 * The controller class validates a request and uses it to create a configuration for Minify::serve().
 *
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 */
abstract class Minify_Controller_Base implements Minify_ControllerInterface
{
    protected Minify_Env $env;

    protected Minify_Source_Factory $sourceFactory;

    protected LoggerInterface $logger;

    public function __construct(Minify_Env $env, Minify_Source_Factory $sourceFactory, ?LoggerInterface $logger = null)
    {
        $this->env           = $env;
        $this->sourceFactory = $sourceFactory;
        if (!$logger) {
            $logger = new Logger('minify');
        }
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    abstract public function createConfiguration(array $options): Minify_ServeConfiguration;

    /**
     * @inheritdoc
     */
    public function getEnv(): Minify_Env
    {
        return $this->env;
    }
}
