# Product Requirements Document (PRD) — OpenGenetics

## Document Info

| Field | Value |
|-------|-------|
| **Product** | OpenGenetics Framework |
| **Version** | 1.0.0 |
| **Status** | Released (Stable) |
| **Author** | OpenGenetics Team |
| **Last Updated** | February 2026 |

---

## 1. Executive Summary

OpenGenetics เป็น PHP Micro-Framework สำหรับสร้าง REST API บน XAMPP  
ออกแบบมาเพื่อให้นักพัฒนาสามารถมี Authentication, Authorization, i18n, Audit Trail  
และ Frontend SDK พร้อมใช้งาน — ตั้งแต่วินาทีแรก

**Target Users**: PHP developers ที่ต้องการสร้าง API อย่างรวดเร็ว  
**Key Value Prop**: ติดตั้ง → สร้าง API → Deploy — ใน 5 นาที

---

## 2. Problem Statement

### ปัญหาที่พบ
1. Framework ใหญ่ (Laravel, Symfony) มี boilerplate เยอะเกินไปสำหรับ API เล็กๆ
2. Micro-frameworks (Slim, Lumen) ไม่มี Auth/RBAC ในตัว — ต้องติดตั้งเพิ่ม
3. การตั้งค่า JWT + RBAC + i18n ใช้เวลาหลายชั่วโมง
4. ไม่มี Frontend SDK ที่เชื่อมต่อกับ PHP API ได้ทันที
5. Documentation ส่วนใหญ่เป็นภาษาอังกฤษเท่านั้น

### Solution
OpenGenetics รวมทุกอย่างไว้ใน framework เดียว:
- **Zero-config**: ไม่ต้องตั้งค่า routing, middleware, หรือ service container
- **Built-in security**: JWT + RBAC + OWASP ในตัว
- **Dual-language**: Thai/English documentation + i18n API
- **Full-stack SDK**: React Hook + Vanilla JS SDK

---

## 3. Functional Requirements

### FR-001: File-based Routing
- **Priority**: P0 (Must Have)
- **Description**: สร้างไฟล์ PHP ใน `api/` folder → ได้ endpoint อัตโนมัติ
- **Acceptance Criteria**:
  - `api/users.php` → `GET|POST|PUT|DELETE /api/users`
  - `api/auth/login.php` → `POST /api/auth/login`
  - Nested folders สร้าง nested routes
  - HTTP method dispatch ผ่าน `$_SERVER['REQUEST_METHOD']`

### FR-002: JWT Authentication
- **Priority**: P0 (Must Have)
- **Description**: ระบบ login/register ด้วย JWT token
- **Acceptance Criteria**:
  - POST `/api/auth/register` — สร้างบัญชีใหม่
  - POST `/api/auth/login` — รับ JWT token
  - GET `/api/auth/profile` — ข้อมูล user (ต้อง auth)
  - Token ใช้ HS256 algorithm
  - Password hash ด้วย Bcrypt 12 rounds
  - Token expiration ตั้งค่าได้ใน `.env`

### FR-003: RBAC Guard System
- **Priority**: P0 (Must Have)
- **Description**: Role-based access control 3 ระดับ
- **Acceptance Criteria**:
  - 3 Roles: `ADMIN`, `HR`, `EMPLOYEE`
  - `Guard::requireAuth()` — ต้อง login
  - `Guard::requireRole('ADMIN')` — ต้องเป็น admin
  - `Guard::requireRole('ADMIN', 'HR')` — admin หรือ HR
  - `Guard::hasRole('ADMIN')` — check โดยไม่ block
  - `Guard::user()` — ดึงข้อมูล user ปัจจุบัน

### FR-004: Database Layer
- **Priority**: P0 (Must Have)
- **Description**: PDO Singleton wrapper
- **Acceptance Criteria**:
  - `Database::query()` — SELECT all rows
  - `Database::queryOne()` — SELECT single row
  - `Database::execute()` — INSERT/UPDATE/DELETE
  - `Database::transaction()` — atomic operations
  - `Database::lastInsertId()` — last auto-increment
  - Prepared statements เท่านั้น (ป้องกัน SQL injection)

### FR-005: Response Helper
- **Priority**: P0 (Must Have)
- **Description**: Standardized JSON response format
- **Acceptance Criteria**:
  - `Response::json($data, $message, $code)` — success response
  - `Response::error($message, $code)` — error response
  - `Response::paginate($query, $params, $page, $perPage)` — pagination
  - `Response::created($data)` — 201 response
  - Content-Type: `application/json; charset=utf-8`

