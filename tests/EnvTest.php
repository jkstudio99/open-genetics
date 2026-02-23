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
        Env::reset();
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

    public function testMissingFileIsGraceful(): void
    {
        // Should NOT throw — graceful return when .env missing
        Env::load('/nonexistent/path/that/does/not/exist');
        $this->assertSame('default', Env::get('MISSING_KEY', 'default'));
    }

    public function testInlineCommentStripped(): void
    {
        file_put_contents($this->tmpDir . '/.env', "KEY=value # this is a comment\n");
        Env::load($this->tmpDir);
        $this->assertSame('value', Env::get('KEY'));
    }

    public function testHashInsideDoubleQuotesKept(): void
    {
        file_put_contents($this->tmpDir . '/.env', 'DB_URL="mysql://user:p#ss@host/db"' . "\n");
        Env::load($this->tmpDir);
        $this->assertSame('mysql://user:p#ss@host/db', Env::get('DB_URL'));
    }

    public function testHashInsideSingleQuotesKept(): void
    {
        file_put_contents($this->tmpDir . '/.env', "SECRET='abc#123'\n");
        Env::load($this->tmpDir);
        $this->assertSame('abc#123', Env::get('SECRET'));
    }

    public function testBoolHelper(): void
    {
        file_put_contents($this->tmpDir . '/.env', "FLAG_TRUE=true\nFLAG_ONE=1\nFLAG_YES=yes\nFLAG_FALSE=false\n");
        Env::load($this->tmpDir);
        $this->assertTrue(Env::bool('FLAG_TRUE'));
        $this->assertTrue(Env::bool('FLAG_ONE'));
        $this->assertTrue(Env::bool('FLAG_YES'));
        $this->assertFalse(Env::bool('FLAG_FALSE'));
        $this->assertFalse(Env::bool('NONEXISTENT'));
        $this->assertTrue(Env::bool('NONEXISTENT', true));
    }

    public function testIntHelper(): void
    {
        file_put_contents($this->tmpDir . '/.env', "PORT=8080\n");
        Env::load($this->tmpDir);
        $this->assertSame(8080, Env::int('PORT'));
        $this->assertSame(3306, Env::int('NONEXISTENT', 3306));
    }

    public function testLoadIsIdempotent(): void
    {
        file_put_contents($this->tmpDir . '/.env', "KEY=first\n");
        Env::load($this->tmpDir);
        // Second load should be no-op (already loaded)
        file_put_contents($this->tmpDir . '/.env', "KEY=second\n");
        Env::load($this->tmpDir);
        $this->assertSame('first', Env::get('KEY'));
    }
}
