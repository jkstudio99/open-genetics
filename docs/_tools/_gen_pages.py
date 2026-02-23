#!/usr/bin/env python3
"""
Generate 15 individual documentation pages from docs/index.html content.
Run: python3 docs/_tools/_gen_pages.py
"""
import os, re

# Resolve paths relative to this script: _tools/ -> docs/ -> docs/site/
_TOOLS_DIR = os.path.dirname(os.path.abspath(__file__))
BASE = os.path.dirname(_TOOLS_DIR)  # docs/ directory
SITE_DIR = os.path.join(BASE, 'site')  # docs/site/ output directory

# (slug, thai_title, english_title, i18n_key)
PAGES = [
    ('overview',           'ภาพรวม',              'Overview',            'ds.overview'),
    ('installation',       'การติดตั้ง',            'Installation',        'ds.installation'),
    ('configuration',      'การตั้งค่า',            'Configuration',       'ds.configuration'),
    ('directory-structure', 'โครงสร้างโปรเจค',      'Directory Structure',  'ds.directory'),
    ('architecture',       'สถาปัตยกรรม',           'Architecture',        'ds.architecture'),
    ('request-lifecycle',  'Request Lifecycle',    'Request Lifecycle',   'ds.lifecycle'),
    ('api-reference',      'API Reference',        'API Reference',       'ds.api_ref'),
    ('database',           'ฐานข้อมูล',             'Database',            'ds.database'),
    ('response',           'Response',             'Response',            'ds.response'),
    ('guard',              'การกำหนดสิทธิ์',        'Authorization (Guards)', 'ds.authorization'),
    ('i18n',               'หลายภาษา (i18n)',       'i18n',                'ds.i18n'),
    ('audit-trail',        'Audit Trail',          'Audit Trail',         'ds.audit'),
    ('sdk',                'Genetic SDK',          'Genetic SDK',         'ds.sdk'),
    ('cli',                'CLI Tool',             'CLI Tool',            'ds.cli'),
    ('openapi',            'OpenAPI Generator',    'OpenAPI Generator',   'ds.openapi'),
    ('deployment',         'การ Deploy',            'Deployment',          'ds.deployment'),
]

GROUPS = {
    'overview': 'getting-started', 'installation': 'getting-started', 'configuration': 'getting-started',
    'directory-structure': 'structure', 'architecture': 'structure', 'request-lifecycle': 'structure',
    'api-reference': 'api-ref', 'database': 'api-ref', 'response': 'api-ref',
    'guard': 'api-ref', 'i18n': 'api-ref', 'audit-trail': 'api-ref',
    'sdk': 'tools', 'cli': 'tools', 'openapi': 'tools', 'deployment': 'tools',
}

# ── Heroicon SVG strings for search icons ──
SI = {
    'book':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>',
    'cube':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>',
    'cog':    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
    'folder': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>',
    'build':  '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/></svg>',
    'code':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>',
    'db':     '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>',
    'out':    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>',
    'shield': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>',
    'globe':  '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg>',
    'clip':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>',
    'dna':    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>',
    'term':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>',
    'rocket': '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/></svg>',
    'lock':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>',
    'home':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>',
    'git':    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.295 24 12c0-6.63-5.37-12-12-12"/></svg>',
    'bolt':   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>',
}

# ── Search data icon mapping (slug -> icon key) ──
SEARCH_ICONS = {
    'overview': 'book', 'installation': 'cube', 'configuration': 'cog',
    'directory-structure': 'folder', 'architecture': 'build',
    'request-lifecycle': 'bolt', 'api-reference': 'code',
    'database': 'db', 'response': 'out', 'guard': 'shield',
    'i18n': 'globe', 'audit-trail': 'clip', 'sdk': 'dna',
    'cli': 'term', 'openapi': 'code', 'deployment': 'rocket',
}

# ── Extract content sections from _source.html ──
def extract_sections():
    """Read docs/_source.html and extract each section's HTML content."""
    src = os.path.join(_TOOLS_DIR, '_source.html')
    if not os.path.exists(src):
        print("ERROR: _source.html not found. Copy the original index.html to _source.html first.")
        return {}
    with open(src, 'r', encoding='utf-8') as f:
        html = f.read()

    # Extract between <main> and </main>
    m = re.search(r'<main[^>]*>(.*?)</main>', html, re.DOTALL)
    if not m:
        print("ERROR: Could not find <main> tag in _source.html")
        return {}
    main_content = m.group(1)

    # Section IDs in order
    section_ids = [p[0] for p in PAGES]

    # Split content by section markers
    # overview is special — it's a <section id="overview">
    # others are <h2 id="xxx">
    sections = {}

    for i, sid in enumerate(section_ids):
        if sid == 'overview':
            # Find <section id="overview"> ... up to next <h2
            pat = re.compile(r'(<section\s+id="overview">.*?)(?=<h2\s+id=)', re.DOTALL)
            m2 = pat.search(main_content)
            if m2:
                sections[sid] = m2.group(1).strip()
        else:
            # Find <h2 id="xxx"> ... up to next <h2 id= or end of main or <hr> before footer
            if i < len(section_ids) - 1:
                next_sid = section_ids[i + 1]
                pat = re.compile(
                    rf'(<h2\s+id="{re.escape(sid)}".*?)(?=<h2\s+id="{re.escape(next_sid)}")',
                    re.DOTALL
                )
            else:
                # Last section — up to <hr> or footer
                pat = re.compile(
                    rf'(<h2\s+id="{re.escape(sid)}".*?)(?=<hr>|<div\s+style="margin-top:60px)',
                    re.DOTALL
                )
            m2 = pat.search(main_content)
            if m2:
                sections[sid] = m2.group(1).strip()

    return sections

# Group i18n keys
GROUP_I18N = {
    'getting-started': 'sb.getting_started',
    'structure': 'sb.structure',
    'api-ref': 'sb.api_ref',
    'tools': 'sb.tools',
}

# ── Build sidebar HTML ──
def build_sidebar(active_slug):
    def group_html(gid, icon_svg, icon_style, label, pages_in_group):
        i18n_key = GROUP_I18N.get(gid, '')
        i18n_attr = f' data-i18n="{i18n_key}"' if i18n_key else ''
        items = ''
        for slug, th, en, ds_key in pages_in_group:
            cls = ' active' if slug == active_slug else ''
            items += f'      <a href="{slug}.html" class="sb-link{cls}" data-i18n="{ds_key}">{th}</a>\n'
        return f'''  <div class="sb-group open" data-group="{gid}">
    <div class="sb-header" onclick="toggleGroup(this)">
      <div class="sb-icon" style="{icon_style}">{icon_svg}</div>
      <span class="sb-header-text"{i18n_attr}>{label}</span>
      <svg class="sb-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </div>
    <div class="sb-links">
{items}    </div>
  </div>'''

    gs = group_html('getting-started',
        '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>',
        'background:rgba(52,211,153,.12);color:#34d399;border:1px solid rgba(52,211,153,.2)',
        'เริ่มต้น',
        [p for p in PAGES if GROUPS[p[0]] == 'getting-started'])
    st = group_html('structure',
        '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>',
        'background:rgba(108,99,255,.12);color:#818cf8;border:1px solid rgba(108,99,255,.2)',
        'โครงสร้าง',
        [p for p in PAGES if GROUPS[p[0]] == 'structure'])
    ar = group_html('api-ref',
        '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>',
        'background:rgba(0,207,255,.1);color:#00cfff;border:1px solid rgba(0,207,255,.2)',
        'API Reference',
        [p for p in PAGES if GROUPS[p[0]] == 'api-ref'])
    tl = group_html('tools',
        '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/></svg>',
        'background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.2)',
        'เครื่องมือ',
        [p for p in PAGES if GROUPS[p[0]] == 'tools'])
    return gs + '\n' + st + '\n' + ar + '\n' + tl

# ── Build search data JS with Heroicon SVGs ──
def build_search_js():
    """Generate search data array with Heroicon SVGs instead of emojis."""
    entries = []
    for slug, th, en, _ds in PAGES:
        icon_key = SEARCH_ICONS.get(slug, 'book')
        # Escape single quotes in descriptions
        desc_th = th.replace("'", "\\'")
        entries.append(f"  {{ title:'{en}', desc:'{desc_th}', href:'{slug}.html', icon:'{icon_key}' }}")
    # Add extra entries
    entries.append(f"  {{ title:'JWT Authentication', desc:'HS256 Bcrypt firebase/php-jwt token auth', href:'api-reference.html', icon:'lock' }}")
    entries.append(f"  {{ title:'Landing Page', desc:'กลับไปหน้าหลัก OpenGenetics', href:'../../public/index.html', icon:'home' }}")
    entries.append(f"  {{ title:'GitHub Repository', desc:'open-genetics/framework — MIT License', href:'https://github.com/jkstudio99/open-genetics', icon:'git' }}")

    # Build the icon map JS
    icon_js = "const siSvg = {\n"
    for key, svg in SI.items():
        icon_js += f"  {key}: '{svg}',\n"
    icon_js += "};\n"

    data_js = "const searchData = [\n" + ",\n".join(entries) + "\n];"

    return icon_js + "\n" + data_js

