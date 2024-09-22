<?php

declare(strict_types=1);

namespace UtipTestCase\Controllers;

use Exception;
use UtipTestCase\Libraries\TokensHelper;
use UtipTestCase\Libraries\UsersHelper;
use UtipTestCase\Models\Tokens;
use UtipTestCase\Models\Users;

/**
 * Контроллер авторизации и регистрации
 */
class AuthController extends PostsController
{

    /**
     * Получение токена авторизации
     *
     * @throws Exception
     */
    public function auth(): array
    {
        /**
         * Получение и очистка данных из POST-запроса
         */
        $login = $this->request->getPost('login', 'string', '');
        $password = $this->request->getPost('password', 'string', '');

        /**
         * Проверка полученных данных
         */
        if (empty($login) || empty($password)) {
            throw new Exception('Не указан логин или пароль', 400);
        }

        /**
         * Поиск пользователя по логину и паролю
         */
        $user = Users::findFirst([
            'conditions' => 'login = :login:',
            'bind' => [
                'login' => $login
            ]
        ]);

        if (! $user) {
            throw new Exception('Неправильный логин или пароль', 400);
        }

        /** @var Users $user */

        /**
         * Проверка на статус пользователя
         */
        if ($user->state == Users::STATE_DELETED) {
            throw new Exception('Вам запрещён доступ', 403);
        }

        /**
         * Проверка пароля
         */
        if (! $this->security->checkHash($password, $user->password)) {
            throw new Exception('Неправильный логин или пароль', 400);
        }

        /**
         * Получение токена авторизации
         */
        $tokenEntry = Tokens::findFirst([
            'conditions' => "user_id = {$user->id}",
        ]);

        if (! $tokenEntry) {
            $token = TokensHelper::generateToken($user->id);

            $tokenEntry = new Tokens([
                'user_id' => $user->id,
                'token' => $token
            ]);
        } else {
            /** @var Tokens $tokenEntry */
            $token = $tokenEntry->token;
        }

        /**
         * Генерация токена или его обновление
         */
        if (! $tokenEntry->save()) {
            foreach ($tokenEntry->getMessages() as $message) {
                throw new Exception($message->getMessage(), 400);
            }
        }

        return compact('token');
    }

    /**
     * Регистрация пользователя
     *
     * @throws Exception
     */
    public function registration(): array
    {
        /**
         * Получение и очистка данных из POST-запроса
         */
        $login = $this->request->getPost('login', 'string', '');
        $password = $this->request->getPost('password', 'string', '');
        $groupId = $this->request->getPost('group_id', 'int', 2);

        return [
            'user_id' => UsersHelper::registerUser($login, $password, $groupId)
        ];
    }

}

