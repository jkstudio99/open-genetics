<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * FieldSelector — GraphQL-lite Sparse Fieldsets (OpenGenetics v2.0)
 *
 * Allows API clients to request only specific fields via ?fields= query param,
 * reducing response payload without needing a full GraphQL implementation.
 *
 * Usage in endpoints:
 *   GET /api/users?fields=id,name,email
 *   GET /api/products?fields=id,name,price,category.name
 *
 * In your endpoint:
 *   $users = Database::query("SELECT * FROM users");
 *   Response::success(FieldSelector::apply($users));
 *
 *   // Single item
 *   $user = Database::queryOne("SELECT * FROM users WHERE id = :id", ['id' => 1]);
 *   Response::success(FieldSelector::applyOne($user));
 *
 *   // Explicit allowed fields (whitelist)
 *   FieldSelector::allow(['id', 'name', 'email', 'role_name']);
 *   Response::success(FieldSelector::apply($users));
 */
final class FieldSelector
{
    private static ?array $allowedFields = null;

    // ── Configuration ─────────────────────────────────────

    /**
     * Whitelist allowed fields to prevent information leakage.
     * Whitelist is automatically cleared after apply()/applyOne()/respond().
     *
     * @param array $fields Allowed field names
     */
    public static function allow(array $fields): void
    {
        self::$allowedFields = array_map('strtolower', $fields);
    }

    /**
     * Reset whitelist (auto-called after apply — safe to call manually too).
     */
    public static function reset(): void
    {
        self::$allowedFields = null;
    }

    // ── Parse ?fields= from request ──────────────────────

    /**
     * Get the requested fields from the current HTTP request.
     * Returns null if no ?fields= param was provided (return all).
     *
     * Supports dot-notation for nested: ?fields=id,category.name,tags.title
     */
    public static function requested(): ?array
    {
        $raw = $_GET['fields'] ?? null;
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $fields = array_map('trim', explode(',', $raw));
        $fields = array_filter($fields);
        $fields = array_values($fields);

        // Apply whitelist if set
        if (self::$allowedFields !== null) {
            $fields = array_filter($fields, function (string $f) {
                $base = strtolower(explode('.', $f)[0]);
                return in_array($base, self::$allowedFields, true);
            });
            $fields = array_values($fields);
        }

        return empty($fields) ? null : $fields;
    }

    // ── Apply field selection ─────────────────────────────

    /**
     * Apply field selection to an array of rows.
     * If no ?fields= param, returns original data.
     *
     * @param array $rows Collection of records
     * @return array Filtered records
     */
    public static function apply(array $rows): array
    {
        $fields = self::requested();
        $result = $fields === null
            ? $rows
            : array_map(fn(array $row) => self::filterRow($row, $fields), $rows);
        self::reset();
        return $result;
    }

    /**
     * Apply field selection to a single record.
     */
    public static function applyOne(array $row): array
    {
        $fields = self::requested();
        $result = $fields === null ? $row : self::filterRow($row, $fields);
        self::reset();
        return $result;
    }

    /**
     * Apply to custom data (already structured for response).
     */
    public static function applyTo(mixed $data, ?array $fields = null): mixed
    {
        $fields ??= self::requested();
        if ($fields === null || !is_array($data)) {
            return $data;
        }

        if (isset($data[0]) && is_array($data[0])) {
            return array_map(fn($row) => self::filterRow($row, $fields), $data);
        }

        return self::filterRow($data, $fields);
    }

    // ── Internal filtering ────────────────────────────────

    /**
     * Filter a single row to only include requested fields.
     * Supports dot-notation: category.name → $row['category']['name']
     */
    private static function filterRow(array $row, array $fields): array
    {
        $result       = [];
        $topLevel     = [];
        $dotFields    = [];

        foreach ($fields as $field) {
            if (str_contains($field, '.')) {
                [$parent, $child] = explode('.', $field, 2);
                $dotFields[$parent][] = $child;
            } else {
                $topLevel[] = $field;
            }
        }

        // Top-level fields
        foreach ($topLevel as $key) {
            if (array_key_exists($key, $row)) {
                $result[$key] = $row[$key];
            }
        }

        // Dot-notation nested fields
        foreach ($dotFields as $parent => $children) {
            if (!array_key_exists($parent, $row)) {
                continue;
            }

            $parentVal = $row[$parent];

            if (is_array($parentVal)) {
                $result[$parent] = array_intersect_key(
                    $parentVal,
                    array_flip($children)
                );
            } elseif (is_object($parentVal)) {
                $nested = [];
                foreach ($children as $child) {
                    if (isset($parentVal->$child)) {
                        $nested[$child] = $parentVal->$child;
                    }
                }
                $result[$parent] = $nested;
            } else {
                // scalar — include as-is
                $result[$parent] = $parentVal;
            }
        }

        return $result;
    }

    // ── Response helper ───────────────────────────────────

    /**
     * Convenience: apply selection and send success response immediately.
     * Replaces: Response::success(FieldSelector::apply($rows))
     *
     * Usage: FieldSelector::respond($rows);
     */
    public static function respond(array $rows, string $message = 'OK', int $status = 200): never
    {
        Response::success(self::apply($rows), $message, $status);
    }

    /**
     * Get meta info about the current field selection request (for debugging).
     */
    public static function info(): array
    {
        $requested = self::requested();
        return [
            'sparse_fieldset' => $requested !== null,
            'fields'          => $requested ?? 'all',
            'whitelist'       => self::$allowedFields ?? 'none',
        ];
    }
}
