<?php

declare(strict_types=1);

interface Minify_ControllerInterface
{
    /**
     * Create controller sources and options for Minify::serve()
     *
     * @param array $options controller and Minify options
     * @return Minify_ServeConfiguration
     */
    public function createConfiguration(array $options): Minify_ServeConfiguration;

    /**
     * @return Minify_Env
     */
    public function getEnv(): Minify_Env;
}
