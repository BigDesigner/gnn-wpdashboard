# Implementation Plan — GNN WPDashboard Modern SaaS UI Redesign (Hardened)

## 1. Overview & Objective
Redesign the **GNN WPDashboard** interface into a modern, clean, SaaS-style WordPress dashboard layout, while maintaining 100% backward compatibility with existing AJAX handlers, security sanitization, and WordPress admin styles.

---

## 2. Hardened Architecture & DOM Contract

To prevent runtime JavaScript crashes and ensure all installer/updater actions work seamlessly, the redesigned template and script MUST strictly preserve the following DOM contract:

| DOM Element ID / Class | Purpose | Requirement |
| :--- | :--- | :--- |
| `#plugin-grid-container` | Card grid container | Must exist and receive rendered cards |
| `#search-input` | Search box | Must trigger dynamic filter on input |
| `#btn-sync-plugins`, `#sync-icon` | Update check button & spinning icon | Must trigger `gnn_wpdashboard_check_updates` |
| `.category-tab[data-category="..."]` | Top category filter tabs | Must preserve exact category names |
| `.status-tab[data-status="..."]` | Segmented status pills | Must preserve `all`, `uninstalled`, `active`, `inactive`, `updates` |
| `#count-all`, `#count-uninstalled`, `#count-active`, `#count-inactive`, `#count-updates` | Badge counter spans | Must exist inside status pills to prevent JS null errors |
| `#plugin-total-count` | Total filtered counter | Display active items count |
| `#btn-bulk-install`, `#btn-bulk-activate`, `#btn-bulk-deactivate`, `#btn-bulk-delete` | Bulk action triggers | Must exist in DOM and toggle `.hidden` based on counts |
| `#details-modal` & inner IDs | Plugin detail modal popup | Must be preserved in template |
| `.btn-install-plugin`, `.btn-activate-plugin`, `.btn-deactivate-plugin`, `.btn-delete-plugin`, `.btn-open-details` | Card action buttons | Must keep exact classes and `data-slug`, `data-file`, `data-type` |

---

## 3. Proposed Changes

### A. Template: `gnn-wpdashboard/templates/dashboard-page.php`
1. **Scope & Containment**: Ensure all root elements are strictly wrapped within `<div class="wrap gnn-wpdashboard-wrap font-body text-on-surface">` with no leaky global CSS.
2. **Top Header Bar**:
   - GNN brand logo with rounded icon container.
   - Status badge: `<span class="gnn-registered-badge">AKTİF / REGISTERED</span>`.
   - Horizontal top category tabs (`.category-tab`) with horizontal overflow scroll for mobile.
3. **Sub-Header Hero Section**:
   - Left side: Icon badge, breadcrumbs (`GNN / Eklentiler & Temalar`), page title.
   - Right side: Dynamic **System Status Widget** displaying theme/dashboard update status.
   - Segmented status filter pills (`.status-tab`) with counter spans (`#count-*`).
4. **Notice / Banner Bar**:
   - Server notice banner and `#update-notification-banner`.
5. **Bulk Actions Bar**:
   - Preserves total item count (`#plugin-total-count`) and bulk action buttons.
6. **Card Grid Container**:
   - 3-column responsive grid (`grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6`).
7. **Modal & Footer**:
   - Preserve `#details-modal`.
   - Add modern clean footer (`© GNN Dashboard • Dokümantasyon • Destek`).

### B. JavaScript: `gnn-wpdashboard/assets/js/admin.js`
1. **`createCardHTML(item)` Redesign**:
   - Pure white card layout with subtle border (`border border-slate-200/80 rounded-2xl shadow-sm hover:shadow-md`).
   - Top-right repo link / star icon.
   - Centered icon container with soft colored background and 100% white solid badge.
   - Centered title and description with `escapeHtml()` sanitization on all fields.
   - Bottom card footer:
     - Left: `Sürüm: ${safeDisplayVer}` (or update indicator `v${safeInstalledVer} ➔ v${safeLatestVer}`).
     - Right: Action button:
       - Install: Emerald green (`bg-emerald-600 hover:bg-emerald-700 text-white`).
       - Active: Muted slate pill (`bg-slate-100 text-slate-700 hover:bg-slate-200`).
       - Update: Solid amber/emerald update button.
       - Delete & Details: Clean secondary text actions.
2. **Horizontal Tabs Active State**:
   - Update `.category-tab` click handler to smoothly update active class on the top navigation bar.
3. **Dynamic System Status Card**:
   - Update the hero status widget when update check completes.

### C. Stylesheet: `gnn-wpdashboard/assets/css/admin.css`
1. Scoped under `.gnn-wpdashboard-wrap`:
   - Design tokens: `#f8fafc` background, `#10b981` primary green, `#e2e8f0` borders.
   - Centered card icon styling (`.gnn-brand-icon-box`).
   - Horizontal tab navigation styling with active indicators.
   - Responsive rules for tablet (`md:`) and mobile (<768px).

---

## 4. Pre-Mortem Failure Simulation & Risk Register

| Risk / Failure Mode | Likelihood | Impact | Applied Mitigation in Plan |
| :--- | :---: | :---: | :--- |
| **Missing Counter Badges**: Redesigning status tabs drops `#count-all`, causing JS errors in `updateCounters()` | Low | High | Enforced strict DOM contract; all 5 counter IDs preserved in segmented pill markup |
| **Broken Action Delegation**: CSS classes or `data-slug`/`data-file` changed during card restyling | Medium | High | Reused exact delegated classes (`.btn-install-plugin`, etc.) and data attributes in `createCardHTML()` |
| **CSS Leakage to WP Admin**: New utility classes or unrestrained layout breaking WordPress sidebar/admin bar | Low | Medium | All CSS rules strictly scoped under `.gnn-wpdashboard-wrap` with no root tag overrides |
| **Mobile Layout Overflow**: Horizontal category bar overflowing on small screens | Medium | Low | Added `overflow-x-auto whitespace-nowrap scrollbar-none` to tab navigation container |
| **XSS Injection via API Data**: Unescaped repo descriptions or titles injected into innerHTML | Low | Critical | Preserved strict `escapeHtml()` wrapping on all dynamic strings in `createCardHTML()` |

---

## 5. Verification Steps
1. **PHP Syntax Check**: `php -l gnn-wpdashboard/templates/dashboard-page.php`.
2. **DOM Element Integrity**: Inspect DOM for all contract IDs (`#plugin-grid-container`, `#search-input`, `#count-*`, etc.).
3. **Action Verification**:
   - Test search filter.
   - Test category filtering (SEO, Güvenlik, Temalar, etc.).
   - Test status filtering (Hazır, Aktif, Pasif, Güncellemeler).
   - Test single install / update / activate / deactivate.
   - Test details modal open / close.
4. **Visual Verification**: Check responsive rendering on desktop (3-column) and mobile.

---

### Audit Notes
- Audited against `.specs/boundary-conditions.md`, `.specs/constitution.md`, and `.memory-bank/system-coherence.md`.
- Enforced complete DOM element contract preservation to guarantee zero regression on AJAX functions.
- Sanitization verified: all dynamic card content strictly passed through `escapeHtml()`.
- Responsive scrolling guards added for top horizontal navigation tabs.