### FR-006: i18n Engine
- **Priority**: P1 (Should Have)
- **Description**: Thai/English switching via HTTP header
- **Acceptance Criteria**:
  - Header: `X-Locale: th` หรือ `X-Locale: en`
  - Locale files: `locales/th.json`, `locales/en.json`
  - `I18n::t('key')` — translate
  - `I18n::locale()` — current locale
  - Default: Thai

### FR-007: Audit Trail
- **Priority**: P1 (Should Have)
- **Description**: Auto-logging สำหรับทุก mutation
- **Acceptance Criteria**:
  - `AuditLog::log($action, $entity, $entityId, $details)` — บันทึก
  - Actions: `CREATE`, `UPDATE`, `DELETE`, `LOGIN`, `LOGOUT`
  - บันทึก: user_id, action, entity, entity_id, ip, user_agent, timestamp
  - Non-blocking (ไม่กระทบ response time)

### FR-008: Genetic SDK
- **Priority**: P1 (Should Have)
- **Description**: Frontend SDK สำหรับ React และ Vanilla JS
- **Acceptance Criteria**:
  - **Vanilla JS**: `Genetics.init()`, `Genetics.login()`, `Genetics.t()`, `Genetics.setTheme()`
  - **React Hook**: `useGenetics()` → `{ login, logout, user, t, setLocale, setTheme }`
  - Auto token management (localStorage)
  - TypeScript type definitions

### FR-009: CLI Tool
- **Priority**: P1 (Should Have)
- **Description**: Command-line สำหรับ scaffolding และ management
- **Acceptance Criteria**:
  - `php bin/genetics mutate` — สร้างตาราง + seed
  - `php bin/genetics serve` — dev server
  - `php bin/genetics make:endpoint <name>` — scaffold endpoint
  - `php bin/genetics make:middleware <name>` — scaffold middleware
  - `php bin/genetics new <name>` — สร้าง project ใหม่
  - `php bin/genetics status` — ตรวจสอบ DB connection

---

## 4. Non-Functional Requirements

### NFR-001: Performance
- API response time < **50ms** (excluding database query time)
- No ORM overhead — direct PDO
- PDO Singleton — single connection per request
- Static caching for Env and i18n

### NFR-002: Security
- OWASP Top 10 compliance
- SQL Injection: **Prevented** (prepared statements only)
- XSS: **Prevented** (JSON-only responses, no HTML output)
- CSRF: **N/A** (stateless JWT API)
- Password: **Bcrypt 12 rounds**
- JWT: **HS256 with configurable expiration**

### NFR-003: Compatibility
- PHP >= 8.1 (8.1, 8.2, 8.3 tested)
- MySQL >= 5.7 / MariaDB >= 10.3
- Apache >= 2.4 with mod_rewrite
- XAMPP (Windows, macOS, Linux)

### NFR-004: Maintainability
- PSR-12 coding standard
- PSR-4 autoloading
- Minimal dependencies (1 optional: firebase/php-jwt)
- Comprehensive documentation (TH/EN)

---

## 5. User Stories

| ID | As a... | I want to... | So that... |
|----|---------|-------------|-----------|
| US-01 | Developer | ติดตั้ง framework ด้วย composer | เริ่มสร้าง API ได้ทันที |
| US-02 | Developer | สร้าง endpoint โดยสร้างไฟล์ PHP | ไม่ต้อง config routing |
| US-03 | Developer | ใช้ Guard::requireRole() | จำกัดสิทธิ์ได้ในบรรทัดเดียว |
| US-04 | Developer | ใช้ CLI scaffold | สร้าง boilerplate อัตโนมัติ |
| US-05 | Frontend Dev | ใช้ useGenetics() Hook | เชื่อมต่อ API จาก React ได้ง่าย |
| US-06 | API Consumer | ส่ง X-Locale header | ได้ response ภาษาที่ต้องการ |
| US-07 | Admin | ดู audit trail | ตรวจสอบกิจกรรมทั้งหมดได้ |
| US-08 | DevOps | deploy ด้วย free hosting | ไม่ต้องเสียค่าใช้จ่าย |

---

## 6. Out of Scope (v1.0)

- GraphQL support
- WebSocket real-time
- Admin dashboard UI
- Rate limiting
- OAuth2 / Social login
- File upload handler
- Queue / Job system
- Email sending
- Caching layer (Redis/Memcached)

---

## 7. Release Criteria

- [ ] ทุก FR ผ่าน acceptance criteria
- [ ] Response time < 50ms
- [ ] ไม่มี critical/high severity bugs
- [ ] Documentation ครบ 100%
- [ ] CLI commands ทำงานถูกต้อง
- [ ] SDK ทำงานได้ทั้ง React และ Vanilla JS
- [ ] OWASP Top 10 compliance
