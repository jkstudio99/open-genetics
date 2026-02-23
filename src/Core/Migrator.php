<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Database Migrator
 *
 * Version-tracked database migrations with rollback support.
 * Migration files live in database/migrations/ and are
 * timestamped to ensure execution order.
 *
 * CLI:
 *   php add/genetics migrate              — Run pending migrations
 *   php add/genetics migrate:rollback     — Undo last batch
 *   php add/genetics migrate:status       — Show migration history
 *   php add/genetics make:migration name  — Create migration file
 */
final class Migrator
{
    private string $migrationsDir;
    private string $table = 'og_migrations';

    public function __construct(string $projectRoot)
    {
        $this->migrationsDir = rtrim($projectRoot, '/') . '/database/migrations';
    }

    /**
     * Ensure the migrations tracking table exists.
     */
    public function ensureTable(): void
    {
        $pdo = Database::connect();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL UNIQUE,
                `batch` INT UNSIGNED NOT NULL,
                `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Run all pending migrations.
     * @return array<string> Names of executed migrations
     */
    public function migrate(): array
    {
        $this->ensureTable();
        $this->ensureDirectory();

        $ran      = $this->getRanMigrations();
        $pending  = $this->getPendingMigrations($ran);
        $executed = [];

        if (empty($pending)) {
            return [];
        }

        $batch = $this->getNextBatch();

        foreach ($pending as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $migration = require $file;

            if (is_object($migration) && method_exists($migration, 'up')) {
                $pdo = Database::connect();
                $migration->up($pdo);

                Database::execute(
                    "INSERT INTO `{$this->table}` (migration, batch) VALUES (:name, :batch)",
                    ['name' => $name, 'batch' => $batch]
                );

                $executed[] = $name;
            }
        }

        return $executed;
    }

    /**
     * Rollback the last batch of migrations.
     * @return array<string> Names of rolled-back migrations
     */
    public function rollback(): array
    {
        $this->ensureTable();

        $lastBatch = $this->getLastBatch();
        if ($lastBatch === 0) {
            return [];
        }

        $migrations = Database::query(
            "SELECT migration FROM `{$this->table}` WHERE batch = :batch ORDER BY id DESC",
            ['batch' => $lastBatch]
        );

        $rolledBack = [];

        foreach ($migrations as $row) {
            $name = $row['migration'];
            $file = $this->migrationsDir . "/{$name}.php";

            if (file_exists($file)) {
                $migration = require $file;

                if (is_object($migration) && method_exists($migration, 'down')) {
                    $pdo = Database::connect();
                    $migration->down($pdo);
                }
            }

            Database::execute(
                "DELETE FROM `{$this->table}` WHERE migration = :name",
                ['name' => $name]
            );

            $rolledBack[] = $name;
        }

        return $rolledBack;
    }

    /**
     * Get status of all migrations.
     * @return array<array{name: string, status: string, batch: ?int, date: ?string}>
     */
    public function status(): array
    {
        $this->ensureTable();
        $this->ensureDirectory();

        $ran = Database::query(
            "SELECT migration, batch, executed_at FROM `{$this->table}` ORDER BY id"
        );

        $ranMap = [];
        foreach ($ran as $r) {
            $ranMap[$r['migration']] = $r;
        }

        $files  = $this->getAllMigrationFiles();
        $status = [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (isset($ranMap[$name])) {
                $status[] = [
                    'name'   => $name,
                    'status' => 'Ran',
                    'batch'  => (int) $ranMap[$name]['batch'],
                    'date'   => $ranMap[$name]['executed_at'],
                ];
            } else {
                $status[] = [
                    'name'   => $name,
                    'status' => 'Pending',
                    'batch'  => null,
                    'date'   => null,
                ];
            }
        }

        return $status;
    }

    /**
     * Create a new migration file.
     * @return string Full path to created file
     */
    public function createMigration(string $name): string
    {
        $this->ensureDirectory();

        $timestamp = date('Y_m_d_His');
        $filename  = "{$timestamp}_{$name}.php";
        $path      = $this->migrationsDir . "/{$filename}";

        $template = <<<'PHP'
<?php

/**
 * Migration: {{NAME}}
 * Created: {{DATE}}
 */
return new class {
    /**
     * Run the migration.
     */
    public function up(PDO $pdo): void
    {
        $pdo->exec("
            -- Write your SQL here
        ");
    }

    /**
     * Reverse the migration.
     */
    public function down(PDO $pdo): void
    {
        $pdo->exec("
            -- Write your rollback SQL here
        ");
    }
};
PHP;

        $content = str_replace(
            ['{{NAME}}', '{{DATE}}'],
            [$name, date('Y-m-d H:i:s')],
            $template
        );

        file_put_contents($path, $content);

        return $path;
    }

    // ─── Private Helpers ─────────────────────────────

    private function getRanMigrations(): array
    {
        $rows = Database::query("SELECT migration FROM `{$this->table}`");
        return array_column($rows, 'migration');
    }

    private function getPendingMigrations(array $ran): array
    {
        $files = $this->getAllMigrationFiles();
        return array_filter($files, function ($file) use ($ran) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            return !in_array($name, $ran, true);
        });
    }

    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }

        $files = glob($this->migrationsDir . '/*.php');
        sort($files);
        return $files;
    }

    private function getNextBatch(): int
    {
        return $this->getLastBatch() + 1;
    }

    private function getLastBatch(): int
    {
        $rows = Database::query("SELECT MAX(batch) as max_batch FROM `{$this->table}`");
        return (int) ($rows[0]['max_batch'] ?? 0);
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0755, true);
        }
    }
}
