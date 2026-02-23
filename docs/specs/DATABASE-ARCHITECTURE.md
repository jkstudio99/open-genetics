# Database Architecture — OpenGenetics

## Overview

OpenGenetics ใช้ **MySQL / MariaDB** เป็น database หลัก  
เชื่อมต่อผ่าน **PDO Singleton** พร้อม Prepared Statements เท่านั้น (ป้องกัน SQL Injection)

---

## ER Diagram (Entity-Relationship)

```
┌─────────────────────────────────────────────────────────────────┐
│                        OPEN-GENETICS DB                         │
└─────────────────────────────────────────────────────────────────┘

  ┌──────────────┐       1:N        ┌──────────────────┐
  │    roles     │◄─────────────────│      users       │
  │──────────────│                  │──────────────────│
  │ PK id        │                  │ PK id            │
  │    name      │                  │ FK role_id ──────┤
  │    level     │                  │    name          │
  │    created_at│                  │    email (UQ)    │
  └──────────────┘                  │    password      │
                                    │    tenant_id     │
                                    │    is_active     │
                                    │    created_at    │
                                    │    updated_at    │
                                    └────────┬─────────┘
                                             │
                              ┌──────────────┼──────────────┐
                              │ 1:N          │ 1:N          │ 1:N
                              ▼              ▼              ▼
                  ┌─────────────────┐ ┌────────────┐ ┌────────────────┐
                  │   audit_logs    │ │  password_  │ │   (custom      │
                  │─────────────────│ │  resets     │ │    tables)     │
                  │ PK id           │ │────────────│ │────────────────│
                  │ FK user_id ─────┤ │ PK id      │ │ FK user_id     │
                  │    action       │ │ FK user_id  │ │    ...         │
                  │    entity       │ │    token    │ └────────────────┘
                  │    entity_id    │ │    expires  │
                  │    details      │ │    used     │
                  │    ip_address   │ │    created  │
                  │    user_agent   │ └────────────┘
                  │    created_at   │
                  └─────────────────┘
```

---

## Table Definitions

### `roles`

บทบาทของผู้ใช้ในระบบ RBAC 3 ระดับ

| Column | Type | Constraints | Description |
|--------|------|------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | Role ID |
| `name` | VARCHAR(50) | UNIQUE, NOT NULL | ADMIN, HR, EMPLOYEE |
| `level` | INT | NOT NULL, DEFAULT 3 | 1=Admin, 2=HR, 3=Employee |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | สร้างเมื่อ |

```sql
CREATE TABLE roles (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL UNIQUE,
    level      INT NOT NULL DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Seed Data:**

| id | name | level |
|----|------|-------|
| 1 | ADMIN | 1 |
| 2 | HR | 2 |
| 3 | EMPLOYEE | 3 |

---

### `users`

ข้อมูลผู้ใช้ พร้อม JWT authentication

| Column | Type | Constraints | Description |
|--------|------|------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | User ID |
| `role_id` | INT | FK → roles.id, NOT NULL | บทบาท |
| `name` | VARCHAR(100) | NOT NULL | ชื่อ-นามสกุล |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | อีเมล (ใช้ login) |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hash (12 rounds) |
| `tenant_id` | VARCHAR(50) | NULLABLE, INDEX | Multi-tenancy ID |
| `is_active` | TINYINT(1) | DEFAULT 1 | สถานะใช้งาน |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | สร้างเมื่อ |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | แก้ไขเมื่อ |

```sql
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    role_id    INT NOT NULL DEFAULT 3,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    tenant_id  VARCHAR(50) NULL,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Default Admin Seed:**

| name | email | password | role_id |
|------|-------|----------|---------|
| Admin | admin@opengenetics.io | (bcrypt of "password") | 1 |

---

### `audit_logs`

บันทึกกิจกรรมทั้งหมด (Non-blocking)

