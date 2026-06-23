@extends('layouts.admin')

<<<<<<< ours
@section('title', 'Tạo người dùng')

@section('css')
    <style>
        .user-form-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .user-form-header {
            border-bottom: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .user-form-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #0369a1;
            background: #e0f2fe;
        }

        .btn-toggle-password {
            border-color: #dee2e6;
            border-left: 0;
            color: #64748b;
            background: #f8fafc;
        }

        .btn-toggle-password:hover {
            color: #0369a1;
            background: #eef6ff;
=======
@section('title', $createLabel ?? 'Thêm tài khoản')

@section('css')
    <style>
        .account-create-card {
            border: 1px solid #d8dee6;
            border-radius: 3px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
        }

        .account-create-card .card-header {
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
            gap: 8px;
            padding: 18px 0 4px;
        }

        .account-form-actions .btn {
            min-width: 40px;
            min-height: 30px;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
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
>>>>>>> theirs
        }
    </style>
@endsection

@section('content')
<<<<<<< ours
    <div class="container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Tạo người dùng</h1>
                <div class="text-muted">Thêm tài khoản quản trị, nhân viên hoặc khách hàng vào hệ thống.</div>
            </div>

            <a href="{{ route('admin.users.list') }}" class="btn btn-light border fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="card user-form-card">
            <div class="user-form-header px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="user-form-icon">
                        <i class="fa-solid fa-user-plus"></i>
                    </span>
                    <div>
                        <h2 class="h5 fw-bold mb-1">Thông tin tài khoản</h2>
                        <div class="text-muted small">Các trường có dấu * là bắt buộc.</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off" class="needs-validation" novalidate>
                @csrf

                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-semibold">
                                Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="username" id="username"
                                class="form-control @error('username') is-invalid @enderror"
                                value="{{ old('username') }}" placeholder="Ví dụ: sales_staff" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" placeholder="user@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone_number" class="form-label fw-semibold">Số điện thoại</label>
                            <input type="text" name="phone_number" id="phone_number"
                                class="form-control @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number') }}" placeholder="Ví dụ: 0901234567">
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="role_id" class="form-label fw-semibold">
                                Vai trò <span class="text-danger">*</span>
                            </label>
                            <select name="role_id" id="role_id"
                                class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Chọn vai trò</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                                        {{ ucfirst($role->name) }}
=======
    <main class="app-main container-fluid py-4">
        <x-notification />

        <h1 class="h4 fw-semibold mb-4">{{ $createLabel ?? 'Thêm tài khoản' }} mới</h1>

        <div class="card account-create-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="h6 fw-semibold mb-0">Thông tin {{ $itemLabel ?? 'Tài khoản' }}</h2>
                <span class="text-muted">-</span>
            </div>

            <form method="POST" action="{{ route(($routePrefix ?? 'admin.users').'.store') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="is_active" value="1">

                <div class="card-body p-3">
                    <div class="account-form-row">
                        <label for="display_name" class="account-form-label">Họ Và Tên</label>
                        <div>
                            <input type="text" name="display_name" id="display_name"
                                class="form-control account-form-control @error('display_name') is-invalid @enderror"
                                value="{{ old('display_name') }}" required>
                            @error('display_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="email" class="account-form-label">Email</label>
                        <div>
                            <input type="email" name="email" id="email"
                                class="form-control account-form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="password" class="account-form-label">Mật Khẩu</label>
                        <div>
                            <input type="password" name="password" id="password"
                                class="form-control account-form-control @error('password') is-invalid @enderror"
                                required>
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
                                value="{{ old('phone_number') }}">
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
                                @if(($type ?? 'all') === 'all') required @endif>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) old('role_id', $roles->first()?->id) === (string) $role->id)>
                                        {{ $role->name }}
>>>>>>> theirs
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
<<<<<<< ours
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="is_active" class="form-label fw-semibold">
                                Trạng thái <span class="text-danger">*</span>
                            </label>
                            <select name="is_active" id="is_active"
                                class="form-select @error('is_active') is-invalid @enderror" required>
                                <option value="1" @selected(old('is_active', '1') === '1')>Đang hoạt động</option>
                                <option value="0" @selected(old('is_active') === '0')>Đã khóa</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Tối thiểu 6 ký tự" required>
                                <button type="button" class="btn btn-toggle-password" data-toggle-password="password"
                                    aria-label="Hiển thị mật khẩu">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Nhập lại mật khẩu" required>
                                <button type="button" class="btn btn-toggle-password"
                                    data-toggle-password="password_confirmation" aria-label="Hiển thị mật khẩu xác nhận">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-end gap-2 px-4 py-3">
                    <a href="{{ route('admin.users.list') }}" class="btn btn-light border fw-semibold">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="fas fa-save me-1"></i> Lưu người dùng
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(button.dataset.togglePassword);
                const icon = button.querySelector('i');

                if (!input || !icon) {
                    return;
                }

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);
            });
        });
    </script>
@endpush
=======
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="city" class="account-form-label">Tỉnh, Thành Phố</label>
                        <div>
                            <input type="text" name="city" id="city"
                                class="form-control account-form-control @error('city') is-invalid @enderror"
                                value="{{ old('city') }}">
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
                                value="{{ old('district') }}">
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
                                value="{{ old('ward') }}">
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
                                value="{{ old('apartment_number') }}">
                            @error('apartment_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-actions">
                        <button type="submit" class="btn btn-success">Thêm</button>
                        <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-danger">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
>>>>>>> theirs
