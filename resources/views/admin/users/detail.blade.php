@extends('layouts.admin')

@section('title', 'Chi tiết người dùng')

@section('content')
    <main class="app-main container">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row my-3">
                    <div class="col-sm-12 px-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="mb-0 fw-bold text-uppercase">Chi tiết người dùng</h3>

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.users.list') }}" class="btn btn-light border fw-semibold">
                                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning fw-semibold">
                                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="card shadow-sm border">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0 text-uppercase">{{ $user->name }}</h6>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">ID</div>
                            <div class="fw-semibold">{{ $user->id }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Họ tên</div>
                            <div class="fw-semibold">{{ $user->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Email</div>
                            <div class="fw-semibold">{{ $user->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Số điện thoại</div>
                            <div class="fw-semibold">{{ $user->phone_number ?? 'Chưa cập nhật' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Vai trò</div>
                            <div class="fw-semibold">{{ $user->role?->name ?? 'Chưa có vai trò' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Trạng thái</div>
                            <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Ngày tạo</div>
                            <div class="fw-semibold">{{ optional($user->created_at)->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Cập nhật lần cuối</div>
                            <div class="fw-semibold">{{ optional($user->updated_at)->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