# ── Build prev/next navigation ──
def build_nav(slug):
    idx = [p[0] for p in PAGES].index(slug)
    prev_html = ''
    next_html = ''
    if idx > 0:
        ps, pt, pe, pds = PAGES[idx - 1]
        prev_html = f'<a href="{ps}.html" class="nav-prev"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg><div><div class="nav-label" data-i18n="nav.prev">ก่อนหน้า</div><div class="nav-title" data-i18n="{pds}">{pt}</div></div></a>'
    if idx < len(PAGES) - 1:
        ns, nt, ne, nds = PAGES[idx + 1]
        next_html = f'<a href="{ns}.html" class="nav-next"><div><div class="nav-label" data-i18n="nav.next">ถัดไป</div><div class="nav-title" data-i18n="{nds}">{nt}</div></div><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg></a>'
    return f'<div class="page-nav">{prev_html}{next_html}</div>'

# ── Complete standalone CSS for individual pages ──
def get_page_css():
    return """
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Inter','Noto Sans Thai',sans-serif;background:#0e0e1a;color:#cbd5e1;-webkit-font-smoothing:antialiased;overflow-x:hidden;margin:0}
/* Orbs */
.orb{position:fixed;border-radius:50%;pointer-events:none;z-index:0;will-change:transform}
.orb-1{width:600px;height:600px;background:radial-gradient(circle,rgba(30,24,207,.2) 0%,transparent 65%);top:-200px;left:-150px;filter:blur(60px)}
.orb-2{width:500px;height:500px;background:radial-gradient(circle,rgba(0,207,255,.1) 0%,transparent 65%);top:40%;right:-150px;filter:blur(70px)}
/* Sidebar */
.sidebar{position:fixed;top:56px;left:0;bottom:0;width:260px;overflow-y:auto;z-index:40;background:rgba(6,6,15,.95);border-right:1px solid rgba(255,255,255,.055);padding:16px 12px 40px;scrollbar-width:thin;scrollbar-color:rgba(108,99,255,.35) rgba(255,255,255,.03)}
.sidebar::-webkit-scrollbar{width:6px}
.sidebar::-webkit-scrollbar-track{background:rgba(255,255,255,.03);border-radius:6px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(108,99,255,.35);border-radius:6px}
.sidebar::-webkit-scrollbar-thumb:hover{background:rgba(108,99,255,.55)}
[data-theme="light"] .sidebar::-webkit-scrollbar-track{background:rgba(0,0,0,.03)}
[data-theme="light"] .sidebar::-webkit-scrollbar-thumb{background:rgba(79,70,229,.2)}
[data-theme="light"] .sidebar::-webkit-scrollbar-thumb:hover{background:rgba(79,70,229,.35)}
.sb-group{margin-bottom:2px}
.sb-header{display:flex;align-items:center;gap:10px;padding:10px 12px;cursor:pointer;border-radius:10px;transition:all 150ms;user-select:none}
.sb-header:hover{background:rgba(255,255,255,.04)}
.sb-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px}
.sb-header-text{flex:1;font-size:14px;font-weight:600;color:rgba(255,255,255,.75);letter-spacing:-.01em}
.sb-chevron{width:16px;height:16px;color:rgba(255,255,255,.2);transition:transform 250ms ease;flex-shrink:0}
.sb-group.open .sb-chevron{transform:rotate(180deg)}
.sb-links{overflow:hidden;max-height:0;transition:max-height 300ms ease;padding-left:12px}
.sb-group.open .sb-links{max-height:600px}
.sb-link{display:block;padding:7px 12px 7px 28px;font-size:13px;color:rgba(255,255,255,.4);text-decoration:none;border-left:2px solid transparent;transition:all 120ms;font-weight:500;border-radius:0 6px 6px 0;margin:1px 0}
.sb-link:hover{color:rgba(255,255,255,.85);background:rgba(255,255,255,.04);border-left-color:rgba(108,99,255,.4)}
.sb-link.active{color:#a5b4fc;background:rgba(108,99,255,.12);border-left-color:#6c63ff;font-weight:600}
/* Content — centered between sidebar and TOC */
.doc-content{margin-left:260px;margin-right:220px;padding:96px 52px 100px;max-width:none;min-height:100vh;position:relative;z-index:10}
.doc-content>*{max-width:760px;margin-left:auto;margin-right:auto}
.doc-content>.page-nav{max-width:760px;margin-left:auto;margin-right:auto}
.doc-content section{max-width:760px;margin-left:auto;margin-right:auto;display:block}
.doc-content section>*{width:100%}
.doc-content .tip,.doc-content .warn,.doc-content .info{max-width:760px;width:100%;margin-left:auto;margin-right:auto}
/* Right TOC */
.toc{position:fixed;top:56px;right:0;width:220px;height:calc(100vh - 56px);overflow-y:auto;padding:28px 20px 40px 16px;z-index:30;scrollbar-width:none}
.toc::-webkit-scrollbar{display:none}
/* TOC progress bar */
.toc-progress-wrap{height:2px;background:rgba(255,255,255,.06);border-radius:2px;margin-bottom:16px;overflow:hidden}
.toc-progress-bar{height:100%;width:0%;background:linear-gradient(90deg,#6c63ff,#818cf8);border-radius:2px;transition:width 100ms ease}
[data-theme="light"] .toc-progress-wrap{background:rgba(0,0,0,.06)}
[data-theme="light"] .toc-progress-bar{background:linear-gradient(90deg,#4f46e5,#6366f1)}
.toc-title{font-size:12px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;padding-left:10px}
.toc-link{display:block;padding:4px 10px;font-size:12.5px;color:rgba(255,255,255,.3);text-decoration:none;border-left:2px solid transparent;transition:all 120ms;line-height:1.5;font-weight:500}
.toc-link:hover{color:rgba(255,255,255,.7)}
.toc-link.active{color:#a5b4fc;border-left-color:#6c63ff}
.toc-link.toc-h3{padding-left:20px;font-size:12px}
[data-theme="light"] .toc-title{color:#94a3b8}
[data-theme="light"] .toc-link{color:#94a3b8}
[data-theme="light"] .toc-link:hover{color:#334155}
[data-theme="light"] .toc-link.active{color:#4f46e5;border-left-color:#4f46e5}
/* Typography */
.doc-content h1{font-size:32px;font-weight:800;color:#f1f5f9;margin-bottom:8px;letter-spacing:-.02em;line-height:1.4;overflow:visible;margin-top:0}
.doc-content h1 em{font-style:normal;background:linear-gradient(90deg,#818cf8,#00cfff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;padding-bottom:4px;display:inline-block}
.doc-content .lead{font-size:16px;color:rgba(255,255,255,.45);margin-bottom:32px;line-height:1.75}
.doc-content h2{font-size:22px;font-weight:700;color:#f1f5f9;margin-top:48px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.07);scroll-margin-top:80px;letter-spacing:-.015em}
.doc-content h2:first-child{margin-top:0}
.doc-content h2 a{color:inherit;text-decoration:none}
.doc-content h2 a:hover{color:#a5b4fc}
.doc-content h3{font-size:16px;font-weight:600;color:#e2e8f0;margin-top:32px;margin-bottom:12px}
.doc-content h4{font-size:15px;font-weight:600;color:#cbd5e1;margin-top:24px;margin-bottom:6px}
.doc-content p{margin-bottom:16px;line-height:1.75;color:#94a3b8}
.doc-content ul,.doc-content ol{margin-bottom:16px;padding-left:22px;color:#94a3b8}
.doc-content li{margin-bottom:6px;line-height:1.7}
.doc-content strong{font-weight:600;color:#cbd5e1}
.doc-content hr{border:none;border-top:1px solid rgba(255,255,255,.07);margin:48px 0}
.doc-content code:not(pre code){background:rgba(108,99,255,.15);color:#a5b4fc;padding:2px 7px;border-radius:5px;font-family:'JetBrains Mono',monospace;font-size:13px;border:1px solid rgba(108,99,255,.2)}
pre{background:#080817;border-radius:12px;padding:22px 26px;overflow-x:auto;margin-bottom:20px;border:1px solid rgba(108,99,255,.18);box-shadow:0 0 30px rgba(108,99,255,.08)}
pre code{font-family:'JetBrains Mono',monospace;font-size:13px;line-height:1.9;color:#e2e8f0;background:none;padding:0;border:none}
pre::-webkit-scrollbar{height:4px}
pre::-webkit-scrollbar-thumb{background:rgba(108,99,255,.4);border-radius:4px}
/* Tables */
.doc-content table{width:100%;border-collapse:collapse;margin-bottom:20px;font-size:14px}
.doc-content th{text-align:left;padding:10px 14px;font-weight:600;font-size:12px;color:rgba(255,255,255,.35);border-bottom:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.025);letter-spacing:.04em;text-transform:uppercase}
.doc-content td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);color:#94a3b8}
.doc-content tr:hover td{background:rgba(108,99,255,.04)}
/* Footer classes */
.ft-desc{color:rgba(255,255,255,.35)}
.ft-label{font-size:12px;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.06em;font-weight:600}
.ft-value{font-size:18px;font-weight:700;color:rgba(255,255,255,.85)}
.ft-icon{color:#818cf8;flex-shrink:0}
.ft-github-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#e2e8f0;font-size:14px;font-weight:600;text-decoration:none;transition:all 200ms}
.ft-github-btn:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2)}
.ft-copy{color:rgba(255,255,255,.22)}
.ft-link{color:#818cf8}
/* Callouts — consistent layout */
.tip,.warn,.info{border-radius:10px;padding:14px 18px;margin:20px 0;font-size:14px;display:flex;align-items:flex-start;gap:12px;line-height:1.75;border-left-width:3px;border-left-style:solid;width:100%;box-sizing:border-box}
.tip{background:rgba(52,211,153,.07);border-left-color:#34d399}
.warn{background:rgba(251,191,36,.07);border-left-color:#fbbf24}
.info{background:rgba(108,99,255,.1);border-left-color:#6c63ff}
.tip>span,.warn>span,.info>span{display:flex;align-items:flex-start;flex-shrink:0;padding-top:1px;color:inherit}
.tip>span svg{color:#34d399}
.warn>span svg{color:#fbbf24}
.info>span svg{color:#818cf8}
.tip>div,.warn>div,.info>div{flex:1;min-width:0}
.tip p,.warn p,.info p{margin:0;color:#94a3b8;line-height:1.75}
.tip strong,.warn strong,.info strong{color:#cbd5e1}
[data-theme="light"] .tip{background:rgba(52,211,153,.08);border-left-color:#10b981}
[data-theme="light"] .warn{background:rgba(251,191,36,.08);border-left-color:#d97706}
[data-theme="light"] .info{background:rgba(79,70,229,.07);border-left-color:#4f46e5}
[data-theme="light"] .tip>span svg{color:#10b981}
[data-theme="light"] .warn>span svg{color:#d97706}
[data-theme="light"] .info>span svg{color:#4f46e5}
[data-theme="light"] .tip p,[data-theme="light"] .warn p,[data-theme="light"] .info p{color:#475569}
[data-theme="light"] .tip strong,[data-theme="light"] .warn strong,[data-theme="light"] .info strong{color:#1e293b}
/* Cards */
.card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.card{background:rgba(255,255,255,.028);border:1px solid rgba(255,255,255,.065);border-radius:12px;padding:18px;transition:all 200ms}
.card:hover{border-color:rgba(108,99,255,.38);background:rgba(108,99,255,.055);transform:translateY(-2px)}
.card .ic{width:36px;height:36px;border-radius:9px;background:rgba(108,99,255,.15);color:#818cf8;border:1px solid rgba(108,99,255,.22);display:flex;align-items:center;justify-content:center;margin-bottom:10px}
.card h4{font-size:14px;font-weight:700;margin-bottom:4px;color:#f1f5f9}
.card p{font-size:12px;color:rgba(255,255,255,.38);margin:0;line-height:1.5}
/* Syntax */
.kw{color:#c084fc}.fn{color:#60a5fa}.st{color:#fbbf24}.cm{color:#475569}.vr{color:#34d399}.cl{color:#f472b6}.nb{color:#38bdf8}
/* Copy button */
.copy-btn{position:absolute;top:10px;right:10px;padding:6px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.4);cursor:pointer;transition:all 150ms;opacity:0;line-height:0}
.copy-wrap:hover .copy-btn{opacity:1}
.copy-btn:hover{background:rgba(108,99,255,.2);color:#a5b4fc;border-color:rgba(108,99,255,.35)}
.copy-btn.copied{color:#34d399!important;border-color:rgba(52,211,153,.35)!important;opacity:1}
/* Footer card */
.footer-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:20px;padding:36px 40px;display:flex;align-items:center;gap:40px;flex-wrap:wrap}
/* Search trigger animation */
@keyframes search-glow{0%,100%{box-shadow:0 0 0 rgba(108,99,255,0),0 0 0 rgba(0,207,255,0)}50%{box-shadow:0 0 12px rgba(108,99,255,.15),0 0 24px rgba(0,207,255,.08)}}
#search-trigger{animation:search-glow 4s ease-in-out infinite}
#search-trigger:hover{box-shadow:0 0 16px rgba(108,99,255,.25),0 0 32px rgba(0,207,255,.12)!important;border-color:rgba(108,99,255,.3)!important;background:rgba(108,99,255,.08)!important;transform:scale(1.02)}
[data-theme="light"] #search-trigger{animation:search-glow-light 4s ease-in-out infinite}
@keyframes search-glow-light{0%,100%{box-shadow:0 0 0 rgba(79,70,229,0)}50%{box-shadow:0 0 10px rgba(79,70,229,.1),0 0 20px rgba(79,70,229,.05)}}
/* Search modal */
.search-overlay{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
.search-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding-top:18vh}
.search-box{width:min(560px,90vw);background:#0f0f1e;border:1px solid rgba(108,99,255,.25);border-radius:16px;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.6),0 0 40px rgba(108,99,255,.12)}
.search-input{width:100%;padding:16px 20px;background:transparent;border:none;outline:none;color:#e2e8f0;font-size:16px;font-family:inherit}
.search-input::placeholder{color:rgba(255,255,255,.28)}
.search-hint{padding:12px 20px;color:rgba(255,255,255,.22);font-size:13px}
.search-item{display:flex;align-items:center;gap:12px;padding:10px 16px;cursor:pointer;transition:background 120ms;border-bottom:1px solid rgba(255,255,255,.03)}
.search-item:hover,.search-item.active{background:rgba(108,99,255,.1)}
.search-item .si-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:rgba(108,99,255,.12);color:#818cf8;flex-shrink:0}
.search-item .si-title{font-size:14px;font-weight:600;color:#e2e8f0}
.search-item .si-desc{font-size:12px;color:rgba(255,255,255,.3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:380px}
.search-item mark{background:rgba(108,99,255,.3);color:#a5b4fc;border-radius:2px;padding:0 2px}
.search-no-result{padding:24px 20px;text-align:center;color:rgba(255,255,255,.2);font-size:14px}
/* Overlay */
.overlay{display:none;position:fixed;inset:0;z-index:39;background:rgba(0,0,0,.6)}
.overlay.show{display:block}
/* Page navigation (prev/next) */
.page-nav{display:flex;gap:12px;margin-top:52px;padding-top:24px;border-top:1px solid rgba(255,255,255,.07)}
.page-nav a{flex:1;display:flex;align-items:center;gap:12px;padding:16px 20px;border-radius:12px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);text-decoration:none;transition:all 200ms}
.page-nav a:hover{border-color:rgba(108,99,255,.3);background:rgba(108,99,255,.05)}
.page-nav a svg{color:rgba(255,255,255,.3);flex-shrink:0}
.nav-next{justify-content:flex-end;text-align:right}
.nav-label{font-size:11px;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:2px}
.nav-title{font-size:14px;font-weight:600;color:#a5b4fc}
/* Responsive */
@media(max-width:1440px){
  .doc-content{padding:96px 40px 100px}
}
@media(max-width:1280px){
  .toc{display:none}
  .doc-content{margin-right:0;padding:96px 48px 100px}
}
@media(max-width:1024px){
  .sidebar{transform:translateX(-100%);transition:transform 250ms;z-index:50;box-shadow:0 4px 40px rgba(0,0,0,.5)}
  .sidebar.open{transform:translateX(0)}
  .doc-content{margin-left:0;padding:80px 24px 80px}
  .doc-content>*{max-width:720px}
  .menu-btn{display:flex!important}
}
@media(max-width:640px){
  .doc-content{padding:76px 16px 60px}
  .doc-content>*{max-width:100%}
  .card-grid{grid-template-columns:1fr}
  .page-nav{flex-direction:column}
  .footer-card{flex-direction:column;padding:24px 20px;gap:20px}
}

/* ═══ LIGHT MODE ═══ */
:root{--c-bg:#0e0e1a;--c-text:#e2e8f0;--c-text2:#94a3b8;--c-text3:rgba(255,255,255,.28);--c-border:rgba(255,255,255,.06);--c-code-bg:#080817;--c-code-text:#e2e8f0;--c-card-bg:rgba(255,255,255,.028);--c-card-border:rgba(255,255,255,.065);--c-search-bg:#0f0f1e;--c-sidebar-bg:rgba(6,6,15,.95);--c-kw:#c084fc;--c-fn:#60a5fa;--c-st:#fbbf24;--c-cm:#475569;--c-vr:#34d399;--c-cl:#f472b6;--c-nb:#38bdf8}
[data-theme="light"]{--c-bg:#f5f5fa;--c-text:#1e293b;--c-text2:#475569;--c-text3:#94a3b8;--c-border:rgba(0,0,0,.08);--c-code-bg:#f8fafc;--c-code-text:#334155;--c-card-bg:rgba(255,255,255,.8);--c-card-border:rgba(0,0,0,.08);--c-search-bg:#ffffff;--c-sidebar-bg:rgba(248,250,252,.97);--c-kw:#7c3aed;--c-fn:#2563eb;--c-st:#d97706;--c-cm:#94a3b8;--c-vr:#059669;--c-cl:#db2777;--c-nb:#0284c7}
/* Navbar */
[data-theme="light"] #navbar{background:transparent!important;border-bottom-color:rgba(0,0,0,.06)!important;box-shadow:none!important}
[data-theme="light"] #navbar svg{color:#475569!important}
[data-theme="light"] #navbar a{color:#334155!important}
[data-theme="light"] #navbar a:hover{color:#1e293b!important}
[data-theme="light"] #navbar button{color:#475569!important}
[data-theme="light"] #navbar button:hover{color:#1e293b!important}
[data-theme="light"] #search-trigger{background:rgba(0,0,0,.04)!important;border-color:rgba(0,0,0,.12)!important;color:#64748b!important}
[data-theme="light"] #search-trigger kbd{background:rgba(0,0,0,.06)!important;border-color:rgba(0,0,0,.1)!important;color:#94a3b8!important}
/* Sidebar */
[data-theme="light"] .sidebar{background:var(--c-sidebar-bg)!important;border-right-color:var(--c-border)!important}
[data-theme="light"] .sb-header-text{color:#334155!important}
[data-theme="light"] .sb-header:hover{background:rgba(0,0,0,.03)!important}
[data-theme="light"] .sb-chevron{color:#94a3b8!important}
[data-theme="light"] .sb-link{color:#475569!important}
[data-theme="light"] .sb-link:hover{color:#1e293b!important;background:rgba(79,70,229,.04)!important}
[data-theme="light"] .sb-link.active{color:#4f46e5!important;background:rgba(79,70,229,.08)!important;border-left-color:#4f46e5!important}
/* Content */
[data-theme="light"] .doc-content h1,[data-theme="light"] .doc-content h2{color:#1e293b!important;border-bottom-color:var(--c-border)!important}
[data-theme="light"] .doc-content h3,[data-theme="light"] .doc-content h4{color:#334155!important}
[data-theme="light"] .doc-content h1 em{background:linear-gradient(90deg,#4f46e5,#0891b2)!important;-webkit-background-clip:text!important;-webkit-text-fill-color:transparent!important;background-clip:text!important}
[data-theme="light"] .doc-content .lead{color:#64748b!important}
[data-theme="light"] .doc-content p,[data-theme="light"] .doc-content ul,[data-theme="light"] .doc-content ol,[data-theme="light"] .doc-content li{color:#475569!important}
[data-theme="light"] .doc-content strong{color:#1e293b!important}
[data-theme="light"] .doc-content hr{border-top-color:var(--c-border)!important}
[data-theme="light"] .doc-content code:not(pre code){background:rgba(79,70,229,.08)!important;color:#4f46e5!important;border-color:rgba(79,70,229,.15)!important}
/* Code blocks */
[data-theme="light"] pre{background:var(--c-code-bg)!important;border-color:rgba(0,0,0,.1)!important;box-shadow:none!important}
[data-theme="light"] pre code{color:var(--c-code-text)!important}
[data-theme="light"] .kw{color:var(--c-kw)!important}[data-theme="light"] .fn{color:var(--c-fn)!important}[data-theme="light"] .st{color:var(--c-st)!important}[data-theme="light"] .cm{color:var(--c-cm)!important}[data-theme="light"] .vr{color:var(--c-vr)!important}[data-theme="light"] .cl{color:var(--c-cl)!important}[data-theme="light"] .nb{color:var(--c-nb)!important}
/* Cards */
[data-theme="light"] .card{background:var(--c-card-bg)!important;border-color:var(--c-card-border)!important;box-shadow:0 1px 3px rgba(0,0,0,.04)!important}
[data-theme="light"] .card:hover{background:rgba(79,70,229,.04)!important;border-color:rgba(79,70,229,.2)!important}
[data-theme="light"] .card h4{color:#1e293b!important}
[data-theme="light"] .card p{color:#64748b!important}
[data-theme="light"] .card .ic{background:rgba(79,70,229,.08)!important;border-color:rgba(79,70,229,.15)!important}
/* Tables */
[data-theme="light"] .doc-content th{background:rgba(0,0,0,.03)!important;color:#475569!important;border-bottom-color:rgba(0,0,0,.1)!important}
[data-theme="light"] .doc-content td{color:#475569!important;border-bottom-color:rgba(0,0,0,.06)!important}
[data-theme="light"] .doc-content tr:hover td{background:rgba(79,70,229,.03)!important}
/* Callouts */
[data-theme="light"] .tip{background:rgba(52,211,153,.08)!important;border-left-color:#10b981!important}
[data-theme="light"] .warn{background:rgba(251,191,36,.08)!important;border-left-color:#d97706!important}
[data-theme="light"] .info{background:rgba(79,70,229,.07)!important;border-left-color:#4f46e5!important}
[data-theme="light"] .tip>span svg{color:#10b981!important}
[data-theme="light"] .warn>span svg{color:#d97706!important}
[data-theme="light"] .info>span svg{color:#4f46e5!important}
[data-theme="light"] .tip p,[data-theme="light"] .warn p,[data-theme="light"] .info p{color:#475569!important}
[data-theme="light"] .tip strong,[data-theme="light"] .warn strong,[data-theme="light"] .info strong{color:#1e293b!important}
/* Search */
[data-theme="light"] .search-overlay{background:rgba(0,0,0,.3)!important}
[data-theme="light"] .search-box{background:var(--c-search-bg)!important;border-color:rgba(79,70,229,.18)!important;box-shadow:0 24px 60px rgba(0,0,0,.12)!important}
[data-theme="light"] .search-input{color:var(--c-text)!important}
[data-theme="light"] .search-input::placeholder{color:#94a3b8!important}
[data-theme="light"] .search-item .si-title{color:var(--c-text)!important}
[data-theme="light"] .search-item .si-desc{color:var(--c-text2)!important}
[data-theme="light"] .search-item:hover,[data-theme="light"] .search-item.active{background:rgba(79,70,229,.06)!important}
[data-theme="light"] .search-hint{color:#94a3b8!important}
/* Copy */
[data-theme="light"] .copy-btn{background:rgba(0,0,0,.04)!important;border-color:rgba(0,0,0,.1)!important;color:#64748b!important}
[data-theme="light"] .copy-btn:hover{background:rgba(79,70,229,.1)!important;color:#4f46e5!important}
/* Footer card */
[data-theme="light"] .footer-card{background:rgba(0,0,0,.03)!important;border-color:rgba(0,0,0,.08)!important}
[data-theme="light"] .ft-desc{color:#475569!important}
[data-theme="light"] .ft-label{color:#64748b!important}
[data-theme="light"] .ft-value{color:#1e293b!important}
[data-theme="light"] .ft-github-btn{background:rgba(15,23,42,.03)!important;border-color:rgba(15,23,42,.1)!important;color:#334155!important}
[data-theme="light"] .ft-github-btn:hover{background:rgba(15,23,42,.06)!important;border-color:rgba(15,23,42,.15)!important;color:#0f172a!important}
[data-theme="light"] .ft-icon{color:#4f46e5!important}
[data-theme="light"] .ft-copy{color:#64748b!important}
[data-theme="light"] .ft-link{color:#4f46e5!important}
/* Page nav light */
[data-theme="light"] .page-nav{border-top-color:rgba(0,0,0,.08)}
[data-theme="light"] .page-nav a{border-color:rgba(0,0,0,.08);background:rgba(255,255,255,.5)}
[data-theme="light"] .page-nav a:hover{border-color:rgba(79,70,229,.25);background:rgba(79,70,229,.04)}
[data-theme="light"] .page-nav a svg{color:#94a3b8}
[data-theme="light"] .nav-label{color:#94a3b8}
[data-theme="light"] .nav-title{color:#4f46e5}
/* BG overrides */
[data-theme="light"] [class*="bg-white/"]{background:transparent!important}
[data-theme="light"] [class*="text-white"]{color:var(--c-text)!important}
[data-theme="light"] [class*="text-white/4"],[data-theme="light"] [class*="text-white/3"],[data-theme="light"] [class*="text-white/5"],[data-theme="light"] [class*="text-white/6"]{color:var(--c-text2)!important}
[data-theme="light"] [class*="text-white/2"],[data-theme="light"] [class*="text-white/1"]{color:var(--c-text3)!important}
"""

