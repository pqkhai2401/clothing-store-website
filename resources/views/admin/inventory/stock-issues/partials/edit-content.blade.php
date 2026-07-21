{{--
    Edit Stock Issue Content — loaded dynamically via AJAX into offcanvas body.
    Variables expected: $stockIssue — Model StockIssue
    $variants — Collection các biến thể còn tồn kho, đã map sẵn field cần thiết.
    $warehouses — Danh sách kho hàng hoạt động.
    $selectedItems — JSON structure of selected items.
--}}

<form method="POST" action="{{ route('admin.stock-issues.update', $stockIssue->id) }}" id="stockIssueEditForm" class="d-flex flex-column h-100">
    @csrf
    @method('PUT')
    <input type="hidden" name="submit_action" id="siEditActionInput" value="draft">

    <div class="offcanvas-body flex-grow-1 overflow-auto">
        {{-- Thông tin chung --}}
        <div class="card si-info-card mb-4">
            <div class="card-header bg-white py-2">
                <span class="fw-bold text-dark" style="font-size:14px;">Thông tin phiếu xuất #{{ $stockIssue->code }}</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Loại xuất kho --}}
                    <div class="col-md-6">
                        <span class="si-inline-label d-block mb-1">Loại xuất kho <span class="text-danger">*</span></span>
                        <input type="hidden" name="issue_type" id="issueTypeInputEdit" value="{{ $stockIssue->issue_type }}" required>
                        <div class="hk-cat-filter si-filter-width" id="issueTypeFilterEdit">
                            <button type="button" class="hk-cat-trigger" id="issueTypeTriggerEdit">
                                <span class="hk-cat-trigger-label" id="issueTypeLabelEdit">
                                    {{ \App\Models\StockIssue::ISSUE_TYPE_LABELS[$stockIssue->issue_type] ?? $stockIssue->issue_type }}
                                </span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="issueTypePanelEdit" hidden style="width:280px;">
                                <div class="hk-cat-list" id="issueTypeListEdit">
                                    @foreach(\App\Models\StockIssue::ISSUE_TYPE_SELECTABLE_LABELS as $value => $label)
                                        <button type="button" class="hk-cat-item {{ $value === $stockIssue->issue_type ? 'is-active' : '' }}"
                                            data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback d-block mt-1" data-si-error="issue_type"></div>
                    </div>

                    {{-- Kho xuất kho — hệ thống hiện chỉ vận hành 1 kho nên gán cố định, không cho chọn --}}
                    <div class="col-md-6">
                        <span class="si-inline-label d-block mb-1">Kho xuất hàng</span>
                        @php
                            $selectedWh = $stockIssue->warehouse ?? $warehouses->firstWhere('is_default', true) ?? $warehouses->first();
                        @endphp
                        <input type="hidden" name="warehouse_id" id="warehouseIdInputEdit" value="{{ $selectedWh?->id }}" required>
                        <div class="si-warehouse-static">
                            <i class="fa-solid fa-warehouse"></i>
                            {{ $selectedWh?->name ?? '—' }}
                        </div>
                        <div class="invalid-feedback d-block mt-1" data-si-error="warehouse_id"></div>
                    </div>

                    {{-- Đơn hàng liên quan (chỉ hiển thị khi xuất bán) --}}
                    <div class="col-md-6" id="orderSelectWrapEdit" style="display: {{ $stockIssue->issue_type === 'sale' ? '' : 'none' }}">
                        <span class="si-inline-label d-block mb-1">Đơn hàng liên quan</span>
                        <input type="hidden" name="order_id" id="orderIdInputEdit" value="{{ $stockIssue->order_id }}">
                        <div class="hk-cat-filter si-filter-width" id="orderIdFilterEdit">
                            <button type="button" class="hk-cat-trigger" id="orderIdTriggerEdit">
                                <span class="hk-cat-trigger-label" id="orderIdLabelEdit">
                                    {{ $stockIssue->order ? ($stockIssue->order->order_code ?? 'Đơn #'.$stockIssue->order_id) : 'Chọn đơn hàng (Tùy chọn)' }}
                                </span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="orderIdPanelEdit" hidden style="width:280px; max-height:250px; overflow-y:auto;">
                                <div class="hk-cat-list" id="orderIdListEdit">
                                    <button type="button" class="hk-cat-item {{ !$stockIssue->order_id ? 'is-active' : '' }}" data-value="" data-label="Chọn đơn hàng (Tùy chọn)">Không liên kết đơn</button>
                                    @php
                                        // Dynamic orders list passed from view
                                        $orders = \App\Models\Order::orderByDesc('id')->get(['id', 'order_code']);
                                    @endphp
                                    @foreach($orders as $order)
                                        <button type="button" class="hk-cat-item {{ $stockIssue->order_id == $order->id ? 'is-active' : '' }}"
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
                        <input type="datetime-local" id="siModalIssuedAtEdit" name="issued_at" class="form-control form-control-sm border-grey" 
                               value="{{ $stockIssue->issued_at ? $stockIssue->issued_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" required style="border-radius: 999px; height: 38px;">
                        <div class="invalid-feedback d-block mt-1" data-si-error="issued_at"></div>
                    </div>

                    {{-- Lý do xuất --}}
                    <div class="col-md-12">
                        @php
                            $isRequired = in_array($stockIssue->issue_type, ['adjustment', 'damaged'], true);
                        @endphp
                        <label for="siModalReasonEdit" class="si-inline-label d-block mb-1" id="siReasonLabelEdit">
                            Lý do xuất kho {!! $isRequired ? '<span class="text-danger">*</span>' : '' !!}
                        </label>
                        <input type="text" id="siModalReasonEdit" name="reason" class="form-control form-control-sm border-grey" 
                               value="{{ $stockIssue->reason }}"
                               placeholder="Ví dụ: Xuất hủy hàng lỗi mốc, kiểm kê kho định kỳ..." style="border-radius: 999px; height: 38px;"
                               {{ $isRequired ? 'required' : '' }}>
                        <div class="invalid-feedback d-block mt-1" data-si-error="reason"></div>
                    </div>

                    {{-- Ghi chú --}}
                    <div class="col-md-12">
                        <span class="si-inline-label d-block mb-1">Ghi chú chi tiết</span>
                        <textarea id="siModalNoteEdit" name="note" rows="2" class="form-control border-grey"
                            placeholder="Thêm ghi chú chi tiết cho phiếu xuất kho..." style="border-radius: 12px;">{{ $stockIssue->note }}</textarea>
                        <div class="invalid-feedback d-block mt-1" data-si-error="note"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sản phẩm xuất kho --}}
        <div class="card mx-3 mb-4">
            <div class="card-header bg-white py-2">
                <span class="fw-bold text-dark" style="font-size:14px;">Sản phẩm xuất kho</span>
            </div>
            <div class="card-body p-3">
                <div class="gr-picker-wrap">
                    <input type="text" class="gr-picker-input" id="siModalPickerInputEdit"
                        placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off" style="border-radius: 999px;">
                    <div class="gr-picker-panel" id="siModalPickerPanelEdit" hidden></div>
                </div>
                <div class="invalid-feedback d-block mt-2" data-si-error="items"></div>

                <div class="gr-table-wrap" id="siModalTableWrapEdit" style="border-radius:12px;">
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
                        <tbody id="siModalTableBodyEdit"></tbody>
                    </table>
                    <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                </div>

                <div class="d-flex flex-column gap-2 mt-3">
                    <div class="gr-summary-bar" style="border-radius:12px;">
                        <span class="gr-summary-label">Tổng giá trị vốn:</span>
                        <span class="gr-summary-value text-secondary" id="siModalTotalCostEdit">0đ</span>
                    </div>
                    <div class="gr-summary-bar" style="border-radius:12px; margin-top:0;">
                        <span class="gr-summary-label">Tổng giá trị bán:</span>
                        <span class="gr-summary-value" id="siModalTotalSaleEdit">0đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas" style="border-radius:999px;">Hủy bỏ</button>
        <button type="submit" class="btn btn-outline-dark fw-semibold" data-si-edit-action="draft" style="border-radius:999px;">
            <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
        </button>
        <button type="submit" class="btn gr-btn-emerald fw-semibold" id="siModalIssueBtnEdit" data-si-edit-action="complete" style="border-radius:999px;">
            <i class="fa-solid fa-check me-1"></i> Hoàn tất xuất kho
        </button>
    </div>
