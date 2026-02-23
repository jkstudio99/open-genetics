<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

use OpenGenetics\Middleware\CorsMiddleware;
use OpenGenetics\Middleware\AuthMiddleware;
use OpenGenetics\Middleware\RateLimitMiddleware;

/**
 * 🧬 OpenGenetics — File-based API Router (v2.1 with Path Parameters)
 *
 * Maps HTTP requests to PHP files in the api/ directory.
 * Each .php file = one endpoint. Supports REST methods via static class pattern.
 * Supports Middleware Pipeline via #[Middleware] PHP 8.1+ attributes.
 * Supports path parameters via {param} syntax in directory/file names.
 *
 * Examples:
 *   api/auth/login.php        → POST /api/auth/login
 *   api/users/{id}.php        → GET  /api/users/123  ($params['id'] = '123')
 *   api/users/{id}/posts.php  → GET  /api/users/123/posts
 *
 *   #[Middleware('auth', 'rate:10,60')]
 *   class UsersId {
 *       public static function get(array $body, array $params): void { ... }
 *   }
 */
final class Router
{
    private string $basePath;
    private string $apiDir;
    private ?string $cachedUri = null;

    /** @var array<string, string> Extracted path parameters from current request */
    private array $pathParams = [];

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

        // Resolve file path (also extracts path params into $this->pathParams)
        $filePath = $this->resolveFile($uri);

        if ($filePath === null) {
            Response::json(['error' => 'Endpoint not found'], 404);
            return;
        }

        // Include the endpoint file
        require_once $filePath;

        // Determine the handler class name from the resolved (non-param) URI
        $className = $this->resolveClassName($this->resolvedUri ?? $uri);

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

        // Expose path params via $_ROUTE superglobal-style + merge into body
        $params = $this->pathParams;

        // Collect middleware stack: global + endpoint-level
        $middleware = $this->collectMiddleware($className);

