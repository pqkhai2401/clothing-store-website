@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@push('styles')
    @include('admin.orders.styles')
    <style>
        /* Modal update order */
        .account-modal .modal-content {
            border: 1px solid #d8dee6;
            border-radius: 8px;
            overflow: hidden;
        }
        .account-modal .modal-header {
            background: #ffffff;
            border-bottom: 1px solid #d8dee6;
        }
        .account-modal .modal-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .account-action-btn {
            min-height: 38px;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
        }
        .account-action-btn.btn-dark {
            background: #16A34A !important;
            border-color: #16A34A !important;
            color: #fff !important;
        }
        .account-action-btn.btn-dark:hover {
            background: #15803D !important;
            border-color: #15803D !important;
        }
        .account-action-btn.btn-light {
            background: #fff !important;
            border: 1.5px solid #F87171 !important;
            color: #DC2626 !important;
        }
        .account-action-btn.btn-light:hover {
            background: #FEF2F2 !important;
            border-color: #EF4444 !important;
            color: #B91C1C !important;
        }
    </style>
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý đơn hàng</h1>
                    <p class="product-header-desc mb-0">Danh sách tất cả đơn hàng trong hệ thống.</p>
                </div>
                <div class="product-header-actions">
                    
                    <a href="{{ route('admin.orders.export') }}?{{ http_build_query(request()->except('page')) }}"
                       class="btn product-action-btn product-action-btn--neutral">
                        <i class="fa-solid fa-download me-1"></i> Xuất Excel
                    </a>
                    <a href="{{ route('admin.orders.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm đơn hàng
                    </a>
                </div>
            </div>

            <div data-admin-stats-area>
                @include('admin.orders.partials.stats')
            </div>

            @php
                $statusVal  = $statusFilter ?? '';
                $paymentVal = $paymentFilter ?? '';
                $selectedStatusLabel  = $statusVal  ? ($statusLabels[$statusVal]  ?? 'Tất cả trạng thái') : 'Tất cả trạng thái';
                $selectedPaymentLabel = $paymentVal ? ($paymentStatusLabels[$paymentVal] ?? 'Tất cả thanh toán') : 'Tất cả thanh toán';
            @endphp

            <form method="GET" action="{{ route('admin.orders.list') }}" id="orderFilterForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="status" data-admin-filter id="orderStatusHidden" value="{{ $statusVal }}">
                <input type="hidden" name="payment_status" data-admin-filter id="orderPaymentHidden" value="{{ $paymentVal }}">

                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="orderSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Mã đơn, tên khách hàng, SĐT..." autocomplete="off">

                    {{-- Filter trạng thái đơn --}}
                    <div class="hk-cat-filter" id="hkOrderStatusDrop">
                        <button type="button" class="hk-cat-trigger" id="hkOrderStatusTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkOrderStatusLabel">{{ $selectedStatusLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkOrderStatusPanel" hidden>
                            <div class="hk-cat-list" id="hkOrderStatusList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}"
                                    data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                @foreach($statusLabels as $val => $label)
                                    <button type="button" class="hk-cat-item {{ $statusVal === $val ? 'is-active' : '' }}"
                                        data-value="{{ $val }}" data-label="{{ $label }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Filter thanh toán --}}
                    <div class="hk-cat-filter" id="hkOrderPaymentDrop">
                        <button type="button" class="hk-cat-trigger" id="hkOrderPaymentTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkOrderPaymentLabel">{{ $selectedPaymentLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkOrderPaymentPanel" hidden>
                            <div class="hk-cat-list" id="hkOrderPaymentList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $paymentVal === '' ? 'is-active' : '' }}"
                                    data-value="" data-label="Tất cả thanh toán">Tất cả thanh toán</button>
                                @foreach($paymentStatusLabels as $val => $label)
                                    <button type="button" class="hk-cat-item {{ $paymentVal === $val ? 'is-active' : '' }}"
                                        data-value="{{ $val }}" data-label="{{ $label }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Dropdown chọn kỳ linh hoạt (Năm/Quý/Tháng) --}}
                    <div class="hk-cat-filter order-period-dropdown" id="orderPeriodDropdown">
                        <button type="button" class="hk-cat-trigger" id="orderPeriodTrigger" aria-haspopup="true" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="orderPeriodLabel">Chọn kỳ</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel order-period-panel" id="orderPeriodPanel" hidden>
                            <div class="order-period-quick-row">
                                <button type="button" class="order-period-quick-btn" id="orderPeriodThisYear">Năm nay</button>
                                <button type="button" class="order-period-quick-btn" id="orderPeriodLastYear">Năm ngoái</button>
                                <button type="button" class="order-period-quick-btn order-period-clear-btn" id="orderPeriodClear" style="color: #EF4444;">Xoá chọn</button>
                            </div>

                            <div class="order-period-year-row">
                                <label style="color: #000000 !important;">Năm</label>
                                <div class="hk-cat-filter order-period-year-filter" id="orderPeriodYearDrop">
                                    <button type="button" class="hk-cat-trigger" id="orderPeriodYearTrigger" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="hk-cat-trigger-label" id="orderPeriodYearLabel"></span>
                                        <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                    </button>
                                    <div class="hk-cat-panel" id="orderPeriodYearPanel" hidden>
                                        <div class="hk-cat-list" id="orderPeriodYearList" role="listbox"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="orderPeriodYear">
                            </div>

                            <div class="order-period-section">
                                <div class="order-period-section-title" style="color: #000000 !important;">Quý</div>
                                <div class="order-period-grid order-period-grid--quarter">
                                    <button type="button" class="order-period-cell" data-quarter="1">Quý 1</button>
                                    <button type="button" class="order-period-cell" data-quarter="2">Quý 2</button>
                                    <button type="button" class="order-period-cell" data-quarter="3">Quý 3</button>
                                    <button type="button" class="order-period-cell" data-quarter="4">Quý 4</button>
                                </div>
                            </div>

                            <div class="order-period-section">
                                <div class="order-period-section-title" style="color: #000000 !important;">Tháng</div>
                                <div class="order-period-grid order-period-grid--month">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <button type="button" class="order-period-cell" data-month="{{ $m }}">Th {{ $m }}</button>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Lọc theo khoảng thời gian --}}
                    <div class="order-date-range">
                        <input type="date" name="date_from" id="orderDateFrom" data-admin-filter class="form-control order-date-input"
                            value="{{ $dateFrom ?? '' }}" title="Từ ngày">
                        <span class="order-date-sep">—</span>
                        <input type="date" name="date_to" id="orderDateTo" data-admin-filter class="form-control order-date-input"
                            value="{{ $dateTo ?? '' }}" title="Đến ngày">
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.orders.partials.table')
            </div>
        </section>
    </div>

    {{-- ── Order Update Modal ── --}}
<div class="modal fade account-modal" id="orderUpdateModal" tabindex="-1" aria-labelledby="orderUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderUpdateModalLabel">
                    Cập nhật trạng thái đơn hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px; font-weight:700;">
                        Trạng thái thanh toán <span class="text-danger">*</span>
                    </label>
                    <select id="ouPaymentStatus" class="form-select" style="font-size:13px;">
                        <option value="unpaid">Chưa thanh toán</option>
                        <option value="paid">Đã thanh toán</option>
                    </select>
                    <div id="ouPaymentHint" class="form-text text-muted" style="font-size:12px; display:none;">
                        Đồng bộ tự động từ cổng thanh toán online, không thể sửa tay.
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label" style="font-size:13px; font-weight:700;">
                        Trạng thái giao hàng <span class="text-danger">*</span>
                    </label>
                    <select id="ouOrderStatus" class="form-select" style="font-size:13px;">
                        @foreach($statusLabels as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="ouError" class="text-danger mt-2" style="font-size:12px; display:none;"></div>
            </div>
            <div class="modal-footer justify-content-center gap-3 pb-4">
                <button type="button" class="btn account-action-btn btn-light" data-bs-dismiss="modal">
                    Huỷ
                </button>
                <button type="button" id="ouSaveBtn" class="btn account-action-btn btn-dark" style="min-width:120px;">
                    Cập nhật
                </button>
            </div>
        </div>
    </div>
</div>

@push('modals')
    @include('layouts.components.confirm.delete')
@endpush
@endsection

@push('scripts')
    <script>
    (function () {
        /* ── Order Update Modal ── */
        (function () {
            const modal        = new bootstrap.Modal(document.getElementById('orderUpdateModal'));
            const statusSel    = document.getElementById('ouOrderStatus');
            const paymentSel   = document.getElementById('ouPaymentStatus');
            const paymentHint  = document.getElementById('ouPaymentHint');
            const saveBtn      = document.getElementById('ouSaveBtn');
            const errBox       = document.getElementById('ouError');
            const csrf         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            let currentOrderId = null;
            let currentRow     = null;

            const statusLabels = @json($statusLabels);
            const statusTransitions = @json(\App\Http\Controllers\Admin\OrderController::STATUS_TRANSITIONS);

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-update-order]');
                if (!btn) return;
                currentOrderId = btn.dataset.updateOrder;
                currentRow     = btn.closest('tr');

                const currentStatus   = btn.dataset.status;
                const paymentStatus   = btn.dataset.payment;
                const isOnlineGateway = btn.dataset.onlineGateway === '1';
                // Đơn online-gateway (PayOS/MoMo) chưa "paid" thì chỉ được hủy, không xử lý tiếp —
                // khớp với gate canAdvanceOnlineOrder() ở backend.
                const blockedByPayment = isOnlineGateway && paymentStatus !== 'paid';
                let transitions = statusTransitions[currentStatus] ?? [];
                if (blockedByPayment) {
                    transitions = transitions.filter(function (s) { return s === 'cancelled'; });
                }
                const allowed = [currentStatus, ...transitions];
                statusSel.innerHTML = allowed.map(function (val) {
                    return `<option value="${val}">${statusLabels[val] ?? val}</option>`;
                }).join('');

                statusSel.value  = currentStatus;
                paymentSel.value = paymentStatus;
                // Đơn online-gateway: payment_status chỉ do webhook đồng bộ, khoá không cho sửa tay.
                paymentSel.disabled = isOnlineGateway;
                paymentHint.style.display = isOnlineGateway ? 'block' : 'none';
                errBox.style.display = 'none';
                errBox.textContent   = '';
                saveBtn.disabled = false;
                saveBtn.textContent = 'Cập nhật';
                modal.show();
            });

            saveBtn.addEventListener('click', async function () {
                if (!currentOrderId) return;
                saveBtn.disabled    = true;
                saveBtn.innerHTML   = '<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...';
                errBox.style.display = 'none';

                try {
                    const res = await fetch(`/admin/orders/${currentOrderId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            status:         statusSel.value,
                            payment_status: paymentSel.value,
                            note:           '',
                        }),
                    });

                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        const msg = data?.message || Object.values(data?.errors ?? {}).flat().join(' ') || 'Có lỗi xảy ra.';
                        errBox.textContent   = msg;
                        errBox.style.display = 'block';
                        saveBtn.disabled     = false;
                        saveBtn.textContent  = 'Cập nhật';
                        return;
                    }

                    modal.hide();
                    window.location.reload();
                    return;
                } catch {
                    errBox.textContent   = 'Không thể kết nối. Vui lòng thử lại.';
                    errBox.style.display = 'block';
                    saveBtn.disabled     = false;
                    saveBtn.textContent  = 'Cập nhật';
                }
            });
        }());

        /* ── Sổ xuống chọn nhanh trạng thái đơn / thanh toán ngay trong bảng ── */
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            function closeAllPanels(except) {
                document.querySelectorAll('.oc-row-panel').forEach(function (p) {
                    if (p === except) return;
                    p.hidden = true;
                    p.closest('.oc-row-dropdown')?.querySelector('.oc-row-trigger')?.classList.remove('is-open');
                });
            }

            document.addEventListener('click', async function (e) {
                const trigger = e.target.closest('.oc-row-trigger');
                if (trigger) {
                    const dropdown = trigger.closest('.oc-row-dropdown');
                    const panel = dropdown.querySelector('.oc-row-panel');
                    const willOpen = panel.hidden;
                    closeAllPanels();
                    panel.hidden = !willOpen;
                    trigger.classList.toggle('is-open', willOpen);
                    return;
                }

                const item = e.target.closest('.oc-row-panel .hk-cat-item');
                if (item) {
                    const dropdown = item.closest('.oc-row-dropdown');
                    const row       = dropdown.closest('tr');
                    const orderId   = dropdown.dataset.orderId;
                    const field     = dropdown.dataset.field;
                    const newValue  = item.dataset.value;
                    const newCss    = item.dataset.css;

                    // Dùng data-value ở phần tử [data-field] (dropdown tương tác hoặc badge tĩnh
                    // khi trạng thái/thanh toán bị khoá) để lấy giá trị hiện tại của field còn lại.
                    const statusDrop  = row.querySelector('[data-field="status"]');
                    const paymentDrop = row.querySelector('[data-field="payment_status"]');

                    const payload = {
                        status:         field === 'status' ? newValue : (statusDrop?.dataset.value ?? ''),
                        payment_status: field === 'payment_status' ? newValue : (paymentDrop?.dataset.value ?? ''),
                        note: '',
                    };

                    closeAllPanels();

                    const trigger = dropdown.querySelector('.oc-row-trigger');
                    const previousValue = trigger.dataset.value;
                    const previousCss   = Array.from(trigger.classList).find(c => c.startsWith('order-badge--') || c.startsWith('payment-badge--'));
                    trigger.disabled = true;

                    try {
                        const res = await fetch(`/admin/orders/${orderId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (!res.ok) throw new Error('update failed');

                        window.location.reload();
                        return;
                    } catch {
                        alert('Không thể cập nhật. Vui lòng thử lại.');
                        trigger.className = (field === 'status' ? 'order-badge' : 'payment-badge') + ' oc-row-trigger ' + (previousCss ?? '');
                        trigger.dataset.value = previousValue;
                    } finally {
                        trigger.disabled = false;
                    }
                    return;
                }

                if (!e.target.closest('.oc-row-dropdown')) {
                    closeAllPanels();
                }
            });
        }());

        /* ── Filter đơn giản dạng "trigger + panel + hidden input" (trạng thái đơn / thanh toán) ── */
        function initSimpleFilterDropdown(dropId, triggerId, panelId, labelId, listId, hiddenId) {
            const drop    = document.getElementById(dropId);
            const trigger = document.getElementById(triggerId);
            const panel   = document.getElementById(panelId);
            const label   = document.getElementById(labelId);
            const list    = document.getElementById(listId);
            const hidden  = document.getElementById(hiddenId);
            if (!trigger) return;

            function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
            function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

            trigger.addEventListener('click', () => panel.hidden ? open() : close());

            list.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                label.textContent = btn.dataset.label;
                hidden.value = btn.dataset.value;
                close();
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !drop?.contains(e.target)) close();
            });
        }

        initSimpleFilterDropdown('hkOrderStatusDrop', 'hkOrderStatusTrigger', 'hkOrderStatusPanel', 'hkOrderStatusLabel', 'hkOrderStatusList', 'orderStatusHidden');
        initSimpleFilterDropdown('hkOrderPaymentDrop', 'hkOrderPaymentTrigger', 'hkOrderPaymentPanel', 'hkOrderPaymentLabel', 'hkOrderPaymentList', 'orderPaymentHidden');

        /* ── Dropdown chọn kỳ linh hoạt (Năm nay/Năm ngoái/Chọn năm + Quý/Tháng của năm đó) ── */
        (function () {
            const root      = document.getElementById('orderPeriodDropdown');
            const trigger   = document.getElementById('orderPeriodTrigger');
            const panel     = document.getElementById('orderPeriodPanel');
            const label     = document.getElementById('orderPeriodLabel');
            const yearSelect  = document.getElementById('orderPeriodYear');
            const yearDrop    = document.getElementById('orderPeriodYearDrop');
            const yearTrigger = document.getElementById('orderPeriodYearTrigger');
            const yearPanel   = document.getElementById('orderPeriodYearPanel');
            const yearLabel   = document.getElementById('orderPeriodYearLabel');
            const yearList    = document.getElementById('orderPeriodYearList');
            const fromInput = document.getElementById('orderDateFrom');
            const toInput   = document.getElementById('orderDateTo');
            if (!root || !fromInput || !toInput) return;

            const nowYear = new Date().getFullYear();
            const yearsBack = 6;
            let yearItemsHtml = '';
            for (let y = nowYear; y >= nowYear - yearsBack; y--) {
                yearItemsHtml += `<button type="button" class="hk-cat-item" data-value="${y}">${y}</button>`;
            }
            yearList.innerHTML = yearItemsHtml;

            /* Đồng bộ nhãn + mục đang chọn của dropdown "Năm" theo giá trị hiện tại của yearSelect.
               Gọi lại hàm này sau MỖI lần yearSelect.value bị gán bằng JS (thay cho việc <select>
               tự vẽ lại UI của nó) để nhãn/hộp bo tròn luôn khớp với năm đang áp dụng. */
            function syncYearUI() {
                yearLabel.textContent = yearSelect.value;
                yearList.querySelectorAll('.hk-cat-item').forEach(function (b) {
                    b.classList.toggle('is-active', b.dataset.value === yearSelect.value);
                });
            }

            function openYearPanel()  { yearPanel.hidden = false; yearTrigger.classList.add('is-open'); yearTrigger.setAttribute('aria-expanded', 'true'); }
            function closeYearPanel() { yearPanel.hidden = true;  yearTrigger.classList.remove('is-open'); yearTrigger.setAttribute('aria-expanded', 'false'); }

            yearTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                yearPanel.hidden ? openYearPanel() : closeYearPanel();
            });

            yearList.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                yearSelect.value = btn.dataset.value;
                syncYearUI();
                closeYearPanel();
                yearSelect.dispatchEvent(new Event('change', { bubbles: true }));
            });

            document.addEventListener('click', function (e) {
                if (!yearPanel.hidden && !yearDrop.contains(e.target)) closeYearPanel();
            });

            yearSelect.value = String(nowYear);
            syncYearUI();

            function pad(n) { return String(n).padStart(2, '0'); }
            function iso(y, m, d) { return y + '-' + pad(m) + '-' + pad(d); }
            function lastDayOfMonth(y, m) { return new Date(y, m, 0).getDate(); }

            function yearRange(y) { return [iso(y, 1, 1), iso(y, 12, 31)]; }
            function quarterRange(y, q) {
                const startMonth = (q - 1) * 3 + 1;
                const endMonth   = startMonth + 2;
                return [iso(y, startMonth, 1), iso(y, endMonth, lastDayOfMonth(y, endMonth))];
            }
            function monthRange(y, m) {
                return [iso(y, m, 1), iso(y, m, lastDayOfMonth(y, m))];
            }

            function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
            function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

            function apply(range, labelText) {
                fromInput.value = range[0];
                toInput.value   = range[1];
                label.textContent = labelText;
                close();
                fromInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            trigger.addEventListener('click', () => panel.hidden ? open() : close());
            document.addEventListener('click', function (e) {
                if (!panel.hidden && !root.contains(e.target)) close();
            });

            document.getElementById('orderPeriodThisYear').addEventListener('click', function () {
                yearSelect.value = String(nowYear);
                syncYearUI();
                apply(yearRange(nowYear), 'Năm nay');
            });

            document.getElementById('orderPeriodLastYear').addEventListener('click', function () {
                yearSelect.value = String(nowYear - 1);
                syncYearUI();
                apply(yearRange(nowYear - 1), 'Năm ngoái');
            });

            yearSelect.addEventListener('change', function () {
                const y = Number(yearSelect.value);
                apply(yearRange(y), 'Năm ' + y);
            });

            panel.querySelectorAll('[data-quarter]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const y = Number(yearSelect.value);
                    const q = Number(btn.dataset.quarter);
                    apply(quarterRange(y, q), 'Quý ' + q + '/' + y);
                });
            });

            panel.querySelectorAll('[data-month]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const y = Number(yearSelect.value);
                    const m = Number(btn.dataset.month);
                    apply(monthRange(y, m), 'Tháng ' + m + '/' + y);
                });
            });

            /* Đồng bộ nhãn khi bộ lọc ngày hiện có (vd sau khi tải lại trang) khớp đúng 1 kỳ */
            (function syncLabelFromInputs() {
                const fromVal = fromInput.value;
                const toVal   = toInput.value;
                if (!fromVal || !toVal) return;

                for (let y = nowYear; y >= nowYear - yearsBack; y--) {
                    const yr = yearRange(y);
                    if (fromVal === yr[0] && toVal === yr[1]) {
                        label.textContent = y === nowYear ? 'Năm nay' : (y === nowYear - 1 ? 'Năm ngoái' : 'Năm ' + y);
                        yearSelect.value = String(y);
                        syncYearUI();
                        return;
                    }
                    for (let q = 1; q <= 4; q++) {
                        const qr = quarterRange(y, q);
                        if (fromVal === qr[0] && toVal === qr[1]) {
                            label.textContent = 'Quý ' + q + '/' + y;
                            yearSelect.value = String(y);
                            syncYearUI();
                            return;
                        }
                    }
                    for (let m = 1; m <= 12; m++) {
                        const mr = monthRange(y, m);
                        if (fromVal === mr[0] && toVal === mr[1]) {
                            label.textContent = 'Tháng ' + m + '/' + y;
                            yearSelect.value = String(y);
                            syncYearUI();
                            return;
                        }
                    }
                }

                label.textContent = 'Khoảng đã chọn';
            }());

            // Nút xóa chọn kỳ
            document.getElementById('orderPeriodClear')?.addEventListener('click', function () {
                fromInput.value = '';
                toInput.value   = '';
                label.textContent = 'Chọn kỳ';
                close();
                fromInput.dispatchEvent(new Event('change', { bubbles: true }));
            });

            // Lắng nghe thay đổi trực tiếp trên inputs ngày để cập nhật nhãn
            const handleManualDateClear = function () {
                if (fromInput.value === '' && toInput.value === '') {
                    label.textContent = 'Chọn kỳ';
                }
            };
            fromInput.addEventListener('change', handleManualDateClear);
            toInput.addEventListener('change', handleManualDateClear);
        }());

    }());
    </script>

    @include('admin.partials.realtime-table')
@endpush
