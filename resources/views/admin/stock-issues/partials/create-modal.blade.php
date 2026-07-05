{{--
    Modal tạo phiếu xuất kho mới — Bootstrap thuần (không dùng Tailwind).
    Variables expected: $variants — Collection các biến thể còn tồn kho, đã map sẵn field cần thiết.
--}}

{{-- Add Stock Issue Offcanvas — panel trượt từ bên phải, cao hết màn hình --}}
<div class="offcanvas offcanvas-end si-offcanvas" tabindex="-1" id="stockIssueOffcanvas" aria-labelledby="stockIssueOffcanvasLabel">
    <form method="POST" action="{{ route('admin.stock-issues.store') }}" id="stockIssueAjaxForm" class="d-flex flex-column h-100">
        @csrf
        {{-- Lưu ý: KHÔNG được đặt tên field là "action" — nó sẽ ghi đè thuộc tính gốc form.action của DOM,
             khiến fetch() gửi nhầm URL. Đã cố tình đặt tên khác. --}}
        <input type="hidden" name="submit_action" id="siModalActionInput" value="draft">

        <div class="offcanvas-header border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="offcanvas-title mb-0" id="stockIssueOffcanvasLabel">Tạo phiếu xuất kho mới</h2>
                    <span class="gr-badge gr-badge--draft" id="siModalStatusBadge">Nháp</span>
                </div>
                <p class="mb-0 text-muted" style="font-size:13px;">Chọn sản phẩm cần xuất và điền lý do tương ứng.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>

        <div class="offcanvas-body flex-grow-1 overflow-auto">
                    {{-- Thông tin chung --}}
                    <div class="card si-info-card mb-4">
                        <div class="card-header">
                            <span class="fw-bold" style="font-size:14px;">Thông tin phiếu xuất</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <span class="si-inline-label d-block mb-1">Loại lý do <span class="text-danger">*</span></span>
                                <input type="hidden" name="reason_type" id="reasonTypeInput"
                                    value="{{ old('reason_type', array_key_first(\App\Models\StockIssue::REASON_TYPE_LABELS)) }}" required>
                                <div class="hk-cat-filter si-reason-filter" id="reasonTypeFilter">
                                    <button type="button" class="hk-cat-trigger" id="reasonTypeTrigger">
                                        <span class="hk-cat-trigger-label" id="reasonTypeLabel">
                                            {{ \App\Models\StockIssue::REASON_TYPE_LABELS[old('reason_type', array_key_first(\App\Models\StockIssue::REASON_TYPE_LABELS))] }}
                                        </span>
                                        <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                    </button>
                                    <div class="hk-cat-panel" id="reasonTypePanel" hidden style="width:280px;">
                                        <div class="hk-cat-list" id="reasonTypeList">
                                            @foreach(\App\Models\StockIssue::REASON_TYPE_LABELS as $value => $label)
                                                <button type="button" class="hk-cat-item {{ old('reason_type', array_key_first(\App\Models\StockIssue::REASON_TYPE_LABELS)) === $value ? 'is-active' : '' }}"
                                                    data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block mt-1" data-si-error="reason_type"></div>
                            </div>

                            <div class="edit-field mb-0">
                                <label for="siModalNote">Ghi chú chi tiết</label>
                                <textarea id="siModalNote" name="note" rows="2" class="form-control"
                                    placeholder="Thêm ghi chú nội bộ cho phiếu xuất này...">{{ old('note') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Sản phẩm xuất kho --}}
                    <div class="card">
                        <div class="card-header">
                            <span class="fw-bold" style="font-size:14px;">Sản phẩm xuất kho</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="gr-picker-wrap">
                                <input type="text" class="gr-picker-input" id="siModalPickerInput"
                                    placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off">
                                <div class="gr-picker-panel" id="siModalPickerPanel" hidden></div>
                            </div>
                            <div class="invalid-feedback d-block mt-2" data-si-error="items"></div>

                            <div class="gr-table-wrap is-empty" id="siModalTableWrap">
                                <table class="gr-table">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th style="width:100px;">Tồn kho</th>
                                            <th style="width:100px;">Số lượng</th>
                                            <th style="width:130px;">Đơn giá (₫)</th>
                                            <th style="width:130px;">Thành tiền</th>
                                            <th style="width:44px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="siModalTableBody"></tbody>
                                </table>
                                <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                            </div>

                            <div class="gr-summary-bar">
                                <span class="gr-summary-label">Tổng cộng đơn xuất:</span>
                                <span class="gr-summary-value" id="siModalTotalAmount">0đ</span>
                            </div>
                        </div>
                    </div>
                </div>

        <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas">Hủy bỏ</button>
            <button type="submit" class="btn btn-outline-dark fw-semibold" data-si-modal-action="draft">
                <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
            </button>
            <button type="submit" class="btn gr-btn-emerald fw-semibold" id="siModalIssueBtn" data-si-modal-action="issue">
                <i class="fa-solid fa-check me-1"></i> Xuất kho ngay
            </button>
        </div>
    </form>
