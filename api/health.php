<?php

/**
 * 🧬 GET /api/health
 *
 * Health check endpoint — no authentication required.
 * Returns server status, database connectivity, and PHP version.
 */

use OpenGenetics\Core\Database;
use OpenGenetics\Core\Env;
use OpenGenetics\Core\Response;

class Health
{
    public static function get(array $body): void
    {
        $checks = [
            'status'  => 'ok',
            'version' => '1.0.0',
            'php'     => PHP_VERSION,
            'env'     => Env::get('APP_ENV', 'production'),
            'time'    => date('c'),
        ];

        // Database check
        try {
            $pdo = Database::connect();
            $row = $pdo->query("SELECT 1 AS ping")->fetch();
            $checks['database'] = $row ? 'connected' : 'error';
        } catch (\Throwable $e) {
            $checks['database'] = 'disconnected';
            $checks['status']   = 'degraded';
        }

        $status = $checks['status'] === 'ok' ? 200 : 503;
        Response::json($checks, $status);
    }
}
