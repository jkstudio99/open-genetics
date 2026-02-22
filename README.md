<div align="center">
  <img src="public/images/logo/open-genetics-logo.svg" alt="OpenGenetics Logo" width="200" />
  <br/>
  <h1>OpenGenetics Framework</h1>
  <p><strong>Enterprise PHP Micro-Framework</strong> &mdash; <em>JWT Auth, Genetic RBAC, i18n, Audit Trail, and Dual-Frontend SDK.</em></p>

  <p>
    <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.1%2B-8892BF.svg?logo=php&logoColor=white" alt="PHP Version"></a>
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1.svg?logo=mysql&logoColor=white" alt="MySQL Version">
  </p>
</div>

---

## ✨ Features

- ⚡️ **< 50ms Response** — No ORM overhead, PDO Singleton, static caching
- 🔒 **JWT Authentication** — HS256 with Bcrypt 12-round password hashing
- 🛡️ **Genetic RBAC** — Admin / HR / Employee guard system in one line
- 🗂️ **File-based Routing** — Drop a PHP file in `api/`, get an endpoint instantly
- 🌐 **i18n Engine** — Thai/English instant switching via HTTP header
- 📝 **Audit Trail** — Non-blocking auto-logging for all mutations
- ⚛️ **Dual SDK** — React Hook + Vanilla JS for single-line API calls
- 🛠️ **CLI Tool** — Database migrations, seeding, scaffolding

---

## 🚀 Quick Start (Composer)

Get started in 5 simple steps — just like installing Laravel!

### 1. Create a New Project
```bash
composer create-project open-genetics/framework my-api
cd my-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Environment
Create a database in phpMyAdmin, then configure your `.env` file:
```bash
cp .env.example .env
```
*(Don't forget to update `DB_NAME`, `DB_USER`, `DB_PASS`, and `JWT_SECRET`)*

### 4. Genetic Scaffolding
Create all tables, seed the RBAC roles, and create the default Admin user automatically:
```bash
php bin/genetics mutate
```

### 5. Start the Dev Server
Ready to write APIs—no route declarations required!
```bash
php bin/genetics serve
```
> Server runs at `http://127.0.0.1:8080`.

---

## 🔑 Default Admin Account
After running `mutate`, a default admin is created:
- **Email:** `admin@opengenetics.io`
- **Password:** `password`

*(Please change this password immediately in production)*

---

## 🏗️ Project Structure

```text
my-api/
├── api/                    # File-based route endpoints
│   ├── auth/               # Auth routes (login, register...)
│   └── dashboard.php       # GET  /api/dashboard
├── bin/genetics            # CLI tool (mutate, seed, serve, scaffold)
├── src/                    # Framework core (PSR-4)
│   ├── Auth/               # AuthService, Guard, JwtManager
│   ├── Audit/              # AuditLog (non-blocking)
│   ├── Core/               # Database, Env, Router, Response
│   └── I18n/               # Multi-language engine (Thai/English)
├── public/                 # Web root (index.php)
├── sdk/                    # Frontend SDK (Vanilla JS + React Hook)
├── locales/                # i18n dictionaries (en.json, th.json)
└── .env                    # Environment config
```

---

## 💻 Building an Endpoint

Just drop a file in the `api/` directory.

```php
// api/products.php 
// Accessible via GET or POST /api/products

use OpenGenetics\Auth\Guard;
use OpenGenetics\Core\Database;
use OpenGenetics\Core\Response;

class Products
{
    public static function get(array $body): void
    {
        // Require user to be logged in
        Guard::requireAuth();

        $products = Database::query("SELECT * FROM products WHERE active = 1");
        Response::success($products);
    }

    public static function post(array $body): void
    {
        // Require ADMIN role
        Guard::requireRole(Guard::ADMIN);

        // ... create product logic here
        Response::success(null, 'Product Created', 201);
    }
}
```

Or instantly scaffold it via CLI:
```bash
php bin/genetics make:endpoint products
```

---

## 📚 Documentation

The full official documentation is available inside the project:
* Open `public/index.html` or `docs/site/overview.html` in your browser.
* Or view live documentation if deployed.

---

## 🛡️ Security

If you discover a security vulnerability, please send an email to `security@opengenetics.io` instead of using the issue tracker.

## 📄 License

The OpenGenetics framework is open-sourced software licensed under the [MIT license](LICENSE).
