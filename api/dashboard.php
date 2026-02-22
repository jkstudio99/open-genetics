<?php

/**
 * 🧬 GET /api/dashboard
 * 
 * Dashboard statistics — requires authentication.
 * Returns user counts, recent activity, and system overview.
 */

use OpenGenetics\Auth\Guard;
use OpenGenetics\Core\Database;
use OpenGenetics\Audit\AuditLog;
use OpenGenetics\Core\Response;

class Dashboard
{
    public static function get(array $body): void
    {
        Guard::requireAuth();

        // Total users
        $totalUsers = Database::queryOne("SELECT COUNT(*) as count FROM users")['count'];

        // Active users
        $activeUsers = Database::queryOne(
            "SELECT COUNT(*) as count FROM users WHERE is_active = 1"
        )['count'];

        // Users by role
        $usersByRole = Database::query(
            "SELECT r.role_name, COUNT(u.id) as count
             FROM roles r
             LEFT JOIN users u ON u.role_id = r.id
             GROUP BY r.id, r.role_name
             ORDER BY r.id"
        );

        // Recent audit logs (last 10)
        $recentLogs = AuditLog::query(null, null, null, 10, 0);

        // Parse JSON payloads in logs
        foreach ($recentLogs as &$log) {
            if (!empty($log['payload'])) {
                $log['payload'] = json_decode($log['payload'], true);
            }
        }

        Response::success([
            'total_users'       => (int) $totalUsers,
            'active_users'      => (int) $activeUsers,
            'users_by_role'     => $usersByRole,
            'recent_activities' => $recentLogs,
        ]);
    }
}
