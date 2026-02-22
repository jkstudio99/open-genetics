<?php

/**
 * 🧬 GET /api/users
 * 
 * List all users (Admin/HR only).
 * Supports pagination and search.
 */

use OpenGenetics\Auth\Guard;
use OpenGenetics\Core\Database;
use OpenGenetics\Core\Response;

class Users
{
    public static function get(array $body): void
    {
        // Only ADMIN and HR can view user list
        Guard::requireRole(Guard::ADMIN, Guard::HR);

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 20)));
        $search  = $_GET['search'] ?? '';
        $offset  = ($page - 1) * $perPage;

        $where  = [];
        $params = [];

        if (!empty($search)) {
            $where[]          = 'u.email LIKE :search';
            $params['search'] = "%{$search}%";
        }

        // Multi-tenant isolation
        $tenantId = Guard::tenantId();
        if ($tenantId !== null) {
            $where[]             = 'u.tenant_id = :tenant_id';
            $params['tenant_id'] = $tenantId;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $countRow = Database::queryOne(
            "SELECT COUNT(*) as total FROM users u {$whereClause}",
            $params
        );
        $total = (int) ($countRow['total'] ?? 0);

        // Get users (intval guards against SQL injection in LIMIT/OFFSET)
        $perPage = intval($perPage);
        $offset  = intval($offset);

        $users = Database::query(
            "SELECT u.id, u.email, u.role_id, r.role_name, u.is_active, u.tenant_id, u.created_at
             FROM users u
             JOIN roles r ON r.id = u.role_id
             {$whereClause}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        Response::paginated($users, $total, $page, $perPage);
    }
}
