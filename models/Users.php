<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Mvc\Model;

/**
 * Токены авторизации
 */
class Users extends Model
{

    /**
     * Статусы
     */
    const STATE_DELETED = 0;
    const STATE_ACTIVE = 1;

    /**
     * Группы пользователей
     */
    const GROUP_ADMIN = 0; // Администратор - может управлять всем
    const GROUP_AUTHOR = 1; // Автор - может управлять только своими постами

    /**
     * ID пользователя
     */
    public int $id;

    /**
     * Группа пользователя
     */
    public int $group_id;

    /**
     * Логин
     */
    public string $login;

    /**
     * Пароль (зашифрованный)
     */
    public string $password;

    /**
     * Статус
     */
    public int $state;

}
