<?php

/**
 * 🧬 POST /api/auth/forgot-password
 * 
 * Generate a password reset token and return it.
 * In production, this would send an email.
 */

use OpenGenetics\Auth\AuthService;
use OpenGenetics\Core\Response;

class AuthForgotPassword
{
    public static function post(array $body): void
    {
        $email = $body['email'] ?? '';

        if (empty($email)) {
            Response::error('Email is required', 422);
        }

        try {
            $token = AuthService::forgotPassword($email);

            // In production: send email with token
            // For development: return the token directly
            Response::success([
                'token'   => $token,
                'message' => 'Reset token generated. In production, this would be sent via email.',
            ], 'Password reset token generated');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), (int) $e->getCode() ?: 400);
        }
    }
}
