<?php

declare(strict_types=1);

namespace OpenGenetics\Middleware;

/**
 * 🧬 OpenGenetics — Request/Response Logger Middleware
 *
 * Logs method, URI, status, and duration of every request.
 * Writes to storage/logs/access-YYYY-MM-DD.log
 *
 * Register globally:
 *   Pipeline::alias('log', LogMiddleware::class);
 *   Pipeline::addGlobal(LogMiddleware::class);
 *
 * Or per-endpoint:
 *   #[Middleware('log')]
 */
final class LogMiddleware
{
    private static string $logDir = '';

    public function handle(array $request, callable $next, string ...$_params): void
    {
        $start  = hrtime(true);
        $method = str_replace(["\r", "\n"], ['', ''], $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
        $uri    = str_replace(["\r", "\n"], ['\\r', '\\n'], $_SERVER['REQUEST_URI'] ?? '/');
        $ip     = str_replace(["\r", "\n"], ['', ''], $_SERVER['REMOTE_ADDR'] ?? '-');

        $next($request);

        $ms     = round((hrtime(true) - $start) / 1_000_000, 2);
        $status = http_response_code() ?: 200;

        $this->writeLog($method, $uri, $ip, $status, $ms);
    }

    private function writeLog(string $method, string $uri, string $ip, int $status, float $ms): void
    {
        try {
            $dir   = $this->logDirectory();
            $file  = $dir . '/access-' . date('Y-m-d') . '.log';
            $ts    = date('Y-m-d H:i:s');
            $entry = "[{$ts}] {$ip} {$method} {$uri} → {$status} ({$ms}ms)" . PHP_EOL;
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
        } catch (\Throwable) {
            // Log failure must never break the HTTP response
        }
    }

    private function logDirectory(): string
    {
        if (self::$logDir === '') {
            self::$logDir = dirname(__DIR__, 2) . '/storage/logs';
        }
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        return self::$logDir;
    }
}
