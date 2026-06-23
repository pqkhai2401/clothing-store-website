@extends('layouts.admin')

@section('title', 'Cập nhật '.$itemLabelLower)

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

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
                            <label for="display_name" class="form-label fw-semibold">Tên hiển thị</label>
                            <input type="text" name="display_name" id="display_name"
                                class="form-control @error('display_name') is-invalid @enderror"
                                value="{{ old('display_name', $user->display_name) }}"
                                placeholder="Ví dụ: Nguyễn Văn A">
                            @error('display_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
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
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Để trống nếu không đổi">
                                <button type="button" class="btn btn-light border" data-toggle-password="password" aria-label="Hiện mật khẩu">
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
                                <button type="button" class="btn btn-light border" data-toggle-password="password_confirmation" aria-label="Hiện mật khẩu xác nhận">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
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
    </main>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(button.dataset.togglePassword);
                const icon = button.querySelector('i');

                if (!input || !icon) return;

                const hidden = input.type === 'password';
                input.type = hidden ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !hidden);
                icon.classList.toggle('fa-eye-slash', hidden);
            });
        });
    </script>
@endpush