        // Execute through Pipeline
        Pipeline::send($body)
            ->through($middleware)
            ->then(function (array $body) use ($className, $handler, $params) {
                try {
                    // Pass $params as second argument if method accepts it
                    $ref = new \ReflectionMethod($className, $handler);
                    if (count($ref->getParameters()) >= 2) {
                        $className::$handler($body, $params);
                    } else {
                        $className::$handler($body);
                    }
                } catch (\Throwable $e) {
                    ErrorHandler::handleException($e);
                }
            });
    }

    /**
     * Get extracted path parameters from the current request.
     * e.g., for /api/users/{id} matched against /api/users/42 → ['id' => '42']
     */
    public function params(): array
    {
        return $this->pathParams;
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

    /** Resolved URI after stripping path param segments (used for class name resolution) */
    private ?string $resolvedUri = null;

    /**
     * Resolve URI to a PHP file in the api/ directory.
     * Supports {param} wildcards: api/users/{id}.php matches /api/users/42
     */
    private function resolveFile(string $uri): ?string
    {
        $this->pathParams  = [];
        $this->resolvedUri = null;

        if (empty($uri)) {
            $uri = 'index';
        }

        // 1. Exact match
        $file = "{$this->basePath}/{$this->apiDir}/{$uri}.php";
        if (file_exists($file)) {
            $this->resolvedUri = $uri;
            return $file;
        }

        // 2. index.php in directory
        $indexFile = "{$this->basePath}/{$this->apiDir}/{$uri}/index.php";
        if (file_exists($indexFile)) {
            $this->resolvedUri = $uri . '/index';
            return $indexFile;
        }

        // 3. Path parameter matching — scan api/ for {param} patterns
        $result = $this->matchParamRoute($uri);
        if ($result !== null) {
            return $result;
        }

        return null;
    }

    /**
     * Recursively scan api/ directory for {param} route patterns.
     * e.g., api/users/{id}.php matches URI "users/42"
     */
    private function matchParamRoute(string $uri): ?string
    {
        $uriParts  = explode('/', trim($uri, '/'));
        $apiBase   = "{$this->basePath}/{$this->apiDir}";

        return $this->scanForParamMatch($apiBase, $uriParts, [], '');
    }

    /**
     * Recursive directory scan for {param} file/folder matches.
     *
     * @param string   $dir       Current directory being scanned
     * @param string[] $remaining Remaining URI segments to match
     * @param array    $params    Accumulated path params so far
     * @param string   $uriSoFar URI segments matched so far (for class name)
     */
    private function scanForParamMatch(
        string $dir,
        array  $remaining,
        array  $params,
        string $uriSoFar
    ): ?string {
        if (empty($remaining)) {
            // Try index.php at this level
            $index = $dir . '/index.php';
            if (file_exists($index)) {
                $this->pathParams  = $params;
                $this->resolvedUri = trim($uriSoFar . '/index', '/');
                return $index;
            }
            return null;
        }

        $segment = array_shift($remaining);
        $items   = is_dir($dir) ? (scandir($dir) ?: []) : [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $itemPath = $dir . '/' . $item;

            // Match {param} file: e.g., {id}.php
            if (empty($remaining) && is_file($itemPath) && preg_match('/^\{(\w+)\}\.php$/', $item, $m)) {
                $newParams            = array_merge($params, [$m[1] => $segment]);
                $this->pathParams     = $newParams;
                $this->resolvedUri    = trim($uriSoFar . '/' . $item, '/.');
                // Use placeholder name for class resolution
                $this->resolvedUri    = trim($uriSoFar . '/' . preg_replace('/\{\w+\}/', '_param_', $item), '/');
                $this->resolvedUri    = rtrim($this->resolvedUri, '.php');
                return $itemPath;
            }

            // Match {param} directory: e.g., {id}/
            if (is_dir($itemPath) && preg_match('/^\{(\w+)\}$/', $item, $m)) {
                $newParams = array_merge($params, [$m[1] => $segment]);
                $result    = $this->scanForParamMatch(
                    $itemPath,
                    $remaining,
                    $newParams,
                    trim($uriSoFar . '/' . $item, '/')
                );
                if ($result !== null) return $result;
            }

            // Match exact segment as directory
            if (is_dir($itemPath) && $item === $segment) {
                $result = $this->scanForParamMatch(
                    $itemPath,
                    $remaining,
                    $params,
                    trim($uriSoFar . '/' . $item, '/')
                );
                if ($result !== null) return $result;
            }

            // Match exact segment as file (last segment)
            if (empty($remaining) && is_file($itemPath) && $item === $segment . '.php') {
                $this->pathParams  = $params;
                $this->resolvedUri = trim($uriSoFar . '/' . $segment, '/');
                return $itemPath;
            }
        }

        return null;
    }

    /**
     * Convert URI path to a PascalCase class name.
     * e.g., "auth/login" → "AuthLogin", "audit-logs" → "AuditLogs"
     * Path param placeholders {id} → "Id" (e.g., "users/{id}" → "UsersId")
     */
    private function resolveClassName(string $uri): string
    {
        if (empty($uri)) {
            $uri = 'index';
        }

        // Normalize {param} → param for class name
        $uri   = preg_replace('/\{(\w+)\}/', '$1', $uri);
        $uri   = str_replace('_param_', 'Id', $uri ?? '');
        $parts = explode('/', $uri);
        $name  = '';

        foreach ($parts as $part) {
            $segments = explode('-', $part);
            $name .= implode('', array_map('ucfirst', $segments));
        }

        return $name;
    }

    /**
     * Parse request body — supports JSON, form-data, and multipart.
     */
    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // multipart/form-data or application/x-www-form-urlencoded
        if (!empty($_POST) || str_contains($contentType, 'multipart/form-data') || str_contains($contentType, 'application/x-www-form-urlencoded')) {
            return array_merge($_GET, $_POST);
        }

        // JSON body
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Fallback: query string only
        return $_GET;
    }
}