# ── Generate a single page ──
def generate_page(slug, th_title, en_title, content, css):
    sidebar_html = build_sidebar(slug)
    search_js = build_search_js()
    nav_html = build_nav(slug)

    return f'''<!DOCTYPE html>
<html lang="th" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{en_title} — OpenGenetics Docs</title>
<meta name="description" content="{th_title} — OpenGenetics Framework Documentation">
<link rel="icon" type="image/svg+xml" href="images/favicon/favicon.svg">
<link rel="manifest" href="images/favicon/site.webmanifest">
<meta name="theme-color" content="#06060f">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Thai:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={{darkMode:'class',theme:{{extend:{{fontFamily:{{sans:['Inter','Noto Sans Thai','sans-serif'],mono:['JetBrains Mono','monospace']}},transitionTimingFunction:{{'out-expo':'cubic-bezier(0.16,1,0.3,1)'}},transitionDuration:{{'600':'600ms','800':'800ms'}}}}}}}}</script>
<style>
{css}
</style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<!-- TOPBAR -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 h-14 flex items-center" style="background:rgba(14,14,26,.75);backdrop-filter:saturate(180%) blur(24px);-webkit-backdrop-filter:saturate(180%) blur(24px);border-bottom:1px solid rgba(255,255,255,.055)">
  <div class="relative max-w-none w-full h-full flex items-center px-5">
    <div class="flex items-center gap-3 shrink-0">
      <button id="menu-btn" class="menu-btn hidden items-center justify-center w-8 h-8 rounded-lg text-white/60 hover:text-white hover:bg-white/[0.07] transition-all" style="display:none" aria-label="Menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
      </button>
      <a href="../../public/index.html" class="flex items-center shrink-0">
        <img id="nav-logo" src="images/logo/open-genetics-logo-white.svg" alt="OpenGenetics" class="h-[24px] w-auto">
      </a>
    </div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
      <button id="search-trigger" class="hidden sm:flex items-center gap-2.5 px-4 py-[7px] rounded-full text-[13px] text-white/28 hover:text-white/45 hover:border-white/15 transition-all ease-out-expo duration-300 cursor-text" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);min-width:220px">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="shrink-0 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        Search
        <kbd class="ml-auto text-[11px] font-mono px-1.5 py-0.5 rounded" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.3);line-height:1.2">⌘K</kbd>
      </button>
    </div>
    <div class="flex items-center gap-0.5 shrink-0 ml-auto">
      <a href="overview.html" class="hidden md:inline-flex text-[13px] font-medium px-3 py-1.5 rounded-lg text-white bg-white/[0.06] transition-all" data-i18n="nav.docs">เอกสาร</a>
      <a href="blog.html" class="hidden md:inline-flex text-[13px] font-medium px-3 py-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/[0.06] transition-all" data-i18n="nav.blog">บล็อก</a>
      <a href="sdk.html" class="hidden md:inline-flex text-[13px] font-medium px-3 py-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/[0.06] transition-all">SDK</a>
      <div class="w-px h-4 mx-1.5 hidden md:block" style="background:rgba(255,255,255,.1)"></div>
      <button id="theme-btn" class="flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/[0.06] transition-all" title="Toggle theme">
        <svg id="doc-moon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
        <svg id="doc-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
      </button>
      <button id="lang-toggle" class="flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/[0.06] transition-all" title="Switch language">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg>
      </button>
      <a href="https://github.com/jkstudio99/open-genetics" target="_blank" class="flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/[0.06] transition-all" title="GitHub">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
      </a>
      <a href="https://x.com" target="_blank" class="flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/[0.06] transition-all" title="X / Twitter">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </a>
    </div>
  </div>
</nav>

<!-- SEARCH MODAL -->
<div id="search-modal" class="search-overlay">
  <div class="search-box">
    <div class="flex items-center px-4" style="border-bottom:1px solid rgba(255,255,255,.06)">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-white/30 shrink-0 mr-3"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
      <input id="search-input" type="text" class="search-input" placeholder="Search documentation..." autofocus>
      <kbd class="text-[11px] font-mono px-1.5 py-0.5 rounded shrink-0 cursor-pointer" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.3)" onclick="closeSearch()">ESC</kbd>
    </div>
    <div id="search-results" class="max-h-[320px] overflow-y-auto" style="scrollbar-width:thin;scrollbar-color:rgba(108,99,255,.3) transparent"></div>
    <div id="search-empty" class="search-hint"><span class="text-white/15">Type to search across docs, API reference and guides...</span></div>
    <div id="search-footer" class="hidden px-4 py-2.5 text-[11px] text-white/20 flex items-center gap-3" style="border-top:1px solid rgba(255,255,255,.05)">
      <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded text-[10px]" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08)">↑↓</kbd> navigate</span>
      <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded text-[10px]" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08)">↵</kbd> select</span>
      <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded text-[10px]" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08)">esc</kbd> close</span>
    </div>
  </div>
</div>

<div class="overlay" id="overlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
{sidebar_html}
</aside>

<!-- MAIN CONTENT -->
<main class="doc-content" id="doc-main">

{content}

{nav_html}

<!-- FOOTER -->
<div style="margin-top:80px;padding:0 0 32px;max-width:820px;margin-left:auto;margin-right:auto">
  <div class="footer-card" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:24px;padding:44px 48px;display:flex;align-items:center;gap:40px;flex-wrap:wrap;justify-content:space-between">
    <div style="flex:1;min-width:260px">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:14px">
        <img src="images/logo/open-genetics-logo-white.svg" alt="OpenGenetics" style="height:60px;width:auto;opacity:.9" id="footer-logo">
      </div>
      <p class="ft-desc" style="font-size:15px;margin:0 0 24px;line-height:1.6;font-style:italic">Enterprise PHP Micro-Framework</p>
      <a href="https://github.com/jkstudio99/open-genetics" target="_blank" class="ft-github-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
        GitHub
      </a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px 40px;flex-shrink:0">
      <div style="display:flex;align-items:center;gap:12px">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="ft-icon"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
        <div><div class="ft-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Speed</div><div class="ft-value" style="font-weight:600;font-size:16px">&lt; 50ms</div></div>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="ft-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
        <div><div class="ft-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Security</div><div class="ft-value" style="font-weight:600;font-size:16px">OWASP</div></div>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="ft-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
        <div><div class="ft-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Experience</div><div class="ft-value" style="font-weight:600;font-size:16px">Exceptional</div></div>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="ft-icon"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
        <div><div class="ft-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Frontend SDK</div><div class="ft-value" style="font-weight:600;font-size:16px">Dual SDK</div></div>
      </div>
    </div>
  </div>
  <p class="ft-copy" style="text-align:center;font-size:14px;margin-top:28px">Built with <span style="color:#f472b6">&#10084;</span> for PHP developers by <a href="https://github.com/jkstudio99" target="_blank" class="ft-link" style="text-decoration:none;font-weight:500">jkstudio99</a></p>
</div>

</main>

<!-- RIGHT TOC -->
<div class="toc" id="page-toc">
  <div class="toc-progress-wrap"><div class="toc-progress-bar" id="toc-progress"></div></div>
  <div class="toc-title" data-i18n="toc.title">สารบัญ</div>
  <div id="toc-links"></div>
</div>

<script src="scripts/i18n-page.js"></script>
<script>
// Theme Toggle
const themeBtn = document.getElementById('theme-btn');
const docMoon = document.getElementById('doc-moon');
const docSun = document.getElementById('doc-sun');
let theme = localStorage.getItem('og_theme') || 'dark';

function applyTheme(t) {{
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
}}

function toggleGroup(header) {{ header.closest('.sb-group').classList.toggle('open'); }}
applyTheme(theme);
localStorage.setItem('og_theme', theme);
themeBtn.addEventListener('click', () => {{
  theme = theme === 'dark' ? 'light' : 'dark';
  applyTheme(theme);
  localStorage.setItem('og_theme', theme);
}});

// Language Toggle
let currentLang = localStorage.getItem('og_locale') || 'th';
const langToggle = document.getElementById('lang-toggle');
updateLangTitle();
langToggle.addEventListener('click', () => {{
  currentLang = currentLang === 'th' ? 'en' : 'th';
  localStorage.setItem('og_locale', currentLang);
  updateLangTitle();
  if (window.OGi18n) window.OGi18n.setLocale(currentLang);
  langToggle.style.color = '#818cf8';
  setTimeout(() => langToggle.style.color = '', 300);
}});
function updateLangTitle() {{ langToggle.title = currentLang === 'th' ? 'เปลี่ยนเป็น English' : 'Switch to ภาษาไทย'; }}

// Mobile Sidebar
const menuBtn = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
menuBtn.addEventListener('click', () => {{ sidebar.classList.toggle('open'); overlay.classList.toggle('show'); }});
overlay.addEventListener('click', () => {{ sidebar.classList.remove('open'); overlay.classList.remove('show'); }});
function checkMobile() {{ menuBtn.style.display = window.innerWidth <= 1024 ? 'flex' : 'none'; }}
checkMobile();
window.addEventListener('resize', checkMobile);

// Close sidebar on link click (mobile)
document.querySelectorAll('.sb-link').forEach(l => l.addEventListener('click', () => {{
  if (window.innerWidth <= 1024) {{ sidebar.classList.remove('open'); overlay.classList.remove('show'); }}
}}));

// Search Modal
const searchModal = document.getElementById('search-modal');
const searchTrigger = document.getElementById('search-trigger');
const searchInput = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
const searchEmpty = document.getElementById('search-empty');
const searchFooter = document.getElementById('search-footer');
let searchIdx = -1;

{search_js}

function openSearch() {{
  searchModal.classList.add('open');
  requestAnimationFrame(() => {{ searchInput.focus(); searchInput.select(); }});
  renderSearch('');
}}
function closeSearch() {{
  searchModal.classList.remove('open');
  searchInput.value = '';
  searchIdx = -1;
}}

function renderSearch(q) {{
  q = q.trim().toLowerCase();
  if (!q) {{
    searchResults.innerHTML = '';
    searchEmpty.style.display = '';
    searchFooter.classList.add('hidden');
    searchIdx = -1;
    return;
  }}
  const matches = searchData.filter(d =>
    d.title.toLowerCase().includes(q) || d.desc.toLowerCase().includes(q)
  );
  searchEmpty.style.display = 'none';
  searchFooter.classList.remove('hidden');
  searchFooter.style.display = 'flex';
  if (!matches.length) {{
    searchResults.innerHTML = '<div class="search-no-result">No results for "<strong>' + q.replace(/</g,'&lt;') + '</strong>"</div>';
    searchIdx = -1;
    return;
  }}
  searchIdx = 0;
  searchResults.innerHTML = matches.map((m, i) => {{
    const hl = (s) => s.replace(new RegExp('(' + q.replace(/[.*+?^${{}}()|[\\]\\\\]/g,'\\\\$&') + ')','gi'), '<mark>$1</mark>');
    return '<div class="search-item' + (i===0?' active':'') + '" data-href="' + m.href + '">' +
      '<div class="si-icon">' + (siSvg[m.icon] || siSvg.book) + '</div>' +
      '<div class="flex-1 min-w-0"><div class="si-title">' + hl(m.title) + '</div><div class="si-desc">' + hl(m.desc) + '</div></div>' +
      '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-white/15 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>' +
    '</div>';
  }}).join('');
  searchResults.querySelectorAll('.search-item').forEach(el => {{
    el.addEventListener('click', () => {{ navigateTo(el.dataset.href); closeSearch(); }});
  }});
}}

function navigateTo(href) {{
  if (href.startsWith('http') || href.startsWith('../')) {{
    window.location.href = href;
  }} else {{
    window.location.href = href;
  }}
}}

searchInput.addEventListener('input', () => renderSearch(searchInput.value));
searchTrigger.addEventListener('click', openSearch);
searchModal.addEventListener('click', e => {{ if (e.target === searchModal) closeSearch(); }});

document.addEventListener('keydown', e => {{
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {{ e.preventDefault(); openSearch(); return; }}
  if (!searchModal.classList.contains('open')) return;
  if (e.key === 'Escape') {{ closeSearch(); return; }}
  const items = searchResults.querySelectorAll('.search-item');
  if (!items.length) return;
  if (e.key === 'ArrowDown') {{ e.preventDefault(); searchIdx = Math.min(searchIdx + 1, items.length - 1); }}
  if (e.key === 'ArrowUp')   {{ e.preventDefault(); searchIdx = Math.max(searchIdx - 1, 0); }}
  if (e.key === 'Enter' && searchIdx >= 0) {{ e.preventDefault(); navigateTo(items[searchIdx].dataset.href); closeSearch(); return; }}
  items.forEach((el, i) => el.classList.toggle('active', i === searchIdx));
  if (items[searchIdx]) items[searchIdx].scrollIntoView({{ block:'nearest' }});
}});

// Copy to Clipboard
function copyText(text, btn) {{
  navigator.clipboard.writeText(text).then(() => {{
    btn.classList.add('copied');
    const svg = btn.querySelector('svg');
    const orig = svg.innerHTML;
    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>';
    setTimeout(() => {{ btn.classList.remove('copied'); svg.innerHTML = orig; }}, 1800);
  }});
}}

// Build right-side TOC from headings
(function buildTOC() {{
  const tocLinks = document.getElementById('toc-links');
  const main = document.getElementById('doc-main');
  if (!tocLinks || !main) return;
  const headings = main.querySelectorAll('h2, h3');
  if (!headings.length) return;
  const frag = document.createDocumentFragment();
  headings.forEach(h => {{
    let id = h.id;
    if (!id) {{
      id = h.textContent.trim().toLowerCase().replace(/[^a-z0-9ก-๙]+/g, '-').replace(/^-|-$/g, '');
      h.id = id;
    }}
    const a = document.createElement('a');
    a.href = '#' + id;
    a.className = 'toc-link' + (h.tagName === 'H3' ? ' toc-h3' : '');
    a.textContent = h.textContent.replace(/^#\\s*/, '');
    frag.appendChild(a);
  }});
  tocLinks.appendChild(frag);

  // Scroll spy
  const links = tocLinks.querySelectorAll('.toc-link');
  const ids = Array.from(links).map(l => l.getAttribute('href').slice(1));
  let ticking = false;
  function onScroll() {{
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {{
      let current = '';
      ids.forEach(id => {{
        const el = document.getElementById(id);
        if (el && el.getBoundingClientRect().top <= 120) current = id;
      }});
      links.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + current));
      ticking = false;
    }});
  }}
  // Progress bar
  const progressBar = document.getElementById('toc-progress');
  function updateProgress() {{
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const pct = docHeight > 0 ? Math.min((scrollTop / docHeight) * 100, 100) : 0;
    if (progressBar) progressBar.style.width = pct + '%';
  }}

  function onScrollAll() {{
    onScroll();
    updateProgress();
  }}
  window.addEventListener('scroll', onScrollAll, {{ passive: true }});
  onScrollAll();
}})();
</script>
</body>
</html>'''


