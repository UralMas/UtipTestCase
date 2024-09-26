<?php

use Phalcon\Config\Config;

/**
 * Подключение библиотек
 */
include_once BASE_PATH . '/vendor/autoload.php';

/**
 * Регистрация параметров из файла .env
 */
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

/**
 * Конфиг проекта
 */
return new Config([
    // Настройки БД
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => $_ENV['DB_HOST'],
        'username'    => $_ENV['DB_USERNAME'],
        'password'    => $_ENV['DB_PASSWORD'],
        'dbname'      => $_ENV['DB_DBNAME'],
        'charset'     => 'utf8',
    ],
    // Настройки приложения
    'application' => [
        'isDev'                 => $_ENV['ENVIRONMENT'] === 'development', // Является ли окружение dev
        'cacheLifetime'         => 7200, // Время жизни кэша запросов БД
        'tokenLifetime'         => 24 * 60 * 60, // Время жизни токена авторизации
        'logsDir'               => BASE_PATH . '/logs/', // Папка для сохранения логов
        'migrationsDir'         => BASE_PATH . '/migrations', // Папка для сохранения миграций
        'imagesDir'             => BASE_PATH . '/images/', // Папка для сохранения загружаемых изображений
        'imagesUrl'             => 'https://utip.ru/images/', // Путь для формирования ссылки на изображение
        'imagesAllowedTypes'    => 'image/gif;image/jpeg;image/png', // Разрешённые типы изображений, которые можно закачивать
        'imagesMaxSize'         => 10 * 1024 * 1024, // максимально разрешённый размер изображений, который можно закачивать
    ]
]);
