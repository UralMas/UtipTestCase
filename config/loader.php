<?php

use Phalcon\Autoload\Loader;

/** @var Phalcon\Di\Di $di */
$config = $di->getConfig();

$loader = new Loader();

/**
 * Регистрация файлов классов через папки
 */
$loader->setNamespaces(
    [
        'UtipTestCase\Controllers'  => APP_PATH . '/Controllers/',
        'UtipTestCase\Libraries'	=> APP_PATH . '/Libraries/',
        'UtipTestCase\Models'		=> APP_PATH . '/Models/'
    ]
)->register();
