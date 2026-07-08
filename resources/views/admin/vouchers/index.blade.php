@extends('layouts.admin')

@section('title', 'Quản lý voucher')

@push('styles')
    @include('admin.vouchers.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý voucher</h1>
                    <p class="product-header-desc mb-0">Danh sách tất cả mã giảm giá trong hệ thống.</p>
                </div>
                <div class="product-header-actions">
                    <a href="{{ route('admin.vouchers.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm voucher
                    </a>
                    <a href="{{ route('admin.vouchers.export') }}?{{ http_build_query(request()->except('page')) }}"
                       class="btn product-action-btn product-action-btn--neutral">
                        <i class="fa-solid fa-download me-1"></i> Xuất Excel
                    </a>
                </div>
            </div>

            <div data-admin-stats-area>
                @include('admin.vouchers.partials.stats')
            </div>

            @php
                $statusVal = $statusFilter ?? '';
                $statusLabelMap = ['' => 'Tất cả trạng thái', '1' => 'Hoạt động', '0' => 'Khóa'];
            @endphp

            <form method="GET" action="{{ route('admin.vouchers.list') }}" id="voucherFilterForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="status" data-admin-filter id="voucherStatusHidden" value="{{ $statusVal }}">

                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="voucherSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm theo mã voucher..." autocomplete="off">

                    {{-- Filter trạng thái --}}
                    <div class="hk-cat-filter" id="hkVoucherStatusDrop">
                        <button type="button" class="hk-cat-trigger" id="hkVoucherStatusTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkVoucherStatusLabel">{{ $statusLabelMap[$statusVal] ?? 'Tất cả trạng thái' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkVoucherStatusPanel" hidden>
                            <div class="hk-cat-list" id="hkVoucherStatusList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '1' ? 'is-active' : '' }}" data-value="1" data-label="Hoạt động">Hoạt động</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '0' ? 'is-active' : '' }}" data-value="0" data-label="Khóa">Khóa</button>
                            </div>
                        </div>
                    </div>

                    {{-- Lọc theo khoảng ngày hiệu lực --}}
                    <div class="voucher-date-range">
                        <input type="date" name="date_from" id="voucherDateFrom" data-admin-filter class="form-control voucher-date-input"
                            value="{{ $dateFrom ?? '' }}" title="Từ ngày">
                        <span class="voucher-date-sep">—</span>
                        <input type="date" name="date_to" id="voucherDateTo" data-admin-filter class="form-control voucher-date-input"
                            value="{{ $dateTo ?? '' }}" title="Đến ngày">
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.vouchers.partials.table')
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        /* ── Filter trạng thái ── */
        (function () {
            const trigger = document.getElementById('hkVoucherStatusTrigger');
            const panel   = document.getElementById('hkVoucherStatusPanel');
            const label   = document.getElementById('hkVoucherStatusLabel');
            const list    = document.getElementById('hkVoucherStatusList');
            const hidden  = document.getElementById('voucherStatusHidden');
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
                if (!panel.hidden && !document.getElementById('hkVoucherStatusDrop')?.contains(e.target)) close();
            });
        }());

        /* ── Sổ xuống chọn nhanh trạng thái ngay trong bảng ── */
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function closeAllPanels(except) {
                document.querySelectorAll('.voucher-status-panel').forEach(function (p) {
                    if (p === except) return;
                    p.hidden = true;
                    p.closest('.voucher-status-dropdown')?.querySelector('.voucher-status-trigger')?.classList.remove('is-open');
                });
            }

            document.addEventListener('click', async function (e) {
                const trigger = e.target.closest('.voucher-status-trigger');
                if (trigger) {
                    const dropdown = trigger.closest('.voucher-status-dropdown');
                    const panel = dropdown.querySelector('.voucher-status-panel');
                    const willOpen = panel.hidden;
                    closeAllPanels();
                    panel.hidden = !willOpen;
                    trigger.classList.toggle('is-open', willOpen);
                    return;
                }

                const item = e.target.closest('.voucher-status-panel .hk-cat-item');
                if (item) {
                    const dropdown = item.closest('.voucher-status-dropdown');
                    const btn = dropdown.querySelector('.voucher-status-trigger');
                    const newValue = item.dataset.value;
                    const newCss = item.dataset.css;
                    closeAllPanels();

                    if (newValue === btn.dataset.value) return;

                    const previousValue = btn.dataset.value;
                    const previousCss = Array.from(btn.classList).find(c => c.startsWith('status-badge--'));
                    btn.disabled = true;

                    try {
                        const res = await fetch(dropdown.dataset.toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Accept':           'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN':     csrf,
                            },
                            body: new URLSearchParams({ _method: 'PATCH' }),
                        });

                        if (!res.ok) throw new Error('toggle failed');

                        btn.className = 'status-badge voucher-status-trigger ' + newCss;
                        btn.dataset.value = newValue;
                        btn.querySelector('.voucher-status-trigger-label').textContent = item.textContent;
                        dropdown.querySelectorAll('.hk-cat-item').forEach(function (b) {
                            b.classList.toggle('is-active', b === item);
                        });
                    } catch {
                        alert('Không thể cập nhật trạng thái. Vui lòng thử lại.');
                        btn.className = 'status-badge voucher-status-trigger ' + (previousCss ?? '');
                        btn.dataset.value = previousValue;
                    } finally {
                        btn.disabled = false;
                    }
                    return;
                }

                if (!e.target.closest('.voucher-status-dropdown')) {
                    closeAllPanels();
                }
            });
        }());
    }());
    </script>
@endpush
