<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Environment Variable Loader
 *
 * Parses .env files and loads values into $_ENV and getenv().
 * Supports comments (#) and quoted values.
 */
final class Env
{
    private static bool $loaded = false;
    private static array $cache = [];

    /**
     * Load environment variables from a .env file.
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        $file = rtrim($path, '/') . '/.env';

        if (!file_exists($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Remove surrounding quotes
            if (preg_match('/^"(.*)"$/', $value, $m)) {
                $value = $m[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $m)) {
                $value = $m[1];
            }

            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    /**
     * Get an environment variable with optional default.
     */
    public static function get(string $key, string $default = ''): string
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        } else {
            $env = getenv($key);
            $value = $env !== false ? $env : $default;
        }

        self::$cache[$key] = $value;
        return $value;
    }

    /**
     * Get an environment variable as boolean.
     * Treats '1', 'true', 'yes', 'on' as true.
     */
    public static function bool(string $key, bool $default = false): bool
    {
        $val = strtolower(self::get($key, $default ? 'true' : 'false'));
        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Get an environment variable as integer.
     */
    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, (string) $default);
    }

    /**
     * Check if running in debug mode.
     */
    public static function isDebug(): bool
    {
        return self::bool('APP_DEBUG', false);
    }

    /**
     * Reset loaded state (useful for testing).
     */
    public static function reset(): void
    {
        self::$loaded = false;
        self::$cache  = [];
    }
}
