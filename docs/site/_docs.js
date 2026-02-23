// ══════════════════════════════════════════════
// Theme Toggle
// ══════════════════════════════════════════════
const themeBtn = document.getElementById('theme-btn');
const docMoon  = document.getElementById('doc-moon');
const docSun   = document.getElementById('doc-sun');
let theme = localStorage.getItem('og_theme') || 'dark';

function applyTheme(t) {
  const isDark = (t === 'dark');
  document.documentElement.classList.toggle('dark', isDark);
  document.documentElement.setAttribute('data-theme', t);
  docMoon.classList.toggle('hidden', !isDark);
  docSun.classList.toggle('hidden', isDark);
  document.body.style.background = isDark ? '#0e0e1a' : '#f5f5fa';
  document.body.style.color = isDark ? '#cbd5e1' : '#1e293b';
  document.querySelectorAll('.orb').forEach(el => el.style.opacity = isDark ? '1' : '0');
  document.getElementById('navbar').style.background = isDark ? 'rgba(14,14,26,.75)' : 'transparent';
  document.getElementById('navbar').style.borderBottomColor = isDark ? 'rgba(255,255,255,.055)' : 'rgba(0,0,0,.06)';
  document.getElementById('navbar').style.backdropFilter = isDark ? 'saturate(180%) blur(24px)' : 'saturate(180%) blur(20px)';
  const logoSrc = isDark ? 'images/logo/open-genetics-logo-white.svg' : 'images/logo/open-genetics-logo.svg';
  document.getElementById('nav-logo').src = logoSrc;
  const fl = document.getElementById('footer-logo');
  if (fl) fl.src = logoSrc;
  themeBtn.title = isDark ? 'Switch to Light mode' : 'Switch to Dark mode';
}
applyTheme(theme);
localStorage.setItem('og_theme', theme);
themeBtn.addEventListener('click', () => {
  theme = theme === 'dark' ? 'light' : 'dark';
  applyTheme(theme);
  localStorage.setItem('og_theme', theme);
});

// ══════════════════════════════════════════════
// Language Toggle
// ══════════════════════════════════════════════
let currentLang = localStorage.getItem('og_locale') || 'th';
const btnTh = document.getElementById('btn-th');
const btnEn = document.getElementById('btn-en');

function updateLangBtns() {
  btnTh.classList.toggle('active', currentLang === 'th');
  btnEn.classList.toggle('active', currentLang === 'en');
}
updateLangBtns();
if (window.OGi18n) window.OGi18n.setLocale(currentLang);

btnTh.addEventListener('click', () => { currentLang = 'th'; localStorage.setItem('og_locale', 'th'); updateLangBtns(); if (window.OGi18n) window.OGi18n.setLocale('th'); });
btnEn.addEventListener('click', () => { currentLang = 'en'; localStorage.setItem('og_locale', 'en'); updateLangBtns(); if (window.OGi18n) window.OGi18n.setLocale('en'); });

// ══════════════════════════════════════════════
// Sidebar Dropdown Toggle
// ══════════════════════════════════════════════
function toggleGroup(header) {
  header.closest('.sb-group').classList.toggle('open');
}

// ══════════════════════════════════════════════
// Mobile Sidebar
// ══════════════════════════════════════════════
const menuBtn = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
if (menuBtn) {
  menuBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
  overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
}
function checkMobile() { if (menuBtn) menuBtn.style.display = window.innerWidth <= 1024 ? 'flex' : 'none'; }
checkMobile();
window.addEventListener('resize', checkMobile);

// Close sidebar on link click (mobile)
document.querySelectorAll('.sb-link').forEach(l => l.addEventListener('click', () => {
  if (window.innerWidth <= 1024) { sidebar.classList.remove('open'); overlay.classList.remove('show'); }
}));

// ══════════════════════════════════════════════
// TOC Scroll Spy
// ══════════════════════════════════════════════
const tocLinks = document.querySelectorAll('.toc-link');
const headings = Array.from(document.querySelectorAll('h2[id], h3[id]'));
function updateTOC() {
  let cur = '';
  const y = window.scrollY + 80;
  headings.forEach(h => { if (h.offsetTop <= y) cur = h.getAttribute('id'); });
  tocLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + cur));
}
window.addEventListener('scroll', updateTOC, { passive: true });
updateTOC();

// ══════════════════════════════════════════════
// Copy to Clipboard
// ══════════════════════════════════════════════
function copyText(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    btn.classList.add('copied');
    const svg = btn.querySelector('svg');
    const orig = svg.innerHTML;
    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>';
    setTimeout(() => { btn.classList.remove('copied'); svg.innerHTML = orig; }, 1800);
  });
}

// ══════════════════════════════════════════════
// Smooth scroll for anchor links
// ══════════════════════════════════════════════
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

// ══════════════════════════════════════════════
// Search Modal
// ══════════════════════════════════════════════
const searchModal   = document.getElementById('search-modal');
const searchTrigger = document.getElementById('search-trigger');
const searchInput   = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
const searchEmpty   = document.getElementById('search-empty');
const searchFooter  = document.getElementById('search-footer');
let searchIdx = -1;

