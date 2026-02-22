<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OpenGenetics\Core\Env;

/**
 * 🧬 Unit tests for Env class.
 */
class EnvTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/og_test_' . uniqid();
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpDir . '/.env');
        @rmdir($this->tmpDir);

        // Reset Env state via reflection
        $ref = new ReflectionClass(Env::class);
        $loaded = $ref->getProperty('loaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, false);

        $cache = $ref->getProperty('cache');
        $cache->setAccessible(true);
        $cache->setValue(null, []);
    }

    public function testLoadAndGet(): void
    {
        file_put_contents($this->tmpDir . '/.env', "APP_NAME=TestApp\nAPP_DEBUG=true\n");
        Env::load($this->tmpDir);

        $this->assertSame('TestApp', Env::get('APP_NAME'));
        $this->assertSame('true', Env::get('APP_DEBUG'));
    }

    public function testDefaultValue(): void
    {
        file_put_contents($this->tmpDir . '/.env', "KEY1=val1\n");
        Env::load($this->tmpDir);

        $this->assertSame('fallback', Env::get('NONEXISTENT', 'fallback'));
    }

    public function testIsDebug(): void
    {
        file_put_contents($this->tmpDir . '/.env', "APP_DEBUG=true\n");
        Env::load($this->tmpDir);

        $this->assertTrue(Env::isDebug());
    }

    public function testQuotedValues(): void
    {
        file_put_contents($this->tmpDir . '/.env', "KEY1=\"hello world\"\nKEY2='single'\n");
        Env::load($this->tmpDir);

        $this->assertSame('hello world', Env::get('KEY1'));
        $this->assertSame('single', Env::get('KEY2'));
    }

    public function testCommentsIgnored(): void
    {
        file_put_contents($this->tmpDir . '/.env', "# comment\nKEY=value\n");
        Env::load($this->tmpDir);

        $this->assertSame('value', Env::get('KEY'));
        $this->assertSame('', Env::get('# comment'));
    }

    public function testMissingFileThrows(): void
    {
        $this->expectException(RuntimeException::class);
        Env::load('/nonexistent/path');
    }
}
