<?php

/**
 * 🧬 POST /api/auth/reset-password
 * 
 * Reset password using a valid token.
 */

use OpenGenetics\Auth\AuthService;
use OpenGenetics\Audit\AuditLog;
use OpenGenetics\Core\Response;

class AuthResetPassword
{
    public static function post(array $body): void
    {
        $token    = $body['token'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($token) || empty($password)) {
            Response::error('Token and new password are required', 422);
        }

        if (strlen($password) < 8) {
            Response::error('Password must be at least 8 characters', 422);
        }

        try {
            AuthService::resetPassword($token, $password);

            Response::success(null, 'Password has been reset successfully');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), (int) $e->getCode() ?: 400);
        }
    }
}
