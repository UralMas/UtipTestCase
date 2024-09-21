<?php

declare(strict_types=1);

namespace UtipTestCase\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер постов и всего, что с ними связано
 */
class PostsController extends Controller
{

    /**
     * Проверка доступа
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher): bool
    {
        if ($this->user->isLogin() === false) {
            $dispatcher->forward([
                'controller' => 'login',
                'action' => 'index'
            ]);

            return false;
        }

        return true;
    }
}
