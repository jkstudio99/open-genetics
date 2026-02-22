<?php

/**
 * 🧬 POST /api/auth/register
 *
 * Register a new user account and return a JWT token.
 */

use OpenGenetics\Auth\AuthService;
use OpenGenetics\Audit\AuditLog;
use OpenGenetics\Core\RateLimiter;
use OpenGenetics\Core\Response;

class AuthRegister
{
    public static function post(array $body): void
    {
        $email    = $body['email'] ?? '';
        $password = $body['password'] ?? '';
        $roleId   = 3; // Always EMPLOYEE for self-registration (prevent privilege escalation)
        $tenantId = $body['tenant_id'] ?? null;

        // Rate limit: 3 attempts per 10 minutes per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        RateLimiter::check('register', $ip, 3, 600);

        if (empty($email) || empty($password)) {
            Response::error('Email and password are required', 422);
        }

        if (strlen($password) < 8) {
            Response::error('Password must be at least 8 characters', 422);
        }

        try {
            $result = AuthService::register($email, $password, $roleId, $tenantId);

            // Log registration
            AuditLog::log(AuditLog::CREATE, 'users', [
                'email'   => $email,
                'role_id' => $roleId,
            ], (int) $result['user']['id']);

            Response::success($result, 'Registration successful', 201);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), (int) $e->getCode() ?: 400);
        }
    }
}
