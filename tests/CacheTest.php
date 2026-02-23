<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OpenGenetics\Core\Cache;

/**
 * 🧬 Unit tests for Cache class.
 */
class CacheTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/og_cache_test_' . uniqid();
        mkdir($this->tmpDir, 0755, true);

        // Point Cache storage to temp dir via reflection
        $ref  = new ReflectionClass(Cache::class);
        $prop = $ref->getProperty('storageDir');
        $prop->setAccessible(true);
        $prop->setValue(null, $this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*.cache') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->tmpDir);

        // Reset storageDir
        $ref  = new ReflectionClass(Cache::class);
        $prop = $ref->getProperty('storageDir');
        $prop->setAccessible(true);
        $prop->setValue(null, '');
    }

    // ── put / get ────────────────────────────────────────

    public function testPutAndGet(): void
    {
        $cache = new Cache();
        $cache->put('key1', 'hello');
        $this->assertSame('hello', $cache->get('key1'));
    }

    public function testGetMissingReturnsDefault(): void
    {
        $cache = new Cache();
        $this->assertNull($cache->get('nonexistent'));
        $this->assertSame('fallback', $cache->get('nonexistent', 'fallback'));
    }

    public function testGetExpiredReturnsDefault(): void
    {
        $cache = new Cache();
        $cache->put('expiring', 'value', 1);

        // Manually expire by manipulating file
        $file = $this->tmpDir . '/' . md5('expiring') . '.cache';
        $payload = unserialize(file_get_contents($file));
        $payload['expires'] = time() - 10;
        file_put_contents($file, serialize($payload));

        $this->assertNull($cache->get('expiring'));
    }

    public function testPutWithTtl(): void
    {
        $cache = new Cache();
        $cache->put('ttl_key', 'data', 300);
        $this->assertSame('data', $cache->get('ttl_key'));
    }

    // ── has ──────────────────────────────────────────────

    public function testHasReturnsTrueForExisting(): void
    {
        $cache = new Cache();
        $cache->put('exists', 'yes');
        $this->assertTrue($cache->has('exists'));
    }

    public function testHasReturnsFalseForMissing(): void
    {
        $cache = new Cache();
        $this->assertFalse($cache->has('missing'));
    }

    public function testHasReturnsTrueWhenValueIsNull(): void
    {
        // Critical: has() must not return false just because value is null
        $cache = new Cache();
        $cache->put('null_val', null);
        $this->assertTrue($cache->has('null_val'));
    }

    // ── forget ───────────────────────────────────────────

    public function testForget(): void
    {
        $cache = new Cache();
        $cache->put('to_delete', 'bye');
        $cache->forget('to_delete');
        $this->assertFalse($cache->has('to_delete'));
    }

    // ── tag ──────────────────────────────────────────────

    public function testTagPutAndGet(): void
    {
        Cache::tag('products')->put('list', ['a', 'b'], 300);
        $result = Cache::tag('products')->get('list');
        $this->assertSame(['a', 'b'], $result);
    }

    public function testTagFlush(): void
    {
        Cache::tag('orders')->put('all', ['x'], 300);
        Cache::tag('orders')->put('pending', ['y'], 300);
        Cache::tag('orders')->flush();

        $this->assertNull(Cache::tag('orders')->get('all'));
        $this->assertNull(Cache::tag('orders')->get('pending'));
    }

    public function testTagDoesNotFlushOtherTags(): void
    {
        Cache::tag('users')->put('list', ['u1'], 300);
        Cache::tag('posts')->put('list', ['p1'], 300);

        Cache::tag('posts')->flush();

        // users tag should still be intact
        $this->assertSame(['u1'], Cache::tag('users')->get('list'));
    }

    public function testTagIsolation(): void
    {
        // Two different tags with same key should not collide
        Cache::tag('a')->put('item', 'from-a', 300);
        Cache::tag('b')->put('item', 'from-b', 300);

        $this->assertSame('from-a', Cache::tag('a')->get('item'));
        $this->assertSame('from-b', Cache::tag('b')->get('item'));
    }

    // ── remember ─────────────────────────────────────────

    public function testRememberComputesOnce(): void
    {
        $calls = 0;
        $val   = Cache::remember('computed', 300, function () use (&$calls) {
            $calls++;
            return 'result';
        });

        $this->assertSame('result', $val);
        $this->assertSame(1, $calls);

        // Second call should use cache
        Cache::remember('computed', 300, function () use (&$calls) {
            $calls++;
            return 'result';
        });

        $this->assertSame(1, $calls);
    }

    // ── forever ──────────────────────────────────────────

    public function testForever(): void
    {
        Cache::forever('permanent', 'always');
        $this->assertSame('always', (new Cache())->get('permanent'));
    }

    // ── increment / decrement ────────────────────────────

    public function testIncrement(): void
    {
        Cache::increment('counter');
        Cache::increment('counter');
        $this->assertSame(2, Cache::increment('counter', 0));
    }

    public function testIncrementByAmount(): void
    {
        Cache::increment('score', 5);
        $this->assertSame(10, Cache::increment('score', 5));
    }

    public function testDecrement(): void
    {
        Cache::increment('stock', 10);
        Cache::decrement('stock', 3);
        $this->assertSame(7, Cache::increment('stock', 0));
    }

    // ── clear / gc ───────────────────────────────────────

    public function testClear(): void
    {
        $cache = new Cache();
        $cache->put('k1', 'v1');
        $cache->put('k2', 'v2');
        Cache::clear();
        $this->assertFalse($cache->has('k1'));
        $this->assertFalse($cache->has('k2'));
    }

    public function testGcRemovesExpiredOnly(): void
    {
        $cache = new Cache();
        $cache->put('live', 'yes', 300);
        $cache->put('dead', 'no', 1);

        // Expire 'dead'
        $file = $this->tmpDir . '/' . md5('dead') . '.cache';
        $payload = unserialize(file_get_contents($file));
        $payload['expires'] = time() - 10;
        file_put_contents($file, serialize($payload));

        $removed = Cache::gc();

        $this->assertSame(1, $removed);
        $this->assertTrue($cache->has('live'));
    }
}
