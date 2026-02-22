# 🚀 OpenGenetics — Deployment Guide

## Option A: InfinityFree (Free PHP + MySQL Hosting)

### Step 1: Create Account
1. Go to [https://www.infinityfree.net](https://www.infinityfree.net)
2. Sign up (no credit card required)
3. Create a new hosting account → you'll get a free subdomain (e.g., `yoursite.infinityfreeapp.com`)

### Step 2: Create MySQL Database
1. Go to **Control Panel** → **MySQL Databases**
2. Create a new database (note down: `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_HOST`)
3. InfinityFree provides the host (usually `sqlXXX.infinityfree.com`)

### Step 3: Configure .env
Create `.env` on your local machine with the InfinityFree credentials:

```env
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_db_pass

JWT_SECRET=<generate-with: php -r "echo bin2hex(random_bytes(32));">
JWT_EXPIRATION=86400

APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.infinityfreeapp.com
APP_TIMEZONE=Asia/Bangkok

CORS_ORIGIN=*
BCRYPT_COST=12
```

### Step 4: Upload Files
Upload via **File Manager** (cPanel) or **FTP**:

```
htdocs/                          ← InfinityFree web root
├── api/                         ← Copy from your project
├── src/                         ← Copy from your project
├── locales/                     ← Copy from your project
├── sdk/                         ← Copy from your project
├── storage/                     ← Copy from your project
│   └── rate-limit/
├── vendor/                      ← Run `composer install` locally, then upload
├── .env                         ← Your production .env (from Step 3)
├── .htaccess                    ← Copy from public/.htaccess
├── index.php                    ← Copy from public/index.php
└── composer.json                ← Copy from your project
```

**IMPORTANT:** On InfinityFree, the web root IS `htdocs/`. So the contents of your `public/` folder go directly into `htdocs/`, and everything else goes alongside it.

### Step 5: Fix index.php paths
Edit `index.php` (uploaded to htdocs/) to match the flat structure:

```php
// Change this:
require_once __DIR__ . '/../src/bootstrap.php';
Env::load(__DIR__ . '/..');

// To this (if everything is in htdocs/):
require_once __DIR__ . '/src/bootstrap.php';
Env::load(__DIR__);
```

And update the Router initialization:
```php
$router = new Router(__DIR__, 'api');
```

### Step 6: Import Database Schema
1. Go to **Control Panel** → **phpMyAdmin**
2. Select your database
3. Run the SQL from `bin/genetics mutate` or paste these statements:

```sql
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_resets_email` (`email`),
    INDEX `idx_resets_token` (`token`),
    INDEX `idx_resets_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed roles
INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'ADMIN', 'Full system administrator'),
(2, 'HR', 'Human Resources manager'),
(3, 'EMPLOYEE', 'Regular employee')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- Seed admin (password: 'password')
INSERT INTO `users` (`email`, `password_hash`, `role_id`, `is_active`) VALUES
('admin@opengenetics.io', '$2y$12$LJ3m4ks9Yqz5Xb5w5xGOd.wJmCQbKz3FGh5fBqV7K2W5LrKzGmXi6', 1, 1)
ON DUPLICATE KEY UPDATE `email` = `email`;
```

### Step 7: Test
```bash
# Health check
curl https://yoursite.infinityfreeapp.com/api/health

# Login
curl -X POST https://yoursite.infinityfreeapp.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@opengenetics.io","password":"password"}'
```

---

## Option B: Deploy Docs to Netlify (Free)

1. Go to [https://app.netlify.com](https://app.netlify.com)
2. Drag & drop the `docs/` folder
3. Done! URL: `https://your-site.netlify.app`

---

## Option C: Publish to Packagist (Free)

### Prerequisites
- GitHub account
- Code pushed to a public GitHub repo

### Steps
1. Push to GitHub:
```bash
git init
git add .
git commit -m "v1.0.0"
git remote add origin https://github.com/YOUR_USER/open-genetics-framework.git
git push -u origin main
git tag v1.0.0
git push --tags
```

2. Go to [https://packagist.org](https://packagist.org)
3. Login with GitHub
4. Click **Submit** → paste your GitHub repo URL
5. Done! Now anyone can run:
```bash
composer create-project open-genetics/framework my-api
```

---

## Production Checklist

| Item | Action |
|------|--------|
| `APP_DEBUG` | Set to `false` |
| `APP_ENV` | Set to `production` |
| `JWT_SECRET` | Use `php -r "echo bin2hex(random_bytes(32));"` |
| `CORS_ORIGIN` | Set to your real frontend domain |
| Default password | Change `admin@opengenetics.io` password immediately |
| HTTPS | InfinityFree provides free SSL automatically |
| Rate limiting | Already built-in (5 login attempts / 5 min) |
| Error handler | Already built-in (hides details in production) |
