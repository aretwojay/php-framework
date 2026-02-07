<?php

namespace App\Lib\Security;

class Csrf
{
    private const SESSION_KEY = 'csrf_token';
    private const ERROR_INVALID_TOKEN = 'Invalid CSRF token';

    public static function generate(): string
    {
        self::ensureSessionStarted();

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = $token;

        return $token;
    }

    public static function verify(?string $token): void
    {
        self::ensureSessionStarted();

        if (!self::isTokenValid($token)) {
            http_response_code(403);
            throw new \Exception(self::ERROR_INVALID_TOKEN);
        }
    }

    private static function isTokenValid(?string $token): bool
    {
        return !empty($token)
            && !empty($_SESSION[self::SESSION_KEY])
            && hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    private static function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
