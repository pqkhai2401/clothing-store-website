# Tables, AJAX filtering, and forms

## AJAX list refresh: `data-admin-table-area` / `data-admin-search` / `data-admin-filter`

This is the single most important convention to reuse when adding a new filter, search box, or sort control to any list page — it means you write **zero JavaScript** for the new field.

`resources/views/admin/partials/realtime-table.blade.php` is included once per list page (via `@include('admin.partials.realtime-table')` inside `@push('scripts')`). It:
- Watches any input carrying `data-admin-search` (debounced 500ms) or `data-admin-filter` (on `change`), collects them all by their `name` attribute, and re-fetches the page's URL with those as query params.
- Fetches with `X-Requested-With: XMLHttpRequest`, expects back `{ "html": "<rendered table partial>" }` (and optionally `"stats": "<rendered stat-card partial>"` — see `badges-and-buttons.md`), and swaps `[data-admin-table-area]`'s `innerHTML`.
- Also wires up checkbox select-all/select-row (`hk-cb-all`/`hk-cb-row`), column sort buttons (`.product-sort-btn[data-sort-key]`), and pagination links — all for free, as long as the markup uses those exact classes/attributes.

To add a new filter field to an existing list page:

```blade
{{-- A plain native date/text input just needs the attribute — no JS at all: --}}
<input type="date" name="date_from" data-admin-filter class="form-control" value="{{ $dateFrom ?? '' }}">

{{-- A custom hk-cat-filter dropdown's hidden input needs it too: --}}
<input type="hidden" name="status" data-admin-filter id="statusHidden" value="{{ $statusVal }}">
```

Controller side — the `index()` method should already branch on `$request->ajax()` to return the JSON partial; if you're adding a *new* list page from scratch, copy that branch from `OrderController@index` or `ProductController@index` rather than writing a new AJAX handler.

## Data table conventions

```blade
<table class="table table-hover product-table align-middle" id="thingTable">
    <thead>
        <tr>
            <th style="width:44px;"><input type="checkbox" class="form-check-input product-check hk-cb-all"></th>
            <th style="width:110px;">
                <button type="button" class="product-sort-btn {{ $isActive('total') }}" data-sort-key="total" data-sort-type="number">
                    Tổng tiền <span class="product-sort-icon">{{ $sortIcon('total') }}</span>
                </button>
            </th>
            {{-- ...more columns... --}}
            <th class="text-center" style="width:90px;">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            <tr>
                <td><input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $item->id }}"></td>
                <td data-sort-value="{{ $item->total }}">{{ number_format($item->total) }}₫</td>
                {{-- ... --}}
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="{{ route('admin.things.detail', $item->id) }}" class="order-row-action-btn" title="Xem chi tiết"><i class="fa-regular fa-eye"></i></a>
                        <button type="button" class="order-row-action-btn" title="Cập nhật"><i class="fa-regular fa-pen-to-square"></i></button>
                    </div>
                </td>
            </tr>
        @empty
            <tr data-empty-row>
                <td colspan="{N}" class="text-center py-5">
                    <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                    <div class="fw-semibold text-muted">Chưa có gì cả</div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
```

Row actions: prefer two small icon buttons (`.order-row-action-btn`, 30×30px, `border-radius:8px`, `background:#F1F5F9`) shown directly in the row, over hiding them behind a "..." dropdown menu — this project explicitly moved away from the "..." pattern for the two most common row actions (view/update) because it added an extra click for something used constantly. Reserve a "..." menu (`.product-more-btn` + `.dropdown-menu.product-row-menu`) for 3+ less-frequent actions.

Column-width discipline: give every column an explicit `width` in its `<th>`. An unconstrained column will stretch to absorb whatever space is left over, which reliably produces a column with far more blank space than its content needs (this happened to the "Khách hàng" column in the orders table).

**But giving every column a width is not enough by itself** — `.product-table` (the shared class, from `admin/products/styles.blade.php`) carries `min-width: 1420px`. On a page with fewer/narrower columns than Products (e.g. Sizes, ~900px of declared column widths), the table still renders at 1420px, and the browser's automatic table-layout algorithm distributes that extra width **proportionally across every column** — every column looks noticeably wider than its declared width, all at once. This is the "dư khoảng trống" (leftover blank space) complaint.

**The fix is to override just `min-width`, scoped to that table's `#id`, sized to the actual sum of this page's column widths** — nothing else:

