<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * Базовый класс моделей
 * Т.к. все модели, относящиеся к постам, имеют по 2 одинаковых поля - вынес общий код для них в базовый класс
 */
class ModelBase extends Model
{

    /**
     * ID записи
     */
    public int $id = 0;

    /**
     * Дата создания
     */
    public ?string $created_at = null;

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