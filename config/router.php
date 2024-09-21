<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;
use UtipTestCase\Controllers as Controllers;

/**
 * Регистрация роутинга
 */

/** @var Phalcon\Mvc\Micro $app */

$auth = new MicroCollection();

$auth->setHandler(Controllers\AuthController::class, true)
    ->setPrefix('/api/v1')
    ->get(
        '/auth',
        'auth',
        'auth'
    )
    ->get(
        '/registration',
        'registration',
        'registration'
    );

$app->mount($auth);
