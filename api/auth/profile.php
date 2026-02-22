<?php

/**
 * 🧬 GET /api/auth/profile
 * 
 * Get the current authenticated user's profile.
 * Requires valid JWT token.
 */

use OpenGenetics\Auth\Guard;
use OpenGenetics\Auth\AuthService;
use OpenGenetics\Core\Response;

class AuthProfile
{
    public static function get(array $body): void
    {
        $user = Guard::requireAuth();

        // Fetch full user data from DB
        $fullUser = AuthService::findById((int) $user['id']);

        if ($fullUser === null) {
            Response::error('User not found', 404);
        }

        unset($fullUser['password_hash']);

        Response::success($fullUser, 'Profile retrieved');
    }
}
