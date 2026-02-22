# OpenGenetics

**Enterprise PHP Micro-Framework** — JWT Auth, Genetic RBAC, i18n, Audit Trail, and Dual-Frontend SDK.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-8892BF.svg)](https://php.net)

---

## Features

- **< 50ms Response** — No ORM overhead, PDO Singleton, static caching
- **JWT Authentication** — HS256 with Bcrypt 12-round password hashing
- **Genetic RBAC** — Admin / HR / Employee guard system in one line
- **File-based Routing** — Drop a PHP file in `api/`, get an endpoint instantly
- **i18n Engine** — Thai/English instant switching via HTTP header
- **Audit Trail** — Non-blocking auto-logging for all mutations
- **Genetic SDK** — React Hook + Vanilla JS for single-line API calls
- **CLI Tool** — Database migrations, seeding, scaffolding

## Quick Start

### Install Globally (Recommended)

```bash
composer global require open-genetics/framework
genetics new my-api
cd my-api
composer install
```

### Install Locally

```bash
git clone https://github.com/open-genetics/framework.git my-api
cd my-api
composer install
cp .env.example .env
```

### Setup

```bash
# Configure your database in .env, then:
php bin/genetics mutate      # Create tables & seed RBAC
php bin/genetics serve       # Start dev server at http://127.0.0.1:8080
```

### Default Admin

```
Email:    admin@opengenetics.io
Password: password
```

## Project Structure

```
my-api/
├── api/                    # File-based route endpoints
│   ├── auth/
│   │   ├── login.php       # POST /api/auth/login
│   │   ├── register.php    # POST /api/auth/register
│   │   └── profile.php     # GET  /api/auth/profile
│   ├── dashboard.php       # GET  /api/dashboard
│   └── users.php           # GET  /api/users
├── bin/
│   └── genetics            # CLI tool
├── src/                    # Framework source (PSR-4)
│   ├── Auth/               # AuthService, Guard, JwtManager
│   ├── Audit/              # AuditLog
│   ├── Core/               # Database, Env, Response, Router
│   └── I18n/               # I18n engine
├── public/                 # Web root
│   └── index.php           # Entry point
├── sdk/                    # Frontend SDK
├── locales/                # i18n dictionaries (en/th)
├── docs/                   # Documentation
└── .env                    # Environment config
```

## CLI Commands

```bash
php bin/genetics mutate            # Create database tables & seed RBAC
php bin/genetics seed              # Seed RBAC roles & admin user
php bin/genetics status            # Show database connection status
php bin/genetics serve             # Start PHP development server
php bin/genetics make:endpoint     # Scaffold a new API endpoint
php bin/genetics make:middleware   # Scaffold a new middleware class
php bin/genetics new <name>        # Create a new project
php bin/genetics help              # Show all commands
```

## Creating an API Endpoint

```php
// api/products.php → GET|POST /api/products

use OpenGenetics\Auth\Guard;
use OpenGenetics\Core\Database;
use OpenGenetics\Core\Response;

class Products
{
    public static function get(array $body): void
    {
        Guard::requireAuth();

        $products = Database::query("SELECT * FROM products WHERE active = :a", ['a' => 1]);
        Response::success($products);
    }

    public static function post(array $body): void
    {
        Guard::requireRole(Guard::ADMIN);

        // ... create product
        Response::success(null, 'Created', 201);
    }
}
```

Or scaffold it:

```bash
php bin/genetics make:endpoint products
```

## Requirements

| Requirement | Version                     |
| ----------- | --------------------------- |
| PHP         | >= 8.1                      |
| MySQL       | >= 5.7                      |
| Composer    | >= 2.x                      |
| Apache      | >= 2.4 (with `mod_rewrite`) |

## Documentation

Full documentation is available in the [`docs/`](docs/index.html) directory.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please send an email to security@opengenetics.io instead of using the issue tracker.

## License

The OpenGenetics framework is open-sourced software licensed under the [MIT license](LICENSE).
