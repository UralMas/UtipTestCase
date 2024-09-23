<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

use Exception;
use UtipTestCase\Models\Images;
use UtipTestCase\Models\Posts;

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

    /**
     * Формирование данных поста
     */
    public static function getPostData(Posts $post, array $fields, array $expandModels): array
    {
        $postData = [];

        /**
         * Заполнение данных полей
         */
        foreach ($fields as $field) {
            $postData[$field] = $post->$field;
        }

        /**
         * Данные категории
         */
        if (in_array('category', $expandModels, true)) {
            $category = $post->category;

            $postData['category'] = [
                'name'          => $category->name,
                'created_at'    => $category->created_at
            ];
        }

        /**
         * Данные изображений
         */
        if (in_array('images', $expandModels, true)) {
            $images = [];

            $possibleFieldsNames = ['id', 'title', 'filename', 'url', 'created_at'];

            foreach ($post->images as $image) {
                $images[] = self::getImageData($image, $possibleFieldsNames);
            }

            $postData['images'] = $images;
        }

        return $postData;
    }

    /**
     * Формирование данных изображения
     */
    public static function getImageData(Images $image, array $possibleFieldsNames): array
    {
        $imageData = [];

        foreach ($possibleFieldsNames as $field) {
            $imageData[$field] = $field !== 'url'
                ? $image->$field
                : ImagesHelper::getImageUrl($image->filename);
        }

        return $imageData;
    }
}