<?php

declare(strict_types=1);

namespace OpenGenetics\Middleware;

use OpenGenetics\Core\Env;

/**
 * 🧬 CorsMiddleware — Cross-Origin Resource Sharing
 *
 * Sets CORS headers and handles preflight OPTIONS requests.
 * Automatically applied as global middleware.
 */
final class CorsMiddleware
{
    public function handle(array $request, callable $next): void
    {
        $origin  = Env::get('CORS_ORIGIN', '*');
        $methods = Env::get('CORS_METHODS', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $headers = Env::get('CORS_HEADERS', 'Content-Type, Authorization, X-Locale');

        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Methods: {$methods}");
        header("Access-Control-Allow-Headers: {$headers}");
        header('Access-Control-Max-Age: 86400');
        // Vary: Origin prevents CDN/proxy caching a response for one origin and serving it to another
        if ($origin !== '*') {
            header('Vary: Origin');
        }

        // Handle preflight
        if (strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next($request);
    }
}
