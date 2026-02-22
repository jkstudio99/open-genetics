<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Global Error & Exception Handler
 *
 * Catches all uncaught exceptions and PHP errors, returning
 * clean JSON responses instead of raw PHP error output.
 * In production (APP_DEBUG=false), sensitive details are hidden.
 */
final class ErrorHandler
{
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
     * Handle uncaught exceptions.
     */
    public static function handleException(\Throwable $e): void
    {
        $code = $e->getCode();
        $code = ($code >= 400 && $code < 600) ? $code : 500;

        $payload = [
            'success' => false,
            'message' => Env::isDebug() ? $e->getMessage() : self::httpMessage($code),
        ];

        if (Env::isDebug()) {
            $payload['debug'] = [
                'exception' => get_class($e),
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

        // Log to error_log in production
        if (!Env::isDebug()) {
            error_log("[OpenGenetics] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
        }

        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
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
