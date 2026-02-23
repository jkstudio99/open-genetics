<?php

/**
 * Migration: seed_rbac_roles
 * Created: 2026-02-23 16:00:00
 *
 * Seeds the initial RBAC roles and default admin user.
 */
return new class {
    public function up(PDO $pdo): void
    {
        // Seed roles
        $roles = [
            [1, 'ADMIN', 'Full system administrator'],
            [2, 'Manager', 'Manager'],
            [3, 'EMPLOYEE', 'Regular employee'],
        ];

        $stmt = $pdo->prepare(
            "INSERT INTO `roles` (`id`, `role_name`, `description`)
             VALUES (:id, :name, :desc)
             ON DUPLICATE KEY UPDATE `description` = VALUES(`description`)"
        );

        foreach ($roles as [$id, $name, $desc]) {
            $stmt->execute(['id' => $id, 'name' => $name, 'desc' => $desc]);
        }

        // Seed admin user
        $existing = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $existing->execute(['email' => 'admin@opengenetics.io']);

        if (!$existing->fetch()) {
            $hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare(
                "INSERT INTO `users` (`email`, `password_hash`, `role_id`, `is_active`)
                 VALUES (:email, :hash, 1, 1)"
            );
            $stmt->execute(['email' => 'admin@opengenetics.io', 'hash' => $hash]);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DELETE FROM `users` WHERE email = 'admin@opengenetics.io'");
        $pdo->exec("DELETE FROM `roles` WHERE id IN (1, 2, 3)");
    }
};
