<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\View\Simple;

/**
 * Категории постов
 *
 * @property Simple|Posts[] $posts
 * @method   Simple|Posts[] getProducts($parameters = null)
 *
 * @property Simple|Images[] $images
 * @method   Simple|Images[] getImages($parameters = null)
 */
class Categories extends ModelPostable
{

    /**
     * Наименование
     */
    public ?string $name;

    /**
     * Валидация данных
     */
    public function validation(): bool
    {
        $validator = new Validation();

        $validator->rules(
            "name",
            [
                new PresenceOf([
                    "message" => "Не указано название категории"
                ]),
                new UniquenessValidator([
                    "message" => "Такое название уже используется. Пожалуйста, укажите другое.",
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
         * Связь с таблицей Posts по типу "one-to-many"
         */
        $this->hasMany(
            'id',
            Posts::class,
            'category_id',
            [
                'reusable' => true,
                'alias'    => 'posts',
            ]
        );

        /**
         * Связь с таблицей Images по типу "many-to-many"
         */
        $this->hasManyToMany(
            'id',
            Posts::class,
            'category_id',
            'id',
            Images::class,
            'post_id',
            [
                'reusable' => true,
                'alias'    => 'images',
            ]
        );
    }
}
