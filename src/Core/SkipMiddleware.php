<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 #[SkipMiddleware] Attribute
 *
 * Exclude specific middleware from running on an endpoint,
 * even if that middleware is registered globally.
 *
 * Usage:
 *   #[SkipMiddleware(CorsMiddleware::class)]
 *   #[SkipMiddleware(CorsMiddleware::class, RateLimitMiddleware::class)]
 *   class PublicWebhook
 *   {
 *       public static function post(array $body): void { ... }
 *   }
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class SkipMiddleware
{
    /** @var array<class-string> */
    public readonly array $middleware;

    public function __construct(string ...$middleware)
    {
        $this->middleware = $middleware;
    }
}
