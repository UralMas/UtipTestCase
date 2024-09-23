<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

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

    /**
     * Действия после удаления
     */
    public function afterDelete(): void
    {
        // Удаление файла после удаления записи
        @unlink($this->getDI()->getConfig()->application->imagesFolder . $this->filename);
    }

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
                    "message" => "Не указана подпись к изображению"
                ])
            ]
        );

        $validator->rules(
            "filename",
            [
                new PresenceOf([
                    "message" => "Не указано имя файла"
                ])
            ]
        );

        return $this->validate($validator);
    }

}
