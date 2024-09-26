<?php

declare(strict_types=1);

/** @var Phalcon\Di\Di $di */

use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Logger;
use Phalcon\Storage\SerializerFactory;

/**
 * Регистрация конфига
 */
$di->setShared('config', function () {
    return include BASE_PATH . "/config/config.php";
});

/**
 * Подключение к БД и регистрация как сервиса
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    return new Phalcon\Db\Adapter\Pdo\Mysql($params);
});

/**
 * Настройка кэширования запросов БД
 */
$di->setShared(
    'modelsCache',
    function () {
        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new AdapterFactory($serializerFactory);

        /**
         * Кэширование средствами PHP
         */
        $options = [
            'defaultSerializer' => 'Php',
            'lifetime'          => $this->getConfig()->application->cacheLifetime
        ];

        $adapter = $adapterFactory->newInstance('apcu', $options);

        return new Cache($adapter);
    }
);

/**
 * Регистрация сервиса безопасности
 */
$di->setShared('security', function(){
    $security = new Phalcon\Encryption\Security();
    $security->setWorkFactor(13);
    $security->setDefaultHash(Phalcon\Encryption\Security::CRYPT_ARGON2ID);

    return $security;
});

/**
 * Регистрация сервиса логирования запросов на изменение
 * Только если тип окружения установлен в "development"
 */
if ($di->getConfig()->application->isDev) {
    $di->setShared('logger', function () {
        return new Logger(
            'messages',
            [
                'main' => new Stream($this->getConfig()->application->logsDir . 'changes.log')
            ]
        );
    });
}
