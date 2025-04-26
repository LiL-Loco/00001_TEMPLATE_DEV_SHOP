<?php

/**
 * Sets up MinApp controller and serves files
 *
 * DO NOT EDIT! Configure this utility via config.php and groupsConfig.php
 *
 * @package Minify
 */

declare(strict_types=1);

/* @var \Minify\App $app */
$app = (require __DIR__ . '/bootstrap.php');
$app->runServer();
