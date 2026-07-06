{{--
    Offcanvas tạo phiếu nhập kho mới.
    Variables expected:
    - $suppliers: danh sách nhà cung cấp đang hoạt động.
    - $variants: danh sách biến thể sản phẩm đã map sẵn field hiển thị.
--}}

<div class="offcanvas offcanvas-end gr-offcanvas" tabindex="-1" id="goodsReceiptOffcanvas" aria-labelledby="goodsReceiptOffcanvasLabel">
    <form method="POST" action="{{ route('admin.goods-receipts.store') }}" id="goodsReceiptAjaxForm" class="d-flex flex-column h-100">
        @csrf
        <input type="hidden" name="action" id="grModalActionInput" value="draft">

        <div class="offcanvas-header border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="offcanvas-title mb-0" id="goodsReceiptOffcanvasLabel">Tạo phiếu nhập kho mới</h2>
                    <span class="gr-badge gr-badge--draft" id="grModalStatusBadge">Nháp</span>
                </div>
                <p class="mb-0 text-muted" style="font-size:13px;">Chọn nhà cung cấp và thêm sản phẩm cần nhập kho.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>

        <div class="offcanvas-body flex-grow-1 overflow-auto">
            <div class="card gr-info-card mb-4">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Thông tin phiếu nhập</span>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <span class="gr-inline-label d-block mb-1">Nhà cung cấp <span class="text-danger">*</span></span>
                        <input type="hidden" name="supplier_id" id="grModalSupplierInput" required>
                        <div class="hk-cat-filter gr-supplier-filter" id="grModalSupplierFilter">
                            <button type="button" class="hk-cat-trigger" id="grModalSupplierTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="grModalSupplierLabel">Chọn nhà cung cấp</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="grModalSupplierPanel" hidden>
                                <div class="hk-cat-list" id="grModalSupplierList" role="listbox">
                                    @foreach($suppliers as $supplier)
                                        <button type="button" class="hk-cat-item" data-value="{{ $supplier->id }}" data-label="{{ $supplier->name }}">
                                            {{ $supplier->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="supplier_id"></div>
                        @if($suppliers->isEmpty())
                            <div class="form-text text-danger">
                                Chưa có nhà cung cấp nào. <a href="{{ route('admin.suppliers.list') }}">Thêm nhà cung cấp</a>
                            </div>
                        @endif
                    </div>

                    <div class="edit-field mb-0">
                        <label for="grModalNote">Ghi chú</label>
                        <textarea id="grModalNote" name="note" rows="2" class="form-control"
                            placeholder="Ghi chú nội bộ cho phiếu nhập kho..."></textarea>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="note"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Sản phẩm nhập kho</span>
                </div>
                <div class="card-body p-3">
                    <div class="gr-picker-wrap">
                        <input type="text" class="gr-picker-input" id="grModalPickerInput"
                            placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off">
                        <div class="gr-picker-panel" id="grModalPickerPanel" hidden></div>
                    </div>
                    <div class="invalid-feedback d-block mt-2" data-gr-error="items"></div>

                    <div class="gr-table-wrap is-empty" id="grModalTableWrap">
                        <table class="gr-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="width:90px;">Tồn kho</th>
                                    <th style="width:110px;">SL nhập</th>
                                    <th style="width:140px;">Giá vốn (₫)</th>
                                    <th style="width:130px;">Thành tiền</th>
                                    <th style="width:44px;"></th>
                                </tr>
                            </thead>
                            <tbody id="grModalTableBody"></tbody>
                        </table>
                        <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                    </div>

                    <div class="gr-summary-bar">
                        <span class="gr-summary-label">Tổng giá trị phiếu nhập:</span>
                        <span class="gr-summary-value" id="grModalTotalAmount">0đ</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas">Hủy bỏ</button>
            <button type="submit" class="btn btn-outline-dark fw-semibold" data-gr-modal-action="draft">
                <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
            </button>
            <button type="submit" class="btn gr-btn-emerald fw-semibold" data-gr-modal-action="complete">
                <i class="fa-solid fa-check me-1"></i> Hoàn tất nhập kho
            </button>
        </div>
    </form>
</div>

@once
@push('styles')
<style>
.gr-offcanvas {
    width: min(900px, 92vw) !important;
    border-top-left-radius: 18px;
    border-bottom-left-radius: 18px;
    overflow: hidden;
    box-shadow: -18px 0 40px rgba(15, 23, 42, 0.18);
}
.gr-info-card { border: 1.5px solid #e5e7eb; border-radius: 12px; background: #f9fafb; }
.gr-inline-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; white-space: nowrap; }
.gr-supplier-filter { width: 100%; }
.gr-supplier-filter .hk-cat-trigger { width: 100%; height: 46px; justify-content: space-between; border-radius: 12px; }
.gr-supplier-filter .hk-cat-panel {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
}
.gr-supplier-filter .hk-cat-list { max-height: 240px; overflow-y: auto; }

.gr-picker-wrap { position: relative; }
.gr-picker-input {
    width: 100%; height: 42px; border: 1.5px solid #d1d5db; border-radius: 8px;
    padding: 0 14px; font-size: 14px; outline: none;
}
.gr-picker-input:focus { border-color: #174761; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
.gr-picker-panel {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.12); max-height: 300px; overflow-y: auto;
    z-index: 1060;
}
.gr-picker-item {
    display: flex; align-items: center; gap: 10px; padding: 9px 14px;
    cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .1s;
}
.gr-picker-item:last-child { border-bottom: 0; }
.gr-picker-item:hover { background: #f9fafb; }
.gr-picker-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
.gr-picker-info { flex: 1; min-width: 0; }
.gr-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gr-picker-meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.gr-picker-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); }
.gr-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.gr-picker-empty { padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }

.gr-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; max-height: 360px; overflow-y: auto; }
.gr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 650px; }
.gr-table thead th {
    background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
    color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    position: sticky; top: 0; z-index: 1;
}
.gr-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.gr-table tbody tr:last-child td { border-bottom: 0; }
.gr-row-product { display: flex; align-items: center; gap: 10px; min-width: 200px; }
.gr-row-thumb { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
.gr-row-name { font-size: 13px; font-weight: 700; color: #111827; }
.gr-row-sub { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.gr-row-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; }
.gr-num-input {
    width: 100%; min-width: 85px; height: 34px; border: 1.5px solid #d1d5db; border-radius: 6px;
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

.gr-summary-bar {
    display: flex; align-items: center; justify-content: flex-end; gap: 10px;
    margin-top: 14px; padding: 14px 16px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
}
.gr-summary-label { font-size: 14px; color: #374151; font-weight: 600; }
.gr-summary-value { font-size: 20px; font-weight: 800; color: #174761; }
</style>
@endpush
@endonce

@once
@push('scripts')
<script>
(function () {
    const variants = @json($variants);
    const offcanvas = document.getElementById('goodsReceiptOffcanvas');
    const form = document.getElementById('goodsReceiptAjaxForm');
    const actionInput = document.getElementById('grModalActionInput');
    const statusBadge = document.getElementById('grModalStatusBadge');
    const supplierInput = document.getElementById('grModalSupplierInput');
    const supplierFilter = document.getElementById('grModalSupplierFilter');
    const supplierTrigger = document.getElementById('grModalSupplierTrigger');
    const supplierPanel = document.getElementById('grModalSupplierPanel');
    const supplierLabel = document.getElementById('grModalSupplierLabel');
    const supplierList = document.getElementById('grModalSupplierList');
    const pickerInput = document.getElementById('grModalPickerInput');
    const pickerPanel = document.getElementById('grModalPickerPanel');
    const tableWrap = document.getElementById('grModalTableWrap');
    const tableBody = document.getElementById('grModalTableBody');
    const totalEl = document.getElementById('grModalTotalAmount');
    const tableArea = document.querySelector('[data-admin-table-area]');
    const STATUS_LABELS = { draft: 'Nháp', complete: 'Hoàn tất nhập kho' };
    let selected = {};
    let order = [];

    if (!offcanvas || !form) return;

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

    function clearErrors() {
        document.querySelectorAll('[data-gr-error]').forEach(el => { el.textContent = ''; });
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const normalized = key.startsWith('items') ? 'items' : key;
            const target = document.querySelector(`[data-gr-error="${normalized}"]`);
            if (!target) return;
            target.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) { alert(message); return; }
        const toast = document.createElement('div');
        toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
        toast.style.pointerEvents = 'auto';
        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">
                    <svg style="flex-shrink:0;display:block;" viewBox="0 0 24 24" width="22" height="22">
                        ${type === 'error'
                            ? '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
                            : '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'}
                    </svg>
                </div>
                <div class="toast-message">${esc(message)}</div>
            </div>
            <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    function setAction(action) {
        actionInput.value = action;
        if (!statusBadge) return;
        statusBadge.textContent = STATUS_LABELS[action] || action;
        statusBadge.classList.toggle('gr-badge--draft', action === 'draft');
        statusBadge.classList.toggle('gr-badge--completed', action === 'complete');
    }

    function closeSupplierDropdown() {
        if (supplierPanel) supplierPanel.hidden = true;
        supplierTrigger?.classList.remove('is-open');
        supplierTrigger?.setAttribute('aria-expanded', 'false');
    }

    supplierTrigger?.addEventListener('click', function () {
        const shouldOpen = supplierPanel.hidden;
        closeSupplierDropdown();
        if (shouldOpen) {
            supplierPanel.hidden = false;
            supplierTrigger.classList.add('is-open');
            supplierTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    supplierList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        supplierInput.value = btn.dataset.value || '';
        supplierLabel.textContent = btn.dataset.label || 'Chọn nhà cung cấp';
        supplierList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeSupplierDropdown();
        clearErrors();
    });

    document.addEventListener('click', function (e) {
        if (supplierFilter && !supplierFilter.contains(e.target)) closeSupplierDropdown();
    });

    function filterVariants(query) {
        query = query.trim().toLowerCase();
        if (!query) return [];
        return variants.filter(v => `${v.product_name} ${v.sku} ${v.color_name} ${v.size_name}`.toLowerCase().includes(query)).slice(0, 30);
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

    pickerInput.addEventListener('input', function () { renderPicker(filterVariants(this.value)); });
    pickerInput.addEventListener('focus', function () { if (this.value.trim()) renderPicker(filterVariants(this.value)); });
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

    pickerInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const query = this.value.trim().toLowerCase();
        if (!query) return;
        const matches = filterVariants(query);
        if (matches.length === 0) return;
        const exact = matches.find(v => String(v.sku).toLowerCase() === query);
        addVariant((exact || matches[0]).id);
        this.value = '';
        pickerPanel.hidden = true;
    });

    function addVariant(variantId) {
        variantId = String(variantId);
        const variant = variants.find(v => String(v.id) === variantId);
        if (!variant) return;

        if (selected[variantId]) {
            selected[variantId].quantity += 1;
        } else {
            selected[variantId] = { variant, quantity: 1, cost_price: variant.cost_price || 0 };
            order.unshift(variantId);
        }
        renderTable();
    }

    function removeVariant(variantId) {
        delete selected[variantId];
        order = order.filter(id => id !== variantId);
        renderTable();
    }

    function renderTable() {
        const ids = order.filter(id => selected[id]);
        tableWrap.classList.toggle('is-empty', ids.length === 0);
        tableBody.innerHTML = '';

        ids.forEach(function (variantId) {
            const item = selected[variantId];
            const { variant, quantity, cost_price } = item;
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
                    <input type="hidden" name="items[${variantId}][product_variant_id]" value="${variantId}">
                </td>
                <td>${variant.stock}</td>
                <td>
                    <input type="number" min="1" step="1" class="gr-num-input" data-field="quantity"
                        name="items[${variantId}][quantity]" value="${quantity}">
                </td>
                <td>
                    <input type="number" min="0" step="1000" class="gr-num-input" data-field="cost_price"
                        name="items[${variantId}][cost_price]" value="${cost_price}">
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
        const item = selected[variantId];
        if (!item) return;

        if (e.target.matches('[data-field="quantity"]')) {
            item.quantity = Math.max(1, parseInt(e.target.value, 10) || 1);
            e.target.value = item.quantity;
        }
        if (e.target.matches('[data-field="cost_price"]')) {
            item.cost_price = Math.max(0, parseFloat(e.target.value) || 0);
        }

        row.querySelector('[data-field="line-total"]').textContent = money(item.quantity * item.cost_price);
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

    async function refreshInboundTable(url) {
        if (!tableArea) return;
        const res = await fetch(url || '{{ route('admin.goods-receipts.list', ['tab' => 'inbound']) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.html) tableArea.innerHTML = data.html;
    }

    document.querySelectorAll('[data-gr-modal-action]').forEach(btn => {
        btn.addEventListener('click', function () { setAction(this.dataset.grModalAction); });
    });

    offcanvas.addEventListener('shown.bs.offcanvas', function () {
        pickerInput?.focus();
    });
    offcanvas.addEventListener('hidden.bs.offcanvas', function () {
        clearErrors();
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        if (Object.keys(selected).length === 0) {
            showErrors({ items: ['Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập kho.'] });
            return;
        }

        if (!supplierInput.value) {
            showErrors({ supplier_id: ['Vui lòng chọn nhà cung cấp.'] });
            return;
        }

        const submitter = e.submitter;
        submitter?.setAttribute('disabled', 'disabled');

        try {
            const res = await fetch('{{ route('admin.goods-receipts.store') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            });

            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                showErrors({ items: ['Phiên làm việc có thể đã hết hạn hoặc máy chủ phản hồi không hợp lệ. Vui lòng tải lại trang (F5) và thử lại.'] });
                return;
            }

            const data = await res.json().catch(() => null);
            if (!res.ok || !data) {
                showErrors((data && data.errors) || { items: [(data && data.message) || 'Không thể tạo phiếu nhập kho.'] });
                return;
            }

            selected = {};
            order = [];
            form.reset();
            supplierInput.value = '';
            supplierLabel.textContent = 'Chọn nhà cung cấp';
            supplierList?.querySelectorAll('.hk-cat-item').forEach(item => item.classList.remove('is-active'));
            setAction('draft');
            renderTable();
            bootstrap.Offcanvas.getOrCreateInstance(offcanvas).hide();
            await refreshInboundTable(data.table_url);
            showToast(data.message || `Tạo phiếu nhập kho "${data.code}" thành công.`);
        } catch (err) {
            showToast('Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng và thử lại.', 'error');
        } finally {
            submitter?.removeAttribute('disabled');
        }
    });
})();
</script>
@endpush
@endonce