</div>

@once
@push('styles')
<style>
.si-offcanvas { width: min(900px, 92vw) !important; }
.si-info-card { border: 1.5px solid #e5e7eb; border-radius: 12px; background: #f9fafb; }
.si-inline-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; white-space: nowrap; }
.si-reason-filter { width: 100%; max-width: 320px; }
.gr-row-status-filter { width: auto; flex: none; }
.gr-row-status-filter .hk-cat-trigger { width: auto; white-space: nowrap; min-height: 30px; padding: 0 10px; font-size: 11px; }
.price-input.is-locked { background: #f3f4f6 !important; color: #9ca3af !important; cursor: not-allowed; }
.gr-num-input.border-danger { border-color: #dc2626 !important; }
.si-stock-warning { font-size: 11px; font-weight: 600; color: #dc2626; margin-top: 4px; }

/* ── Variant picker (dùng chung style với trang tạo phiếu nhập kho) ── */
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
.gr-picker-item:hover, .gr-picker-item.is-active { background: #f9fafb; }
.gr-picker-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
.gr-picker-info { flex: 1; min-width: 0; }
.gr-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gr-picker-meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.gr-picker-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); }
.gr-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.gr-picker-empty { padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }

/* ── Items table ── */
.gr-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; max-height: 360px; overflow-y: auto; }
.gr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 640px; }
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

