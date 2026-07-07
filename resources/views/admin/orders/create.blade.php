@extends('layouts.admin')

@section('title', 'Thêm đơn hàng')

@push('styles')
    @include('admin.orders.styles')
    <style>
        .create-header-title { font-size: 25px; font-weight: 800; color: #000 !important; margin-bottom: 4px; }
        .create-header-desc { color: #64748b; font-size: 14px; margin: 0; }

        .oc-search-wrap { position: relative; }
        .oc-search-results {
            position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 50;
            background: #fff; border: 1px solid #d8dee6; border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.10); max-height: 260px; overflow-y: auto;
        }
        .oc-search-item { padding: 8px 14px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .oc-search-item:last-child { border-bottom: none; }
        .oc-search-item:hover { background: #f0fdf4; }
        .oc-search-item .oc-item-sub { color: #6b7280; font-size: 12px; }

        .oc-customer-card {
            display: flex; align-items: center; justify-content: space-between;
            border: 1.5px solid #16A34A; background: #F0FDF4; border-radius: 8px;
            padding: 10px 14px; font-size: 13px;
        }

        .oc-items-table th, .oc-items-table td { font-size: 13px; vertical-align: middle; }
        .oc-items-table .oc-qty-input { width: 70px; }
        .oc-empty-items { padding: 24px; text-align: center; color: #94A3B8; font-size: 13px; }

        .oc-summary-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .oc-summary-row.total { font-weight: 800; font-size: 15px; border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 10px; }

        /* ── Dropdown tuỳ chỉnh (bo viền) thay cho <select> mặc định của trình duyệt ── */
        .edit-field .oc-dropdown.hk-cat-filter { width: 100%; flex: none; }
        .oc-dropdown .hk-cat-trigger:disabled { opacity: .6; cursor: not-allowed; background: #F8FAFC; }
        .edit-field .oc-dropdown.is-invalid .hk-cat-trigger { border-color: #dc3545; }

        /* Bảng tuỳ chọn mở rộng từ TRÁI qua PHẢI, rộng bằng đúng nút chọn (thay vì lệch trái, 240px cố định) */
        .oc-dropdown .hk-cat-panel {
            left: 0;
            right: auto;
            width: 100%;
        }

        /* Thanh hành động cố định ngang phía dưới cùng, chỉ nội dung ở giữa cuộn bên trên */
        #createOrderForm .row { padding-bottom: 90px; }
        .sticky-action-bar {
            position: fixed; bottom: 0; display: flex; justify-content: flex-end; gap: 12px;
            background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
            padding: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); z-index: 1030;
            margin-bottom: 16px;
        }
        .sticky-action-bar .btn { min-height: 42px; min-width: 150px; }

        [data-theme="dark"] .oc-search-results { background: #101C33 !important; border-color: #2A3B59 !important; }
        [data-theme="dark"] .oc-search-item { border-color: #1E293B !important; color: #E2E8F0; }
        [data-theme="dark"] .oc-search-item:hover { background: #162843 !important; }
        [data-theme="dark"] .oc-customer-card { background: rgba(34,197,94,0.1) !important; border-color: #16A34A !important; color: #E2E8F0; }
        [data-theme="dark"] .sticky-action-bar { background: #0F1B33 !important; border-color: #2A3B59 !important; }
        [data-theme="dark"] .oc-summary-row.total { border-color: #2A3B59 !important; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="create-header-title">Thêm đơn hàng</h1>
            <p class="create-header-desc">Tạo đơn hàng mới thay cho khách hàng.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.orders.store') }}" id="createOrderForm">
        @csrf
        <input type="hidden" name="user_id" id="ocUserId" value="{{ old('user_id') }}">
        <input type="hidden" name="address_id" id="ocAddressId" value="{{ old('address_id') }}">
        <div id="ocItemsHidden"></div>

        <div class="row g-4">
            {{-- ── CỘT TRÁI ── --}}
            <div class="col-lg-8">

                {{-- Khách hàng --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Khách hàng</span></div>
                    <div class="card-body p-4">
                        <div class="edit-field oc-search-wrap mb-2">
                            <label>Tìm khách hàng <span class="text-danger">*</span></label>
                            <input type="text" id="ocCustomerSearch" class="form-control @error('user_id') is-invalid @enderror"
                                placeholder="Nhập tên, email hoặc SĐT khách hàng..." autocomplete="off">
                            <div class="oc-search-results d-none" id="ocCustomerResults"></div>
                            @error('user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div id="ocSelectedCustomer" class="oc-customer-card d-none">
                            <div>
                                <div class="fw-bold" id="ocSelectedCustomerName"></div>
                                <div class="text-muted" id="ocSelectedCustomerSub"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border" id="ocChangeCustomer">Đổi khách hàng</button>
                        </div>
                    </div>
                </div>

                {{-- Địa chỉ giao hàng --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Địa chỉ giao hàng</span></div>
                    <div class="card-body p-4">
                        <div class="edit-field">
                            <label>Địa chỉ đã lưu</label>
                            <div class="hk-cat-filter oc-dropdown" id="ocAddressDrop">
                                <button type="button" class="hk-cat-trigger" id="ocAddressTrigger" aria-haspopup="listbox" aria-expanded="false" disabled>
                                    <span class="hk-cat-trigger-label" id="ocAddressLabel">— Chọn khách hàng trước —</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="ocAddressPanel" hidden>
                                    <div class="hk-cat-list" id="ocAddressList" role="listbox"></div>
                                </div>
                            </div>
                            @error('address_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div id="ocNewAddressFields" class="d-none">
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
                    </div>
                </div>

                {{-- Sản phẩm --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Sản phẩm</span></div>
                    <div class="card-body p-4">
                        <div class="edit-field oc-search-wrap">
                            <label>Tìm sản phẩm (tên hoặc SKU)</label>
                            <input type="text" id="ocProductSearch" class="form-control" placeholder="Nhập tên sản phẩm hoặc SKU..." autocomplete="off">
                            <div class="oc-search-results d-none" id="ocProductResults"></div>
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
                                <tbody id="ocItemsBody">
                                    <tr id="ocEmptyItemsRow">
                                        <td colspan="5" class="oc-empty-items">Chưa có sản phẩm nào. Tìm và thêm sản phẩm ở trên.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── CỘT PHẢI ── --}}
            <div class="col-lg-4">

                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Thanh toán & Vận chuyển</span></div>
                    <div class="card-body p-4">
                        <div class="edit-field">
                            <label>Số điện thoại liên hệ <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="ocPhone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @php
                            $oldPaymentMethodId = old('payment_method_id');
                            $selectedPaymentMethodLabel = '— Chọn phương thức —';
                            foreach ($paymentMethods as $method) {
                                if ((string) $oldPaymentMethodId === (string) $method->id) {
                                    $selectedPaymentMethodLabel = $method->name; break;
                                }
                            }
                            $oldStatus = old('status', 'pending');
                            $oldPaymentStatus = old('payment_status', 'unpaid');
                        @endphp

                        <div class="edit-field">
                            <label>Phương thức thanh toán <span class="text-danger">*</span></label>
                            <input type="hidden" name="payment_method_id" id="ocPaymentMethodHidden" value="{{ $oldPaymentMethodId }}">
                            <div class="hk-cat-filter oc-dropdown {{ $errors->has('payment_method_id') ? 'is-invalid' : '' }}" id="ocPaymentMethodDrop">
                                <button type="button" class="hk-cat-trigger" id="ocPaymentMethodTrigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="hk-cat-trigger-label" id="ocPaymentMethodLabel">{{ $selectedPaymentMethodLabel }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="ocPaymentMethodPanel" hidden>
                                    <div class="hk-cat-list" id="ocPaymentMethodList" role="listbox">
                                        <button type="button" class="hk-cat-item {{ !$oldPaymentMethodId ? 'is-active' : '' }}" data-value="" data-label="— Chọn phương thức —">— Chọn phương thức —</button>
                                        @foreach($paymentMethods as $method)
                                            <button type="button" class="hk-cat-item {{ (string) $oldPaymentMethodId === (string) $method->id ? 'is-active' : '' }}"
                                                data-value="{{ $method->id }}" data-label="{{ $method->name }}">{{ $method->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('payment_method_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field">
                            <label>Trạng thái đơn hàng <span class="text-danger">*</span></label>
                            <input type="hidden" name="status" id="ocStatusHidden" value="{{ $oldStatus }}">
                            <div class="hk-cat-filter oc-dropdown" id="ocStatusDrop">
                                <button type="button" class="hk-cat-trigger" id="ocStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="hk-cat-trigger-label" id="ocStatusLabel">{{ $statusLabels[$oldStatus] ?? reset($statusLabels) }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="ocStatusPanel" hidden>
                                    <div class="hk-cat-list" id="ocStatusList" role="listbox">
                                        @foreach($statusLabels as $val => $label)
                                            <button type="button" class="hk-cat-item {{ $oldStatus === $val ? 'is-active' : '' }}"
                                                data-value="{{ $val }}" data-label="{{ $label }}">{{ $label }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="edit-field">
                            <label>Trạng thái thanh toán <span class="text-danger">*</span></label>
                            <input type="hidden" name="payment_status" id="ocPaymentStatusHidden" value="{{ $oldPaymentStatus }}">
                            <div class="hk-cat-filter oc-dropdown" id="ocPaymentStatusDrop">
                                <button type="button" class="hk-cat-trigger" id="ocPaymentStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="hk-cat-trigger-label" id="ocPaymentStatusLabel">{{ $paymentStatusLabels[$oldPaymentStatus] ?? reset($paymentStatusLabels) }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="ocPaymentStatusPanel" hidden>
                                    <div class="hk-cat-list" id="ocPaymentStatusList" role="listbox">
                                        @foreach($paymentStatusLabels as $val => $label)
                                            <button type="button" class="hk-cat-item {{ $oldPaymentStatus === $val ? 'is-active' : '' }}"
                                                data-value="{{ $val }}" data-label="{{ $label }}">{{ $label }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="edit-field mb-0">
                            <label>Phí vận chuyển</label>
                            <input type="number" name="shipping_fee" id="ocShippingFee" min="0" step="1000"
                                class="form-control @error('shipping_fee') is-invalid @enderror" value="{{ old('shipping_fee', 0) }}">
                            @error('shipping_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Tổng cộng</span></div>
                    <div class="card-body p-4">
                        <div class="oc-summary-row">
                            <span>Tạm tính</span>
                            <span id="ocSubtotal">0đ</span>
                        </div>
                        <div class="oc-summary-row">
                            <span>Phí vận chuyển</span>
                            <span id="ocSummaryShipping">0đ</span>
                        </div>
                        <div class="oc-summary-row total">
                            <span>Tổng tiền</span>
                            <span id="ocTotal">0đ</span>
                        </div>
                    </div>
                </div>

                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Ghi chú</span></div>
                    <div class="card-body p-4">
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú nội bộ (không bắt buộc)">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky-action-bar" id="ocActionBar">
            <a href="{{ route('admin.orders.list') }}" class="btn btn-light border fw-bold">
                <i class="fa-solid fa-xmark me-1"></i> Hủy bỏ
            </a>
            <button type="submit" class="btn btn-primary fw-bold" id="ocSubmitBtn">
                <i class="fa-solid fa-plus me-1"></i> Tạo đơn hàng
            </button>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const money = (n) => Number(n || 0).toLocaleString('vi-VN') + 'đ';

    /* ── Tìm & chọn khách hàng ── */
    (function () {
        const input   = document.getElementById('ocCustomerSearch');
        const results = document.getElementById('ocCustomerResults');
        const userIdField = document.getElementById('ocUserId');
        const selectedCard = document.getElementById('ocSelectedCustomer');
        const selectedName = document.getElementById('ocSelectedCustomerName');
        const selectedSub  = document.getElementById('ocSelectedCustomerSub');
        const changeBtn    = document.getElementById('ocChangeCustomer');
        let timer = null;

        function search(q) {
            fetch(`{{ route('admin.orders.searchCustomers') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => renderResults(data.customers || []));
        }

        function renderResults(customers) {
            if (!customers.length) {
                results.innerHTML = '<div class="oc-search-item text-muted">Không tìm thấy khách hàng.</div>';
            } else {
                results.innerHTML = customers.map(c => `
                    <div class="oc-search-item" data-id="${c.id}" data-name="${c.username}" data-sub="${c.email ?? ''} ${c.phone_number ?? ''}">
                        <div>${c.username}</div>
                        <div class="oc-item-sub">${c.email ?? ''} ${c.phone_number ? '· ' + c.phone_number : ''}</div>
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
            if (input.value.trim() !== '') search(input.value.trim());
        });

        results.addEventListener('click', function (e) {
            const item = e.target.closest('.oc-search-item[data-id]');
            if (!item) return;

            userIdField.value = item.dataset.id;
            selectedName.textContent = item.dataset.name;
            selectedSub.textContent  = item.dataset.sub;
            selectedCard.classList.remove('d-none');
            input.closest('.oc-search-wrap').classList.add('d-none');
            results.classList.add('d-none');

            loadAddresses(item.dataset.id);
        });

        changeBtn.addEventListener('click', function () {
            userIdField.value = '';
            selectedCard.classList.add('d-none');
            input.closest('.oc-search-wrap').classList.remove('d-none');
            input.value = '';
            input.focus();
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#ocCustomerSearch') && !e.target.closest('#ocCustomerResults')) {
                results.classList.add('d-none');
            }
        });
    }());

    /* ── Dropdown bo viền dùng chung (thay cho <select> mặc định) ── */
    function wireDropdown({ root, trigger, panel, label, list, hidden, onSelect }) {
        const rootEl    = document.getElementById(root);
        const triggerEl = document.getElementById(trigger);
        const panelEl   = document.getElementById(panel);
        const labelEl   = document.getElementById(label);
        const listEl    = document.getElementById(list);
        const hiddenEl  = document.getElementById(hidden);
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
            if (onSelect) onSelect(btn.dataset.value, btn.dataset.label);
        });

        document.addEventListener('click', function (e) {
            if (!panelEl.hidden && !rootEl.contains(e.target)) close();
        });

        return { setOptions(html) { listEl.innerHTML = html; }, enable() { triggerEl.disabled = false; }, disable() { triggerEl.disabled = true; } };
    }

    wireDropdown({ root: 'ocPaymentMethodDrop', trigger: 'ocPaymentMethodTrigger', panel: 'ocPaymentMethodPanel', label: 'ocPaymentMethodLabel', list: 'ocPaymentMethodList', hidden: 'ocPaymentMethodHidden' });
    wireDropdown({ root: 'ocStatusDrop', trigger: 'ocStatusTrigger', panel: 'ocStatusPanel', label: 'ocStatusLabel', list: 'ocStatusList', hidden: 'ocStatusHidden' });
    wireDropdown({ root: 'ocPaymentStatusDrop', trigger: 'ocPaymentStatusTrigger', panel: 'ocPaymentStatusPanel', label: 'ocPaymentStatusLabel', list: 'ocPaymentStatusList', hidden: 'ocPaymentStatusHidden' });

    /* ── Địa chỉ giao hàng ── */
    const addressIdField   = document.getElementById('ocAddressId');
    const newAddressFields = document.getElementById('ocNewAddressFields');

    const addressDropdown = wireDropdown({
        root: 'ocAddressDrop', trigger: 'ocAddressTrigger', panel: 'ocAddressPanel',
        label: 'ocAddressLabel', list: 'ocAddressList', hidden: 'ocAddressId',
        onSelect: function (value) {
            if (value === '__new__' || value === '') {
                addressIdField.value = '';
                newAddressFields.classList.remove('d-none');
            } else {
                newAddressFields.classList.add('d-none');
            }
        },
    });

    function loadAddresses(userId) {
        fetch(`/admin/orders/customers/${userId}/addresses`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(data => {
                const addresses = data.addresses || [];
                let html = '';
                addresses.forEach((a, idx) => {
                    const label = [a.apartment_number, a.ward, a.district, a.city].filter(Boolean).join(', ');
                    html += `<button type="button" class="hk-cat-item ${idx === 0 ? 'is-active' : ''}" data-value="${a.id}" data-label="${label}">${label}</button>`;
                });
                html += `<button type="button" class="hk-cat-item ${addresses.length === 0 ? 'is-active' : ''}" data-value="__new__" data-label="+ Thêm địa chỉ mới">+ Thêm địa chỉ mới</button>`;

                addressDropdown.setOptions(html);
                addressDropdown.enable();

                if (addresses.length > 0) {
                    document.getElementById('ocAddressLabel').textContent = [addresses[0].apartment_number, addresses[0].ward, addresses[0].district, addresses[0].city].filter(Boolean).join(', ');
                    addressIdField.value = addresses[0].id;
                    newAddressFields.classList.add('d-none');
                } else {
                    document.getElementById('ocAddressLabel').textContent = '+ Thêm địa chỉ mới';
                    addressIdField.value = '';
                    newAddressFields.classList.remove('d-none');
                }
            });
    }

    /* ── Tìm & thêm sản phẩm ── */
    const items = [];

    (function () {
        const input   = document.getElementById('ocProductSearch');
        const results = document.getElementById('ocProductResults');
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
            if (!e.target.closest('#ocProductSearch') && !e.target.closest('#ocProductResults')) {
                results.classList.add('d-none');
            }
        });
    }());

    function addItem(variant) {
        const existing = items.find(i => i.id === variant.id);
        if (existing) {
            existing.quantity = Math.min(existing.quantity + 1, variant.stock);
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
        const body = document.getElementById('ocItemsBody');
        const hiddenWrap = document.getElementById('ocItemsHidden');

        if (!items.length) {
            body.innerHTML = '<tr id="ocEmptyItemsRow"><td colspan="5" class="oc-empty-items">Chưa có sản phẩm nào. Tìm và thêm sản phẩm ở trên.</td></tr>';
            hiddenWrap.innerHTML = '';
            updateSummary();
            return;
        }

        body.innerHTML = items.map((item, idx) => `
            <tr>
                <td>${item.name} ${item.color ? '· ' + item.color : ''} ${item.size ? '· ' + item.size : ''}</td>
                <td>${money(item.unit_price)}</td>
                <td><input type="number" class="form-control form-control-sm oc-qty-input" data-idx="${idx}" min="1" max="${item.stock}" value="${item.quantity}"></td>
                <td>${money(item.unit_price * item.quantity)}</td>
                <td><button type="button" class="btn btn-sm btn-light border oc-remove-item" data-idx="${idx}"><i class="fa-solid fa-trash"></i></button></td>
            </tr>
        `).join('');

        hiddenWrap.innerHTML = items.map((item, idx) => `
            <input type="hidden" name="items[${idx}][product_variant_id]" value="${item.id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `).join('');

        updateSummary();
    }

    document.getElementById('ocItemsBody').addEventListener('input', function (e) {
        if (!e.target.matches('.oc-qty-input')) return;
        const idx = Number(e.target.dataset.idx);
        let qty = parseInt(e.target.value, 10) || 1;
        qty = Math.max(1, Math.min(qty, items[idx].stock));
        items[idx].quantity = qty;
        renderItems();
    });

    document.getElementById('ocItemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.oc-remove-item');
        if (!btn) return;
        items.splice(Number(btn.dataset.idx), 1);
        renderItems();
    });

    const shippingInput = document.getElementById('ocShippingFee');
    shippingInput.addEventListener('input', updateSummary);

    function updateSummary() {
        const subtotal = items.reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
        const shipping = parseFloat(shippingInput.value) || 0;
        document.getElementById('ocSubtotal').textContent = money(subtotal);
        document.getElementById('ocSummaryShipping').textContent = money(shipping);
        document.getElementById('ocTotal').textContent = money(subtotal + shipping);
    }

    document.getElementById('createOrderForm').addEventListener('submit', function (e) {
        if (!document.getElementById('ocUserId').value) {
            e.preventDefault();
            alert('Vui lòng chọn khách hàng.');
            return;
        }
        if (!items.length) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một sản phẩm.');
        }
    });

    updateSummary();

    /* ── Giữ thanh hành động cố định đúng vị trí/độ rộng vùng nội dung (không đè lên sidebar) ── */
    (function () {
        const bar  = document.getElementById('ocActionBar');
        const ref  = document.querySelector('#createOrderForm .row');
        if (!bar || !ref) return;

        function sync() {
            const rect = ref.getBoundingClientRect();
            bar.style.left  = rect.left + 'px';
            bar.style.width = rect.width + 'px';
        }

        if (window.ResizeObserver) {
            new ResizeObserver(sync).observe(ref);
        }
        window.addEventListener('resize', sync);
        sync();
    }());
}());
</script>
@endpush
