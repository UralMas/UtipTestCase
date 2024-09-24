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
        'tokenLifetime'         => 24 * 60 * 60, // Время жизни токена авторизации
        'imagesFolder'          => BASE_PATH . '/images/', // Папка для сохранения загружаемых изображений
        'imagesUrl'             => 'https://utip.ru/images/', // Путь для формирования ссылки на изображение
        'imagesAllowedTypes'    => 'image/gif;image/jpeg;image/png', // Разрешённые типы изображений, которые можно закачивать
        'imagesMaxSize'         => 10 * 1024 * 1024, // максимально разрешённый размер изображений, который можно закачивать
    ]
]);
