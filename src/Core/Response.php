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
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Send an error response.
     *
     * @param string      $message Human-readable error message
     * @param int         $status  HTTP status code (default 400)
     * @param mixed       $errors  Field-level validation errors or extra detail
     * @param string|null $code    Machine-readable error code (e.g. ERR_USER_NOT_FOUND)
     *
     * Response::error('User not found', 404, code: 'ERR_USER_NOT_FOUND');
     * // → {"success":false,"message":"User not found","code":"ERR_USER_NOT_FOUND"}
     */
    public static function error(
        string  $message,
        int     $status = 400,
        mixed   $errors = null,
        ?string $code   = null
    ): never {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($code !== null) {
            $payload['code'] = $code;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        self::json($payload, $status);
    }

    /**
     * Send a paginated response.
     *
     * Accepts either flat args or a QueryBuilder/Database::paginate() result:
     *   Response::paginatedFrom(DB::table('users')->paginate(20));
     *   Response::paginated($rows, $total, $page, $perPage);
     */
    public static function paginated(array $data, int $total, int $page, int $perPage): never
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

    /**
     * Send a paginated response from a QueryBuilder or Database::paginate() result.
     *
     * $result = DB::table('users')->where('active', 1)->paginate(20);
     * Response::paginatedFrom($result);
     *
     * @param array{data: array, meta: array} $result
     */
    public static function paginatedFrom(array $result, string $message = 'OK'): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $result['data'] ?? [],
            'meta'    => $result['meta'] ?? [],
        ]);
    }
}
