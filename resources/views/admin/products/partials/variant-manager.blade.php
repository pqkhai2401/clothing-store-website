{{--
    Variant Manager Partial
    Variables expected:
      $colors  — Collection<Color>  (id, name, display_hex_code)
      $sizes   — Collection<Size>   (id, name)
      $existingVariants — optional, array keyed [color_id][size_id] = stock (for edit mode)
--}}
@php
    $existingVariants = $existingVariants ?? [];
@endphp

<div class="vm-wrap" id="variantManager">

    {{-- ── SECTION HEADER ── --}}
    <div class="vm-section-header">
        <p class="vm-section-sub mb-0">
            {{-- <i class="fa-solid fa-circle-info me-1" style="color:#000000;"></i> --}}
            <strong>Hướng dẫn:</strong> Chọn màu → tích size → nhập số lượng tồn kho
        </p>
        <span class="vm-selected-summary" id="vmSummary" style="display:none;"></span>
    </div>

    {{-- ── BƯỚC 1: CHỌN MÀU ── --}}
    <div class="vm-step">
        <div class="vm-step-label">
            <span class="vm-step-num">1.</span>
            Chọn màu sắc
        </div>

        <div class="vm-color-grid" id="vmColorGrid">
            @forelse($colors as $color)
                @php
                    $hex = $color->display_hex_code ?? '#cccccc';
                    $isLight = in_array(strtolower($hex), ['#ffffff','#fff','#f5f5f5','#fafafa','#efefef','#e5e7eb']);
                    $checked = isset($existingVariants[$color->id]);
                @endphp
                <label class="vm-color-chip {{ $checked ? 'is-checked' : '' }}"
                       data-color-id="{{ $color->id }}"
                       data-color-name="{{ $color->name }}"
                       data-color-hex="{{ $hex }}">
                    <input type="checkbox"
                           class="vm-color-cb"
                           value="{{ $color->id }}"
                           {{ $checked ? 'checked' : '' }}>
                    <span class="vm-color-dot {{ $isLight ? 'is-light' : '' }}"
                          style="background:{{ $hex }};"></span>
                    <span class="vm-color-name">{{ $color->name }}</span>
                    <span class="vm-color-check"><i class="fa-solid fa-check"></i></span>
                </label>
            @empty
                <p class="text-muted small">Chưa có màu nào. <a href="{{ route('admin.colors.list') }}">Thêm màu</a></p>
            @endforelse
        </div>
    </div>

    {{-- ── BƯỚC 2: CẤU HÌNH SIZE & KHO ── --}}
    <div class="vm-step" id="vmStep2" style="{{ empty($existingVariants) ? 'display:none;' : '' }}">
        <div class="vm-step-label">
            <span class="vm-step-num">2.</span>
            Cấu hình size &amp; kho hàng
        </div>

        <div class="vm-boxes-wrap" id="vmBoxesWrap">
            {{-- Boxes are injected by JS; pre-render for existing variants in edit mode --}}
        </div>
    </div>

</div>

{{-- ── SIZES JSON for JS ── --}}
<script>
window.__VM_SIZES__ = @json($sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());
window.__VM_EXISTING__ = @json($existingVariants);
</script>

{{-- ══════════════════════════════════════════ --}}
{{-- CSS                                        --}}
{{-- ══════════════════════════════════════════ --}}
@once
@push('styles')
<style>
/* ── Wrapper ── */
.vm-wrap {
    --vm-radius: 12px;
    --vm-border: #e5e7eb;
    --vm-accent: #111827;
    --vm-accent-light: #f0fdf4;
    --vm-green: #16a34a;
}

.vm-section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.vm-section-sub {
    font-size: 14px;
    color: #000000;
    margin: 0;
}

.vm-selected-summary {
    font-size: 12px;
    font-weight: 600;
    color: var(--vm-green);
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Steps ── */
.vm-step {
    margin-bottom: 20px;
}

.vm-step-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
}

.vm-step-num {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
}

/* ── Color chips ── */
.vm-color-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.vm-color-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 12px 7px 8px;
    border: 1.5px solid var(--vm-border);
    border-radius: 99px;
    cursor: pointer;
    user-select: none;
    transition: border-color 0.15s, background 0.15s, transform 0.1s;
    position: relative;
    background: #fff;
}

.vm-color-chip:hover {
    border-color: #9ca3af;
    transform: translateY(-1px);
}

.vm-color-chip.is-checked {
    border-color: var(--vm-accent);
    background: #f9fafb;
}

.vm-color-chip input[type="checkbox"] {
    display: none;
}

