<?php

declare(strict_types=1);

namespace OpenGenetics\Auth;

use OpenGenetics\Core\Response;

/**
 * 🧬 OpenGenetics — Genetic RBAC Guard
 * 
 * Role-Based Access Control at the DNA level of the framework.
 * Supports 3 roles: ADMIN (1), MANAGER (2), EMPLOYEE (3).
 * 
 * Usage:
 *   Guard::requireAuth();              // Any authenticated user
 *   Guard::requireRole('ADMIN');       // Admin only
 *   Guard::requireRole('ADMIN', 'MANAGER'); // Admin or Manager
 */
final class Guard
{
    /** Role constants */
    public const ADMIN    = 'ADMIN';
    public const MANAGER  = 'MANAGER';
    public const EMPLOYEE = 'EMPLOYEE';

    /** Current authenticated user (cached per request) */
    private static ?array $currentUser = null;

    /**
     * Require authentication. Returns user data or sends 401.
     */
    public static function requireAuth(): array
    {
        $user = JwtManager::authenticate();

        if ($user === null) {
            Response::error('Authentication required', 401);
        }

        self::$currentUser = $user;
        return $user;
    }

    /**
     * Require specific role(s). Sends 403 if role doesn't match.
     *
     * @param string ...$allowedRoles One or more role names (ADMIN, MANAGER, EMPLOYEE)
     */
    public static function requireRole(string ...$allowedRoles): array
    {
        $user = self::requireAuth();

        $userRole = strtoupper($user['role_name'] ?? '');

        if (!in_array($userRole, array_map('strtoupper', $allowedRoles), true)) {
            Response::error('Insufficient permissions', 403);
        }

        return $user;
    }

    /**
     * Check if current user has a specific role (without blocking).
     */
    public static function hasRole(string $role): bool
    {
        $user = self::$currentUser ?? JwtManager::authenticate();

        if ($user === null) {
            return false;
        }

        return strtoupper($user['role_name'] ?? '') === strtoupper($role);
    }

    /**
     * Get the current authenticated user (null if not authenticated).
     */
    public static function user(): ?array
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        self::$currentUser = JwtManager::authenticate();
        return self::$currentUser;
    }

    /**
     * Get the current user ID or null.
     */
    public static function userId(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Get the current user's tenant ID.
     */
    public static function tenantId(): ?string
    {
        $user = self::user();
        return $user['tenant_id'] ?? null;
    }
}
