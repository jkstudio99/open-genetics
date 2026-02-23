<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

use OpenGenetics\Middleware\CorsMiddleware;
use OpenGenetics\Middleware\AuthMiddleware;
use OpenGenetics\Middleware\RateLimitMiddleware;

/**
 * 🧬 OpenGenetics — File-based API Router (v2.0 with Middleware Pipeline)
 *
 * Maps HTTP requests to PHP files in the api/ directory.
 * Each .php file = one endpoint. Supports REST methods via static class pattern.
 * Now supports Middleware Pipeline via #[Middleware] PHP 8.1+ attributes.
 *
 * Example:
 *   api/auth/login.php → POST /api/auth/login
 *
 *   #[Middleware('auth', 'rate:10,60')]
 *   class Products { ... }
 */
final class Router
{
    private string $basePath;
    private string $apiDir;
    private ?string $cachedUri = null;

    public function __construct(string $basePath, string $apiDir = 'api')
    {
        $this->basePath = rtrim($basePath, '/');
        $this->apiDir   = rtrim($apiDir, '/');

        // Register default middleware aliases
        Pipeline::alias('auth', AuthMiddleware::class);
        Pipeline::alias('rate', RateLimitMiddleware::class);
        Pipeline::alias('cors', CorsMiddleware::class);
    }

    /**
     * Resolve the incoming request to an API file and dispatch.
     */
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        header('Content-Type: application/json; charset=utf-8');

        $uri = $this->parseUri();

        // Resolve file path
        $filePath = $this->resolveFile($uri);

        if ($filePath === null) {
            Response::json(['error' => 'Endpoint not found'], 404);
            return;
        }

        // Include the endpoint file
        require_once $filePath;

        // Determine the handler class name from the file
        $className = $this->resolveClassName($uri);

        if (!class_exists($className)) {
            Response::json(['error' => 'Handler class not found'], 500);
            return;
        }

        // Map HTTP method to static method
        $handler = strtolower($method);

        if (!method_exists($className, $handler)) {
            Response::json(['error' => "Method {$method} not allowed"], 405);
            return;
        }

        // Parse request body
        $body = $this->parseBody();

        // Collect middleware stack: global + endpoint-level
        $middleware = $this->collectMiddleware($className);

        // Execute through Pipeline
        Pipeline::send($body)
            ->through($middleware)
            ->then(function (array $body) use ($className, $handler) {
                try {
                    $className::$handler($body);
                } catch (\Throwable $e) {
                    ErrorHandler::handleException($e);
                }
            });
    }

    /**
     * Collect middleware for an endpoint: global + class-level #[Middleware] attributes.
     *
     * @return array<string> Resolved middleware class names
     */
    private function collectMiddleware(string $className): array
    {
        // Start with global middleware
        $stack = Pipeline::getGlobal();

        // Parse #[Middleware] attributes from the endpoint class (PHP 8.1+)
        try {
            $reflection = new \ReflectionClass($className);
            $attributes = $reflection->getAttributes(Middleware::class);

            foreach ($attributes as $attr) {
                $instance = $attr->newInstance();
                foreach ($instance->middleware as $name) {
                    $stack[] = Pipeline::resolve($name);
                }
            }
        } catch (\ReflectionException $e) {
            // Ignore — class without attributes
        }

        return $stack;
    }

    /**
     * Parse the request URI, stripping base path and query string.
     */
    private function parseUri(): string
    {
        if ($this->cachedUri !== null) {
            return $this->cachedUri;
        }

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Remove the project base path (e.g., /open-genetics)
        $projectBase = parse_url(Env::get('APP_URL', ''), PHP_URL_PATH) ?? '';
        if ($projectBase && str_starts_with($uri, $projectBase)) {
            $uri = substr($uri, strlen($projectBase));
        }

        // Remove leading /public/api/ or /api/ prefix
        $uri = preg_replace('#^(?:/public)?/api/?#', '', $uri);

        $this->cachedUri = trim($uri, '/');
        return $this->cachedUri;
    }

    /**
     * Resolve URI to a PHP file in the api/ directory.
     */
    private function resolveFile(string $uri): ?string
    {
        if (empty($uri)) {
            $uri = 'index';
        }

        $file = "{$this->basePath}/{$this->apiDir}/{$uri}.php";

        if (file_exists($file)) {
            return $file;
        }

        // Try index.php in directory
        $indexFile = "{$this->basePath}/{$this->apiDir}/{$uri}/index.php";
        if (file_exists($indexFile)) {
            return $indexFile;
        }

        return null;
    }

    /**
     * Convert URI path to a PascalCase class name.
     * e.g., "auth/login" → "AuthLogin", "audit-logs" → "AuditLogs"
     */
    private function resolveClassName(string $uri): string
    {
        if (empty($uri)) {
            $uri = 'index';
        }

        $parts = explode('/', $uri);
        $name  = '';

        foreach ($parts as $part) {
            $segments = explode('-', $part);
            $name .= implode('', array_map('ucfirst', $segments));
        }

        return $name;
    }

    /**
     * Parse JSON request body.
     */
    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');

        if (empty($raw)) {
            return array_merge($_GET, $_POST);
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
