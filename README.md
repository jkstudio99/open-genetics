<div align="center">
  <img src="public/images/logo/open-genetics-logo-white.svg" alt="OpenGenetics Logo" width="200" />
  <br/>
  <h1>OpenGenetics Framework</h1>
  <p><strong>Enterprise PHP Micro-Framework v2.0</strong> &mdash; <em>10 production features built-in. No config bloat. Just PHP.</em></p>

  <p>
    <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.1%2B-8892BF.svg?logo=php&logoColor=white" alt="PHP 8.1+"></a>
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1.svg?logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/version-2.1.0-6c63ff.svg" alt="v2.1.0">
  </p>
</div>

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

use OpenGenetics\Core\{Database, Response, Cache};

#[\OpenGenetics\Core\Middleware('auth', 'rate:60,60')]
class Products
{
    public static function get(array $body): void
    {
        // Cache for 5 minutes
        $products = Cache::remember('products:all', 300, fn() =>
            Database::query("SELECT * FROM products WHERE active = 1")
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

## 🖥️ CLI — 25 Commands

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
│   ├── Cache.php           # Caching layer (v2.0)
│   ├── FieldSelector.php   # GraphQL-lite sparse fieldsets (v2.0)
│   ├── Pulse.php           # Server-Sent Events (v2.0)
│   ├── ModuleLoader.php    # Plugin system (v2.0)
│   ├── AdminGenerator.php  # Admin endpoint scaffolder (v2.0)
│   ├── EndpointAI.php      # AI endpoint generator (v2.0)
│   ├── Marketplace.php     # Package registry (v2.0)
│   ├── Pipeline.php        # Middleware pipeline (v2.0)
│   ├── Migrator.php        # DB migrations (v2.0)
│   ├── Database.php        # PDO Singleton
│   ├── Router.php          # File-based router
│   └── Response.php        # JSON response helpers
├── src/Auth/               # JWT + Guard RBAC
├── src/Testing/            # GeneticTestCase + TestResponse (v2.0)
├── src/Middleware/         # Auth, CORS, RateLimit (v2.0)
├── database/migrations/    # Versioned migration files (v2.0)
├── modules/                # Genetic Modules (plugins)
├── storage/cache/          # File cache store
├── sdk/                    # Frontend SDK (React + Vanilla JS)
├── locales/                # i18n dictionaries (en.json, th.json)
├── genetics            # CLI tool
├── public/                 # Web root (index.php)
└── .env                    # Environment config
```

---

## 🔥 v2.0 Feature Highlights

### Middleware Pipeline

```php
#[Middleware('auth:ADMIN', 'rate:10,60')]
class AdminProducts { ... }
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
