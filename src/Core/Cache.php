<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * Cache — File-based Caching Layer (OpenGenetics v2.0)
 *
 * Features:
 *  - File-based storage in storage/cache/ — no Redis/Memcached needed
 *  - TTL (Time-To-Live) per entry
 *  - Tag-based invalidation: Cache::flush('products')
 *  - remember() pattern: cache or compute in one call
 *  - Zero external dependencies
 *
 * Usage:
 *   Cache::put('user:1', $userData, 300);       // Cache 5 min
 *   $user = Cache::get('user:1');
 *   Cache::forget('user:1');
 *
 *   // remember() — fetch from cache or compute
 *   $products = Cache::remember('products:all', 600, function() {
 *       return Database::query("SELECT * FROM products");
 *   });
 *
 *   // Tag-based invalidation
 *   Cache::tag('products')->put('list', $data, 300);
 *   Cache::tag('products')->flush();
 */
final class Cache
{
    private static string $storageDir = '';

    /** @var string|null Per-instance tag (not static — avoids cross-call pollution) */
    private ?string $instanceTag = null;

    // ── Bootstrap ────────────────────────────────────────

    private static function dir(): string
    {
        if (self::$storageDir === '') {
            $root = dirname(__DIR__, 2);
            self::$storageDir = $root . '/storage/cache';
        }

        if (!is_dir(self::$storageDir)) {
            mkdir(self::$storageDir, 0755, true);
        }

        return self::$storageDir;
    }

    // ── Key → File path ──────────────────────────────────

    private static function filePath(string $key): string
    {
        $hash = md5($key);
        return self::dir() . '/' . $hash . '.cache';
    }

    // ── Tag fluent interface ──────────────────────────────

    /**
     * Start a tag-scoped operation.
     * Cache::tag('products')->put('list', $data, 300);
     */
    public static function tag(string $tag): static
    {
        $instance = new static();
        $instance->instanceTag = $tag;
        return $instance;
    }

    private function taggedKey(string $key): string
    {
        return $this->instanceTag !== null ? $this->instanceTag . ':' . $key : $key;
    }

    // ── Core Operations ───────────────────────────────────

    /**
     * Store a value with optional TTL (seconds). 0 = forever.
     */
    public function put(string $key, mixed $value, int $ttl = 0): void
    {
        $key = $this->taggedKey($key);
        $payload = [
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'tag'     => $this->instanceTag,
            'data'    => $value,
        ];
        file_put_contents(self::filePath($key), serialize($payload), LOCK_EX);
    }

    /**
     * Retrieve a cached value, or null if missing/expired.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key  = $this->taggedKey($key);
        $file = self::filePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $payload = unserialize(file_get_contents($file));

        if ($payload === false) {
            @unlink($file);
            return $default;
        }

        // TTL check
        if ($payload['expires'] > 0 && time() > $payload['expires']) {
            @unlink($file);
            return $default;
        }

        return $payload['data'];
    }

    /**
     * Check if a key exists and is not expired.
     * Correctly handles cached null values.
     */
    public function has(string $key): bool
    {
        $key  = $this->taggedKey($key);
        $file = self::filePath($key);

        if (!file_exists($file)) {
            return false;
        }

        $payload = @unserialize(file_get_contents($file));
        if ($payload === false) {
            @unlink($file);
            return false;
        }

        if ($payload['expires'] > 0 && time() > $payload['expires']) {
            @unlink($file);
            return false;
        }

        return true;
    }

    /**
     * Delete a specific cache entry.
     */
    public function forget(string $key): void
    {
        $key  = $this->taggedKey($key);
        $file = self::filePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Get cached value or compute it via callback — the "remember" pattern.
     *
     * $products = Cache::remember('products:list', 300, fn() => Database::query(...));
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $instance = new static();
        $cached = $instance->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        $instance->put($key, $value, $ttl);
        return $value;
    }

    /**
     * Store forever (no TTL).
     */
    public static function forever(string $key, mixed $value): void
    {
        (new static())->put($key, $value, 0);
    }

    /**
     * Atomically increment a numeric cache value.
     */
    public static function increment(string $key, int $by = 1): int
    {
        $instance = new static();
        $current  = (int) ($instance->get($key) ?? 0);
        $new      = $current + $by;
        $instance->put($key, $new, 0);
        return $new;
    }

    /**
     * Atomically decrement a numeric cache value.
     */
    public static function decrement(string $key, int $by = 1): int
    {
        return self::increment($key, -$by);
    }

    /**
     * Flush all entries matching a tag prefix.
     * Cache::tag('products')->flush()  or  Cache::flush('products')
     */
    public function flush(?string $tag = null): void
    {
        $tag ??= $this->instanceTag;
        $dir  = self::dir();

        foreach (glob($dir . '/*.cache') ?: [] as $file) {
            $payload = @unserialize(file_get_contents($file));
            if ($payload === false) {
                @unlink($file);
                continue;
            }
            if ($tag === null || ($payload['tag'] ?? null) === $tag) {
                @unlink($file);
            }
        }
    }

    /**
     * Wipe entire cache directory.
     */
    public static function clear(): void
    {
        foreach (glob(self::dir() . '/*.cache') as $file) {
            @unlink($file);
        }
    }

    /**
     * Get cache stats: total entries, disk size, tag breakdown.
     */
    public static function stats(): array
    {
        $files  = glob(self::dir() . '/*.cache') ?: [];
        $total  = count($files);
        $size   = 0;
        $tags   = [];
        $expired = 0;

        foreach ($files as $file) {
            $size += filesize($file);
            $payload = @unserialize(file_get_contents($file));
            if (!$payload) continue;

            $tag = $payload['tag'] ?? null;
            if ($tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
            if ($payload['expires'] > 0 && time() > $payload['expires']) {
                $expired++;
            }
        }

        return [
            'entries' => $total,
            'expired' => $expired,
            'size_kb' => round($size / 1024, 1),
            'tags'    => $tags,
        ];
    }

    /**
     * Remove expired entries only (cache garbage collection).
     */
    public static function gc(): int
    {
        $count = 0;
        foreach (glob(self::dir() . '/*.cache') as $file) {
            $payload = @unserialize(file_get_contents($file));
            if (!$payload) {
                @unlink($file);
                $count++;
                continue;
            }
            if ($payload['expires'] > 0 && time() > $payload['expires']) {
                @unlink($file);
                $count++;
            }
        }
        return $count;
    }
}
