<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — JSON Response Helper
 *
 * Standardized JSON responses for the entire framework.
 */
final class Response
{
    /**
     * Send a JSON response and terminate.
     */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send a 201 Created response.
     */
    public static function created(mixed $data = null, string $message = 'Created'): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 201);
    }

    /**
     * Send a 204 No Content response.
     */
    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }

    /**
     * Send a success response.
     */
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Send an error response.
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        self::json($payload, $status);
    }

    /**
     * Send a paginated response.
     */
    public static function paginated(array $data, int $total, int $page, int $perPage): void
    {
        $totalPages = (int) ceil($total / max($perPage, 1));
        self::json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages,
                'last_page'   => $totalPages,
                'has_more'    => $page < $totalPages,
            ],
        ]);
    }
}
