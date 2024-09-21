<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

error_reporting(E_ALL);

const APP_PATH = __DIR__;

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    $app = new Micro($di);

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Handle the request
     */
    $app->handle(
        $_SERVER['REQUEST_URI']
    );
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
}
