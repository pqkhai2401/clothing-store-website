---
name: admin-ui-design-system
description: Design system and reusable UI conventions for the admin panel of this Laravel clothing-store project (resources/views/admin/**). Use this skill whenever building, editing, or reviewing anything in the admin panel — adding a button, badge, status pill, filter dropdown, stat card, data table, form, or AJAX-refreshed list — even if the user just says "add a filter" or "show a stat card" without mentioning design/style explicitly. The project already has an established look (rounded cards, pill badges, a shared custom dropdown component, dark-mode tokens); this skill tells you which existing class to reuse instead of inventing new CSS, and documents the small set of "why" rules (dark mode pairing, AJAX data-attributes, sticky action bars) that keep new admin pages consistent with old ones.
---

# Admin UI Design System

This project's admin panel (`resources/views/admin/**`) is built on Bootstrap 5.3.3 (never Tailwind) plus one shared, hand-rolled design system. Most page-specific stylesheets (`resources/views/admin/*/styles.blade.php`) start with `@include('admin.products.styles')` — that file is the de facto global stylesheet for the whole admin panel, not just the Products page. **Before writing any new CSS or markup for an admin page, grep that file (and this skill's reference files) for an existing class that already does what you need.** Inventing a one-off style when a shared one exists is the single most common way this codebase drifts out of consistency.

The reasoning behind that rule: every admin page was built by copying the same handful of components (stat cards, badges, the custom dropdown, the data table, the sticky form-action bar). A user who's used one page expects every other page to look and behave the same way. When a new page reinvents a badge or a filter dropdown from scratch, it's rarely a deliberate design choice — it's just because the previous convention wasn't found in time. That's the failure mode this skill exists to prevent.

## Quick reference — which class already does this?

| You need to build... | Reuse this | Details |
|---|---|---|
| A colored status pill (order status, stock status, active/inactive...) | `.order-badge`/`.status-badge` + a `--variant` modifier class | `references/badges-and-buttons.md` |
| A page-header action button (Thêm, Xuất Excel, Thùng rác...) | `.product-action-btn` + `.btn-dark` / `.btn-light` / `.product-action-btn--neutral` | `references/badges-and-buttons.md` |
| A row of KPI numbers at the top of a list page | `.product-stat-row` > `.product-stat-card` | `references/badges-and-buttons.md` |
| A custom rounded filter/select dropdown (status, category, "chọn kỳ"...) | `.hk-cat-filter` / `.hk-cat-trigger` / `.hk-cat-panel` / `.hk-cat-list` / `.hk-cat-item` | `references/dropdowns.md` |
| A data table with sortable columns, checkboxes, row actions | `.product-table` + `data-sort-key` + `hk-cb-all`/`hk-cb-row` | `references/tables-and-forms.md` |
| Search/filter that refreshes the table without a page reload | `data-admin-table-area` / `data-admin-search` / `data-admin-filter` | `references/tables-and-forms.md` |
| A create/edit form with a fixed Save/Cancel bar | `.edit-card` / `.edit-field` + `.sticky-action-bar` | `references/tables-and-forms.md` |
| A right-sliding edit panel loaded via AJAX | Bootstrap Offcanvas + `window.runInjectedScripts()` | `references/tables-and-forms.md` |

Read the relevant reference file before building the component — each one has copy-pasteable markup + CSS, not just a description.

## Design tokens (dark mode)

Every admin page must work in both light and dark mode. The theme is driven by CSS custom properties defined in `public/css/admin-theme.css` and toggled via `[data-theme="dark"]` on the root element:

```css
--hk-bg-app     /* page background */
--hk-bg-card    /* card/panel background */
--hk-bg-input   /* form input background */
--hk-border     /* default border color */
--hk-text-1     /* primary text */
--hk-text-2     /* secondary/label text */
--hk-text-3     /* muted/placeholder text */
--hk-accent     /* brand accent (green in light, blue in dark) */
```

Prefer `var(--hk-border, #d8dee6)`-style fallbacks for structural chrome (cards, borders, generic text) so it survives in both themes automatically. For **semantic/status colors** (badges, danger buttons, success stats), the project does NOT use the `--hk-*` tokens — it hand-picks a light-mode color and writes an explicit `[data-theme="dark"] .my-class { ... !important; }` override instead (see the badge formula in `references/badges-and-buttons.md`). Do the same: pick your light-mode color, then always add its dark-mode twin in the same edit. A component that only has light-mode CSS is an incomplete component here, not an optional nice-to-have — the previous work in this project has been screenshot-tested in dark mode every time, and skipping it is the most common gap Claude introduces.

## Compact-view overrides

Some pages (e.g. `admin/orders`) carry an additional "COMPACT VIEW" block at the bottom of their `styles.blade.php` that shrinks font sizes, paddings, and component heights (`min-height: 34px` instead of the default 38px) to fit more on screen. If you're adding a new toolbar element to one of these pages, check whether that block exists and extend it — otherwise your new element will look oversized next to everything else on that specific page.

## When you're adding a genuinely new kind of component

If nothing in the reference files fits, it's fine to add new CSS — this system grew by accretion, one page at a time. Just follow the existing shape: page-scoped class name prefixed by the page/domain (e.g. `oc-` for orders-create, `order-` for the orders page), rounded corners (8–14px depending on size), a light/dark color pair, and — if it's interactive — a `:hover` state. Put it in that page's `styles.blade.php`, not inline, so the next page can find and reuse it the same way you found this one.