| Column | Type | Constraints | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, AUTO_INCREMENT | Log ID |
| `user_id` | INT | FK → users.id, NULLABLE | ผู้ทำรายการ |
| `action` | VARCHAR(50) | NOT NULL, INDEX | CREATE, UPDATE, DELETE, LOGIN, LOGOUT |
| `entity` | VARCHAR(100) | NOT NULL | ชื่อ entity (users, products, etc.) |
| `entity_id` | INT | NULLABLE | ID ของ entity ที่ถูกแก้ไข |
| `details` | JSON | NULLABLE | รายละเอียดเพิ่มเติม |
| `ip_address` | VARCHAR(45) | NULLABLE | IPv4/IPv6 |
| `user_agent` | VARCHAR(500) | NULLABLE | Browser user agent |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | เวลาที่เกิด |

```sql
CREATE TABLE audit_logs (
    id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NULL,
    action     VARCHAR(50) NOT NULL,
    entity     VARCHAR(100) NOT NULL,
    entity_id  INT NULL,
    details    JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_entity (entity, entity_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `password_resets`

Token สำหรับรีเซ็ตรหัสผ่าน

| Column | Type | Constraints | Description |
|--------|------|------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | Reset ID |
| `user_id` | INT | FK → users.id, NOT NULL | User ที่ขอรีเซ็ต |
| `token` | VARCHAR(255) | NOT NULL, INDEX | Reset token (hashed) |
| `expires_at` | TIMESTAMP | NOT NULL | หมดอายุเมื่อ |
| `used` | TINYINT(1) | DEFAULT 0 | ใช้แล้วหรือยัง |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | สร้างเมื่อ |

```sql
CREATE TABLE password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token      VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used       TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Relationships Summary

```
roles (1) ──────── (N) users
users (1) ──────── (N) audit_logs
users (1) ──────── (N) password_resets
```

| Relationship | Type | ON DELETE |
|-------------|------|----------|
| roles → users | One-to-Many | RESTRICT |
| users → audit_logs | One-to-Many | SET NULL |
| users → password_resets | One-to-Many | CASCADE |

---

## Connection Pattern

```php
// PDO Singleton — one connection per request
$pdo = Database::connect();

// Query with prepared statements (ONLY way)
$users = Database::query(
    "SELECT u.*, r.name as role_name 
     FROM users u 
     JOIN roles r ON u.role_id = r.id 
     WHERE u.is_active = :active",
    ['active' => 1]
);

// Single row
$user = Database::queryOne(
    "SELECT * FROM users WHERE email = :email",
    ['email' => 'admin@opengenetics.io']
);

// Insert
Database::execute(
    "INSERT INTO users (name, email, password, role_id) VALUES (:n, :e, :p, :r)",
    ['n' => 'John', 'e' => 'john@example.com', 'p' => password_hash('pw', PASSWORD_BCRYPT, ['cost' => 12]), 'r' => 3]
);

// Transaction
Database::transaction(function ($pdo) {
    Database::execute("UPDATE users SET is_active = 0 WHERE id = :id", ['id' => 5]);
    Database::execute("INSERT INTO audit_logs (user_id, action, entity) VALUES (:u, :a, :e)", 
        ['u' => 1, 'a' => 'DELETE', 'e' => 'users']);
});
```

---

## Indexes Strategy

| Table | Index | Columns | Purpose |
|-------|-------|---------|---------|
| users | PRIMARY | id | PK lookup |
| users | UNIQUE | email | Login lookup |
| users | idx_tenant | tenant_id | Multi-tenancy queries |
| users | idx_active | is_active | Filter active users |
| audit_logs | idx_action | action | Filter by action type |
| audit_logs | idx_entity | entity, entity_id | Entity history lookup |
| audit_logs | idx_user | user_id | User activity lookup |
| audit_logs | idx_created | created_at | Date range queries |
| password_resets | idx_token | token | Token validation |

---

## Migration Command

```bash
# สร้างตารางทั้งหมด + seed RBAC roles + admin user
php add/genetics mutate

# ตรวจสอบ connection
php add/genetics status
```

---

## Security Notes

1. **SQL Injection**: ป้องกัน 100% ด้วย PDO Prepared Statements
2. **Password Storage**: Bcrypt 12 rounds (ไม่เก็บ plain text)
3. **Sensitive Data**: `.env` ไม่ commit เข้า git
4. **Connection**: `ATTR_EMULATE_PREPARES = false` (real prepared statements)
5. **Charset**: `utf8mb4` สำหรับรองรับ emoji และภาษาไทย
