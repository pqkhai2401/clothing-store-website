{{--
    Variant Manager Partial
    Variables expected:
      $colors  — Collection<Color>  (id, name, display_hex_code)
      $sizes   — Collection<Size>   (id, name) — mixed letter sizes (S,M,L..) and number sizes (38,39..)
      $existingVariants — optional, nested [color_id][size_id] = ['sku'=>, 'cost_price'=>, 'sale_price'=>, 'stock'=>] (edit mode)
--}}
@php
    $existingVariants = $existingVariants ?? [];
@endphp

<div class="vm-wrap" id="variantManager">

    {{-- ── SECTION HEADER ── --}}
    <div class="vm-section-header">
        <p class="vm-section-sub mb-0">
            <strong>Hướng dẫn:</strong> Chọn màu → chọn hệ size → tích size → bảng biến thể sẽ tự sinh bên dưới
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

    {{-- ── BƯỚC 2: HỆ SIZE + CHỌN SIZE ── --}}
    <div class="vm-step">
        <div class="vm-step-label">
            <span class="vm-step-num">2.</span>
            Hệ size &amp; chọn size
        </div>

        <div class="vm-size-system-tabs" id="vmSizeSystemTabs" role="tablist">
            <button type="button" class="vm-size-system-tab is-active" data-system="letters">Size chữ (S, M, L...)</button>
            <button type="button" class="vm-size-system-tab" data-system="numbers">Size số (38, 39, 40...)</button>
        </div>

        <div class="vm-size-pick-grid" id="vmSizePickGrid">
            @forelse($sizes as $size)
                @php
                    $system  = preg_match('/^\d+$/', trim($size->name)) ? 'numbers' : 'letters';
                    $checked = false;
                    foreach ($existingVariants as $colorVariants) {
                        if (isset($colorVariants[$size->id])) { $checked = true; break; }
                    }
                @endphp
                <label class="vm-size-pick-chip vm-size-system-{{ $system }} {{ $checked ? 'is-checked' : '' }}"
                       data-system="{{ $system }}">
                    <input type="checkbox"
                           class="vm-size-pick-cb"
                           data-size-id="{{ $size->id }}"
                           data-size-name="{{ $size->name }}"
                           {{ $checked ? 'checked' : '' }}>
                    <span class="vm-size-pick-label">{{ $size->name }}</span>
                </label>
            @empty
                <p class="text-muted small">Chưa có size nào. <a href="{{ route('admin.sizes.list') }}">Thêm size</a></p>
            @endforelse
        </div>
    </div>

    {{-- ── BƯỚC 3: BẢNG MA TRẬN BIẾN THỂ ── --}}
    <div class="vm-step vm-step-matrix" id="vmMatrixStep" style="{{ empty($existingVariants) ? 'display:none;' : '' }}">
        <div class="vm-step-label">
            <span class="vm-step-num">3.</span>
            Bảng biến thể (SKU / Giá vốn / Giá bán / Tồn kho)
        </div>

        {{-- Bulk apply bar --}}
        <div class="vm-bulk-bar">
            <span class="vm-bulk-label">Áp dụng nhanh:</span>
            <div class="vm-bulk-field">
                <label>Giá vốn</label>
                <input type="number" min="0" step="1000" class="vm-bulk-input" id="vmBulkCost" placeholder="0">
            </div>
            <div class="vm-bulk-field">
                <label>Giá bán</label>
                <input type="number" min="0" step="1000" class="vm-bulk-input" id="vmBulkSale" placeholder="0">
            </div>
            <div class="vm-bulk-field">
                <label>Tồn kho</label>
                <input type="number" min="0" step="1" class="vm-bulk-input" id="vmBulkStock" placeholder="0">
            </div>
            <button type="button" class="vm-bulk-apply-btn" id="vmBulkApplyBtn">
                <i class="fa-solid fa-bolt me-1"></i> Áp dụng cho tất cả
            </button>
        </div>

        <div class="vm-matrix-table-wrap">
            <table class="vm-matrix-table">
                <thead>
                    <tr>
                        <th>Biến thể</th>
                        <th>SKU</th>
                        <th>Giá vốn (₫)</th>
                        <th>Giá bán (₫)</th>
                        <th>Tồn kho</th>
                    </tr>
                </thead>
                <tbody id="vmMatrixBody">
                    {{-- Rows injected by JS --}}
                </tbody>
            </table>
            <div class="vm-matrix-empty" id="vmMatrixEmpty">Chọn màu và size để tạo bảng biến thể.</div>
        </div>
    </div>

</div>

{{-- ── DATA FOR JS ── --}}
<script>
window.__VM_SIZES__    = @json($sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());
window.__VM_EXISTING__ = @json($existingVariants);
</script>

