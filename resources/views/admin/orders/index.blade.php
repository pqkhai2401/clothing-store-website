@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@push('styles')
    @include('admin.orders.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý đơn hàng</h1>
                <p class="product-header-desc mb-0">Danh sách tất cả đơn hàng trong hệ thống.</p>
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
                <input type="hidden" name="status" id="orderStatusHidden" value="{{ $statusVal }}">
                <input type="hidden" name="payment_status" id="orderPaymentHidden" value="{{ $paymentVal }}">

                <div class="product-toolbar-left">
                    <input type="search" name="keyword" id="orderSearch"
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
                </div>
            </form>

            <div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="orderTable">
                        <thead>
                            <tr>
                                <th style="width:150px;">Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th style="width:120px;">Số điện thoại</th>
                                <th style="width:140px;">Tổng tiền</th>
                                <th style="width:110px;">Phí ship</th>
                                <th style="width:150px;">Trạng thái đơn</th>
                                <th style="width:150px;">Thanh toán</th>
                                <th style="width:120px;">Ngày đặt</th>
                                <th class="text-center" style="width:90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $orderBadgeCss = [
                                    'pending'   => 'order-badge--pending',
                                    'confirmed' => 'order-badge--confirmed',
                                    'shipping'  => 'order-badge--shipping',
                                    'delivered' => 'order-badge--delivered',
                                    'cancelled' => 'order-badge--cancelled',
                                ];
                            @endphp
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <span class="order-code">{{ $order->order_code ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $order->user?->username ?? 'Khách vãng lai' }}</div>
                                        @if($order->user?->email)
                                            <div class="text-muted" style="font-size:11px;">{{ $order->user->email }}</div>
                                        @endif
                                    </td>
                                    <td style="color:#64748B;font-size:13px;">{{ $order->phone ?? '—' }}</td>
                                    <td>
                                        <span class="fw-bold" style="color:#0F172A;">
                                            {{ number_format($order->total_money, 0, ',', '.') }}₫
                                        </span>
                                    </td>
                                    <td style="color:#64748B;font-size:13px;">
                                        @if($order->shipping_fee > 0)
                                            {{ number_format($order->shipping_fee, 0, ',', '.') }}₫
                                        @else
                                            <span class="text-muted">Miễn phí</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="order-badge {{ $orderBadgeCss[$order->status] ?? '' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                            <span class="payment-badge payment-badge--paid">Đã thanh toán</span>
                                        @else
                                            <span class="payment-badge payment-badge--unpaid">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                    <td style="color:#64748B;font-size:13px;">
                                        {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-eye"></i> Xem chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có đơn hàng nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator' => $orders,
                    'itemLabel' => 'đơn hàng',
                ])
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
    (function () {
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
                document.getElementById('orderFilterForm')?.submit();
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
                document.getElementById('orderFilterForm')?.submit();
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !document.getElementById('hkOrderPaymentDrop')?.contains(e.target)) close();
            });
        }());

        /* ── Search: Enter submits form ── */
        document.getElementById('orderSearch')?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); document.getElementById('orderFilterForm')?.submit(); }
        });
    }());
    </script>
@endpush