/* ── Summary footer ── */
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
    const modal = document.getElementById('stockIssueOffcanvas');
    const form = document.getElementById('stockIssueAjaxForm');
    const actionInput = document.getElementById('siModalActionInput');
    const pickerInput = document.getElementById('siModalPickerInput');
    const pickerPanel = document.getElementById('siModalPickerPanel');
    const tableWrap = document.getElementById('siModalTableWrap');
    const tableBody = document.getElementById('siModalTableBody');
    const totalEl = document.getElementById('siModalTotalAmount');
    const issueBtn = document.getElementById('siModalIssueBtn');
    const reasonTypeSelect = document.getElementById('reasonTypeInput');
    const tableArea = document.querySelector('[data-admin-table-area]');
    const REASON_TYPES_ALLOWING_PRICE_EDIT = ['XUAT_TRA_NCC'];
    const STATUS_LABELS = { draft: 'Nháp', issue: 'Xuất kho ngay' };
    let selected = {};
    let order = []; // variantId (string) — mới thêm sẽ nằm ở đầu bảng

    if (!modal || !form) return;

    function priceEditAllowed() {
        return REASON_TYPES_ALLOWING_PRICE_EDIT.includes(reasonTypeSelect?.value);
    }

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
        document.querySelectorAll('[data-si-error]').forEach(el => { el.textContent = ''; });
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const normalized = key.startsWith('items') ? 'items' : key;
            const target = document.querySelector(`[data-si-error="${normalized}"]`);
            if (!target) return;
            target.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    // ── Toast thông báo (đồng bộ với component thông báo chung của trang) ──
    const TOAST_ICONS = {
        success: '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        error:   '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
    };
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
                        ${TOAST_ICONS[type] ?? TOAST_ICONS.success}
                    </svg>
                </div>
                <div class="toast-message">${message}</div>
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

    /* ── Dropdown "Loại lý do" ── */
    const reasonTypeFilter  = document.getElementById('reasonTypeFilter');
    const reasonTypeTrigger = document.getElementById('reasonTypeTrigger');
    const reasonTypePanel   = document.getElementById('reasonTypePanel');
    const reasonTypeLabelEl = document.getElementById('reasonTypeLabel');
    const reasonTypeList    = document.getElementById('reasonTypeList');
    const statusBadge       = document.getElementById('siModalStatusBadge');

    reasonTypeTrigger?.addEventListener('click', function () {
        const isHidden = reasonTypePanel.hidden;
        closeAllDropdowns();
        if (isHidden) { reasonTypePanel.hidden = false; reasonTypeTrigger.classList.add('is-open'); }
    });
    reasonTypeList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        reasonTypeSelect.value = btn.dataset.value;
        reasonTypeLabelEl.textContent = btn.dataset.label;
        reasonTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllDropdowns();
        handleReasonChange();
    });

    function closeAllDropdowns() {
        if (reasonTypePanel) reasonTypePanel.hidden = true;
        reasonTypeTrigger?.classList.remove('is-open');
    }

    document.addEventListener('click', function (e) {
        if (reasonTypeFilter && !reasonTypeFilter.contains(e.target)) { reasonTypePanel.hidden = true; reasonTypeTrigger?.classList.remove('is-open'); }
    });

    // Trạng thái phiếu hiển thị dạng badge tĩnh trên tiêu đề — quyết định Nháp/Xuất kho ngay
    // thực hiện qua 2 nút submit ở cuối panel, không còn dropdown riêng.
    function setAction(action) {
        actionInput.value = action;
        if (statusBadge) {
            statusBadge.textContent = STATUS_LABELS[action] || action;
            statusBadge.classList.toggle('gr-badge--draft', action === 'draft');
            statusBadge.classList.toggle('gr-badge--completed', action === 'issue');
        }
    }

    /* ── Picker: search & dropdown ── */
    function filterVariants(query) {
        query = query.trim().toLowerCase();
        if (!query) return [];
        return variants.filter(v => `${v.product_name} ${v.sku} ${v.color_name} ${v.size_name}`.toLowerCase().includes(query)).slice(0, 30);
    }

    function renderPicker(list) {
        if (list.length === 0) {
            pickerPanel.innerHTML = '<div class="gr-picker-empty">Không tìm thấy sản phẩm phù hợp hoặc đã hết hàng.</div>';
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

    // Hỗ trợ quét mã Barcode/SKU: gõ (hoặc scan) rồi Enter sẽ tự thêm sản phẩm khớp nhất
    pickerInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const query = this.value.trim().toLowerCase();
        if (!query) return;
        const matches = filterVariants(query);
        if (matches.length === 0) return;
        const exact = matches.find(v => v.sku.toLowerCase() === query);
        addVariant((exact || matches[0]).id);
        this.value = '';
        pickerPanel.hidden = true;
    });

    /* ── Item table management ── */
    function addVariant(variantId) {
        variantId = String(variantId);
        const variant = variants.find(v => String(v.id) === variantId);
        if (!variant) return;

        if (selected[variantId]) {
            selected[variantId].quantity = Math.min(selected[variantId].quantity + 1, variant.stock || selected[variantId].quantity + 1);
        } else {
            selected[variantId] = { variant, quantity: 1, unit_price: variant.unit_price || 0 };
            order.unshift(variantId);
        }
        renderTable();
    }

    function removeVariant(variantId) {
        delete selected[variantId];
        order = order.filter(id => id !== variantId);
        renderTable();
    }

    function updateIssueButtonState() {
        if (!issueBtn) return;
        const hasOverStock = Object.values(selected).some(item => item.quantity > item.variant.stock);
        issueBtn.disabled = hasOverStock;
    }

    function renderTable() {
        const ids = order.filter(id => selected[id]);
        tableWrap.classList.toggle('is-empty', ids.length === 0);
        tableBody.innerHTML = '';

        ids.forEach(function (variantId) {
            const item = selected[variantId];
            const { variant, quantity, unit_price } = item;
            const lineTotal = quantity * unit_price;
            const overStock = quantity > variant.stock;
            const allowed = priceEditAllowed();

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
                    <input type="number" min="1" max="${variant.stock}" step="1" class="gr-num-input ${overStock ? 'border-danger' : ''}" data-field="quantity"
                        name="items[${variantId}][quantity]" value="${quantity}">
                    <div class="si-stock-warning" data-field="stock-warning" style="${overStock ? '' : 'display:none;'}">Vượt tồn kho</div>
                </td>
                <td>
                    <input type="number" min="0" step="1000" class="gr-num-input price-input ${allowed ? '' : 'is-locked'}" data-field="unit_price"
                        value="${unit_price}" ${allowed ? '' : 'disabled readonly'}>
                    <input type="hidden" data-field="unit_price_hidden" name="items[${variantId}][unit_price]" value="${unit_price}">
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
        updateIssueButtonState();
    }

    function updatePrice(row, item, inputEl) {
        item.unit_price = Math.max(0, parseFloat(inputEl.value) || 0);
        const hiddenInput = row.querySelector('[data-field="unit_price_hidden"]');
        if (hiddenInput) hiddenInput.value = item.unit_price;
    }

    tableBody.addEventListener('input', function (e) {
        const row = e.target.closest('tr[data-variant-id]');
        if (!row) return;
        const variantId = row.dataset.variantId;
        const item = selected[variantId];
        if (!item) return;

        if (e.target.matches('[data-field="quantity"]')) {
            const qty = Math.max(1, parseInt(e.target.value, 10) || 1);
            item.quantity = qty;
            e.target.value = qty;
            const overStock = qty > item.variant.stock;
            e.target.classList.toggle('border-danger', overStock);
            const warn = row.querySelector('[data-field="stock-warning"]');
            if (warn) warn.style.display = overStock ? '' : 'none';
        }

        if (e.target.matches('[data-field="unit_price"]')) {
            updatePrice(row, item, e.target);
        }

        row.querySelector('[data-field="line-total"]').textContent = money(item.quantity * item.unit_price);
        updateTotal();
        updateIssueButtonState();
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

    // Khi đổi "Loại lý do": khóa/mở toàn bộ ô đơn giá trong bảng cho phù hợp
    function handleReasonChange() {
        const allowed = priceEditAllowed();

        document.querySelectorAll('#siModalTableBody tr[data-variant-id]').forEach(row => {
            const variantId = row.dataset.variantId;
            const item = selected[variantId];
            const priceInput = row.querySelector('.price-input');
            if (!item || !priceInput) return;

            if (allowed) {
                priceInput.disabled = false;
                priceInput.readOnly = false;
                priceInput.classList.remove('is-locked');
            } else {
                item.unit_price = item.variant.original_cost_price ?? item.variant.unit_price ?? 0;
                priceInput.value = item.unit_price;
                priceInput.disabled = true;
                priceInput.readOnly = true;
                priceInput.classList.add('is-locked');
                const hiddenInput = row.querySelector('[data-field="unit_price_hidden"]');
                if (hiddenInput) hiddenInput.value = item.unit_price;
            }

            const lineTotalEl = row.querySelector('[data-field="line-total"]');
            if (lineTotalEl) lineTotalEl.textContent = money(item.quantity * item.unit_price);
        });

        updateTotal();
    }

    /* ── Mở/đóng panel (Bootstrap Offcanvas) ── */
    modal.addEventListener('shown.bs.offcanvas', function () {
        pickerInput?.focus();
    });
    modal.addEventListener('hidden.bs.offcanvas', function () {
        clearErrors();
    });

    async function refreshOutboundTable(url) {
        if (!tableArea) return;
        const res = await fetch(url || '{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.html) tableArea.innerHTML = data.html;
    }

    document.querySelectorAll('[data-si-modal-action]').forEach(btn => {
        btn.addEventListener('click', function () { setAction(this.dataset.siModalAction); });
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();
        if (Object.keys(selected).length === 0) {
            showErrors({ items: ['Vui lòng thêm ít nhất một sản phẩm vào phiếu xuất kho.'] });
            return;
        }

        const submitter = e.submitter;
        submitter?.setAttribute('disabled', 'disabled');

        try {
            const res = await fetch('{{ route('admin.stock-issues.store') }}', {
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
                showErrors((data && data.errors) || { items: [(data && data.message) || 'Không thể tạo phiếu xuất kho.'] });
                return;
            }

            if (!data.code) {
                showErrors({ items: ['Không nhận được xác nhận tạo phiếu từ máy chủ. Vui lòng tải lại trang và kiểm tra lại danh sách.'] });
                return;
            }

            selected = {};
            order = [];
            form.reset();
            setAction('draft');
            const firstReasonItem = reasonTypeList?.querySelector('.hk-cat-item');
            if (firstReasonItem) {
                reasonTypeSelect.value = firstReasonItem.dataset.value;
                reasonTypeLabelEl.textContent = firstReasonItem.dataset.label;
                reasonTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === firstReasonItem));
            }
            renderTable();
            bootstrap.Offcanvas.getOrCreateInstance(modal).hide();
            await refreshOutboundTable(data.table_url);
            showToast(data.message || `Tạo phiếu xuất kho "${data.code}" thành công.`);
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
