<?php

declare(strict_types=1);

namespace UtipTestCase\Libraries;

/**
 * Класс-помощник для токенов авторизация
 */
class TokensHelper
{

    /**
     * Генерация токена авторизация
     */
    public static function generateToken(int $userId): string
    {
        return sha1($userId . date('Y-m-d H:i:s'));
    }
}