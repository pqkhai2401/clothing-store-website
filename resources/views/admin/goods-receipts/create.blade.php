@extends('layouts.admin')

@section('title', 'Tạo phiếu nhập kho')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-header-title { font-size: 25px; font-weight: 800; color: #000 !important; margin-bottom: 4px; }
    .gr-header-desc  { color: #64748b; font-size: 14px; margin: 0; }

    /* ── Variant picker ── */
    .gr-picker-wrap { position: relative; }
    .gr-picker-input {
        width: 100%; height: 42px; border: 1.5px solid #d1d5db; border-radius: 8px;
        padding: 0 14px; font-size: 14px; outline: none;
    }
    .gr-picker-input:focus { border-color: #174761; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
    .gr-picker-panel {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.12); max-height: 340px; overflow-y: auto;
        z-index: 40;
    }
    .gr-picker-item {
        display: flex; align-items: center; gap: 10px; padding: 9px 14px;
        cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .1s;
    }
    .gr-picker-item:last-child { border-bottom: 0; }
    .gr-picker-item:hover, .gr-picker-item.is-active { background: #f9fafb; }
    .gr-picker-thumb {
        width: 38px; height: 38px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6;
    }
    .gr-picker-info { flex: 1; min-width: 0; }
    .gr-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .gr-picker-meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    .gr-picker-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); }
    .gr-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
    .gr-picker-empty { padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }

    /* ── Items table ── */
    .gr-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; }
    .gr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 720px; }
    .gr-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .gr-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .gr-table tbody tr:last-child td { border-bottom: 0; }
    .gr-row-product { display: flex; align-items: center; gap: 10px; min-width: 220px; }
    .gr-row-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .gr-row-name { font-size: 13px; font-weight: 700; color: #111827; }
    .gr-row-sub { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    .gr-row-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; }
    .gr-num-input {
        width: 100%; min-width: 90px; height: 34px; border: 1.5px solid #d1d5db; border-radius: 6px;
        padding: 0 8px; font-size: 13px; outline: none; box-sizing: border-box;
    }
    .gr-num-input:focus { border-color: #174761; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
    .gr-row-total { font-weight: 700; color: #111827; white-space: nowrap; }
    .gr-row-remove {
        width: 28px; height: 28px; border-radius: 50%; border: 0; background: #f3f4f6; color: #6b7280;
        display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .15s, color .15s;
    }
    .gr-row-remove:hover { background: #fee2e2; color: #dc2626; }
    .gr-table-empty { padding: 28px 16px; text-align: center; color: #9ca3af; font-size: 13px; }
    .gr-table-wrap.is-empty .gr-table { display: none; }
    .gr-table-wrap:not(.is-empty) .gr-table-empty { display: none; }

    /* ── Summary footer ── */
    .gr-summary-bar {
        display: flex; align-items: center; justify-content: flex-end; gap: 10px;
        margin-top: 14px; padding: 14px 16px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
    }
    .gr-summary-label { font-size: 14px; color: #374151; font-weight: 600; }
    .gr-summary-value { font-size: 20px; font-weight: 800; color: #174761; }

    /* ── Status badges ── */
    .gr-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }

    /* ── Sticky action bar ── */
    .gr-sticky-actions {
        position: sticky; bottom: 16px; display: flex; gap: 10px; justify-content: flex-end;
        background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08); z-index: 20; margin-top: 20px;
    }
    .gr-sticky-actions .btn { min-height: 42px; min-width: 160px; }
    @media (max-width: 768px) {
        .gr-sticky-actions { flex-direction: column; }
        .gr-sticky-actions .btn { width: 100%; }
    }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="gr-header-title">Tạo phiếu nhập kho</h1>
            <p class="gr-header-desc">Chọn nhà cung cấp và thêm sản phẩm cần nhập kho.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.goods-receipts.store') }}" id="goodsReceiptForm">
        @csrf
        <input type="hidden" name="action" id="grActionInput" value="draft">

        <div class="row g-4">
            {{-- ── Thông tin phiếu ── --}}
            <div class="col-lg-4">
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Thông tin phiếu nhập</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="edit-field">
                            <label>Trạng thái</label>
                            <div><span class="gr-badge gr-badge--draft">Nháp</span></div>
                            <div class="form-text">Phiếu sẽ ở trạng thái Nháp cho đến khi bạn bấm "Hoàn tất nhập kho".</div>
                        </div>

                        <div class="edit-field">
                            <label for="supplier_id">Nhà cung cấp <span class="text-danger">*</span></label>
                            <select id="supplier_id" name="supplier_id"
                                class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="">— Chọn nhà cung cấp —</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if($suppliers->isEmpty())
                                <div class="form-text text-danger">
                                    Chưa có nhà cung cấp nào. <a href="{{ route('admin.suppliers.list') }}">Thêm nhà cung cấp</a>
                                </div>
                            @endif
                        </div>

                        <div class="edit-field mb-0">
                            <label for="note">Ghi chú</label>
                            <textarea id="note" name="note" rows="4"
                                class="form-control @error('note') is-invalid @enderror"
                                placeholder="Ghi chú cho phiếu nhập kho...">{{ old('note') }}</textarea>
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Bảng chọn sản phẩm ── --}}
            <div class="col-lg-8">
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Sản phẩm nhập kho</span>
                    </div>
                    <div class="card-body p-4">

                        <div class="gr-picker-wrap">
                            <input type="text" class="gr-picker-input" id="grPickerInput"
                                placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off">
                            <div class="gr-picker-panel" id="grPickerPanel" hidden></div>
                        </div>
                        @error('items') <div class="invalid-feedback d-block mt-2">{{ $message }}</div> @enderror

                        <div class="gr-table-wrap is-empty" id="grTableWrap">
                            <table class="gr-table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th style="width:110px;">Tồn kho</th>
                                        <th style="width:110px;">Số lượng nhập</th>
                                        <th style="width:140px;">Giá vốn thực tế (₫)</th>
                                        <th style="width:130px;">Thành tiền</th>
                                        <th style="width:44px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="grTableBody"></tbody>
                            </table>
                            <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                        </div>

                        <div class="gr-summary-bar">
                            <span class="gr-summary-label">Tổng giá trị phiếu nhập:</span>
                            <span class="gr-summary-value" id="grTotalAmount">0đ</span>
                        </div>

                    </div>
                </div>

                <div class="gr-sticky-actions">
                    <a href="{{ route('admin.goods-receipts.list') }}" class="btn btn-light border fw-bold">
                        <i class="fa-solid fa-xmark me-1"></i> Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-outline-dark fw-bold" data-gr-action="draft">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
                    </button>
                    <button type="submit" class="btn btn-primary fw-bold" data-gr-action="complete">
                        <i class="fa-solid fa-check me-1"></i> Hoàn tất nhập kho
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

    const pickerInput = document.getElementById('grPickerInput');
    const pickerPanel = document.getElementById('grPickerPanel');
    const tableWrap    = document.getElementById('grTableWrap');
    const tableBody    = document.getElementById('grTableBody');
    const totalEl      = document.getElementById('grTotalAmount');
    const form         = document.getElementById('goodsReceiptForm');
    const actionInput  = document.getElementById('grActionInput');

    let selected = {}; // variant_id -> { variant, quantity, cost_price }
    let rowIndex = 0;

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
            pickerPanel.innerHTML = '<div class="gr-picker-empty">Không tìm thấy sản phẩm phù hợp.</div>';
            pickerPanel.hidden = false;
            return;
        }
        pickerPanel.innerHTML = list.map(v => `
            <div class="gr-picker-item" data-variant-id="${v.id}">
                <img class="gr-picker-thumb" src="${resolveImageUrl(v.thumbnail)}" alt="">
                <div class="gr-picker-info">
                    <div class="gr-picker-name">${esc(v.product_name)}</div>
                    <div class="gr-picker-meta">
                        <span class="gr-picker-dot" style="background:${esc(v.color_hex || '#ccc')};"></span>
                        ${esc(v.color_name)} · ${esc(v.size_name)} · SKU: ${esc(v.sku)}
                    </div>
                </div>
                <span class="gr-picker-stock">Tồn: ${v.stock}</span>
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
        if (!e.target.closest('.gr-picker-wrap')) pickerPanel.hidden = true;
    });

    pickerPanel.addEventListener('click', function (e) {
        const item = e.target.closest('.gr-picker-item');
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

        selected[variantId] = { variant, quantity: 1, cost_price: variant.cost_price || 0 };
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

        ids.forEach(function (variantId, idx) {
            const { variant, quantity, cost_price } = selected[variantId];
            const lineTotal = quantity * cost_price;

            const tr = document.createElement('tr');
            tr.dataset.variantId = variantId;
            tr.innerHTML = `
                <td>
                    <div class="gr-row-product">
                        <img class="gr-row-thumb" src="${resolveImageUrl(variant.thumbnail)}" alt="">
                        <div>
                            <div class="gr-row-name">${esc(variant.product_name)}</div>
                            <div class="gr-row-sub">
                                <span class="gr-row-dot" style="background:${esc(variant.color_hex || '#ccc')};"></span>
                                ${esc(variant.color_name)} · ${esc(variant.size_name)} · ${esc(variant.sku)}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${idx}][product_variant_id]" value="${variantId}">
                </td>
                <td>${variant.stock}</td>
                <td>
                    <input type="number" min="1" step="1" class="gr-num-input" data-field="quantity"
                        name="items[${idx}][quantity]" value="${quantity}">
                </td>
                <td>
                    <input type="number" min="0" step="1000" class="gr-num-input" data-field="cost_price"
                        name="items[${idx}][cost_price]" value="${cost_price}">
                </td>
                <td class="gr-row-total" data-field="line-total">${money(lineTotal)}</td>
                <td>
                    <button type="button" class="gr-row-remove" data-remove-variant="${variantId}" title="Xóa">
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
            selected[variantId].quantity = Math.max(1, parseInt(e.target.value, 10) || 1);
        }
        if (e.target.matches('[data-field="cost_price"]')) {
            selected[variantId].cost_price = Math.max(0, parseFloat(e.target.value) || 0);
        }

        const lineTotal = selected[variantId].quantity * selected[variantId].cost_price;
        row.querySelector('[data-field="line-total"]').textContent = money(lineTotal);
        updateTotal();
    });

    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-variant]');
        if (!btn) return;
        removeVariant(btn.dataset.removeVariant);
    });

    function updateTotal() {
        const total = Object.values(selected).reduce((sum, item) => sum + item.quantity * item.cost_price, 0);
        totalEl.textContent = money(total);
    }

    /* ── Submit action (draft vs complete) ── */
    document.querySelectorAll('[data-gr-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            actionInput.value = this.dataset.grAction;
        });
    });

    form.addEventListener('submit', function (e) {
        if (Object.keys(selected).length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập kho.');
        }
    });
})();
</script>
@endpush
