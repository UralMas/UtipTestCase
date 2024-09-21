<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * Токены авторизации
 */
class Tokens extends Model
{

    /**
     * ID записи
     */
    public int $id;

    /**
     * ID пользователя
     */
    public int $user_id;

    /**
     * Сам токен
     */
    public string $token;

    /**
     * Дата создания токена
     */
    public string $created_at;

    /**
     * Инициализация модели
     */
    public function initialize(): void
    {
        /**
         * Автоматическая установка даты создания
         */
        $this->addBehavior(new Timestampable(
            [
                'beforeCreate' => [
                    'field' => 'created_at',
                    'format' => 'Y-m-d H:i:s'
                ]
            ]
        ));
    }

}
