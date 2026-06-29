@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@push('styles')
    @include('admin.orders.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
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
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.orders.partials.table')
            </div>
        </section>
    </div>
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

    }());
    </script>
    @include('admin.partials.realtime-table')
@endpush
