<div align="center">
  <img src="public/images/logo/open-genetics-logo-white.svg" alt="OpenGenetics Logo" width="200" />
  <br/>
  <h1>OpenGenetics Framework</h1>
  <p><strong>Enterprise PHP Micro-Framework v2.2</strong> &mdash; <em>Production-ready features built-in. No config bloat. Just PHP.</em></p>

  <p>
    <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.1%2B-8892BF.svg?logo=php&logoColor=white" alt="PHP 8.1+"></a>
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1.svg?logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/version-2.3.0-6c63ff.svg" alt="v2.3.0">
  </p>
</div>

---

## ✨ What's New in v2.3

| Feature                         | Description                                                                                            |
| ------------------------------- | ------------------------------------------------------------------------------------------------------ |
| 🗃️ **Query Builder**            | `DB::table('users')->where('active',1)->paginate(20)` — fluent builder on PDO with prepared statements |
| ✅ **Guard::check()**           | Soft auth check — returns `bool` instead of throwing, perfect for optional-auth endpoints              |
| 🏷️ **Cache::namespace()**       | Isolate cache key spaces between modules — `Cache::namespace('shop')->get('products')`                 |
| 🧪 **Testing: seed()**          | `$this->seed(['users' => [...]])` — seed test data within transaction rollback                         |
| ⏩ **Pipeline::after()**        | Post-response hooks for async side effects like audit logging                                          |
| 🚫 **#[SkipMiddleware]**        | `#[SkipMiddleware(CorsMiddleware::class)]` — exclude routes from global middleware                     |
| 📝 **LogMiddleware**            | Built-in request/response logging middleware                                                           |
| 🔌 **ErrorHandler::reporter()** | `ErrorHandler::reporter(callable)` — hook for Sentry/Bugsnag integration                               |

---

## ✨ What's in v2.0

| #   | Feature                    | Description                                                                               |
| --- | -------------------------- | ----------------------------------------------------------------------------------------- |
| 1   | ⚡️ **Middleware Pipeline** | `#[Middleware('auth', 'rate:10,60')]` declarative middleware with Chain of Responsibility |
| 2   | 🗄️ **DB Migrations**       | Versioned, batch-rollback migrations with `migrate`, `migrate:rollback`, `migrate:status` |
| 3   | 🧪 **Testing Framework**   | `GeneticTestCase` with HTTP client, `actingAsAdmin()`, `assertOk()`, `assertPaginated()`  |
| 4   | 🚀 **Caching Layer**       | File-based `Cache::remember()`, tag-based flush, zero dependencies                        |
| 5   | 🔍 **Field Selector**      | `?fields=id,name,price` sparse fieldsets — GraphQL-lite without the server                |
| 6   | 📡 **Genetic Pulse**       | Server-Sent Events real-time push — no WebSocket server needed                            |
| 7   | 🧩 **Genetic Modules**     | `GeneticModule` plugin system with auto-discovery and lifecycle hooks                     |
| 8   | 🛠️ **Admin Generator**     | `make:admin <table>` — full CRUD admin endpoint from DB schema in seconds                 |
| 9   | 🤖 **Endpoint AI**         | `make:endpoint-ai products "CRUD with auth and cache"` — NLP scaffold                     |
| 10  | 🏪 **Marketplace**         | `market:install og/notifications` — community packages in one command                     |

> Plus all v1.0 features: JWT Auth, RBAC, i18n, Audit Trail, Dual SDK, File-based Routing, CLI, OpenAPI

---

## 🚀 Quick Start

```bash
# 1. Clone or create project
composer create-project open-genetics/framework my-api && cd my-api

# 2. Configure environment
cp .env.example .env
# Edit: DB_NAME, DB_USER, DB_PASS, JWT_SECRET

# 3. Bootstrap (tables + RBAC + admin user)
php genetics mutate

# 4. Start dev server
php genetics serve
# → http://127.0.0.1:8080
```

**Default admin:** `admin@opengenetics.io` / `password` _(change immediately in production)_

---

## 💻 Building an API Endpoint

Drop a file in `api/` — it's instantly a route. No registration needed.

