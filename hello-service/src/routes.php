<?php

/** @noinspection PhpUnused */
declare(strict_types=1);

use App\Controller\HelloController;
use App\Controller\TestController;
use Slim\App;

return function (App $app) {
    $app->get('/', [HelloController::class, 'hello']);
    $app->get('/test', [TestController::class, 'test']);
};
