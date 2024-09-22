<?php

use Phalcon\Config\Config;

/**
 * Конфиг проекта
 */
return new Config([
    // Настройки БД
    'database' => [
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'utip',
        'charset'     => 'utf8',
    ],
    // Настройки приложения
    'application' => [
        'tokenLifetime' => 86400, // Время жизни токена авторизации
        'imagesFolder'  => APP_PATH . '/images/', // Папка для сохранения загружаемых изображений
        'imagesUrl'     => 'https://utip.ru/images/', // Путь для формирования ссылки на изображение
    ]
]);
