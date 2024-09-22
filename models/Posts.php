<?php

namespace UtipTestCase\Models;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Callback as CallbackValidator;
use Phalcon\Filter\Validation\Validator\PresenceOf;

/**
 * Посты
 */
class Posts extends ModelBase
{

    /**
     * ID связанной категории
     */
    public int $category_id;

    /**
     * ID автора
     */
    public int $author_id;

    /**
     * Заголовок
     */
    public string $title;

    /**
     * Текстовое содержимое
     */
    public string $content;

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
                    },
                    'allowEmpty' => true
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
            ]
        );
    }

}