{{-- ══════════════════════════════════════════ --}}
{{-- CSS                                        --}}
{{-- ══════════════════════════════════════════ --}}
{{--
    Lưu ý: KHÔNG dùng @push('styles')/@push('scripts') ở đây — vì khi partial này được
    render riêng lẻ qua AJAX (mở panel Sửa sản phẩm dạng trượt phải), nội dung push sẽ
    không bao giờ được xuất ra (chỉ layout chính mới gọi @stack). Style/script phải nằm
    trực tiếp trong HTML trả về để hoạt động đúng ở cả 2 chế độ (trang đầy đủ & panel AJAX).
--}}
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
    margin-bottom: 24px;
}
.vm-step:last-child { margin-bottom: 0; }

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

/* ── Size system tabs ── */
.vm-size-system-tabs {
    display: inline-flex;
    background: #f3f4f6;
    border-radius: 10px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 14px;
}

.vm-size-system-tab {
    border: 0;
    background: transparent;
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s, box-shadow 0.15s;
    white-space: nowrap;
}

.vm-size-system-tab:hover { color: #374151; }

.vm-size-system-tab.is-active {
    background: #fff;
    color: #111827;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

/* ── Size pick chips (flat multi-select grid, no clipping) ── */
.vm-size-pick-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.vm-size-pick-chip {
    display: none; /* toggled via JS based on active system */
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1.5px solid var(--vm-border);
    border-radius: 8px;
    cursor: pointer;
    user-select: none;
    background: #fff;
    transition: border-color 0.15s, background 0.15s;
    min-width: 52px;
    justify-content: center;
    box-sizing: border-box;
}

.vm-size-pick-chip.vm-size-system-visible {
    display: inline-flex;
}

.vm-size-pick-chip:hover { border-color: #9ca3af; }

.vm-size-pick-chip.is-checked {
    border-color: var(--vm-accent);
    background: #f9fafb;
}

.vm-size-pick-chip input[type="checkbox"] { display: none; }

.vm-size-pick-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    overflow: visible;
}

/* ── Bulk apply bar ── */
.vm-bulk-bar {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 8px;
    background: #f9fafb;
    border: 1.5px solid var(--vm-border);
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 14px;
}

.vm-bulk-label {
    width: 100%;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}

.vm-bulk-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.vm-bulk-field label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 600;
}

.vm-bulk-input {
    width: 90px;
    height: 34px;
    border: 1.5px solid #d1d5db;
    border-radius: 6px;
    padding: 0 8px;
    font-size: 13px;
    outline: none;
}

.vm-bulk-input:focus {
    border-color: var(--vm-accent);
    box-shadow: 0 0 0 3px rgba(17,24,39,0.07);
}

.vm-bulk-apply-btn {
    height: 34px;
    border: 0;
    border-radius: 6px;
    background: #111827;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 0 12px;
    cursor: pointer;
    transition: background 0.15s;
    white-space: nowrap;
}

.vm-bulk-apply-btn:hover { background: #000; }

/* ── Matrix table ── */
.vm-matrix-table-wrap {
    border: 1.5px solid var(--vm-border);
    border-radius: var(--vm-radius);
    overflow-x: auto;
}

.vm-matrix-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 620px;
}

.vm-matrix-table thead th {
    background: #f9fafb;
    text-align: left;
    padding: 10px 12px;
    font-weight: 700;
    color: #374151;
    border-bottom: 1.5px solid var(--vm-border);
    white-space: nowrap;
}

.vm-matrix-table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.vm-matrix-table tbody tr:last-child td { border-bottom: 0; }
.vm-matrix-table tbody tr { animation: vmRowIn 0.15s ease; }

@keyframes vmRowIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.vm-matrix-combo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}

.vm-matrix-combo-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    flex-shrink: 0;
}

.vm-matrix-input {
    width: 100%;
    min-width: 90px;
    height: 34px;
    border: 1.5px solid #d1d5db;
    border-radius: 6px;
    padding: 0 8px;
    font-size: 13px;
    outline: none;
    box-sizing: border-box;
}

.vm-matrix-input:focus {
    border-color: var(--vm-accent);
    box-shadow: 0 0 0 3px rgba(17,24,39,0.07);
}

.vm-matrix-empty {
    display: none;
    padding: 28px 16px;
    text-align: center;
    color: #9ca3af;
    font-size: 13px;
}

.vm-matrix-table-wrap.is-empty .vm-matrix-table { display: none; }
.vm-matrix-table-wrap.is-empty .vm-matrix-empty { display: block; }
</style>

