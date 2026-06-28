@extends('layouts.admin')

@section('title', $pageTitle ?? 'Quản lý tài khoản')

@section('css')
    @include('admin.users.styles')
@endsection

@section('content')
    @php
        $showRoleColumn = ($type ?? 'all') !== 'customer';
        $showAddressFields = in_array(($type ?? 'all'), ['staff', 'customer']);
        $nameColumnLabel = ($type ?? 'all') === 'customer' ? 'Tên khách hàng' : 'Họ và tên';
        $breadcrumbLabel = ($type ?? 'all') === 'customer' ? 'Quản lý khách hàng' : 'Quản lý nhân sự';
        $description = ($type ?? 'all') === 'staff'
            ? 'Danh sách tất cả nhân sự trong hệ thống.'
            : (($type ?? 'all') === 'customer' ? 'Danh sách tất cả khách hàng trong hệ thống.' : ($pageDescription ?? 'Danh sách tài khoản trong hệ thống.'));
        $searchPlaceholder = ($type ?? 'all') === 'customer' ? 'Tìm kiếm theo tên khách hàng...' : 'Tìm kiếm theo tên nhân sự...';
        $roleLabels = [
            'admin' => 'Quản trị viên',
            'staff' => 'Nhân viên',
            'customer' => 'Khách hàng',
        ];
        $currentUserId = auth()->id();
        $isStaffPage   = ($type ?? 'all') === 'staff';
        $isCustomerPage = ($type ?? 'all') === 'customer';
        $showBulkCheckbox = $isCustomerPage;
        $emptyColspan = ($showRoleColumn ? 7 : 6) + ($showBulkCheckbox ? 1 : 0);
    @endphp

    <main class="app-main account-admin-page container-fluid py-4">
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
                        <input type="search" id="accountRealtimeSearch" class="form-control account-search"
                            placeholder="{{ $searchPlaceholder }}" autocomplete="off">

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
                        <input type="search" name="keyword" id="customerKeyword"
                            value="{{ request('keyword') }}"
                            class="form-control account-search"
                            placeholder="{{ $searchPlaceholder }}"
                            autocomplete="off">

                        <input type="hidden" name="status" id="customerStatusHidden" value="{{ $statusVal }}">
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
                        <a href="{{ route(($routePrefix ?? 'admin.users') . '.create') }}" class="btn btn-dark account-action-btn">
                            <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="account-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover account-table align-middle" id="accountUsersTable">
                        <thead>
                            <tr>
                                @if($showBulkCheckbox)
                                    <th style="width: 58px;">
                                        <input type="checkbox" class="form-check-input account-check hk-cb-all" id="accountCheckAll">
                                    </th>
                                @endif
                                <th style="width: 86px;">
                                    <button type="button" class="account-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="account-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="username">
                                        {{ $nameColumnLabel }} <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="email">
                                        Email <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="phone_number">
                                        Số điện thoại <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                @if($showRoleColumn)
                                    <th>
                                        <button type="button" class="account-sort-btn" data-sort-key="role_name">
                                            Vai trò <span class="account-sort-icon">↑↓</span>
                                        </button>
                                    </th>
                                @endif
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="status">
                                        Trạng thái <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th class="text-end pe-4" style="width: 96px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $user)
                                @php
                                    $roleName = $user->roles->first()?->name;
                                    $roleLabel = $roleName ? ($roleLabels[$roleName] ?? $roleName) : 'Chưa có vai trò';
                                @endphp
                                <tr data-user-row="{{ $user->id }}" data-user-status="{{ $user->is_active ? '1' : '0' }}" data-protected="{{ $user->is_protected ? '1' : '0' }}">
                                    @if($showBulkCheckbox)
                                        <td>
                                            <input type="checkbox" class="form-check-input account-check account-row-check hk-cb-row" value="{{ $user->id }}">
                                        </td>
                                    @endif
                                    <td data-cell="id" data-sort-value="{{ $user->id }}">{{ $user->id }}</td>
                                    <td data-cell="username" data-sort-value="{{ $user->username }}">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
                                            <span>{{ $user->username }}</span>
                                            @if($isStaffPage && $user->is_protected)
                                                <span class="system-admin-chip">Admin hệ thống</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-cell="email" data-sort-value="{{ $user->email }}">
                                        <span class="fw-semibold">{{ $user->email }}</span>
                                    </td>
                                    <td data-cell="phone_number" data-sort-value="{{ $user->phone_number }}">
                                        {{ $user->phone_number ?: 'Chưa cập nhật' }}
                                    </td>
                                    @if($showRoleColumn)
                                        <td data-cell="role_name" data-sort-value="{{ $roleLabel }}">
                                            <span class="role-badge" data-role="{{ $roleName ?? '' }}">{{ $roleLabel }}</span>
                                        </td>
                                    @endif
                                    <td data-cell="status" data-sort-value="{{ $user->is_active ? 1 : 0 }}">
                                        <span class="status-badge {{ $user->is_active ? 'status-badge--active' : 'status-badge--inactive' }}">
                                            {{ $user->is_active ? 'Hoạt động' : 'Ngưng hoạt động' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="account-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end account-row-menu">
                                                <button type="button" class="dropdown-item js-view-user"
                                                    data-show-url="{{ route(($routePrefix ?? 'admin.users') . '.show', $user->id) }}">
                                                    <i class="fa-regular fa-eye"></i> Xem
                                                </button>
                                                <button type="button" class="dropdown-item js-edit-user"
                                                    data-show-url="{{ route(($routePrefix ?? 'admin.users') . '.show', $user->id) }}"
                                                    data-update-url="{{ route(($routePrefix ?? 'admin.users') . '.update', $user->id) }}">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </button>
                                                @if (!$isStaffPage && $user->id !== $currentUserId && ! $user->is_protected)
                                                    <button type="button" class="dropdown-item text-danger"
                                                        data-delete-url="{{ route(($routePrefix ?? 'admin.users') . '.destroy', $user->id) }}"
                                                        data-delete-name="{{ $user->username }}"
                                                        data-delete-type="{{ $itemLabelLower ?? 'tài khoản' }}">
                                                        <i class="fa-regular fa-trash-can"></i> Xóa
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="{{ $emptyColspan }}" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px;"></i>
                                        <div class="fw-semibold text-muted">Chưa có {{ $itemLabelLower ?? 'tài khoản' }} phù hợp</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $data,
                    'itemLabel'     => $itemLabelLower ?? 'tài khoản',
                    'bulkDeleteUrl' => null,
                    'bulkStatusUrl' => $isCustomerPage ? route('admin.customers.bulkUpdateStatus') : null,
                ])
            </div>
        </section>

        @include('admin.users.detail')
        @include('admin.users.edit')
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.users.scripts')
    <script>
        /* ── Customer filter (server-side) ───────────────────── */
        function submitCustomerFilter() {
            const form = document.getElementById('customerFilterForm');
            if (!form) return;
            const url = new URL(window.location.href);
            const keyword = form.elements.keyword?.value ?? '';
            const status  = form.elements.status?.value ?? '';
            keyword ? url.searchParams.set('keyword', keyword) : url.searchParams.delete('keyword');
            status !== '' ? url.searchParams.set('status', status) : url.searchParams.delete('status');
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        document.getElementById('customerKeyword')?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); submitCustomerFilter(); }
        });

        /* ── Custom status dropdown ── */
        (function () {
            const trigger = document.getElementById('hkStatusTrigger');
            const panel   = document.getElementById('hkStatusPanel');
            const label   = document.getElementById('hkStatusLabel');
            const list    = document.getElementById('hkStatusList');
            const hidden  = document.getElementById('customerStatusHidden');

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

            trigger?.addEventListener('click', function () { panel.hidden ? open() : close(); });

            list?.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                list.querySelectorAll('.hk-cat-item').forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');
                label.textContent = btn.dataset.label;
                if (hidden) hidden.value = btn.dataset.value;
                close();
                submitCustomerFilter();
            });

            document.addEventListener('click', function (e) {
                if (!panel?.hidden && !document.getElementById('hkStatusDrop')?.contains(e.target)) {
                    close();
                }
            });
        }());

        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('accountUsersTable');
            const searchInput = document.getElementById('accountRealtimeSearch');
            const checkAll = document.getElementById('accountCheckAll');

            if (!table) return;

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-user-row]'));
            let sortState = { key: 'id', dir: 'asc' };
            let staffStatusFilter = '';

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function filterRows() {
                const keyword = normalize(searchInput?.value);

                rows.forEach(row => {
                    const matchesKeyword = !keyword || normalize(row.innerText).includes(keyword);
                    const matchesStatus  = !staffStatusFilter || row.dataset.userStatus === staffStatusFilter;
                    row.hidden = !(matchesKeyword && matchesStatus);
                });

                if (checkAll) {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            }

            /* ── Staff status dropdown (client-side filter) ── */
            (function () {
                const stTrigger = document.getElementById('hkStaffStatusTrigger');
                const stPanel   = document.getElementById('hkStaffStatusPanel');
                const stLabel   = document.getElementById('hkStaffStatusLabel');
                const stList    = document.getElementById('hkStaffStatusList');

                if (!stTrigger) return;

                function open() {
                    stPanel.hidden = false;
                    stTrigger.classList.add('is-open');
                    stTrigger.setAttribute('aria-expanded', 'true');
                }
                function close() {
                    stPanel.hidden = true;
                    stTrigger.classList.remove('is-open');
                    stTrigger.setAttribute('aria-expanded', 'false');
                }

                stTrigger.addEventListener('click', function () { stPanel.hidden ? open() : close(); });

                stList.addEventListener('click', function (e) {
                    const btn = e.target.closest('.hk-cat-item');
                    if (!btn) return;
                    stList.querySelectorAll('.hk-cat-item').forEach(function (b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    stLabel.textContent = btn.dataset.label;
                    staffStatusFilter = btn.dataset.value;
                    close();
                    filterRows();
                });

                document.addEventListener('click', function (e) {
                    if (!stPanel.hidden && !document.getElementById('hkStaffStatusDrop')?.contains(e.target)) {
                        close();
                    }
                });
            }());

            function cellValue(row, key, type) {
                if (key === 'id') {
                    return Number(row.querySelector('[data-cell="id"]')?.dataset.sortValue || 0);
                }

                const cell = row.querySelector(`[data-cell="${key}"]`);
                const value = cell?.dataset.sortValue ?? cell?.innerText ?? '';

                return type === 'number' ? Number(value || 0) : normalize(value);
            }

            function setSortIcon(button, dir) {
                table.querySelectorAll('.account-sort-btn').forEach(btn => {
                    const icon = btn.querySelector('.account-sort-icon');
                    btn.classList.remove('is-active');
                    if (icon) icon.textContent = '↑↓';
                });

                const icon = button.querySelector('.account-sort-icon');
                button.classList.add('is-active');
                if (icon) {
                    icon.textContent = dir === 'asc' ? '↑' : '↓';
                }
            }

            table.querySelectorAll('.account-sort-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const key = this.dataset.sortKey;
                    const type = this.dataset.sortType || 'text';
                    const dir = sortState.key === key && sortState.dir === 'asc' ? 'desc' : 'asc';
                    sortState = { key, dir };

                    rows.sort((a, b) => {
                        const valueA = cellValue(a, key, type);
                        const valueB = cellValue(b, key, type);

                        if (valueA < valueB) return dir === 'asc' ? -1 : 1;
                        if (valueA > valueB) return dir === 'asc' ? 1 : -1;
                        return 0;
                    }).forEach(row => tbody.appendChild(row));

                    setSortIcon(this, dir);
                    filterRows();
                });
            });

            searchInput?.addEventListener('input', filterRows);

            checkAll?.addEventListener('change', function () {
                rows.filter(row => !row.hidden).forEach(row => {
                    const checkbox = row.querySelector('.account-row-check');
                    if (checkbox) checkbox.checked = this.checked;
                });
            });
        });
    </script>
@endpush
