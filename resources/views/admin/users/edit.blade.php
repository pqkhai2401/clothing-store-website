@extends('layouts.admin')

@section('title', 'Cập nhật '.$itemLabelLower)

@section('css')
    <style>
        .account-edit-card {
            border: 1px solid #d8dee6;
            border-radius: 3px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
        }

        .account-edit-card .card-header {
            min-height: 38px;
            padding: 10px 14px;
            background: #ffffff;
            border-bottom: 1px solid #d8dee6;
        }

        .account-form-row {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            margin-bottom: 10px;
        }

        .account-form-label {
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

        .account-form-control {
            min-height: 32px;
            border-radius: 0 3px 3px 0;
            font-size: 13px;
        }

        .account-form-control:focus {
            background: #eaf3ff;
        }

        .account-form-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 18px 14px;
            background: #ffffff;
            border-top: 1px solid #d8dee6;
        }

        .account-form-actions .btn {
            min-height: 34px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .account-form-row {
                grid-template-columns: 1fr;
            }

            .account-form-label {
                border-right: 1px solid #cfd6df;
                border-radius: 3px 3px 0 0;
            }

            .account-form-control {
                border-radius: 0 0 3px 3px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $isStaffContext = ($type ?? 'all') === 'staff';
        $primaryAddress = $user->addresses->first();
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        @if($isStaffContext)
            <h1 class="h4 fw-semibold mb-4">CẬP NHẬT QUẢN TRỊ VIÊN</h1>

            <form method="POST" action="{{ route(($routePrefix ?? 'admin.staff').'.update', $user->id) }}" autocomplete="off">
                @csrf
                @method('PUT')

                <div class="card account-edit-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h2 class="h6 fw-semibold mb-0">Thông tin Quản trị viên</h2>
                        <span class="text-muted">-</span>
                    </div>

                    <div class="card-body p-3">
                        <div class="account-form-row">
                            <label for="username" class="account-form-label">Họ Và Tên</label>
                            <div>
                                <input type="text" name="username" id="username"
                                    class="form-control account-form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="email" class="account-form-label">Email</label>
                            <div>
                                <input type="email" name="email" id="email"
                                    class="form-control account-form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="password" class="account-form-label">Mật Khẩu</label>
                            <div>
                                <input type="password" name="password" id="password"
                                    class="form-control account-form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="phone_number" class="account-form-label">Số Điện Thoại</label>
                            <div>
                                <input type="text" name="phone_number" id="phone_number"
                                    class="form-control account-form-control @error('phone_number') is-invalid @enderror"
                                    value="{{ old('phone_number', $user->phone_number) }}">
                                @error('phone_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="role_id" class="account-form-label">Vai Trò</label>
                            <div>
                                <select name="role_id" id="role_id"
                                    class="form-select account-form-control @error('role_id') is-invalid @enderror"
                                    required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) old('role_id', $user->role_id) === (string) $role->id)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="city" class="account-form-label">Tỉnh, Thành Phố</label>
                            <div>
                                <input type="text" name="city" id="city"
                                    class="form-control account-form-control @error('city') is-invalid @enderror"
                                    value="{{ old('city', $primaryAddress?->city) }}">
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="district" class="account-form-label">Quận, Huyện</label>
                            <div>
                                <input type="text" name="district" id="district"
                                    class="form-control account-form-control @error('district') is-invalid @enderror"
                                    value="{{ old('district', $primaryAddress?->district) }}">
                                @error('district')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="ward" class="account-form-label">Phường, Xã</label>
                            <div>
                                <input type="text" name="ward" id="ward"
                                    class="form-control account-form-control @error('ward') is-invalid @enderror"
                                    value="{{ old('ward', $primaryAddress?->ward) }}">
                                @error('ward')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="apartment_number" class="account-form-label">Số Nhà</label>
                            <div>
                                <input type="text" name="apartment_number" id="apartment_number"
                                    class="form-control account-form-control @error('apartment_number') is-invalid @enderror"
                                    value="{{ old('apartment_number', $primaryAddress?->apartment_number) }}">
                                @error('apartment_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row">
                            <label for="is_active" class="account-form-label">Trạng Thái</label>
                            <div>
                                <select name="is_active" id="is_active"
                                    class="form-select account-form-control @error('is_active') is-invalid @enderror"
                                    required>
                                    <option value="1" @selected((string) old('is_active', $user->is_active ? '1' : '0') === '1')>Hoạt động</option>
                                    <option value="0" @selected((string) old('is_active', $user->is_active ? '1' : '0') === '0')>Đã khóa</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="account-form-row mb-0" data-lock-reason-row>
                            <label for="lock_reason" class="account-form-label">Lý Do Khóa Tài Khoản</label>
                            <div>
                                <input type="text" name="lock_reason" id="lock_reason"
                                    class="form-control account-form-control @error('lock_reason') is-invalid @enderror"
                                    value="{{ old('lock_reason', $user->lock_reason) }}">
                                @error('lock_reason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="account-form-actions">
                        <a href="{{ route(($routePrefix ?? 'admin.staff').'.list') }}" class="btn btn-light border">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
                <div>
                    <h1 class="h3 fw-bold mb-1">Cập nhật {{ $itemLabelLower ?? 'tài khoản' }}</h1>
                    <div class="text-muted">Chỉnh sửa thông tin tài khoản #{{ $user->id }}.</div>
                </div>

                <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            <div class="card border shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-warning-subtle text-warning" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-user-pen"></i>
                        </span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Thông tin {{ $itemLabelLower ?? 'tài khoản' }}</h2>
                            <div class="text-muted small">Để trống mật khẩu nếu không muốn thay đổi.</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route(($routePrefix ?? 'admin.users').'.update', $user->id) }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="username" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone_number" class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone_number" id="phone_number"
                                    class="form-control @error('phone_number') is-invalid @enderror"
                                    value="{{ old('phone_number', $user->phone_number) }}" placeholder="Ví dụ: 0901234567">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(($type ?? 'all') === 'all')
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label fw-semibold">Vai trò <span class="text-danger">*</span></label>
                                    <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                        <option value="">Chọn vai trò</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @selected((string) old('role_id', $user->role_id) === (string) $role->id)>
                                                {{ match ($role->name) {
                                                    'admin' => 'Quản trị viên',
                                                    'staff' => 'Nhân viên',
                                                    'customer' => 'Khách hàng',
                                                    default => ucfirst($role->name),
                                                } }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label for="is_active" class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                                <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                                    <option value="1" @selected((string) old('is_active', $user->is_active ? '1' : '0') === '1')>Đang hoạt động</option>
                                    <option value="0" @selected((string) old('is_active', $user->is_active ? '1' : '0') === '0')>Đã khóa</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Mật khẩu mới</label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Để trống nếu không đổi">
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Nhập lại mật khẩu mới">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-end gap-2 px-4 py-3">
                        <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border fw-semibold">Hủy</a>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fa-solid fa-save me-1"></i> Lưu
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            const statusSelect = document.getElementById('is_active');
            const reasonRow = document.querySelector('[data-lock-reason-row]');
            const reasonInput = document.getElementById('lock_reason');

            if (!statusSelect || !reasonRow || !reasonInput) {
                return;
            }

            function syncLockReason() {
                const locked = statusSelect.value === '0';
                reasonRow.classList.toggle('d-none', !locked);
                reasonInput.disabled = !locked;

                if (!locked) {
                    reasonInput.value = '';
                }
            }

            statusSelect.addEventListener('change', syncLockReason);
            syncLockReason();
        })();
    </script>
@endpush
