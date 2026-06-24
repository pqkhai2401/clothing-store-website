@extends('layouts.admin')

@section('title', $pageTitle ?? 'Quản lý tài khoản')

@section('css')
    <style>
        .account-table thead th {
            background: #ffffff;
            color: #111827;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .account-table tbody td {
            color: #374151;
            font-size: 13px;
            height: 42px;
        }

        .account-table tbody tr:nth-child(odd) {
            background: #f3f3f3;
        }

        .page-action-btn {
            min-height: 32px;
            padding: 7px 14px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .page-action-btn.btn-primary {
            background: #174761;
            border-color: #174761;
        }

        .page-action-btn.btn-outline-secondary {
            color: #174761;
            border-color: #9fb3bf;
            background: #ffffff;
        }

        .status-badge {
            border-radius: 2px;
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
            padding: 4px 6px;
        }

        .account-modal .modal-content {
            border: 1px solid #d8dee6;
            border-radius: 4px;
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

        .account-ajax-alert {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 2000;
            min-width: 280px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
        }

        .account-modal-loading {
            padding: 28px 12px;
            color: #6b7280;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    @php
        $showRoleColumn = ($type ?? 'all') !== 'customer';
        $showAddressFields = in_array(($type ?? 'all'), ['staff', 'customer']);
        $emptyColspan = $showRoleColumn ? 7 : 6;
        $nameColumnLabel = ($type ?? 'all') === 'customer' ? 'Tên khách hàng' : 'Họ và tên';
        $breadcrumbLabel = ($type ?? 'all') === 'customer' ? 'Khách hàng' : 'Nhân viên';
        $roleLabels = [
            'admin' => 'Quản trị viên',
            'staff' => 'Nhân viên',
            'customer' => 'Khách hàng',
        ];
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <div>   
                <h1 class="h4 fw-bold mb-0 text-capitalize" style="color:#174761;">{{ $pageTitle ?? 'Quản lý tài khoản' }}
                </h1>
            </div>

            <div class="small text-muted">
                Trang chủ <span class="mx-1">/</span> {{ $breadcrumbLabel }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <a href="{{ route(($routePrefix ?? 'admin.users') . '.create') }}" class="btn btn-primary page-action-btn">
                <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
            </a>
            <a href="{{ route(($routePrefix ?? 'admin.users') . '.trash') }}"
                class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-trash me-1"></i> THÙNG RÁC
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route(($routePrefix ?? 'admin.users') . '.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}"
                                    placeholder="Tìm theo họ tên, email hoặc số điện thoại">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered account-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th>{{ $nameColumnLabel }}</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                @if($showRoleColumn)
                                    <th>Vai trò</th>
                                @endif
                                <th>Trạng thái</th>
                                <th class="text-center pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $user)
                                @php
                                    $roleName = $user->roles->first()?->name;
                                @endphp
                                <tr data-user-row="{{ $user->id }}">
                                    <td class="ps-3">{{ $user->id }}</td>
                                    <td data-cell="username">
                                        <div class="fw-bold text-dark">{{ $user->username }}</div>
                                    </td>
                                    <td data-cell="email">
                                        <span class="fw-semibold">{{ $user->email }}</span>
                                    </td>
                                    <td data-cell="phone_number">
                                        {{ $user->phone_number ?: 'Chưa cập nhật' }}
                                    </td>
                                    @if($showRoleColumn)
                                        <td data-cell="role_name">
                                            {{ $roleName ? ($roleLabels[$roleName] ?? $roleName) : 'Chưa có vai trò' }}
                                        </td>
                                    @endif
                                    <td data-cell="status">
                                        <span
                                            class="badge status-badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $user->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-info js-view-user"
                                                title="Xem chi tiết"
                                                data-show-url="{{ route(($routePrefix ?? 'admin.users') . '.show', $user->id) }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning js-edit-user"
                                                title="Sửa"
                                                data-show-url="{{ route(($routePrefix ?? 'admin.users') . '.show', $user->id) }}"
                                                data-update-url="{{ route(($routePrefix ?? 'admin.users') . '.update', $user->id) }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Xóa"
                                                data-delete-url="{{ route(($routePrefix ?? 'admin.users') . '.destroy', $user->id) }}"
                                                data-delete-name="{{ $user->username }}"
                                                data-delete-type="{{ $itemLabelLower ?? 'tài khoản' }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $emptyColspan }}" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px;"></i>
                                        <div class="fw-semibold text-muted">Chưa có {{ $itemLabelLower ?? 'tài khoản' }} phù hợp
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $data])
            </div>
        </div>

        @include('admin.users.detail')
        @include('admin.users.edit')
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.users.scripts')
@endpush