<?php

use Phalcon\Autoload\Loader;

/** @var Phalcon\Di\Di $di */
$config = $di->getConfig();

$loader = new Loader();

/**
 * Регистрация файлов классов через папки
 */
$loader->setDirectories(
    [
        $config->application->controllersDir,
        $config->application->modelsDir
    ]
)->register();
