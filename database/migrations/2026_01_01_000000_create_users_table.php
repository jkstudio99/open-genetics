<?php

/**
 * Migration: create_users_table
 * Created: 2026-02-23 16:00:00
 *
 * Initial database schema — roles, users, password_resets, audit_logs.
 */
return new class {
    public function up(PDO $pdo): void
    {
        // Roles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `roles` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `role_name` VARCHAR(50) NOT NULL UNIQUE,
                `description` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `role_id` INT UNSIGNED NOT NULL DEFAULT 3,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `tenant_id` VARCHAR(100) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE,
                INDEX `idx_users_email` (`email`),
                INDEX `idx_users_role` (`role_id`),
                INDEX `idx_users_tenant` (`tenant_id`),
                INDEX `idx_users_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Password resets table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `password_resets` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL,
                `token` VARCHAR(255) NOT NULL UNIQUE,
                `expires_at` TIMESTAMP NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_resets_email` (`email`),
                INDEX `idx_resets_token` (`token`),
                INDEX `idx_resets_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Audit logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `audit_logs` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED DEFAULT NULL,
                `action` VARCHAR(50) NOT NULL,
                `entity` VARCHAR(100) NOT NULL,
                `payload` JSON DEFAULT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `user_agent` VARCHAR(500) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                    ON DELETE SET NULL ON UPDATE CASCADE,
                INDEX `idx_audit_user` (`user_id`),
                INDEX `idx_audit_action` (`action`),
                INDEX `idx_audit_entity` (`entity`),
                INDEX `idx_audit_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `audit_logs`");
        $pdo->exec("DROP TABLE IF EXISTS `password_resets`");
        $pdo->exec("DROP TABLE IF EXISTS `users`");
        $pdo->exec("DROP TABLE IF EXISTS `roles`");
    }
};
