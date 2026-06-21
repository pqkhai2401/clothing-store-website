@extends('layouts.admin')

@section('title', 'Cập nhật người dùng')

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
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Cập nhật người dùng</h1>
                <div class="text-muted">Chỉnh sửa thông tin tài khoản #{{ $user->id }}.</div>
            </div>

            <a href="{{ route('admin.users.list') }}" class="btn btn-light border fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="card user-form-card">
            <div class="user-form-header px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="user-form-icon">
                        <i class="fa-solid fa-user-pen"></i>
                    </span>
                    <div>
                        <h2 class="h5 fw-bold mb-1">Thông tin tài khoản</h2>
                        <div class="text-muted small">Để trống mật khẩu nếu không muốn thay đổi.</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" autocomplete="off"
                class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" placeholder="Ví dụ: Nguyễn Văn A" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" placeholder="user@example.com" required>
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

                        <div class="col-md-6">
                            <label for="role_id" class="form-label fw-semibold">
                                Vai trò <span class="text-danger">*</span>
                            </label>
                            <select name="role_id" id="role_id"
                                class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Chọn vai trò</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        @selected((string) old('role_id', $user->role_id) === (string) $role->id)>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="is_active" class="form-label fw-semibold">
                                Trạng thái <span class="text-danger">*</span>
                            </label>
                            <select name="is_active" id="is_active"
                                class="form-select @error('is_active') is-invalid @enderror" required>
                                <option value="1"
                                    @selected((string) old('is_active', $user->is_active ? '1' : '0') === '1')>
                                    Đang hoạt động
                                </option>
                                <option value="0"
                                    @selected((string) old('is_active', $user->is_active ? '1' : '0') === '0')>
                                    Đã khóa
                                </option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Để trống nếu không đổi">
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
                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Nhập lại mật khẩu mới">
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
                        <i class="fas fa-save me-1"></i> Cập nhật
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
