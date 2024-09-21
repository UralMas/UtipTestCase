<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

/**
 * Изображения, связанные с постами
 */
class Images extends \Phalcon\Mvc\Model
{

    /**
     * ID связанного поста
     */
    public int $post_id;

    /**
     * Подпись к изображению
     */
    public string $title;

    /**
     * Имя файла
     */
    public string $filename;

}