```css
#thisTableId {
    min-width: 1194px; /* = sum of every <th> width on this page, measured, not guessed */
}
```

Leave Bootstrap's `width: 100%` on `.table` alone. That's what makes the table **responsive**: at any container width above your `min-width`, the browser stretches all columns proportionally to fill the space (same mechanism that just caused the bug — except now every column has an explicit width, so the extra space splits fairly across all of them instead of dumping into one). At container widths below `min-width`, `.table-responsive` kicks in a horizontal scrollbar instead of squeezing columns unreadably.

Do **not** reach for `table-layout: fixed` + `width: auto` + `min-width: 0` here, even though it looks like the more surgical fix (it pins every column to its exact declared px and stops the proportional-stretch algorithm entirely). It solves the leftover-blank-space bug at *one* viewport width, then reintroduces the exact same bug at any wider one — the table stays hard-capped at the declared sum while its `.product-table-wrap` card (still `width: 100%`) keeps growing, so the gap just reappears beside the table instead of inside a column. This surfaces in practice when the user zooms the browser out (e.g. to 90%) — the effective CSS viewport gets wider, and a `table-layout:fixed` table doesn't follow. `table-layout: fixed` is only correct when you deliberately want a non-responsive, exact-width table (rare in this codebase) — the `min-width`-only approach is the default.

Verify by measuring at more than one viewport width, not eyeballing one screenshot. Resize to something noticeably wider than your dev viewport (or narrower, near your chosen `min-width`) and recheck — a fix that only works at the width you happened to test in is not verified. In a JS console (or via `preview_eval`): compare `table.getBoundingClientRect().width` to its `.product-table-wrap`'s width at both a wide and a narrow viewport. They should track together (small few-px gap from borders) at every width above `min-width`; below it, the table should hold `min-width` and scroll horizontally rather than shrink.

## Forms: `.edit-card` / `.edit-field` and the sticky action bar

Create/edit pages (full-page forms, not offcanvas panels) use:

```css
.edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
.edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
.edit-field { margin-bottom: 18px; }
.edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
```

```blade
<div class="card edit-card shadow-sm mb-4">
    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Section title</span></div>
    <div class="card-body p-4">
        <div class="edit-field">
            <label>Field label <span class="text-danger">*</span></label>
            <input type="text" name="field" class="form-control @error('field') is-invalid @enderror" value="{{ old('field') }}">
            @error('field') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
```

The Save/Cancel bar at the bottom of a long form should be a **fixed** bar pinned to the viewport bottom, not merely `position: sticky` inside a column (sticky-within-a-column only sticks for the height of that column, so it can scroll away — a real bug found and fixed on the "Thêm đơn hàng" page). Put the bar as a sibling of the `.row`, not nested inside one of its columns:

```css
.sticky-action-bar {
    position: fixed; bottom: 0; display: flex; justify-content: flex-end; gap: 12px;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    padding: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.08); z-index: 1030; margin-bottom: 16px;
}
```

```js
// Keep the fixed bar's left/width in sync with the content column (not the sidebar) —
// re-measure on load and on resize, and via ResizeObserver so it also reacts to sidebar collapse/expand.
const bar = document.getElementById('actionBar');
const ref = document.querySelector('#theForm .row');
function sync() {
    const rect = ref.getBoundingClientRect();
    bar.style.left = rect.left + 'px';
    bar.style.width = rect.width + 'px';
}
new ResizeObserver(sync).observe(ref);
window.addEventListener('resize', sync);
sync();
```

Also add `padding-bottom` to `.row` (e.g. `90px`) so the last card isn't hidden behind the fixed bar.

## Offcanvas edit panels loaded via AJAX

For quick edits that shouldn't navigate away from the list (e.g. "Sửa sản phẩm" sliding in from the right), the controller's `edit()`/`show()` detects `$request->ajax()` and returns `{'html' => view(...)->render()}`; the trigger fetches it and injects into a Bootstrap Offcanvas body. Because `innerHTML` doesn't execute injected `<script>` tags, the shared helper `window.runInjectedScripts(container)` (defined once in `admin/products/scripts.blade.php`) must be called right after injecting — it walks the container, rebuilds each `<script>` node, and swaps it in so the browser actually runs it. Reuse this helper rather than re-solving the same "my inline script didn't run after AJAX injection" problem again.
