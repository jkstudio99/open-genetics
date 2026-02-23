<?php
/**
 * 🧬 OpenGenetics — Default Welcome Page
 */
http_response_code(200);
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="th" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>OpenGenetics — It's Running!</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Thai:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

/* ── CSS Variables (Dark default) ── */
:root{
  --bg:#0e0e1a;--text:#e2e8f0;--text2:#94a3b8;--text3:rgba(255,255,255,.28);
  --card-bg:rgba(255,255,255,.025);--card-border:rgba(255,255,255,.06);
  --accent:#818cf8;--accent2:#22d3ee;--accent-bg:rgba(99,102,241,.08);--accent-border:rgba(99,102,241,.2);
  --btn-bg:#6366f1;--btn-shadow:rgba(99,102,241,.3);
  --pill-bg:rgba(99,102,241,.1);--pill-border:rgba(99,102,241,.2);
  --badge-color:#a5b4fc;--code-color:#a5b4fc;
  --orb-opacity:1;--grid-opacity:1;--circle-opacity:.06;
  --icon-bg:rgba(99,102,241,.1);--icon-border:rgba(99,102,241,.15);
  --grad-from:#818cf8;--grad-to:#22d3ee;--grad-end:#a5b4fc;
  --logo-filter:drop-shadow(0 0 28px rgba(0,207,255,.35)) drop-shadow(0 0 60px rgba(108,99,255,.2));
}
[data-theme="light"]{
  --bg:#f5f5fa;--text:#1e293b;--text2:#475569;--text3:#94a3b8;
  --card-bg:rgba(255,255,255,.8);--card-border:rgba(0,0,0,.08);
  --accent:#4f46e5;--accent2:#0891b2;--accent-bg:rgba(79,70,229,.06);--accent-border:rgba(79,70,229,.15);
  --btn-bg:#4f46e5;--btn-shadow:rgba(79,70,229,.2);
  --pill-bg:rgba(79,70,229,.08);--pill-border:rgba(79,70,229,.15);
  --badge-color:#4338ca;--code-color:#4f46e5;
  --orb-opacity:0;--grid-opacity:0;--circle-opacity:.04;
  --icon-bg:rgba(79,70,229,.08);--icon-border:rgba(79,70,229,.12);
  --grad-from:#4f46e5;--grad-to:#0891b2;--grad-end:#6366f1;
  --logo-filter:drop-shadow(0 0 20px rgba(79,70,229,.15));
}

body{font-family:'Inter','Noto Sans Thai',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased;transition:background .4s,color .4s}

/* ── Orbs ── */
.orb{position:fixed;border-radius:50%;pointer-events:none;z-index:0;opacity:var(--orb-opacity);transition:opacity .5s}
.orb-1{width:700px;height:700px;background:radial-gradient(circle,rgba(30,24,207,.22),transparent 65%);top:-250px;left:-180px;filter:blur(80px)}
.orb-2{width:600px;height:600px;background:radial-gradient(circle,rgba(0,207,255,.12),transparent 65%);top:60px;right:-180px;filter:blur(90px)}
.orb-3{width:500px;height:500px;background:radial-gradient(circle,rgba(108,99,255,.14),transparent 65%);bottom:15%;left:28%;filter:blur(100px)}

