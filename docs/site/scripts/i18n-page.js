/**
 * OpenGenetics — Static Page i18n Engine
 * Supports TH/EN switching via data-i18n attributes.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'og_locale';

  // ═══════════════════════════════════════════════════
  // Translation Dictionaries
  // ═══════════════════════════════════════════════════

  const dict = {
    // ── Navbar ──
    'nav.features': { th: 'คุณสมบัติ', en: 'Features' },
    'nav.code': { th: 'ตัวอย่างโค้ด', en: 'Code Examples' },
    'nav.quickstart': { th: 'เริ่มต้นใช้งาน', en: 'Quick Start' },
    'nav.compare': { th: 'เปรียบเทียบ', en: 'Compare' },
    'nav.docs': { th: 'เอกสาร', en: 'Docs' },
    'nav.blog': { th: 'บล็อก', en: 'Blog' },

    // ── Hero ──
    'hero.subtitle': {
      th: 'PHP Micro-Framework ที่มาพร้อม <strong class="text-white/70 font-medium">JWT Auth</strong>, <strong class="text-white/70 font-medium">RBAC</strong>, <strong class="text-white/70 font-medium">Query Builder</strong> และ <strong class="text-white/70 font-medium">Genetic SDK</strong> — ทุกอย่างพร้อมตั้งแต่คำสั่งแรก ไม่ต้องเขียน boilerplate อีกต่อไป',
      en: 'PHP Micro-Framework with built-in <strong class="text-white/70 font-medium">JWT Auth</strong>, <strong class="text-white/70 font-medium">RBAC</strong>, <strong class="text-white/70 font-medium">Query Builder</strong> and <strong class="text-white/70 font-medium">Genetic SDK</strong> — everything ready from the first command. No more boilerplate.'
    },
    'hero.btn.start': { th: 'เริ่มต้นใช้งาน', en: 'Get Started' },
    'hero.btn.docs': { th: 'อ่านคู่มือ', en: 'Read Docs' },

    // ── Features ──
    'feat.title': { th: 'คุณสมบัติ', en: 'Features' },
    'feat.heading': { th: 'ทุกอย่างที่ต้องการ<span class="grad-text2"> อยู่ในกล่องเดียว</span>', en: 'Everything you need<span class="grad-text2"> in one box</span>' },
    'feat.subtitle': { th: 'ไม่ต้องติดตั้ง package เพิ่ม — Auth, RBAC, Query Builder, Middleware, Cache, Testing พร้อมทันที', en: 'No extra packages — Auth, RBAC, Query Builder, Middleware, Cache, and Testing ready out of the box.' },

    'feat.speed.title': { th: 'รวดเร็ว < 50ms', en: 'Fast < 50ms' },
    'feat.speed.desc': {
      th: 'Micro-framework ที่เบาที่สุด — ไม่มี ORM overhead, ใช้ PDO Singleton และ File-based Routing ตอบสนองใต้ 50 มิลลิวินาที',
      en: 'Ultra-light micro-framework — no ORM overhead, PDO Singleton and File-based Routing. Responds under 50 milliseconds.'
    },

    'feat.jwt.title': { th: 'JWT Authentication', en: 'JWT Authentication' },
    'feat.jwt.desc': {
      th: 'ระบบยืนยันตัวตน HS256 ในตัว พร้อม Bcrypt 12 รอบ รองรับทั้ง firebase/php-jwt และ native PHP fallback',
      en: 'Built-in HS256 authentication with Bcrypt 12 rounds. Supports both firebase/php-jwt and native PHP fallback.'
    },

    'feat.rbac.title': { th: 'RBAC 3 ระดับ', en: '3-Level RBAC' },
    'feat.rbac.desc': {
      th: 'Guard ควบคุมสิทธิ์ Admin, Manager, Employee ในระดับ DNA ของทุก API request — เพียงบรรทัดเดียว',
      en: 'Guard controls Admin, Manager, Employee access at the DNA level of every API request — in a single line.'
    },

    'feat.i18n.title': { th: 'i18n Engine', en: 'i18n Engine' },
    'feat.i18n.desc': {
      th: 'สลับภาษา Thai/English ทันทีผ่าน X-Locale header หรือ ?lang= param — Lazy load ประหยัด ~0.5ms',
      en: 'Switch Thai/English instantly via X-Locale header or ?lang= param — lazy load saves ~0.5ms.'
    },

    'feat.sdk.title': { th: 'Dual SDK', en: 'Dual SDK' },
    'feat.sdk.desc': {
      th: 'React Hook + Vanilla JS SDK พร้อมใช้งาน — ครอบคลุม auth, RBAC, i18n และ API calls ในไฟล์เดียว',
      en: 'Ready-made React Hook + Vanilla JS SDK — covers auth, RBAC, i18n and API calls in a single file.'
    },

    'feat.audit.title': { th: 'Audit Trail', en: 'Audit Trail' },
    'feat.audit.desc': {
      th: 'บันทึก log ทุก action อัตโนมัติแบบ Non-blocking — ติดตาม CREATE, UPDATE, DELETE, LOGIN ได้ทันที',
      en: 'Auto-log every action non-blocking — track CREATE, UPDATE, DELETE, LOGIN instantly.'
    },

    'feat.route.title': { th: 'File-based Routing', en: 'File-based Routing' },
    'feat.route.desc': {
      th: 'แต่ละไฟล์ .php ใน api/ คือ 1 endpoint — ไม่ต้องประกาศ route ง่ายเหมือนสร้างไฟล์',
      en: 'Each .php file in api/ becomes an endpoint — no route declaration needed. Simple as creating a file.'
    },

    'feat.cli.title': { th: 'Genetic CLI', en: 'Genetic CLI' },
    'feat.cli.desc': {
      th: 'คำสั่ง php genetics mutate สร้างตาราง, Seed RBAC, และ Admin user อัตโนมัติ',
      en: 'Run php genetics mutate to auto-create tables, seed RBAC, and admin user.'
    },

    'feat.psr4.title': { th: 'PSR-4 Autoloading', en: 'PSR-4 Autoloading' },
    'feat.psr4.desc': {
      th: 'โครงสร้าง Namespace ตามมาตรฐาน PSR-4 ใช้ร่วมกับ Composer autoloader ได้ทันที',
      en: 'PSR-4 standard namespace structure. Works with Composer autoloader out of the box.'
    },

    'feat.mw.title': { th: 'Middleware Pipeline', en: 'Middleware Pipeline' },
    'feat.mw.desc': {
      th: 'Chain of Responsibility pattern — CORS, Auth, RateLimit พร้อม <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">#[Middleware]</code> attribute',
      en: 'Chain of Responsibility pattern — CORS, Auth, RateLimit with <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">#[Middleware]</code> attribute.'
    },

    'feat.cache.title': { th: 'Caching Layer', en: 'Caching Layer' },
    'feat.cache.desc': {
      th: 'File-based cache พร้อม TTL, namespace isolation และ <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">remember()</code> pattern — ไม่ต้องติดตั้ง Redis',
      en: 'File-based cache with TTL, namespace isolation and <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">remember()</code> pattern — no Redis needed.'
    },

    'feat.admin.title': { th: 'Admin Generator', en: 'Admin Generator' },
    'feat.admin.desc': {
      th: 'สร้าง CRUD admin API อัตโนมัติจาก DB schema — <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">php genetics make:admin users</code>',
      en: 'Auto-generate CRUD admin API from DB schema — <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">php genetics make:admin users</code>'
    },

    'feat.market.title': { th: 'Marketplace', en: 'Marketplace' },
    'feat.market.desc': {
      th: 'ติดตั้ง community packages ผ่าน CLI — JWT Refresh, 2FA, File Upload, Notifications พร้อมใช้ทันที',
      en: 'Install community packages via CLI — JWT Refresh, 2FA, File Upload, Notifications ready to use.'
    },

    // ── Query Builder & Testing ──
    'feat.qb.title': { th: 'Query Builder', en: 'Query Builder' },
    'feat.qb.desc': {
      th: 'Fluent Query Builder บน PDO — <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">DB::table(\'users\')->where(\'active\',1)->paginate(20)</code> พร้อม prepared statements ทุกคำสั่ง',
      en: 'Fluent Query Builder on PDO — <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">DB::table(\'users\')->where(\'active\',1)->paginate(20)</code> with prepared statements on every query.'
    },

    'feat.test.title': { th: 'Testing Framework', en: 'Testing Framework' },
    'feat.test.desc': {
      th: 'HTTP test client พร้อม <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">actingAs()</code>, <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">seed()</code> และ fluent assertions — ทดสอบ API ได้ทันทีโดยไม่ต้อง mock',
      en: 'HTTP test client with <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">actingAs()</code>, <code class="text-indigo-300 bg-indigo-500/10 px-1 rounded text-xs">seed()</code> and fluent assertions — test APIs instantly without mocking.'
    },

    // ── Code Examples ──
    'code.title': { th: 'ตัวอย่างโค้ด', en: 'Code Examples' },
    'code.heading': { th: 'โค้ดที่อ่านง่าย<span class="grad-text2"> ผลลัพธ์ที่ทรงพลัง</span>', en: 'Clean Code<span class="grad-text2"> Powerful Results</span>' },
    'code.subtitle': { th: 'สร้างไฟล์ → ได้ endpoint ทันที — ไม่มี routing config, ไม่มี boilerplate', en: 'Create a file → get an endpoint instantly — no routing config, no boilerplate.' },

    // ── Comparison ──
    'cmp.title': { th: 'เปรียบเทียบ', en: 'Comparison' },
    'cmp.heading': { th: 'ทำไมไม่ใช้<span class="grad-text2"> Laravel หรือ Slim?</span>', en: 'Why not<span class="grad-text2"> Laravel or Slim?</span>' },
    'cmp.subtitle': { th: 'คำตอบอยู่ในตารางเดียว — ทุก feature พร้อมโดยไม่ต้องติดตั้ง package เพิ่ม', en: 'One table answers it all — every feature ready without installing extra packages.' },
    'cmp.feature': { th: 'คุณสมบัติ', en: 'Feature' },
    'cmp.jwt': { th: 'JWT Auth ในตัว', en: 'Built-in JWT Auth' },
    'cmp.rbac': { th: 'RBAC ในตัว', en: 'Built-in RBAC' },
    'cmp.i18n': { th: 'i18n ในตัว', en: 'Built-in i18n' },
    'cmp.sdk': { th: 'Frontend SDK', en: 'Frontend SDK' },
    'cmp.audit': { th: 'Audit Trail', en: 'Audit Trail' },
    'cmp.cli': { th: 'CLI Tool', en: 'CLI Tool' },
    'cmp.routing': { th: 'File-based Routing', en: 'File-based Routing' },
    'cmp.deps': { th: 'Dependencies', en: 'Dependencies' },
    'cmp.need_install': { th: 'ต้องติดตั้ง', en: 'Install required' },
    'cmp.builtin': { th: '✅ ในตัว', en: '✅ Built-in' },
    'cmp.3levels': { th: '✅ 3 ระดับ', en: '✅ 3 levels' },
    'cmp.optional': { th: '1 (optional)', en: '1 (optional)' },

    // ── Requirements ──
    'req.title': { th: 'ความต้องการ', en: 'Requirements' },
    'req.heading': { th: 'ทำงานได้บน<span class="grad-text2"> XAMPP ทันที</span>', en: 'Runs on<span class="grad-text2"> XAMPP Instantly</span>' },
    'req.subtitle': { th: 'ไม่ต้องติดตั้ง Redis, Docker หรือ Nginx — PHP 8.1 + MySQL + Composer ก็พอ', en: 'No Redis, Docker, or Nginx — PHP 8.1 + MySQL + Composer is all you need.' },
    'req.php.detail': { th: 'รองรับ PHP 8.1, 8.2, 8.3', en: 'Supports PHP 8.1, 8.2, 8.3' },
    'req.mysql.detail': { th: 'หรือ MariaDB 10.3+', en: 'or MariaDB 10.3+' },
    'req.apache.label': { th: 'XAMPP / Apache', en: 'XAMPP / Apache' },
    'req.apache.detail': { th: 'เปิด mod_rewrite', en: 'mod_rewrite enabled' },
    'req.mbstring.detail': { th: 'UTF-8 / ภาษาไทย', en: 'UTF-8 / Thai language' },

    // ── Quick Start ──
    'qs.title': { th: 'เริ่มต้นใช้งาน', en: 'Quick Start' },
    'qs.heading': { th: 'จาก Terminal สู่<span class="grad-text2"> API พร้อมใช้</span>', en: 'From Terminal to<span class="grad-text2"> Ready API</span>' },
    'qs.subtitle': { th: '5 ขั้นตอน — API ที่มี Auth, RBAC และ Database พร้อมทดสอบ', en: '5 steps — API with Auth, RBAC, and Database ready to test.' },
    'qs.step1': { th: 'สร้างโปรเจกต์', en: 'Create Project' },
    'qs.step2': { th: 'ติดตั้ง Dependencies', en: 'Install Dependencies' },
    'qs.step3': { th: 'ตั้งค่า Environment', en: 'Configure Environment' },
    'qs.step3.desc': { th: 'สร้างฐานข้อมูลใน phpMyAdmin แล้วแก้ไข .env: DB_HOST, DB_NAME, DB_PASS', en: 'Create a database in phpMyAdmin then edit .env: DB_HOST, DB_NAME, DB_PASS' },
    'qs.step4': { th: 'Genetic Scaffolding', en: 'Genetic Scaffolding' },
    'qs.step4.desc': { th: 'สร้างตาราง + Seed RBAC + Admin user อัตโนมัติ — Default: admin@opengenetics.io / password', en: 'Auto-create tables + Seed RBAC + Admin user — Default: admin@opengenetics.io / password' },
    'qs.step5': { th: 'เริ่มสร้าง API', en: 'Start Building APIs' },
    'qs.step5.code': { th: 'สร้างไฟล์ api/your-endpoint.php', en: 'Create file api/your-endpoint.php' },
    'qs.step5.desc': { th: 'เปิด dev server ที่ http://127.0.0.1:8080 — สร้างไฟล์ api/your-endpoint.php ได้ทันที', en: 'Start dev server at http://127.0.0.1:8080 — create api/your-endpoint.php and start building.' },

    // ── Download CTA ──
    'dl.title': { th: 'พร้อมสร้าง API<span class="grad-text2"> ชิ้นแรกแล้วหรือยัง?</span>', en: 'Ready to build<span class="grad-text2"> your first API?</span>' },
    'dl.subtitle': { th: 'v2.3.0 — ฟรีและ Open Source (MIT License)<br>หนึ่งคำสั่ง สร้าง API ที่มี Auth, RBAC และ Database พร้อมใช้ทันที', en: 'v2.3.0 — Free and Open Source (MIT License)<br>One command. Auth, RBAC, and Database ready instantly.' },
    'dl.btn.github': { th: 'เริ่มต้นบน GitHub', en: 'Get Started on GitHub' },
    'dl.btn.docs': { th: 'อ่าน Documentation', en: 'Read Documentation' },

    // ── Footer ──
    'footer.text': { th: 'Enterprise PHP Micro-Framework', en: 'Enterprise PHP Micro-Framework' },
    'footer.license': { th: 'MIT License — ฟรีและ Open Source', en: 'MIT License — Free & Open Source' },

    // ═══ Docs Page ═══
    'doc.title': { th: 'OpenGenetics เอกสาร', en: 'OpenGenetics Documentation' },
    'doc.h1': { th: 'Documentation', en: 'Documentation' },
    'doc.lead': {
      th: 'Enterprise PHP Micro-Framework v2.3 — JWT Auth, Genetic RBAC, Query Builder, i18n, Audit Trail, Testing Framework, Middleware Pipeline, Caching, Admin Generator และ Dual-Frontend SDK. ออกแบบมาเพื่อความเร็ว (<50ms), ความปลอดภัย (OWASP), และประสบการณ์นักพัฒนาที่ดี',
      en: 'Enterprise PHP Micro-Framework v2.3 — JWT Auth, Genetic RBAC, Query Builder, i18n, Audit Trail, Testing Framework, Middleware Pipeline, Caching, Admin Generator and Dual-Frontend SDK. Designed for speed (<50ms), security (OWASP), and great developer experience.'
    },
    'doc.philosophy': {
      th: 'ปรัชญา: เล็ก เบา ทรงพลัง — ทุก byte และ millisecond มีค่า',
      en: 'Philosophy: Small, Light, Powerful — Every byte and millisecond matters.'
    },

    // Docs sidebar groups
    'sb.getting_started': { th: 'เริ่มต้น', en: 'Getting Started' },
    'sb.structure': { th: 'โครงสร้าง', en: 'Structure' },
    'sb.api_ref': { th: 'API Reference', en: 'API Reference' },
    'sb.tools': { th: 'เครื่องมือ', en: 'Tools' },
    // TOC
    'toc.title': { th: 'สารบัญ', en: 'On this page' },
    // Hero scroll
    'hero.scroll': { th: 'ทำไมนักพัฒนาถึงเลือก OpenGenetics', en: 'Why developers choose OpenGenetics' },
    // Docs page navigation
    'nav.prev': { th: 'ก่อนหน้า', en: 'Previous' },
    'nav.next': { th: 'ถัดไป', en: 'Next' },

    // Docs sidebar links
    'ds.overview': { th: 'ภาพรวม', en: 'Overview' },
    'ds.installation': { th: 'การติดตั้ง', en: 'Installation' },
    'ds.configuration': { th: 'การตั้งค่า', en: 'Configuration' },
    'ds.directory': { th: 'โครงสร้างโปรเจค', en: 'Directory Structure' },
    'ds.architecture': { th: 'สถาปัตยกรรม', en: 'Architecture' },
    'ds.lifecycle': { th: 'Request Lifecycle', en: 'Request Lifecycle' },
    'ds.middleware': { th: 'Middleware Pipeline', en: 'Middleware Pipeline' },
    'ds.routing': { th: 'การ Routing', en: 'Routing' },
    'ds.database': { th: 'ฐานข้อมูล', en: 'Database' },
    'ds.migrations': { th: 'Migrations', en: 'Migrations' },
    'ds.response': { th: 'Response', en: 'Response' },
    'ds.environment': { th: 'Environment', en: 'Environment' },
    'ds.authentication': { th: 'การยืนยันตัวตน', en: 'Authentication' },
    'ds.authorization': { th: 'การกำหนดสิทธิ์', en: 'Authorization (Guards)' },
    'ds.security': { th: 'ความปลอดภัย & OWASP', en: 'Security & OWASP' },
    'ds.i18n': { th: 'หลายภาษา (i18n)', en: 'i18n' },
    'ds.audit': { th: 'Audit Trail', en: 'Audit Trail' },
    'ds.testing': { th: 'Testing Framework', en: 'Testing Framework' },
    'ds.cache': { th: 'Caching Layer', en: 'Caching Layer' },
    'ds.field_selector': { th: 'Field Selector', en: 'Field Selector' },
    'ds.pulse': { th: 'Genetic Pulse (SSE)', en: 'Genetic Pulse (SSE)' },
    'ds.modules': { th: 'Genetic Modules', en: 'Genetic Modules' },
    'ds.admin_generator': { th: 'Admin Generator', en: 'Admin Generator' },
    'ds.endpoint_ai': { th: 'Endpoint AI', en: 'Endpoint AI' },
    'ds.marketplace': { th: 'Marketplace', en: 'Marketplace' },
    'ds.sdk': { th: 'Genetic SDK', en: 'Genetic SDK' },
    'ds.api_ref': { th: 'API Reference', en: 'API Reference' },
    'ds.cli': { th: 'CLI Tool', en: 'CLI Tool' },
    'ds.openapi': { th: 'OpenAPI Generator', en: 'OpenAPI Generator' },
    'ds.deployment': { th: 'การ Deploy', en: 'Deployment' },

    // Docs section headings
    'dh.installation': { th: '# การติดตั้ง', en: '# Installation' },
    'dh.sysreq': { th: 'ความต้องการของระบบ', en: 'System Requirements' },
    'dh.global_install': { th: 'ติดตั้งแบบ Global (แนะนำ)', en: 'Global Install (Recommended)' },
    'dh.manual_install': { th: 'ติดตั้งด้วยตนเอง', en: 'Manual Install' },
    'dh.initial_setup': { th: 'ตั้งค่าเริ่มต้น', en: 'Initial Setup' },
    'dh.configuration': { th: '# Configuration', en: '# Configuration' },
    'dh.dir_structure': { th: '# Directory Structure', en: '# Directory Structure' },
    'dh.architecture': { th: '# Architecture', en: '# Architecture' },
    'dh.lifecycle': { th: '# Request Lifecycle', en: '# Request Lifecycle' },
    'dh.routing': { th: '# Routing', en: '# Routing' },
    'dh.database': { th: '# Database', en: '# Database' },
    'dh.response': { th: '# Response', en: '# Response' },
    'dh.environment': { th: '# Environment', en: '# Environment' },
    'dh.authentication': { th: '# Authentication', en: '# Authentication' },
    'dh.authorization': { th: '# Authorization (Guards)', en: '# Authorization (Guards)' },
    'dh.security': { th: '# Security & OWASP', en: '# Security & OWASP' },
    'dh.i18n': { th: '# Internationalization (i18n)', en: '# Internationalization (i18n)' },
    'dh.audit': { th: '# Audit Trail', en: '# Audit Trail' },
    'dh.middleware': { th: '# Middleware Pipeline', en: '# Middleware Pipeline' },
    'dh.middleware.desc': {
      th: 'v2.2 — ระบบ Middleware แบบ Chain of Responsibility ทุก request ผ่าน pipeline ก่อนถึง endpoint รองรับ #[SkipMiddleware] และ Pipeline::after()',
      en: 'v2.2 — Middleware system with Chain of Responsibility pattern. Every request passes through a pipeline before reaching the endpoint. Supports #[SkipMiddleware] and Pipeline::after().'
    },
    'dh.migrations': { th: '# Database Migrations', en: '# Database Migrations' },
    'dh.migrations.desc': {
      th: 'v2.0 — Version-tracked migrations with rollback — ต่างจาก mutate ที่สร้างทุกอย่างครั้งเดียว',
      en: 'v2.0 — Version-tracked migrations with batch rollback. Different from mutate which creates everything at once.'
    },
    'dh.testing': { th: '# Testing Framework', en: '# Testing Framework' },
    'dh.testing.desc': {
      th: 'v2.2 — Testing Framework ที่รันผ่าน PHPUnit — GeneticTestCase ให้ HTTP test client พร้อม actingAs(), seed() และ fluent assertions',
      en: 'v2.2 — Testing Framework built on PHPUnit — GeneticTestCase provides an HTTP test client with actingAs(), seed() and fluent assertions.'
    },
    'dh.sdk': { th: '# Genetic SDK', en: '# Genetic SDK' },
    'dh.api_ref': { th: '# API Reference', en: '# API Reference' },
    'dh.cli': { th: '# CLI Tool', en: '# CLI Tool' },
    'dh.openapi': { th: '# OpenAPI Generator', en: '# OpenAPI Generator' },
    'dh.openapi.desc': { th: 'ระบบสร้าง OpenAPI 3.0 (Swagger) อัตโนมัติจากไฟล์ในโฟลเดอร์ api/ และ PHPDoc comments', en: 'Automatically generates OpenAPI 3.0 (Swagger) from api/ files and PHPDoc comments.' },
    'dh.openapi.setup': { th: 'การสร้าง Specification', en: 'Generating Specification' },
    'dh.openapi.setup.desc': { th: 'ใช้คำสั่งสคริปต์ CLI เพื่อสร้างไฟล์ JSON สำหรับ OpenAPI:', en: 'Use the CLI script to generate the OpenAPI JSON file:' },
    'dh.openapi.output': { th: 'คำสั่งนี้จะทำการ scan โฟลเดอร์ <code>api/</code> ทั้งหมดเพื่อสร้างไฟล์ <code>public/openapi.json</code>', en: 'This command scans all <code>api/</code> endpoints to generate <code>public/openapi.json</code>' },
    'dh.openapi.phpdoc': { th: 'การเขียน PHPDoc', en: 'Writing PHPDoc' },
    'dh.openapi.phpdoc.desc': { th: 'คุณสามารถใช้ PHPDoc บน class ของ endpoint เพื่อเพิ่มคำอธิบายใน API Spec:', en: 'You can use PHPDoc on the endpoint class to add a description to the API Spec:' },
    'dh.deployment': { th: '# Deployment', en: '# Deployment' },

    // Doc sub-headings
    'dh.steps': { th: 'ขั้นตอนการติดตั้ง', en: 'Installation Steps' },
    'dh.endpoint_template': { th: 'Endpoint Template', en: 'Endpoint Template' },
    'dh.scaffolding': { th: 'Scaffolding', en: 'Scaffolding' },
    'dh.auth_endpoints': { th: 'Authentication Endpoints', en: 'Authentication Endpoints' },
    'dh.req_res_format': { th: 'Request / Response Format', en: 'Request / Response Format' },
    'dh.auth_header': { th: 'Authorization Header', en: 'Authorization Header' },
    'dh.locale_files': { th: 'ไฟล์ภาษา (Locale Files)', en: 'Locale Files' },
    'dh.react_hook': { th: 'React Hook', en: 'React Hook' },
    'dh.vanilla_js': { th: 'Vanilla JS', en: 'Vanilla JS' },
    'dh.docs_generate': { th: 'docs:generate', en: 'docs:generate' },
    'dh.make_endpoint': { th: 'make:endpoint', en: 'make:endpoint' },
    'dh.apache_htaccess': { th: 'Apache .htaccess', en: 'Apache .htaccess' },
    'dh.prod_checklist': { th: 'Production Checklist', en: 'Production Checklist' },

    // Installation steps
    'install.desc': { th: '5 ขั้นตอน — เหมือนติดตั้ง Laravel', en: '5 steps — just like installing Laravel' },
    'install.step1': { th: '1. ดาวน์โหลดหรือ Clone', en: '1. Download or Clone' },
    'install.step2': { th: '2. ติดตั้ง Dependencies', en: '2. Install Dependencies' },
    'install.step3': { th: '3. ตั้งค่า Environment', en: '3. Configure Environment' },
    'install.step3_desc': { th: 'สร้างฐานข้อมูลใน phpMyAdmin แล้วแก้ไขไฟล์ <code>.env</code>', en: 'Create a database in phpMyAdmin and configure <code>.env</code> file' },
    'install.step4': { th: '4. Genetic Scaffolding', en: '4. Genetic Scaffolding' },
    'install.step4_desc': { th: 'สร้างตาราง + Seed RBAC + Admin user อัตโนมัติ', en: 'Auto-create tables + Seed RBAC + Admin user' },
    'install.step5': { th: '5. เริ่มสร้าง API', en: '5. Start Building API' },
    'install.step5_desc': { th: 'เขียน API ได้ทันที — ไม่ต้องประกาศ route', en: 'Write APIs instantly — no route declaration needed' },
    'install.default_admin': {
      th: '<strong>Default Admin:</strong> <code>admin@opengenetics.io</code> / <code>password</code> &mdash; เปลี่ยนรหัสผ่านทันทีหลัง deploy',
      en: '<strong>Default Admin:</strong> <code>admin@opengenetics.io</code> / <code>password</code> &mdash; ปลี่ยนรหัสผ่านทันทีหลัง deploy'
    },

    // Doc description paragraphs
    'dh.configuration.desc': {
      th: 'ค่าทั้งหมดจัดการผ่านไฟล์ <code>.env</code> — class <code>Env</code> จะ load ครั้งเดียวแล้ว cache ใน static array',
      en: 'All configuration is managed via <code>.env</code> — the <code>Env</code> class loads once and caches in a static array.'
    },
    'dh.architecture.desc': {
      th: 'OpenGenetics ใช้ <strong>File-based Routing</strong> — URL path ตรงกับ file path ใน <code>api/</code> โดยตรง ไม่มี route registry',
      en: 'OpenGenetics uses <strong>File-based Routing</strong> — URL paths map directly to file paths in <code>api/</code>. No route registry needed.'
    },
    'dh.database.desc': {
      th: 'v2.2 — เข้าถึงฐานข้อมูลได้ 2 วิธี: <strong>Query Builder</strong> (<code>DB::table()</code>) สำหรับ dynamic queries ที่อ่านง่าย และ <strong>Raw SQL</strong> (<code>Database::query()</code>) สำหรับ queries ที่ซับซ้อน — ทั้งคู่ใช้ PDO prepared statements ป้องกัน SQL Injection โดยค่าเริ่มต้น',
      en: 'v2.2 — Two ways to query: <strong>Query Builder</strong> (<code>DB::table()</code>) for readable dynamic queries, and <strong>Raw SQL</strong> (<code>Database::query()</code>) for complex operations — both use PDO prepared statements to prevent SQL Injection by default.'
    },
    'dh.guard.desc': {
      th: 'Guard ตรวจสอบ JWT token และ role ก่อนเข้า endpoint — ใช้ <code>requireAuth()</code> / <code>requireRole()</code> แบบ strict (throw 401/403) หรือ <code>Guard::check()</code> แบบ soft (คืนค่า <code>bool</code>) สำหรับ endpoint ที่ auth เป็น optional',
      en: 'Guard validates JWT token and role before entering an endpoint — use <code>requireAuth()</code> / <code>requireRole()</code> for strict enforcement (throws 401/403), or <code>Guard::check()</code> for soft auth that returns <code>bool</code> for optional-auth endpoints.'
    },
    'dh.i18n.desc': {
      th: 'สลับภาษาทันทีผ่าน <code>X-Locale</code> header หรือ <code>?lang=</code> param',
      en: 'Switch language instantly via <code>X-Locale</code> header or <code>?lang=</code> param.'
    },
    'dh.sdk.desc': {
      th: 'Frontend SDK พร้อมใช้งานทั้ง React Hook และ Vanilla JS — ครอบคลุม auth, RBAC, i18n และ API calls',
      en: 'Frontend SDK ready for both React Hook and Vanilla JS — covers auth, RBAC, i18n and API calls.'
    },
    'dh.cli.desc': {
      th: 'Genetics CLI คือหัวใจของ Developer Experience ใน OpenGenetics — 25+ คำสั่งที่ลดงานซ้ำซ้อน ตั้งแต่ Scaffold endpoint, จัดการ Migration ไปจนถึง AI-assisted code generation ที่สร้าง endpoint พร้อม auth และ cache ในเสี้ยววินาที',
      en: 'The Genetics CLI is the heart of OpenGenetics DX — 25+ commands that eliminate boilerplate: scaffold endpoints, manage migrations, and generate production-ready code with auth and caching in seconds.'
    },
    'dh.cache.desc': {
      th: 'v2.2 — File-based cache พร้อม TTL, tag-based invalidation, <code>remember()</code> pattern และ <code>Cache::namespace()</code> สำหรับแยก key space ระหว่าง module — ไม่ต้องติดตั้ง Redis หรือ Memcached',
      en: 'v2.2 — File-based cache with TTL, tag-based invalidation, <code>remember()</code> pattern, and <code>Cache::namespace()</code> to isolate key spaces between modules — no Redis or Memcached required.'
    },
  };

  // ═══════════════════════════════════════════════════
  // Engine
  // ═══════════════════════════════════════════════════

  function getLocale() {
    return localStorage.getItem(STORAGE_KEY) || 'th';
  }

  function setLocale(locale) {
    localStorage.setItem(STORAGE_KEY, locale);
    applyLocale(locale);
  }

  function t(key) {
    const locale = getLocale();
    const entry = dict[key];
    if (!entry) return key;
    return entry[locale] || entry['en'] || key;
  }

  function applyLocale(locale) {
    document.documentElement.setAttribute('lang', locale);

    // Update all data-i18n elements
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      const entry = dict[key];
      if (!entry) return;
      const text = entry[locale] || entry['en'] || '';
      if (/<[a-z][\s\S]*>/i.test(text)) {
        el.innerHTML = text;
      } else {
        el.textContent = text;
      }
    });

    // Update all data-i18n-html elements (HTML content)
    document.querySelectorAll('[data-i18n-html]').forEach(el => {
      const key = el.getAttribute('data-i18n-html');
      const entry = dict[key];
      if (!entry) return;
      const text = entry[locale] || entry['en'] || '';
      el.innerHTML = text;
    });

    // Update title
    const titleEntry = dict['page.title'];
    if (titleEntry) {
      document.title = titleEntry[locale] || titleEntry['en'];
    }

    // Update language toggle button state
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.classList.toggle('active', btn.getAttribute('data-lang') === locale);
    });
  }

  // ═══════════════════════════════════════════════════
  // Init
  // ═══════════════════════════════════════════════════

  function init() {
    const locale = getLocale();
    applyLocale(locale);

    // Bind language toggle buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        setLocale(btn.getAttribute('data-lang'));
      });
    });
  }

  // Export for external toggles
  window.OGi18n = {
    setLocale,
    applyLocale,
    getLocale
  };

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose globally
  window.OGi18n = { setLocale, getLocale, t, dict };
})();