{{-- ══════════════════════════════════════════ --}}
{{-- JavaScript                                 --}}
{{-- ══════════════════════════════════════════ --}}
<script>
(function () {
    const existing = window.__VM_EXISTING__ || {};

    const colorGrid    = document.getElementById('vmColorGrid');
    const sizeTabs      = document.getElementById('vmSizeSystemTabs');
    const sizePickGrid  = document.getElementById('vmSizePickGrid');
    const matrixStep    = document.getElementById('vmMatrixStep');
    const matrixBody    = document.getElementById('vmMatrixBody');
    const matrixWrap     = document.querySelector('.vm-matrix-table-wrap');
    const summary       = document.getElementById('vmSummary');
    if (!colorGrid || !sizePickGrid || !matrixBody) return;

    function esc(str) {
        return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function slugify(str) {
        return String(str || '')
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/gi, 'd')
            .toUpperCase()
            .replace(/[^A-Z0-9]+/g, '')
            .substring(0, 6) || 'SP';
    }

    /* ═══════════════════════════════════════════
       SIZE SYSTEM TABS
    ═══════════════════════════════════════════ */
    sizeTabs?.addEventListener('click', function (e) {
        const btn = e.target.closest('.vm-size-system-tab');
        if (!btn) return;
        sizeTabs.querySelectorAll('.vm-size-system-tab').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        applySizeSystemVisibility(btn.dataset.system);
    });

    function applySizeSystemVisibility(system) {
        sizePickGrid.querySelectorAll('.vm-size-pick-chip').forEach(function (chip) {
            chip.classList.toggle('vm-size-system-visible', chip.dataset.system === system);
        });
    }

    /* ═══════════════════════════════════════════
       COLOR CHIP TOGGLE
    ═══════════════════════════════════════════ */
    colorGrid.addEventListener('change', function (e) {
        if (!e.target.matches('.vm-color-cb')) return;
        const chip = e.target.closest('.vm-color-chip');
        chip.classList.toggle('is-checked', e.target.checked);
        rebuildMatrix();
    });

    /* ═══════════════════════════════════════════
       SIZE CHIP TOGGLE
    ═══════════════════════════════════════════ */
    sizePickGrid.addEventListener('change', function (e) {
        if (!e.target.matches('.vm-size-pick-cb')) return;
        const chip = e.target.closest('.vm-size-pick-chip');
        chip.classList.toggle('is-checked', e.target.checked);
        rebuildMatrix();
    });

    /* ═══════════════════════════════════════════
       MATRIX BUILD (cross-product of selected colors x sizes)
    ═══════════════════════════════════════════ */
    function getSelectedColors() {
        return Array.from(colorGrid.querySelectorAll('.vm-color-cb:checked')).map(cb => {
            const chip = cb.closest('.vm-color-chip');
            return { id: cb.value, name: chip.dataset.colorName, hex: chip.dataset.colorHex };
        });
    }

    function getSelectedSizes() {
        return Array.from(sizePickGrid.querySelectorAll('.vm-size-pick-cb:checked')).map(cb => ({
            id: cb.dataset.sizeId, name: cb.dataset.sizeName,
        }));
    }

    function readCurrentValues() {
        const map = {};
        matrixBody.querySelectorAll('tr[data-color-id]').forEach(function (row) {
            const key = row.dataset.colorId + '-' + row.dataset.sizeId;
            map[key] = {
                sku:        row.querySelector('[data-field="sku"]')?.value || '',
                cost_price: row.querySelector('[data-field="cost_price"]')?.value || '',
                sale_price: row.querySelector('[data-field="sale_price"]')?.value || '',
                stock:      row.querySelector('[data-field="stock"]')?.value || '',
            };
        });
        return map;
    }

    function rebuildMatrix() {
        const colors   = getSelectedColors();
        const sizes    = getSelectedSizes();
        const previous = readCurrentValues();
        const productName = document.getElementById('name')?.value || '';
        const namePrefix   = slugify(productName);
        const baseCost = document.getElementById('cost_price')?.value || '';
        const baseSale = computeBaseSalePrice();

        matrixBody.innerHTML = '';

        const pricingWrapper = document.getElementById('generalPricingWrapper');
        if (colors.length === 0 || sizes.length === 0) {
            matrixStep.style.display = 'none'; // Ẩn bước 3 khi chưa chọn màu/size
            matrixWrap.classList.add('is-empty');
            if (pricingWrapper) {
                pricingWrapper.style.display = 'block';
            }
            updateSummary();
            return;
        }

        // Nếu có biến thể -> ẩn khung nhập giá chung phía trên đi cho sạch giao diện
        if (pricingWrapper) {
            pricingWrapper.style.display = 'none';
        }

        matrixWrap.classList.remove('is-empty');

        colors.forEach(function (color) {
            sizes.forEach(function (size) {
                const key = color.id + '-' + size.id;
                const prev = previous[key];
                const fromExisting = (existing[color.id] && existing[color.id][size.id]) || null;
                const data = prev || fromExisting || {};

                const sku        = data.sku        ?? `${namePrefix}-${slugify(color.name)}-${size.name}`;
                const costPrice  = data.cost_price ?? baseCost ?? '';
                const salePrice  = data.sale_price ?? baseSale ?? '';
                const stock      = data.stock      ?? '';

                const tr = document.createElement('tr');
                tr.dataset.colorId = color.id;
                tr.dataset.sizeId  = size.id;
                tr.innerHTML = `
                    <td>
                        <div class="vm-matrix-combo">
                            <span class="vm-matrix-combo-dot" style="background:${color.hex};"></span>
                            ${esc(color.name)} - ${esc(size.name)}
                        </div>
                    </td>
                    <td>
                        <input type="text" class="vm-matrix-input" data-field="sku"
                               name="variants[${color.id}][${size.id}][sku]" value="${esc(sku)}">
                    </td>
                    <td>
                        <input type="number" min="0" step="1000" class="vm-matrix-input" data-field="cost_price"
                               name="variants[${color.id}][${size.id}][cost_price]" value="${esc(costPrice)}" placeholder="0">
                    </td>
                    <td>
                        <input type="number" min="0" step="1000" class="vm-matrix-input" data-field="sale_price"
                               name="variants[${color.id}][${size.id}][sale_price]" value="${esc(salePrice)}" placeholder="0">
                    </td>
                    <td>
                        <input type="number" min="0" step="1" class="vm-matrix-input" data-field="stock"
                               name="variants[${color.id}][${size.id}][stock]" value="${esc(stock)}" placeholder="0">
                    </td>
                `;
                matrixBody.appendChild(tr);
            });
        });

        matrixStep.style.display = '';
        updateSummary();
    }

    function computeBaseSalePrice() {
        const price    = parseFloat(document.getElementById('price')?.value || '0');
        const discount = parseFloat(document.getElementById('discount')?.value || '0');
        if (!price) return '';
        const finalPrice = price * (100 - discount) / 100;
        return Math.round(finalPrice);
    }

    function updateSummary() {
        const rows = matrixBody.querySelectorAll('tr[data-color-id]');
        let totalStock = 0;
        rows.forEach(row => {
            totalStock += parseInt(row.querySelector('[data-field="stock"]')?.value || '0', 10) || 0;
        });
        if (rows.length > 0) {
            summary.style.display = '';
            summary.textContent   = `${rows.length} biến thể · ${totalStock.toLocaleString('vi-VN')} sp`;
        } else {
            summary.style.display = 'none';
        }
    }

    matrixBody.addEventListener('input', function (e) {
        if (e.target.matches('[data-field="stock"]')) updateSummary();
    });

    /* ═══════════════════════════════════════════
       BULK APPLY BAR
    ═══════════════════════════════════════════ */
    document.getElementById('vmBulkApplyBtn')?.addEventListener('click', function () {
        const cost  = document.getElementById('vmBulkCost').value;
        const sale  = document.getElementById('vmBulkSale').value;
        const stock = document.getElementById('vmBulkStock').value;

        matrixBody.querySelectorAll('tr[data-color-id]').forEach(function (row) {
            if (cost  !== '') row.querySelector('[data-field="cost_price"]').value = cost;
            if (sale  !== '') row.querySelector('[data-field="sale_price"]').value = sale;
            if (stock !== '') row.querySelector('[data-field="stock"]').value = stock;
        });
        updateSummary();
    });

    /* ═══════════════════════════════════════════
       INITIALIZE (edit mode: pre-check + build)
    ═══════════════════════════════════════════ */
    applySizeSystemVisibility('letters');

    const existingColorIds = Object.keys(existing).map(Number);
    if (existingColorIds.length) {
        /* pre-check colors */
        existingColorIds.forEach(cid => {
            const chip = colorGrid.querySelector(`.vm-color-chip[data-color-id="${cid}"]`);
            if (chip) { chip.classList.add('is-checked'); chip.querySelector('.vm-color-cb').checked = true; }
        });

        /* pre-check sizes referenced by any color */
        const sizeIds = new Set();
        Object.values(existing).forEach(sizesObj => Object.keys(sizesObj).forEach(sid => sizeIds.add(Number(sid))));
        sizeIds.forEach(sid => {
            const chip = sizePickGrid.querySelector(`.vm-size-pick-chip .vm-size-pick-cb[data-size-id="${sid}"]`)?.closest('.vm-size-pick-chip');
            if (chip) { chip.classList.add('is-checked'); chip.querySelector('.vm-size-pick-cb').checked = true; }
        });

        rebuildMatrix();
    } else {
        matrixWrap?.classList.add('is-empty');
    }
})();
</script>
