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
    </style>
@endsection

@section('content')
    @php
        $showRoleColumn = ($type ?? 'all') !== 'customer';
        $emptyColspan = $showRoleColumn ? 7 : 6;
        $nameColumnLabel = ($type ?? 'all') === 'customer' ? 'Tên khách hàng' : 'Họ và tên';
        $breadcrumbLabel = ($type ?? 'all') === 'customer' ? 'Khách hàng' : 'Nhân sự';
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <h1 class="h4 fw-bold mb-0 text-capitalize" style="color:#174761;">{{ $pageTitle ?? 'Quản lý tài khoản' }}</h1>
            </div>

            <div class="small text-muted">
                Trang chủ <span class="mx-1">/</span> {{ $breadcrumbLabel }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <a href="{{ route(($routePrefix ?? 'admin.users').'.create') }}" class="btn btn-primary page-action-btn">
                <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
            </a>
            <a href="{{ route(($routePrefix ?? 'admin.users').'.trash') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-trash me-1"></i> THÙNG RÁC
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route(($routePrefix ?? 'admin.users').'.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control"
                                    value="{{ request('keyword') }}"
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
                                    $roleName = $user->role?->name;
                                @endphp
                                <tr>
                                    <td class="ps-3">{{ $user->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $user->username }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $user->email }}</span>
                                    </td>
                                    <td>
                                        {{ $user->phone_number ?: 'Chưa cập nhật' }}
                                    </td>
                                    @if($showRoleColumn)
                                        <td>
                                            {{ $roleName ?: 'Chưa có vai trò' }}
                                        </td>
                                    @endif
                                    <td>
                                        <span class="badge status-badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="{{ route(($routePrefix ?? 'admin.users').'.show', $user->id) }}"
                                                class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route(($routePrefix ?? 'admin.users').'.edit', $user->id) }}"
                                                class="btn btn-sm btn-outline-warning" title="Sửa">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                title="Xóa"
                                                data-delete-url="{{ route(($routePrefix ?? 'admin.users').'.destroy', $user->id) }}"
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
                                        <div class="fw-semibold text-muted">Chưa có {{ $itemLabelLower ?? 'tài khoản' }} phù hợp</div>
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
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
