<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 #[Middleware] Attribute
 *
 * Apply to endpoint classes to declare required middleware.
 *
 * Usage:
 *   #[Middleware('auth')]                  — Require authentication
 *   #[Middleware('auth:admin,hr')]         — Require Admin or HR role
 *   #[Middleware('auth', 'rate:10,60')]    — Auth + Rate limit
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Middleware
{
    /** @var array<string> */
    public readonly array $middleware;

    public function __construct(string ...$middleware)
    {
        $this->middleware = $middleware;
    }
}
