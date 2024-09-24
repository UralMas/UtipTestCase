<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

use Exception;
use UtipTestCase\Models\Users;

/**
 * Класс-помощник для работы с пользователями
 */
class UsersHelper
{
    /**
     * Регистрация пользователя
     *
     * @throws Exception
     */
    public static function registerUser(string $login, string $password, int $groupId = 2): int
    {
        /**
         * Проверка полученных данных
         */
        if (empty($login) || empty($password)) {
            throw new Exception('Не указан логин или пароль', 400);
        }

        $user = new Users([
            'login' => $login,
            'password' => $password,
            'group_id' => $groupId
        ]);

        if (! $user->save()) {
            throw new Exception($user->getMessages()[0]->getMessage(), 400);
        }

        return $user->id;
    }
}