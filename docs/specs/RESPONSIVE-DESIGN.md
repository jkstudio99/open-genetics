# Responsive Design — OpenGenetics

## Overview

OpenGenetics ใช้ **Mobile-First** approach ร่วมกับ Tailwind CSS breakpoints  
ทุกหน้าต้องทำงานได้ดีตั้งแต่ **320px** ถึง **2560px**

---

## Breakpoints

| Name | Min Width | Target |
|------|-----------|--------|
| `xs` | 0px | โทรศัพท์แนวตั้ง |
| `sm` | 640px | โทรศัพท์แนวนอน |
| `md` | 768px | แท็บเล็ต |
| `lg` | 1024px | แล็ปท็อป |
| `xl` | 1280px | เดสก์ท็อป |
| `2xl` | 1536px | จอใหญ่ |

---

## Landing Page (`public/index.html`)

### Navbar

| Breakpoint | Behavior |
|-----------|----------|
| < 640px (xs) | ซ่อน Search button, ซ่อน nav links (Docs/Blog/SDK) |
| >= 640px (sm) | แสดง Search button (pill) |
| >= 768px (md) | แสดง nav links + divider + icons |
| All | Logo + icon buttons visible ทุกขนาด |

```
Mobile:  [Logo] ──────────────── [Theme] [Lang] [GitHub] [X]
Tablet+: [Logo] ─ [Search ⌘K] ─ [Docs Blog SDK | Theme Lang GitHub X]
```

### Hero Section

| Breakpoint | Behavior |
|-----------|----------|
| xs | H1: 38px, subtitle: 15px, CTA wrap vertically |
| sm | Code snippet inline, copy button visible |
| lg | H1: 72px, subtitle: 18px, 5th spinning circle visible |

```css
/* Fluid typography */
font-size: clamp(38px, 6.5vw, 72px);     /* H1 */
font-size: clamp(15px, 2vw, 18px);       /* Subtitle */
height: clamp(56px, 7vw, 84px);          /* Logo */
```

### Feature Cards

| Breakpoint | Grid |
|-----------|------|
| xs | 1 column |
| sm | 2 columns |
| lg | 3 columns |

```html
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
```

### Code Showcase

| Breakpoint | Behavior |
|-----------|----------|
| xs | Full width, horizontal scroll |
| md | Centered with max-width |
| All | Tab buttons wrap if needed |

### Footer

| Breakpoint | Behavior |
|-----------|----------|
| xs | Single column, centered |
| md | Flex row with links |

---

## Documentation (`docs/index.html`)

### Three-Column Layout

```
Desktop (>= 1280px):
┌──────────┬──────────────────────┬─────────┐
│ Sidebar  │    Main Content      │   TOC   │
│  260px   │    flexible          │  220px  │
└──────────┴──────────────────────┴─────────┘

Laptop (1024-1279px):
┌──────────┬─────────────────────────────────┐
│ Sidebar  │    Main Content                 │
│  260px   │    flexible (TOC hidden)        │
└──────────┴─────────────────────────────────┘

Mobile (< 1024px):
┌─────────────────────────────────────────────┐
│  [☰] Main Content (full width)              │
│       Sidebar = slide-in overlay            │
│       TOC hidden                            │
└─────────────────────────────────────────────┘
```

### CSS Implementation

```css
/* Base (mobile) */
.doc-content {
  margin-left: 0;
  padding: 32px 24px 80px;
}

/* Desktop */
@media (min-width: 1024px) {
  .sidebar { position: fixed; width: 260px; }
  .doc-content { margin-left: 260px; }
}

/* Wide desktop */
@media (min-width: 1280px) {
  .toc { position: fixed; width: 220px; }
  .doc-content { margin-right: 220px; }
}
```

### Mobile Sidebar

| State | Behavior |
|-------|----------|
| Closed | `transform: translateX(-100%)` |
| Open | `transform: translateX(0)` + overlay backdrop |
| Trigger | Hamburger button (visible < 1024px) |
| Close | Click overlay หรือ click link |

```css
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 250ms;
    z-index: 50;
    box-shadow: 0 4px 40px rgba(0,0,0,.5);
  }
  .sidebar.open { transform: translateX(0); }
  .menu-btn { display: flex !important; }
}
```

### Tables

| Breakpoint | Behavior |
|-----------|----------|
| xs | `overflow-x: auto` with horizontal scroll |
| md+ | Full width table |

### Code Blocks

| Breakpoint | Behavior |
|-----------|----------|
| All | `overflow-x: auto`, 4px scrollbar |
| xs | Reduced padding (16px) |
| md+ | Full padding (22px 26px) |

---

## Blog Page (`blog/index.html`)

### Layout

| Breakpoint | Behavior |
|-----------|----------|
| xs | Single column, full width cards |
| md | Max-width 900px centered |
| All | Cards stack vertically with 24px gap |

---

## Touch & Interaction

### Touch Targets

| Element | Min Size | Standard |
|---------|---------|----------|
| Buttons | 44x44px | WCAG 2.5.5 |
| Nav links | 44px height | Padding py-1.5 |
| Icon buttons | 32x32px | w-8 h-8 |
| Sidebar links | 32px height | Padding py-6px |

### Hover vs Touch

```css
/* Hover effects only on devices with hover support */
@media (hover: hover) {
  .card:hover { transform: translateY(-3px); }
  .copy-btn { opacity: 0; }
  .copy-wrap:hover .copy-btn { opacity: 1; }
}

/* Touch devices — always visible */
@media (hover: none) {
  .copy-btn { opacity: 1; }
}
```

---

## Performance Budget

| Metric | Target | Current |
|--------|--------|---------|
| First Contentful Paint | < 1.5s | ~0.8s |
| Largest Contentful Paint | < 2.5s | ~1.2s |
| Cumulative Layout Shift | < 0.1 | ~0.02 |
| Total Blocking Time | < 200ms | ~50ms |
| Lighthouse Performance | > 90 | 92 |

### Optimization

- **Fonts**: `display=swap` + preconnect
- **Tailwind**: CDN (dev) / purge (production)
- **GSAP**: async load, only on landing page
- **Images**: SVG logos, no raster images
- **CSS**: Inline critical styles in `<style>` tag

---

## Testing Checklist

### Device Testing

- [ ] iPhone SE (375px)
- [ ] iPhone 14 (390px)
- [ ] iPad Mini (768px)
- [ ] iPad Pro (1024px)
- [ ] MacBook Air (1440px)
- [ ] Desktop (1920px)
- [ ] Ultra-wide (2560px)

### Browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

### Accessibility

- [ ] Keyboard navigation ทำงานทุกหน้า
- [ ] Screen reader compatible
- [ ] Color contrast ratio >= 4.5:1
- [ ] Focus indicators visible
- [ ] Skip-to-content link
- [ ] `aria-label` on icon buttons
- [ ] `alt` text on images
