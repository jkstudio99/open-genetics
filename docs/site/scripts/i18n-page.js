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
      th: 'เฟรมเวิร์ค PHP ที่มาพร้อม <strong class="text-white/70 font-medium">JWT Auth</strong>, <strong class="text-white/70 font-medium">RBAC</strong>, <strong class="text-white/70 font-medium">i18n</strong> และ <strong class="text-white/70 font-medium">Genetic SDK</strong> ให้คุณสร้าง REST API ที่ปลอดภัย เร็ว และขยายได้ — บน XAMPP',
      en: 'A PHP framework with built-in <strong class="text-white/70 font-medium">JWT Auth</strong>, <strong class="text-white/70 font-medium">RBAC</strong>, <strong class="text-white/70 font-medium">i18n</strong> and <strong class="text-white/70 font-medium">Genetic SDK</strong>. Build secure, fast, and scalable REST APIs — on XAMPP.'
    },
    'hero.btn.start': { th: 'เริ่มต้นใช้งาน', en: 'Get Started' },
    'hero.btn.docs': { th: 'อ่านคู่มือ', en: 'Read Docs' },

    // ── Features ──
    'feat.title': { th: 'คุณสมบัติ', en: 'Features' },
    'feat.heading': { th: 'พร้อมใช้งาน<span class="grad-text2"> ตั้งแต่วันแรก</span>', en: 'Ready to use<span class="grad-text2"> from day one</span>' },
    'feat.subtitle': { th: 'พร้อมใช้งานตั้งแต่วันแรก ไม่ต้องตั้งค่าซับซ้อน', en: 'Ready to use from day one — no complex configuration needed.' },

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
      th: 'Guard ควบคุมสิทธิ์ Admin, HR, Employee ในระดับ DNA ของทุก API request — เพียงบรรทัดเดียว',
      en: 'Guard controls Admin, HR, Employee access at the DNA level of every API request — in a single line.'
    },

    'feat.i18n.title': { th: 'หลายภาษา (i18n)', en: 'Multi-language (i18n)' },
    'feat.i18n.desc': {
      th: 'รองรับ Thai/English ด้วย JSON dictionaries สลับภาษาผ่าน HTTP Header แบบ real-time ไม่ต้องรีโหลด',
      en: 'Thai/English support with JSON dictionaries. Switch languages via HTTP Header in real-time without reload.'
    },

    'feat.sdk.title': { th: 'Genetic SDK', en: 'Genetic SDK' },
    'feat.sdk.desc': {
      th: 'SDK สำเร็จรูปสำหรับ Vanilla JS และ React Hook — Login, i18n, Theme Switching ด้วยโค้ดบรรทัดเดียว',
      en: 'Ready-made SDK for Vanilla JS and React Hook — Login, i18n, Theme Switching in a single line of code.'
    },

    'feat.audit.title': { th: 'Audit Trail', en: 'Audit Trail' },
    'feat.audit.desc': {
      th: 'บันทึก Log กิจกรรมอัตโนมัติ CREATE/UPDATE/DELETE แบบ Non-blocking — ไม่กระทบประสิทธิภาพ',
      en: 'Auto-log all CREATE/UPDATE/DELETE activities in non-blocking mode — zero performance impact.'
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

    // ── Code Examples ──
    'code.title': { th: 'ตัวอย่างโค้ด', en: 'Code Examples' },
    'code.heading': { th: 'เขียนน้อย <span class="grad-text2">ได้มาก</span>', en: 'Write less <span class="grad-text2">do more</span>' },
    'code.subtitle': { th: 'API พร้อมใช้งานใน 10 บรรทัด — ไม่ต้องประกาศ route', en: 'API ready in 10 lines — no route declaration needed.' },

    // ── Comparison ──
    'cmp.title': { th: 'เปรียบเทียบ', en: 'Comparison' },
    'cmp.heading': { th: 'ทำไมต้อง <span class="grad-text2">OpenGenetics</span>', en: 'Why <span class="grad-text2">OpenGenetics</span>' },
    'cmp.subtitle': { th: 'เทียบกับ framework ยอดนิยมสำหรับ REST API', en: 'Compared with popular REST API frameworks' },
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
    'req.title': { th: 'ความต้องการของระบบ', en: 'System Requirements' },
    'req.heading': { th: 'System <span class="grad-text2">Requirements</span>', en: 'System <span class="grad-text2">Requirements</span>' },
    'req.subtitle': { th: 'ใช้งานได้บน XAMPP ทันที — ไม่ต้องติดตั้งเพิ่ม', en: 'Works on XAMPP instantly — no extra installation needed.' },
    'req.php.detail': { th: 'รองรับ PHP 8.1, 8.2, 8.3', en: 'Supports PHP 8.1, 8.2, 8.3' },
    'req.mysql.detail': { th: 'หรือ MariaDB 10.3+', en: 'or MariaDB 10.3+' },
    'req.apache.label': { th: 'XAMPP / Apache', en: 'XAMPP / Apache' },
    'req.apache.detail': { th: 'เปิด mod_rewrite', en: 'mod_rewrite enabled' },
    'req.mbstring.detail': { th: 'UTF-8 / ภาษาไทย', en: 'UTF-8 / Thai language' },

    // ── Quick Start ──
    'qs.title': { th: 'เริ่มต้นใช้งาน', en: 'Quick Start' },
    'qs.heading': { th: 'เริ่มต้นด้วย<span class="grad-text2"> Composer</span>', en: 'Get started with<span class="grad-text2"> Composer</span>' },
    'qs.subtitle': { th: '5 ขั้นตอน — เหมือนติดตั้ง Laravel', en: '5 steps — just like installing Laravel' },
    'qs.step1': { th: 'ดาวน์โหลดหรือ Clone', en: 'Download or Clone' },
    'qs.step2': { th: 'ติดตั้ง Dependencies', en: 'Install Dependencies' },
    'qs.step3': { th: 'ตั้งค่า Environment', en: 'Configure Environment' },
    'qs.step3.desc': { th: 'สร้างฐานข้อมูลใน phpMyAdmin แล้วแก้ไข .env', en: 'Create a database in phpMyAdmin, then edit .env' },
    'qs.step4': { th: 'Genetic Scaffolding', en: 'Genetic Scaffolding' },
    'qs.step4.desc': { th: 'สร้างตาราง + Seed RBAC + Admin user อัตโนมัติ', en: 'Auto-create tables + Seed RBAC + Admin user' },
    'qs.step5': { th: 'เริ่มสร้าง API', en: 'Start Building APIs' },
    'qs.step5.code': { th: 'สร้างไฟล์ api/your-endpoint.php', en: 'Create file api/your-endpoint.php' },
    'qs.step5.desc': { th: 'เขียน API ได้ทันที — ไม่ต้องประกาศ route', en: 'Write APIs instantly — no route declaration needed' },

    // ── Download CTA ──
    'dl.title': { th: 'ดาวน์โหลด OpenGenetics', en: 'Download OpenGenetics' },
    'dl.subtitle': { th: 'เวอร์ชัน 1.0.0 พร้อมใช้งาน — ฟรีและ Open Source (MIT License)', en: 'Version 1.0.0 ready — Free and Open Source (MIT License)' },
    'dl.btn.github': { th: 'ดาวน์โหลดจาก GitHub', en: 'Download from GitHub' },
    'dl.btn.docs': { th: 'อ่านคู่มือฉบับเต็ม', en: 'Read Full Docs' },

    // ── Footer ──
    'footer.text': { th: 'Enterprise PHP Micro-Framework', en: 'Enterprise PHP Micro-Framework' },
    'footer.license': { th: 'MIT License — ฟรีและ Open Source', en: 'MIT License — Free & Open Source' },

    // ═══ Docs Page ═══
    'doc.title': { th: 'OpenGenetics เอกสาร', en: 'OpenGenetics Documentation' },
    'doc.h1': { th: 'Documentation', en: 'Documentation' },
    'doc.lead': {
      th: 'Enterprise PHP Micro-Framework — JWT Auth, Genetic RBAC, i18n, Audit Trail, Dual-Frontend SDK. ออกแบบมาเพื่อความเร็ว (<50ms), ความปลอดภัย (OWASP), และประสบการณ์นักพัฒนาที่ดี',
      en: 'Enterprise PHP Micro-Framework — JWT Auth, Genetic RBAC, i18n, Audit Trail, Dual-Frontend SDK. Designed for speed (<50ms), security (OWASP), and great developer experience.'
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
    'hero.scroll': { th: 'ดูว่าทำไมนักพัฒนาถึงชอบ OpenGenetics', en: 'See why developers love OpenGenetics' },
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
      th: 'v2.0 — ระบบ Middleware แบบ Chain of Responsibility ทุก request ผ่าน pipeline ก่อนถึง endpoint',
      en: 'v2.0 — Middleware system with Chain of Responsibility pattern. Every request passes through a pipeline before reaching the endpoint.'
    },
    'dh.migrations': { th: '# Database Migrations', en: '# Database Migrations' },
    'dh.migrations.desc': {
      th: 'v2.0 — Version-tracked migrations with rollback — ต่างจาก mutate ที่สร้างทุกอย่างครั้งเดียว',
      en: 'v2.0 — Version-tracked migrations with batch rollback. Different from mutate which creates everything at once.'
    },
    'dh.testing': { th: '# Testing Framework', en: '# Testing Framework' },
    'dh.testing.desc': {
      th: 'v2.0 — Testing Framework ที่รันผ่าน PHPUnit — GeneticTestCase ให้ HTTP test client พร้อม authentication helpers',
      en: 'v2.0 — Testing Framework built on PHPUnit — GeneticTestCase provides an HTTP test client with authentication helpers and fluent assertions.'
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
      th: 'PDO Singleton — <code>EMULATE_PREPARES=false</code> (real prepared statements) ป้องกัน SQL Injection โดยค่าเริ่มต้น',
      en: 'PDO Singleton — <code>EMULATE_PREPARES=false</code> (real prepared statements) prevents SQL Injection by default.'
    },
    'dh.guard.desc': {
      th: 'Guard ตรวจสอบ JWT token และ role ก่อนเข้า endpoint — throw <code>RuntimeException</code> พร้อม HTTP status อัตโนมัติ',
      en: 'Guard validates JWT token and role before entering an endpoint — throws <code>RuntimeException</code> with HTTP status automatically.'
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
      th: 'เครื่องมือ command-line สำหรับ scaffold, migrate และ manage โปรเจกต์',
      en: 'Command-line tool for scaffolding, migrating and managing your project.'
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
