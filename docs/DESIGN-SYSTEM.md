# Design System — OpenGenetics

## Overview

OpenGenetics Design System กำหนดมาตรฐานสำหรับ UI ทั้งหมด  
ใช้หลัก **Consistency, Accessibility, Performance** เป็นแกนหลัก

---

## Color Palette

### Dark Mode (Default)

| Token | Hex | Usage |
|-------|-----|-------|
| `--bg-primary` | `#0e0e1a` | Body background |
| `--bg-surface` | `#12121f` | Cards, modals |
| `--bg-elevated` | `#1a1a2e` | Hover states, dropdowns |
| `--text-primary` | `#e2e8f0` | Main text (slate-200) |
| `--text-secondary` | `#94a3b8` | Descriptions (slate-400) |
| `--text-muted` | `rgba(255,255,255,.28)` | Hints, placeholders |
| `--border` | `rgba(255,255,255,.06)` | Card borders |
| `--border-hover` | `rgba(108,99,255,.3)` | Hover borders |
| `--accent` | `#6c63ff` | Primary brand (indigo) |
| `--accent-light` | `#818cf8` | Links, highlights |
| `--accent-lighter` | `#a5b4fc` | Active states |
| `--cyan` | `#00cfff` | Secondary accent |
| `--success` | `#34d399` | Success states |
| `--warning` | `#fbbf24` | Warning states |
| `--error` | `#f87171` | Error states |

### Light Mode

| Token | Hex | Usage |
|-------|-----|-------|
| `--bg-primary` | `#f5f5fa` | Body background |
| `--bg-surface` | `#ffffff` | Cards, modals |
| `--bg-elevated` | `#f8fafc` | Code blocks |
| `--text-primary` | `#1e293b` | Main text (slate-800) |
| `--text-secondary` | `#475569` | Descriptions (slate-600) |
| `--text-muted` | `#94a3b8` | Hints (slate-400) |
| `--border` | `rgba(0,0,0,.08)` | Card borders |
| `--border-hover` | `rgba(79,70,229,.2)` | Hover borders |
| `--accent` | `#4f46e5` | Primary brand (deeper indigo) |
| `--accent-light` | `#6366f1` | Links |
| `--accent-lighter` | `#818cf8` | Active states |

### Gradient Definitions

```css
/* Brand gradient — headings */
.grad-text {
  background: linear-gradient(135deg, #818cf8 0%, #00cfff 55%, #a5b4fc 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Light mode variant */
[data-theme="light"] .grad-text {
  background: linear-gradient(135deg, #4f46e5 0%, #0891b2 55%, #6366f1 100%);
}

/* Secondary gradient */
.grad-text2 {
  background: linear-gradient(90deg, #818cf8, #00cfff);
}

/* Orb gradients (background decoration) */
.orb-1 { background: radial-gradient(circle, rgba(30,24,207,.22), transparent 65%); }
.orb-2 { background: radial-gradient(circle, rgba(0,207,255,.12), transparent 65%); }
.orb-3 { background: radial-gradient(circle, rgba(108,99,255,.14), transparent 65%); }
```

---

## Typography

### Font Stack

```css
font-family: 'Inter', 'Noto Sans Thai', sans-serif;   /* Body */
font-family: 'JetBrains Mono', monospace;              /* Code */
```

### Scale

| Element | Size | Weight | Line Height | Letter Spacing |
|---------|------|--------|-------------|---------------|
| Hero H1 | `clamp(38px, 6.5vw, 72px)` | 800 | 1.08 | -0.03em |
| Section H2 | `clamp(26px, 3.5vw, 42px)` | 800 | 1.15 | -0.025em |
| Card H3 | 16px | 700 | 1.4 | 0 |
| Body | `clamp(15px, 2vw, 18px)` | 400 | 1.8 | 0 |
| Small | 13px | 500 | 1.5 | 0 |
| Caption | 11px | 700 | 1.2 | 0.12em |
| Code | 13px | 400 | 1.9 | 0 |

