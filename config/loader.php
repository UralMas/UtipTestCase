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
        'UtipTestCase\Controllers'  => $config->application->controllersDir,
        'UtipTestCase\Libraries'	=> $config->application->librariesDir,
        'UtipTestCase\Models'		=> $config->application->modelsDir
    ]
)->register();
