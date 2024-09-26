<?php

declare(strict_types=1);

namespace UtipTestCase\Cli\Tasks;

use Exception;
use Phalcon\Cli\Task;
use UtipTestCase\Libraries\UsersHelper;
use UtipTestCase\Models\Users;

/**
 * Класс, отвечающий за таски, связанные с пользователями
 */
class UsersTask extends Task
{

    /**
     * Регистрация пользователя
     * @throws Exception
     */
    public function registrationAction(string $login, string $password, int $groupId = 2): void
    {
        /**
         * Проверка переданных параметров
         */
        if (empty($login) || empty($password) || ($groupId != 0 && ! in_array($groupId, [Users::GROUP_ADMIN, Users::GROUP_AUTHOR]))) {
            throw new Exception('Переданы неверные данные');
        }

        /**
         * Сама регистрация
         */
        UsersHelper::registerUser($login, $password, $groupId != 0 ? $groupId : 2);

        echo 'Пользователь успешно зарегистрирован' . PHP_EOL;
    }
}