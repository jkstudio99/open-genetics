# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.3.html).

## [2.0.3] - 2026-02-23

### Added — Feature 1: Middleware Pipeline

- `Pipeline` class — Chain of responsibility pattern for HTTP request processing
- `#[Middleware]` PHP 8.1 Attribute for declarative middleware on endpoints
- Built-in middleware: `CorsMiddleware`, `AuthMiddleware` (with role params), `RateLimitMiddleware`
- Global + per-endpoint middleware with named aliases (`auth`, `rate`, `cors`)
- `Guard::setUser()` for middleware-injected authentication

### Added — Feature 2: Database Migration Versioning

- `Migrator` class — version-tracked migrations with batch rollback
- `database/migrations/` directory with timestamped migration files
- CLI: `migrate`, `migrate:rollback`, `migrate:status`, `make:migration`
- Initial migrations: `create_users_table`, `seed_rbac_roles`

### Added — Feature 3: Testing Framework

- `GeneticTestCase` — HTTP test client extending PHPUnit with curl-based requests
- `TestResponse` — Fluent assertions: `assertOk()`, `assertJsonHas()`, `assertPaginated()`, etc.
- Auth helpers: `actingAs()`, `actingAsAdmin()`, `asGuest()`
- Database assertions: `assertDatabaseHas()`, `assertDatabaseMissing()`
- CLI: `make:test` scaffolder

### Added — Feature 4: Caching Layer

- `Cache` class — File-based cache with TTL, tag-based flush, and `remember()` pattern
- Zero dependencies — uses PHP serialization in `storage/cache/`
- CLI: `cache:clear`, `cache:stats`, `cache:gc`

### Added — Feature 5: GraphQL-lite Field Selector

- `FieldSelector` class — Sparse fieldsets via `?fields=id,name,email`
- Dot-notation support for nested fields: `?fields=id,category.name`
- Whitelist (`allow()`) to prevent data leakage

### Added — Feature 6: Real-time SSE (Genetic Pulse)

- `Pulse` class — Server-Sent Events streaming without WebSocket server
- Broadcast/consume queue via `Pulse::broadcast(channel, data)`
- Heartbeat, retry, and graceful close support
- Client reconnect via `Last-Event-ID`

### Added — Feature 7: Plugin System (Genetic Modules)

- `GeneticModule` interface and `GeneticModuleBase` abstract class
- `ModuleLoader` — registers modules, calls `boot()`, merges middleware aliases
- Auto-discovery from `modules/` directory
- CLI: `modules:list`

### Added — Feature 8: Auto Admin Panel Generator

- `AdminGenerator` — inspects DB schema, generates full CRUD admin API endpoints
- CLI: `make:admin <table>`
- Generated endpoints: paginated GET, POST, PUT, DELETE with ADMIN role protection

### Added — Feature 9: AI Endpoint Generator

- `EndpointAI` — parses natural-language descriptions to detect: auth, RBAC, pagination, search, caching, audit
- CLI: `make:endpoint-ai <name> "<description>"`
- Shows detected features before generating

### Added — Feature 10: Marketplace

- `Marketplace` class — package registry with search, install, publish
- 5 built-in featured packages: `og/jwt-refresh`, `og/notifications`, `og/file-upload`, `og/two-factor`, `og/graphql-lite`
- Local registry cache (1h TTL), remote registry fetch
- CLI: `market:list`, `market:search`, `market:install`

### Changed

- `Router` v2.0 — integrated Pipeline, removed hard-coded CORS (now CorsMiddleware)
- `index.php` v2.0 — registers global middleware via Pipeline
- CLI upgraded to v2.0.3 with **25 total commands**
- Docs: 19 generated pages (added `middleware.html`, `migrations.html`, `testing.html`)
- Landing page: bumped to v2.0.3

## [1.0.0] - 2024-01-01

### Added

- File-based routing system (`Router`)
- JWT authentication with hybrid implementation (`JwtManager`)
- Genetic RBAC with 3 roles: ADMIN, HR, EMPLOYEE (`Guard`)
- PDO database singleton with prepared statements (`Database`)
- `queryOne()` helper for single-row fetches
- Environment loader with static caching (`Env`)
- Standardized JSON response helper (`Response`)
- i18n engine with Thai/English support (`I18n`)
- Non-blocking audit trail logging (`AuditLog`)
- Password reset with token expiration (`AuthService`)
- Vanilla JS SDK (`genetics.min.js`)
- React Hook SDK (`useGenetics`)
- CLI tool with commands: `mutate`, `seed`, `status`, `make:endpoint`, `make:middleware`, `new`, `serve`
- Multi-tenancy support via `tenant_id`
- OWASP-aligned security (prepared statements, bcrypt, JWT expiration)
- Dark/Light theme support in documentation
- Responsive documentation site
