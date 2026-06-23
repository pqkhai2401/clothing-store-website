@extends('layouts.admin')

@section('title', 'Chi tiết '.$itemLabelLower)

@section('css')
    <style>
        .account-detail-card {
            border: 1px solid #d8dee6;
            border-radius: 3px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
        }

        .account-detail-card .card-header {
            min-height: 38px;
            padding: 10px 14px;
            background: #ffffff;
            border-bottom: 1px solid #d8dee6;
        }

        .account-detail-row {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            margin-bottom: 10px;
        }

        .account-detail-label {
            display: flex;
            align-items: center;
            min-height: 32px;
            padding: 7px 10px;
            color: #334155;
            background: #e9ecef;
            border: 1px solid #cfd6df;
            border-right: 0;
            border-radius: 3px 0 0 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .account-detail-value {
            min-height: 32px;
            display: flex;
            align-items: center;
            padding: 7px 12px;
            color: #0f172a;
            background: #ffffff;
            border: 1px solid #cfd6df;
            border-radius: 0 3px 3px 0;
            font-size: 13px;
            font-weight: 600;
            word-break: break-word;
        }

        .account-detail-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 22px 0 6px;
        }

        .account-detail-actions .btn {
            min-height: 34px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .account-detail-row {
                grid-template-columns: 1fr;
            }

            .account-detail-label {
                border-right: 1px solid #cfd6df;
                border-radius: 3px 3px 0 0;
            }

            .account-detail-value {
                border-radius: 0 0 3px 3px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $isStaffContext = ($type ?? 'all') === 'staff';
        $showRoleBlock = in_array(($type ?? 'all'), ['all', 'staff'], true);
        $primaryAddress = $user->addresses->first();
        $roleLabel = match ($user->role?->name) {
            'admin' => 'admin',
            'staff' => 'staff',
            'customer' => 'customer',
            default => 'Chưa có vai trò',
        };
        $statusLabel = $user->is_active ? 'Hoạt động' : 'Đã khóa';
    @endphp

    <main class="app-main container-fluid py-4">
        @if($isStaffContext)
            <h1 class="h4 fw-semibold mb-4">Chi tiết quản trị viên</h1>

            <div class="card account-detail-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-semibold mb-0">Thông tin Quản trị viên</h2>
                    <span class="text-muted">-</span>
                </div>

                <div class="card-body p-3">
                    <div class="account-detail-row">
                        <div class="account-detail-label">Họ Và Tên</div>
                        <div class="account-detail-value">{{ $user->username }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Email</div>
                        <div class="account-detail-value">{{ $user->email }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Mật Khẩu</div>
                        <div class="account-detail-value">********</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Số Điện Thoại</div>
                        <div class="account-detail-value">{{ $user->phone_number ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Vai Trò</div>
                        <div class="account-detail-value">{{ $roleLabel }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Tỉnh, Thành Phố</div>
                        <div class="account-detail-value">{{ $primaryAddress?->city ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Quận, Huyện</div>
                        <div class="account-detail-value">{{ $primaryAddress?->district ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Phường, Xã</div>
                        <div class="account-detail-value">{{ $primaryAddress?->ward ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Số Nhà</div>
                        <div class="account-detail-value">{{ $primaryAddress?->apartment_number ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="account-detail-row">
                        <div class="account-detail-label">Trạng Thái</div>
                        <div class="account-detail-value">{{ $statusLabel }}</div>
                    </div>

                    @unless($user->is_active)
                        <div class="account-detail-row">
                            <div class="account-detail-label">Lý Do Khóa Tài Khoản</div>
                            <div class="account-detail-value">{{ $user->lock_reason ?: 'Chưa nhập lý do' }}</div>
                        </div>
                    @endunless

                    <div class="account-detail-actions">
                        <a href="{{ route(($routePrefix ?? 'admin.staff').'.list') }}" class="btn btn-light border">Quay lại</a>
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
                <div>
                    <h1 class="h3 fw-bold mb-1">Chi tiết {{ $itemLabelLower ?? 'tài khoản' }}</h1>
                    <div class="text-muted">Thông tin tài khoản {{ $user->username }}.</div>
                </div>

                <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

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
        @endif
    </main>
@endsection
