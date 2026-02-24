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
 *       ->select('id', 'email', 'role_name')
 *       ->where(['is_active' => 1, 'role_name' => 'ADMIN'])
 *       ->sort('-created_at')
 *       ->paginate(20);
 *
 *   $user = DB::table('users')->find(3);
 *   $user = DB::find('users', 3);
 *
 *   DB::table('users')
 *       ->where(['status' => 'open', 'priority' => ['high', 'critical']])
 *       ->when($search, fn($q) => $q->search(['name', 'email'], $search))
 *       ->sort('-created_at')
 *       ->paginate(20);
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
     * select('id', 'email', 'name')           → variadic strings
     * select(['id', 'email', 'name'])          → array (backward compatible)
     * select('id')                             → single column
     */
    public function select(array|string ...$columns): static
    {
        if (count($columns) === 1 && is_array($columns[0])) {
            $this->selects = $columns[0];
        } else {
            $this->selects = $columns;
        }
        return $this;
    }

    // ─── WHERE ─────────────────────────────────────────────

    /**
     * Add WHERE clauses. Chainable. Supports multiple calling styles:
     *
     * where('status', 'active')                → WHERE status = ?
     * where('age', '>=', 18)                   → WHERE age >= ?
     * where('age >=', 18)                      → WHERE age >= ?  (operator-in-key)
     * where('deleted_at', null)                → WHERE deleted_at IS NULL
     * where(['status' => 'open', ...])         → array-based (operator-in-key, auto IN/NULL)
     */
    public function where(string|array $column, mixed $operator = '__MISSING__', mixed $value = '__MISSING__'): static
    {
        if (is_array($column)) {
            return $this->whereArray($column);
        }

        if ($operator === '__MISSING__') {
            throw new \InvalidArgumentException('QueryBuilder: where() requires at least 2 arguments for string column');
        }

        if ($value === '__MISSING__') {
            $parsed = self::parseOperatorFromKey($column);
            if ($parsed) {
                return $this->addWhere($parsed['column'], $parsed['operator'], $operator);
            }
            return $this->addWhere($column, '=', $operator);
        }

        return $this->addWhere($column, (string) $operator, $value);
    }

    /**
     * Process an array of conditions.
     *
     * where([
     *     'status'     => 'active',              // = (default)
     *     'priority'   => ['high', 'critical'],   // auto IN()
     *     'age >='     => 18,                     // operator-in-key
     *     'deleted_at' => null,                   // auto IS NULL
     *     'email !'    => null,                   // IS NOT NULL
     * ])
     */
    private function whereArray(array $conditions): static
    {
        foreach ($conditions as $key => $val) {
            $parsed = self::parseOperatorFromKey($key);
            $col = $parsed ? $parsed['column'] : $key;
            $op  = $parsed ? $parsed['operator'] : '=';

            if ($val === null) {
                if ($op === '!' || $op === '!=') {
                    $this->wheres[] = self::quoteRef($col) . ' IS NOT NULL';
                } else {
                    $this->wheres[] = self::quoteRef($col) . ' IS NULL';
                }
            } elseif (is_array($val)) {
                if (empty($val)) {
                    $this->wheres[] = '1 = 0';
                } else {
                    $placeholders = implode(', ', array_fill(0, count($val), '?'));
                    $this->wheres[]  = self::quoteRef($col) . " IN ({$placeholders})";
                    $this->bindings  = array_merge($this->bindings, array_values($val));
                }
            } else {
                $this->wheres[]   = self::quoteRef($col) . " {$op} ?";
                $this->bindings[] = $val;
            }
        }
        return $this;
    }

    /**
     * Internal: add a single where condition.
     */
    private function addWhere(string $column, string $operator, mixed $value): static
    {
        if ($value === null) {
            if ($operator === '!' || $operator === '!=') {
                $this->wheres[] = self::quoteRef($column) . ' IS NOT NULL';
            } else {
                $this->wheres[] = self::quoteRef($column) . ' IS NULL';
            }
        } elseif (is_array($value)) {
            if (empty($value)) {
                $this->wheres[] = '1 = 0';
            } else {
                $placeholders = implode(', ', array_fill(0, count($value), '?'));
                $this->wheres[]  = self::quoteRef($column) . " IN ({$placeholders})";
                $this->bindings  = array_merge($this->bindings, array_values($value));
            }
        } else {
            static $allowedOps = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'];
            $opUpper = strtoupper($operator);
            if (!\in_array($opUpper, $allowedOps, true)) {
                throw new \InvalidArgumentException("QueryBuilder: invalid operator '{$operator}'");
            }
            $this->wheres[]   = self::quoteRef($column) . " {$operator} ?";
            $this->bindings[] = $value;
        }
        return $this;
    }

    /**
     * Parse operator from key string. e.g. 'age >=' → ['column' => 'age', 'operator' => '>=']
     * Returns null if no operator found in the key.
     */
    private static function parseOperatorFromKey(string $key): ?array
    {
        if (preg_match('/^(.+?)\s+(!=|<>|>=|<=|>|<|=|!|LIKE|NOT LIKE)$/i', $key, $m)) {
            return ['column' => trim($m[1]), 'operator' => $m[2]];
        }
        return null;
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
        $op = $operator === '=' ? '=' : throw new \InvalidArgumentException("QueryBuilder: join operator must be '='");
        // Columns must be simple table.column — backtick each part
        $this->joins[] = "INNER JOIN `{$table}` ON " . self::quoteColumn($first) . " {$op} " . self::quoteColumn($second);
        return $this;
    }

    /**
     * Add a LEFT JOIN.
     *
     * leftJoin('categories', 'tickets.category_id', '=', 'categories.id')  → 4-arg (original)
     * leftJoin('categories AS c', 'c.id', 't.category_id')                → 3-arg (new shortcut, = implied)
     */
    public function leftJoin(string $table, string $first, string $operatorOrSecond, ?string $second = null): static
    {
        if ($second === null) {
            $this->joins[] = "LEFT JOIN {$this->quoteTableRef($table)} ON " . self::quoteColumn($first) . " = " . self::quoteColumn($operatorOrSecond);
        } else {
            $op = $operatorOrSecond === '=' ? '=' : throw new \InvalidArgumentException("QueryBuilder: join operator must be '='");
            $this->joins[] = "LEFT JOIN `{$table}` ON " . self::quoteColumn($first) . " {$op} " . self::quoteColumn($second);
        }
        return $this;
    }

    /** Quote a table.column reference safely. */
    private static function quoteColumn(string $ref): string
    {
        $parts = explode('.', $ref, 2);
        return implode('.', array_map(fn($p) => '`' . str_replace('`', '', trim($p)) . '`', $parts));
    }

    /**
     * Quote a column or table.column reference, supporting aliases (AS).
     * 't.created_at' → `t`.`created_at`
     * 'created_at'   → `created_at`
     */
    private static function quoteRef(string $ref): string
    {
        if (str_contains($ref, '.')) {
            return self::quoteColumn($ref);
        }
        return '`' . str_replace('`', '', $ref) . '`';
    }

    /**
     * Quote a table reference that may have an alias.
     * 'users AS u' → `users` AS `u`
     * 'categories' → `categories`
     */
    private function quoteTableRef(string $table): string
    {
        if (preg_match('/^(.+?)\s+AS\s+(.+)$/i', $table, $m)) {
            return '`' . trim($m[1]) . '` AS `' . trim($m[2]) . '`';
        }
        return '`' . $table . '`';
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

    // ─── CONDITIONAL / SEARCH / SORT (OpenGenetics exclusive) ─────

    /**
     * Conditional filter — apply only when $condition is truthy.
     *
     * when($status, ['status' => $status])                    → array filter
     * when($search, fn($q) => $q->search(['name'], $search)) → closure
     */
    public function when(mixed $condition, array|\Closure $filter): static
    {
        if (!$condition) {
            return $this;
        }

        if ($filter instanceof \Closure) {
            $filter($this);
        } else {
            $this->whereArray($filter);
        }

        return $this;
    }

    /**
     * Multi-column LIKE search. Skips if $keyword is empty.
     *
     * search(['subject', 'ticket_number'], $keyword)
     * → WHERE (subject LIKE ? OR ticket_number LIKE ?)
     */
    public function search(array $columns, ?string $keyword): static
    {
        if ($keyword === null || $keyword === '') {
            return $this;
        }

        $parts = [];
        foreach ($columns as $col) {
            $parts[] = self::quoteRef($col) . ' LIKE ?';
            $this->bindings[] = '%' . $keyword . '%';
        }

        $this->wheres[] = '(' . implode(' OR ', $parts) . ')';
        return $this;
    }

    /**
     * Sort with prefix notation. '-' = DESC, no prefix = ASC.
     *
     * sort('-created_at')              → ORDER BY created_at DESC
     * sort('name')                     → ORDER BY name ASC
     * sort('-priority', '-created_at') → ORDER BY priority DESC, created_at DESC
     */
    public function sort(string ...$columns): static
    {
        foreach ($columns as $col) {
            if (str_starts_with($col, '-')) {
                $this->orders[] = self::quoteRef(substr($col, 1)) . ' DESC';
            } else {
                $colName = str_starts_with($col, '+') ? substr($col, 1) : $col;
                $this->orders[] = self::quoteRef($colName) . ' ASC';
            }
        }
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
        // Use COUNT(DISTINCT pk) when JOINs are present to avoid double-counting
        $countExpr    = empty($this->joins)
            ? 'COUNT(*) as aggregate'
            : "COUNT(DISTINCT `{$this->table}`.`id`) as aggregate";
        $this->selects = [$countExpr];
        $sql           = $this->buildSelect(omitOrderLimit: true);
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
     * Find a single row by primary key.
     *
     * DB::table('users')->find(3)  → SELECT * WHERE id = 3 LIMIT 1
     */
    public function find(int|string $id, string $primaryKey = 'id'): ?array
    {
        return $this->where($primaryKey, $id)->first();
    }

    /**
     * Find all rows matching a column value.
     *
     * DB::table('users')->findAll('role_name', 'ADMIN')
     */
    public function findAll(string $column, mixed $value): array
    {
        return $this->where($column, $value)->get();
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

    /**
     * Return the compiled SQL string (for debugging).
     */
    public function toSql(): string
    {
        return $this->buildSelect();
    }

    /**
     * Return the current bindings (for debugging).
     *
     * @return array<mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}

/**
 * Convenience alias for QueryBuilder + static one-liner shortcuts.
 *
 * Usage:
 *   DB::table('users')->where(['active' => 1])->get();
 *   DB::find('users', 3);
 *   DB::insert('users', ['name' => 'Jo']);
 */
final class DB
{
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder($table);
    }

    /**
     * Find a single row by primary key.
     *
     * DB::find('users', 3)
     * DB::find('users', 3, ['id', 'email'])
     */
    public static function find(string $table, int|string $id, ?array $columns = null): ?array
    {
        $q = new QueryBuilder($table);
        if ($columns) {
            $q->select($columns);
        }
        return $q->find($id);
    }

    /**
     * Find all rows matching conditions.
     *
     * DB::findAll('users', ['is_active' => 1])
     * DB::findAll('users', ['role_name' => 'ADMIN'], ['id', 'email'])
     */
    public static function findAll(string $table, array $conditions = [], ?array $columns = null): array
    {
        $q = new QueryBuilder($table);
        if ($columns) {
            $q->select($columns);
        }
        if (!empty($conditions)) {
            $q->where($conditions);
        }
        return $q->get();
    }

    /**
     * Get a single column value from the first matching row.
     *
     * DB::value('users', 'email', ['id' => 3])  → 'jo@test.com'
     */
    public static function value(string $table, string $column, array $conditions): mixed
    {
        $row = (new QueryBuilder($table))
            ->select($column)
            ->where($conditions)
            ->first();

        return $row[$column] ?? null;
    }

    /**
     * Insert a row. Returns the new ID.
     *
     * DB::insert('users', ['name' => 'Jo', 'email' => 'j@test.com'])
     */
    public static function insert(string $table, array $data): string
    {
        return (new QueryBuilder($table))->insert($data);
    }

    /**
     * Update rows matching conditions. Returns affected row count.
     *
     * DB::update('users', ['name' => 'New'], ['id' => 3])
     */
    public static function update(string $table, array $data, array $conditions): int
    {
        return (new QueryBuilder($table))->where($conditions)->update($data);
    }

    /**
     * Hard-delete rows matching conditions.
     *
     * DB::delete('users', ['id' => 3])
     */
    public static function delete(string $table, array $conditions): int
    {
        return (new QueryBuilder($table))->where($conditions)->delete();
    }

    /**
     * Soft-delete rows (set deleted_at = NOW()).
     *
     * DB::softDelete('users', ['id' => 3])
     */
    public static function softDelete(string $table, array $conditions): int
    {
        return (new QueryBuilder($table))->where($conditions)->softDelete();
    }

    /**
     * Check if any row exists matching conditions.
     *
     * DB::exists('users', ['email' => 'j@test.com'])
     */
    public static function exists(string $table, array $conditions): bool
    {
        return (new QueryBuilder($table))->where($conditions)->exists();
    }

    /**
     * Count rows matching conditions.
     *
     * DB::count('users', ['is_active' => 1])
     */
    public static function count(string $table, array $conditions = []): int
    {
        $q = new QueryBuilder($table);
        if (!empty($conditions)) {
            $q->where($conditions);
        }
        return $q->count();
    }
}
