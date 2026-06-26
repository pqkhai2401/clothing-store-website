@extends('layouts.admin')

@section('title', $pageTitle ?? 'Quản lý tài khoản')

@section('css')
    <style>
        .account-admin-page {
            background: #f8fafc;
            min-height: calc(100vh - 56px);
            padding-top: 0 !important;
        }


        .account-header-title {
            color: #020617;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: 0;
            margin-top: 15px;
        }

        .account-header-desc {
            color: #64748b;
            font-size: 16px;
        }

        .account-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 30px 0 16px;
        }

        .account-search {
            width: min(380px, 100%);
            min-height: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            font-size: 14px;
        }

        .account-search:focus {
            border-color: #111827;
            box-shadow: none;
        }

        .account-tool-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .account-action-btn {
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
        }

        .account-action-btn.btn-dark {
            background: #020617;
            border-color: #020617;
        }

        .account-table-wrap {
            overflow: hidden;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
        }

        .account-table {
            margin: 0;
            --bs-table-hover-bg: #f8fafc;
        }

        .account-table thead th {
            height: 48px;
            padding: 0 16px;
            background: #fff;
            color: #020617;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            vertical-align: middle;
            white-space: nowrap;
        }

        .account-table tbody td {
            min-height: 64px;
            padding: 15px 16px;
            color: #0f172a;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            vertical-align: middle;
        }

        .account-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .account-check {
            width: 18px;
            height: 18px;
            cursor: pointer;
            border-color: #d1d5db;
            border-radius: 5px;
        }

        .account-sort-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0;
            color: inherit;
            background: transparent;
            border: 0;
            font: inherit;
            font-weight: 800;
        }

        .account-sort-icon {
            display: inline-flex;
            align-items: center;
            min-width: 20px;
            color: #111827;
            font-size: 15px;
            font-weight: 800;
            line-height: 1;
        }

        .account-sort-btn.is-active {
            padding: 8px 10px;
            border-radius: 10px;
            background: #f3f4f6;
        }

        .account-sort-btn.is-active .account-sort-icon {
            color: #111827;
            font-size: 16px;
        }

        .status-badge {
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            padding: 5px 9px;
        }

        .account-more-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 10px;
            color: #111827;
            background: #f3f4f6;
            font-weight: 900;
        }

        .account-more-btn:hover {
            background: #e5e7eb;
        }

        .account-row-menu {
            min-width: 168px;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
        }

        .account-row-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 38px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
        }

        .account-modal .modal-content {
            border: 1px solid #d8dee6;
            border-radius: 8px;
            overflow: hidden;
        }

        .account-modal .modal-content>form {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        .account-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }

        .account-modal .modal-header {
            background: #ffffff;
            border-bottom: 1px solid #d8dee6;
            flex-shrink: 0;
        }

        .account-modal .modal-footer {
            flex-shrink: 0;
        }

        .account-modal .modal-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .account-modal .form-label {
            font-size: 13px;
            font-weight: 700;
        }

        .account-modal .form-control,
        .account-modal .form-select {
            border-radius: 3px;
            font-size: 14px;
        }

        .account-modal .is-invalid {
            border-color: #dc3545;
        }

        .account-modal-readonly {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            border: 1px solid #d8dee6;
            border-bottom: 0;
        }

        .account-modal-readonly:last-child {
            border-bottom: 1px solid #d8dee6;
        }

        .account-modal-readonly-label {
            padding: 9px 12px;
            background: #e9ecef;
            border-right: 1px solid #d8dee6;
            font-size: 13px;
            font-weight: 700;
        }

        .account-modal-readonly-value {
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 600;
            word-break: break-word;
        }

        .account-modal-loading {
            padding: 28px 12px;
            color: #6b7280;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .account-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .account-tool-actions {
                justify-content: flex-end;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $showRoleColumn = ($type ?? 'all') !== 'customer';
        $showAddressFields = in_array(($type ?? 'all'), ['staff', 'customer']);
        $emptyColspan = $showRoleColumn ? 8 : 7;
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
    @endphp

    <main class="app-main account-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="account-header-title mb-2">{{ $pageTitle ?? 'Quản lý tài khoản' }}</h1>
                <p class="account-header-desc mb-0">{{ $description }}</p>
            </div>

            <div class="account-toolbar">
                <input type="search" id="accountRealtimeSearch" class="form-control account-search"
                    placeholder="{{ $searchPlaceholder }}" autocomplete="off">

                <div class="account-tool-actions">
                    <a href="{{ route(($routePrefix ?? 'admin.users') . '.trash') }}" class="btn btn-light border account-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="{{ route(($routePrefix ?? 'admin.users') . '.create') }}" class="btn btn-dark account-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
                    </a>
                </div>
            </div>

            <div class="account-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover account-table align-middle" id="accountUsersTable">
                        <thead>
                            <tr>
                                <th style="width: 58px;">
                                    <input type="checkbox" class="form-check-input account-check hk-cb-all" id="accountCheckAll">
                                </th>
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
                                <tr data-user-row="{{ $user->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input account-check account-row-check hk-cb-row" value="{{ $user->id }}">
                                    </td>
                                    <td data-sort-value="{{ $user->id }}">{{ $user->id }}</td>
                                    <td data-cell="username" data-sort-value="{{ $user->username }}">
                                        <div class="fw-bold text-dark">{{ $user->username }}</div>
                                    </td>
                                    <td data-cell="email" data-sort-value="{{ $user->email }}">
                                        <span class="fw-semibold">{{ $user->email }}</span>
                                    </td>
                                    <td data-cell="phone_number" data-sort-value="{{ $user->phone_number }}">
                                        {{ $user->phone_number ?: 'Chưa cập nhật' }}
                                    </td>
                                    @if($showRoleColumn)
                                        <td data-cell="role_name" data-sort-value="{{ $roleLabel }}">
                                            {{ $roleLabel }}
                                        </td>
                                    @endif
                                    <td data-cell="status" data-sort-value="{{ $user->is_active ? 1 : 0 }}">
                                        <span class="badge status-badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $user->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
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
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route(($routePrefix ?? 'admin.users') . '.destroy', $user->id) }}"
                                                    data-delete-name="{{ $user->username }}"
                                                    data-delete-type="{{ $itemLabelLower ?? 'tài khoản' }}">
                                                    <i class="fa-regular fa-trash-can"></i> Xóa
                                                </button>
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
                    'bulkDeleteUrl' => route(($routePrefix ?? 'admin.users') . '.bulkDelete'),
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
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('accountUsersTable');
            const searchInput = document.getElementById('accountRealtimeSearch');
            const checkAll = document.getElementById('accountCheckAll');

            if (!table) return;

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-user-row]'));
            let sortState = { key: 'id', dir: 'asc' };

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function filterRows() {
                const keyword = normalize(searchInput?.value);

                rows.forEach(row => {
                    row.hidden = keyword && !normalize(row.innerText).includes(keyword);
                });

                if (checkAll) {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            }

            function cellValue(row, key, type) {
                if (key === 'id') {
                    return Number(row.children[1]?.dataset.sortValue || 0);
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