# ── Main ──
def main():
    print("Extracting sections from docs/_source.html...")
    sections = extract_sections()
    css = get_page_css()

    if not sections:
        print("ERROR: No sections extracted!")
        return

    print(f"Found {len(sections)} sections: {list(sections.keys())}")

    for slug, th, en, ds_key in PAGES:
        content = sections.get(slug, f'<h1>{en}</h1><p class="lead">{th} — Coming soon</p>')
        # Enrich overview content with h2 headings for TOC
        if slug == 'overview':
            content = content.replace(
                '<div class="card-grid">',
                '<h2 id="features">Features</h2>\n<div class="card-grid">'
            ).replace(
                '<div class="info">',
                '<h2 id="philosophy">Philosophy</h2>\n<div class="info">'
            )
        html = generate_page(slug, th, en, content, css)
        filepath = os.path.join(SITE_DIR, f'{slug}.html')
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(html)
        print(f"  ✓ Generated: {slug}.html ({len(html):,} bytes)")

    # ── Blog System ──
    import re as _re

    BLOG_POSTS = [
        {
            'slug': 'opengenetics-v1',
            'title': 'OpenGenetics v1.0 Released',
            'desc': 'เปิดตัว OpenGenetics Framework เวอร์ชัน 1.0 อย่างเป็นทางการ พร้อม JWT Auth, Genetic RBAC, i18n, Audit Trail และ Dual-Frontend SDK',
            'date': '22 Feb 2026',
            'author': 'jkstudio99',
            'gradient': 'linear-gradient(135deg, #6c63ff 0%, #00cfff 100%)',
            'hero_title': 'OpenGenetics',
            'hero_sub': 'v1.0 — Enterprise PHP Micro-Framework',
            'content': """
<h2 id="introduction">Introduction</h2>
<p>วันนี้เราเปิดตัว OpenGenetics Framework เวอร์ชัน 1.0 อย่างเป็นทางการ — Enterprise PHP Micro-Framework ที่ออกแบบมาเพื่อความเร็ว (&lt;50ms), ความปลอดภัย (OWASP), และ Developer Experience ที่ยอดเยี่ยม</p>

<h2 id="whats-new">What's New in v1.0</h2>
<p>เวอร์ชัน 1.0 มาพร้อมกับฟีเจอร์ครบครัน:</p>
<ul>
<li><strong>JWT Authentication</strong> — HS256 auth พร้อม 12-round bcrypt hashing</li>
<li><strong>Genetic RBAC</strong> — Role-Based Access Control รองรับ Admin, HR, Employee guards</li>
<li><strong>i18n Engine</strong> — Thai/English instant switching</li>
<li><strong>Audit Trail</strong> — Non-blocking auto-logging ทุก action</li>
<li><strong>Dual-Frontend SDK</strong> — React Hook + Vanilla JS</li>
</ul>

<h2 id="performance">Performance</h2>
<p>OpenGenetics ถูกออกแบบให้เร็วตั้งแต่แกนกลาง:</p>
<ul>
<li>No ORM — ใช้ PDO Singleton + static cache</li>
<li>Response time &lt; 50ms ในทุก endpoint</li>
<li>Memory footprint ต่ำ เหมาะกับ shared hosting</li>
</ul>

<h2 id="getting-started">Getting Started</h2>
<p>เริ่มต้นใช้งานได้ทันที:</p>
<pre><code><span class="fn">composer</span> create-project open-genetics/framework myapp
<span class="kw">cd</span> myapp
<span class="fn">php</span> genetic serve</code></pre>
<p>เปิด <code>localhost:8000</code> แล้วเริ่มสร้าง API ได้เลย</p>

<h2 id="whats-next">What's Next</h2>
<p>ใน roadmap ถัดไปเราวางแผน:</p>
<ul>
<li>WebSocket support</li>
<li>GraphQL adapter</li>
<li>Docker official image</li>
<li>VS Code extension</li>
</ul>
"""
        },
        {
            'slug': 'genetic-rbac',
            'title': 'Getting Started with Genetic RBAC',
            'desc': 'เรียนรู้วิธีใช้ระบบ Role-Based Access Control แบบ Genetic ที่รองรับ Admin, HR และ Employee guards',
            'date': '20 Feb 2026',
            'author': 'jkstudio99',
            'gradient': 'linear-gradient(135deg, #818cf8 0%, #f472b6 100%)',
            'hero_title': 'Genetic RBAC',
            'hero_sub': 'Role-Based Access Control',
            'content': """
<h2 id="what-is-rbac">What is Genetic RBAC?</h2>
<p>Genetic RBAC เป็นระบบจัดการสิทธิ์แบบ Role-Based ที่ออกแบบมาให้ใช้งานง่ายแต่ทรงพลัง รองรับ guard หลายระดับ ตั้งแต่ Admin, HR ไปจนถึง Employee</p>

<h2 id="setup">Setup</h2>
<p>เปิดใช้งาน RBAC ในไฟล์ config:</p>
<pre><code><span class="st">'guards'</span> => [
    <span class="st">'admin'</span>  => App\\Guards\\AdminGuard::<span class="kw">class</span>,
    <span class="st">'hr'</span>     => App\\Guards\\HRGuard::<span class="kw">class</span>,
    <span class="st">'employee'</span> => App\\Guards\\EmployeeGuard::<span class="kw">class</span>,
]</code></pre>

<h2 id="usage">Usage in Routes</h2>
<p>ใช้ guard ใน route definition:</p>
<pre><code>Route::<span class="fn">group</span>([<span class="st">'guard'</span> => <span class="st">'admin'</span>], <span class="kw">function</span>() {
    Route::<span class="fn">get</span>(<span class="st">'/users'</span>, [UserController::<span class="kw">class</span>, <span class="st">'index'</span>]);
    Route::<span class="fn">delete</span>(<span class="st">'/users/{id}'</span>, [UserController::<span class="kw">class</span>, <span class="st">'destroy'</span>]);
});</code></pre>

<h2 id="custom-guards">Custom Guards</h2>
<p>สร้าง guard ของตัวเองได้ง่ายๆ:</p>
<pre><code><span class="kw">class</span> <span class="cl">HRGuard</span> <span class="kw">implements</span> Guard {
    <span class="kw">public function</span> <span class="fn">check</span>(<span class="vr">$user</span>): <span class="nb">bool</span> {
        <span class="kw">return</span> <span class="vr">$user</span>->role === <span class="st">'hr'</span>
            || <span class="vr">$user</span>->role === <span class="st">'admin'</span>;
    }
}</code></pre>

<h2 id="best-practices">Best Practices</h2>
<ul>
<li>ใช้ guard ที่เฉพาะเจาะจงที่สุดเสมอ</li>
<li>อย่าใช้ <code>admin</code> guard กับทุก route</li>
<li>ทดสอบ guard ด้วย unit test ทุกครั้ง</li>
<li>ใช้ Audit Trail ร่วมกับ RBAC เพื่อ track ทุก action</li>
</ul>
"""
        },
        {
            'slug': 'rest-api-5-minutes',
            'title': 'Building a REST API in 5 Minutes',
            'desc': 'สร้าง REST API ด้วย OpenGenetics CLI — จาก composer create-project ถึง production ใน 5 นาที',
            'date': '18 Feb 2026',
            'author': 'jkstudio99',
            'gradient': 'linear-gradient(135deg, #34d399 0%, #00cfff 100%)',
            'hero_title': 'REST API',
            'hero_sub': 'Build in 5 Minutes',
            'content': """
<h2 id="prerequisites">Prerequisites</h2>
<ul>
<li>PHP 8.1+ with mbstring extension</li>
<li>Composer 2.x</li>
<li>MySQL 8.0+ or MariaDB 10.6+</li>
</ul>

<h2 id="step-1">Step 1: Create Project</h2>
<pre><code><span class="fn">composer</span> create-project open-genetics/framework my-api
<span class="kw">cd</span> my-api</code></pre>

<h2 id="step-2">Step 2: Configure Database</h2>
<p>แก้ไขไฟล์ <code>.env</code>:</p>
<pre><code><span class="vr">DB_HOST</span>=<span class="st">localhost</span>
<span class="vr">DB_NAME</span>=<span class="st">my_api</span>
<span class="vr">DB_USER</span>=<span class="st">root</span>
<span class="vr">DB_PASS</span>=</code></pre>

<h2 id="step-3">Step 3: Run Migrations</h2>
<pre><code><span class="fn">php</span> genetic migrate</code></pre>

<h2 id="step-4">Step 4: Create Your First Endpoint</h2>
<pre><code><span class="fn">php</span> genetic make:controller ProductController</code></pre>
<p>เปิดไฟล์ที่สร้างขึ้นมาแล้วเพิ่ม logic:</p>
<pre><code><span class="kw">class</span> <span class="cl">ProductController</span> {
    <span class="kw">public function</span> <span class="fn">index</span>() {
        <span class="kw">return</span> Response::<span class="fn">json</span>(
            Product::<span class="fn">all</span>()
        );
    }
}</code></pre>

<h2 id="step-5">Step 5: Start Server</h2>
<pre><code><span class="fn">php</span> genetic serve</code></pre>
<p>เปิด <code>http://localhost:8000/api/products</code> — เสร็จ!</p>
"""
        },
        {
            'slug': 'dual-frontend-sdk',
            'title': 'Introducing Dual-Frontend SDK',
            'desc': 'SDK สำเร็จรูปสำหรับ Vanilla JS และ React Hook — Login, i18n, Theme Switching ด้วยโค้ดบรรทัดเดียว',
            'date': '15 Feb 2026',
            'author': 'jkstudio99',
            'gradient': 'linear-gradient(135deg, #f472b6 0%, #fbbf24 100%)',
            'hero_title': 'Dual SDK',
            'hero_sub': 'React Hook + Vanilla JS',
            'content': """
<h2 id="overview">Overview</h2>
<p>Dual-Frontend SDK ให้คุณเชื่อมต่อ frontend กับ OpenGenetics backend ได้ทันที ไม่ว่าจะใช้ React หรือ Vanilla JS</p>

<h2 id="react-hook">React Hook</h2>
<pre><code><span class="kw">import</span> { <span class="fn">useGenetics</span> } <span class="kw">from</span> <span class="st">'@opengenetics/react'</span>;

<span class="kw">function</span> <span class="fn">App</span>() {
  <span class="kw">const</span> { <span class="vr">user</span>, <span class="fn">login</span>, <span class="fn">logout</span> } = <span class="fn">useGenetics</span>();
  <span class="kw">return</span> <span class="vr">user</span>
    ? &lt;p&gt;Hello {<span class="vr">user</span>.name}&lt;/p&gt;
    : &lt;button onClick={<span class="fn">login</span>}&gt;Login&lt;/button&gt;;
}</code></pre>

<h2 id="vanilla-js">Vanilla JS</h2>
<pre><code><span class="kw">import</span> { Genetics } <span class="kw">from</span> <span class="st">'@opengenetics/sdk'</span>;

<span class="kw">const</span> <span class="vr">sdk</span> = <span class="kw">new</span> <span class="fn">Genetics</span>({ baseURL: <span class="st">'/api'</span> });
<span class="kw">await</span> <span class="vr">sdk</span>.<span class="fn">login</span>(<span class="st">'admin@example.com'</span>, <span class="st">'password'</span>);
<span class="kw">const</span> <span class="vr">users</span> = <span class="kw">await</span> <span class="vr">sdk</span>.<span class="fn">get</span>(<span class="st">'/users'</span>);</code></pre>

<h2 id="features">Features</h2>
<ul>
<li><strong>Auto Token Refresh</strong> — JWT token หมดอายุ refresh อัตโนมัติ</li>
<li><strong>i18n Helper</strong> — <code>sdk.t('key')</code> สลับภาษาทันที</li>
<li><strong>Theme Toggle</strong> — <code>sdk.toggleTheme()</code> dark/light mode</li>
<li><strong>TypeScript</strong> — Full type definitions</li>
</ul>
"""
        },
        {
            'slug': 'why-opengenetics',
            'title': 'Why We Built OpenGenetics',
            'desc': 'เบื้องหลังการออกแบบ PHP Micro-Framework ที่เน้นความเร็ว ความปลอดภัย และ Developer Experience',
            'date': '10 Feb 2026',
            'author': 'jkstudio99',
            'gradient': 'linear-gradient(135deg, #6c63ff 0%, #c084fc 100%)',
            'hero_title': 'Why?',
            'hero_sub': 'The Story Behind OpenGenetics',
            'content': """
<h2 id="the-problem">The Problem</h2>
<p>PHP frameworks ส่วนใหญ่มีปัญหาร่วมกัน: ช้า, ซับซ้อน, และต้อง setup เยอะก่อนจะเริ่มงานจริงได้ เราต้องการ framework ที่:</p>
<ul>
<li>เร็วจริง — ไม่ใช่แค่ benchmark สวย</li>
<li>ปลอดภัยตั้งแต่แกะกล่อง — JWT, RBAC, OWASP built-in</li>
<li>Developer Experience ดี — CLI, auto-reload, clear errors</li>
</ul>

<h2 id="design-principles">Design Principles</h2>
<h3 id="small-light-powerful">Small, Light, Powerful</h3>
<p>ทุก byte และ millisecond มีค่า เราเลือก PDO ตรงๆ แทน ORM, ใช้ static cache, และ singleton pattern เพื่อลด overhead</p>

<h3 id="security-first">Security First</h3>
<p>ไม่ต้องติดตั้ง package เพิ่ม — JWT Auth, bcrypt, RBAC, CSRF protection, rate limiting มาครบตั้งแต่ <code>composer create-project</code></p>

<h3 id="dx-matters">DX Matters</h3>
<p>CLI ที่ใช้งานง่าย, error messages ที่อ่านรู้เรื่อง, hot reload, และ documentation ที่ครบถ้วน</p>

<h2 id="conclusion">Conclusion</h2>
<p>OpenGenetics ไม่ได้พยายามเป็น "อีกหนึ่ง PHP framework" — เราสร้างมันเพื่อแก้ปัญหาจริงที่นักพัฒนา PHP เจอทุกวัน ลองใช้แล้วจะเข้าใจ</p>
"""
        },
    ]

    # ── Blog listing page (Elysia-style with featured card + grid) ──
    def build_blog_listing():
        featured = BLOG_POSTS[0]
        cards_html = ''
        # Featured post — large hero card with image left + text right
        cards_html += f'''
<a href="blog/{featured['slug']}.html" style="display:flex;gap:0;text-decoration:none;border-radius:20px;overflow:hidden;border:1px solid rgba(255,255,255,.08);transition:all 300ms;margin-bottom:32px" onmouseover="this.style.borderColor='rgba(108,99,255,.3)';this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 40px rgba(108,99,255,.1)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.transform='none';this.style.boxShadow='none'">
  <div style="flex:1;min-width:280px;min-height:220px;background:{featured['gradient']};display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px">
    <div style="font-size:42px;font-weight:800;color:rgba(255,255,255,.95);letter-spacing:-.03em;text-align:center;line-height:1.1">{featured['hero_title']}</div>
    <div style="font-size:15px;color:rgba(255,255,255,.6);margin-top:10px;text-align:center">{featured['hero_sub']}</div>
  </div>
  <div style="flex:1;padding:36px 40px;display:flex;flex-direction:column;justify-content:center">
    <h2 style="font-size:24px;font-weight:700;color:#f1f5f9;margin:0 0 14px;border:none;padding:0;letter-spacing:-.02em">{featured['title']}</h2>
    <p style="font-size:15px;color:#94a3b8;margin:0 0 20px;line-height:1.75">{featured['desc']}</p>
    <time style="font-size:13px;color:rgba(255,255,255,.2);font-weight:500">{featured['date']}</time>
  </div>
</a>
'''
        # Grid of remaining posts — 2 columns
        cards_html += '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px">\n'
        for post in BLOG_POSTS[1:]:
            cards_html += f'''<a href="blog/{post['slug']}.html" style="display:block;text-decoration:none;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,.08);transition:all 300ms" onmouseover="this.style.borderColor='rgba(108,99,255,.3)';this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(108,99,255,.08)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.transform='none';this.style.boxShadow='none'">
  <div style="height:140px;background:{post['gradient']};display:flex;flex-direction:column;justify-content:center;align-items:center;padding:24px">
    <div style="font-size:28px;font-weight:800;color:rgba(255,255,255,.95);letter-spacing:-.02em">{post['hero_title']}</div>
    <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:6px">{post['hero_sub']}</div>
  </div>
  <div style="padding:20px 24px">
    <h3 style="font-size:16px;font-weight:700;color:#f1f5f9;margin:0 0 8px">{post['title']}</h3>
    <p style="font-size:13px;color:#94a3b8;margin:0 0 12px;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{post['desc']}</p>
    <time style="font-size:12px;color:rgba(255,255,255,.2);font-weight:500">{post['date']}</time>
  </div>
</a>
'''
        cards_html += '</div>'

        return f'''<h1 style="font-size:36px;font-weight:800;letter-spacing:-.03em;margin-bottom:12px;text-align:center">Latest News</h1>
<p class="lead" style="font-size:17px;color:rgba(255,255,255,.4);margin-bottom:48px;text-align:center">Update on the latest news, insights and development progress of OpenGenetics</p>

{cards_html}'''

    blog_content = build_blog_listing()
    blog_html = generate_page('overview', 'บล็อก', 'Blog', blog_content, css)
    # Remove sidebar, overlay, TOC
    blog_html = _re.sub(r'<aside class="sidebar".*?</aside>', '', blog_html, flags=_re.DOTALL)
    blog_html = blog_html.replace('<div class="overlay" id="overlay"></div>', '')
    blog_html = _re.sub(r'<!-- RIGHT TOC -->.*?</div>\s*</div>', '', blog_html, flags=_re.DOTALL)
    blog_html = blog_html.replace(
        '<main class="doc-content" id="doc-main">',
        '<main class="doc-content" id="doc-main" style="margin-left:auto;margin-right:auto;max-width:900px;padding-left:32px;padding-right:32px">'
    )
    # Fix navbar: Blog active, Docs not
    blog_html = blog_html.replace(
        'text-white bg-white/[0.06] transition-all" data-i18n="nav.docs">',
        'text-white/50 hover:text-white hover:bg-white/[0.06] transition-all" data-i18n="nav.docs">'
    )
    blog_html = blog_html.replace(
        'text-white/50 hover:text-white hover:bg-white/[0.06] transition-all" data-i18n="nav.blog">',
        'text-white bg-white/[0.06] transition-all" data-i18n="nav.blog">'
    )
    # Remove page-nav from blog listing
    blog_html = _re.sub(r'<div class="page-nav">.*?</div></div>', '', blog_html, flags=_re.DOTALL)
    with open(os.path.join(SITE_DIR, 'blog.html'), 'w', encoding='utf-8') as f:
        f.write(blog_html)
    print(f"  ✓ Generated: blog.html ({len(blog_html):,} bytes)")

    # ── Individual blog post pages ──
    blog_dir = os.path.join(SITE_DIR, 'blog')
    os.makedirs(blog_dir, exist_ok=True)

    for post in BLOG_POSTS:
        post_content = f'''<div style="margin-bottom:28px">
  <a href="../blog.html" style="font-size:14px;color:#818cf8;text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:6px;transition:color 150ms" onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#818cf8'">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Blog
  </a>
</div>

<h1 style="font-size:32px;font-weight:800;letter-spacing:-.03em;margin-bottom:16px;margin-top:0">{post['title']}</h1>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:28px">
  <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6c63ff,#00cfff);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white">{post['author'][0].upper()}</div>
  <div>
    <div style="font-size:14px;font-weight:600;color:#e2e8f0">{post['author']}</div>
    <div style="font-size:13px;color:rgba(255,255,255,.3)">{post['date']}</div>
  </div>
</div>

<div style="border-radius:16px;overflow:hidden;margin-bottom:36px;background:{post['gradient']};padding:48px 40px;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px">
  <div style="font-size:48px;font-weight:800;color:rgba(255,255,255,.95);letter-spacing:-.03em;text-align:center">{post['hero_title']}</div>
  <div style="font-size:16px;color:rgba(255,255,255,.6);margin-top:10px;text-align:center">{post['hero_sub']}</div>
</div>

{post['content']}
'''
        # Generate with sidebar (for navigation) and TOC
        post_html = generate_page('overview', post['title'], post['title'], post_content, css)
        # Remove sidebar active link
        post_html = post_html.replace('class="sb-link active"', 'class="sb-link"')
        # Remove sidebar and overlay — blog posts are clean like Elysia
        post_html = _re.sub(r'<aside class="sidebar".*?</aside>', '', post_html, flags=_re.DOTALL)
        post_html = post_html.replace('<div class="overlay" id="overlay"></div>', '')
        # Center content, keep TOC
        post_html = post_html.replace(
            '<main class="doc-content" id="doc-main">',
            '<main class="doc-content" id="doc-main" style="margin-left:auto;margin-right:220px;max-width:none;padding:96px 52px 100px">'
        )
        # Fix navbar: Blog active
        post_html = post_html.replace(
            'text-white bg-white/[0.06] transition-all" data-i18n="nav.docs">',
            'text-white/50 hover:text-white hover:bg-white/[0.06] transition-all" data-i18n="nav.docs">'
        )
        post_html = post_html.replace(
            'text-white/50 hover:text-white hover:bg-white/[0.06] transition-all" data-i18n="nav.blog">',
            'text-white bg-white/[0.06] transition-all" data-i18n="nav.blog">'
        )
        # Fix asset paths for blog/ subdirectory (one level deeper)
        post_html = post_html.replace('href="images/', 'href="../images/')
        post_html = post_html.replace('src="images/', 'src="../images/')
        post_html = post_html.replace('src="scripts/', 'src="../scripts/')
        post_html = post_html.replace('../../public/index.html', '../../../public/index.html')
        # Remove page-nav
        post_html = _re.sub(r'<div class="page-nav">.*?</div></div>', '', post_html, flags=_re.DOTALL)
        filepath = os.path.join(blog_dir, f'{post["slug"]}.html')
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(post_html)
        print(f"  ✓ Generated: blog/{post['slug']}.html ({len(post_html):,} bytes)")

    # index.html removed to allow Vercel rewrites to serve landing/index.html at root '/'

    print(f"\nDone! Generated {len(PAGES)} pages + redirect.")


if __name__ == '__main__':
    main()
