<?php

/**
 * 🧬 POST /api/auth/login
 *
 * Authenticate a user and return a JWT token.
 */

use OpenGenetics\Auth\AuthService;
use OpenGenetics\Audit\AuditLog;
use OpenGenetics\Core\RateLimiter;
use OpenGenetics\Core\Response;

class AuthLogin
{
    public static function post(array $body): void
    {
        $email    = $body['email'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            Response::error('Email and password are required', 422);
        }

        // Rate limit: 5 attempts per 5 minutes per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        RateLimiter::check('login', $ip, 5, 300);

        try {
            $result = AuthService::login($email, $password);

            // Clear rate limit on success
            RateLimiter::clear('login', $ip);

            // Log login activity
            AuditLog::log(AuditLog::LOGIN, 'users', [
                'email' => $email,
            ], (int) $result['user']['id']);

            Response::success($result, 'Login successful');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), (int) $e->getCode() ?: 401);
        }
    }
}
