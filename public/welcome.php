<?php
/**
 * 🧬 OpenGenetics — Welcome Page
 */
http_response_code(200);
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="th" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OpenGenetics — It's Running!</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Thai:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Inter', 'Noto Sans Thai', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] } } } }
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box }
    body { font-family: 'Inter', 'Noto Sans Thai', sans-serif; background: #0e0e1a; color: #e2e8f0; overflow-x: hidden; -webkit-font-smoothing: antialiased }

    .grad-text { background: linear-gradient(135deg, #818cf8 0%, #00cfff 55%, #a5b4fc 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text }

    .orb { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; will-change: transform }
    .orb-1 { width: 700px; height: 700px; background: radial-gradient(circle, rgba(30, 24, 207, .22) 0%, transparent 65%); top: -250px; left: -180px; filter: blur(80px) }
    .orb-2 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(0, 207, 255, .12) 0%, transparent 65%); top: 60px; right: -180px; filter: blur(90px) }
    .orb-3 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(108, 99, 255, .14) 0%, transparent 65%); bottom: 15%; left: 28%; filter: blur(100px) }

    .grid-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px); background-size: 64px 64px; mask-image: radial-gradient(ellipse 90% 65% at 50% 0%, #000 30%, transparent 100%); -webkit-mask-image: radial-gradient(ellipse 90% 65% at 50% 0%, #000 30%, transparent 100%) }

    @keyframes spin-circle { to { transform: rotate(360deg) } }
    @keyframes float { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-12px) } }
    @keyframes pulseDot { 0%, 100% { box-shadow: 0 0 6px #00cfff, 0 0 14px rgba(34,211,238,.4) } 50% { box-shadow: 0 0 12px #00cfff, 0 0 28px rgba(34,211,238,.6) } }

    .hero-circle { position: absolute; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: .06; animation: spin-circle linear infinite }
    .float-anim { animation: float 6s ease-in-out infinite }
    .dot-pulse { animation: pulseDot 2s ease-in-out infinite }

    [data-theme="light"] body { background: #f5f5fa; color: #1e293b }
    [data-theme="light"] .orb { opacity: 0 }
    [data-theme="light"] .grid-bg { opacity: 0 }
    [data-theme="light"] .hero-circle { opacity: .04 }
    [data-theme="light"] .grad-text { background: linear-gradient(135deg, #4f46e5 0%, #0891b2 55%, #6366f1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text }
    [data-theme="light"] h1 { color: #0f172a !important }
    [data-theme="light"] p { color: #475569 !important }
    [data-theme="light"] p strong { color: #1e293b !important }
    [data-theme="light"] .text-white { color: #0f172a !important }
    [data-theme="light"] .text-white\/40 { color: #64748b !important }
    [data-theme="light"] .text-white\/70 { color: #334155 !important }
    [data-theme="light"] .text-indigo-300 { color: #4f46e5 !important }
    [data-theme="light"] .text-indigo-400 { color: #4f46e5 !important }
    [data-theme="light"] .bg-indigo-500 { background: #4f46e5 !important }
    [data-theme="light"] .bg-indigo-500\/20 { background: rgba(79,70,229,.15) !important }
    [data-theme="light"] .bg-indigo-500\/10 { background: rgba(79,70,229,.08) !important }
    [data-theme="light"] .bg-white\/5 { background: rgba(0,0,0,.06) !important; border-color: rgba(0,0,0,.12) !important }
    [data-theme="light"] .bg-white\/5:hover { background: rgba(0,0,0,.1) !important; border-color: rgba(0,0,0,.2) !important }
    [data-theme="light"] .border-indigo-500\/15 { border-color: rgba(79,70,229,.12) !important }
    [data-theme="light"] .border-cyan-500\/10 { border-color: rgba(8,145,178,.1) !important }
    [data-theme="light"] .border-indigo-400\/\[0\.07\] { border-color: rgba(79,70,229,.08) !important }
    [data-theme="light"] .border-cyan-400\/\[0\.05\] { border-color: rgba(8,145,178,.06) !important }
    [data-theme="light"] .text-white\/20 { color: #94a3b8 !important }
    [data-theme="light"] .text-white\/40 { color: #64748b !important }
    [data-theme="light"] button { color: #475569 !important }
    [data-theme="light"] button:hover { color: #4f46e5 !important }
    [data-theme="light"] button svg { color: inherit !important }
  </style>
</head>
<body>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <div class="grid-bg"></div>

  <!-- Top bar: theme + lang -->
  <div class="fixed top-0 right-0 z-50 flex items-center gap-1 p-3">
    <button id="theme-toggle" class="flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/[0.06] transition-all" title="Toggle theme">
      <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
      </svg>
      <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="hidden">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
      </svg>
    </button>
    <button id="lang-toggle" class="flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/[0.06] transition-all" title="Switch language">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
      </svg>
    </button>
  </div>

  <!-- HERO -->
  <section class="relative z-10 min-h-screen flex items-center justify-center text-center px-6 pt-32 pb-24 overflow-hidden">
    <!-- Decorative spinning circles -->
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
      <div class="hero-circle w-[36rem] h-[36rem] border-l border-indigo-500/15 rotate-12" style="animation-duration:24s"></div>
      <div class="hero-circle w-[52rem] h-[52rem] border-t border-l border-cyan-500/10 rotate-45" style="animation-duration:30s"></div>
      <div class="hero-circle w-[68rem] h-[68rem] border-l border-indigo-400/[0.07]" style="animation-duration:24s;animation-direction:reverse"></div>
      <div class="hero-circle w-[84rem] h-[84rem] border-r border-cyan-400/[0.05] rotate-45" style="animation-duration:36s"></div>
    </div>

    <div class="relative z-20 max-w-[820px] mx-auto">
      <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full text-[13px] font-medium text-indigo-300 mb-9" style="background:rgba(108,99,255,.08);border:1px solid rgba(108,99,255,.2);backdrop-filter:blur(8px)">
        <span class="w-1.5 h-1.5 rounded-full bg-[#00cfff] dot-pulse"></span>
        <span data-i18n="badge">เฟรมเวิร์คพร้อมใช้งาน</span>
      </div>

      <img id="hero-logo" src="/open-genetics/public/images/logo/open-genetics-logo-white.svg" alt="OpenGenetics" class="mx-auto mb-8 block w-auto float-anim" style="height:clamp(56px,7vw,84px);filter:drop-shadow(0 0 28px rgba(0,207,255,.35)) drop-shadow(0 0 60px rgba(108,99,255,.2))">

      <h1 class="font-extrabold tracking-[-0.03em] text-white mb-5 leading-[1.1] px-2 sm:px-0" style="font-size:clamp(32px,8vw,72px)">
        PHP <span class="grad-text">Micro-Framework</span>
      </h1>

      <p class="text-white/40 max-w-[580px] w-full mx-auto px-4 sm:px-0 mb-10 leading-[1.8] text-[15px] sm:text-[16px] lg:text-[18px]" data-i18n-html="subtitle">
        เฟรมเวิร์ค PHP ที่มาพร้อม <strong class="text-white/70 font-medium">JWT Auth</strong>, <strong class="text-white/70 font-medium">RBAC</strong>, <strong class="text-white/70 font-medium">i18n</strong> และ <strong class="text-white/70 font-medium">Genetic SDK</strong> ให้คุณสร้าง REST API ที่ปลอดภัย เร็ว และขยายได้
      </p>

      <div class="flex flex-col sm:flex-row gap-3 items-center justify-center mb-12">
        <a href="https://open-genetics.vercel.app" class="text-white font-semibold text-base sm:text-lg bg-indigo-500 px-7 py-3 sm:py-2.5 rounded-full shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 hover:scale-105 transition-all w-full sm:w-auto text-center" data-i18n="btn.docs">
          เอกสาร
        </a>
        <a href="https://github.com/jkstudio99/open-genetics" class="text-white/70 font-medium text-base sm:text-lg bg-white/5 px-7 py-3 sm:py-2.5 rounded-full border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all w-full sm:w-auto text-center">
          GitHub
        </a>
      </div>

      <div class="flex items-center gap-1 bg-indigo-500/20 rounded-full pl-5 pr-1.5 py-1.5 max-w-full mx-auto" style="width:fit-content">
        <code class="text-indigo-300 font-mono font-medium tracking-tight text-[11px] sm:text-sm">composer create-project open-genetics/framework my-api</code>
        <button onclick="copyText('composer create-project open-genetics/framework my-api',this)" class="p-2 sm:p-2.5 rounded-full text-indigo-400/60 hover:text-indigo-400 hover:bg-indigo-500/30 transition-all" title="Copy">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
        </button>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="relative z-10 px-6 pb-24">
    <div class="max-w-[820px] mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="p-6 rounded-2xl transition-all" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(8px)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.15)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-indigo-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z"/>
          </svg>
        </div>
        <h3 class="text-white font-semibold text-base mb-2" data-i18n="f.cli.t">Genetics CLI</h3>
        <p class="text-white/40 text-sm leading-relaxed" data-i18n-html="f.cli.d">ใช้คำสั่ง <code class="text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded text-xs">php genetics help</code> เพื่อสร้าง controller, model และ middleware</p>
      </div>

      <div class="p-6 rounded-2xl transition-all" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(8px)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.15)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-indigo-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
          </svg>
        </div>
        <h3 class="text-white font-semibold text-base mb-2" data-i18n="f.route.t">File-based Routing</h3>
        <p class="text-white/40 text-sm leading-relaxed" data-i18n-html="f.route.d">วางไฟล์ใน <code class="text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded text-xs">api/</code> ระบบจะลงทะเบียน route ให้อัตโนมัติ</p>
      </div>

      <div class="p-6 rounded-2xl transition-all" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(8px)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.15)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-indigo-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
          </svg>
        </div>
        <h3 class="text-white font-semibold text-base mb-2" data-i18n="f.mw.t">Middleware Pipeline</h3>
        <p class="text-white/40 text-sm leading-relaxed" data-i18n="f.mw.d">เชื่อมต่อ middleware สำหรับ auth, CORS, rate-limit และอื่นๆ</p>
      </div>

      <div class="p-6 rounded-2xl transition-all" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(8px)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.15)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-indigo-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
          </svg>
        </div>
        <h3 class="text-white font-semibold text-base mb-2" data-i18n="f.db.t">Query Builder</h3>
        <p class="text-white/40 text-sm leading-relaxed" data-i18n="f.db.d">คิวรีฐานข้อมูลแบบ fluent พร้อม field selection และ relationship loading</p>
      </div>

      <div class="p-6 rounded-2xl transition-all" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(8px)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.15)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-indigo-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
          </svg>
        </div>
        <h3 class="text-white font-semibold text-base mb-2" data-i18n="f.sec.t">ระบบความปลอดภัย</h3>
        <p class="text-white/40 text-sm leading-relaxed" data-i18n="f.sec.d">ป้องกัน CSRF, กรองข้อมูล input และ JWT authentication พร้อมใช้งาน</p>
      </div>

      <div class="p-6 rounded-2xl transition-all" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);backdrop-filter:blur(8px)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.15)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-indigo-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <h3 class="text-white font-semibold text-base mb-2" data-i18n="f.zero.t">ไม่ต้องตั้งค่า</h3>
        <p class="text-white/40 text-sm leading-relaxed" data-i18n="f.zero.d">Convention over configuration เริ่มสร้างได้ทันทีด้วยค่าเริ่มต้นที่เหมาะสม</p>
      </div>
    </div>
  </section>

  <!-- Status -->
  <section class="relative z-10 px-6 pb-24">
    <div class="max-w-[820px] mx-auto flex flex-wrap gap-3 justify-center">
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-mono" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06)">
        <span class="text-indigo-400">PHP</span>
        <span class="text-white/40"><?= PHP_VERSION ?></span>
      </div>
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-mono" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06)">
        <span class="text-indigo-400">Framework</span>
        <span class="text-white/40">v2.0.2</span>
      </div>
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-mono" style="background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06)">
        <span class="text-indigo-400">Env</span>
        <span class="text-white/40"><?= isset($_SERVER['APP_ENV']) ? htmlspecialchars($_SERVER['APP_ENV']) : 'local' ?></span>
      </div>
    </div>
    <p class="text-center text-white/20 text-sm mt-12" data-i18n="footer">แอปพลิเคชันของคุณพร้อมแล้ว เริ่มสร้างสิ่งที่ยอดเยี่ยมกันเถอะ</p>
  </section>

<script>
// i18n
const i18n = {
  'badge': { en: 'Framework is running', th: 'เฟรมเวิร์คพร้อมใช้งาน' },
  'subtitle': {
    en: 'Enterprise-grade <strong class="text-white/70 font-medium">PHP Micro-Framework</strong> with built-in CLI, middleware pipeline, and automatic API routing.',
    th: 'เฟรมเวิร์ค PHP ที่มาพร้อม <strong class="text-white/70 font-medium">JWT Auth</strong>, <strong class="text-white/70 font-medium">RBAC</strong>, <strong class="text-white/70 font-medium">i18n</strong> และ <strong class="text-white/70 font-medium">Genetic SDK</strong> ให้คุณสร้าง REST API ที่ปลอดภัย เร็ว และขยายได้'
  },
  'btn.docs': { en: 'Documentation', th: 'เอกสาร' },
  'f.cli.t': { en: 'Genetics CLI', th: 'Genetics CLI' },
  'f.cli.d': { en: 'Run <code class="text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded text-xs">php genetics help</code> to scaffold controllers, models, and middleware.', th: 'ใช้คำสั่ง <code class="text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded text-xs">php genetics help</code> เพื่อสร้าง controller, model และ middleware' },
  'f.route.t': { en: 'Auto API Routing', th: 'File-based Routing' },
  'f.route.d': { en: 'Place files in <code class="text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded text-xs">api/</code> and routes are registered automatically.', th: 'วางไฟล์ใน <code class="text-indigo-300 bg-indigo-500/10 px-1.5 py-0.5 rounded text-xs">api/</code> ระบบจะลงทะเบียน route ให้อัตโนมัติ' },
  'f.mw.t': { en: 'Middleware Pipeline', th: 'Middleware Pipeline' },
  'f.mw.d': { en: 'Chain middleware for auth, CORS, rate-limiting, and more.', th: 'เชื่อมต่อ middleware สำหรับ auth, CORS, rate-limit และอื่นๆ' },
  'f.db.t': { en: 'Query Builder', th: 'Query Builder' },
  'f.db.d': { en: 'Fluent database queries with field selection and relationship loading.', th: 'คิวรีฐานข้อมูลแบบ fluent พร้อม field selection และ relationship loading' },
  'f.sec.t': { en: 'Built-in Security', th: 'ระบบความปลอดภัย' },
  'f.sec.d': { en: 'CSRF protection, input sanitization, and JWT authentication out of the box.', th: 'ป้องกัน CSRF, กรองข้อมูล input และ JWT authentication พร้อมใช้งาน' },
  'f.zero.t': { en: 'Zero Config', th: 'ไม่ต้องตั้งค่า' },
  'f.zero.d': { en: 'Convention over configuration. Start building immediately with sensible defaults.', th: 'Convention over configuration เริ่มสร้างได้ทันทีด้วยค่าเริ่มต้นที่เหมาะสม' },
  'footer': { en: 'Your application is ready. Start building something amazing.', th: 'แอปพลิเคชันของคุณพร้อมแล้ว เริ่มสร้างสิ่งที่ยอดเยี่ยมกันเถอะ' }
};

let currentLang = localStorage.getItem('og_locale') || 'th';

function applyLang(lang) {
  currentLang = lang;
  localStorage.setItem('og_locale', lang);
  document.documentElement.setAttribute('lang', lang);
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (i18n[key]) el.textContent = i18n[key][lang] || i18n[key]['en'] || '';
  });
  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const key = el.getAttribute('data-i18n-html');
    if (i18n[key]) el.innerHTML = i18n[key][lang] || i18n[key]['en'] || '';
  });
}
applyLang(currentLang);

document.getElementById('lang-toggle').addEventListener('click', () => {
  applyLang(currentLang === 'th' ? 'en' : 'th');
});

// Theme
const themeBtn = document.getElementById('theme-toggle');
const iconMoon = document.getElementById('theme-icon-moon');
const iconSun = document.getElementById('theme-icon-sun');
const heroLogo = document.getElementById('hero-logo');
let themeMode = localStorage.getItem('og_theme') || 'dark';

function applyTheme(mode) {
  themeMode = mode;
  localStorage.setItem('og_theme', mode);
  if (mode === 'dark') {
    document.documentElement.classList.add('dark');
    iconMoon.classList.remove('hidden');
    iconSun.classList.add('hidden');
    heroLogo.src = '/open-genetics/public/images/logo/open-genetics-logo-white.svg';
  } else {
    document.documentElement.classList.remove('dark');
    iconMoon.classList.add('hidden');
    iconSun.classList.remove('hidden');
    heroLogo.src = '/open-genetics/public/images/logo/open-genetics-logo.svg';
  }
  document.documentElement.setAttribute('data-theme', mode);
}
applyTheme(themeMode);

themeBtn.addEventListener('click', () => {
  applyTheme(themeMode === 'dark' ? 'light' : 'dark');
});

// Copy
function copyText(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
    btn.style.color = '#34d399';
    setTimeout(() => { btn.innerHTML = orig; btn.style.color = ''; }, 1500);
  });
}
</script>
</body>
</html>
