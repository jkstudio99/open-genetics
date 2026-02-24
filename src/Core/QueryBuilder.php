<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

use PDO;

/**
 * 🧬 OpenGenetics — Fluent Query Builder
 *
 * Minimal, chainable query builder that always uses prepared statements.
 * Eliminates raw SQL for common patterns without sacrificing performance.
 *
 * Usage:
 *   $users = DB::table('users')
 *       ->select(['id', 'email', 'role_name'])
 *       ->where('is_active', 1)
 *       ->where('tenant_id', $tenantId)
 *       ->orderBy('created_at', 'DESC')
 *       ->paginate(20);
 *
 *   $user = DB::table('users')->where('email', $email)->first();
 *
 *   DB::table('users')
 *       ->where('id', $id)
 *       ->update(['role_name' => 'MANAGER']);
 *
 *   DB::table('users')->where('id', $id)->delete();
 */
final class QueryBuilder
{
    private string $table;
    private array  $selects  = ['*'];
    private array  $wheres   = [];
    private array  $bindings = [];
    private array  $orders   = [];
    private ?int   $limitVal  = null;
    private ?int   $offsetVal = null;
    private array  $joins    = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    // ─── SELECT ────────────────────────────────────────────

    /**
     * Specify columns to select.
     *
     * @param array<string>|string $columns
     */
    public function select(array|string $columns): static
    {
        $this->selects = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    // ─── WHERE ─────────────────────────────────────────────

    /**
     * Add a WHERE clause. Chainable.
     *
     * where('status', 'active')                → WHERE status = ?
     * where('age', '>=', 18)                   → WHERE age >= ?
     * where('deleted_at', null)                → WHERE deleted_at IS NULL
     */
    public function where(string $column, mixed $operator, mixed $value = '__MISSING__'): static
    {
        if ($value === '__MISSING__') {
            // Two-arg form: where('column', 'value') — operator defaults to =
            $value    = $operator;
            $operator = '=';
        }

        if ($value === null) {
            $this->wheres[] = "`{$column}` IS NULL";
        } else {
            $this->wheres[]   = "`{$column}` {$operator} ?";
            $this->bindings[] = $value;
        }

        return $this;
    }

    /**
     * Add a WHERE NOT NULL clause.
     */
    public function whereNotNull(string $column): static
    {
        $this->wheres[] = "`{$column}` IS NOT NULL";
        return $this;
    }

    /**
     * Add a WHERE IN clause.
     *
     * whereIn('status', ['active', 'pending'])
     */
    public function whereIn(string $column, array $values): static
    {
        if (empty($values)) {
            $this->wheres[] = '1 = 0'; // Always false when array is empty
            return $this;
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[]    = "`{$column}` IN ({$placeholders})";
        $this->bindings    = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Add a WHERE LIKE clause.
     *
     * whereLike('name', '%john%')
     */
    public function whereLike(string $column, string $pattern): static
    {
        $this->wheres[]   = "`{$column}` LIKE ?";
        $this->bindings[] = $pattern;
        return $this;
    }

    // ─── JOIN ──────────────────────────────────────────────

    /**
     * Add an INNER JOIN.
     *
     * join('roles', 'users.role_id', '=', 'roles.id')
     */
    public function join(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "INNER JOIN `{$table}` ON {$first} {$operator} {$second}";
        return $this;
    }

    /**
     * Add a LEFT JOIN.
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "LEFT JOIN `{$table}` ON {$first} {$operator} {$second}";
        return $this;
    }

    // ─── ORDER / LIMIT / OFFSET ────────────────────────────

    /**
     * Add an ORDER BY clause.
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction      = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = "`{$column}` {$direction}";
        return $this;
    }

    /**
     * Limit the result set.
     */
    public function limit(int $value): static
    {
        $this->limitVal = max(0, $value);
        return $this;
    }

    /**
     * Skip N rows.
     */
    public function offset(int $value): static
    {
        $this->offsetVal = max(0, $value);
        return $this;
    }

    // ─── FETCH ─────────────────────────────────────────────

    /**
     * Fetch all matching rows.
     *
     * @return array<array<string, mixed>>
     */
    public function get(): array
    {
        $sql    = $this->buildSelect();
        return Database::query($sql, $this->bindings);
    }

    /**
     * Fetch the first matching row, or null.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limitVal = 1;
        $sql = $this->buildSelect();
        return Database::queryOne($sql, $this->bindings);
    }

    /**
     * Count matching rows.
     */
    public function count(): int
    {
        $origSelects  = $this->selects;
        $this->selects = ['COUNT(*) as aggregate'];
        $sql = $this->buildSelect(omitOrderLimit: true);
        $this->selects = $origSelects;

        $row = Database::queryOne($sql, $this->bindings);
        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * Check if any row matches.
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Paginate results. Returns Response-compatible array with data + meta.
     *
     * @return array{data: array, meta: array}
     */
    public function paginate(int $perPage = 20, ?int $page = null): array
    {
        $page    = max(1, $page ?? (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(200, $perPage));

        $total = $this->count();

        $this->limitVal  = $perPage;
        $this->offsetVal = ($page - 1) * $perPage;

        $data       = $this->get();
        $totalPages = (int) ceil($total / $perPage);

        return [
            'data' => $data,
            'meta' => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages,
                'last_page'   => $totalPages,
                'has_more'    => $page < $totalPages,
            ],
        ];
    }

    // ─── MUTATE ────────────────────────────────────────────

    /**
     * Insert a new row. Returns the new ID.
     *
     * DB::table('users')->insert(['email' => 'a@b.com', 'role_name' => 'EMPLOYEE']);
     */
    public function insert(array $data): string
    {
        $columns      = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $cols   = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $vals   = implode(', ', $placeholders);

        $sql = "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$vals})";
        Database::execute($sql, array_values($data));

        return Database::lastInsertId();
    }

    /**
     * Update matching rows.
     *
     * DB::table('users')->where('id', $id)->update(['role_name' => 'ADMIN']);
     */
    public function update(array $data): int
    {
        $setClauses = array_map(fn($col) => "`{$col}` = ?", array_keys($data));
        $set        = implode(', ', $setClauses);
        $params     = array_merge(array_values($data), $this->bindings);

        $where = $this->buildWhere();
        $sql   = "UPDATE `{$this->table}` SET {$set}{$where}";

        return Database::execute($sql, $params);
    }

    /**
     * Soft-delete matching rows (sets deleted_at = NOW()).
     *
     * DB::table('users')->where('id', $id)->softDelete();
     */
    public function softDelete(): int
    {
        return $this->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Restore soft-deleted rows (sets deleted_at = NULL).
     */
    public function restore(): int
    {
        $setClauses = ['`deleted_at` = NULL'];
        $set        = implode(', ', $setClauses);
        $where      = $this->buildWhere();
        $sql        = "UPDATE `{$this->table}` SET {$set}{$where}";

        return Database::execute($sql, $this->bindings);
    }

    /**
     * Exclude soft-deleted rows (WHERE deleted_at IS NULL).
     */
    public function withoutTrashed(): static
    {
        return $this->whereNull('deleted_at');
    }

    /**
     * Include ONLY soft-deleted rows.
     */
    public function onlyTrashed(): static
    {
        return $this->whereNotNull('deleted_at');
    }

    /**
     * Hard-delete matching rows.
     */
    public function delete(): int
    {
        $where = $this->buildWhere();
        $sql   = "DELETE FROM `{$this->table}`{$where}";
        return Database::execute($sql, $this->bindings);
    }

    // ─── PRIVATE BUILDERS ──────────────────────────────────

    private function buildSelect(bool $omitOrderLimit = false): string
    {
        $cols  = implode(', ', $this->selects);
        $sql   = "SELECT {$cols} FROM `{$this->table}`";

        foreach ($this->joins as $join) {
            $sql .= " {$join}";
        }

        $sql .= $this->buildWhere();

        if (!$omitOrderLimit) {
            if (!empty($this->orders)) {
                $sql .= ' ORDER BY ' . implode(', ', $this->orders);
            }
            if ($this->limitVal !== null) {
                $sql .= " LIMIT {$this->limitVal}";
            }
            if ($this->offsetVal !== null) {
                $sql .= " OFFSET {$this->offsetVal}";
            }
        }

        return $sql;
    }

    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $this->wheres);
    }

    /** @alias for whereNotNull */
    private function whereNull(string $column): static
    {
        $this->wheres[] = "`{$column}` IS NULL";
        return $this;
    }
}

/**
 * Convenience alias for QueryBuilder.
 *
 * Usage: DB::table('users')->where('active', 1)->get();
 */
final class DB
{
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder($table);
    }
}