```php
// api/products.php  →  GET/POST /api/products

use OpenGenetics\Core\{Response, Cache};
use OpenGenetics\Core\DB;

#[\OpenGenetics\Core\Middleware('auth', 'rate:60,60')]
class Products
{
    public static function get(array $body): void
    {
        // Query Builder with caching
        $products = Cache::remember('products:all', 300, fn() =>
            DB::table('products')->where('active', 1)->paginate(20)
        );
        Response::success($products);
    }

    public static function post(array $body): void
    {
        // AI-assisted scaffold: php genetics make:endpoint-ai products "with auth and audit"
        Response::success(null, 'Created', 201);
    }
}
```

Or scaffold in seconds:

```bash
php genetics make:endpoint-ai products "CRUD with auth, search, pagination and cache"
```

---

## 🖥️ CLI — 25+ Commands

```
Database:    mutate, seed, status, serve
Migrations:  migrate, migrate:rollback, migrate:status
Scaffold:    make:endpoint, make:middleware, make:migration,
             make:test, make:admin, make:endpoint-ai
Cache:       cache:clear, cache:stats, cache:gc
Marketplace: market:list, market:search, market:install
Modules:     modules:list
Docs:        docs:generate
Info:        help, --version, new
```

---

## 🏗️ Project Structure

```
my-api/
├── api/                    # File-based routing (1 file = 1 endpoint)
│   └── auth/               # Auth routes (login, register, logout)
├── src/Core/               # Framework core (PSR-4, PHP 8.1+)
│   ├── QueryBuilder.php    # Fluent Query Builder (v2.2)
│   ├── Cache.php           # Caching layer with namespace support (v2.2)
│   ├── FieldSelector.php   # GraphQL-lite sparse fieldsets
│   ├── Pulse.php           # Server-Sent Events
│   ├── ModuleLoader.php    # Plugin system
│   ├── AdminGenerator.php  # Admin endpoint scaffolder
│   ├── EndpointAI.php      # AI endpoint generator
│   ├── Marketplace.php     # Package registry
│   ├── Pipeline.php        # Middleware pipeline with Pipeline::after()
│   ├── Migrator.php        # DB migrations
│   ├── Database.php        # PDO Singleton
│   ├── Router.php          # File-based router
│   └── Response.php        # JSON response helpers
├── src/Auth/               # JWT + Guard RBAC (Guard::check() v2.2)
├── src/Testing/            # GeneticTestCase + TestResponse + seed()
├── src/Middleware/         # Auth, CORS, RateLimit, LogMiddleware
├── database/migrations/    # Versioned migration files
├── modules/                # Genetic Modules (plugins)
├── storage/cache/          # File cache store
├── sdk/                    # Frontend SDK (React + Vanilla JS)
├── locales/                # i18n dictionaries (en.json, th.json)
├── genetics                # CLI tool
├── public/                 # Web root (index.php)
└── .env                    # Environment config
```

---

## 🔥 Feature Highlights

### Query Builder (v2.2)

```php
$users = DB::table('users')
    ->select(['id', 'email', 'role_name'])
    ->where('is_active', 1)
    ->where('tenant_id', $tenantId)
    ->orderBy('created_at', 'DESC')
    ->paginate(20);
```

### Middleware Pipeline

```php
#[Middleware('auth:ADMIN', 'rate:10,60')]
class AdminProducts { ... }
```

### Guard::check() — Optional Auth (v2.2)

```php
if (Guard::check()) {
    $user = Guard::user(); // personalized response
} else {
    // public response
}
```

### Database Migrations

```bash
php genetics migrate            # Run pending
php genetics migrate:rollback   # Undo last batch
php genetics make:migration add_image_to_products
```

### Testing Framework

```php
class ProductsTest extends GeneticTestCase {
    public function testList(): void {
        $this->actingAsAdmin();
        $this->seed(['products' => [['name' => 'Test', 'active' => 1]]]);
        $this->get('/api/products')->assertOk()->assertPaginated();
    }
}
```

### Field Selector (GraphQL-lite)

```
GET /api/products?fields=id,name,price,category.name
```

### Real-time SSE

```php
Pulse::broadcast('orders', ['id' => 123, 'status' => 'new']);
// Client: const es = new EventSource('/api/events');
```

### Marketplace

```bash
php genetics market:search notifications
php genetics market:install og/notifications
```

---

## 📚 Documentation

Full documentation: open `docs/site/overview.html` or browse the 26-page docs site.

```bash
# Regenerate docs
cd docs/_tools && python3 _gen_pages.py
```

---

## 🛡️ Security

Report vulnerabilities to `security@opengenetics.io` — do not use the issue tracker.

## 📄 License

MIT License — free and open-source. See [LICENSE](LICENSE).
