<?php

declare(strict_types=1);

namespace OpenGenetics\Audit;

use OpenGenetics\Core\Database;
use OpenGenetics\Auth\Guard;

/**
 * 🧬 OpenGenetics — Audit Trail Logger
 *
 * Automatically logs CREATE, UPDATE, DELETE activities.
 * Non-blocking design: failures are silently logged, never thrown.
 */
final class AuditLog
{
    public const CREATE = 'CREATE';
    public const UPDATE = 'UPDATE';
    public const DELETE = 'DELETE';
    public const LOGIN  = 'LOGIN';
    public const LOGOUT = 'LOGOUT';

    /**
     * Log an activity.
     *
     * @param string      $action  One of CREATE, UPDATE, DELETE, LOGIN, LOGOUT
     * @param string      $entity  Entity name (e.g., "users", "roles")
     * @param array|null  $payload Additional data to log
     * @param int|null    $userId  Override user ID (defaults to current auth user)
     */
    public static function log(
        string $action,
        string $entity,
        ?array $payload = null,
        ?int   $userId = null
    ): void {
        try {
            $userId = $userId ?? Guard::userId();

            Database::execute(
                "INSERT INTO audit_logs (user_id, action, entity, payload, ip_address, user_agent, created_at)
                 VALUES (:user_id, :action, :entity, :payload, :ip, :ua, NOW())",
                [
                    'user_id' => $userId,
                    'action'  => $action,
                    'entity'  => $entity,
                    'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
                    'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
                    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            // Non-blocking: log to error_log but don't disrupt the request
            error_log("[OpenGenetics AuditLog] Failed: " . $e->getMessage());
        }
    }

    /**
     * Query audit logs with optional filters.
     */
    public static function query(
        ?int    $userId = null,
        ?string $action = null,
        ?string $entity = null,
        int     $limit  = 50,
        int     $offset = 0
    ): array {
        [$whereClause, $params] = self::buildWhere($userId, $action, $entity);

        $limit  = intval($limit);
        $offset = intval($offset);

        $sql = "SELECT al.*, u.email as user_email
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id
                {$whereClause}
                ORDER BY al.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        return Database::query($sql, $params);
    }

    /**
     * Count total audit logs with filters.
     */
    public static function count(
        ?int    $userId = null,
        ?string $action = null,
        ?string $entity = null
    ): int {
        [$whereClause, $params] = self::buildWhere($userId, $action, $entity);

        $row = Database::queryOne(
            "SELECT COUNT(*) as total FROM audit_logs {$whereClause}",
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Build shared WHERE clause for query() and count().
     *
     * @return array{0: string, 1: array}
     */
    private static function buildWhere(?int $userId, ?string $action, ?string $entity): array
    {
        $where  = [];
        $params = [];

        if ($userId !== null) {
            $where[]           = 'user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($action !== null) {
            $where[]          = 'action = :action';
            $params['action'] = $action;
        }

        if ($entity !== null) {
            $where[]          = 'entity = :entity';
            $params['entity'] = $entity;
        }

        $clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        return [$clause, $params];
    }
}
