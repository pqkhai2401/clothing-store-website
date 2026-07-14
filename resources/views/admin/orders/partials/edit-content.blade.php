{{--
    Nội dung form "Sửa đơn hàng" — nạp qua AJAX vào offcanvas (partials/edit-offcanvas.blade.php).
    Variables expected: $order, $scope ('full'|'limited'), $addresses (Collection<Address> của khách),
    $items (mảng item hiện tại của đơn, chỉ cần khi $scope === 'full').
--}}
@php
    $orderBadgeCss = [
        'pending'    => 'order-badge--pending',
        'processing' => 'order-badge--processing',
        'shipping'   => 'order-badge--shipping',
        'completed'  => 'order-badge--completed',
        'cancelled'  => 'order-badge--cancelled',
    ];
    $selectedAddressId = old('address_id', $order->address_id);
    $jsItems = $items->map(fn ($i) => [
        'id'         => $i['product_variant_id'],
        'name'       => $i['product_name'],
        'color'      => $i['color'],
        'size'       => $i['size'],
        'unit_price' => $i['unit_price'],
        'stock'      => $i['stock'],
        'quantity'   => $i['quantity'],
    ])->values();
@endphp

<form id="editOrderForm" class="d-flex flex-column h-100"
      action="{{ route('admin.orders.updateContent', $order->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="offcanvas-header border-bottom">
        <div>
            <h2 class="offcanvas-title mb-0">Sửa đơn hàng</h2>
            <p class="mb-0 text-muted" style="font-size:13px;">
                <span class="order-code">{{ $order->order_code ?? '#'.$order->id }}</span>
                <span class="order-badge {{ $orderBadgeCss[$order->status] ?? '' }} ms-1">
                    {{ \App\Http\Controllers\Admin\OrderController::STATUS_LABELS[$order->status] ?? $order->status }}
                </span>
            </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>

    <div class="offcanvas-body flex-grow-1 overflow-auto" data-edit-errors-anchor>
        <div class="alert alert-danger d-none" data-edit-errors-summary></div>

        @if($scope === 'limited')
            <div class="alert alert-secondary" style="font-size:12.5px;">
                @if($order->status === 'processing')
                    Đơn đã chuyển "Đang xử lý" (đã trừ kho) nên chỉ có thể cập nhật thông tin giao hàng.
                @else
                    Đơn thanh toán online đã tạo link/QR thanh toán nên chỉ có thể cập nhật thông tin giao hàng,
                    không thể sửa sản phẩm hay phí vận chuyển.
                @endif
            </div>
        @endif

        {{-- Địa chỉ giao hàng --}}
        <div class="card edit-card shadow-sm mb-4">
            <div class="card-header"><span class="fw-bold" style="font-size:14px;">Địa chỉ giao hàng</span></div>
            <div class="card-body p-4">
                <div class="edit-field">
                    <label>Địa chỉ đã lưu</label>
                    <input type="hidden" name="address_id" id="oeAddressId" value="{{ $selectedAddressId }}">
                    <div class="hk-cat-filter oc-dropdown" id="oeAddressDrop">
                        <button type="button" class="hk-cat-trigger" id="oeAddressTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="oeAddressLabel">— Chọn địa chỉ —</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="oeAddressPanel" hidden>
                            <div class="hk-cat-list" id="oeAddressList" role="listbox">
                                @foreach($addresses as $a)
                                    @php
                                        $addrLabel = collect([$a->apartment_number, $a->ward, $a->district, $a->city])->filter()->implode(', ');
                                    @endphp
                                    <button type="button" class="hk-cat-item {{ (string) $selectedAddressId === (string) $a->id ? 'is-active' : '' }}"
                                        data-value="{{ $a->id }}" data-label="{{ $addrLabel }}">{{ $addrLabel }}</button>
                                @endforeach
                                <button type="button" class="hk-cat-item {{ $selectedAddressId ? '' : 'is-active' }}"
                                    data-value="__new__" data-label="+ Thêm địa chỉ mới">+ Thêm địa chỉ mới</button>
                            </div>
                        </div>
                    </div>
                    @error('address_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div id="oeNewAddressFields" class="d-none">
                    <div class="row g-3">
                        <div class="col-md-6 edit-field mb-0">
                            <label>Tỉnh/Thành phố <span class="text-danger">*</span></label>
                            <input type="text" name="new_address[city]" class="form-control @error('new_address.city') is-invalid @enderror" value="{{ old('new_address.city') }}">
                            @error('new_address.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 edit-field mb-0">
                            <label>Quận/Huyện</label>
                            <input type="text" name="new_address[district]" class="form-control" value="{{ old('new_address.district') }}">
                        </div>
                        <div class="col-md-6 edit-field mb-0">
                            <label>Phường/Xã <span class="text-danger">*</span></label>
                            <input type="text" name="new_address[ward]" class="form-control @error('new_address.ward') is-invalid @enderror" value="{{ old('new_address.ward') }}">
                            @error('new_address.ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 edit-field mb-0">
                            <label>Địa chỉ cụ thể <span class="text-danger">*</span></label>
                            <input type="text" name="new_address[apartment_number]" class="form-control @error('new_address.apartment_number') is-invalid @enderror" value="{{ old('new_address.apartment_number') }}">
                            @error('new_address.apartment_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="edit-field mb-0">
                    <label>Số điện thoại liên hệ <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $order->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Sản phẩm --}}
        <div class="card edit-card shadow-sm mb-4">
            <div class="card-header"><span class="fw-bold" style="font-size:14px;">Sản phẩm</span></div>
            <div class="card-body p-4">
                @if($scope === 'full')
                    <div class="edit-field oc-search-wrap">
                        <label>Tìm sản phẩm (tên hoặc SKU)</label>
                        <input type="text" id="oeProductSearch" class="form-control" placeholder="Nhập tên sản phẩm hoặc SKU..." autocomplete="off">
                        <div class="oc-search-results d-none" id="oeProductResults"></div>
                    </div>

                    @error('items') <div class="text-danger mb-2" style="font-size:13px;">{{ $message }}</div> @enderror

                    <div class="table-responsive">
                        <table class="table oc-items-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th style="width:90px;">Số lượng</th>
                                    <th>Thành tiền</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="oeItemsBody">
                                <tr id="oeEmptyItemsRow">
                                    <td colspan="5" class="oc-empty-items">Chưa có sản phẩm nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="oeItemsHidden"></div>
                @else
                    <div class="table-responsive">
                        <table class="table oc-items-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th style="width:90px;">Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item['product_name'] }} {{ $item['color'] ? '· '.$item['color'] : '' }} {{ $item['size'] ? '· '.$item['size'] : '' }}</td>
                                        <td>{{ number_format($item['unit_price'], 0, ',', '.') }}đ</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>{{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Phí vận chuyển & Tổng cộng --}}
        <div class="card edit-card shadow-sm mb-4">
            <div class="card-header"><span class="fw-bold" style="font-size:14px;">Phí vận chuyển & Tổng cộng</span></div>
            <div class="card-body p-4">
                @if($scope === 'full')
                    <div class="edit-field">
                        <label>Phí vận chuyển</label>
                        <input type="number" name="shipping_fee" id="oeShippingFee" min="0" step="1000"
                            class="form-control @error('shipping_fee') is-invalid @enderror"
                            value="{{ old('shipping_fee', (float) $order->shipping_fee) }}">
                        @error('shipping_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="oc-summary-row">
                        <span>Tạm tính</span>
                        <span id="oeSubtotal">0đ</span>
                    </div>
                    <div class="oc-summary-row">
                        <span>Phí vận chuyển</span>
                        <span id="oeSummaryShipping">0đ</span>
                    </div>
                    <div class="oc-summary-row total mb-0">
                        <span>Tổng tiền</span>
                        <span id="oeTotal">0đ</span>
                    </div>
                @else
                    <div class="oc-summary-row">
                        <span>Phí vận chuyển</span>
                        <span>{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="oc-summary-row total mb-0">
                        <span>Tổng tiền</span>
                        <span>{{ number_format((float) $order->total_money, 0, ',', '.') }}đ</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Ghi chú --}}
        <div class="card edit-card shadow-sm mb-0">
            <div class="card-header"><span class="fw-bold" style="font-size:14px;">Ghi chú</span></div>
            <div class="card-body p-4">
                <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3"
                    placeholder="Ghi chú nội bộ (không bắt buộc)">{{ old('note', $order->note) }}</textarea>
                @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas">Hủy bỏ</button>
        <button type="submit" class="btn btn-primary fw-semibold" id="editOrderSubmitBtn">
            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi
        </button>
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('editOrderForm');
    if (!form || form.dataset.wired === '1') return;
    form.dataset.wired = '1';

    const scope = @json($scope);
    const money = (n) => Number(n || 0).toLocaleString('vi-VN') + 'đ';

    /* ── Dropdown địa chỉ (bo viền) ── */
    (function () {
        const root    = document.getElementById('oeAddressDrop');
        const trigger = document.getElementById('oeAddressTrigger');
        const panel   = document.getElementById('oeAddressPanel');
        const label   = document.getElementById('oeAddressLabel');
        const list    = document.getElementById('oeAddressList');
        const hidden  = document.getElementById('oeAddressId');
        const newFields = document.getElementById('oeNewAddressFields');
        if (!root) return;

        function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
        function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

        trigger.addEventListener('click', () => panel.hidden ? open() : close());

        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.hk-cat-item');
            if (!btn) return;
            list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            label.textContent = btn.dataset.label;

            if (btn.dataset.value === '__new__') {
                hidden.value = '';
                newFields.classList.remove('d-none');
            } else {
                hidden.value = btn.dataset.value;
                newFields.classList.add('d-none');
            }
            close();
        });

        document.addEventListener('click', function (e) {
            if (!panel.hidden && !root.contains(e.target)) close();
        });

        const activeBtn = list.querySelector('.hk-cat-item.is-active');
        if (activeBtn) label.textContent = activeBtn.dataset.label;
        if (!hidden.value) newFields.classList.remove('d-none');
    }());

    if (scope !== 'full') return;

    /* ── Danh sách sản phẩm trong đơn (thêm/xóa/đổi số lượng) ── */
    const items = @json($jsItems);

    function addItem(variant) {
        const existing = items.find(i => i.id === variant.id);
        if (existing) {
            existing.quantity = Math.min(existing.quantity + 1, variant.stock || existing.quantity + 1);
        } else {
            items.push({
                id: variant.id,
                name: variant.product_name,
                color: variant.color,
                size: variant.size,
                unit_price: variant.unit_price,
                stock: variant.stock,
                quantity: 1,
            });
        }
        renderItems();
    }

    function renderItems() {
        const body = document.getElementById('oeItemsBody');
        const hiddenWrap = document.getElementById('oeItemsHidden');

        if (!items.length) {
            body.innerHTML = '<tr id="oeEmptyItemsRow"><td colspan="5" class="oc-empty-items">Chưa có sản phẩm nào.</td></tr>';
            hiddenWrap.innerHTML = '';
            updateSummary();
            return;
        }

        body.innerHTML = items.map((item, idx) => `
            <tr>
                <td>${item.name} ${item.color ? '· ' + item.color : ''} ${item.size ? '· ' + item.size : ''}</td>
                <td>${money(item.unit_price)}</td>
                <td><input type="number" class="form-control form-control-sm oc-qty-input" data-idx="${idx}" min="1" max="${item.stock || item.quantity}" value="${item.quantity}"></td>
                <td>${money(item.unit_price * item.quantity)}</td>
                <td><button type="button" class="btn btn-sm btn-light border oe-remove-item" data-idx="${idx}"><i class="fa-solid fa-trash"></i></button></td>
            </tr>
        `).join('');

        hiddenWrap.innerHTML = items.map((item, idx) => `
            <input type="hidden" name="items[${idx}][product_variant_id]" value="${item.id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `).join('');

        updateSummary();
    }

    document.getElementById('oeItemsBody').addEventListener('input', function (e) {
        if (!e.target.matches('.oc-qty-input')) return;
        const idx = Number(e.target.dataset.idx);
        let qty = parseInt(e.target.value, 10) || 1;
        const max = items[idx].stock || qty;
        qty = Math.max(1, Math.min(qty, max));
        items[idx].quantity = qty;
        renderItems();
    });

    document.getElementById('oeItemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.oe-remove-item');
        if (!btn) return;
        items.splice(Number(btn.dataset.idx), 1);
        renderItems();
    });

    (function () {
        const input   = document.getElementById('oeProductSearch');
        const results = document.getElementById('oeProductResults');
        let timer = null;

        function search(q) {
            fetch(`{{ route('admin.orders.searchVariants') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => renderResults(data.variants || []));
        }

        function renderResults(variants) {
            if (!variants.length) {
                results.innerHTML = '<div class="oc-search-item text-muted">Không tìm thấy sản phẩm còn hàng.</div>';
            } else {
                results.innerHTML = variants.map(v => `
                    <div class="oc-search-item" data-variant='${JSON.stringify(v).replace(/'/g, "&apos;")}'>
                        <div>${v.product_name} ${v.color ? '· ' + v.color : ''} ${v.size ? '· ' + v.size : ''}</div>
                        <div class="oc-item-sub">SKU: ${v.sku} · Tồn: ${v.stock} · ${money(v.unit_price)}</div>
                    </div>
                `).join('');
            }
            results.classList.remove('d-none');
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = input.value.trim();
            timer = setTimeout(() => search(q), 300);
        });

        input.addEventListener('focus', function () {
            search(input.value.trim());
        });

        results.addEventListener('click', function (e) {
            const item = e.target.closest('.oc-search-item[data-variant]');
            if (!item) return;
            const variant = JSON.parse(item.dataset.variant.replace(/&apos;/g, "'"));
            addItem(variant);
            input.value = '';
            results.classList.add('d-none');
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#oeProductSearch') && !e.target.closest('#oeProductResults')) {
                results.classList.add('d-none');
            }
        });
    }());

    const shippingInput = document.getElementById('oeShippingFee');
    shippingInput.addEventListener('input', updateSummary);

    function updateSummary() {
        const subtotal = items.reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
        const shipping = parseFloat(shippingInput.value) || 0;
        document.getElementById('oeSubtotal').textContent = money(subtotal);
        document.getElementById('oeSummaryShipping').textContent = money(shipping);
        document.getElementById('oeTotal').textContent = money(subtotal + shipping);
    }

    renderItems();
})();

/* ── Submit: gửi AJAX vì luôn nằm trong offcanvas ── */
(function () {
    const form = document.getElementById('editOrderForm');
    if (!form || form.dataset.submitWired === '1') return;
    form.dataset.submitWired = '1';

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('editOrderSubmitBtn');
        submitBtn?.setAttribute('disabled', 'disabled');

        const offcanvasEl = form.closest('.offcanvas');
        const bodyEl = offcanvasEl?.querySelector('[data-order-edit-body]');

        try {
            const res = await fetch(form.action, {
                method: 'PUT',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            });

            const data = await res.json().catch(() => null);

            if (res.status === 422 && data?.html && bodyEl) {
                bodyEl.innerHTML = data.html;
                window.runInjectedScripts?.(bodyEl);
                return;
            }

            if (!res.ok || !data) {
                alert((data && data.message) || 'Không thể cập nhật đơn hàng.');
                return;
            }

            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).hide();

            if (window.reloadAdminTable) {
                window.reloadAdminTable();
            } else {
                window.location.reload();
            }
        } catch (err) {
            alert('Không thể kết nối tới máy chủ. Vui lòng thử lại.');
        } finally {
            submitBtn?.removeAttribute('disabled');
        }
    });
})();
</script>
