<?php

declare(strict_types=1);

namespace OpenGenetics\Middleware;

use OpenGenetics\Core\RateLimiter;

/**
 * 🧬 RateLimitMiddleware — Request Rate Limiting
 *
 * Limits requests per IP address within a time window.
 * Uses the existing file-based RateLimiter.
 *
 * Usage:
 *   #[Middleware('rate')]              — Default: 60 req/min
 *   #[Middleware('rate:10,60')]        — 10 requests per 60 seconds
 */
final class RateLimitMiddleware
{
    /**
     * @param array  $request       Request data
     * @param callable $next        Next middleware
     * @param string $maxAttempts   Max attempts (default: 60)
     * @param string $windowSeconds Time window in seconds (default: 60)
     */
    public function handle(array $request, callable $next, string $maxAttempts = '60', string $windowSeconds = '60'): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // check() returns remaining count atomically — no second file read needed
        $remaining = RateLimiter::check($uri, $ip, (int) $maxAttempts, (int) $windowSeconds);
        header("X-RateLimit-Limit: {$maxAttempts}");
        header("X-RateLimit-Remaining: {$remaining}");

        $next($request);
    }
}
