<?php

declare(strict_types=1);

/** @var Phalcon\Di\Di $di */

/**
 * Регистрация конфига
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
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
 * Регистрация сервиса безопасности
 */
$di->setShared('security', function(){
    $security = new Phalcon\Encryption\Security();
    $security->setWorkFactor(13);
    $security->setDefaultHash(Phalcon\Encryption\Security::CRYPT_ARGON2ID);

    return $security;
});
