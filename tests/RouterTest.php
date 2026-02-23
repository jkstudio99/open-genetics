<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OpenGenetics\Core\Router;

/**
 * 🧬 Unit tests for Router — path parameter matching and class name resolution.
 *
 * We test the private methods via ReflectionClass since dispatch() requires
 * a live HTTP request context.
 */
class RouterTest extends TestCase
{
    private string $tmpApiDir;
    private Router $router;

    protected function setUp(): void
    {
        $this->tmpApiDir = sys_get_temp_dir() . '/og_router_test_' . uniqid();
        mkdir($this->tmpApiDir . '/api', 0755, true);
        $this->router = new Router($this->tmpApiDir, 'api');
    }

    protected function tearDown(): void
    {
        $this->rrmdir($this->tmpApiDir);
    }

    // ── resolveClassName ─────────────────────────────────

    public function testClassNameSimple(): void
    {
        $this->assertSame('Login', $this->className('login'));
    }

    public function testClassNameNested(): void
    {
        $this->assertSame('AuthLogin', $this->className('auth/login'));
    }

    public function testClassNameKebabCase(): void
    {
        $this->assertSame('AuditLogs', $this->className('audit-logs'));
    }

    public function testClassNameWithPathParam(): void
    {
        // {id} in URI should become "Id" in class name
        $this->assertSame('UsersId', $this->className('users/{id}'));
    }

    public function testClassNameDeepNested(): void
    {
        $this->assertSame('ApiV1Users', $this->className('api/v1/users'));
    }

    public function testClassNameEmpty(): void
    {
        $this->assertSame('Index', $this->className(''));
    }

    // ── resolveFile (exact match) ─────────────────────────

    public function testResolveExactFile(): void
    {
        $this->createFile('api/users.php');
        $result = $this->resolveFile('users');
        $this->assertStringEndsWith('users.php', $result);
    }

    public function testResolveIndexFile(): void
    {
        mkdir($this->tmpApiDir . '/api/products', 0755, true);
        $this->createFile('api/products/index.php');
        $result = $this->resolveFile('products');
        $this->assertStringEndsWith('index.php', $result);
    }

    public function testResolveNonExistentReturnsNull(): void
    {
        $result = $this->resolveFile('nonexistent');
        $this->assertNull($result);
    }

    // ── resolveFile (path param matching) ────────────────

    public function testResolveParamFile(): void
    {
        $this->createFile('api/users/{id}.php');
        $result = $this->resolveFile('users/42');

        $this->assertNotNull($result);
        $this->assertStringEndsWith('{id}.php', $result);

        $params = $this->getPathParams();
        $this->assertSame(['id' => '42'], $params);
    }

    public function testResolveParamDirectory(): void
    {
        mkdir($this->tmpApiDir . '/api/users/{id}', 0755, true);
        $this->createFile('api/users/{id}/posts.php');

        $result = $this->resolveFile('users/99/posts');

        $this->assertNotNull($result);
        $this->assertStringEndsWith('posts.php', $result);

        $params = $this->getPathParams();
        $this->assertSame(['id' => '99'], $params);
    }

    public function testResolveMultipleParams(): void
    {
        mkdir($this->tmpApiDir . '/api/users/{userId}', 0755, true);
        mkdir($this->tmpApiDir . '/api/users/{userId}/posts/{postId}', 0755, true);
        $this->createFile('api/users/{userId}/posts/{postId}/comments.php');

        $result = $this->resolveFile('users/5/posts/12/comments');

        $this->assertNotNull($result);
        $params = $this->getPathParams();
        $this->assertSame(['userId' => '5', 'postId' => '12'], $params);
    }

    public function testExactMatchTakesPriorityOverParam(): void
    {
        // Both api/users/me.php and api/users/{id}.php exist
        $this->createFile('api/users/me.php');
        $this->createFile('api/users/{id}.php');

        $result = $this->resolveFile('users/me');

        $this->assertNotNull($result);
        $this->assertStringEndsWith('me.php', $result);
        // No path params should be set for exact match
        $this->assertSame([], $this->getPathParams());
    }

    // ── parseUri helpers ─────────────────────────────────

    public function testParseUriStripsApiPrefix(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/users/42';
        $_SERVER['CONTENT_TYPE'] = '';

        $uri = $this->parseUri();
        $this->assertSame('users/42', $uri);
    }

    public function testParseUriStripsPublicApiPrefix(): void
    {
        $_SERVER['REQUEST_URI'] = '/public/api/products';
        $uri = $this->parseUri();
        $this->assertSame('products', $uri);
    }

    // ── Helpers ───────────────────────────────────────────

    private function createFile(string $relPath, string $content = '<?php'): void
    {
        $full = $this->tmpApiDir . '/' . $relPath;
        $dir  = dirname($full);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($full, $content);
    }

    private function className(string $uri): string
    {
        $ref    = new ReflectionClass($this->router);
        $method = $ref->getMethod('resolveClassName');
        $method->setAccessible(true);
        return $method->invoke($this->router, $uri);
    }

    private function resolveFile(string $uri): ?string
    {
        $ref    = new ReflectionClass($this->router);
        $method = $ref->getMethod('resolveFile');
        $method->setAccessible(true);
        return $method->invoke($this->router, $uri);
    }

    private function getPathParams(): array
    {
        $ref  = new ReflectionClass($this->router);
        $prop = $ref->getProperty('pathParams');
        $prop->setAccessible(true);
        return $prop->getValue($this->router);
    }

    private function parseUri(): string
    {
        // Reset cachedUri first
        $ref  = new ReflectionClass($this->router);
        $prop = $ref->getProperty('cachedUri');
        $prop->setAccessible(true);
        $prop->setValue($this->router, null);

        $method = $ref->getMethod('parseUri');
        $method->setAccessible(true);
        return $method->invoke($this->router);
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
