@extends('layouts.admin')

@section('title', 'Chi tiết '.$itemLabelLower)

@section('content')
    @php
        $showRoleBlock = in_array(($type ?? 'all'), ['all', 'staff'], true);
    @endphp

    <main class="app-main container-fluid py-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1">Chi tiết {{ $itemLabelLower ?? 'tài khoản' }}</h1>
                <div class="text-muted">Thông tin tài khoản {{ $user->username }}.</div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
                <a href="{{ route(($routePrefix ?? 'admin.users').'.edit', $user->id) }}" class="btn btn-warning fw-semibold">
                    <i class="fa-solid fa-pen me-1"></i> Sửa
                </a>
            </div>
        </div>

        @if($showRoleBlock)
            @php
                $roleLabel = match ($user->role?->name) {
                    'admin' => 'Quản trị viên',
                    'staff' => 'Nhân viên',
                    'customer' => 'Khách hàng',
                    default => 'Chưa có vai trò',
                };
            @endphp
        @endif

        <div class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h5 fw-bold mb-0">{{ $user->username }}</h2>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">ID</div>
                        <div class="fw-semibold">{{ $user->id }}</div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Họ và tên</div>
                        <div class="fw-semibold">{{ $user->username }}</div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Email</div>
                        <div class="fw-semibold">{{ $user->email }}</div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Số điện thoại</div>
                        <div class="fw-semibold">{{ $user->phone_number ?: 'Chưa cập nhật' }}</div>
                    </div>
                    @if($showRoleBlock)
                        <div class="col-md-6 col-xl-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Vai trò</div>
                            <span class="badge text-bg-light border">{{ $roleLabel }}</span>
                        </div>
                    @endif
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Trạng thái</div>
                        <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                        </span>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Ngày tạo</div>
                        <div class="fw-semibold">{{ optional($user->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Cập nhật lần cuối</div>
                        <div class="fw-semibold">{{ optional($user->updated_at)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
