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
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm đơn hàng
                    </a>
                    <a href="{{ route('admin.orders.export') }}?{{ http_build_query(request()->except('page')) }}"
                       class="btn product-action-btn product-action-btn--neutral">
                        <i class="fa-solid fa-download me-1"></i> Xuất Excel
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

                    {{-- Chip chọn nhanh theo kỳ --}}
                    <div class="order-period-chips" id="orderPeriodChips">
                        <button type="button" class="order-period-chip" data-period="today">Hôm nay</button>
                        <button type="button" class="order-period-chip" data-period="week">Tuần này</button>
                        <button type="button" class="order-period-chip" data-period="month">Tháng này</button>
                        <button type="button" class="order-period-chip" data-period="quarter">Quý này</button>
                        <button type="button" class="order-period-chip" data-period="year">Năm này</button>
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
@endsection

@push('scripts')
    <script>
    (function () {
        /* ── Order Update Modal ── */
        (function () {
            const modal       = new bootstrap.Modal(document.getElementById('orderUpdateModal'));
            const statusSel   = document.getElementById('ouOrderStatus');
            const paymentSel  = document.getElementById('ouPaymentStatus');
            const saveBtn     = document.getElementById('ouSaveBtn');
            const errBox      = document.getElementById('ouError');
            const csrf        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            let currentOrderId = null;
            let currentRow     = null;

            const statusLabels = @json($statusLabels);
            const orderBadgeCss = {
                pending:    'order-badge--pending',
                processing: 'order-badge--processing',
                shipping:   'order-badge--shipping',
                completed:  'order-badge--completed',
                cancelled:  'order-badge--cancelled',
            };

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-update-order]');
                if (!btn) return;
                currentOrderId = btn.dataset.updateOrder;
                currentRow     = btn.closest('tr');
                statusSel.value  = btn.dataset.status;
                paymentSel.value = btn.dataset.payment;
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

                    /* Update row in DOM */
                    if (currentRow) {
                        const newStatus  = statusSel.value;
                        const newPayment = paymentSel.value;

                        const badgeEl = currentRow.querySelector('.order-badge');
                        if (badgeEl) {
                            badgeEl.className = 'order-badge ' + (orderBadgeCss[newStatus] ?? '');
                            badgeEl.textContent = statusLabels[newStatus] ?? newStatus;
                        }

                        const payEl = currentRow.querySelector('.payment-badge');
                        if (payEl) {
                            payEl.className  = 'payment-badge ' + (newPayment === 'paid' ? 'payment-badge--paid' : 'payment-badge--unpaid');
                            payEl.textContent = newPayment === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
                        }

                        const trigger = currentRow.querySelector('[data-update-order]');
                        if (trigger) {
                            trigger.dataset.status  = newStatus;
                            trigger.dataset.payment = newPayment;
                        }
                    }

                    modal.hide();
                } catch {
                    errBox.textContent   = 'Không thể kết nối. Vui lòng thử lại.';
                    errBox.style.display = 'block';
                    saveBtn.disabled     = false;
                    saveBtn.textContent  = 'Cập nhật';
                }
            });
        }());

        /* ── Status dropdown ── */
        (function () {
            const trigger = document.getElementById('hkOrderStatusTrigger');
            const panel   = document.getElementById('hkOrderStatusPanel');
            const label   = document.getElementById('hkOrderStatusLabel');
            const list    = document.getElementById('hkOrderStatusList');
            const hidden  = document.getElementById('orderStatusHidden');
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
                if (!panel.hidden && !document.getElementById('hkOrderStatusDrop')?.contains(e.target)) close();
            });
        }());

        /* ── Payment dropdown ── */
        (function () {
            const trigger = document.getElementById('hkOrderPaymentTrigger');
            const panel   = document.getElementById('hkOrderPaymentPanel');
            const label   = document.getElementById('hkOrderPaymentLabel');
            const list    = document.getElementById('hkOrderPaymentList');
            const hidden  = document.getElementById('orderPaymentHidden');
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
                if (!panel.hidden && !document.getElementById('hkOrderPaymentDrop')?.contains(e.target)) close();
            });
        }());

        /* ── Chip chọn nhanh theo kỳ (Hôm nay/Tuần/Tháng/Quý/Năm) ── */
        (function () {
            const chips     = document.querySelectorAll('#orderPeriodChips .order-period-chip');
            const fromInput = document.getElementById('orderDateFrom');
            const toInput   = document.getElementById('orderDateTo');
            if (!chips.length || !fromInput || !toInput) return;

            function pad(n) { return String(n).padStart(2, '0'); }
            function iso(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

            function rangeFor(period) {
                const now = new Date();
                let from, to;

                switch (period) {
                    case 'today':
                        from = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        to   = new Date(from);
                        break;
                    case 'week': {
                        const day = (now.getDay() + 6) % 7; // Thứ 2 = 0
                        from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - day);
                        to   = new Date(from.getFullYear(), from.getMonth(), from.getDate() + 6);
                        break;
                    }
                    case 'month':
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                        to   = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        break;
                    case 'quarter': {
                        const q = Math.floor(now.getMonth() / 3);
                        from = new Date(now.getFullYear(), q * 3, 1);
                        to   = new Date(now.getFullYear(), q * 3 + 3, 0);
                        break;
                    }
                    case 'year':
                        from = new Date(now.getFullYear(), 0, 1);
                        to   = new Date(now.getFullYear(), 11, 31);
                        break;
                    default:
                        return null;
                }

                return [iso(from), iso(to)];
            }

            function syncActiveChips() {
                const fromVal = fromInput.value;
                const toVal   = toInput.value;
                chips.forEach(function (chip) {
                    const range  = rangeFor(chip.dataset.period);
                    const active = !!range && fromVal === range[0] && toVal === range[1];
                    chip.classList.toggle('is-active', active);
                });
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    const range = rangeFor(chip.dataset.period);
                    if (!range) return;
                    fromInput.value = range[0];
                    toInput.value   = range[1];
                    syncActiveChips();
                    fromInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            fromInput.addEventListener('change', syncActiveChips);
            toInput.addEventListener('change', syncActiveChips);
            syncActiveChips();
        }());

    }());
    </script>

    @include('admin.partials.realtime-table')
@endpush
