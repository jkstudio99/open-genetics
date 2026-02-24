<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

use PDO;
use PDOException;

/**
 * 🧬 OpenGenetics — Database Connection (Singleton)
 *
 * Thread-safe PDO wrapper using Prepared Statements only.
 * Prevents SQL Injection at the DNA level of the framework.
 *
 * Quick helpers:
 *   Database::paginate($sql, $countSql, $params, $page, $perPage)
 *   Database::softDelete($table, $id)
 *   Database::table($table)  → QueryBuilder (fluent API)
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    /**
     * Get or create the PDO singleton connection.
     */
    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $name = Env::get('DB_NAME', 'open-genetics-db');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Real prepared statements
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Execute a prepared query and return all rows.
     */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a prepared query and return the first row or null.
     * More efficient than query()[0] ?? null — uses FETCH mode directly.
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute a prepared statement (INSERT/UPDATE/DELETE) and return affected rows.
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Get the last inserted ID.
     */
    public static function lastInsertId(): string
    {
        return self::connect()->lastInsertId();
    }

    /**
     * Run a callback inside a transaction.
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connect();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Check if any row matches the query.
     */
    public static function exists(string $sql, array $params = []): bool
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Paginate raw SQL — runs data query + count query, returns data + meta.
     *
     * @param string $sql      Main SELECT query (no LIMIT/OFFSET)
     * @param string $countSql COUNT(*) version of the same query
     * @param array  $params   Shared parameters for both queries
     * @return array{data: array, meta: array}
     */
    public static function paginate(
        string $sql,
        string $countSql,
        array  $params   = [],
        int    $page     = 1,
        int    $perPage  = 20
    ): array {
        $page    = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset  = ($page - 1) * $perPage;

        $total      = (int) (self::queryOne($countSql, $params)['total'] ?? 0);
        $data       = self::query($sql . " LIMIT {$perPage} OFFSET {$offset}", $params);
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

    /**
     * Soft-delete a row by setting deleted_at = NOW().
     * Requires a `deleted_at` column on the table.
     */
    public static function softDelete(string $table, int|string $id, string $idColumn = 'id'): int
    {
        self::assertIdentifier($table, 'table');
        self::assertIdentifier($idColumn, 'idColumn');
        return self::execute(
            "UPDATE `{$table}` SET `deleted_at` = NOW() WHERE `{$idColumn}` = ?",
            [$id]
        );
    }

    /**
     * Restore a soft-deleted row (sets deleted_at = NULL).
     */
    public static function restore(string $table, int|string $id, string $idColumn = 'id'): int
    {
        self::assertIdentifier($table, 'table');
        self::assertIdentifier($idColumn, 'idColumn');
        return self::execute(
            "UPDATE `{$table}` SET `deleted_at` = NULL WHERE `{$idColumn}` = ?",
            [$id]
        );
    }

    /** Reject any identifier that is not a simple alphanumeric/underscore name. */
    private static function assertIdentifier(string $value, string $label): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            throw new \InvalidArgumentException("Database: invalid {$label} name '{$value}'");
        }
    }

    /**
     * Start a fluent QueryBuilder for the given table.
     * Alias for DB::table() — both work.
     *
     * Database::table('users')->where('active', 1)->paginate(20)
     */
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder($table);
    }

    /**
     * Reset the singleton connection (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
