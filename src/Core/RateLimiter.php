<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — File-based Rate Limiter
 *
 * Simple rate limiting using file storage (no Redis/Memcached needed).
 * Works on shared hosting like InfinityFree.
 *
 * Usage:
 *   RateLimiter::check('login', $ip, 5, 300);  // 5 attempts per 5 minutes
 */
final class RateLimiter
{
    private static string $storageDir = '';

    /**
     * Check if the request is rate limited.
     * Throws RuntimeException (429) if limit exceeded.
     *
     * @param string $key     Action key (e.g., 'login', 'register')
     * @param string $identifier  Client identifier (e.g., IP address)
     * @param int    $maxAttempts Maximum attempts allowed
     * @param int    $windowSeconds Time window in seconds
     */
    public static function check(string $key, string $identifier, int $maxAttempts = 5, int $windowSeconds = 300): void
    {
        self::ensureStorageDir();

        $file = self::getFilePath($key, $identifier);
        $attempts = self::getAttempts($file, $windowSeconds);

        if (count($attempts) >= $maxAttempts) {
            $retryAfter = $attempts[0] + $windowSeconds - time();
            header("Retry-After: {$retryAfter}");
            throw new \RuntimeException('Too many attempts. Please try again later.', 429);
        }

        // Record this attempt
        $attempts[] = time();
        file_put_contents($file, json_encode($attempts), LOCK_EX);
    }

    /**
     * Clear rate limit for a specific key+identifier (e.g., after successful login).
     */
    public static function clear(string $key, string $identifier): void
    {
        $file = self::getFilePath($key, $identifier);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Get remaining attempts for a key+identifier.
     */
    public static function remaining(string $key, string $identifier, int $maxAttempts = 5, int $windowSeconds = 300): int
    {
        self::ensureStorageDir();
        $file = self::getFilePath($key, $identifier);
        $attempts = self::getAttempts($file, $windowSeconds);
        return max(0, $maxAttempts - count($attempts));
    }

    /**
     * Get valid (non-expired) attempts from file.
     */
    private static function getAttempts(string $file, int $windowSeconds): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return [];
        }

        $cutoff = time() - $windowSeconds;
        return array_values(array_filter($data, fn(int $t) => $t > $cutoff));
    }

    /**
     * Get the file path for a rate limit record.
     */
    private static function getFilePath(string $key, string $identifier): string
    {
        $hash = md5("{$key}:{$identifier}");
        return self::$storageDir . "/{$hash}.json";
    }

    /**
     * Ensure the storage directory exists.
     */
    private static function ensureStorageDir(): void
    {
        if (self::$storageDir !== '') {
            return;
        }

        // Try to find project root
        $root = defined('OPENGENETICS_ROOT') ? OPENGENETICS_ROOT : dirname(__DIR__, 2);
        self::$storageDir = $root . '/storage/rate-limit';

        if (!is_dir(self::$storageDir)) {
            @mkdir(self::$storageDir, 0755, true);
        }
    }

    /**
     * Clean up expired rate limit files (call periodically or via cron).
     */
    public static function cleanup(int $maxAge = 3600): int
    {
        self::ensureStorageDir();
        $count = 0;

        foreach (glob(self::$storageDir . '/*.json') as $file) {
            if (filemtime($file) < time() - $maxAge) {
                @unlink($file);
                $count++;
            }
        }

        return $count;
    }
}
