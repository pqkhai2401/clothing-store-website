{{--
    Partial nội dung chỉnh sửa phiếu nhập kho.
    Variables expected:
    - $goodsReceipt: phiếu nhập kho đang sửa (nháp).
    - $suppliers: danh sách nhà cung cấp.
    - $variants: danh sách biến thể sản phẩm.
    - $selectedItems: mảng các sản phẩm đã có sẵn trong phiếu.
--}}

<form method="POST" action="{{ route('admin.goods-receipts.update', $goodsReceipt->id) }}" id="goodsReceiptEditAjaxForm" class="d-flex flex-column h-100">
    @csrf
    @method('PUT')
    <input type="hidden" name="action" id="grEditModalActionInput" value="draft">

    <div class="offcanvas-body flex-grow-1 overflow-auto">
        <div class="card gr-info-card mb-4">
            <div class="card-header">
                <span class="fw-bold" style="font-size:14px;">Thông tin phiếu nhập</span>
            </div>
            <div class="card-body p-3">
                {{-- Nguồn nhập --}}
                <div class="edit-field mb-3" id="grEditModalSourceTypeFilterWrap">
                    <label class="gr-inline-label d-block mb-1">Nguồn nhập <span class="text-danger">*</span></label>
                    <input type="hidden" name="source_type" id="grEditModalSourceType" value="{{ $goodsReceipt->source_type }}">
                    <div class="hk-cat-filter gr-source-type-filter" id="grEditModalSourceTypeFilter">
                        <button type="button" class="hk-cat-trigger" id="grEditModalSourceTypeTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="grEditModalSourceTypeLabel">
                                {{ $goodsReceipt->source_type === 'internal' ? '🏠 Nhập nội bộ (kiểm kê / điều chỉnh)' : '🏭 Nhập từ nhà cung cấp' }}
                            </span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="grEditModalSourceTypePanel" hidden>
                            <div class="hk-cat-list" id="grEditModalSourceTypeList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $goodsReceipt->source_type === 'supplier' ? 'is-active' : '' }}" data-value="supplier" data-label="🏭 Nhập từ nhà cung cấp">
                                    🏭 Nhập từ nhà cung cấp
                                </button>
                                <button type="button" class="hk-cat-item {{ $goodsReceipt->source_type === 'internal' ? 'is-active' : '' }}" data-value="internal" data-label="🏠 Nhập nội bộ (kiểm kê / điều chỉnh)">
                                    🏠 Nhập nội bộ (kiểm kê / điều chỉnh)
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="source_type"></div>
                </div>

                {{-- Loại nhập --}}
                <div class="edit-field mb-3" id="grEditModalReceiptTypeFilterWrap">
                    <label class="gr-inline-label d-block mb-1">Loại nhập <span class="text-danger">*</span></label>
                    <input type="hidden" name="receipt_type" id="grEditModalReceiptType" value="{{ $goodsReceipt->receipt_type }}">
                    <div class="hk-cat-filter gr-receipt-type-filter" id="grEditModalReceiptTypeFilter">
                        <button type="button" class="hk-cat-trigger" id="grEditModalReceiptTypeTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="grEditModalReceiptTypeLabel">
                                @php
                                    $receiptLabels = [
                                        'purchase' => '📦 Nhập hàng nhà cung cấp',
                                        'return' => '🔄 Nhập trả hàng',
                                        'adjustment' => '⚖️ Điều chỉnh kiểm kê',
                                        'initial' => '🗂️ Nhập tồn đầu kỳ',
                                    ];
                                @endphp
                                {{ $receiptLabels[$goodsReceipt->receipt_type] ?? '📦 Nhập hàng nhà cung cấp' }}
                            </span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="grEditModalReceiptTypePanel" hidden>
                            <div class="hk-cat-list" id="grEditModalReceiptTypeList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $goodsReceipt->receipt_type === 'purchase' ? 'is-active' : '' }}" data-value="purchase" data-label="📦 Nhập hàng nhà cung cấp">
                                    📦 Nhập hàng nhà cung cấp
                                </button>
                                <button type="button" class="hk-cat-item {{ $goodsReceipt->receipt_type === 'return' ? 'is-active' : '' }}" data-value="return" data-label="🔄 Nhập trả hàng">
                                    🔄 Nhập trả hàng
                                </button>
                                <button type="button" class="hk-cat-item {{ $goodsReceipt->receipt_type === 'adjustment' ? 'is-active' : '' }}" data-value="adjustment" data-label="⚖️ Điều chỉnh kiểm kê">
                                    ⚖️ Điều chỉnh kiểm kê
                                </button>
                                <button type="button" class="hk-cat-item {{ $goodsReceipt->receipt_type === 'initial' ? 'is-active' : '' }}" data-value="initial" data-label="🗂️ Nhập tồn đầu kỳ">
                                    🗂️ Nhập tồn đầu kỳ
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="receipt_type"></div>
                </div>

                {{-- Kho nhận hàng --}}
                <div class="edit-field mb-3">
                    <label class="gr-inline-label d-block mb-1">Kho nhận <span class="text-danger">*</span></label>
                    <input type="hidden" name="warehouse_id" id="grEditModalWarehouseId" value="{{ $goodsReceipt->warehouse_id }}">
                    <div class="hk-cat-filter gr-warehouse-filter" id="grEditModalWarehouseFilter">
                        <button type="button" class="hk-cat-trigger" id="grEditModalWarehouseTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="grEditModalWarehouseLabel">
                                @php
                                    $selectedWh = $warehouses->firstWhere('id', $goodsReceipt->warehouse_id);
                                @endphp
                                {{ $selectedWh ? $selectedWh->full_name . ($selectedWh->is_default ? ' (Mặc định)' : '') : '— Chọn kho nhận —' }}
                            </span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="grEditModalWarehousePanel" hidden>
                            <div class="hk-cat-list" id="grEditModalWarehouseList" role="listbox">
                                @foreach($warehouses as $warehouse)
                                    <button type="button" class="hk-cat-item {{ $goodsReceipt->warehouse_id == $warehouse->id ? 'is-active' : '' }}"
                                        data-value="{{ $warehouse->id }}"
                                        data-label="{{ $warehouse->full_name }}{{ $warehouse->is_default ? ' (Mặc định)' : '' }}">
                                        <span class="gr-wh-dot {{ $warehouse->is_default ? 'gr-wh-dot--default' : '' }}"></span>
                                        {{ $warehouse->full_name }}
                                        @if($warehouse->is_default)
                                            <span class="badge bg-success ms-auto" style="font-size:10px;">Mặc định</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="warehouse_id"></div>
                </div>

                {{-- Ngày nhập kho --}}
                <div class="edit-field mb-3">
                    <label for="grEditModalReceivedAt" class="gr-inline-label d-block mb-1">Ngày nhập kho <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="grEditModalReceivedAt" name="received_at" class="form-control form-control-sm"
                        value="{{ $goodsReceipt->received_at ? $goodsReceipt->received_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" required>
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="received_at"></div>
                </div>

                {{-- Nhà cung cấp (ẩn khi nội bộ) --}}
                <div class="edit-field mb-3" id="grEditModalSupplierField">
                    <label class="gr-inline-label d-block mb-1">Nhà cung cấp <span class="text-danger" id="grEditModalSupplierRequired">*</span></label>
                    <input type="hidden" name="supplier_id" id="grEditModalSupplierSelect" value="{{ $goodsReceipt->supplier_id }}">
                    <div class="hk-cat-filter gr-supplier-filter" id="grEditModalSupplierFilter">
                        <button type="button" class="hk-cat-trigger" id="grEditModalSupplierTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="grEditModalSupplierLabel">
                                @php
                                    $selectedSup = $suppliers->firstWhere('id', $goodsReceipt->supplier_id);
                                @endphp
                                {{ $selectedSup ? $selectedSup->name : '— Chọn nhà cung cấp —' }}
                            </span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="grEditModalSupplierPanel" hidden>
                            <div class="hk-cat-list" id="grEditModalSupplierList" role="listbox">
                                <button type="button" class="hk-cat-item {{ !$goodsReceipt->supplier_id ? 'is-active' : '' }}" data-value="" data-label="— Chọn nhà cung cấp —">
                                    — Chọn nhà cung cấp —
                                </button>
                                @foreach($suppliers as $supplier)
                                    <button type="button" class="hk-cat-item {{ $goodsReceipt->supplier_id == $supplier->id ? 'is-active' : '' }}" data-value="{{ $supplier->id }}" data-label="{{ $supplier->name }}">
                                        {{ $supplier->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="supplier_id"></div>
                </div>

                {{-- Lý do nhập (hiện khi nội bộ hoặc điều chỉnh) --}}
                <div class="edit-field mb-3" id="grEditModalReasonField" style="display:none;">
                    <label for="grEditModalReceiptReason" class="gr-inline-label d-block mb-1">Lý do nhập</label>
                    <input type="text" id="grEditModalReceiptReason" name="receipt_reason" class="form-control form-control-sm"
                        placeholder="Ví dụ: Cân bằng tồn kho sau kiểm kê..."
                        value="{{ $goodsReceipt->receipt_reason }}">
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="receipt_reason"></div>
                </div>

                <div class="edit-field mb-0">
                    <label for="grEditModalNote">Ghi chú</label>
                    <textarea id="grEditModalNote" name="note" rows="2" class="form-control form-control-sm"
                        placeholder="Ghi chú nội bộ cho phiếu nhập kho...">{{ $goodsReceipt->note }}</textarea>
                    <div class="invalid-feedback d-block mt-1" data-gr-edit-error="note"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="fw-bold" style="font-size:14px;">Sản phẩm nhập kho</span>
            </div>
            <div class="card-body p-3">
                <div class="gr-picker-wrap">
                    <input type="text" class="gr-picker-input" id="grEditModalPickerInput"
                        placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off">
                    <div class="gr-picker-panel" id="grEditModalPickerPanel" hidden></div>
                </div>
                <div class="invalid-feedback d-block mt-2" data-gr-edit-error="items"></div>

                <div class="gr-table-wrap is-empty" id="grEditModalTableWrap">
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
                        <tbody id="grEditModalTableBody"></tbody>
                    </table>
                    <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                </div>

                <div class="gr-summary-bar d-flex justify-content-between align-items-center flex-wrap">
                    <div class="gr-summary-item me-4">
                        <span class="gr-summary-label">Tổng số lượng:</span>
                        <span class="gr-summary-value fw-bold text-dark" id="grEditModalTotalQuantity" style="font-size:16px;">0</span>
                    </div>
                    <div class="gr-summary-item">
                        <span class="gr-summary-label">Tổng giá trị phiếu nhập:</span>
                        <span class="gr-summary-value" id="grEditModalTotalAmount">0đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas">Hủy bỏ</button>
        <button type="submit" class="btn btn-outline-dark fw-semibold" data-gr-edit-modal-action="draft">
            <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
        </button>
        <button type="submit" class="btn gr-btn-emerald fw-semibold" data-gr-edit-modal-action="complete" id="grEditModalBtnComplete">
            <i class="fa-solid fa-check me-1"></i> Hoàn tất nhập kho
        </button>
    </div>
</form>

<script>
(function () {
    const variants = @json($variants);
    const offcanvas = document.getElementById('goodsReceiptEditOffcanvas');
    const form = document.getElementById('goodsReceiptEditAjaxForm');
    const actionInput = document.getElementById('grEditModalActionInput');
    const sourceTypeEl = document.getElementById('grEditModalSourceType');
    const sourceTypeFilter = document.getElementById('grEditModalSourceTypeFilter');
    const sourceTypeTrigger = document.getElementById('grEditModalSourceTypeTrigger');
    const sourceTypePanel = document.getElementById('grEditModalSourceTypePanel');
    const sourceTypeLabel = document.getElementById('grEditModalSourceTypeLabel');
    const sourceTypeList = document.getElementById('grEditModalSourceTypeList');
    const receiptTypeEl = document.getElementById('grEditModalReceiptType');
    const receiptTypeFilter = document.getElementById('grEditModalReceiptTypeFilter');
    const receiptTypeTrigger = document.getElementById('grEditModalReceiptTypeTrigger');
    const receiptTypePanel = document.getElementById('grEditModalReceiptTypePanel');
    const receiptTypeLabel = document.getElementById('grEditModalReceiptTypeLabel');
    const receiptTypeList = document.getElementById('grEditModalReceiptTypeList');
    const supplierField = document.getElementById('grEditModalSupplierField');
    const supplierSelect = document.getElementById('grEditModalSupplierSelect');
    const supplierRequired = document.getElementById('grEditModalSupplierRequired');
    const supplierFilter = document.getElementById('grEditModalSupplierFilter');
    const supplierTrigger = document.getElementById('grEditModalSupplierTrigger');
    const supplierPanel = document.getElementById('grEditModalSupplierPanel');
    const supplierLabel = document.getElementById('grEditModalSupplierLabel');
    const supplierList = document.getElementById('grEditModalSupplierList');
    const warehouseHiddenEl = document.getElementById('grEditModalWarehouseId');
    const warehouseTrigger = document.getElementById('grEditModalWarehouseTrigger');
    const warehousePanel = document.getElementById('grEditModalWarehousePanel');
    const warehouseLabel = document.getElementById('grEditModalWarehouseLabel');
    const warehouseList = document.getElementById('grEditModalWarehouseList');
    const warehouseFilter = document.getElementById('grEditModalWarehouseFilter');
    const reasonField = document.getElementById('grEditModalReasonField');
    const receiptReasonEl = document.getElementById('grEditModalReceiptReason');
    const pickerInput = document.getElementById('grEditModalPickerInput');
    const pickerPanel = document.getElementById('grEditModalPickerPanel');
    const tableWrap = document.getElementById('grEditModalTableWrap');
    const tableBody = document.getElementById('grEditModalTableBody');
    const totalEl = document.getElementById('grEditModalTotalAmount');
    const tableArea = document.querySelector('[data-admin-table-area]');

    let selected = @json($selectedItems ?? (object)[]);

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
        document.querySelectorAll('[data-gr-edit-error]').forEach(el => { el.textContent = ''; });
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const normalized = key.startsWith('items') ? 'items' : key;
            const target = document.querySelector(`[data-gr-edit-error="${normalized}"]`);
            if (target) target.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    function setAction(act) {
        actionInput.value = act;
    }

    // ── Custom dropdowns helper ──────────────────────────────
    function closeAllCustomDropdowns() {
        if (warehousePanel) warehousePanel.hidden = true;
        warehouseTrigger?.classList.remove('is-open');
        warehouseTrigger?.setAttribute('aria-expanded', 'false');

        if (sourceTypePanel) sourceTypePanel.hidden = true;
        sourceTypeTrigger?.classList.remove('is-open');
        sourceTypeTrigger?.setAttribute('aria-expanded', 'false');

        if (receiptTypePanel) receiptTypePanel.hidden = true;
        receiptTypeTrigger?.classList.remove('is-open');
        receiptTypeTrigger?.setAttribute('aria-expanded', 'false');

        if (supplierPanel) supplierPanel.hidden = true;
        supplierTrigger?.classList.remove('is-open');
        supplierTrigger?.setAttribute('aria-expanded', 'false');
    }

    // ── Warehouse dropdown ──────────────────────────────────
    warehouseTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = warehousePanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            warehousePanel.hidden = false;
            warehouseTrigger.classList.add('is-open');
            warehouseTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    warehouseList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        warehouseHiddenEl.value = btn.dataset.value || '';
        warehouseLabel.textContent = btn.dataset.label || '— Chọn kho nhận —';
        warehouseList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();
        checkFormValidity();
    });

    // ── Source Type dropdown ────────────────────────────────
    sourceTypeTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = sourceTypePanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            sourceTypePanel.hidden = false;
            sourceTypeTrigger.classList.add('is-open');
            sourceTypeTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    sourceTypeList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        sourceTypeEl.value = btn.dataset.value || '';
        sourceTypeLabel.textContent = btn.dataset.label || '';
        sourceTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();

        syncReceiptTypeOptions();
        updateFieldVisibility();
        checkFormValidity();
    });

    // ── Receipt Type dropdown ────────────────────────────────
    receiptTypeTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = receiptTypePanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            receiptTypePanel.hidden = false;
            receiptTypeTrigger.classList.add('is-open');
            receiptTypeTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    receiptTypeList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn || btn.disabled) return;
        receiptTypeEl.value = btn.dataset.value || '';
        receiptTypeLabel.textContent = btn.dataset.label || '';
        receiptTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();

        updateFieldVisibility();
        checkFormValidity();
    });

    // ── Supplier dropdown ───────────────────────────────────
    supplierTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = supplierPanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            supplierPanel.hidden = false;
            supplierTrigger.classList.add('is-open');
            supplierTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    supplierList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        supplierSelect.value = btn.dataset.value || '';
        supplierLabel.textContent = btn.dataset.label || '— Chọn nhà cung cấp —';
        supplierList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();
        checkFormValidity();
    });

    document.addEventListener('click', function (e) {
        if (warehouseFilter && !warehouseFilter.contains(e.target) &&
            sourceTypeFilter && !sourceTypeFilter.contains(e.target) &&
            receiptTypeFilter && !receiptTypeFilter.contains(e.target) &&
            supplierFilter && !supplierFilter.contains(e.target)) {
            closeAllCustomDropdowns();
        }
    });

    function syncReceiptTypeOptions() {
        const isInternal = (sourceTypeEl?.value === 'internal');
        const purchaseBtn = receiptTypeList?.querySelector('button[data-value="purchase"]');
        if (!purchaseBtn) return;
        if (isInternal) {
            purchaseBtn.disabled = true;
            purchaseBtn.style.opacity = '0.5';
            purchaseBtn.style.pointerEvents = 'none';
            if (receiptTypeEl.value === 'purchase') {
                receiptTypeEl.value = 'adjustment';
                const adjustBtn = receiptTypeList?.querySelector('button[data-value="adjustment"]');
                if (adjustBtn) {
                    receiptTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === adjustBtn));
                    receiptTypeLabel.textContent = adjustBtn.dataset.label;
                }
            }
        } else {
            purchaseBtn.disabled = false;
            purchaseBtn.style.opacity = '';
            purchaseBtn.style.pointerEvents = '';
        }
    }

    function updateFieldVisibility() {
        const isInternal = (sourceTypeEl?.value === 'internal');
        const isAdjustmentOrReturn = ['adjustment', 'return', 'initial'].includes(receiptTypeEl?.value);

        if (supplierField) {
            supplierField.style.display = isInternal ? 'none' : '';
        }
        if (isInternal) {
            if (supplierSelect) {
                supplierSelect.value = '';
                supplierSelect.removeAttribute('required');
            }
            if (supplierRequired) supplierRequired.style.display = 'none';
            // Reset custom selection to default option
            if (supplierList && supplierLabel) {
                const defaultItem = supplierList.querySelector('button[data-value=""]');
                if (defaultItem) {
                    supplierList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === defaultItem));
                    supplierLabel.textContent = defaultItem.dataset.label;
                }
            }
        } else {
            if (supplierSelect) {
                supplierSelect.setAttribute('required', '');
            }
            if (supplierRequired) supplierRequired.style.display = '';
        }

        if (reasonField) {
            reasonField.style.display = (isInternal || isAdjustmentOrReturn) ? '' : 'none';
        }
    }

    // Run initial sync and visibility check
    syncReceiptTypeOptions();
    updateFieldVisibility();

    /* ── Picker & Product Autocomplete ── */
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
                    <div class="gr-picker-name" style="font-size: 13px;">
                        ${esc(v.product_name)} - ${esc(v.color_name)} - Size ${esc(v.size_name)} - SKU ${esc(v.sku)}
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

    /* ── Table Draw & Quantities ── */
    function addVariant(variantId) {
        const variant = variants.find(v => String(v.id) === String(variantId));
        if (!variant) return;

        if (selected[variantId]) {
            alert('Sản phẩm này đã có trong phiếu nhập.');
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
            const item = selected[variantId];
            const variant = item.variant;
            const lineTotal = item.quantity * item.cost_price;

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
                        name="items[${idx}][quantity]" value="${item.quantity}">
                </td>
                <td>
                    <input type="number" min="0" step="1000" class="gr-num-input" data-field="cost_price"
                        name="items[${idx}][cost_price]" value="${item.cost_price}">
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

    const totalQtyEl = document.getElementById('grEditModalTotalQuantity');
    const btnComplete = document.getElementById('grEditModalBtnComplete');
    const receivedAtEl = document.getElementById('grEditModalReceivedAt');

    function checkFormValidity() {
        if (!btnComplete) return;

        let hasError = false;

        // 1. Chưa chọn kho nhận
        if (!warehouseHiddenEl || !warehouseHiddenEl.value) hasError = true;

        // 2. Chưa chọn ngày nhập kho
        if (!receivedAtEl || !receivedAtEl.value) hasError = true;

        // 3. Nếu nguồn nhập là nhà cung cấp mà chưa chọn nhà cung cấp
        const isInternal = sourceTypeEl.value === 'internal';
        if (!isInternal && (!supplierSelect || !supplierSelect.value)) hasError = true;

        // 4. Chưa có sản phẩm nào
        const items = Object.values(selected);
        if (items.length === 0) {
            hasError = true;
        } else {
            // 5. Có sản phẩm số lượng <= 0 hoặc đơn giá nhập < 0
            const invalidItem = items.find(item => item.quantity <= 0 || item.cost_price < 0);
            if (invalidItem) hasError = true;
        }

        if (hasError) {
            btnComplete.setAttribute('disabled', 'disabled');
        } else {
            btnComplete.removeAttribute('disabled');
        }
    }

    function updateTotal() {
        const total = Object.values(selected).reduce((sum, item) => sum + item.quantity * item.cost_price, 0);
        const qty = Object.values(selected).reduce((sum, item) => sum + item.quantity, 0);
        totalEl.textContent = money(total);
        if (totalQtyEl) totalQtyEl.textContent = qty.toLocaleString('vi-VN');
        checkFormValidity();
    }

    // Gắn sự kiện thay đổi để checkFormValidity
    if (receivedAtEl) receivedAtEl.addEventListener('input', checkFormValidity);

    // Chạy kiểm tra ban đầu
    checkFormValidity();

    async function refreshInboundTable(url) {
        if (!tableArea) return;
        const res = await fetch(url || '{{ route('admin.goods-receipts.list', ['tab' => 'inbound']) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.html) tableArea.innerHTML = data.html;
    }

    document.querySelectorAll('[data-gr-edit-modal-action]').forEach(btn => {
        btn.addEventListener('click', function () { setAction(this.dataset.grEditModalAction || this.getAttribute('data-gr-edit-modal-action')); });
    });

    // Vẽ lại bảng ngay khi nạp partial
    renderTable();

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        const action = actionInput.value;

        if (Object.keys(selected).length === 0) {
            showErrors({ items: ['Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập kho.'] });
            return;
        }

        const isInternal = sourceTypeEl.value === 'internal';
        if (!isInternal && !supplierSelect.value) {
            showErrors({ supplier_id: ['Vui lòng chọn nhà cung cấp.'] });
            return;
        }

        if (action === 'complete') {
            const isConfirmed = confirm('Bạn có chắc chắn muốn hoàn tất nhập kho? Sau khi hoàn tất, phiếu sẽ được cộng tồn kho và không thể chỉnh sửa trực tiếp.');
            if (!isConfirmed) {
                return;
            }
        }

        const submitter = e.submitter;
        submitter?.setAttribute('disabled', 'disabled');

        try {
            const res = await fetch('{{ route('admin.goods-receipts.update', $goodsReceipt->id) }}', {
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
                showErrors((data && data.errors) || { items: [(data && data.message) || 'Không thể cập nhật phiếu nhập kho.'] });
                return;
            }

            bootstrap.Offcanvas.getOrCreateInstance(offcanvas).hide();
            await refreshInboundTable(data.table_url);
            showToast(data.message || `Cập nhật phiếu nhập kho "${data.code}" thành công.`, 'success');
        } catch (err) {
            showToast('Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng và thử lại.', 'error');
        } finally {
            submitter?.removeAttribute('disabled');
        }
    });
})();
</script>
