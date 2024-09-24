<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

use Phalcon\Di\Di;

/**
 * Класс-помощник для изображений
 */
class ImagesHelper
{

    /**
     * Формирование ссылки на изображение
     */
    public static function getImageUrl(string $filename): string
    {
        return DI::getDefault()->getConfig()->application->imagesUrl . $filename;
    }
}