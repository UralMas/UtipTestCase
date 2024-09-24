<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ResultsetInterface;

/**
 * Абстрактный класс моделей, поддерживающие кэширование для стандартных функций
 */
class ModelCacheable extends Model
{

    /**
     * ID записи
     */
    public int $id = 0;

    /**
     * Переписывание стандартных функций
     */
    public static function find($parameters = null): ResultsetInterface
    {
        $parameters = self::checkCacheParameters($parameters);

        return parent::find($parameters);
    }

    /**
     * @param array|string|int|null $parameters
     * @return \Phalcon\Mvc\ModelInterface|\Phalcon\Mvc\Model\Row|null
     */
    public static function findFirst($parameters = null)
    {
        $parameters = self::checkCacheParameters($parameters);

        return parent::findFirst($parameters);
    }

    /**
     * Добавление кэширования в параметры поиска
     */
    protected static function checkCacheParameters($parameters = null)
    {
        if (null !== $parameters) {
            if (true !== is_array($parameters)) {
                $parameters = [
                    $parameters
                ];
            }

            if (true !== isset($parameters['cache'])) {
                $parameters['cache'] = [
                    'key'       => self::generateCacheKey($parameters),
                    'service'   => 'modelsCache'
                ];
            }
        }

        return $parameters;
    }

    /**
     * Генерация ключа кэша
     */
    protected static function generateCacheKey(array $parameters): string
    {
        return preg_replace('/[^a-z0-9]/si' ,'', static::class . self::generateCacheKeyRecursive($parameters));
    }

    protected static function generateCacheKeyRecursive(array $parameters): string
    {
        $uniqueKey = [];

        foreach ($parameters as $key => $value) {
            if (true === is_scalar($value)) {
                $uniqueKey[] = $key . ':' . $value;
            } elseif (true === is_array($value)) {
                $uniqueKey[] = sprintf(
                    '%s:%s',
                    $key,
                    self::generateCacheKeyRecursive($value)
                );
            }
        }

        return join(',', $uniqueKey);
    }

    /**
     * Очистка кэша после создания/изменения/удаления
     */
    public function afterSave(): void
    {
        $this->getDI()->getModelsCache()->clear();
    }

    public function afterDelete(): void
    {
        $this->getDI()->getModelsCache()->clear();
    }
}