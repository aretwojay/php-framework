<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Sécurisation des cookies de session
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '', // Peut être défini selon l'environnement
                'secure' => true, // Requis pour HTTPS
                'httponly' => true, // Empêche l'accès JS au cookie
                'samesite' => 'Strict' // Protection CSRF
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }
}
