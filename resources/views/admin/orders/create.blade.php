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
        .oc-search-item--warn { cursor: default; background: #FEF3C7; color: #92400E; font-weight: 600; border-bottom: 1px solid #FDE68A; }
        .oc-search-item--warn:hover { background: #FEF3C7; }
        .oc-search-item--match { background: #FFFBEB; }

        .oc-items-table th, .oc-items-table td { font-size: 13px; vertical-align: middle; }
        .oc-items-table .oc-qty-input { width: 70px; }
        .oc-empty-items { padding: 24px; text-align: center; color: #94A3B8; font-size: 13px; }
        .oc-row-thumb { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; background: #f3f4f6; flex-shrink: 0; }
        .oc-row-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); margin-right: 4px; vertical-align: middle; }

        .oc-summary-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .oc-summary-row.total { font-weight: 800; font-size: 15px; border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 10px; }

        /* Ngăn cách 2 phần Khách hàng / Địa chỉ trong cùng một thẻ */
        .oc-card-divider { border: 0; border-top: 1px solid #e5e7eb; opacity: 1; }
        .oc-subsection-title { font-size: 13px; font-weight: 800; color: #0F172A; }

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
            /* margin-bottom: 16px; */
        }
        .sticky-action-bar .btn { min-height: 42px; min-width: 150px; }

        [data-theme="dark"] .oc-search-results { background: #101C33 !important; border-color: #2A3B59 !important; }
        [data-theme="dark"] .oc-search-item { border-color: #1E293B !important; color: #E2E8F0; }
        [data-theme="dark"] .oc-search-item:hover { background: #162843 !important; }
        [data-theme="dark"] .sticky-action-bar { background: #0F1B33 !important; border-color: #2A3B59 !important; }
        [data-theme="dark"] .oc-summary-row.total { border-color: #2A3B59 !important; }
        [data-theme="dark"] .oc-card-divider { border-color: #2A3B59 !important; }
        [data-theme="dark"] .oc-subsection-title { color: #E2E8F0 !important; }
        [data-theme="dark"] .oc-row-thumb { background: #1E293B !important; }
        [data-theme="dark"] .oc-search-item--warn { background: #3F2D0E !important; color: #FCD34D !important; border-color: #57430F !important; }
        [data-theme="dark"] .oc-search-item--warn:hover { background: #3F2D0E !important; }
        [data-theme="dark"] .oc-search-item--match { background: #241C0A !important; }
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

                {{-- Khách hàng, Địa chỉ giao hàng, Sản phẩm (gộp 1 thẻ) --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Thông tin đơn hàng</span></div>
                    <div class="card-body p-4">

                        <div class="section-title mb-3">Khách hàng</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-5 edit-field oc-search-wrap mb-0">
                                <label>Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="ocPhone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}"
                                    placeholder="Nhập SĐT hoặc email để tìm khách hàng..." autocomplete="off">
                                <div class="oc-search-results d-none" id="ocCustomerResults"></div>
                                @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-7 edit-field mb-0">
                                <label>Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" id="ocCustomerName" name="customer_name"
                                    class="form-control @error('user_id') is-invalid @enderror @error('customer_name') is-invalid @enderror"
                                    value="{{ old('customer_name') }}"
                                    placeholder="Nhập tên khách hàng...">
                                @error('user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @error('customer_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="section-title mb-3">Địa chỉ giao hàng</div>
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
                                    <input type="hidden" name="new_address[city]" id="ocProvinceHidden" value="{{ old('new_address.city') }}">
                                    <div class="hk-cat-filter oc-dropdown @error('new_address.city') is-invalid @enderror" id="ocProvinceDrop">
                                        <button type="button" class="hk-cat-trigger" id="ocProvinceTrigger" aria-haspopup="listbox" aria-expanded="false">
                                            <span class="hk-cat-trigger-label" id="ocProvinceLabel">{{ old('new_address.city') ?: '— Chọn tỉnh/thành phố —' }}</span>
                                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                        </button>
                                        <div class="hk-cat-panel" id="ocProvincePanel" hidden>
                                            <div class="hk-cat-list" id="ocProvinceList" role="listbox"></div>
                                        </div>
                                    </div>
                                    @error('new_address.city') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 edit-field mb-0">
                                    <label>Phường/Xã <span class="text-danger">*</span></label>
                                    <input type="hidden" name="new_address[ward]" id="ocWardHidden" value="{{ old('new_address.ward') }}">
                                    <div class="hk-cat-filter oc-dropdown @error('new_address.ward') is-invalid @enderror" id="ocWardDrop">
                                        <button type="button" class="hk-cat-trigger" id="ocWardTrigger" aria-haspopup="listbox" aria-expanded="false" disabled>
                                            <span class="hk-cat-trigger-label" id="ocWardLabel">— Chọn tỉnh/thành trước —</span>
                                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                        </button>
                                        <div class="hk-cat-panel" id="ocWardPanel" hidden>
                                            <div class="hk-cat-list" id="ocWardList" role="listbox"></div>
                                        </div>
                                    </div>
                                    @error('new_address.ward') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12 edit-field mb-0">
                                    <label>Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                    <input type="text" name="new_address[apartment_number]" class="form-control @error('new_address.apartment_number') is-invalid @enderror" value="{{ old('new_address.apartment_number') }}">
                                    @error('new_address.apartment_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="section-title mb-3">Sản phẩm</div>
                        <div class="edit-field oc-search-wrap">
                            <label>Tìm sản phẩm (tên hoặc SKU)</label>
                            <input type="text" id="ocProductSearch" class="form-control" placeholder="Nhập tên sản phẩm hoặc SKU..." autocomplete="off">
                            <div class="oc-search-results d-none" id="ocProductResults"></div>
                        </div>

                        @error('items') <div class="text-danger mb-2" style="font-size:13px;">{{ $message }}</div> @enderror
                        {{-- Bảng sản phẩm bên dưới do JS dựng lại từ đầu (rỗng) mỗi lần tải trang,
                             không phục hồi được danh sách cũ khi validate lỗi — nên các lỗi
                             items.*.quantity (vd. vượt tồn kho) phải hiện riêng ở đây, nếu không
                             admin sẽ không thấy được lý do đơn bị từ chối. --}}
                        @php
                            $itemQuantityErrors = collect($errors->keys())
                                ->filter(fn ($key) => preg_match('/^items\.\d+\.quantity$/', $key))
                                ->map(fn ($key) => $errors->first($key));
                        @endphp
                        @if($itemQuantityErrors->isNotEmpty())
                            <div class="text-danger mb-2" style="font-size:13px;">
                                @foreach($itemQuantityErrors as $message)
                                    <div>{{ $message }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table oc-items-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:36px;">#</th>
                                        <th style="width:56px;">Ảnh</th>
                                        <th>Sản phẩm</th>
                                        <th style="width:110px;">Màu</th>
                                        <th style="width:70px;">Size</th>
                                        <th style="width:100px;">Đơn giá</th>
                                        <th style="width:90px;">SL</th>
                                        <th style="width:110px;">Thành tiền</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="ocItemsBody">
                                    <tr id="ocEmptyItemsRow">
                                        <td colspan="9" class="oc-empty-items">Chưa có sản phẩm nào. Tìm và thêm sản phẩm ở trên.</td>
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
                        <div class="edit-field mb-3">
                            <label>Mã giảm giá</label>
                            <div class="d-flex gap-2">
                                <input type="text" id="ocVoucherInput" class="form-control text-uppercase"
                                    placeholder="Nhập mã giảm giá..." autocomplete="off" value="{{ old('voucher_code') }}">
                                <button type="button" id="ocVoucherApplyBtn" class="btn btn-outline-dark fw-bold" style="white-space:nowrap;">Áp dụng</button>
                            </div>
                            <input type="hidden" name="voucher_code" id="ocVoucherHidden" value="">
                            <div id="ocVoucherMessage" style="font-size:12px;margin-top:4px;"></div>
                            @error('voucher_code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="oc-summary-row">
                            <span>Tạm tính</span>
                            <span id="ocSubtotal">0đ</span>
                        </div>
                        <div class="oc-summary-row" id="ocDiscountRow" style="display:none;">
                            <span>Giảm giá</span>
                            <span id="ocDiscountValue" class="fw-bold" style="color:#16A34A;">-0đ</span>
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

    /* ── Tìm & chọn khách hàng theo SĐT/email (gõ tên trùng nhau rất dễ chọn nhầm người —
       SĐT/email mới là định danh đáng tin để phân biệt khách; gõ số/email chưa có sẵn = tạo
       khách hàng mới khi lưu đơn). ── */
    (function () {
        const input   = document.getElementById('ocPhone');
        const results = document.getElementById('ocCustomerResults');
        const userIdField = document.getElementById('ocUserId');
        const nameField    = document.getElementById('ocCustomerName');
        let timer = null;
        let selectedPhone = null;

        function search(q) {
            fetch(`{{ route('admin.orders.searchCustomers') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => renderResults(data.customers || [], q));
        }

        function renderResults(customers, typedValue) {
            if (!customers.length) {
                results.innerHTML = '<div class="oc-search-item text-muted">Không tìm thấy khách hàng theo SĐT/email này — sẽ tạo khách hàng mới khi lưu đơn.</div>';
            } else {
                // Kiểm tra trùng khớp ngay khi gõ (realtime) — nếu SĐT gõ trùng khớp tuyệt đối với
                // một khách đã có, cảnh báo rõ ràng để admin bấm chọn thay vì lỡ submit rồi mới
                // biết (lúc đó bảng sản phẩm đã thêm có nguy cơ bị mất khi trang reload).
                const exactMatch = customers.find(c => c.phone_number === typedValue);
                const warningHtml = exactMatch
                    ? `<div class="oc-search-item oc-search-item--warn text-muted">⚠ SĐT này đã thuộc khách hàng <strong>${exactMatch.username}</strong> — bấm chọn bên dưới để dùng, không tạo trùng.</div>`
                    : '';
                results.innerHTML = warningHtml + customers.map(c => `
                    <div class="oc-search-item ${c.phone_number === typedValue ? 'oc-search-item--match' : ''}" data-id="${c.id}" data-name="${c.username}" data-phone="${c.phone_number ?? ''}">
                        <div>${c.username}</div>
                        <div class="oc-item-sub">${c.email ?? ''} ${c.phone_number ? '· ' + c.phone_number : ''}</div>
                    </div>
                `).join('');
            }
            results.classList.remove('d-none');
        }

        input.addEventListener('input', function () {
            // Sửa lại SĐT sau khi đã chọn 1 khách (hoặc gõ số hoàn toàn mới chưa từng chọn) →
            // bỏ liên kết tới khách đã chọn, coi như đang nhập khách hàng mới.
            if (userIdField.value !== '' && input.value !== selectedPhone) {
                userIdField.value = '';
                selectedPhone = null;
                // Đổi khách hàng → mã giảm giá đã áp (nếu có) được kiểm tra theo khách CŨ,
                // không còn đúng cho khách MỚI (giới hạn 1 lượt/khách áp dụng khác nhau).
                clearVoucher?.('Đã đổi khách hàng — vui lòng áp dụng lại mã giảm giá.');
            }
            if (!userIdField.value) {
                resetAddressForNewCustomer();
            }

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

            if (userIdField.value !== '' && userIdField.value !== item.dataset.id) {
                clearVoucher?.('Đã đổi khách hàng — vui lòng áp dụng lại mã giảm giá.');
            }

            userIdField.value = item.dataset.id;
            input.value = item.dataset.phone;
            selectedPhone = item.dataset.phone;
            nameField.value = item.dataset.name;
            results.classList.add('d-none');

            loadAddresses(item.dataset.id);
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#ocPhone') && !e.target.closest('#ocCustomerResults')) {
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
            if (onSelect) onSelect(btn.dataset.value, btn.dataset.label, btn);
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

    /* ── Chọn Tỉnh/Thành phố + Phường/Xã cho địa chỉ mới qua API địa giới hành chính
       (dùng chung route proxy /api/location/... với trang checkout), thay cho gõ tay tự do
       và bỏ hẳn trường Quận/Huyện (cơ cấu hành chính mới chỉ còn 2 cấp Tỉnh → Phường/Xã). ── */
    (function () {
        const provinceHidden = document.getElementById('ocProvinceHidden');
        const wardHidden      = document.getElementById('ocWardHidden');
        const oldCityName = provinceHidden.value;

        const provinceDropdown = wireDropdown({
            root: 'ocProvinceDrop', trigger: 'ocProvinceTrigger', panel: 'ocProvincePanel',
            label: 'ocProvinceLabel', list: 'ocProvinceList', hidden: 'ocProvinceHidden',
            onSelect: function (value, label, btn) {
                wardHidden.value = '';
                document.getElementById('ocWardLabel').textContent = '— Chọn phường/xã —';
                loadWards(btn.dataset.code);
            },
        });

        const wardDropdown = wireDropdown({
            root: 'ocWardDrop', trigger: 'ocWardTrigger', panel: 'ocWardPanel',
            label: 'ocWardLabel', list: 'ocWardList', hidden: 'ocWardHidden',
        });

        function loadWards(code) {
            if (!code) { wardDropdown.disable(); return; }
            wardDropdown.disable();
            document.getElementById('ocWardLabel').textContent = 'Đang tải...';
            fetch(`/api/location/provinces/${code}/wards`, { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(wards => {
                    const html = (wards || []).map(w =>
                        `<button type="button" class="hk-cat-item" data-value="${w.name}" data-label="${w.name}">${w.name}</button>`
                    ).join('');
                    wardDropdown.setOptions(html);
                    wardDropdown.enable();
                    document.getElementById('ocWardLabel').textContent = '— Chọn phường/xã —';
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
                        document.querySelector(`#ocProvinceList .hk-cat-item[data-value="${oldCityName}"]`)?.classList.add('is-active');
                        loadWards(match.code);
                    }
                }
            });
    }());

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

    /* ── Chưa/không còn gắn với khách hàng nào có sẵn → coi như đang nhập khách hàng mới,
       không có địa chỉ đã lưu nào nên luôn đưa thẳng về khối "nhập địa chỉ mới". ── */
    function resetAddressForNewCustomer() {
        const html = '<button type="button" class="hk-cat-item is-active" data-value="__new__" data-label="+ Thêm địa chỉ mới">+ Thêm địa chỉ mới</button>';
        addressDropdown.setOptions(html);
        addressDropdown.enable();
        document.getElementById('ocAddressLabel').textContent = '+ Thêm địa chỉ mới';
        addressIdField.value = '';
        newAddressFields.classList.remove('d-none');
    }

    /* ── Tìm & thêm sản phẩm ──
       Khôi phục lại danh sách đã thêm trước đó nếu trang này vừa reload sau một lần submit
       thất bại (vd. trùng SĐT) — nếu không, bảng sản phẩm sẽ trắng trơn dù dữ liệu khác vẫn
       được Laravel giữ lại qua old(). ── */
    const items = @json($oldItems ?? []);

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

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function resolveImageUrl(path) {
        if (!path) return null;
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    function thumbHtml(path) {
        const url = resolveImageUrl(path);
        if (url) return `<img src="${url}" class="oc-row-thumb" alt="">`;
        return `<div class="oc-row-thumb d-flex align-items-center justify-content-center border"><i class="fa-regular fa-image text-muted"></i></div>`;
    }

    function addItem(variant) {
        const existing = items.find(i => i.id === variant.id);
        if (existing) {
            existing.quantity = Math.min(existing.quantity + 1, variant.stock);
        } else {
            items.push({
                id: variant.id,
                name: variant.product_name,
                thumbnail: variant.thumbnail,
                color: variant.color,
                color_hex: variant.color_hex,
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
            body.innerHTML = '<tr id="ocEmptyItemsRow"><td colspan="9" class="oc-empty-items">Chưa có sản phẩm nào. Tìm và thêm sản phẩm ở trên.</td></tr>';
            hiddenWrap.innerHTML = '';
            updateSummary();
            return;
        }

        body.innerHTML = items.map((item, idx) => `
            <tr data-idx="${idx}">
                <td>${idx + 1}</td>
                <td>${thumbHtml(item.thumbnail)}</td>
                <td class="fw-semibold">${esc(item.name)}</td>
                <td>${item.color ? `<span class="oc-row-dot" style="background:${esc(item.color_hex || '#ccc')};"></span>${esc(item.color)}` : '—'}</td>
                <td>${esc(item.size) || '—'}</td>
                <td>${money(item.unit_price)}</td>
                <td><input type="number" class="form-control form-control-sm oc-qty-input" data-idx="${idx}" min="1" max="${item.stock}" value="${item.quantity}"></td>
                <td class="oc-item-total">${money(item.unit_price * item.quantity)}</td>
                <td><button type="button" class="btn btn-sm btn-light border oc-remove-item" data-idx="${idx}"><i class="fa-solid fa-trash"></i></button></td>
            </tr>
        `).join('');

        updateHiddenItemInputs();
        updateSummary();
    }

    function updateHiddenItemInputs() {
        document.getElementById('ocItemsHidden').innerHTML = items.map((item, idx) => `
            <input type="hidden" name="items[${idx}][product_variant_id]" value="${item.id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `).join('');
    }

    // Cập nhật số lượng tại chỗ (không renderItems() lại toàn bộ bảng) — renderItems() thay
    // mới các <input>, làm mất focus/con trỏ đang gõ dở nên trước đây gõ số 2 chữ số trở lên
    // sẽ bị bung ra khỏi ô sau mỗi phím bấm.
    document.getElementById('ocItemsBody').addEventListener('input', function (e) {
        if (!e.target.matches('.oc-qty-input')) return;
        const idx = Number(e.target.dataset.idx);
        const qty = parseInt(e.target.value, 10);
        if (!Number.isFinite(qty)) return; // đang gõ dở (rỗng...) — chưa ép giá trị vội

        const clamped = Math.min(qty, items[idx].stock);
        if (clamped !== qty) e.target.value = clamped;
        items[idx].quantity = clamped;

        document.querySelector(`#ocItemsBody tr[data-idx="${idx}"] .oc-item-total`).textContent = money(items[idx].unit_price * clamped);
        updateHiddenItemInputs();
        updateSummary();
    });

    document.getElementById('ocItemsBody').addEventListener('blur', function (e) {
        if (!e.target.matches('.oc-qty-input')) return;
        const idx = Number(e.target.dataset.idx);
        let qty = parseInt(e.target.value, 10);
        if (!Number.isFinite(qty) || qty < 1) qty = 1;
        qty = Math.min(qty, items[idx].stock);
        e.target.value = qty;
        items[idx].quantity = qty;

        document.querySelector(`#ocItemsBody tr[data-idx="${idx}"] .oc-item-total`).textContent = money(items[idx].unit_price * qty);
        updateHiddenItemInputs();
        updateSummary();
    }, true);

    document.getElementById('ocItemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.oc-remove-item');
        if (!btn) return;
        items.splice(Number(btn.dataset.idx), 1);
        renderItems();
    });

    const shippingInput = document.getElementById('ocShippingFee');
    shippingInput.addEventListener('input', updateSummary);

    /* ── Mã giảm giá — xem trước qua AJAX (giống trang checkout của khách), xác thực thật sự
       vẫn nằm ở server lúc submit (VoucherService dùng chung luật với checkout). ── */
    let appliedVoucher = null; // { code, discountAmount, subtotalAtApply }

    function setVoucherMessage(text, isError) {
        const el = document.getElementById('ocVoucherMessage');
        el.textContent = text || '';
        el.style.color = isError ? '#dc2626' : '#16a34a';
    }

    function clearVoucher(message) {
        appliedVoucher = null;
        document.getElementById('ocVoucherHidden').value = '';
        document.getElementById('ocDiscountRow').style.display = 'none';
        document.getElementById('ocVoucherInput').disabled = false;
        document.getElementById('ocVoucherApplyBtn').disabled = false;
        if (message) setVoucherMessage(message, true);
    }

    function applyVoucherCode(code) {
        code = (code || '').trim();
        if (!code) {
            setVoucherMessage('Vui lòng nhập mã giảm giá.', true);
            return;
        }

        const subtotal = items.reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
        const applyBtn = document.getElementById('ocVoucherApplyBtn');
        applyBtn.disabled = true;
        setVoucherMessage('Đang kiểm tra mã...', false);

        const params = new URLSearchParams({ code: code, subtotal: subtotal });
        const userId = document.getElementById('ocUserId').value;
        if (userId) params.set('user_id', userId);

        fetch(`{{ route('admin.orders.checkVoucher') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || !data.success) {
                    setVoucherMessage(data.message || 'Mã giảm giá không hợp lệ.', true);
                    applyBtn.disabled = false;
                    return;
                }

                appliedVoucher = { code: code, discountAmount: data.discount_amount, subtotalAtApply: subtotal };
                document.getElementById('ocVoucherHidden').value = code;
                document.getElementById('ocVoucherInput').disabled = true;
                applyBtn.disabled = true;
                setVoucherMessage('Áp dụng mã giảm giá thành công!', false);
                updateSummary();
            })
            .catch(() => {
                setVoucherMessage('Có lỗi xảy ra, vui lòng thử lại.', true);
                applyBtn.disabled = false;
            });
    }

    document.getElementById('ocVoucherApplyBtn').addEventListener('click', function () {
        applyVoucherCode(document.getElementById('ocVoucherInput').value);
    });

    function updateSummary() {
        const subtotal = items.reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
        const shipping = parseFloat(shippingInput.value) || 0;

        // Đơn hàng thay đổi (thêm/bớt/sửa số lượng sản phẩm) sau khi đã áp mã → giảm giá cũ
        // không còn đúng nữa (vd. không đạt tối thiểu, hoặc trần giảm % đã tính sai) — gỡ mã,
        // bắt admin áp lại theo đúng tạm tính mới.
        if (appliedVoucher && subtotal !== appliedVoucher.subtotalAtApply) {
            clearVoucher('Đơn hàng đã thay đổi — vui lòng áp dụng lại mã giảm giá.');
        }

        const discount = appliedVoucher ? appliedVoucher.discountAmount : 0;
        document.getElementById('ocSubtotal').textContent = money(subtotal);
        document.getElementById('ocSummaryShipping').textContent = money(shipping);
        if (discount > 0) {
            document.getElementById('ocDiscountRow').style.display = 'flex';
            document.getElementById('ocDiscountValue').textContent = '-' + money(discount);
        }
        document.getElementById('ocTotal').textContent = money(Math.max(subtotal - discount, 0) + shipping);
    }

    document.getElementById('createOrderForm').addEventListener('submit', function (e) {
        const hasExistingCustomer = !!document.getElementById('ocUserId').value;
        const hasNewCustomerName  = document.getElementById('ocCustomerName').value.trim() !== '';
        if (!hasExistingCustomer && !hasNewCustomerName) {
            e.preventDefault();
            alert('Vui lòng nhập hoặc chọn khách hàng.');
            return;
        }
        if (!items.length) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một sản phẩm.');
        }
    });

    if (items.length) {
        renderItems();
    } else {
        updateSummary();
    }

    // Trang reload sau 1 lần submit lỗi (vd. trùng SĐT) — mã giảm giá đã gõ vẫn còn trong ô
    // input (value cũ do Laravel giữ qua old()) nhưng chưa được xem trước lại; tự động áp lại
    // luôn để "Giảm giá" hiện đúng, không bắt admin phải bấm "Áp dụng" thêm lần nữa.
    {
        const oldVoucherCode = document.getElementById('ocVoucherInput').value.trim();
        if (oldVoucherCode) applyVoucherCode(oldVoucherCode);
    }

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
