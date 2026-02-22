# Project Plan — OpenGenetics Framework

## Project Overview

**OpenGenetics** คือ Enterprise PHP Micro-Framework ที่ออกแบบมาสำหรับ XAMPP  
เป้าหมายคือให้นักพัฒนาสามารถสร้าง REST API ที่ปลอดภัย เร็ว และขยายได้ — ภายใน 5 นาที

---

## Vision

> "Small, Light, Powerful — ทุก byte และ millisecond มีค่า"

สร้าง Micro-Framework ที่:
- ตอบสนองใต้ **50ms** ทุก request
- มี **JWT Auth + RBAC** ในตัว ไม่ต้องติดตั้งเพิ่ม
- รองรับ **i18n** (Thai/English) แบบ real-time
- มี **Frontend SDK** สำเร็จรูปสำหรับ React และ Vanilla JS
- ใช้ **File-based Routing** — สร้างไฟล์ = ได้ endpoint

---

## Goals & Objectives

### Primary Goals
1. **Zero-config API framework** — ติดตั้งเสร็จ ใช้งานได้ทันที
2. **Sub-50ms response time** — ไม่มี ORM overhead
3. **Built-in security** — JWT + RBAC + OWASP compliance
4. **Developer experience** — CLI scaffolding, auto-routing, SDK

### Secondary Goals
1. Multi-tenancy support via `tenant_id`
2. Non-blocking audit trail
3. Dual-frontend SDK (React Hook + Vanilla JS)
4. Responsive documentation site

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | >= 8.1 |
| Database | MySQL / MariaDB | >= 5.7 / 10.3+ |
| Server | Apache (XAMPP) | >= 2.4 |
| Auth | JWT (HS256) | firebase/php-jwt |
| Password | Bcrypt | 12 rounds |
| Frontend SDK | Vanilla JS + React Hook | ES2020+ |
| Docs Site | HTML + Tailwind CSS + GSAP | Latest |
| Package Manager | Composer | >= 2.x |

---

## Timeline & Milestones

### Phase 1: Core Framework (v0.1.0) ✅
- [x] PDO Singleton Database class
- [x] Environment loader with static caching
- [x] File-based Router
- [x] Standardized JSON Response helper
- [x] Basic CLI tool (`mutate`, `seed`, `status`)

### Phase 2: Authentication & Security (v0.5.0) ✅
- [x] JWT Authentication (HS256)
- [x] Bcrypt 12-round password hashing
- [x] RBAC Guard (Admin, HR, Employee)
- [x] Password reset with token expiration
- [x] CORS middleware
- [x] OWASP-aligned security practices

### Phase 3: Developer Experience (v0.8.0) ✅
- [x] i18n Engine (Thai/English)
- [x] Non-blocking Audit Trail
- [x] CLI scaffolding (`make:endpoint`, `make:middleware`)
- [x] `genetics new` project creator
- [x] `genetics serve` dev server

### Phase 4: Frontend SDK (v0.9.0) ✅
- [x] Vanilla JS SDK (`genetics.min.js`)
- [x] React Hook SDK (`useGenetics`)
- [x] Auth, i18n, Theme Switching integration
- [x] TypeScript type definitions

### Phase 5: Documentation & Release (v1.0.0) ✅
- [x] Landing page (Elysia-style design)
- [x] Full documentation site
- [x] Blog page
- [x] Dark/Light theme support
- [x] Responsive design
- [x] i18n for docs (TH/EN)

### Phase 6: Future (v1.x) 🔮
- [ ] Rate limiting middleware
- [ ] WebSocket support
- [ ] GraphQL adapter
- [ ] Docker compose setup
- [ ] Plugin system
- [ ] Admin dashboard UI

---

## Team Roles

| Role | Responsibility |
|------|---------------|
| **Lead Developer** | Architecture, Core Framework, CLI |
| **Security Engineer** | JWT, RBAC, OWASP compliance |
| **Frontend Developer** | SDK, Landing Page, Docs Site |
| **Technical Writer** | Documentation, API Reference |
| **QA Engineer** | Testing, Performance benchmarks |

---

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|-----------|
| PHP version incompatibility | High | Test on 8.1, 8.2, 8.3 |
| JWT token theft | Critical | Short expiration + HTTPS only |
| SQL injection | Critical | PDO prepared statements only |
| Performance degradation | Medium | No ORM, singleton pattern |
| Dependency vulnerabilities | Medium | Minimal deps (1 optional) |

---

## Success Metrics

- **Response time**: < 50ms per API request
- **Setup time**: < 3 minutes from clone to first API call
- **Dependencies**: Maximum 1 (firebase/php-jwt, optional)
- **Code coverage**: > 80% for core modules
- **Documentation**: 100% of public APIs documented
- **Lighthouse score**: > 90 for docs site

---

## Budget

**$0** — 100% Open Source (MIT License)

- Hosting: Free tier (Railway, Render, Vercel)
- Database: Free tier (PlanetScale, TiDB)
- CDN: Cloudflare (free)
- Domain: Optional
