# Deployment Guide — OpenGenetics (100% Free)

## Free Hosting Options

### 1. Railway.app (Recommended)

**Free tier:** 500 hours/month, 1 GB RAM, free MySQL

```bash
# 1. Install Railway CLI
npm install -g @railway/cli

# 2. Login
railway login

# 3. Init project
cd my-api
railway init

# 4. Add MySQL
railway add --plugin mysql

# 5. Set environment variables
railway variables set JWT_SECRET=$(openssl rand -hex 32)
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set CORS_ORIGIN=https://your-domain.com

# 6. Deploy
railway up
```

**Configure `Procfile`:**
```
web: php -S 0.0.0.0:$PORT -t public/
```

**Configure `nixpacks.toml`:**
```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.pdo_mysql", "php82Extensions.mbstring"]

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader"]
```

---

### 2. Render.com

**Free tier:** 750 hours/month, auto-sleep after 15 min idle

1. Push code to GitHub
2. Go to [render.com](https://render.com) → New Web Service
3. Connect your repo
4. Settings:
   - **Build:** `composer install --no-dev --optimize-autoloader`
   - **Start:** `php -S 0.0.0.0:$PORT -t public/`
5. Add environment variables in the dashboard
6. For MySQL, use [PlanetScale](https://planetscale.com) free tier

---

### 3. Vercel (Serverless PHP)

**Free tier:** unlimited deployments, 100 GB bandwidth

Create `api/index.php` as the entry point and use `vercel-php` runtime:

**`vercel.json`:**
```json
{
  "functions": {
    "api/**/*.php": {
      "runtime": "vercel-php@0.7.1"
    }
  },
  "routes": [
    { "src": "/api/(.*)", "dest": "/api/index.php" }
  ]
}
```

```bash
npm i -g vercel
vercel --prod
```

---

### 4. InfinityFree / 000webhost (Traditional Shared Hosting)

**100% Free, no credit card, PHP + MySQL included.**

1. Sign up at [infinityfree.com](https://infinityfree.com) or [000webhost.com](https://www.000webhost.com)
2. Upload project via File Manager or FTP
3. Create MySQL database in cPanel
4. Update `.env` with your database credentials
5. Run `php bin/genetics mutate` via SSH or web terminal

**Directory structure on shared hosting:**
```
public_html/
├── api/           ← symlink or copy from api/
├── .htaccess
├── index.php      ← entry point
└── ...
```

---

### 5. GitHub Pages + Supabase (Static + API)

**For the landing page + docs only (no PHP backend):**

1. Push `public/` and `docs/` to GitHub
2. Enable GitHub Pages in repo settings
3. Use [Supabase](https://supabase.com) (free) for auth + database
4. Connect via Genetic SDK

---

## Free MySQL Providers

| Provider | Free Tier | Notes |
|----------|-----------|-------|
| [PlanetScale](https://planetscale.com) | 1 DB, 5 GB | MySQL-compatible, serverless |
| [Railway MySQL](https://railway.app) | 1 GB | Included with Railway |
| [Aiven](https://aiven.io) | 1 free MySQL | 1 GB storage |
| [TiDB Cloud](https://tidbcloud.com) | 5 GB, Serverless | MySQL-compatible |
| [FreeSQLDatabase](https://freesqldatabase.com) | 5 MB MySQL | Very basic, for testing |

---

## Production Checklist

```bash
# 1. Environment
APP_DEBUG=false
APP_ENV=production
JWT_SECRET=$(openssl rand -hex 32)
CORS_ORIGIN=https://your-domain.com

# 2. Install dependencies (no dev)
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php bin/genetics mutate

# 4. Verify
php bin/genetics status

# 5. Change default admin password immediately!
```

## Security Hardening

- Set `APP_DEBUG=false` in production
- Use a strong `JWT_SECRET` (64+ hex chars)
- Restrict `CORS_ORIGIN` to your domain only
- Ensure `.env` is in `.gitignore` — never commit secrets
- Set short `JWT_EXPIRATION` (e.g., 3600 for 1 hour)
- Use HTTPS only in production
