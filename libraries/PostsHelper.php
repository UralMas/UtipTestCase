<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

use Exception;

/**
 * Класс-помощник для постов и всего, что с ними связано
 */
class PostsHelper
{

    /**
     * Обработка и проверка GET-параметра "expand" (включение данных из других моделей)
     * @throws Exception
     */
    public static function getExpandModelsForRequest(string $expand, array $possibleModelsNames): array
    {
        return self::getParameterData($expand, $possibleModelsNames, 'Неверные параметры включения других моделей');
    }

    /**
     * Обработка и проверка GET-параметра "fields" (вывод только определённых полей поста)
     * @throws Exception
     */
    public static function getFieldsForRequest(string $fields, array $possibleFieldsNames, array $default): array
    {
        return self::getParameterData($fields, $possibleFieldsNames, 'Неверные поля в запросе', $default);
    }

    /**
     * Обработка и проверка параметра
     * @throws Exception
     */
    private static function getParameterData(string $parameter, array $possibleValues, string $exceptionMessage, array $default = []): array
    {
        if ($parameter !== '') {
            $values = explode(',', $parameter);
            $invalidValues = [];

            foreach ($values as $value) {
                if (! in_array($value, $possibleValues, true)) {
                    $invalidValues[] = $value;
                }
            }

            if (! empty($invalidValues)) {
                throw new Exception($exceptionMessage . ': "' . implode('", "', $invalidValues) . '"', 400);
            }

            return $values;
        }

        return $default;
    }

    /**
     * Обработка и проверка GET-параметра "sort"
     * @throws Exception
     */
    public static function getSortForRequest(string $sortString, array $possibleSortFields): array
    {
        if ($sortString !== '') {
            $fields = explode(',', $sortString);

            $sortFields = [];
            $invalidFields = [];

            foreach ($fields as $field) {
                $fieldParts = explode(':', $field);

                if (count($fieldParts) > 2
                    || ! in_array($fieldParts[0], $possibleSortFields, true)
                    || (isset($fieldParts[1]) && ! in_array($fieldParts[1], ['ASC', 'DESC'], true))) {
                    $invalidFields[] = $field;
                }

                $sortFields[] = implode(' ', $fieldParts);
            }

            if (! empty($invalidFields)) {
                throw new Exception('Неверные поля сортировки в запросе: "' . implode('", "', $invalidFields) . '"', 400);
            }

            return $sortFields;
        }

        return [];
    }
}