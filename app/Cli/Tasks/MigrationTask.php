<?php

declare(strict_types=1);

namespace UtipTestCase\Cli\Tasks;

use Exception;
use Phalcon\Cli\Task;
use Phalcon\Migrations\Migrations;

/**
 * Класс, отвечающий за таски, связанные с миграциями
 */
class MigrationTask extends Task
{

    /**
     * Генерация миграций
     * @throws Exception
     */
    public function generateAction(): void
    {
        /**
         * Проверка на существование папки хранения миграций
         */
        if (! file_exists($this->config->application->migrationsDir)) {
            /**
             * Если папка не существует - создаём её
             */
            mkdir($this->config->application->migrationsDir, 0755);
        }

        $migration = new Migrations();

        $migration::generate([
            'migrationsDir' => [
                $this->config->application->migrationsDir,
            ],
            'config' => $this->config,
            'tableName' => 'categories,images,posts,tokens,users',
            'exportData' => 'oncreate'
        ]);

        echo 'Миграция успешно создана' . PHP_EOL;
    }

    /**
     * Разворачивание миграций
     * @throws \Phalcon\Db\Exception
     */
    public function runAction(): void
    {
        $migration = new Migrations();

        $migration::run([
            'migrationsDir' => [
                BASE_PATH . '/migrations',
            ],
            'config' => $this->config,
            'tableName' => 'categories,images,posts,tokens,users',
            'exportData' => 'oncreate'
        ]);

        echo 'Миграция успешно развёрнута' . PHP_EOL;
    }
}