{{--
    Nội dung form "Sửa đơn hàng" — nạp qua AJAX vào offcanvas (partials/edit-offcanvas.blade.php).
    Gộp cả đổi trạng thái giao hàng/thanh toán (trước đây là modal riêng ở index.blade.php) VÀ
    sửa nội dung đơn (địa chỉ/SĐT/sản phẩm/phí ship/ghi chú) vào cùng 1 panel.
    Variables expected: $order, $scope ('full'|'limited'|'none'), $addresses (Collection<Address>),
    $items, $allowedStatuses (mảng key=>label các trạng thái được phép chọn), $paymentStatusLabels,
    $isOnlineGateway (bool).
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
        'thumbnail'  => $i['thumbnail'],
        'color'      => $i['color'],
        'color_hex'  => $i['color_hex'],
        'size'       => $i['size'],
        'sku'        => $i['sku'],
        'unit_price' => $i['unit_price'],
        'stock'      => $i['stock'],
        'quantity'   => $i['quantity'],
    ])->values();
@endphp

<style>
    /* ── Picker tìm sản phẩm (giống ô tìm sản phẩm nhập kho) ── */
    .oe-picker-wrap { position: relative; }
    .oe-picker-input {
        width: 100%; height: 42px; border: 1.5px solid #d1d5db; border-radius: 8px;
        padding: 0 14px; font-size: 14px; outline: none; box-sizing: border-box;
    }
    .oe-picker-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
    .oe-picker-panel {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.12); max-height: 300px; overflow-y: auto;
        z-index: 40;
    }
    .oe-picker-item {
        display: flex; align-items: center; gap: 10px; padding: 9px 14px;
        cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .1s;
    }
    .oe-picker-item:last-child { border-bottom: 0; }
    .oe-picker-item:hover { background: #f9fafb; }
    .oe-picker-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .oe-picker-info { flex: 1; min-width: 0; }
    .oe-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .oe-picker-meta { font-size: 12px; color: #6b7280; }
    .oe-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
    .oe-picker-empty { padding: 16px; text-align: center; color: #9ca3af; font-size: 13px; }

    /* ── Bảng sản phẩm trong đơn (giống bảng sản phẩm nhập kho) ── */
    .oe-row-product { display: flex; align-items: center; gap: 10px; min-width: 200px; }
    .oe-row-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .oe-row-name { font-size: 13px; font-weight: 700; color: #111827; }
    .oe-row-sub { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    .oe-row-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; }
    .oe-num-input {
        width: 100%; min-width: 70px; height: 34px; border: 1.5px solid #d1d5db; border-radius: 6px;
        padding: 0 8px; font-size: 13px; outline: none; box-sizing: border-box;
    }
    .oe-num-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
</style>

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

    <div class="offcanvas-body flex-grow-1 overflow-auto p-3" data-edit-errors-anchor>
        @if($scope === 'limited')
            <div class="alert alert-secondary py-2 px-3 mb-3" style="font-size:12.5px;">
                @if($order->status === 'processing')
                    Đơn đã chuyển "Đang xử lý" (đã trừ kho) nên chỉ có thể cập nhật thông tin giao hàng.
                @else
                    Đơn thanh toán online đã tạo link/QR thanh toán nên chỉ có thể cập nhật thông tin giao hàng,
                    không thể sửa sản phẩm hay phí vận chuyển.
                @endif
            </div>
        @endif

        {{-- Trạng thái đơn hàng --}}
        <div class="card edit-card shadow-sm mb-3">
            <div class="card-header py-2"><span class="fw-bold" style="font-size:14px;">Trạng thái đơn hàng</span></div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-6 edit-field mb-0">
                        <label>Trạng thái giao hàng <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror">
                            @foreach($allowedStatuses as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $order->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 edit-field mb-0">
                        <label>Trạng thái thanh toán <span class="text-danger">*</span></label>
                        @if($isOnlineGateway)
                            <input type="hidden" name="payment_status" value="{{ $order->payment_status }}">
                            <div class="form-control form-control-sm bg-light text-muted" style="font-size:13px;">
                                {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}
                            </div>
                            <div class="form-text" style="font-size:11.5px;">Đồng bộ tự động từ cổng thanh toán online, không thể sửa tay.</div>
                        @else
                            <select name="payment_status" class="form-select form-select-sm @error('payment_status') is-invalid @enderror">
                                @foreach($paymentStatusLabels as $val => $label)
                                    <option value="{{ $val }}" {{ old('payment_status', $order->payment_status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @endif
                    </div>
                </div>
                <div class="edit-field mt-3 mb-0">
                    <label>Ghi chú</label>
                    <textarea name="note" class="form-control form-control-sm @error('note') is-invalid @enderror" rows="2"
                        placeholder="Ghi chú nội bộ (không bắt buộc)">{{ old('note', $order->note) }}</textarea>
                    @error('note') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        @if($scope !== 'none')
            {{-- Địa chỉ giao hàng --}}
            <div class="card edit-card shadow-sm mb-3">
                <div class="card-header py-2"><span class="fw-bold" style="font-size:14px;">Địa chỉ giao hàng</span></div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-8 edit-field mb-0">
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
                        <div class="col-md-4 edit-field mb-0">
                            <label>Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control form-control-sm @error('phone') is-invalid @enderror" value="{{ old('phone', $order->phone) }}">
                            @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="oeNewAddressFields" class="d-none mt-3">
                        <div class="row g-3">
                            <div class="col-md-6 edit-field mb-0">
                                <label>Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                <input type="hidden" name="new_address[city]" id="oeProvinceHidden" value="{{ old('new_address.city') }}">
                                <div class="hk-cat-filter oc-dropdown @error('new_address.city') is-invalid @enderror" id="oeProvinceDrop">
                                    <button type="button" class="hk-cat-trigger" id="oeProvinceTrigger" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="hk-cat-trigger-label" id="oeProvinceLabel">{{ old('new_address.city') ?: '— Chọn tỉnh/thành phố —' }}</span>
                                        <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                    </button>
                                    <div class="hk-cat-panel" id="oeProvincePanel" hidden>
                                        <div class="hk-cat-list" id="oeProvinceList" role="listbox"></div>
                                    </div>
                                </div>
                                @error('new_address.city') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 edit-field mb-0">
                                <label>Phường/Xã <span class="text-danger">*</span></label>
                                <input type="hidden" name="new_address[ward]" id="oeWardHidden" value="{{ old('new_address.ward') }}">
                                <div class="hk-cat-filter oc-dropdown @error('new_address.ward') is-invalid @enderror" id="oeWardDrop">
                                    <button type="button" class="hk-cat-trigger" id="oeWardTrigger" aria-haspopup="listbox" aria-expanded="false" disabled>
                                        <span class="hk-cat-trigger-label" id="oeWardLabel">— Chọn tỉnh/thành trước —</span>
                                        <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                    </button>
                                    <div class="hk-cat-panel" id="oeWardPanel" hidden>
                                        <div class="hk-cat-list" id="oeWardList" role="listbox"></div>
                                    </div>
                                </div>
                                @error('new_address.ward') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 edit-field mb-0">
                                <label>Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                <input type="text" name="new_address[apartment_number]" class="form-control form-control-sm @error('new_address.apartment_number') is-invalid @enderror" value="{{ old('new_address.apartment_number') }}">
                                @error('new_address.apartment_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sản phẩm + Tổng cộng --}}
            <div class="card edit-card shadow-sm mb-0">
                <div class="card-header py-2"><span class="fw-bold" style="font-size:14px;">Sản phẩm</span></div>
                <div class="card-body p-3">
                    @if($scope === 'full')
                        <div class="d-flex gap-2 align-items-start mb-2">
                            <div class="oe-picker-wrap flex-grow-1">
                                <input type="text" id="oeProductSearch" class="oe-picker-input" placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào đơn..." autocomplete="off">
                                <div class="oe-picker-panel" id="oeProductResults" hidden></div>
                            </div>
                        </div>

                        @error('items') <div class="text-danger mb-2" style="font-size:13px;">{{ $message }}</div> @enderror

                        <div class="table-responsive">
                            <table class="table table-bordered item-table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th style="width:90px;">Tồn kho</th>
                                        <th style="width:100px;">Đơn giá</th>
                                        <th style="width:90px;">Số lượng</th>
                                        <th style="width:110px;">Thành tiền</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="oeItemsBody">
                                    <tr id="oeEmptyItemsRow">
                                        <td colspan="6" class="oc-empty-items">Chưa có sản phẩm nào.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="oeItemsHidden"></div>

                        <hr class="my-3">

                        <div class="edit-field mb-2">
                            <label>Phí vận chuyển</label>
                            <input type="number" name="shipping_fee" id="oeShippingFee" min="0" step="1000"
                                class="form-control form-control-sm @error('shipping_fee') is-invalid @enderror"
                                value="{{ old('shipping_fee', (float) $order->shipping_fee) }}">
                            @error('shipping_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div style="font-size:12.5px;" class="text-muted">
                                Tạm tính: <span id="oeSubtotal" class="fw-bold text-dark">0đ</span>
                                &nbsp;·&nbsp; Ship: <span id="oeSummaryShipping" class="fw-bold text-dark">0đ</span>
                            </div>
                            <div style="font-size:15px;">
                                Tổng tiền: <span id="oeTotal" class="fw-bold">0đ</span>
                            </div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered item-table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th style="width:90px;">Tồn kho</th>
                                        <th style="width:100px;">Đơn giá</th>
                                        <th style="width:70px;">SL</th>
                                        <th style="width:110px;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                <div class="oe-row-product">
                                                    @if($item['thumbnail'])
                                                        <img src="{{ asset($item['thumbnail']) }}" class="oe-row-thumb" alt="">
                                                    @else
                                                        <div class="oe-row-thumb d-flex align-items-center justify-content-center border">
                                                            <i class="fa-regular fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="oe-row-name">{{ $item['product_name'] }}</div>
                                                        <div class="oe-row-sub">
                                                            <span class="oe-row-dot" style="background:{{ $item['color_hex'] ?: '#ccc' }};"></span>
                                                            {{ collect([$item['color'], $item['size'], $item['sku']])->filter()->implode(' · ') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted">{{ $item['stock'] }}</td>
                                            <td class="fw-semibold">{{ number_format($item['unit_price'], 0, ',', '.') }}đ</td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td class="fw-bold" style="color:#174761;">{{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div style="font-size:12.5px;" class="text-muted">
                                Phí vận chuyển: <span class="fw-bold text-dark">{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}đ</span>
                            </div>
                            <div style="font-size:15px;">
                                Tổng tiền: <span class="fw-bold">{{ number_format((float) $order->total_money, 0, ',', '.') }}đ</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
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
        if (!root) return;
        const trigger = document.getElementById('oeAddressTrigger');
        const panel   = document.getElementById('oeAddressPanel');
        const label   = document.getElementById('oeAddressLabel');
        const list    = document.getElementById('oeAddressList');
        const hidden  = document.getElementById('oeAddressId');
        const newFields = document.getElementById('oeNewAddressFields');

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

    /* ── Dropdown bo viền dùng chung, tối giản (giống wireDropdown ở trang Thêm đơn hàng) ── */
    function wireSimpleDropdown(root, trigger, panel, label, list, hidden, onSelect) {
        const rootEl = document.getElementById(root);
        const triggerEl = document.getElementById(trigger);
        const panelEl = document.getElementById(panel);
        const labelEl = document.getElementById(label);
        const listEl = document.getElementById(list);
        const hiddenEl = document.getElementById(hidden);
        if (!rootEl || !triggerEl) return null;

        function open()  { if (triggerEl.disabled) return; panelEl.hidden = false; triggerEl.classList.add('is-open'); triggerEl.setAttribute('aria-expanded', 'true'); }
        function close() { panelEl.hidden = true; triggerEl.classList.remove('is-open'); triggerEl.setAttribute('aria-expanded', 'false'); }

        triggerEl.addEventListener('click', () => panelEl.hidden ? open() : close());

        listEl.addEventListener('click', function (e) {
            const btn = e.target.closest('.hk-cat-item');
            if (!btn) return;
            listEl.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            labelEl.textContent = btn.dataset.label;
            hiddenEl.value = btn.dataset.value;
            close();
            if (onSelect) onSelect(btn.dataset.value, btn.dataset.label, btn);
        });

        document.addEventListener('click', function (e) {
            if (!panelEl.hidden && !rootEl.contains(e.target)) close();
        });

        return { setOptions(html) { listEl.innerHTML = html; }, enable() { triggerEl.disabled = false; }, disable() { triggerEl.disabled = true; } };
    }

    /* ── Chọn Tỉnh/Thành phố + Phường/Xã cho địa chỉ mới qua API địa giới hành chính,
       bỏ hẳn trường Quận/Huyện (cơ cấu hành chính mới chỉ còn 2 cấp Tỉnh → Phường/Xã). ── */
    (function () {
        const provinceHiddenEl = document.getElementById('oeProvinceHidden');
        if (!provinceHiddenEl) return;
        const wardHiddenEl = document.getElementById('oeWardHidden');
        const oldCityName = provinceHiddenEl.value;

        const provinceDropdown = wireSimpleDropdown('oeProvinceDrop', 'oeProvinceTrigger', 'oeProvincePanel', 'oeProvinceLabel', 'oeProvinceList', 'oeProvinceHidden',
            function (value, label, btn) {
                wardHiddenEl.value = '';
                document.getElementById('oeWardLabel').textContent = '— Chọn phường/xã —';
                loadWards(btn.dataset.code);
            });

        const wardDropdown = wireSimpleDropdown('oeWardDrop', 'oeWardTrigger', 'oeWardPanel', 'oeWardLabel', 'oeWardList', 'oeWardHidden');

        function loadWards(code) {
            if (!code) { wardDropdown.disable(); return; }
            wardDropdown.disable();
            document.getElementById('oeWardLabel').textContent = 'Đang tải...';
            fetch(`/api/location/provinces/${code}/wards`, { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(wards => {
                    const html = (wards || []).map(w =>
                        `<button type="button" class="hk-cat-item" data-value="${w.name}" data-label="${w.name}">${w.name}</button>`
                    ).join('');
                    wardDropdown.setOptions(html);
                    wardDropdown.enable();
                    document.getElementById('oeWardLabel').textContent = '— Chọn phường/xã —';
                });
        }

        fetch('{{ route('location.provinces') }}', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(provinces => {
                const html = (provinces || []).map(p =>
                    `<button type="button" class="hk-cat-item" data-value="${p.name}" data-label="${p.name}" data-code="${p.code}">${p.name}</button>`
                ).join('');
                provinceDropdown.setOptions(html);

                if (oldCityName) {
                    const match = (provinces || []).find(p => p.name === oldCityName);
                    if (match) {
                        document.querySelector(`#oeProvinceList .hk-cat-item[data-value="${oldCityName}"]`)?.classList.add('is-active');
                        loadWards(match.code);
                    }
                }
            });
    }());

    if (scope !== 'full') return;

    /* ── Danh sách sản phẩm trong đơn (thêm/xóa/đổi số lượng) ── */
    const items = @json($jsItems);

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function resolveImageUrl(path) {
        if (!path) return null;
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    function thumbHtml(path, cssClass) {
        const url = resolveImageUrl(path);
        if (url) return `<img src="${url}" class="${cssClass}" alt="">`;
        return `<div class="${cssClass} d-flex align-items-center justify-content-center border"><i class="fa-regular fa-image text-muted"></i></div>`;
    }

    function addItem(variant) {
        const existing = items.find(i => i.id === variant.id);
        if (existing) {
            existing.quantity = Math.min(existing.quantity + 1, variant.stock || existing.quantity + 1);
        } else {
            items.push({
                id: variant.id,
                name: variant.product_name,
                thumbnail: variant.thumbnail,
                color: variant.color,
                color_hex: variant.color_hex,
                size: variant.size,
                sku: variant.sku,
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
            body.innerHTML = '<tr id="oeEmptyItemsRow"><td colspan="6" class="oc-empty-items">Chưa có sản phẩm nào.</td></tr>';
            hiddenWrap.innerHTML = '';
            updateSummary();
            return;
        }

        body.innerHTML = items.map((item, idx) => {
            const sub = [item.color, item.size, item.sku].filter(Boolean).join(' · ');
            return `
            <tr data-idx="${idx}">
                <td>
                    <div class="oe-row-product">
                        ${thumbHtml(item.thumbnail, 'oe-row-thumb')}
                        <div>
                            <div class="oe-row-name">${esc(item.name)}</div>
                            <div class="oe-row-sub">
                                <span class="oe-row-dot" style="background:${esc(item.color_hex || '#ccc')};"></span>
                                ${esc(sub)}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-muted">${item.stock ?? '—'}</td>
                <td class="fw-semibold">${money(item.unit_price)}</td>
                <td><input type="number" class="oe-num-input" data-idx="${idx}" min="1" max="${item.stock || item.quantity}" value="${item.quantity}"></td>
                <td class="fw-bold oe-item-total" style="color:#174761;">${money(item.unit_price * item.quantity)}</td>
                <td><button type="button" class="btn btn-sm btn-light border oe-remove-item" data-idx="${idx}"><i class="fa-solid fa-trash"></i></button></td>
            </tr>
        `;
        }).join('');

        updateHiddenItemInputs();
        updateSummary();
    }

    function updateHiddenItemInputs() {
        document.getElementById('oeItemsHidden').innerHTML = items.map((item, idx) => `
            <input type="hidden" name="items[${idx}][product_variant_id]" value="${item.id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `).join('');
    }

    // Cập nhật tại chỗ, không renderItems() lại toàn bảng — tránh việc thay input đang focus
    // làm mất con trỏ, khiến gõ số lượng từ 2 chữ số trở lên bị bung khỏi ô sau mỗi phím bấm.
    document.getElementById('oeItemsBody').addEventListener('input', function (e) {
        if (!e.target.matches('.oe-num-input')) return;
        const idx = Number(e.target.dataset.idx);
        const qty = parseInt(e.target.value, 10);
        if (!Number.isFinite(qty)) return;

        const max = items[idx].stock || qty;
        const clamped = Math.min(qty, max);
        if (clamped !== qty) e.target.value = clamped;
        items[idx].quantity = clamped;

        document.querySelector(`#oeItemsBody tr[data-idx="${idx}"] .oe-item-total`).textContent = money(items[idx].unit_price * clamped);
        updateHiddenItemInputs();
        updateSummary();
    });

    document.getElementById('oeItemsBody').addEventListener('blur', function (e) {
        if (!e.target.matches('.oe-num-input')) return;
        const idx = Number(e.target.dataset.idx);
        let qty = parseInt(e.target.value, 10);
        if (!Number.isFinite(qty) || qty < 1) qty = 1;
        const max = items[idx].stock || qty;
        qty = Math.min(qty, max);
        e.target.value = qty;
        items[idx].quantity = qty;

        document.querySelector(`#oeItemsBody tr[data-idx="${idx}"] .oe-item-total`).textContent = money(items[idx].unit_price * qty);
        updateHiddenItemInputs();
        updateSummary();
    }, true);

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
                results.innerHTML = '<div class="oe-picker-empty">Không tìm thấy sản phẩm còn hàng.</div>';
            } else {
                results.innerHTML = variants.map(v => `
                    <div class="oe-picker-item" data-variant='${JSON.stringify(v).replace(/'/g, "&apos;")}'>
                        ${thumbHtml(v.thumbnail, 'oe-picker-thumb')}
                        <div class="oe-picker-info">
                            <div class="oe-picker-name">${esc(v.product_name)}</div>
                            <div class="oe-picker-meta">${[v.color, v.size, v.sku].filter(Boolean).map(esc).join(' · ')}</div>
                        </div>
                        <span class="oe-picker-stock">Tồn: ${v.stock} · ${money(v.unit_price)}</span>
                    </div>
                `).join('');
            }
            results.hidden = false;
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
            const item = e.target.closest('.oe-picker-item[data-variant]');
            if (!item) return;
            const variant = JSON.parse(item.dataset.variant.replace(/&apos;/g, "'"));
            addItem(variant);
            input.value = '';
            results.hidden = true;
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#oeProductSearch') && !e.target.closest('#oeProductResults')) {
                results.hidden = true;
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
                window.showAlert((data && data.message) || 'Không thể cập nhật đơn hàng.', 'Lỗi', 'danger');
                return;
            }

            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).hide();

            if (window.reloadAdminTable) {
                window.reloadAdminTable();
            } else {
                window.location.reload();
            }
        } catch (err) {
            window.showAlert('Không thể kết nối tới máy chủ. Vui lòng thử lại.', 'Lỗi', 'danger');
        } finally {
            submitBtn?.removeAttribute('disabled');
        }
    });
})();
</script>
