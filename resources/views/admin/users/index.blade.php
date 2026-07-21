@extends('layouts.admin')

@section('title', $pageTitle ?? 'Quản lý tài khoản')

@push('styles')
    @include('admin.users.styles')
@endpush

@section('content')
    @php
        $showRoleColumn = ($type ?? 'all') !== 'customer';
        $showAddressFields = in_array(($type ?? 'all'), ['staff', 'customer']);
        $nameColumnLabel = ($type ?? 'all') === 'customer' ? 'Tên khách hàng' : 'Họ và tên';
        $breadcrumbLabel = ($type ?? 'all') === 'customer' ? 'Quản lý khách hàng' : 'Quản lý nhân sự';
        $description = ($type ?? 'all') === 'staff'
            ? 'Danh sách tất cả nhân sự trong hệ thống.'
            : (($type ?? 'all') === 'customer' ? 'Danh sách tất cả khách hàng trong hệ thống.' : ($pageDescription ?? 'Danh sách tài khoản trong hệ thống.'));
        $searchPlaceholder = ($type ?? 'all') === 'customer' ? 'Tìm kiếm theo tên khách hàng, email, SĐT...' : 'Tìm kiếm theo tên nhân sự, email, SĐT...';
        $roleLabels = [
            'admin' => 'Quản trị viên',
            'staff' => 'Nhân viên',
            'customer' => 'Khách hàng',
        ];
        $currentUserId = auth()->id();
        $currentUser   = auth()->user();
        $currentUserIsProtectedAdmin = $currentUser?->isAdmin() && (bool) $currentUser?->is_protected;
        $isStaffPage   = ($type ?? 'all') === 'staff';
        $isCustomerPage = ($type ?? 'all') === 'customer';
        $showBulkCheckbox = $isCustomerPage;
        $emptyColspan = ($showRoleColumn ? 7 : 6) + ($showBulkCheckbox ? 1 : 0);
    @endphp

    <div class="account-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="account-header-title mb-2" style="color: black;">{{ $pageTitle ?? 'Quản lý tài khoản' }}</h1>
                <p class="account-header-desc mb-0" style="color: #64748b;">{{ $description }}</p>
            </div>

            <div class="account-toolbar">
                @if ($isStaffPage)
                    {{-- Staff: tìm kiếm + lọc trạng thái client-side --}}
                    <div class="account-toolbar-left">
                        <input type="search" name="search" data-admin-search id="accountRealtimeSearch" class="form-control account-search"
                            value="{{ request('search', request('keyword')) }}" placeholder="{{ $searchPlaceholder }}" autocomplete="off">

                        <input type="hidden" name="status" data-admin-filter id="staffStatusHidden" value="{{ request('status', '') }}">
                        <div class="hk-cat-filter" id="hkStaffStatusDrop">
                            <button type="button" class="hk-cat-trigger" id="hkStaffStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkStaffStatusLabel">Tất cả trạng thái</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkStaffStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkStaffStatusList" role="listbox">
                                    <button type="button" class="hk-cat-item is-active" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                    <button type="button" class="hk-cat-item" data-value="1" data-label="Hoạt động">Hoạt động</button>
                                    <button type="button" class="hk-cat-item" data-value="0" data-label="Ngưng hoạt động">Ngưng hoạt động</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="account-tool-actions">
                        <a href="{{ route(($routePrefix ?? 'admin.users') . '.create') }}" class="btn btn-dark account-action-btn">
                            <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
                        </a>
                    </div>
                @else
                    {{-- Customer: tìm kiếm + lọc trạng thái server-side --}}
                    @php
                        $statusVal = request('status', '');
                        $statusLabelMap = ['' => 'Tất cả trạng thái', '1' => 'Hoạt động', '0' => 'Ngưng hoạt động'];
                    @endphp
                    <form method="GET" action="{{ route(($routePrefix ?? 'admin.users') . '.list') }}"
                          id="customerFilterForm" class="account-toolbar-left">
                        <input type="search" name="search" data-admin-search id="customerKeyword"
                            value="{{ request('search', request('keyword')) }}"
                            class="form-control account-search"
                            placeholder="{{ $searchPlaceholder }}"
                            autocomplete="off">

                        <input type="hidden" name="status" data-admin-filter id="customerStatusHidden" value="{{ $statusVal }}">
                        <div class="hk-cat-filter" id="hkStatusDrop">
                            <button type="button" class="hk-cat-trigger" id="hkStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkStatusLabel">{{ $statusLabelMap[$statusVal] ?? 'Tất cả trạng thái' }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkStatusList" role="listbox">
                                    <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                    <button type="button" class="hk-cat-item {{ $statusVal === '1' ? 'is-active' : '' }}" data-value="1" data-label="Hoạt động">Hoạt động</button>
                                    <button type="button" class="hk-cat-item {{ $statusVal === '0' ? 'is-active' : '' }}" data-value="0" data-label="Ngưng hoạt động">Ngưng hoạt động</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="account-tool-actions">
                        @if ($isCustomerPage)
                            <a href="{{ route(($routePrefix ?? 'admin.users') . '.trash') }}" class="btn btn-light border account-action-btn">
                                <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                            </a>
                        @endif
                        <a href="{{ route(($routePrefix ?? 'admin.users') . '.create') }}" class="btn btn-dark account-action-btn">
                            <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
                        </a>
                    </div>
                @endif
            </div>

            <div data-admin-table-area>
                @include('admin.users.partials.table')
            </div>
        </section>

        @include('admin.users.detail')
        @include('admin.users.edit')
        @include('admin.users.reset-password')
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.users.scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function wireStatusDropdown(config) {
            const trigger = document.getElementById(config.trigger);
            const panel = document.getElementById(config.panel);
            const label = document.getElementById(config.label);
            const list = document.getElementById(config.list);
            const hidden = document.getElementById(config.hidden);
            const root = document.getElementById(config.root);
            if (!trigger || !panel || !list || !hidden) return;

            function open() {
                panel.hidden = false;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
            function close() {
                panel.hidden = true;
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', function () { panel.hidden ? open() : close(); });
            list.addEventListener('click', function (event) {
                const btn = event.target.closest('.hk-cat-item');
                if (!btn) return;
                list.querySelectorAll('.hk-cat-item').forEach(function (item) { item.classList.remove('is-active'); });
                btn.classList.add('is-active');
                if (label) label.textContent = btn.dataset.label;
                hidden.value = btn.dataset.value || '';
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                close();
            });
            document.addEventListener('click', function (event) {
                if (!panel.hidden && !root?.contains(event.target)) close();
            });
        }

        wireStatusDropdown({
            root: 'hkStaffStatusDrop', trigger: 'hkStaffStatusTrigger', panel: 'hkStaffStatusPanel',
            label: 'hkStaffStatusLabel', list: 'hkStaffStatusList', hidden: 'staffStatusHidden'
        });
        wireStatusDropdown({
            root: 'hkStatusDrop', trigger: 'hkStatusTrigger', panel: 'hkStatusPanel',
            label: 'hkStatusLabel', list: 'hkStatusList', hidden: 'customerStatusHidden'
        });
    });
    </script>
    @include('admin.partials.realtime-table')
@endpush