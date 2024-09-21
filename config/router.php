<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;
use UtipTestCase\Controllers as Controllers;

/**
 * Регистрация роутинга
 */

$launcher = new MicroCollection();

$launcher->setHandler(Controllers\IndexController::class, true)
    ->setPrefix('/api/v1')
    ->get(
        '/user/data',
        'userData',
        'launcherUserData'
    );

/** @var Phalcon\Mvc\Micro $app */
$app->mount($launcher);
