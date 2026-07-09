# The custom dropdown component (`hk-cat-filter`)

Never use a native `<select>` for a filter or a status picker in this admin panel — this project deliberately replaced every native select with a custom button-triggered dropdown because native selects render as unstyled OS chrome that clashes with the rest of the UI (this was an explicit user complaint fixed earlier in the project: "quá cứng và khó nhìn với giao diện" — too stiff/rigid, doesn't fit the interface). Reuse this component instead.

## Anatomy

```blade
<div class="hk-cat-filter" id="hkThingDrop">
    <button type="button" class="hk-cat-trigger" id="hkThingTrigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="hk-cat-trigger-label" id="hkThingLabel">{{ $selectedLabel }}</span>
        <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
    </button>
    <div class="hk-cat-panel" id="hkThingPanel" hidden>
        <div class="hk-cat-list" id="hkThingList" role="listbox">
            <button type="button" class="hk-cat-item {{ $val === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả">Tất cả</button>
            @foreach($options as $val => $label)
                <button type="button" class="hk-cat-item {{ $val === $current ? 'is-active' : '' }}" data-value="{{ $val }}" data-label="{{ $label }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>
</div>
```

## JS wiring

```js
(function () {
    const trigger = document.getElementById('hkThingTrigger');
    const panel   = document.getElementById('hkThingPanel');
    const label   = document.getElementById('hkThingLabel');
    const list    = document.getElementById('hkThingList');
    const hidden  = document.getElementById('thingHiddenInput'); // the real form field, data-admin-filter
    if (!trigger) return;

    function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
    function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

    trigger.addEventListener('click', () => panel.hidden ? open() : close());

    list.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        label.textContent = btn.dataset.label;
        hidden.value = btn.dataset.value;
        close();
        hidden.dispatchEvent(new Event('change', { bubbles: true })); // triggers AJAX table refresh, see tables-and-forms.md
    });

    document.addEventListener('click', function (e) {
        if (!panel.hidden && !document.getElementById('hkThingDrop')?.contains(e.target)) close();
    });
}());
```

For per-row dropdowns rendered inside a table that gets replaced via AJAX (e.g. a quick-edit status picker in each row), don't attach listeners per-row — delegate from `document` once, keyed off `.closest('.hk-cat-item')` etc., exactly like the pattern above but scoped so it survives the table's `innerHTML` being replaced. See the "Sổ xuống chọn nhanh" block in `admin/orders/index.blade.php` for a full worked example (open/close + AJAX PATCH + optimistic revert on failure).

## Base CSS (already defined — don't redefine, just use)

Defined once in `admin/products/styles.blade.php` (included by nearly every admin page). If a page's own `styles.blade.php` doesn't `@include('admin.products.styles')`, check `admin/users/styles.blade.php` too — it keeps its own independent copy of this component (a known duplication; if you fix something here, fix it in both places).

```css
.hk-cat-filter { position: relative; width: 220px; flex: 0 0 220px; } /* override width per-field if needed */
.hk-cat-trigger {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    width: 100%; min-height: 38px; padding: 0 16px;
    border: 1px solid #D8E0EA; border-radius: 999px; background: #fff;
    font-size: 14px; color: #0F172A; cursor: pointer; transition: border-color .15s;
}
.hk-cat-trigger:hover, .hk-cat-trigger.is-open { border-color: #16A34A; }
.hk-cat-trigger.is-open .hk-cat-arrow { transform: rotate(180deg); }

.hk-cat-panel {
    position: absolute; top: calc(100% + 6px); right: 0; z-index: 1050;
    width: 240px; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 14px; box-shadow: 0 8px 32px rgba(15,23,42,.14); overflow: hidden;
}
.hk-cat-list { max-height: 240px; overflow-y: auto; padding: 6px; display: flex; flex-direction: column; gap: 2px; }
.hk-cat-item {
    display: block; width: 100%; padding: 8px 12px; border: 0; border-radius: 8px;
    background: transparent; font-size: 13px; color: #374151; text-align: left; cursor: pointer;
    transition: background .12s;
}
.hk-cat-item:hover { background: #f3f4f6; }
.hk-cat-item.is-active { color: #111827; font-weight: 700; background: #f0f9ff; }
```

**The rounded-highlight rule matters and is easy to get wrong**: `.hk-cat-list` has `padding: 6px` (not `6px 0`) and `gap: 2px` between items, and `.hk-cat-item` has its own `border-radius: 8px`. Together these make the active item render as a small inset rounded rectangle with ~7px of breathing room on every side — not a bar that spans edge-to-edge with square corners. This was a deliberate fix (a native `<select>`'s dropdown list, and an earlier edge-to-edge version of this component, both read as "cứng" / stiff). If you ever see a dropdown item touching the panel's left/right edges, that's the bug to look for.

## Positioning: which side does the panel open from?

By default `.hk-cat-panel` is anchored `right: 0`, meaning it grows **leftward** from the trigger's right edge — correct for filters near the right side of a toolbar. For a dropdown near the **left** edge of its container (e.g. a per-row dropdown in the first few table columns, or a field inside a form/create page where the trigger already starts near the container's left edge), override with a page-scoped rule so the panel grows rightward instead and doesn't overflow off-screen to the left:

```css
.my-page-scope .hk-cat-panel { left: 0; right: auto; width: 100%; /* or a fixed px width */ }
```

Use `width: 100%` when the dropdown is inside a form field and should match the trigger's full width (so long option labels like a full street address don't wrap awkwardly inside a narrower fixed-width panel). Use a fixed px width for compact table-row dropdowns where the trigger's own width varies with the currently-selected label.
