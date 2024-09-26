<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;

error_reporting(E_ALL);

const BASE_PATH = __DIR__;
const APP_PATH = BASE_PATH . '/app';

try {
    /**
     * Создание фабрики сервисов
     */
    $di = new FactoryDefault();

    /**
     * Регистрация сервисов
     */
    include BASE_PATH . '/config/services.php';

    /**
     * Чтение конфигурации
     */
    $config = $di->getConfig();

    /**
     * Автолоад классов
     */
    include BASE_PATH . '/config/loader.php';

    $app = new Micro($di);

    /**
     * Регистрация роутинга
     */
    include BASE_PATH . '/config/router.php';

    /**
     * Обработка неверного запроса
     */
    $app->notFound(
        function () use ($app) {
            if ($app->request->isGet()) {
                throw new Exception('Not Found', 404);
            } else {
                throw new Exception('Method Not Allowed', 405);
            }
        }
    );

    /**
     * Обработка полученного результата и выдача в JSON
     */
    $app->after(
        function () use ($app) {
            $result = $app->getReturnedValue();
            $result['success'] = true;

            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        }
    );

    /**
     * Обработка запроса
     */
    $app->handle(
        $_SERVER['REQUEST_URI']
    );
} catch (Exception $e) {
    /**
     * Обработка ошибок
     */
    $content = json_encode(
        [
            'success' => false,
            'message' => $e->getMessage()
        ],
        JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE
    );

    $response = new Response();

    $response->setStatusCode($e->getCode() != 0 ? $e->getCode() : 400);
    $response->setContent($content);
    $response->send();
}