/* ── Grid BG ── */
.grid-bg{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:var(--grid-opacity);transition:opacity .5s;
  background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);
  background-size:64px 64px;
  mask-image:radial-gradient(ellipse 90% 65% at 50% 0%,#000 30%,transparent 100%);-webkit-mask-image:radial-gradient(ellipse 90% 65% at 50% 0%,#000 30%,transparent 100%)}

/* ── Animations ── */
@keyframes spin-circle{to{transform:rotate(360deg)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulseDot{0%,100%{box-shadow:0 0 6px var(--accent2),0 0 14px rgba(34,211,238,.4)}50%{box-shadow:0 0 12px var(--accent2),0 0 28px rgba(34,211,238,.6)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
.fu{animation:fadeUp .7s cubic-bezier(.16,1,.3,1) both}
.d1{animation-delay:.05s}.d2{animation-delay:.12s}.d3{animation-delay:.2s}.d4{animation-delay:.28s}
.d5{animation-delay:.36s}.d6{animation-delay:.44s}.d7{animation-delay:.52s}.d8{animation-delay:.6s}

/* ── Gradient text ── */
.grad{background:linear-gradient(135deg,var(--grad-from) 0%,var(--grad-to) 55%,var(--grad-end) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

/* ── Page layout ── */
.page{position:relative;z-index:10;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:80px 24px 60px;text-align:center}

/* ── Top bar (theme + lang) ── */
.topbar{position:fixed;top:0;right:0;z-index:50;display:flex;align-items:center;gap:4px;padding:12px 16px}
.topbar button{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;border:1px solid var(--card-border);background:var(--card-bg);color:var(--text2);cursor:pointer;transition:all .25s;backdrop-filter:blur(12px)}
.topbar button:hover{color:var(--accent);border-color:var(--accent-border);background:var(--accent-bg)}
.topbar button svg{width:16px;height:16px}
.topbar .lang-label{font-size:11px;font-weight:700;letter-spacing:.02em}

/* ── Hero circles (Elysia-style, NOT overlapping cards) ── */
.hero-circles{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;z-index:0}
.hero-circle{position:absolute;border-radius:50%;top:50%;left:50%;transform:translate(-50%,-50%);opacity:var(--circle-opacity);animation:spin-circle linear infinite}
.hc-1{width:36rem;height:36rem;border-left:1px solid var(--accent);animation-duration:24s}
.hc-2{width:52rem;height:52rem;border-top:1px solid var(--accent2);border-left:1px solid var(--accent2);animation-duration:30s}
.hc-3{width:68rem;height:68rem;border-left:1px solid var(--accent);animation-duration:24s;animation-direction:reverse}
.hc-4{width:84rem;height:84rem;border-right:1px solid var(--accent2);animation-duration:36s}

/* ── Badge ── */
.badge{position:relative;z-index:2;display:inline-flex;align-items:center;gap:8px;padding:6px 18px;border-radius:9999px;font-size:13px;font-weight:500;color:var(--badge-color);background:var(--accent-bg);border:1px solid var(--accent-border);backdrop-filter:blur(6px);margin-bottom:24px;transition:all .3s}
.badge .dot{width:7px;height:7px;border-radius:50%;background:var(--accent2);animation:pulseDot 2s ease-in-out infinite}

/* ── Logo ── */
.hero-logo{position:relative;z-index:2;height:clamp(52px,7vw,80px);width:auto;margin-bottom:24px;animation:float 6s ease-in-out infinite;filter:var(--logo-filter);transition:filter .4s}

/* ── Title ── */
.hero-title{position:relative;z-index:2;font-size:clamp(30px,6vw,64px);font-weight:800;letter-spacing:-.035em;line-height:1.1;color:var(--text);margin-bottom:14px}
.hero-sub{position:relative;z-index:2;font-size:clamp(14px,1.8vw,16px);color:var(--text2);max-width:520px;line-height:1.8;margin-bottom:32px}
.hero-sub strong{color:var(--text);font-weight:500}

/* ── Buttons ── */
.cta{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-bottom:18px}
.btn-p{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:9999px;background:var(--btn-bg);color:#fff;font-weight:600;font-size:15px;text-decoration:none;border:none;cursor:pointer;box-shadow:0 8px 24px var(--btn-shadow);transition:all .25s}
.btn-p:hover{box-shadow:0 12px 32px var(--btn-shadow);transform:translateY(-2px) scale(1.03)}
.btn-g{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:9999px;background:var(--card-bg);color:var(--text2);font-weight:500;font-size:15px;text-decoration:none;border:1px solid var(--card-border);transition:all .25s;backdrop-filter:blur(8px)}
.btn-g:hover{color:var(--text);border-color:var(--accent-border);transform:translateY(-2px)}
.btn-g svg,.btn-p svg{width:18px;height:18px}

/* ── Install pill ── */
.pill{position:relative;z-index:2;display:inline-flex;align-items:center;gap:10px;background:var(--pill-bg);border:1px solid var(--pill-border);border-radius:9999px;padding:8px 10px 8px 20px;margin-bottom:56px;transition:all .3s}
.pill code{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--code-color)}
.pill button{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;background:var(--accent-bg);border:1px solid var(--accent-border);color:var(--accent);cursor:pointer;transition:all .2s}
.pill button:hover{background:rgba(99,102,241,.25)}
.pill button svg{width:14px;height:14px}

/* ── Feature cards ── */
.features{position:relative;z-index:2;display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:820px;width:100%;margin-bottom:48px}
.fcard{padding:24px 20px;background:var(--card-bg);border:1px solid var(--card-border);border-radius:16px;text-align:left;transition:all .25s;text-decoration:none;color:inherit;backdrop-filter:blur(8px)}
.fcard:hover{background:var(--accent-bg);border-color:var(--accent-border);transform:translateY(-3px)}
.fcard .icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;background:var(--icon-bg);border:1px solid var(--icon-border);transition:all .3s}
.fcard .icon svg{width:20px;height:20px;color:var(--accent)}
.fcard h3{font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px}
.fcard p{font-size:13px;color:var(--text2);line-height:1.6}
.fcard code{font-family:'JetBrains Mono',monospace;font-size:12px;background:var(--accent-bg);padding:1px 5px;border-radius:4px;color:var(--code-color)}

/* ── Status bar ── */
.status{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:20px}
.sbadge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:9999px;font-size:12px;font-weight:500;font-family:'JetBrains Mono',monospace;background:var(--card-bg);border:1px solid var(--card-border);color:var(--text2);transition:all .3s}
.sbadge .lb{color:var(--accent)}

/* ── Footer ── */
.foot{position:relative;z-index:2;font-size:13px;color:var(--text3)}

/* ── Responsive ── */
@media(max-width:768px){.features{grid-template-columns:1fr}.hero-circles{display:none}}
@media(max-width:480px){.cta{flex-direction:column;align-items:center}.pill{flex-wrap:wrap;justify-content:center}}
</style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="grid-bg"></div>

<!-- Top bar: theme + lang -->
<div class="topbar">
  <button id="theme-toggle" title="Toggle theme">
    <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998Z"/></svg>
    <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0Z"/></svg>
    <svg id="icon-system" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25Z"/></svg>
  </button>
  <button id="lang-toggle" title="Switch language">
    <span class="lang-label" id="lang-label">EN</span>
  </button>
</div>

<div class="page">

  <!-- Hero circles (Elysia-style, positioned behind hero, not overlapping cards) -->
  <div class="hero-circles" aria-hidden="true">
    <div class="hero-circle hc-1"></div>
    <div class="hero-circle hc-2"></div>
    <div class="hero-circle hc-3"></div>
    <div class="hero-circle hc-4"></div>
  </div>

  <!-- Badge -->
  <div class="badge fu d1"><span class="dot"></span> <span data-i18n="badge">Framework is running</span></div>

  <!-- Logo -->
  <img id="hero-logo" class="hero-logo fu d2" src="../docs/site/images/logo/open-genetics-logo-white.svg" alt="OpenGenetics">

  <!-- Title -->
  <h1 class="hero-title fu d3">PHP <span class="grad" data-i18n="title">Micro-Framework</span></h1>
  <p class="hero-sub fu d4" data-i18n-html="subtitle">Enterprise-grade <strong>PHP Micro-Framework</strong> with built-in CLI, middleware pipeline, and automatic API routing.</p>

  <!-- CTA -->
  <div class="cta fu d5">
    <a href="https://open-genetics.vercel.app" target="_blank" class="btn-p">
      <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
      <span data-i18n="btn.docs">Documentation</span>
    </a>
    <a href="https://github.com/jkstudio99/open-genetics" target="_blank" class="btn-g">
      <svg fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844a9.59 9.59 0 012.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
      GitHub
    </a>
  </div>

  <!-- Install pill -->
  <div class="pill fu d5">
    <code>composer create-project open-genetics/framework my-api</code>
    <button id="copy-btn" title="Copy">
      <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
    </button>
  </div>

  <!-- Feature cards -->
  <div class="features">
    <div class="fcard fu d5">
      <div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z"/></svg></div>
      <h3 data-i18n="f.cli.t">Genetics CLI</h3>
      <p data-i18n-html="f.cli.d">Run <code>php genetics help</code> to scaffold controllers, models, and middleware.</p>
    </div>
    <div class="fcard fu d6">
      <div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></div>
      <h3 data-i18n="f.route.t">Auto API Routing</h3>
      <p data-i18n-html="f.route.d">Place files in <code>api/</code> and routes are registered automatically.</p>
    </div>
    <div class="fcard fu d7">
      <div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg></div>
      <h3 data-i18n="f.mw.t">Middleware Pipeline</h3>
      <p data-i18n="f.mw.d">Chain middleware for auth, CORS, rate-limiting, and more.</p>
    </div>
    <div class="fcard fu d6">
      <div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg></div>
      <h3 data-i18n="f.db.t">Query Builder</h3>
      <p data-i18n="f.db.d">Fluent database queries with field selection and relationship loading.</p>
    </div>
    <div class="fcard fu d7">
      <div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg></div>
      <h3 data-i18n="f.sec.t">Built-in Security</h3>
      <p data-i18n="f.sec.d">CSRF protection, input sanitization, and JWT authentication out of the box.</p>
    </div>
    <div class="fcard fu d8">
      <div class="icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
      <h3 data-i18n="f.zero.t">Zero Config</h3>
      <p data-i18n="f.zero.d">Convention over configuration. Start building immediately with sensible defaults.</p>
    </div>
  </div>

  <!-- Status bar -->
  <div class="status fu d8">
    <div class="sbadge"><span class="lb">PHP</span> <?= PHP_VERSION ?></div>
    <div class="sbadge"><span class="lb">Framework</span> v2.1.0</div>
    <div class="sbadge"><span class="lb">Env</span> <?= isset($_SERVER['APP_ENV']) ? htmlspecialchars($_SERVER['APP_ENV']) : 'local' ?></div>
  </div>

  <!-- Footer -->
  <p class="foot fu d8" data-i18n="footer">Your application is ready. Start building something amazing.</p>

</div>

<script>
// ══════════════════════════════════════════════
// i18n — TH/EN dictionary
// ══════════════════════════════════════════════
const i18n = {
  'badge':      { en: 'Framework is running', th: 'เฟรมเวิร์คพร้อมใช้งาน' },
  'title':      { en: 'Micro-Framework', th: 'ไมโครเฟรมเวิร์ค' },
  'subtitle':   {
    en: 'Enterprise-grade <strong>PHP Micro-Framework</strong> with built-in CLI, middleware pipeline, and automatic API routing.',
    th: 'เฟรมเวิร์ค PHP ที่มาพร้อม <strong>JWT Auth</strong>, <strong>RBAC</strong>, <strong>i18n</strong> และ <strong>Genetic SDK</strong> ให้คุณสร้าง REST API ที่ปลอดภัย เร็ว และขยายได้'
  },
  'btn.docs':   { en: 'Documentation', th: 'เอกสาร' },
  'f.cli.t':    { en: 'Genetics CLI', th: 'Genetics CLI' },
  'f.cli.d':    { en: 'Run <code>php genetics help</code> to scaffold controllers, models, and middleware.', th: 'ใช้คำสั่ง <code>php genetics help</code> เพื่อสร้าง controller, model และ middleware' },
  'f.route.t':  { en: 'Auto API Routing', th: 'File-based Routing' },
  'f.route.d':  { en: 'Place files in <code>api/</code> and routes are registered automatically.', th: 'วางไฟล์ใน <code>api/</code> ระบบจะลงทะเบียน route ให้อัตโนมัติ' },
  'f.mw.t':     { en: 'Middleware Pipeline', th: 'Middleware Pipeline' },
  'f.mw.d':     { en: 'Chain middleware for auth, CORS, rate-limiting, and more.', th: 'เชื่อมต่อ middleware สำหรับ auth, CORS, rate-limit และอื่นๆ' },
  'f.db.t':     { en: 'Query Builder', th: 'Query Builder' },
  'f.db.d':     { en: 'Fluent database queries with field selection and relationship loading.', th: 'คิวรีฐานข้อมูลแบบ fluent พร้อม field selection และ relationship loading' },
  'f.sec.t':    { en: 'Built-in Security', th: 'ระบบความปลอดภัย' },
  'f.sec.d':    { en: 'CSRF protection, input sanitization, and JWT authentication out of the box.', th: 'ป้องกัน CSRF, กรองข้อมูล input และ JWT authentication พร้อมใช้งาน' },
  'f.zero.t':   { en: 'Zero Config', th: 'ไม่ต้องตั้งค่า' },
  'f.zero.d':   { en: 'Convention over configuration. Start building immediately with sensible defaults.', th: 'Convention over configuration เริ่มสร้างได้ทันทีด้วยค่าเริ่มต้นที่เหมาะสม' },
  'footer':     { en: 'Your application is ready. Start building something amazing.', th: 'แอปพลิเคชันของคุณพร้อมแล้ว เริ่มสร้างสิ่งที่ยอดเยี่ยมกันเถอะ' },
};

let currentLang = localStorage.getItem('og_locale') || 'th';

function applyLang(lang) {
  currentLang = lang;
  localStorage.setItem('og_locale', lang);
  document.documentElement.setAttribute('lang', lang);
  document.getElementById('lang-label').textContent = lang === 'th' ? 'TH' : 'EN';
  document.getElementById('lang-toggle').title = lang === 'th' ? 'เปลี่ยนเป็น English' : 'Switch to ภาษาไทย';
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    const entry = i18n[key];
    if (!entry) return;
    const text = entry[lang] || entry['en'] || '';
    el.textContent = text;
  });
  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const key = el.getAttribute('data-i18n-html');
    const entry = i18n[key];
    if (!entry) return;
    el.innerHTML = entry[lang] || entry['en'] || '';
  });
}
applyLang(currentLang);

document.getElementById('lang-toggle').addEventListener('click', () => {
  applyLang(currentLang === 'th' ? 'en' : 'th');
});

// ══════════════════════════════════════════════
// Theme Toggle — dark / light / system (3-state)
// ══════════════════════════════════════════════
const THEME_KEY = 'og_theme';
const themeBtn = document.getElementById('theme-toggle');
const iconMoon = document.getElementById('icon-moon');
const iconSun = document.getElementById('icon-sun');
const iconSystem = document.getElementById('icon-system');
const heroLogo = document.getElementById('hero-logo');

// Cycle: dark → light → system → dark
let themeMode = localStorage.getItem(THEME_KEY) || 'dark';

function resolveTheme(mode) {
  if (mode === 'system') return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  return mode;
}

function applyTheme(mode) {
  themeMode = mode;
  localStorage.setItem(THEME_KEY, mode);
  const resolved = resolveTheme(mode);
  document.documentElement.setAttribute('data-theme', resolved);
  document.body.style.background = resolved === 'dark' ? '#0e0e1a' : '#f5f5fa';
  // Icons
  iconMoon.style.display = mode === 'dark' ? '' : 'none';
  iconSun.style.display = mode === 'light' ? '' : 'none';
  iconSystem.style.display = mode === 'system' ? '' : 'none';
  // Title
  themeBtn.title = mode === 'dark' ? 'Dark mode (click for Light)' : mode === 'light' ? 'Light mode (click for System)' : 'System mode (click for Dark)';
  // Logo swap
  const logoSrc = resolved === 'dark' ? '../docs/site/images/logo/open-genetics-logo-white.svg' : '../docs/site/images/logo/open-genetics-logo.svg';
  heroLogo.src = logoSrc;
}
applyTheme(themeMode);

// Listen for system preference changes when in system mode
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  if (themeMode === 'system') applyTheme('system');
});

themeBtn.addEventListener('click', () => {
  const next = themeMode === 'dark' ? 'light' : themeMode === 'light' ? 'system' : 'dark';
  applyTheme(next);
});

// ══════════════════════════════════════════════
// Copy to clipboard
// ══════════════════════════════════════════════
document.getElementById('copy-btn').addEventListener('click', function() {
  const btn = this;
  navigator.clipboard.writeText('composer create-project open-genetics/framework my-api').then(() => {
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
    btn.style.color = '#34d399';
    setTimeout(() => { btn.innerHTML = orig; btn.style.color = ''; }, 1500);
  });
});
</script>
</body>
</html>
