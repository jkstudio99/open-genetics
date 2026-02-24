<?php

declare(strict_types=1);

namespace OpenGenetics\Auth;

use OpenGenetics\Core\Database;
use OpenGenetics\Core\Env;

/**
 * 🧬 OpenGenetics — Authentication Service
 *
 * Handles user registration, login, and password reset.
 * Uses Bcrypt (12 rounds) for password hashing.
 */
final class AuthService
{
    /**
     * Register a new user.
     *
     * @return array{token: string, user: array}
     * @throws \RuntimeException
     */
    public static function register(string $email, string $password, int $roleId = 3, ?string $tenantId = null): array
    {
        // Validate input
        if (empty($email) || empty($password)) {
            throw new \RuntimeException('Email and password are required', 422);
        }

        if (strlen($password) < 8) {
            throw new \RuntimeException('Password must be at least 8 characters', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid email format', 422);
        }

        // Hash password with Bcrypt
        $cost = (int) Env::get('BCRYPT_COST', '12');
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);

        // Insert user — catch SQLSTATE 23000 (duplicate key) instead of TOCTOU pre-check
        try {
            Database::execute(
                "INSERT INTO users (email, password_hash, role_id, is_active, tenant_id, created_at)
                 VALUES (:email, :password_hash, :role_id, 1, :tenant_id, NOW())",
                [
                    'email'         => $email,
                    'password_hash' => $hash,
                    'role_id'       => $roleId,
                    'tenant_id'     => $tenantId,
                ]
            );
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('Email already registered', 409);
            }
            throw $e;
        }

        $userId = (int) Database::lastInsertId();

        // Fetch the user with role name
        $user = self::findById($userId);

        return [
            'token' => JwtManager::encode($user),
            'user'  => self::sanitize($user),
        ];
    }

    /**
     * Login with email and password.
     *
     * @return array{token: string, user: array}
     * @throws \RuntimeException
     */
    public static function login(string $email, string $password): array
    {
        $user = Database::queryOne(
            "SELECT u.*, r.role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = 1",
            ['email' => $email]
        );

        if ($user === null) {
            throw new \RuntimeException('Invalid credentials', 401);
        }

        if (!password_verify($password, $user['password_hash'])) {
            throw new \RuntimeException('Invalid credentials', 401);
        }

        return [
            'token' => JwtManager::encode($user),
            'user'  => self::sanitize($user),
        ];
    }

    /**
     * Generate a password reset token.
     */
    public static function forgotPassword(string $email): string
    {
        // Generate token regardless of whether email exists (prevent enumeration)
        $rawToken = bin2hex(random_bytes(32));

        $user = Database::queryOne(
            "SELECT id FROM users WHERE email = :email AND is_active = 1",
            ['email' => $email]
        );

        // Only store token if user exists, but always return same response
        if ($user !== null) {
            $hashedToken = hash('sha256', $rawToken);

            // Remove existing tokens for this email
            Database::execute(
                "DELETE FROM password_resets WHERE email = :email",
                ['email' => $email]
            );

            // Insert hashed token (expires in 1 hour)
            Database::execute(
                "INSERT INTO password_resets (email, token, expires_at)
                 VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))",
                ['email' => $email, 'token' => $hashedToken]
            );
        }

        return $rawToken;
    }

    /**
     * Reset password using a valid token.
     */
    public static function resetPassword(string $token, string $newPassword): void
    {
        // Hash the incoming token to compare with stored hash
        $hashedToken = hash('sha256', $token);

        $reset = Database::queryOne(
            "SELECT email FROM password_resets
             WHERE token = :token AND expires_at > NOW()",
            ['token' => $hashedToken]
        );

        if ($reset === null) {
            throw new \RuntimeException('Invalid or expired token', 400);
        }

        if (strlen($newPassword) < 8) {
            throw new \RuntimeException('Password must be at least 8 characters', 422);
        }

        $email = $reset['email'];
        $cost  = (int) Env::get('BCRYPT_COST', '12');
        $hash  = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => $cost]);

        Database::execute(
            "UPDATE users SET password_hash = :hash WHERE email = :email",
            ['hash' => $hash, 'email' => $email]
        );

        Database::execute(
            "DELETE FROM password_resets WHERE email = :email",
            ['email' => $email]
        );
    }

    /**
     * Find a user by ID with their role name.
     */
    public static function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT u.*, r.role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id",
            ['id' => $id]
        );
    }

    /**
     * Remove sensitive data from user array.
     */
    private static function sanitize(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }
}
