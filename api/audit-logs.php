<?php

/**
 * 🧬 GET /api/audit-logs
 * 
 * Query audit logs (Admin only).
 * Supports filtering by user, action, and entity.
 */

use OpenGenetics\Auth\Guard;
use OpenGenetics\Audit\AuditLog;
use OpenGenetics\Core\Response;

class AuditLogs
{
    public static function get(array $body): void
    {
        // Only ADMIN can view audit logs
        Guard::requireRole(Guard::ADMIN);

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 50)));
        $userId  = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;
        $action  = $_GET['action'] ?? null;
        $entity  = $_GET['entity'] ?? null;
        $offset  = ($page - 1) * $perPage;

        $total = AuditLog::count($userId, $action, $entity);
        $logs  = AuditLog::query($userId, $action, $entity, $perPage, $offset);

        // Parse JSON payloads
        foreach ($logs as &$log) {
            if (!empty($log['payload'])) {
                $log['payload'] = json_decode($log['payload'], true);
            }
        }

        Response::paginated($logs, $total, $page, $perPage);
    }
}
