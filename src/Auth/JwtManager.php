<?php

declare(strict_types=1);

namespace OpenGenetics\Auth;

use OpenGenetics\Core\Env;

/**
 * 🧬 OpenGenetics — JWT Token Manager (Hybrid)
 * 
 * Uses firebase/php-jwt when available (recommended).
 * Falls back to native PHP hash_hmac() if library is missing.
 * Algorithm: HS256 (HMAC-SHA256)
 */
final class JwtManager
{
    private static ?bool $hasFirebaseJwt = null;
    private static ?string $cachedSecret = null;

    /**
     * Generate a JWT token for a user.
     */
    public static function encode(array $user): string
    {
        $secret     = self::secret();
        $expiration = (int) Env::get('JWT_EXPIRATION', '86400');

        $payload = [
            'iss'  => 'open-genetics',
            'iat'  => time(),
            'exp'  => time() + $expiration,
            'sub'  => $user['id'],
            'data' => [
                'id'        => $user['id'],
                'email'     => $user['email'],
                'role_id'   => $user['role_id'],
                'role_name' => $user['role_name'] ?? null,
                'tenant_id' => $user['tenant_id'] ?? null,
            ],
        ];

        // Use firebase/php-jwt if available
        if (self::hasFirebaseJwt()) {
            return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
        }

        // Native fallback
        return self::nativeEncode($payload, $secret);
    }

    /**
     * Decode and validate a JWT token.
     * Returns the payload data or null if invalid/expired.
     */
    public static function decode(string $token): ?array
    {
        $secret = self::secret();

        try {
            // Use firebase/php-jwt if available
            if (self::hasFirebaseJwt()) {
                $decoded = \Firebase\JWT\JWT::decode(
                    $token,
                    new \Firebase\JWT\Key($secret, 'HS256')
                );
                return (array) $decoded->data;
            }

            // Native fallback
            return self::nativeDecode($token, $secret);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Extract token from Authorization header.
     */
    public static function extractFromHeader(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Authenticate the current request.
     * Returns user data or null if not authenticated.
     */
    public static function authenticate(): ?array
    {
        $token = self::extractFromHeader();

        if ($token === null) {
            return null;
        }

        return self::decode($token);
    }

    // ─── Native JWT Implementation (Fallback) ────────────

    /**
     * Encode JWT using native PHP hash_hmac.
     */
    private static function nativeEncode(array $payload, string $secret): string
    {
        $header  = self::base64url(['alg' => 'HS256', 'typ' => 'JWT']);
        $body    = self::base64url($payload);
        $sig     = self::sign("{$header}.{$body}", $secret);

        return "{$header}.{$body}.{$sig}";
    }

    /**
     * Decode JWT using native PHP hash_hmac.
     */
    private static function nativeDecode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature (timing-safe comparison)
        if (!hash_equals(self::sign("{$header}.{$payload}", $secret), $signature)) {
            return null;
        }

        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        if (!is_array($data)) {
            return null;
        }

        // Check expiration
        if (isset($data['exp']) && $data['exp'] < time()) {
            return null;
        }

        return $data['data'] ?? null;
    }

    private static function sign(string $input, string $secret): string
    {
        return rtrim(strtr(base64_encode(
            hash_hmac('sha256', $input, $secret, true)
        ), '+/', '-_'), '=');
    }

    private static function base64url(array $data): string
    {
        return rtrim(strtr(base64_encode(
            json_encode($data, JSON_UNESCAPED_UNICODE)
        ), '+/', '-_'), '=');
    }

    /**
     * Get cached JWT secret.
     */
    private static function secret(): string
    {
        if (self::$cachedSecret === null) {
            $secret = Env::get('JWT_SECRET', '');
            if (strlen($secret) < 32) {
                throw new \RuntimeException(
                    'JWT_SECRET must be set and at least 32 characters. Run: php genetics mutate'
                );
            }
            self::$cachedSecret = $secret;
        }
        return self::$cachedSecret;
    }

    /**
     * Check if firebase/php-jwt is available (cached).
     */
    private static function hasFirebaseJwt(): bool
    {
        if (self::$hasFirebaseJwt === null) {
            self::$hasFirebaseJwt = class_exists(\Firebase\JWT\JWT::class);
        }
        return self::$hasFirebaseJwt;
    }
}