### Docs Typography

| Element | Size | Weight | Color (Dark) |
|---------|------|--------|-------------|
| Doc H1 | 32px | 800 | `#f1f5f9` |
| Doc H2 | 22px | 700 | `#f1f5f9` |
| Doc H3 | 16px | 600 | `#e2e8f0` |
| Doc Body | 14px | 400 | `#94a3b8` |
| Doc Code (inline) | 13px | 400 | `#a5b4fc` |

---

## Spacing System

Based on **4px grid** (Tailwind defaults):

| Token | Value | Usage |
|-------|-------|-------|
| `xs` | 4px | Inline gaps |
| `sm` | 8px | Tight padding |
| `md` | 16px | Standard padding |
| `lg` | 24px | Section padding |
| `xl` | 32px | Major sections |
| `2xl` | 48px | Section margins |
| `3xl` | 64px | Hero padding |

### Page Layout

| Region | Value |
|--------|-------|
| Max content width | 1100px |
| Sidebar width | 260px |
| TOC width | 220px |
| Navbar height | 56px (h-14) |
| Doc content padding | 44px 52px 100px |

---

## Components

### Buttons

```
Primary Button (CTA)
├── bg: indigo-500
├── text: white, font-semibold, text-lg
├── padding: px-7 py-2.5
├── border-radius: rounded-full (pill)
├── shadow: shadow-lg shadow-indigo-500/30
├── hover: shadow-xl + scale-105
└── transition: all 300ms cubic-bezier(0.16, 1, 0.3, 1)

Ghost Button (Navbar)
├── bg: transparent
├── text: white/50, font-medium, text-[13px]
├── padding: px-3 py-1.5
├── border-radius: rounded-lg
├── hover: text-white + bg-white/[0.06]
└── transition: all 150ms

Icon Button
├── size: w-8 h-8
├── border-radius: rounded-lg
├── color: white/40
├── hover: text-white + bg-white/[0.06]
└── transition: all 150ms
```

### Cards

```
Glass Card (Landing)
├── bg: rgba(255,255,255,.025)
├── border: 1px solid rgba(255,255,255,.06)
├── border-radius: 16px
├── padding: 28px
├── hover: border-color → rgba(108,99,255,.3)
├── hover: bg → rgba(108,99,255,.04)
├── hover: transform → translateY(-3px)
└── transition: all 300ms ease-out-expo

Doc Card
├── bg: rgba(255,255,255,.028)
├── border: 1px solid rgba(255,255,255,.065)
├── border-radius: 12px
├── padding: 18px
├── hover: border-color → rgba(108,99,255,.38)
├── hover: transform → translateY(-2px)
└── transition: all 200ms
```

### Code Blocks

```
Pre Block
├── bg: #080817 (docs) / #0d0d1a (landing)
├── border: 1px solid rgba(108,99,255,.18)
├── border-radius: 12px
├── padding: 22px 26px
├── box-shadow: 0 0 30px rgba(108,99,255,.08)
├── font: JetBrains Mono, 13px, line-height 1.9
└── scrollbar: 4px, rgba(108,99,255,.4)

Inline Code
├── bg: rgba(108,99,255,.15)
├── color: #a5b4fc
├── padding: 2px 7px
├── border-radius: 5px
├── border: 1px solid rgba(108,99,255,.2)
└── font: JetBrains Mono, 13px
```

### Syntax Highlighting

| Token | Dark | Light |
|-------|------|-------|
| Keyword `.kw` | `#c084fc` | `#7c3aed` |
| Function `.fn` | `#60a5fa` | `#2563eb` |
| String `.st` | `#fbbf24` | `#d97706` |
| Comment `.cm` | `#475569` | `#94a3b8` |
| Variable `.vr` | `#34d399` | `#059669` |
| Class `.cl` | `#f472b6` | `#db2777` |
| Built-in `.nb` | `#38bdf8` | `#0284c7` |

### Callouts

