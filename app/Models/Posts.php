<?php

namespace UtipTestCase\Models;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Callback as CallbackValidator;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\View\Simple;

/**
 * Посты
 *
 * @property Simple|Images[] $images
 * @method   Simple|Images[] getImages($parameters = null)
 */
class Posts extends ModelBase
{

    /**
     * ID связанной категории
     */
    public int $category_id = 0;

    /**
     * ID автора
     */
    public int $author_id = 0;

    /**
     * Заголовок
     */
    public ?string $title;

    /**
     * Текстовое содержимое
     */
    public ?string $content;

    /**
     * Дата изменения
     */
    public ?string $update_at = null;

    /**
     * Валидация данных
     */
    public function validation(): bool
    {
        $validator = new Validation();

        $validator->rules(
            "title",
            [
                new PresenceOf([
                    "message" => "Не указано название поста"
                ])
            ]
        );

        $validator->rules(
            "content",
            [
                new PresenceOf([
                    "message" => "Не указано тело поста"
                ])
            ]
        );

        $validator->rules(
            "category_id",
            [
                new PresenceOf([
                    "message" => "Не указан ID категории"
                ]),
                new CallbackValidator([
                    'message' => 'Указан неверный ID категории',
                    'callback' => function() {
                        return Categories::count("id = {$this->category_id}") != 0;
                    }
                ])
            ]
        );

        $validator->rules(
            "author_id",
            [
                new PresenceOf([
                    "message" => "Не указан ID автора"
                ]),
                new CallbackValidator([
                    'message' => 'Указан неверный ID автора',
                    'callback' => function() {
                        return Users::count("id = {$this->author_id}") != 0;
                    },
                    'allowEmpty' => true
                ])
            ]
        );

        return $this->validate($validator);
    }

    /**
     * Инициализация модели
     */
    public function initialize(): void
    {
        parent::initialize();

        /**
         * Возможные пустые поля (для отключения валидации)
         */
        $this->allowEmptyStringValues(['updated_at']);

        /**
         * Обновление в БД только измененных полей
         */
        $this->useDynamicUpdate(true);

        /**
         * Пропуск полей при создании
         */
        $this->skipAttributesOnCreate(['updated_at']);

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

        /**
         * Связь с Categories Users по типу "many to one"
         */
        $this->belongsTo(
            'category_id',
            Categories::class,
            'id',
            [
                'reusable' => true,
                'alias' => 'category',
            ]
        );

        /**
         * Связь с таблицей Users по типу "many to one"
         */
        $this->belongsTo(
            'author_id',
            Users::class,
            'id',
            [
                'reusable' => true,
                'alias' => 'author',
            ]
        );

        /**
         * Связь с таблицей Images по типу "one-to-many"
         */
        $this->hasMany(
            'id',
            Images::class,
            'post_id',
            [
                'reusable' => true,
                'alias' => 'images',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE, // Удаление связанных записей изображений при удалении записи поста
                ],
            ]
        );
    }

}
