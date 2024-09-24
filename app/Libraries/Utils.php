<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

use Exception;

/**
 * Класс различных утилит
 */
class Utils
{
    /**
     * Форматирование даты
     */
    public static function formatDate(string $date, string $formatTo, $formatFrom = null): bool|string
    {
        try {
            if (is_null($formatFrom)) {
                $dateTime = new \DateTime($date);
            } else {
                $dateTime = \DateTime::createFromFormat($formatFrom, $date);
            }
        } catch (Exception) {
            return false;
        }

        return $dateTime !== false ? $dateTime->format($formatTo) : false;
    }
}