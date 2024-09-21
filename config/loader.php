<?php

use Phalcon\Autoload\Loader;

/** @var Phalcon\Di\Di $di */
$config = $di->getConfig();

$loader = new Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->setDirectories(
    [
        $config->application->controllersDir,
        $config->application->modelsDir
    ]
)->register();