.vm-color-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: transform 0.15s;
}

.vm-color-dot.is-light {
    border: 1.5px solid #d1d5db;
}

.vm-color-chip.is-checked .vm-color-dot {
    transform: scale(0.85);
}

.vm-color-name {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.vm-color-check {
    display: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--vm-accent);
    color: #fff;
    font-size: 9px;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.vm-color-chip.is-checked .vm-color-check {
    display: inline-flex;
}

/* ── Variant boxes ── */
.vm-boxes-wrap {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.vm-box {
    border: 1.5px solid var(--vm-border);
    border-radius: var(--vm-radius);
    overflow: hidden;
    animation: vmBoxIn 0.2s ease;
}

@keyframes vmBoxIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.vm-box-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f9fafb;
    border-bottom: 1.5px solid var(--vm-border);
    gap: 10px;
}

.vm-box-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.vm-box-color-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    flex-shrink: 0;
}

.vm-box-color-name {
    font-size: 14px;
    font-weight: 700;
    color: #111;
}

.vm-box-stock-total {
    font-size: 13px;
    color: #6b7280;
}

/* ── Size rows ── */
.vm-size-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0;
}

.vm-size-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-bottom: 1px solid #f3f4f6;
    border-right: 1px solid #f3f4f6;
    transition: background 0.1s;
}

.vm-size-row:hover { background: #fafafa; }

.vm-size-row.is-active {
    background: #f0fdf4;
}

.vm-size-cb {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: var(--vm-accent);
    flex-shrink: 0;
}

.vm-size-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    min-width: 36px;
    cursor: pointer;
}

.vm-stock-input {
    width: 72px;
    height: 34px;
    border: 1.5px solid #d1d5db;
    border-radius: 6px;
    padding: 0 8px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    outline: none;
    transition: border-color 0.15s, opacity 0.15s;
    background: #fff;
    margin-left: auto;
}

.vm-stock-input:disabled {
    opacity: 0.3;
    background: #f9fafb;
    cursor: not-allowed;
}

.vm-stock-input:not(:disabled):focus {
    border-color: var(--vm-accent);
    box-shadow: 0 0 0 3px rgba(17,24,39,0.07);
}

.vm-stock-unit {
    font-size: 12px;
    color: #9ca3af;
    flex-shrink: 0;
}

/* ── Empty state ── */
.vm-box-empty {
    padding: 20px 16px;
    text-align: center;
    color: #9ca3af;
    font-size: 12px;
}
</style>
@endpush
@endonce