</form>

<script>
(function () {
    const variants = @json($variants);
    const selectedItems = @json($selectedItems);
    const form = document.getElementById('stockIssueEditForm');
    const actionInput = document.getElementById('siEditActionInput');
    const pickerInput = document.getElementById('siModalPickerInputEdit');
    const pickerPanel = document.getElementById('siModalPickerPanelEdit');
    const tableWrap = document.getElementById('siModalTableWrapEdit');
    const tableBody = document.getElementById('siModalTableBodyEdit');
    const totalCostVal = document.getElementById('siModalTotalCostEdit');
    const totalSaleVal = document.getElementById('siModalTotalSaleEdit');
    const issueBtn = document.getElementById('siModalIssueBtnEdit');

    // ── Dropdowns ──
    const issueTypeInput = document.getElementById('issueTypeInputEdit');
    const issueTypeTrigger = document.getElementById('issueTypeTriggerEdit');
    const issueTypeLabel = document.getElementById('issueTypeLabelEdit');
    const issueTypePanel = document.getElementById('issueTypePanelEdit');
    const issueTypeList = document.getElementById('issueTypeListEdit');

    const warehouseIdInput = document.getElementById('warehouseIdInputEdit');

    const orderIdInput = document.getElementById('orderIdInputEdit');
    const orderIdTrigger = document.getElementById('orderIdTriggerEdit');
    const orderIdLabel = document.getElementById('orderIdLabelEdit');
    const orderIdPanel = document.getElementById('orderIdPanelEdit');
    const orderIdList = document.getElementById('orderIdListEdit');

    const orderSelectWrap = document.getElementById('orderSelectWrapEdit');
    const reasonLabel = document.getElementById('siReasonLabelEdit');
    const reasonInput = document.getElementById('siModalReasonEdit');

    function closeAllPanels() {
        issueTypePanel.hidden = true;
        if (orderIdPanel) orderIdPanel.hidden = true;
        pickerPanel.hidden = true;
    }

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

        if (val === 'sale') {
            orderSelectWrap.style.display = '';
        } else {
            orderSelectWrap.style.display = 'none';
            orderIdInput.value = '';
            orderIdLabel.textContent = 'Chọn đơn hàng (Tùy chọn)';
            orderIdList.querySelectorAll('.hk-cat-item').forEach(el => el.classList.remove('is-active'));
            orderIdList.querySelector('[data-value=""]').classList.add('is-active');
        }

        if (val === 'adjustment' || val === 'damaged') {
            reasonLabel.innerHTML = 'Lý do xuất kho <span class="text-danger">*</span>';
            reasonInput.setAttribute('required', 'required');
        } else {
            reasonLabel.innerHTML = 'Lý do xuất kho';
            reasonInput.removeAttribute('required');
        }

        updatePriceEditingState();
        calculateTotals();
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
        document.querySelectorAll('.price-input-edit').forEach(input => {
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

    // Initialize existing items
    for (const id in selectedItems) {
        renderItemRow(selectedItems[id].variant, selectedItems[id].quantity, selectedItems[id].cost_price, selectedItems[id].sale_price);
    }

    // ── Search & Picker ──
    pickerInput.addEventListener('focus', function() {
        renderSuggestions(pickerInput.value);
    });

    pickerInput.addEventListener('input', function() {
        renderSuggestions(pickerInput.value);
    });

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
                if (selectedItems[v.id]) {
                    const row = document.getElementById(`siEditRow-${v.id}`);
                    const qtyInput = row.querySelector('.qty-input');
                    const newQty = parseInt(qtyInput.value) + 1;
                    if (newQty <= v.stock) {
                        qtyInput.value = newQty;
                        qtyInput.dispatchEvent(new Event('input'));
                    } else {
                        window.showAlert(`Không thể vượt quá số lượng tồn kho (${v.stock}) của biến thể này.`, 'Tồn kho không đủ', 'warning');
                    }
                } else {
                    selectedItems[v.id] = { variant: v, quantity: 1, cost_price: v.cost_price, sale_price: v.sale_price };
                    renderItemRow(v, 1, v.cost_price, v.sale_price);
                }
                pickerPanel.hidden = true;
                pickerInput.value = '';
            });
            pickerPanel.appendChild(item);
        });

        pickerPanel.hidden = false;
    }

    function resolveImageUrl(path) {
        if (!path) return 'https://placehold.co/80x80?text=No+Image';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    function renderItemRow(v, qty, costPrice, salePrice) {
        const isReturn = (issueTypeInput.value === 'return_supplier');
        const priceReadonly = isReturn ? '' : 'readonly';
        const priceLockedClass = isReturn ? '' : 'is-locked';

        const tr = document.createElement('tr');
        tr.id = `siEditRow-${v.id}`;
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
                <input type="number" name="items[${v.id}][quantity]" class="gr-num-input qty-input" value="${qty}" min="1" max="${v.stock}" required>
                <div class="si-stock-warning" hidden>Vượt quá số lượng tồn</div>
            </td>
            <td>
                <span class="fw-semibold text-muted">${formatMoney(costPrice)}đ</span>
                <input type="hidden" name="items[${v.id}][cost_price]" value="${costPrice}">
            </td>
            <td>
                <input type="number" name="items[${v.id}][sale_price]" class="gr-num-input price-input-edit ${priceLockedClass}" value="${salePrice}" min="0" step="1000" ${priceReadonly} data-variant-id="${v.id}">
            </td>
            <td><span class="gr-row-total total-cost">${formatMoney(costPrice * qty)}đ</span></td>
            <td><span class="gr-row-total total-sale">${formatMoney(salePrice * qty)}đ</span></td>
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
        const priceInput = tr.querySelector('.price-input-edit');
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

    // ── Action Buttons ──
    document.querySelectorAll('[data-si-edit-action]').forEach(btn => {
        btn.addEventListener('click', function() {
            actionInput.value = btn.dataset.siEditAction;
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear errors
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
                        const errors = data.errors || {};
                        for (const key in errors) {
                            let errorKey = key;
                            if (key.startsWith('items.')) {
                                errorKey = 'items';
                            }
                            const errorEl = document.querySelector(`[data-si-error="${errorKey}"]`);
                            if (errorEl) {
                                errorEl.textContent = errors[key][0];
                            } else {
                                window.showAlert(errors[key][0], 'Lỗi', 'danger');
                            }
                        }
                    } else {
                        window.showAlert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'Lỗi', 'danger');
                    }
                    throw new Error('validation-failed');
                }
                return data;
            });
        })
        .then(data => {
            window.showAlert(data.message, 'Thông báo', 'success');
            window.location.href = data.table_url || window.location.href;
        })
        .catch(err => {
            if (err.message !== 'validation-failed') {
                console.error(err);
            }
        });
    });
})();
</script>