const searchData = [
  { title:'Overview',           desc:'OpenGenetics PHP Micro-Framework สำหรับ XAMPP',              href:'overview.html',           icon:'📖' },
  { title:'Installation',       desc:'composer create-project open-genetics/framework',             href:'installation.html',       icon:'📦' },
  { title:'Configuration',      desc:'.env DB_HOST DB_NAME JWT_SECRET APP_URL',                     href:'configuration.html',      icon:'⚙️' },
  { title:'Directory Structure',desc:'api/ config/ core/ lang/ public/ storage/',                  href:'directory-structure.html',icon:'📁' },
  { title:'Architecture',       desc:'File-based Routing Request Lifecycle Middleware',             href:'architecture.html',       icon:'🏗️' },
  { title:'Request Lifecycle',  desc:'Router dispatch Guard I18n AuditLog Response',               href:'request-lifecycle.html',  icon:'🔄' },
  { title:'API Reference',      desc:'Response::json error paginate JWT endpoints',                 href:'api-reference.html',      icon:'📋' },
  { title:'Database',           desc:'DB::query DB::fetch DB::insert PDO MySQL',                   href:'database.html',           icon:'💾' },
  { title:'Response',           desc:'Response helper json error paginate noContent',              href:'response.html',           icon:'📤' },
  { title:'Guard / RBAC',       desc:'Guard::requireRole Admin HR Employee authorization',         href:'guard.html',              icon:'🛡️' },
  { title:'i18n',               desc:'Internationalization Thai English X-Locale lang',            href:'i18n.html',               icon:'🌐' },
  { title:'Audit Trail',        desc:'AuditLogger log CREATE UPDATE DELETE LOGIN',                 href:'audit-trail.html',        icon:'📝' },
  { title:'Genetic SDK',        desc:'React Hook Vanilla JS useGenetics GeneticSDK',               href:'sdk.html',                icon:'🧬' },
  { title:'CLI Tool',           desc:'php add/genetics mutate serve make:api',                     href:'cli.html',                icon:'💻' },
  { title:'Deployment',         desc:'Production Apache XAMPP CORS APP_DEBUG',                     href:'deployment.html',         icon:'🚀' },
];

function openSearch() {
  searchModal.classList.add('open');
  requestAnimationFrame(() => { searchInput.focus(); searchInput.select(); });
  renderSearch('');
}
function closeSearch() {
  searchModal.classList.remove('open');
  searchInput.value = '';
  searchIdx = -1;
}
function renderSearch(q) {
  q = q.trim().toLowerCase();
  if (!q) { searchResults.innerHTML = ''; searchEmpty.style.display = ''; searchFooter.style.display = 'none'; searchIdx = -1; return; }
  const matches = searchData.filter(d => d.title.toLowerCase().includes(q) || d.desc.toLowerCase().includes(q));
  searchEmpty.style.display = 'none';
  searchFooter.style.display = 'flex';
  if (!matches.length) { searchResults.innerHTML = '<div class="search-no-result">No results for "<strong>' + q.replace(/</g,'&lt;') + '</strong>"</div>'; searchIdx = -1; return; }
  searchIdx = 0;
  searchResults.innerHTML = matches.map((m, i) => {
    const hl = s => s.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','gi'), '<mark>$1</mark>');
    return `<div class="search-item${i===0?' active':''}" data-href="${m.href}">
      <div class="si-icon">${m.icon}</div>
      <div style="flex:1;min-width:0"><div class="si-title">${hl(m.title)}</div><div class="si-desc">${hl(m.desc)}</div></div>
    </div>`;
  }).join('');
  searchResults.querySelectorAll('.search-item').forEach(el => {
    el.addEventListener('click', () => { window.location.href = el.dataset.href; closeSearch(); });
  });
}
searchInput.addEventListener('input', () => renderSearch(searchInput.value));
searchTrigger.addEventListener('click', openSearch);
searchModal.addEventListener('click', e => { if (e.target === searchModal) closeSearch(); });
document.addEventListener('keydown', e => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); openSearch(); return; }
  if (!searchModal.classList.contains('open')) return;
  if (e.key === 'Escape') { closeSearch(); return; }
  const items = searchResults.querySelectorAll('.search-item');
  if (!items.length) return;
  if (e.key === 'ArrowDown') { e.preventDefault(); searchIdx = Math.min(searchIdx + 1, items.length - 1); }
  if (e.key === 'ArrowUp')   { e.preventDefault(); searchIdx = Math.max(searchIdx - 1, 0); }
  if (e.key === 'Enter' && searchIdx >= 0) { e.preventDefault(); window.location.href = items[searchIdx].dataset.href; closeSearch(); return; }
  items.forEach((el, i) => el.classList.toggle('active', i === searchIdx));
  if (items[searchIdx]) items[searchIdx].scrollIntoView({ block: 'nearest' });
});
