<?php

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($containerBuilder);

/**
 * Create app with DI
 *
 * @noinspection PhpUnhandledExceptionInspection
 */
$app = Bridge::create($containerBuilder->build());

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

$app->run();
