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

        $lines = file($file, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip blank lines and full-line comments
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (!str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $raw] = explode('=', $trimmed, 2);
            $key = trim($key);
            $raw = trim($raw);

            if ($key === '') continue;

            $value = self::parseValue($raw);

            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    /**
     * Parse a raw .env value:
     * - Double-quoted: "value with # hash" → value with # hash (strips quotes, no inline comment)
     * - Single-quoted: 'literal $value'    → literal $value
     * - Unquoted:      value # comment      → value (strips inline comment)
     */
    private static function parseValue(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        // Double-quoted — allow # inside, unescape \n \t
        if (str_starts_with($raw, '"')) {
            if (preg_match('/^"((?:[^\\"]|\\.)*)"/', $raw, $m)) {
                return str_replace(['\\n', '\\t', '\\"'], ["\n", "\t", '"'], $m[1]);
            }
            return trim($raw, '"');
        }

        // Single-quoted — fully literal
        if (str_starts_with($raw, "'")) {
            if (preg_match("/^'([^']*)'/", $raw, $m)) {
                return $m[1];
            }
            return trim($raw, "'");
        }

        // Unquoted — strip inline comment (# preceded by whitespace)
        $value = preg_replace('/\s+#.*$/', '', $raw);
        return trim($value ?? $raw);
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
            self::$cache[$key] = $value;
            return $value;
        }

        $env = getenv($key);
        if ($env !== false) {
            self::$cache[$key] = $env;
            return $env;
        }

        // Do NOT cache the default — key doesn't exist in environment
        return $default;
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
