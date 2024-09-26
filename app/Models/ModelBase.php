<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Di\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ResultsetInterface;

/**
 * Абстрактный класс моделей, поддерживающие кэширование для стандартных функций
 */
class ModelBase extends Model
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
        if (null !== $parameters && DI::getDefault()->has('modelsCache')) {
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
        $this->clearCache();
    }

    public function afterDelete(): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        if ($this->getDI()->has('modelsCache')) {
            $this->getDI()->getModelsCache()->clear();
        }
    }

    /**
     * Логирование запросов на изменение
     * Только если тип окружения установлен в "development"
     */
    public function beforeSave(): void
    {
        if (! $this->getDI()->has('logger')) {
            return;
        }

        $originalData = $this->getSnapshotData();
        $logFields = [];

        foreach ($this->toArray() as $field => $value) {
            if ($field === 'id') {
                continue;
            }

            if (! array_key_exists($field, $originalData) || $originalData[$field] !== $value) {
                $logFields[$field] = $value;
            }
        }

        if (! empty($logFields)) {
            $logData = [
                'model'     => self::class,
                'type'      => $this->id === 0 ? 'create' : 'update',
                'fields'    => $logFields
            ];

            if ($this->id !== 0) {
                $logData['id'] = $this->id;
            }

            $this->getDI()->getLogger()->info(json_encode($logData, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Инициализация модели
     */
    public function initialize(): void
    {
        /**
         * Сохранение слепка данных перед сохранением
         */
        $this->keepSnapshots(true);

        /**
         * Обновление в БД только измененных полей
         */
        $this->useDynamicUpdate(true);
    }
}