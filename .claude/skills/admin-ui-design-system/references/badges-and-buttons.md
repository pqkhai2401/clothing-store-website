# Badges, buttons, and stat cards

## Status pill formula

Every status/state badge in this codebase (order status, payment status, stock status, active/inactive...) follows the same recipe: a pastel background, a 1.5px border one shade darker, and saturated text in the same hue. Base class + `--variant` modifier:

```css
.order-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;      /* full pill */
    font-size: 12px;
    font-weight: 700;
    padding: 5px 13px;
    min-height: 28px;
    line-height: 1;
    white-space: nowrap;
}
.order-badge--pending    { background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; } /* amber/warning */
.order-badge--processing { background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1D4ED8; } /* blue/info */
.order-badge--shipping   { background: #F0F9FF; border: 1.5px solid #BAE6FD; color: #0369A1; } /* light blue */
.order-badge--completed  { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #16A34A; } /* green/success */
.order-badge--cancelled  { background: #FEF2F2; border: 1.5px solid #FECACA; color: #DC2626; } /* red/danger */
```

Dark mode: keep the same border/text hue but drop the background to a translucent tint of the accent color, and lighten the text:

```css
[data-theme="dark"] .order-badge--pending {
    background: rgba(251,191,36,0.12) !important;
    border-color: rgba(251,191,36,0.3) !important;
    color: #FCD34D !important;
}
/* same pattern for each variant: translucent bg at .12 alpha, border at .3 alpha, a lighter text shade */
```

The semantic hue-to-meaning mapping used everywhere in this project:
- **Amber/warning** (`#FDE68A`/`#92400E`) → pending / awaiting action
- **Blue/info** (`#BFDBFE`/`#1D4ED8`) → in progress
- **Green/success** (`#86EFAC`/`#16A34A`) → completed / active / paid
- **Red/danger** (`#FECACA`/`#DC2626`) → cancelled / inactive / unpaid

`.status-badge` (used for simpler active/inactive toggles, e.g. products/users) is a smaller, squared-off variant of the same idea — see `.status-badge--active`/`.status-badge--inactive` in `admin/products/styles.blade.php` or `admin/users/styles.blade.php` for the exact values before copying.

## Header action buttons

Page-header buttons (top-right of a list page: "Thêm X", "Xuất Excel", "Thùng rác"...) share one base class plus a modifier:

```css
.product-action-btn {
    min-height: 38px;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
}
.product-action-btn.btn-dark {           /* primary / create action */
    background: #16A34A !important;
    border-color: #16A34A !important;
    color: #ffffff !important;
}
.product-action-btn.btn-dark:hover { background: #15803D !important; border-color: #15803D !important; }

.product-action-btn.btn-light {          /* destructive action */
    background: #ffffff !important;
    border: 1.5px solid #F87171 !important;
    color: #DC2626 !important;
}
.product-action-btn.btn-light:hover { background: #FEF2F2 !important; border-color: #EF4444 !important; color: #B91C1C !important; }

.product-action-btn--neutral {           /* secondary / export action */
    background: #ffffff !important;
    border: 1.5px solid #D8E0EA !important;
    color: #334155 !important;
}
.product-action-btn--neutral:hover { background: #F8FAFC !important; border-color: #CBD5E1 !important; color: #0F172A !important; }
```

Markup convention — primary (create) action goes **first**, then secondary/neutral actions, in the `.product-header-actions` row:

```blade
<div class="product-header-actions">
    <a href="{{ route('admin.things.create') }}" class="btn btn-dark product-action-btn">
        <i class="fa-solid fa-plus me-1"></i> Thêm thứ gì đó
    </a>
    <a href="..." class="btn product-action-btn product-action-btn--neutral">
        <i class="fa-solid fa-download me-1"></i> Xuất Excel
    </a>
</div>
```

## Stat cards (KPI row)

The row of number tiles at the top of a list page (e.g. "Tổng đơn hàng", "Doanh thu", "Đơn chờ duyệt"):

```css
.product-stat-row { margin-top: 8px; margin-bottom: 20px; }
.product-stat-card {
    background: #ffffff;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 16px 20px;
    height: 100%;
}
.product-stat-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #64748B;
    margin-bottom: 8px;
}
.product-stat-value { font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1; }
.product-stat-value--success { color: #16A34A; }
.product-stat-value--danger  { color: #DC2626; }
```

```blade
<div class="row g-3 product-stat-row">
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Nhãn thẻ</div>
            <div class="product-stat-value product-stat-value--success">{{ number_format($value) }}</div>
        </div>
    </div>
    {{-- repeat per stat, col-md-4 for 3 cards, col-lg-3 for 4 cards --}}
</div>
```

If the stat card row needs to update live when filters change (without a full page reload), wrap it in `<div data-admin-stats-area>@include('admin.orders.partials.stats')</div>` and have the controller return a rendered `stats` HTML string alongside `html` in the AJAX JSON response — `admin/partials/realtime-table.blade.php` already swaps `[data-admin-stats-area]` in automatically when `data.stats` is present in the response. See `tables-and-forms.md` for the full AJAX contract.

Dark mode for all of the above: card background → `#0F1B31`, border → `#22324D`, label → `#94A3B8`, value → `#F8FAFC` (success/danger variants lighten to `#4ADE80`/`#F87171`).
