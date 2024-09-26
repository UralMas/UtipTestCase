<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

use Exception;
use Phalcon\Di\Di;
use Phalcon\Http\Request\FileInterface;
use UtipTestCase\Models\Images;

/**
 * Класс-помощник для файлов
 */
class FilesHelper
{
    /**
     * Загрузка файла
     * @throws Exception
     */
    public static function loadFile(FileInterface $file): string
    {
        /**
         * Проверка на максимальный размер файла
         */
        if ($file->getSize() > DI::getDefault()->getConfig()->application->imagesMaxSize) {
            throw new Exception('Размер загружаемого файла превышает максимальный (' . round(DI::getDefault()->getConfig()->application->imagesMaxSize / (1024 * 1024)) . ' МБ)', 400);
        }

        /**
         * Проверка на разрешённый тип файла
         */
        if (! in_array($file->getRealType(), explode(';', DI::getDefault()->getConfig()->application->imagesAllowedTypes), true)) {
            throw new Exception('Тип файла не разрешён к загрузке. Допускаются лишь следующие типы файлов: ' . DI::getDefault()->getConfig()->application->imagesAllowedTypes, 400);
        }

        /**
         * Проверка на дубликат файла (подразумевается, что файлы с одинаковым названием - это один и тот же файл)
         */
        $dupedImage = Images::count([
            'conditions' => 'filename = :filename:',
            'bind' => [
                'filename' => $file->getName()
            ]
        ]);

        if ($dupedImage != 0) {
            throw new Exception('Файл с таким же названием уже был загружен', 400);
        }

        $imagesPath = DI::getDefault()->getConfig()->application->imagesFolder;

        /**
         * Проверка на существование папки хранения изображений
         */
        if (! file_exists($imagesPath)) {
            /**
             * Если папка не существует - создаём её
             */
            mkdir($imagesPath, 0755);
        }

        /**
         * Загрузка файла в папку хранения изображений
         */
        $file->moveTo(
            $imagesPath . $file->getName()
        );

        return $file->getName();
    }
}