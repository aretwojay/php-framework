<?php

namespace App\Lib\Cache;

class TransientManager
{
    private static array $transients = [];
    private static string $storagePath;

    public static function init(string $storagePath = null): void
    {
        if ($storagePath === null) {
            $storagePath = __DIR__ . '/../../../log/transients';
        }
        self::$storagePath = $storagePath;
        
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        self::loadTransients();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$transients[$key])) {
            return $default;
        }

        $transient = self::$transients[$key];

        if ($transient['expires_at'] !== 0 && time() > $transient['expires_at']) {
            self::delete($key);
            return $default;
        }

        return $transient['value'];
    }

    public static function set(string $key, mixed $value, int $expiration = 3600): bool
    {
        $expiresAt = ($expiration === 0) ? 0 : time() + $expiration;

        self::$transients[$key] = [
            'value' => $value,
            'expires_at' => $expiresAt,
        ];

        return self::saveTransients();
    }

    public static function delete(string $key): bool
    {
        if (isset(self::$transients[$key])) {
            unset(self::$transients[$key]);
            return self::saveTransients();
        }
        return true;
    }

    public static function clear(): bool
    {
        self::$transients = [];
        return self::saveTransients();
    }

    private static function loadTransients(): void
    {
        if (!self::$storagePath) {
            return;
        }

        $file = self::$storagePath . '/transients.json';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            if ($data) {
                self::$transients = $data;
            }
        }
    }

    private static function saveTransients(): bool
    {
        if (!self::$storagePath) {
            return true;
        }

        $file = self::$storagePath . '/transients.json';
        $json = json_encode(self::$transients, JSON_PRETTY_PRINT);
        return file_put_contents($file, $json) !== false;
    }
}
