<?php

namespace UtipTestCase\Models;

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

}
