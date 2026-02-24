<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Global Error & Exception Handler
 *
 * Catches all uncaught exceptions and PHP errors, returning
 * clean JSON responses instead of raw PHP error output.
 * In production (APP_DEBUG=false), sensitive details are hidden.
 *
 * Features:
 *  - File-based daily rotating logs: storage/logs/app-YYYY-MM-DD.log
 *  - Custom reporter hook: ErrorHandler::reporter(fn($e) => ...)
 */
final class ErrorHandler
{
    /** @var callable|null Custom error reporter (e.g. Sentry, Bugsnag) */
    private static $reporter = null;

    /** @var string Resolved log directory (auto-created on first write) */
    private static string $logDir = '';

    /**
     * Register the global error and exception handlers.
     */
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Register a custom error reporter callback.
     * Called for every exception alongside file logging.
     *
     * ErrorHandler::reporter(function (\Throwable $e): void {
     *     // e.g., send to Sentry, Bugsnag, Slack
     * });
     */
    public static function reporter(callable $callback): void
    {
        self::$reporter = $callback;
    }

    /**
     * Handle uncaught exceptions.
     */
    public static function handleException(\Throwable $e): void
    {
        $code = (int)$e->getCode();
        $code = ($code >= 400 && $code < 600) ? $code : 500;

        // Always write to the daily rotating log file
        self::writeLog($e);

        // Fire custom reporter — must never crash the HTTP response
        if (self::$reporter !== null) {
            try {
                $reporter = self::$reporter;
                $reporter($e);
            } catch (\Throwable) {
                // Silently swallow reporter failures
            }
        }

        $payload = [
            'success' => false,
            'message' => Env::isDebug() ? $e->getMessage() : self::httpMessage($code),
        ];

        if (Env::isDebug()) {
            $payload['debug'] = [
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => array_slice(
                    array_map(fn($t) => [
                        'file'     => $t['file'] ?? '(internal)',
                        'line'     => $t['line'] ?? 0,
                        'function' => ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? ''),
                    ], $e->getTrace()),
                    0, 10
                ),
            ];
        }

        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── File Logging ──────────────────────────────────────

    /**
     * Append an exception entry to the daily log file.
     * Path: storage/logs/app-2026-02-24.log
     */
    private static function writeLog(\Throwable $e): void
    {
        try {
            $dir   = self::logDirectory();
            $file  = $dir . '/app-' . date('Y-m-d') . '.log';
            $level = ((int)$e->getCode() >= 400 && (int)$e->getCode() < 500) ? 'WARNING' : 'ERROR';
            $ts    = date('Y-m-d H:i:s');
            $entry = "[{$ts}] {$level} " . $e::class . ": {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
        } catch (\Throwable) {
            // File logging must never break the HTTP response
            error_log("[OpenGenetics] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
        }
    }

    private static function logDirectory(): string
    {
        if (self::$logDir === '') {
            self::$logDir = dirname(__DIR__, 2) . '/storage/logs';
        }
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        return self::$logDir;
    }

    /**
     * Convert PHP errors to ErrorException.
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 500, $severity, $file, $line);
    }

    /**
     * Handle fatal errors on shutdown.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            self::handleException(
                new \ErrorException($error['message'], 500, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * Map HTTP status code to a standard message.
     */
    private static function httpMessage(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            default => 'Internal Server Error',
        };
    }
}
