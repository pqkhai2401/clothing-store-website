{{--
    Modal "Tạo phiếu kiểm kê kho" — Bootstrap thuần (không dùng Tailwind), modal căn giữa cỡ lớn.
    Variables expected: $variants — Collection các biến thể sản phẩm còn hoạt động, đã map sẵn field cần thiết.
    $stocktakeCode — mã phiếu xem trước (thực tế được sinh lại ở server khi lưu để tránh trùng số thứ tự).
--}}

<div class="modal fade" id="stocktakeModal" tabindex="-1" aria-labelledby="stocktakeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered stk-modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h1 class="modal-title fw-bold mb-1" id="stocktakeModalLabel" style="font-size:20px;color:#111827;">
                        Tạo phiếu kiểm kê kho mới
                    </h1>
                    <p class="mb-0 text-muted" style="font-size:13px;">
                        Đối chiếu số lượng tồn kho thực tế tại quầy kệ với số liệu trên phần mềm.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                {{-- Thông tin phiếu kiểm --}}
                <div class="card stk-info-card mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <span class="stk-inline-label d-block mb-1">Mã phiếu kiểm</span>
                                <input type="text" class="form-control stk-locked-input" value="{{ $stocktakeCode ?? 'PKK...' }}" disabled readonly>
                            </div>
                            <div class="col-md-4">
                                <span class="stk-inline-label d-block mb-1">Người kiểm kê</span>
                                <input type="text" class="form-control stk-locked-input" value="{{ auth()->user()->username ?? 'N/A' }}" disabled readonly>
                            </div>
                            <div class="col-md-4">
                                <span class="stk-inline-label d-block mb-1">Ghi chú / Nội dung kiểm</span>
                                <input type="text" class="form-control" id="stkNoteInput"
                                    placeholder="Ví dụ: Kiểm kho định kỳ hàng tháng kệ áo thun...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bảng đối chiếu số liệu thực tế --}}
                <div class="gr-picker-wrap mb-3">
                    <i class="fa-solid fa-magnifying-glass stk-search-icon"></i>
                    <input type="text" class="gr-picker-input stk-search-input" id="stkPickerInput"
                        placeholder="Quét mã vạch hoặc gõ tên sản phẩm, SKU để đưa vào danh sách kiểm kê..." autocomplete="off">
                    <div class="gr-picker-panel" id="stkPickerPanel" hidden></div>
                </div>

                <div class="gr-table-wrap is-empty" id="stkTableWrap">
                    <table class="gr-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm / SKU</th>
                                <th style="width:80px;">Đơn vị</th>
                                <th style="width:110px;">Tồn hệ thống</th>
                                <th style="width:120px;">Tồn thực tế</th>
                                <th style="width:100px;">Chênh lệch</th>
                                <th style="width:44px;"></th>
                            </tr>
                        </thead>
                        <tbody id="stkTableBody"></tbody>
                    </table>
                    <div class="gr-table-empty">Chưa có sản phẩm nào. Quét mã vạch hoặc tìm sản phẩm ở ô phía trên để thêm vào danh sách kiểm kê.</div>
                </div>
            </div>

            <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
                <button type="button" class="btn btn-light border fw-semibold stk-footer-btn" data-bs-dismiss="modal">
                    Hủy bỏ
                </button>
                <button type="button" class="btn btn-outline-dark fw-semibold stk-footer-btn" id="stkSaveDraftBtn">
                    <i class="fa-regular fa-floppy-disk me-1"></i> Lưu phiếu kiểm
                </button>
                <button type="button" class="btn gr-btn-emerald fw-semibold stk-footer-btn" id="stkReconcileBtn">
                    <i class="fa-solid fa-check me-1"></i> Xác nhận cân bằng kho
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal bước 2: Xác nhận kết quả kiểm kê kho trước khi cân bằng --}}
<div class="modal fade" id="stocktakeConfirmModal" tabindex="-1" aria-labelledby="stocktakeConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered stk-confirm-dialog">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="stk-confirm-icon flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1" id="stocktakeConfirmModalLabel" style="font-size:18px;color:#111827;">
                            Xác nhận điều chỉnh số lượng tồn kho?
                        </h2>
                        <p class="mb-0 text-muted" style="font-size:13px;">
                            Hệ thống sẽ tự động tạo các phiếu nhập/xuất kho tương ứng để cân bằng số lượng thực tế. Hành động này không thể hoàn tác.
                        </p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="stk-summary-card stk-summary-card--neg">
                            <div class="stk-summary-label">Tổng sản phẩm hao hụt (Lệch âm)</div>
                            <div class="stk-summary-value stk-summary-value--neg" id="stkSummaryNegValue">-0 cái</div>
                            <div class="stk-summary-caption" id="stkSummaryNegCaption">Hệ thống tự động sinh: 0 Phiếu xuất kho</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stk-summary-card stk-summary-card--pos">
                            <div class="stk-summary-label">Tổng sản phẩm dư thừa (Lệch dương)</div>
                            <div class="stk-summary-value stk-summary-value--pos" id="stkSummaryPosValue">+0 cái</div>
                            <div class="stk-summary-caption" id="stkSummaryPosCaption">Hệ thống tự động sinh: 0 Phiếu nhập kho</div>
                        </div>
                    </div>
                </div>

                <div class="stk-confirm-list" id="stkConfirmList"></div>
            </div>

            <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
                <button type="button" class="btn btn-light border fw-semibold stk-footer-btn" id="stkConfirmBackBtn">
                    Quay lại kiểm tra
                </button>
                <button type="button" class="btn gr-btn-emerald fw-semibold stk-footer-btn" id="stkConfirmFinalBtn">
                    <i class="fa-solid fa-check me-1"></i> Đồng ý cân bằng &amp; Lưu phiếu
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.stk-modal-dialog { max-width: min(1280px, 92vw); }
.stk-info-card { border: 1.5px solid #e5e7eb; border-radius: 12px; background: #f9fafb; }
.stk-inline-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; white-space: nowrap; }
.stk-locked-input { background: #f3f4f6 !important; color: #6b7280 !important; font-weight: 700; cursor: not-allowed; }
.stk-search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; pointer-events: none; }
.stk-search-input { padding-left: 38px !important; }
.stk-footer-btn { min-width: 140px; justify-content: center; display: inline-flex; align-items: center; }

