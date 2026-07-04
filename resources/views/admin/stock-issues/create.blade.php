@extends('layouts.admin')

@section('title', 'Tạo phiếu xuất kho')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .si-header-title { font-size: 25px; font-weight: 800; color: #000 !important; margin-bottom: 4px; }
    .si-header-desc  { color: #64748b; font-size: 14px; margin: 0; }

    /* ── Variant picker ── */
    .si-picker-wrap { position: relative; }
    .si-picker-input {
        width: 100%; height: 42px; border: 1.5px solid #d1d5db; border-radius: 8px;
        padding: 0 14px; font-size: 14px; outline: none;
    }
    .si-picker-input:focus { border-color: #0f9d58; box-shadow: 0 0 0 3px rgba(15,157,88,.08); }
    .si-picker-panel {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.12); max-height: 340px; overflow-y: auto;
        z-index: 40;
    }
    .si-picker-item {
        display: flex; align-items: center; gap: 10px; padding: 9px 14px;
        cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .1s;
    }
    .si-picker-item:last-child { border-bottom: 0; }
    .si-picker-item:hover, .si-picker-item.is-active { background: #f9fafb; }
    .si-picker-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .si-picker-info { flex: 1; min-width: 0; }
    .si-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .si-picker-meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    .si-picker-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); }
    .si-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
    .si-picker-empty { padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }

    /* ── Items table ── */
    .si-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; }
    .si-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 720px; }
    .si-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .si-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .si-table tbody tr:last-child td { border-bottom: 0; }
    .si-row-product { display: flex; align-items: center; gap: 10px; min-width: 220px; }
    .si-row-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .si-row-name { font-size: 13px; font-weight: 700; color: #111827; }
    .si-row-sub { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    .si-row-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; }
    .si-num-input {
        width: 100%; min-width: 90px; height: 34px; border: 1.5px solid #d1d5db; border-radius: 6px;
        padding: 0 8px; font-size: 13px; outline: none; box-sizing: border-box;
    }
    .si-num-input:focus { border-color: #0f9d58; box-shadow: 0 0 0 3px rgba(15,157,88,.08); }
    .si-num-input.is-invalid { border-color: #dc2626; }
    .si-row-total { font-weight: 700; color: #111827; white-space: nowrap; }
    .si-row-remove {
        width: 28px; height: 28px; border-radius: 50%; border: 0; background: #f3f4f6; color: #6b7280;
        display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .15s, color .15s;
    }
    .si-row-remove:hover { background: #fee2e2; color: #dc2626; }
    .si-table-empty { padding: 28px 16px; text-align: center; color: #9ca3af; font-size: 13px; }
    .si-table-wrap.is-empty .si-table { display: none; }
    .si-table-wrap:not(.is-empty) .si-table-empty { display: none; }

    /* ── Summary footer ── */
    .si-summary-bar {
        display: flex; align-items: center; justify-content: flex-end; gap: 10px;
        margin-top: 14px; padding: 14px 16px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
    }
    .si-summary-label { font-size: 14px; color: #374151; font-weight: 600; }
    .si-summary-value { font-size: 20px; font-weight: 800; color: #0f9d58; }

    .gr-badge--draft { background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-btn-emerald { background: #0f9d58; border-color: #0f9d58; color: #fff; }
    .gr-btn-emerald:hover { background: #0c7a45; border-color: #0c7a45; color: #fff; }

    /* ── Sticky action bar ── */
    .si-sticky-actions {
        position: sticky; bottom: 16px; display: flex; gap: 10px; justify-content: flex-end;
        background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08); z-index: 20; margin-top: 20px;
    }
    .si-sticky-actions .btn { min-height: 42px; min-width: 160px; }
    @media (max-width: 768px) {
        .si-sticky-actions { flex-direction: column; }
        .si-sticky-actions .btn { width: 100%; }
    }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="si-header-title">Tạo phiếu xuất kho</h1>
            <p class="si-header-desc">Chọn sản phẩm cần xuất kho và lý do xuất.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.stock-issues.store') }}" id="stockIssueForm">
        @csrf
        <input type="hidden" name="action" id="siActionInput" value="draft">

        <div class="row g-4">
            {{-- ── Thông tin phiếu ── --}}
            <div class="col-lg-4">
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Thông tin phiếu xuất</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="edit-field">
                            <label>Trạng thái</label>
                            <div><span class="gr-badge--draft">Nháp</span></div>
                            <div class="form-text">Phiếu sẽ ở trạng thái Nháp cho đến khi bạn bấm "Xuất kho ngay".</div>
                        </div>

                        <div class="edit-field mb-0">
                            <label for="reason">Lý do xuất kho <span class="text-danger">*</span></label>
                            <input type="text" id="reason" name="reason"
                                class="form-control @error('reason') is-invalid @enderror"
                                list="reasonSuggestions"
                                value="{{ old('reason') }}"
                                placeholder="Ví dụ: Xuất trả NCC - Hàng lỗi" required>
                            <datalist id="reasonSuggestions">
                                <option value="Xuất trả NCC - Hàng lỗi">
                                <option value="Xuất mẫu trưng bày">
                                <option value="Xuất hủy - Hết hạn">
                                <option value="Xuất bán buôn">
                            </datalist>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Bảng chọn sản phẩm ── --}}
            <div class="col-lg-8">
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Sản phẩm xuất kho</span>
                    </div>
                    <div class="card-body p-4">

                        <div class="si-picker-wrap">
                            <input type="text" class="si-picker-input" id="siPickerInput"
                                placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off">
                            <div class="si-picker-panel" id="siPickerPanel" hidden></div>
                        </div>
                        @error('items') <div class="invalid-feedback d-block mt-2">{{ $message }}</div> @enderror

                        <div class="si-table-wrap is-empty" id="siTableWrap">
                            <table class="si-table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th style="width:110px;">Tồn kho</th>
                                        <th style="width:110px;">Số lượng xuất</th>
                                        <th style="width:140px;">Đơn giá xuất (₫)</th>
                                        <th style="width:130px;">Thành tiền</th>
                                        <th style="width:44px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="siTableBody"></tbody>
                            </table>
                            <div class="si-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                        </div>

                        <div class="si-summary-bar">
                            <span class="si-summary-label">Tổng giá trị xuất kho:</span>
                            <span class="si-summary-value" id="siTotalAmount">0đ</span>
                        </div>

                    </div>
                </div>

                <div class="si-sticky-actions">
                    <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}" class="btn btn-light border fw-bold">
                        <i class="fa-solid fa-xmark me-1"></i> Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-outline-dark fw-bold" data-si-action="draft">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
                    </button>
                    <button type="submit" class="btn gr-btn-emerald fw-bold" data-si-action="issue">
                        <i class="fa-solid fa-check me-1"></i> Xuất kho ngay
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script>
(function () {
    const variants = @json($variants);

    const pickerInput = document.getElementById('siPickerInput');
    const pickerPanel = document.getElementById('siPickerPanel');
    const tableWrap    = document.getElementById('siTableWrap');
    const tableBody    = document.getElementById('siTableBody');
    const totalEl      = document.getElementById('siTotalAmount');
    const form         = document.getElementById('stockIssueForm');
    const actionInput  = document.getElementById('siActionInput');

    let selected = {}; // variant_id -> { variant, quantity, unit_price }

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function money(num) {
        return Math.round(num || 0).toLocaleString('vi-VN') + 'đ';
    }

    function resolveImageUrl(path) {
        if (!path) return 'https://placehold.co/80x80?text=No+Image';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    /* ── Picker: search & dropdown ── */
    function filterVariants(query) {
        query = query.trim().toLowerCase();
        if (!query) return [];
        return variants.filter(v => {
            const haystack = `${v.product_name} ${v.sku} ${v.color_name} ${v.size_name}`.toLowerCase();
            return haystack.includes(query);
        }).slice(0, 30);
    }

    function renderPicker(list) {
        if (list.length === 0) {
            pickerPanel.innerHTML = '<div class="si-picker-empty">Không tìm thấy sản phẩm phù hợp hoặc đã hết hàng.</div>';
            pickerPanel.hidden = false;
            return;
        }
        pickerPanel.innerHTML = list.map(v => `
            <div class="si-picker-item" data-variant-id="${v.id}">
                <img class="si-picker-thumb" src="${resolveImageUrl(v.thumbnail)}" alt="">
                <div class="si-picker-info">
                    <div class="si-picker-name">${esc(v.product_name)}</div>
                    <div class="si-picker-meta">
                        <span class="si-picker-dot" style="background:${esc(v.color_hex || '#ccc')};"></span>
                        ${esc(v.color_name)} · ${esc(v.size_name)} · SKU: ${esc(v.sku)}
                    </div>
                </div>
                <span class="si-picker-stock">Tồn: ${v.stock}</span>
            </div>
        `).join('');
        pickerPanel.hidden = false;
    }

    pickerInput.addEventListener('input', function () {
        renderPicker(filterVariants(this.value));
    });

    pickerInput.addEventListener('focus', function () {
        if (this.value.trim()) renderPicker(filterVariants(this.value));
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.si-picker-wrap')) pickerPanel.hidden = true;
    });

    pickerPanel.addEventListener('click', function (e) {
        const item = e.target.closest('.si-picker-item');
        if (!item || !item.dataset.variantId) return;
        addVariant(item.dataset.variantId);
        pickerInput.value = '';
        pickerPanel.hidden = true;
        pickerInput.focus();
    });

    /* ── Item table management ── */
    function addVariant(variantId) {
        const variant = variants.find(v => String(v.id) === String(variantId));
        if (!variant) return;

        if (selected[variantId]) {
            const row = tableBody.querySelector(`tr[data-variant-id="${variantId}"]`);
            row?.querySelector('[data-field="quantity"]')?.focus();
            return;
        }

        selected[variantId] = { variant, quantity: 1, unit_price: variant.unit_price || 0 };
        renderTable();
    }

    function removeVariant(variantId) {
        delete selected[variantId];
        renderTable();
    }

    function renderTable() {
        const ids = Object.keys(selected);
        tableWrap.classList.toggle('is-empty', ids.length === 0);
        tableBody.innerHTML = '';

        ids.forEach(function (variantId) {
            const { variant, quantity, unit_price } = selected[variantId];
            const lineTotal = quantity * unit_price;
            const overStock = quantity > variant.stock;

            const tr = document.createElement('tr');
            tr.dataset.variantId = variantId;
            tr.innerHTML = `
                <td>
                    <div class="si-row-product">
                        <img class="si-row-thumb" src="${resolveImageUrl(variant.thumbnail)}" alt="">
                        <div>
                            <div class="si-row-name">${esc(variant.product_name)}</div>
                            <div class="si-row-sub">
                                <span class="si-row-dot" style="background:${esc(variant.color_hex || '#ccc')};"></span>
                                ${esc(variant.color_name)} · ${esc(variant.size_name)} · ${esc(variant.sku)}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${variantId}][product_variant_id]" value="${variantId}">
                </td>
                <td>${variant.stock}</td>
                <td>
                    <input type="number" min="1" max="${variant.stock}" step="1" class="si-num-input ${overStock ? 'is-invalid' : ''}" data-field="quantity"
                        name="items[${variantId}][quantity]" value="${quantity}">
                </td>
                <td>
                    <input type="number" min="0" step="1000" class="si-num-input" data-field="unit_price"
                        name="items[${variantId}][unit_price]" value="${unit_price}">
                </td>
                <td class="si-row-total" data-field="line-total">${money(lineTotal)}</td>
                <td>
                    <button type="button" class="si-row-remove" data-remove-variant="${variantId}" title="Xóa">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        updateTotal();
    }

    tableBody.addEventListener('input', function (e) {
        const row = e.target.closest('tr[data-variant-id]');
        if (!row) return;
        const variantId = row.dataset.variantId;
        if (!selected[variantId]) return;

        if (e.target.matches('[data-field="quantity"]')) {
            const stock = selected[variantId].variant.stock;
            let qty = Math.max(1, parseInt(e.target.value, 10) || 1);
            selected[variantId].quantity = qty;
            e.target.classList.toggle('is-invalid', qty > stock);
        }
        if (e.target.matches('[data-field="unit_price"]')) {
            selected[variantId].unit_price = Math.max(0, parseFloat(e.target.value) || 0);
        }

        const lineTotal = selected[variantId].quantity * selected[variantId].unit_price;
        row.querySelector('[data-field="line-total"]').textContent = money(lineTotal);
        updateTotal();
    });

    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-variant]');
        if (!btn) return;
        removeVariant(btn.dataset.removeVariant);
    });

    function updateTotal() {
        const total = Object.values(selected).reduce((sum, item) => sum + item.quantity * item.unit_price, 0);
        totalEl.textContent = money(total);
    }

    /* ── Submit action (draft vs issue) ── */
    document.querySelectorAll('[data-si-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            actionInput.value = this.dataset.siAction;
        });
    });

    form.addEventListener('submit', function (e) {
        if (Object.keys(selected).length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một sản phẩm vào phiếu xuất kho.');
            return;
        }

        const overStockItem = Object.values(selected).find(item => item.quantity > item.variant.stock);
        if (overStockItem) {
            e.preventDefault();
            alert(`Số lượng xuất "${overStockItem.variant.product_name}" vượt quá tồn kho hiện có (${overStockItem.variant.stock}).`);
        }
    });
})();
</script>
@endpush
