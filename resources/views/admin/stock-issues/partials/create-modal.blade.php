{{--
    Modal/Offcanvas tạo phiếu xuất kho mới — Bootstrap thuần.
    Variables expected: $variants — Collection các biến thể còn tồn kho, đã map sẵn field cần thiết.
    $warehouses — Danh sách kho hàng hoạt động.
    $orders — Danh sách đơn hàng.
--}}

<div class="offcanvas offcanvas-end si-offcanvas" tabindex="-1" id="stockIssueOffcanvas" aria-labelledby="stockIssueOffcanvasLabel">
    <form method="POST" action="{{ route('admin.stock-issues.store') }}" id="stockIssueAjaxForm" class="d-flex flex-column h-100">
        @csrf
        <input type="hidden" name="submit_action" id="siModalActionInput" value="draft">

        <div class="offcanvas-header border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="offcanvas-title mb-0" id="stockIssueOffcanvasLabel">Tạo phiếu xuất kho mới</h2>
                    <span class="gr-badge gr-badge--draft" id="siModalStatusBadge">Nháp</span>
                </div>
                <p class="mb-0 text-muted" style="font-size:13px;">Chọn kho xuất, loại xuất, sản phẩm cần xuất và điền lý do tương ứng.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>

        <div class="offcanvas-body flex-grow-1 overflow-auto">
            {{-- Thông tin chung --}}
            <div class="card si-info-card mb-4">
                <div class="card-header bg-white py-2">
                    <span class="fw-bold text-dark" style="font-size:14px;">Thông tin phiếu xuất</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        {{-- Loại xuất kho --}}
                        <div class="col-md-6">
                            <span class="si-inline-label d-block mb-1">Loại xuất kho <span class="text-danger">*</span></span>
                            <input type="hidden" name="issue_type" id="issueTypeInput" value="sale" required>
                            <div class="hk-cat-filter si-filter-width" id="issueTypeFilter">
                                <button type="button" class="hk-cat-trigger" id="issueTypeTrigger">
                                    <span class="hk-cat-trigger-label" id="issueTypeLabel">Xuất bán hàng</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="issueTypePanel" hidden style="width:280px;">
                                    <div class="hk-cat-list" id="issueTypeList">
                                        @foreach(\App\Models\StockIssue::ISSUE_TYPE_LABELS as $value => $label)
                                            <button type="button" class="hk-cat-item {{ $value === 'sale' ? 'is-active' : '' }}"
                                                data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="invalid-feedback d-block mt-1" data-si-error="issue_type"></div>
                        </div>

                        {{-- Kho xuất kho --}}
                        <div class="col-md-6">
                            <span class="si-inline-label d-block mb-1">Kho xuất hàng <span class="text-danger">*</span></span>
                            @php
                                $defaultWh = $warehouses->first(fn($w) => $w->is_default) ?? $warehouses->first();
                            @endphp
                            <input type="hidden" name="warehouse_id" id="warehouseIdInput" value="{{ $defaultWh->id ?? '' }}" required>
                            <div class="hk-cat-filter si-filter-width" id="warehouseIdFilter">
                                <button type="button" class="hk-cat-trigger" id="warehouseIdTrigger">
                                    <span class="hk-cat-trigger-label" id="warehouseIdLabel">
                                        {{ $defaultWh ? $defaultWh->name : 'Chọn kho xuất' }}
                                    </span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="warehouseIdPanel" hidden style="width:280px;">
                                    <div class="hk-cat-list" id="warehouseIdList">
                                        @foreach($warehouses as $wh)
                                            <button type="button" class="hk-cat-item {{ ($defaultWh->id ?? '') == $wh->id ? 'is-active' : '' }}"
                                                data-value="{{ $wh->id }}" data-label="{{ $wh->name }}">{{ $wh->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="invalid-feedback d-block mt-1" data-si-error="warehouse_id"></div>
                        </div>

                        {{-- Đơn hàng liên quan (chỉ hiển thị khi xuất bán) --}}
                        <div class="col-md-6" id="orderSelectWrap">
                            <span class="si-inline-label d-block mb-1">Đơn hàng liên quan</span>
                            <input type="hidden" name="order_id" id="orderIdInput" value="">
                            <div class="hk-cat-filter si-filter-width" id="orderIdFilter">
                                <button type="button" class="hk-cat-trigger" id="orderIdTrigger">
                                    <span class="hk-cat-trigger-label" id="orderIdLabel">Chọn đơn hàng (Tùy chọn)</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="orderIdPanel" hidden style="width:280px; max-height:250px; overflow-y:auto;">
                                    <div class="hk-cat-list" id="orderIdList">
                                        <button type="button" class="hk-cat-item is-active" data-value="" data-label="Chọn đơn hàng (Tùy chọn)">Không liên kết đơn</button>
                                        @foreach($orders as $order)
                                            <button type="button" class="hk-cat-item"
                                                data-value="{{ $order->id }}" data-label="{{ $order->order_code ?? 'Đơn #'.$order->id }}">{{ $order->order_code ?? 'Đơn #'.$order->id }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="invalid-feedback d-block mt-1" data-si-error="order_id"></div>
                        </div>

                        {{-- Ngày xuất kho --}}
                        <div class="col-md-6">
                            <span class="si-inline-label d-block mb-1">Ngày xuất kho <span class="text-danger">*</span></span>
                            <input type="datetime-local" id="siModalIssuedAt" name="issued_at" class="form-control form-control-sm border-grey" 
                                   value="{{ now()->format('Y-m-d\TH:i') }}" required style="border-radius: 999px; height: 38px;">
                            <div class="invalid-feedback d-block mt-1" data-si-error="issued_at"></div>
                        </div>

                        {{-- Lý do xuất --}}
                        <div class="col-md-12">
                            <label for="siModalReason" class="si-inline-label d-block mb-1" id="siReasonLabel">Lý do xuất kho</label>
                            <input type="text" id="siModalReason" name="reason" class="form-control form-control-sm border-grey" 
                                   placeholder="Ví dụ: Xuất hủy hàng lỗi mốc, kiểm kê kho định kỳ..." style="border-radius: 999px; height: 38px;">
                            <div class="invalid-feedback d-block mt-1" data-si-error="reason"></div>
                        </div>

                        {{-- Ghi chú --}}
                        <div class="col-md-12">
                            <span class="si-inline-label d-block mb-1">Ghi chú chi tiết</span>
                            <textarea id="siModalNote" name="note" rows="2" class="form-control border-grey"
                                placeholder="Thêm ghi chú chi tiết cho phiếu xuất kho..." style="border-radius: 12px;"></textarea>
                            <div class="invalid-feedback d-block mt-1" data-si-error="note"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sản phẩm xuất kho --}}
            <div class="card">
                <div class="card-header bg-white py-2">
                    <span class="fw-bold text-dark" style="font-size:14px;">Sản phẩm xuất kho</span>
                </div>
                <div class="card-body p-3">
                    <div class="gr-picker-wrap">
                        <input type="text" class="gr-picker-input" id="siModalPickerInput"
                            placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off" style="border-radius: 999px;">
                        <div class="gr-picker-panel" id="siModalPickerPanel" hidden></div>
                    </div>
                    <div class="invalid-feedback d-block mt-2" data-si-error="items"></div>

                    <div class="gr-table-wrap is-empty" id="siModalTableWrap" style="border-radius:12px;">
                        <table class="gr-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="width:90px;">Tồn kho</th>
                                    <th style="width:100px;">Số lượng</th>
                                    <th style="width:120px;">Giá vốn (đ)</th>
                                    <th style="width:120px;">Giá bán (đ)</th>
                                    <th style="width:120px;">Tổng vốn</th>
                                    <th style="width:120px;">Tổng bán</th>
                                    <th style="width:44px;"></th>
                                </tr>
                            </thead>
                            <tbody id="siModalTableBody"></tbody>
                        </table>
                        <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                    </div>

                    <div class="d-flex flex-column gap-2 mt-3">
                        <div class="gr-summary-bar" style="border-radius:12px;">
                            <span class="gr-summary-label">Tổng giá trị vốn:</span>
                            <span class="gr-summary-value text-secondary" id="siModalTotalCost">0đ</span>
                        </div>
                        <div class="gr-summary-bar" style="border-radius:12px; margin-top:0;">
                            <span class="gr-summary-label">Tổng giá trị bán:</span>
                            <span class="gr-summary-value" id="siModalTotalSale">0đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas" style="border-radius:999px;">Hủy bỏ</button>
            <button type="submit" class="btn btn-outline-dark fw-semibold" data-si-modal-action="draft" style="border-radius:999px;">
                <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
            </button>
            <button type="submit" class="btn gr-btn-emerald fw-semibold" id="siModalIssueBtn" data-si-modal-action="complete" style="border-radius:999px;">
                <i class="fa-solid fa-check me-1"></i> Hoàn tất xuất kho
            </button>
        </div>
    </form>
</div>

@once
@push('styles')
<style>
.si-offcanvas { width: min(950px, 95vw) !important; }
.si-info-card { border: 1.5px solid #e5e7eb; border-radius: 12px; background: #f9fafb; }
.si-inline-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; white-space: nowrap; }
.si-filter-width { width: 100%; }
.border-grey { border: 1.5px solid #d1d5db; }
.border-grey:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
.price-input.is-locked { background: #f3f4f6 !important; color: #9ca3af !important; cursor: not-allowed; }
.gr-num-input.border-danger { border-color: #dc2626 !important; }
.si-stock-warning { font-size: 11px; font-weight: 600; color: #dc2626; margin-top: 4px; }

/* ── Custom dropdowns (general) ── */
.si-offcanvas .hk-cat-filter { position: relative; }
.si-offcanvas .hk-cat-trigger {
    width: 100%; height: 38px;
    display: flex; align-items: center; justify-content: space-between;
    border-radius: 999px;
    border: 1.5px solid #d1d5db;
    background: #fff;
    font-size: 13px;
    color: #0f172a;
    padding: 0 14px;
    text-align: left;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
}
.si-offcanvas .hk-cat-trigger:focus {
    border-color: #000;
    box-shadow: 0 0 0 3px rgba(23,71,97,.08);
}
.si-offcanvas .hk-cat-panel {
    position: absolute; top: calc(100% + 4px); left: 0;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    z-index: 1070;
}
.si-offcanvas .hk-cat-list { padding: 6px; }
.si-offcanvas .hk-cat-item {
    width: 100%; border: 0; background: none;
    text-align: left; font-size: 13px; padding: 8px 12px;
    color: #334155; border-radius: 8px;
    transition: background .15s, color .15s;
}
.si-offcanvas .hk-cat-item:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.si-offcanvas .hk-cat-item.is-active {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 600;
}

/* ── Picker ── */
.gr-picker-wrap { position: relative; }
.gr-picker-input {
    width: 100%; height: 40px; border: 1.5px solid #d1d5db; border-radius: 8px;
    padding: 0 14px; font-size: 14px; outline: none;
}
.gr-picker-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
.gr-picker-panel {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.12); max-height: 280px; overflow-y: auto;
    z-index: 1060;
}
.gr-picker-item {
    display: flex; align-items: center; gap: 10px; padding: 8px 12px;
    cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .1s;
}
.gr-picker-item:last-child { border-bottom: 0; }
.gr-picker-item:hover { background: #f9fafb; }
.gr-picker-thumb { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
.gr-picker-info { flex: 1; min-width: 0; }
.gr-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gr-picker-meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.gr-picker-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); }
.gr-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.gr-picker-empty { padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }

/* ── Items table ── */
.gr-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; max-height: 360px; overflow-y: auto; }
.gr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 800px; }
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
    width: 100%; min-width: 75px; height: 34px; border: 1.5px solid #d1d5db; border-radius: 6px;
    padding: 0 8px; font-size: 13px; outline: none; box-sizing: border-box;
}
.gr-num-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
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
    margin-top: 14px; padding: 10px 16px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
}
.gr-summary-label { font-size: 13px; color: #475569; font-weight: 600; }
.gr-summary-value { font-size: 17px; font-weight: 800; color: #1e293b; }
.gr-summary-value#siModalTotalSale { color: #000; font-size: 19px; }
</style>
@endpush
@endonce

@once
@push('scripts')
<script>
(function () {
    const variants = @json($stockIssueVariants);
    const modal = document.getElementById('stockIssueOffcanvas');
    const form = document.getElementById('stockIssueAjaxForm');
    const actionInput = document.getElementById('siModalActionInput');
    const pickerInput = document.getElementById('siModalPickerInput');
    const pickerPanel = document.getElementById('siModalPickerPanel');
    const tableWrap = document.getElementById('siModalTableWrap');
    const tableBody = document.getElementById('siModalTableBody');
    const totalCostVal = document.getElementById('siModalTotalCost');
    const totalSaleVal = document.getElementById('siModalTotalSale');
    const issueBtn = document.getElementById('siModalIssueBtn');

    // ── Dropdowns ──
    const issueTypeInput = document.getElementById('issueTypeInput');
    const issueTypeTrigger = document.getElementById('issueTypeTrigger');
    const issueTypeLabel = document.getElementById('issueTypeLabel');
    const issueTypePanel = document.getElementById('issueTypePanel');
    const issueTypeList = document.getElementById('issueTypeList');

    const warehouseIdInput = document.getElementById('warehouseIdInput');
    const warehouseIdTrigger = document.getElementById('warehouseIdTrigger');
    const warehouseIdLabel = document.getElementById('warehouseIdLabel');
    const warehouseIdPanel = document.getElementById('warehouseIdPanel');
    const warehouseIdList = document.getElementById('warehouseIdList');

    const orderIdInput = document.getElementById('orderIdInput');
    const orderIdTrigger = document.getElementById('orderIdTrigger');
    const orderIdLabel = document.getElementById('orderIdLabel');
    const orderIdPanel = document.getElementById('orderIdPanel');
    const orderIdList = document.getElementById('orderIdList');

    const orderSelectWrap = document.getElementById('orderSelectWrap');
    const reasonLabel = document.getElementById('siReasonLabel');
    const reasonInput = document.getElementById('siModalReason');

    const selectedItems = {};

    function closeAllPanels() {
        issueTypePanel.hidden = true;
        warehouseIdPanel.hidden = true;
        if (orderIdPanel) orderIdPanel.hidden = true;
        pickerPanel.hidden = true;
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#issueTypeFilter') && !e.target.closest('#warehouseIdFilter') && !e.target.closest('#orderIdFilter') && !e.target.closest('.gr-picker-wrap')) {
            closeAllPanels();
        }
    });

    // ── Issue Type Dropdown ──
    issueTypeTrigger.addEventListener('click', function() {
        const isHidden = issueTypePanel.hidden;
        closeAllPanels();
        issueTypePanel.hidden = !isHidden;
    });

    issueTypeList.addEventListener('click', function(e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        
        issueTypeList.querySelectorAll('.hk-cat-item').forEach(el => el.classList.remove('is-active'));
        btn.classList.add('is-active');
        
        const val = btn.dataset.value;
        const lbl = btn.dataset.label;
        
        issueTypeInput.value = val;
        issueTypeLabel.textContent = lbl;
        issueTypePanel.hidden = true;

        // Toggle order selector visibility
        if (val === 'sale') {
            orderSelectWrap.style.display = '';
        } else {
            orderSelectWrap.style.display = 'none';
            orderIdInput.value = '';
            orderIdLabel.textContent = 'Chọn đơn hàng (Tùy chọn)';
            orderIdList.querySelectorAll('.hk-cat-item').forEach(el => el.classList.remove('is-active'));
            orderIdList.querySelector('[data-value=""]').classList.add('is-active');
        }

        // Toggle reason requirement label
        if (val === 'adjustment' || val === 'damaged') {
            reasonLabel.innerHTML = 'Lý do xuất kho <span class="text-danger">*</span>';
            reasonInput.setAttribute('required', 'required');
        } else {
            reasonLabel.innerHTML = 'Lý do xuất kho';
            reasonInput.removeAttribute('required');
        }

        // Update price editable state
        updatePriceEditingState();
        calculateTotals();
    });

    // ── Warehouse Dropdown ──
    warehouseIdTrigger.addEventListener('click', function() {
        const isHidden = warehouseIdPanel.hidden;
        closeAllPanels();
        warehouseIdPanel.hidden = !isHidden;
    });

    warehouseIdList.addEventListener('click', function(e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        
        warehouseIdList.querySelectorAll('.hk-cat-item').forEach(el => el.classList.remove('is-active'));
        btn.classList.add('is-active');
        
        warehouseIdInput.value = btn.dataset.value;
        warehouseIdLabel.textContent = btn.dataset.label;
        warehouseIdPanel.hidden = true;
    });

    // ── Order Dropdown ──
    if (orderIdTrigger) {
        orderIdTrigger.addEventListener('click', function() {
            const isHidden = orderIdPanel.hidden;
            closeAllPanels();
            orderIdPanel.hidden = !isHidden;
        });

        orderIdList.addEventListener('click', function(e) {
            const btn = e.target.closest('.hk-cat-item');
            if (!btn) return;
            
            orderIdList.querySelectorAll('.hk-cat-item').forEach(el => el.classList.remove('is-active'));
            btn.classList.add('is-active');
            
            orderIdInput.value = btn.dataset.value;
            orderIdLabel.textContent = btn.dataset.label;
            orderIdPanel.hidden = true;
        });
    }

    function updatePriceEditingState() {
        const isReturn = (issueTypeInput.value === 'return_supplier');
        document.querySelectorAll('.price-input').forEach(input => {
            if (isReturn) {
                input.removeAttribute('readonly');
                input.classList.remove('is-locked');
            } else {
                input.setAttribute('readonly', 'readonly');
                input.classList.add('is-locked');
                const variantId = input.dataset.variantId;
                if (selectedItems[variantId]) {
                    input.value = selectedItems[variantId].variant.sale_price;
                }
            }
        });
    }

    // ── Search & Picker ──
    pickerInput.addEventListener('focus', function() {
        renderSuggestions(pickerInput.value);
    });

    pickerInput.addEventListener('input', function() {
        renderSuggestions(pickerInput.value);
    });

    function resolveImageUrl(path) {
        if (!path) return 'https://placehold.co/80x80?text=No+Image';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    function renderSuggestions(query) {
        const q = query.trim().toLowerCase();
        let filtered = variants;
        if (q !== '') {
            filtered = variants.filter(v => 
                v.sku.toLowerCase().includes(q) || 
                v.product_name.toLowerCase().includes(q) ||
                v.color_name.toLowerCase().includes(q) ||
                v.size_name.toLowerCase().includes(q)
            );
        }

        pickerPanel.innerHTML = '';
        if (filtered.length === 0) {
            pickerPanel.innerHTML = '<div class="gr-picker-empty">Không tìm thấy biến thể nào phù hợp</div>';
            pickerPanel.hidden = false;
            return;
        }

        filtered.forEach(v => {
            const item = document.createElement('div');
            item.className = 'gr-picker-item';
            const img = `<img src="${resolveImageUrl(v.thumbnail)}" class="gr-picker-thumb" alt="">`;
            item.innerHTML = `
                ${img}
                <div class="gr-picker-info">
                    <div class="gr-picker-name">${v.product_name}</div>
                    <div class="gr-picker-meta">
                        <span class="gr-picker-dot" style="background:${v.color_hex || '#ccc'}"></span>
                        <span>${v.color_name} · ${v.size_name} · ${v.sku}</span>
                    </div>
                </div>
                <div class="gr-picker-stock">Tồn: <b>${v.stock}</b></div>
            `;
            item.addEventListener('click', () => {
                addVariantRow(v);
                pickerPanel.hidden = true;
                pickerInput.value = '';
            });
            pickerPanel.appendChild(item);
        });

        pickerPanel.hidden = false;
    }

    function addVariantRow(v) {
        if (selectedItems[v.id]) {
            const row = document.getElementById(`siRow-${v.id}`);
            const qtyInput = row.querySelector('.qty-input');
            const newQty = parseInt(qtyInput.value) + 1;
            if (newQty <= v.stock) {
                qtyInput.value = newQty;
                qtyInput.dispatchEvent(new Event('input'));
            } else {
                alert(`Không thể vượt quá số lượng tồn kho (${v.stock}) của biến thể này.`);
            }
            return;
        }

        selectedItems[v.id] = { variant: v, quantity: 1, cost_price: v.cost_price, sale_price: v.sale_price };

        const isReturn = (issueTypeInput.value === 'return_supplier');
        const priceReadonly = isReturn ? '' : 'readonly';
        const priceLockedClass = isReturn ? '' : 'is-locked';

        const tr = document.createElement('tr');
        tr.id = `siRow-${v.id}`;
        const img = `<img src="${resolveImageUrl(v.thumbnail)}" class="gr-row-thumb" alt="">`;

        tr.innerHTML = `
            <td>
                <div class="gr-row-product">
                    ${img}
                    <div>
                        <div class="gr-row-name">${v.product_name}</div>
                        <div class="gr-row-sub">
                            <span class="gr-row-dot" style="background:${v.color_hex || '#ccc'}"></span>
                            ${v.color_name} · ${v.size_name} · ${v.sku}
                        </div>
                    </div>
                </div>
                <input type="hidden" name="items[${v.id}][product_variant_id]" value="${v.id}">
            </td>
            <td><b class="text-secondary">${v.stock}</b></td>
            <td>
                <input type="number" name="items[${v.id}][quantity]" class="gr-num-input qty-input" value="1" min="1" max="${v.stock}" required>
                <div class="si-stock-warning" hidden>Vượt quá số lượng tồn</div>
            </td>
            <td>
                <span class="fw-semibold text-muted">${formatMoney(v.cost_price)}đ</span>
                <input type="hidden" name="items[${v.id}][cost_price]" value="${v.cost_price}">
            </td>
            <td>
                <input type="number" name="items[${v.id}][sale_price]" class="gr-num-input price-input ${priceLockedClass}" value="${v.sale_price}" min="0" step="1000" ${priceReadonly} data-variant-id="${v.id}">
            </td>
            <td><span class="gr-row-total total-cost">${formatMoney(v.cost_price)}đ</span></td>
            <td><span class="gr-row-total total-sale">${formatMoney(v.sale_price)}đ</span></td>
            <td>
                <button type="button" class="gr-row-remove"><i class="fa-solid fa-xmark"></i></button>
            </td>
        `;

        // Remove row event listener
        tr.querySelector('.gr-row-remove').addEventListener('click', () => {
            tr.remove();
            delete selectedItems[v.id];
            calculateTotals();
        });

        // Quantity change event listener
        const qtyInput = tr.querySelector('.qty-input');
        const qtyWarning = tr.querySelector('.si-stock-warning');
        qtyInput.addEventListener('input', function() {
            let val = parseInt(qtyInput.value);
            if (isNaN(val) || val < 1) {
                val = 1;
                qtyInput.value = 1;
            }

            if (val > v.stock) {
                qtyInput.classList.add('border-danger');
                qtyWarning.hidden = false;
            } else {
                qtyInput.classList.remove('border-danger');
                qtyWarning.hidden = true;
            }

            selectedItems[v.id].quantity = val;
            updateRowAmounts(tr, v.id);
        });

        // Sale price change event listener (for return supplier)
        const priceInput = tr.querySelector('.price-input');
        priceInput.addEventListener('input', function() {
            let val = parseFloat(priceInput.value);
            if (isNaN(val) || val < 0) {
                val = 0;
                priceInput.value = 0;
            }
            selectedItems[v.id].sale_price = val;
            updateRowAmounts(tr, v.id);
        });

        tableBody.appendChild(tr);
        calculateTotals();
    }

    function updateRowAmounts(row, variantId) {
        const item = selectedItems[variantId];
        const costTotal = item.cost_price * item.quantity;
        const saleTotal = item.sale_price * item.quantity;

        row.querySelector('.total-cost').textContent = formatMoney(costTotal) + 'đ';
        row.querySelector('.total-sale').textContent = formatMoney(saleTotal) + 'đ';

        calculateTotals();
    }

    function calculateTotals() {
        let totalCost = 0;
        let totalSale = 0;
        let hasItems = false;
        let hasErrors = false;

        for (const id in selectedItems) {
            const item = selectedItems[id];
            totalCost += item.cost_price * item.quantity;
            totalSale += item.sale_price * item.quantity;
            hasItems = true;

            if (item.quantity > item.variant.stock) {
                hasErrors = true;
            }
        }

        totalCostVal.textContent = formatMoney(totalCost) + 'đ';
        totalSaleVal.textContent = formatMoney(totalSale) + 'đ';

        if (hasItems) {
            tableWrap.classList.remove('is-empty');
        } else {
            tableWrap.classList.add('is-empty');
        }

        // Disable issue button if stock is insufficient
        if (hasErrors) {
            issueBtn.setAttribute('disabled', 'disabled');
            issueBtn.setAttribute('title', 'Vui lòng giảm số lượng xuất không vượt quá tồn kho.');
        } else {
            issueBtn.removeAttribute('disabled');
            issueBtn.removeAttribute('title');
        }
    }

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    }

    // ── Form submits ──
    document.querySelectorAll('[data-si-modal-action]').forEach(btn => {
        btn.addEventListener('click', function() {
            actionInput.value = btn.dataset.siModalAction;
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear error highlights
        document.querySelectorAll('[data-si-error]').forEach(el => el.textContent = '');

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            return res.json().then(data => {
                if (!res.ok) {
                    if (res.status === 422) {
                        // Display validation errors
                        const errors = data.errors || {};
                        for (const key in errors) {
                            // Map items.xx nested fields to simply items field
                            let errorKey = key;
                            if (key.startsWith('items.')) {
                                errorKey = 'items';
                            }
                            const errorEl = document.querySelector(`[data-si-error="${errorKey}"]`);
                            if (errorEl) {
                                errorEl.textContent = errors[key][0];
                            } else {
                                alert(errors[key][0]);
                            }
                        }
                    } else {
                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    }
                    throw new Error('validation-failed');
                }
                return data;
            });
        })
        .then(data => {
            // Success! Reload or redirect
            if (data.show_url) {
                // Show toast or reload page
                alert(data.message);
                window.location.href = data.table_url || window.location.href;
            }
        })
        .catch(err => {
            if (err.message !== 'validation-failed') {
                console.error(err);
            }
        });
    });

    // Reset form when offcanvas hidden
    modal.addEventListener('hidden.bs.offcanvas', function () {
        form.reset();
        tableBody.innerHTML = '';
        calculateTotals();
        for (const id in selectedItems) {
            delete selectedItems[id];
        }
        closeAllPanels();
        document.querySelectorAll('[data-si-error]').forEach(el => el.textContent = '');
    });

})();
</script>
@endpush
@endonce

