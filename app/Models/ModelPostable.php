<?php

namespace UtipTestCase\Models;

use Phalcon\Mvc\Model\Behavior\Timestampable;

class ModelPostable extends ModelBase
{

    /**
     * Дата создания
     */
    public ?string $created_at = null;

    /**
     * Инициализация модели
     */
    public function initialize(): void
    {
        parent::initialize();

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