# instructions.md — GoliNaab Build Plan (No Code in This File)

**How to use:** Run **one step at a time**. Ask clarifying questions before acting. After each step, report: changed files, key decisions, risks, and Acceptance results. Follow `.cursorrules` strictly.

---

## Step 0 — Theme Skeleton & Tooling
**Goal:** directories, required theme files, Tailwind pipeline, `theme.json` tokens, base enqueues, ACF JSON.

**Expected changes**
- Folders: `assets/{styles,scripts,icons}`, `components`, `templates`, `inc`, `acf-json/`.
- Files: `style.css` (theme header), `functions.php` (supports + conditional enqueues), `index.php`, minimal `header.php`/`footer.php`.
- Tailwind + PostCSS config (purge against PHP/templates), `theme.json` (palette/typography/radii/shadows).
- Font loading hooks (Doran/Crimson), `font-display: swap`.
- `.gitignore` for build/vendor/uploads where applicable.

**Acceptance**
- Site renders; no broken styles.
- CSS/JS within budgets; Lighthouse base ≥ 85.
- `acf-json/` recognized by ACF (saving field groups creates JSON here).

**Pitfalls**
- Over-purging Tailwind; global enqueues; missing `wp_body_open`.

---

## Step 1 — Header (Desktop/Mobile) + Slide-in Menu + Predictive Search
**Goal:** Desktop: Brand | wide typeahead search | 4-item menu + account.  
Mobile: hamburger → right slide-in panel with lavender backdrop; close on overlay/ESC; focus trap.

**Expected**
- Header partial (RTL/LTR-aware), `wp_nav_menu` location `primary`.
- Accessible toggle script (ARIA, focus trap, body scroll lock).
- REST endpoint (nonce-protected) returning combined results: Products(≤5) + Courses(≤5) with highlighted matches.
- Keyboard navigation (↑/↓/Enter/Escape), ellipsis for long titles. Debounce ≈200ms; min length 1.

**Acceptance**
- Smooth slide/backdrop fade; no layout shift.
- 1-char input yields sensible results; TTI preserved; Query Monitor clean.

**Pitfalls**
- Focus leakage; excessive requests; missing rate-limit.

---

## Step 2 — Full-width Hero with Soft Parallax + Primary CTA
**Goal:** Full-bleed image (user-replaceable), ultra-soft parallax, prominent CTA.

**Expected**
- Hero section with low-cost parallax (IntersectionObserver + transform).
- Respect `prefers-reduced-motion`.

**Acceptance**
- No jank; AA contrast for heading/CTA; CLS ≈ 0.

---

## Step 3 — Category Cards (4-up / 2×2 on Mobile) + Buy/Rent Split
**Goal:** 4 main categories (cat1–cat4) with PNG icons.  
cat1 → on click, morph into two cards: **Buy** vs **Rent** (overlay + reversible animation).

**Expected**
- Responsive grid, rounded cards, soft shadows; modal/morph interaction accessible (keyboard/ARIA).
- Icons easily replaceable by admin (ACF/media).

**Acceptance**
- AA contrast; ESC closes; keyboard reachable; no console errors.

---

## Step 4 — Dual Sliders (Embla): “New Products” & “Popular Courses”
**Goal:** Two independent columns with auto-play, faded edges, single-line titles (ellipsis).

**Expected**
- Embla initialized only on Home; item cards with minimal meta and proper links.

**Acceptance**
- Stable FPS; library not enqueued off-Home; touch/keyboard usable.

---

## Step 5 — Footer with Faded Top Border + Base Meta
**Goal:** Visual merge (gradient→transparent), required links (Terms/Privacy/Contact), base favicon/OG/manifest.

**Acceptance**
- Natural fade; accessible links; meta present in source.

---

## Step 6 — Lightweight English Path `/en`
**Goal:** `/en` variant shows **Courses only**, LTR + English font; header language switcher.

**Expected**
- Route detection (`/en`), conditional templates/content, hidden Shop/Rental/Laser.
- Separate meta/OG; `hreflang` links; sitemaps separable by path.

**Acceptance**
- Working switcher; clean separation FA/EN; no stray RTL styles in EN.

---

## Step 7 — WooCommerce Minimal + MU-Plugin Integration
**Goal:** Strip non-essential WC assets off non-shop pages; disable tracking/cron; HPOS ready; **respect existing MU-plugins**.

**Expected**
- Implement/use `gn_is_shop_context()` helper for every Woo enqueue/dequeue decision:
  - true if Woo pages OR has Woo shortcodes/blocks OR explicit Woo regions.
- Keep mini-cart functional without global cart fragments (use a small REST endpoint if needed).
- Ensure no re-enablement of Admin UI or tracking disabled by MU-plugins.

**Acceptance**
- Noticeable reduction in requests/weight; Query Monitor clean (no l10n/dependency warnings).

---

## Step 8 — ACF Field Groups & Content Types
**Goal:** Define ACF groups and attach to:
- **Products (WooCommerce)** for collectible flowers:
  - `gn_collection_name` (text), `gn_materials` (repeater/tags), `gn_limited_edition` (toggle), `gn_featured` (toggle)
- **Course (CPT)** fields:
  - `gn_difficulty_level` (select), `gn_duration` (text), `gn_gamification_stage` (int), `gn_related_product` (post object → product)
- **Laser Service (CPT)** fields:
  - `gn_service_type` (select: Laser Cutting | Vector Design | Both)
  - `gn_upload_file` (svg/pdf/dxf/cdr/ai; size/MIME validation)
  - `gn_material` (text/select), `gn_dimensions_cm` (text), `gn_estimated_cost` (number), `gn_instructions` (textarea)
- **Rental Request (CPT)** fields:
  - `gn_start_date`, `gn_end_date`, `gn_deposit_ack` (true/false), `gn_phone`, `gn_notes`

**Acceptance**
- ACF JSON files generated in `acf-json/` and committed.
- Save/fetch works; no XSS; admin UI clean.

---

## Step 9 — Rental Request Flow + Terms & Deposit Page
**Goal:** Non-booking MVP: legal page + secure form that calculates/prints summary and stores `rental_request`.

**Expected**
- Nonce-protected form, validation (`end_date ≥ start_date`), email/notification basics.

**Acceptance**
- Correct calculations; clean error states; spam-resistant.

---

## Step 10 — Final Optimization & Audits
**Goal:** lazy-load, defer/async, final purge, DOM/cost trimming, A11y/SEO audits.

**Acceptance**
- Lighthouse: Perf ≥ 90, A11y ≥ 95, Best ≥ 95, SEO ≥ 95 (mobile 4G emulation).
- No console errors; budgets respected; CLS under control.

---

## Phase 2 (Optional, Post-MVP)
- **WooCommerce Bookings** (true rentals with calendars/deposits).
- **LMS integration** (Tutor/alternatives) with badges/certificates.
- **PWA/Service Worker** with safe routing and cache strategies.

---
### Commit Policy (Conventional Commits)
Use atomic commits like:
- `feat(header): add RTL slide-in menu with focus trap`
- `perf(search): debounce typeahead and trim payload under 2 KB`
- `fix(wc): whitelist blocks assets on checkout only`
- `chore(acf): add laser_service fields and sync to acf-json`
- `refactor(theme): extract embla init to isolated module`
