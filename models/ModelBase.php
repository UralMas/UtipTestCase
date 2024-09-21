<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * Базовый класс моделей
 * Т.к. все модели, относящиеся к постам, имеют по 4 одинаковых поля - вынес общий код для них в базовый класс
 */
class ModelBase extends Model
{

    /**
     * Статусы
     */
    const STATE_NOT_ACTIVE = 0;
    const STATE_ACTIVE = 1;

    /**
     * ID записи
     */
    public int $id;

    /**
     * Дата создания
     */
    public string $created_at;

    /**
     * Дата обновления
     */
    public string $updated_at;

    /**
     * Статус записи
     */
    public int $state;

    /**
     * Инициализация модели
     */
    public function initialize(): void
    {
        /**
         * Допускается пустое поле
         */
        $this->allowEmptyStringValues(['updated_at']);

        /**
         * Обновление только изменённых полей
         */
        $this->useDynamicUpdate(true);

        /**
         * Пропуск полей при редактировании/создании
         */
        $this->skipAttributesOnCreate(['updated_at']);
        $this->skipAttributesOnUpdate(['created_at']);

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

        /**
         * Автоматическая установка даты обновления
         */
        $this->addBehavior(new Timestampable(
            [
                'beforeUpdate' => [
                    'field' => 'updated_at',
                    'format' => 'Y-m-d H:i:s'
                ]
            ]
        ));
    }
}