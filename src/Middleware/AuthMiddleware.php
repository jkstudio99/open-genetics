<?php

declare(strict_types=1);

namespace OpenGenetics\Middleware;

use OpenGenetics\Auth\JwtManager;
use OpenGenetics\Auth\Guard;
use OpenGenetics\Core\Response;

/**
 * 🧬 AuthMiddleware — JWT Authentication Middleware
 *
 * Validates JWT token from Authorization header.
 * Optionally enforces role-based access.
 *
 * Usage in endpoint:
 *   #[Middleware('auth')]              — Any authenticated user
 *   #[Middleware('auth:admin,hr')]     — Admin or HR only
 */
final class AuthMiddleware
{
    /**
     * @param array  $request  Request data
     * @param callable $next   Next middleware in the chain
     * @param string ...$roles Optional role names to restrict access
     */
    public function handle(array $request, callable $next, string ...$roles): void
    {
        $user = JwtManager::authenticate();

        if ($user === null) {
            Response::error('Authentication required', 401);
            return;
        }

        // Store authenticated user in Guard cache
        Guard::setUser($user);

        // Check roles if specified
        if (!empty($roles)) {
            $userRole = strtoupper($user['role_name'] ?? '');
            $allowed  = array_map('strtoupper', $roles);

            if (!in_array($userRole, $allowed, true)) {
                Response::error('Insufficient permissions', 403);
                return;
            }
        }

        $next($request);
    }
}