{{-- ══════════════════════════════════════════ --}}
{{-- JavaScript                                 --}}
{{-- ══════════════════════════════════════════ --}}
@once
@push('scripts')
<script>
(function () {
    const sizes    = window.__VM_SIZES__   || [];
    const existing = window.__VM_EXISTING__ || {};

    const colorGrid  = document.getElementById('vmColorGrid');
    const step2      = document.getElementById('vmStep2');
    const boxesWrap  = document.getElementById('vmBoxesWrap');
    const summary    = document.getElementById('vmSummary');
    if (!colorGrid || !boxesWrap) return;

    /* ── Utilities ── */
    function esc(str) {
        return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function updateSummary() {
        const boxes   = boxesWrap.querySelectorAll('.vm-box');
        let totalVariants = 0;
        let totalStock    = 0;
        boxes.forEach(box => {
            box.querySelectorAll('.vm-size-cb:checked').forEach(cb => {
                totalVariants++;
                const inp = box.querySelector(`.vm-stock-input[data-size-id="${cb.dataset.sizeId}"]`);
                totalStock += parseInt(inp?.value || 0, 10);
            });
        });
        if (totalVariants > 0) {
            summary.style.display = '';
            summary.textContent   = `${totalVariants} biến thể · ${totalStock.toLocaleString('vi-VN')} sp`;
        } else {
            summary.style.display = 'none';
        }
    }

    function updateBoxStockTotal(box) {
        let total = 0;
        box.querySelectorAll('.vm-size-cb:checked').forEach(cb => {
            const inp = box.querySelector(`.vm-stock-input[data-size-id="${cb.dataset.sizeId}"]`);
            total += parseInt(inp?.value || 0, 10);
        });
        const el = box.querySelector('.vm-box-stock-total');
        if (el) el.textContent = total > 0 ? `Tổng: ${total.toLocaleString('vi-VN')} sp` : '';
        updateSummary();
    }

    /* ── Build a variant box for a color ── */
    function buildBox(colorId, colorName, colorHex) {
        const isLight = ['#ffffff','#fff','#f5f5f5','#efefef','#e5e7eb','#fafafa']
            .includes(colorHex.toLowerCase());

        const existingForColor = existing[colorId] || {};

        const sizeRows = sizes.map(size => {
            const isChecked = size.id in existingForColor;
            const stockVal  = existingForColor[size.id] ?? 0;
            return `
                <div class="vm-size-row${isChecked ? ' is-active' : ''}" data-size-row="${size.id}">
                    <input type="checkbox"
                           class="vm-size-cb"
                           data-color-id="${colorId}"
                           data-size-id="${size.id}"
                           ${isChecked ? 'checked' : ''}>
                    <label class="vm-size-label">${esc(size.name)}</label>
                    <input type="number"
                           class="vm-stock-input"
                           name="variants[${colorId}][${size.id}]"
                           data-size-id="${size.id}"
                           min="0" value="${stockVal}"
                           ${isChecked ? '' : 'disabled'}
                           placeholder="0">
                    <span class="vm-stock-unit">sp</span>
                </div>`;
        }).join('');

        const box = document.createElement('div');
        box.className  = 'vm-box';
        box.id         = `vm-box-${colorId}`;
        box.dataset.colorId = colorId;
        box.innerHTML  = `
            <div class="vm-box-header">
                <div class="vm-box-header-left">
                    <span class="vm-box-color-dot${isLight ? ' border' : ''}"
                          style="background:${colorHex};${isLight?'border:1.5px solid #d1d5db;':''}"></span>
                    <span class="vm-box-color-name">${esc(colorName)}</span>
                    <span class="vm-box-stock-total"></span>
                </div>
            </div>
            <div class="vm-size-grid">${sizes.length ? sizeRows : '<div class="vm-box-empty">Chưa có size nào.</div>'}</div>
        `;

        /* wire size checkboxes */
        box.querySelectorAll('.vm-size-cb').forEach(cb => {
            cb.addEventListener('change', () => onSizeCbChange(box, cb));
        });

        /* initial stock total */
        updateBoxStockTotal(box);

        return box;
    }

    function onSizeCbChange(box, cb) {
        const row = box.querySelector(`[data-size-row="${cb.dataset.sizeId}"]`);
        const inp = box.querySelector(`.vm-stock-input[data-size-id="${cb.dataset.sizeId}"]`);

        if (cb.checked) {
            row?.classList.add('is-active');
            if (inp) {
                inp.disabled = false;
                inp.focus();
                if (!inp.value || inp.value === '0') inp.value = '';
            }
        } else {
            row?.classList.remove('is-active');
            if (inp) { inp.disabled = true; inp.value = 0; }
        }

        updateBoxStockTotal(box);
    }

    /* ── Color chip toggle ── */
    colorGrid.addEventListener('change', function (e) {
        if (!e.target.matches('.vm-color-cb')) return;
        const chip      = e.target.closest('.vm-color-chip');
        const colorId   = parseInt(e.target.value, 10);
        const colorName = chip.dataset.colorName;
        const colorHex  = chip.dataset.colorHex;

        if (e.target.checked) {
            chip.classList.add('is-checked');

            /* show step 2 */
            step2.style.display = '';

            /* append box */
            if (!document.getElementById(`vm-box-${colorId}`)) {
                boxesWrap.appendChild(buildBox(colorId, colorName, colorHex));
            }
        } else {
            chip.classList.remove('is-checked');

            /* remove box */
            document.getElementById(`vm-box-${colorId}`)?.remove();

            /* hide step 2 if no boxes */
            if (!boxesWrap.querySelector('.vm-box')) {
                step2.style.display = 'none';
            }
        }

        updateSummary();
    });

    /* wire stock inputs (live total) - delegated */
    boxesWrap.addEventListener('input', function (e) {
        if (!e.target.matches('.vm-stock-input')) return;
        const box = e.target.closest('.vm-box');
        if (box) updateBoxStockTotal(box);
    });

    /* ── Pre-render existing variants (edit mode) ── */
    const existingColorIds = Object.keys(existing).map(Number);
    if (existingColorIds.length) {
        existingColorIds.forEach(cid => {
            const chip = colorGrid.querySelector(`.vm-color-chip[data-color-id="${cid}"]`);
            if (!chip) return;
            chip.classList.add('is-checked');
            const cb = chip.querySelector('.vm-color-cb');
            if (cb) cb.checked = true;
            boxesWrap.appendChild(buildBox(cid, chip.dataset.colorName, chip.dataset.colorHex));
        });
        step2.style.display = '';
        updateSummary();
    }
})();
</script>
@endpush
@endonce