.stk-row-actual {
    width: 100%; min-width: 90px; height: 36px; border: 1.5px solid #d1d5db; border-radius: 6px;
    padding: 0 10px; font-size: 14px; font-weight: 700; outline: none; box-sizing: border-box; text-align: center;
}
.stk-row-actual:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.12); }
.stk-system-stock { font-weight: 700; color: #6b7280; }
.stk-diff-badge {
    display: inline-flex; align-items: center; justify-content: center; min-width: 44px;
    padding: 3px 10px; border-radius: 999px; font-size: 13px; font-weight: 800;
}
.stk-diff-badge--neg { background: #fee2e2; color: #dc2626; }
.stk-diff-badge--pos { background: #dcfce7; color: #16a34a; }
.stk-diff-badge--zero { background: #f3f4f6; color: #6b7280; }

/* ── Variant picker & bảng sản phẩm (dùng chung style với trang tạo phiếu nhập/xuất kho) ── */
.gr-picker-wrap { position: relative; }
.gr-picker-input {
    width: 100%; height: 42px; border: 1.5px solid #d1d5db; border-radius: 8px;
    padding: 0 14px; font-size: 14px; outline: none;
}
.gr-picker-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
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

.gr-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; max-height: 400px; overflow-y: auto; }
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
.gr-row-remove {
    width: 28px; height: 28px; border-radius: 50%; border: 0; background: #f3f4f6; color: #6b7280;
    display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .15s, color .15s;
}
.gr-row-remove:hover { background: #fee2e2; color: #dc2626; }
.gr-table-empty { padding: 28px 16px; text-align: center; color: #9ca3af; font-size: 13px; }
.gr-table-wrap.is-empty .gr-table { display: none; }
.gr-table-wrap:not(.is-empty) .gr-table-empty { display: none; }

/* ── Modal bước 2: Xác nhận kết quả kiểm kê ── */
.stk-confirm-dialog { max-width: min(672px, 92vw); }
.stk-confirm-icon {
    width: 40px; height: 40px; border-radius: 50%; background: #fef3c7; color: #b45309;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.stk-summary-card { border-radius: 10px; padding: 14px 16px; height: 100%; }
.stk-summary-card--neg { background: #fef2f2; border: 1px solid #fee2e2; }
.stk-summary-card--pos { background: #f0fdf4; border: 1px solid #dcfce7; }
.stk-summary-label { font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 6px; }
.stk-summary-value { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.stk-summary-value--neg { color: #dc2626; }
.stk-summary-value--pos { color: #16a34a; }
.stk-summary-caption { font-size: 12px; color: #6b7280; }
.stk-confirm-list { max-height: 180px; overflow-y: auto; border-top: 1px solid #f3f4f6; }
.stk-confirm-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 4px; border-bottom: 1px solid #f3f4f6; }
.stk-confirm-row:last-child { border-bottom: 0; }
.stk-confirm-row-name { font-size: 13px; font-weight: 700; color: #111827; }
.stk-confirm-row-detail { font-size: 12px; font-weight: 600; white-space: nowrap; }
.stk-confirm-row-detail--neg { color: #dc2626; }
.stk-confirm-row-detail--pos { color: #16a34a; }

/* ── Modal chi tiết & duyệt phiếu kiểm kê ── */
.stkd-modal-dialog { max-width: min(1120px, 85vw); }
.stkd-summary-bar {
    display: flex; flex-wrap: wrap; align-items: center; gap: 24px;
    padding: 16px 20px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
}
.stkd-summary-item { display: flex; flex-direction: column; gap: 2px; }
.stkd-summary-item-label { font-size: 12px; font-weight: 600; color: #6b7280; }
.stkd-summary-item-value { font-size: 14px; font-weight: 700; }
.stkd-summary-item--net { margin-left: auto; text-align: right; }
.stkd-summary-net-value { font-size: 24px; font-weight: 800; color: #000; }
</style>
@endpush
@endonce

@once
@push('scripts')
<script>
(function () {
    const variants = @json($variants);
    const modal = document.getElementById('stocktakeModal');
    const pickerInput = document.getElementById('stkPickerInput');
    const pickerPanel = document.getElementById('stkPickerPanel');
    const tableWrap = document.getElementById('stkTableWrap');
    const tableBody = document.getElementById('stkTableBody');
    const saveDraftBtn = document.getElementById('stkSaveDraftBtn');
    const reconcileBtn = document.getElementById('stkReconcileBtn');
    const confirmModal = document.getElementById('stocktakeConfirmModal');
    const confirmList = document.getElementById('stkConfirmList');
    const confirmBackBtn = document.getElementById('stkConfirmBackBtn');
    const confirmFinalBtn = document.getElementById('stkConfirmFinalBtn');
    let selected = {};
    let order = [];
    let skipResetOnHide = false; // true khi đang chuyển sang modal xác nhận, không phải đóng thật sự

    if (!modal) return;

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function resolveImageUrl(path) {
        if (!path) return 'https://placehold.co/80x80?text=No+Image';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    // ── Toast thông báo (đồng bộ với component thông báo chung của trang) ──
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) { window.showAlert(message, 'Thông báo', type === 'error' ? 'danger' : 'success'); return; }
        const toast = document.createElement('div');
        toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
        toast.style.pointerEvents = 'auto';
        toast.innerHTML = `
            <div class="toast-content">
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

    /* ── Picker: search & dropdown ── */
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
        if (selected[variantId]) {
            const row = tableBody.querySelector(`tr[data-variant-id="${variantId}"] .stk-row-actual`);
            row?.focus();
            return;
        }
        const variant = variants.find(v => String(v.id) === variantId);
        if (!variant) return;

        // Mặc định số đếm thực tế = tồn hệ thống (chênh lệch = 0), người kiểm kê sẽ chỉnh lại theo thực tế.
        selected[variantId] = { variant, actual: variant.stock };
        order.unshift(variantId);
        renderTable();
    }

    function removeVariant(variantId) {
        delete selected[variantId];
        order = order.filter(id => id !== variantId);
        renderTable();
    }

    function diffBadge(diff) {
        if (diff > 0) return `<span class="stk-diff-badge stk-diff-badge--pos">+${diff}</span>`;
        if (diff < 0) return `<span class="stk-diff-badge stk-diff-badge--neg">${diff}</span>`;
        return `<span class="stk-diff-badge stk-diff-badge--zero">0</span>`;
    }

    function renderTable() {
        const ids = order.filter(id => selected[id]);
        tableWrap.classList.toggle('is-empty', ids.length === 0);
        tableBody.innerHTML = '';

        ids.forEach(function (variantId) {
            const item = selected[variantId];
            const { variant, actual } = item;
            const diff = actual - variant.stock;

            const tr = document.createElement('tr');
            tr.dataset.variantId = variantId;
            tr.innerHTML = `
                <td>
                    <div class="gr-row-product">
                        <img class="gr-row-thumb" src="${resolveImageUrl(variant.thumbnail)}" alt="">
                        <div>
                            <div class="gr-row-name">${esc(variant.product_name)} ${variant.color_name || variant.size_name ? `(${esc(variant.color_name)}${variant.color_name && variant.size_name ? ' - ' : ''}${esc(variant.size_name)})` : ''}</div>
                            <div class="gr-row-sub">
                                <span class="gr-row-dot" style="background:${esc(variant.color_hex || '#ccc')};"></span>
                                SKU: ${esc(variant.sku)}
                            </div>
                        </div>
                    </div>
                </td>
                <td>cái</td>
                <td class="stk-system-stock" data-field="system-stock">${variant.stock}</td>
                <td>
                    <input type="number" min="0" step="1" class="stk-row-actual" data-field="actual" value="${actual}">
                </td>
                <td data-field="diff">${diffBadge(diff)}</td>
                <td>
                    <button type="button" class="gr-row-remove" data-remove-variant="${variantId}" title="Xóa">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    tableBody.addEventListener('input', function (e) {
        if (!e.target.matches('[data-field="actual"]')) return;
        const row = e.target.closest('tr[data-variant-id]');
        const variantId = row.dataset.variantId;
        const item = selected[variantId];
        if (!item) return;

        const actual = Math.max(0, parseInt(e.target.value, 10) || 0);
        item.actual = actual;
        const diff = actual - item.variant.stock;
        row.querySelector('[data-field="diff"]').innerHTML = diffBadge(diff);
    });

    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-variant]');
        if (!btn) return;
        removeVariant(btn.dataset.removeVariant);
    });

    modal.addEventListener('shown.bs.modal', function () {
        pickerInput?.focus();
    });
    modal.addEventListener('hidden.bs.modal', function () {
        if (skipResetOnHide) { skipResetOnHide = false; return; }
        selected = {};
        order = [];
        renderTable();
        pickerInput.value = '';
        document.getElementById('stkNoteInput').value = '';
    });

    function requireItems() {
        if (Object.keys(selected).length === 0) {
            showToast('Vui lòng thêm ít nhất một sản phẩm vào phiếu kiểm kê.', 'error');
            return false;
        }
        return true;
    }

    function buildPayload(submitAction) {
        return {
            note: document.getElementById('stkNoteInput').value || null,
            submit_action: submitAction,
            items: order.filter(id => selected[id]).map(id => ({
                product_variant_id: Number(id),
                actual_stock: selected[id].actual,
            })),
        };
    }

    async function refreshStocktakeTable() {
        const tableArea = document.querySelector('[data-admin-table-area]');
        if (!tableArea) return;
        const res = await fetch('{{ route('admin.goods-receipts.list', ['tab' => 'stocktake']) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.html) tableArea.innerHTML = data.html;
    }

    async function submitStocktake(submitAction, button) {
        button?.setAttribute('disabled', 'disabled');
        try {
            const res = await fetch('{{ route('admin.stocktakes.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(buildPayload(submitAction)),
            });

            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                showToast('Phiên làm việc có thể đã hết hạn. Vui lòng tải lại trang (F5) và thử lại.', 'error');
                return false;
            }

            const data = await res.json().catch(() => null);

            if (!res.ok || !data) {
                const firstError = data?.errors ? Object.values(data.errors).flat()[0] : null;
                showToast(firstError || data?.message || 'Không thể lưu phiếu kiểm kê.', 'error');
                return false;
            }

            selected = {};
            order = [];
            renderTable();
            pickerInput.value = '';
            document.getElementById('stkNoteInput').value = '';
            await refreshStocktakeTable();
            showToast(data.message);
            return true;
        } catch (err) {
            showToast('Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng và thử lại.', 'error');
            return false;
        } finally {
            button?.removeAttribute('disabled');
        }
    }

    saveDraftBtn.addEventListener('click', async function () {
        if (!requireItems()) return;
        const ok = await submitStocktake('pending', saveDraftBtn);
        if (ok) bootstrap.Modal.getOrCreateInstance(modal).hide();
    });

    /* ── Bước 2: Xác nhận kết quả kiểm kê trước khi cân bằng kho ── */
    function discrepancyItems() {
        return order
            .filter(id => selected[id])
            .map(id => selected[id])
            .filter(item => item.actual - item.variant.stock !== 0);
    }

    function renderConfirmSummary() {
        const items = discrepancyItems();
        const negItems = items.filter(item => item.actual - item.variant.stock < 0);
        const posItems = items.filter(item => item.actual - item.variant.stock > 0);
        const negTotal = negItems.reduce((sum, item) => sum + (item.actual - item.variant.stock), 0);
        const posTotal = posItems.reduce((sum, item) => sum + (item.actual - item.variant.stock), 0);

        document.getElementById('stkSummaryNegValue').textContent = `${negTotal} cái`;
        document.getElementById('stkSummaryNegCaption').textContent =
            `Hệ thống tự động sinh: ${negItems.length > 0 ? 1 : 0} Phiếu xuất kho`;
        document.getElementById('stkSummaryPosValue').textContent = `+${posTotal} cái`;
        document.getElementById('stkSummaryPosCaption').textContent =
            `Hệ thống tự động sinh: ${posItems.length > 0 ? 1 : 0} Phiếu nhập kho`;

        confirmList.innerHTML = items.map(item => {
            const diff = item.actual - item.variant.stock;
            const sign = diff > 0 ? '+' : '';
            const cls = diff > 0 ? 'stk-confirm-row-detail--pos' : 'stk-confirm-row-detail--neg';
            const variantLabel = item.variant.color_name || item.variant.size_name
                ? ` (${esc(item.variant.color_name)}${item.variant.color_name && item.variant.size_name ? ' - ' : ''}${esc(item.variant.size_name)})`
                : '';
            return `
                <div class="stk-confirm-row">
                    <span class="stk-confirm-row-name">${esc(item.variant.product_name)}${variantLabel}</span>
                    <span class="stk-confirm-row-detail ${cls}">Hệ thống: ${item.variant.stock} &rarr; Thực tế: ${item.actual} (Chênh lệch: ${sign}${diff})</span>
                </div>
            `;
        }).join('');

        return items.length;
    }

    reconcileBtn.addEventListener('click', function () {
        if (!requireItems()) return;
        if (renderConfirmSummary() === 0) {
            showToast('Không có chênh lệch nào cần cân bằng kho.', 'error');
            return;
        }
        skipResetOnHide = true;
        bootstrap.Modal.getOrCreateInstance(modal).hide();
        modal.addEventListener('hidden.bs.modal', function showConfirmOnce() {
            modal.removeEventListener('hidden.bs.modal', showConfirmOnce);
            bootstrap.Modal.getOrCreateInstance(confirmModal).show();
        });
    });

    confirmBackBtn.addEventListener('click', function () {
        bootstrap.Modal.getOrCreateInstance(confirmModal).hide();
        confirmModal.addEventListener('hidden.bs.modal', function showStocktakeOnce() {
            confirmModal.removeEventListener('hidden.bs.modal', showStocktakeOnce);
            bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    });

    confirmFinalBtn.addEventListener('click', async function () {
        const ok = await submitStocktake('approve', confirmFinalBtn);
        if (ok) bootstrap.Modal.getOrCreateInstance(confirmModal).hide();
    });
})();
</script>
@endpush
@endonce