```
Tip (Success)
├── bg: rgba(52,211,153,.07)
├── border-left: 3px solid #34d399
├── icon: ✓ checkmark
└── text: #94a3b8

Warning
├── bg: rgba(251,191,36,.07)
├── border-left: 3px solid #fbbf24
├── icon: ⚠ triangle
└── text: #94a3b8

Info
├── bg: rgba(108,99,255,.1)
├── border-left: 3px solid #6c63ff
├── icon: ℹ info circle
└── text: #94a3b8
```

### Search Modal

```
Overlay: rgba(0,0,0,.55) + backdrop-blur(6px)
Box: 560px max, bg #12121f, border-radius 16px
Input: 16px, transparent bg, placeholder rgba(255,255,255,.25)
Results: max-h 320px, scrollable
Item: flex, gap 12px, padding 10px 16px
Item hover: bg rgba(108,99,255,.1)
Highlight: mark tag, bg rgba(108,99,255,.3), color #a5b4fc
Keyboard: ↑↓ navigate, ↵ select, ESC close, ⌘K open
```

---

## Animations

### Easing

```css
/* Primary easing — expo out */
transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);

/* Tailwind class */
ease-out-expo
```

### Hero Entrance (GSAP)

| Element | Delay | Duration | Effect |
|---------|-------|----------|--------|
| Badge | 0.15s | 0.9s | fade + slide up 28px |
| Logo | 0.3s | 1.0s | fade + scale 0.9→1 + slide 20px |
| Heading | 0.45s | 0.9s | fade + slide up 24px |
| Subtitle | 0.55s | 0.8s | fade + slide up 20px |
| CTA | 0.65s | 0.8s | fade + slide up 20px |
| Scroll hint | 0.8s | 0.6s | fade + slide up 12px |
| Circles | 0.3s+ | 1.8s each | stagger fade (0.18s per circle) |

### Decorative

| Animation | Duration | Effect |
|-----------|----------|--------|
| Spinning circles | 24-36s | `rotate(360deg)` linear infinite |
| Hero overlay | 900s | Very slow spin (barely perceptible) |
| Logo float | 6s | translateY 0 → -12px → 0 |
| Dot pulse | 2.2s | box-shadow glow |
| Search glow | 4s | box-shadow pulse |
| Scroll arrow | native | `animate-bounce` |

### Section Reveals (GSAP ScrollTrigger)

```javascript
// Trigger: 80% from top
gsap.fromTo(el, 
  { opacity: 0, y: 28 }, 
  { opacity: 1, y: 0, duration: 0.9, ease: 'expo.out' }
);
```

---

## Navbar

```
Structure:
├── Fixed top, z-50, h-14 (56px)
├── bg: rgba(14,14,26,.75) + backdrop-blur(24px)
├── border-bottom: 1px solid rgba(255,255,255,.055)
├── Layout: [Logo] --- [Search (absolute center)] --- [Nav + Icons]
│
├── Search button: pill, min-width 240px, glow animation
├── Nav links: Docs | Blog | SDK
├── Icons: Theme | Lang | GitHub | X
└── Scroll behavior: solidify bg on scroll
```

---

## Icons

All icons use **Heroicons** (stroke style, 2px stroke-width):
- Search: `magnifying-glass`
- Moon/Sun: theme toggle
- Globe: language toggle
- Copy: custom dual-rectangle stroke
- Arrow: `chevron-down`
- External: `arrow-top-right-on-square`

Social icons use **fill** style:
- GitHub: brand mark
- X/Twitter: brand mark

---

## File Structure

```
public/
├── index.html              # Landing page
├── images/logo/            # Logo assets
├── favicon/                # Favicon set
└── scripts/
    └── i18n-page.js        # Translation engine

docs/
├── index.html              # Documentation site
├── PROJECT-PLAN.md
├── PRD.md
├── DATABASE-ARCHITECTURE.md
├── DESIGN-SYSTEM.md         # ← this file
├── RESPONSIVE-DESIGN.md
└── DEPLOYMENT.md

blog/
└── index.html              # Blog page
```
