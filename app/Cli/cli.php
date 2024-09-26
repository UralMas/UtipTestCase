<?php

ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '14400');
ini_set('max_input_time', '14400');

use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Cli\Console\Exception as PhalconException;
use Phalcon\Di\FactoryDefault\Cli as CliDI;

const BASE_PATH = __DIR__ . '/../../';
const APP_PATH = BASE_PATH . 'app';
const CLI_PATH = __DIR__;

/**
 * Создание фабрики сервисов
 */
$di = new CliDI();

/**
 * Автолоад классов
 */
include CLI_PATH . '/config/loader.php';

/**
 * Регистрация сервисов
 */
include CLI_PATH . '/config/services.php';

/**
 * Создание консольного приложения
 */
$console = new ConsoleApp();
$console->setDI($di);

/**
 * Обработка передаваемых аргументов
 */
$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    /**
     * Выполнение запроса согласно аргументам
     */
    $console->handle($arguments);
} catch (PhalconException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}