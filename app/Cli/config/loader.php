<?php

include BASE_PATH . '/config/loader.php';

/** @var Phalcon\Autoload\Loader $loader */

/**
 * Регистрация файлов классов тасков
 */
$loader->setNamespaces(
    [
        'UtipTestCase\Cli\Tasks' => CLI_PATH . '/Tasks/'
    ],
    true
)->register();
