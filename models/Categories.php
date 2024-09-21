<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

/**
 * Категории постов
 */
class Categories extends ModelBase
{

    /**
     * Наименование
     */
    public string $name;

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
