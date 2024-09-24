<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

/**
 * Класс-хранилище данных пользователя, полученные из токена авторизации
 */
class User
{
    private int $id;
    private int $group_id;

    public function __construct(int $id, int $group_id) {
        $this->id = $id;
        $this->group_id = $group_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }
}