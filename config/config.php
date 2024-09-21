<?php

use Phalcon\Config\Config;

/**
 * Конфиг проекта
 */
return new Config([
    'database' => [
        // Настройки БД
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'utip',
        'charset'     => 'utf8',
    ],
    'application' => [
        // Папки приложения
        'controllersDir' => APP_PATH . '/controllers/',
        'librariesDir'   => APP_PATH . '/libraries/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'modelsDir'      => APP_PATH . '/models/',

        // Настройки приложения
        'tokenLifetime' => 86400, // Время жизни токена
    ]
]);
