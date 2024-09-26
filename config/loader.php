<?php

use Phalcon\Autoload\Loader;

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
