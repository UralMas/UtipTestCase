<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

error_reporting(E_ALL);

const APP_PATH = __DIR__;

try {
    /**
     * Создание фабрики сервисов
     */
    $di = new FactoryDefault();

    /**
     * Регистрация сервисов
     */
    include APP_PATH . '/config/services.php';

    /**
     * Чтение конфигурации
     */
    $config = $di->getConfig();

    /**
     * Автолоад файлов
     */
    include APP_PATH . '/config/loader.php';

    $app = new Micro($di);

    /**
     * Регистрация роутинга
     */
    include APP_PATH . '/config/router.php';

    /**
     * Обработка запроса
     */
    $app->handle(
        $_SERVER['REQUEST_URI']
    );
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
}
