@extends('layouts.admin')

@section('title', ($type ?? 'all') === 'staff' ? 'Thêm quản trị viên mới' : ($createLabel ?? 'Thêm tài khoản'))

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

        /* Input-group chứa ô password + nút mắt */
        .input-group .account-form-control {
            border-radius: 0;
        }

        .pw-toggle {
            min-height: 32px;
            padding: 0 10px;
            border-radius: 0 3px 3px 0 !important;
            border-left: 0;
            font-size: 13px;
            color: #64748b;
            background: var(--hk-bg-input, #fff);
            border-color: var(--hk-border, #ced4da);
        }

        .pw-toggle:hover {
            color: var(--hk-accent, #174761);
            background: var(--hk-bg-input, #f8f9fa);
        }

        .account-form-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 22px 0 6px;
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
        $pageHeading = $isStaffContext ? 'Thêm quản trị viên mới' : (($createLabel ?? 'Thêm tài khoản').' mới');
        $cardHeading = $isStaffContext ? 'Thông tin Quản trị viên' : 'Thông tin '.($itemLabel ?? 'Tài khoản');
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        <h1 class="h4 fw-semibold mb-4">{{ $pageHeading }}</h1>

        <div class="card account-create-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="h6 fw-semibold mb-0">{{ $cardHeading }}</h2>
                <span class="text-muted">-</span>
            </div>

            <form method="POST" action="{{ route(($routePrefix ?? 'admin.users').'.store') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="is_active" value="1">

                <div class="card-body p-3">
                    <div class="account-form-row">
                        <label for="username" class="account-form-label">Họ Và Tên</label>
                        <div>
                            <input type="text" name="username" id="username"
                                class="form-control account-form-control @error('username') is-invalid @enderror"
                                value="{{ old('username') }}" required>
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
                                value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="password" class="account-form-label">Mật Khẩu</label>
                        <div>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control account-form-control @error('password') is-invalid @enderror"
                                    required>
                                <button type="button" class="btn btn-outline-secondary pw-toggle"
                                    data-target="password" tabindex="-1"
                                    title="Hiện / Ẩn mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
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
                                required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) old('role_id', $roles->first()?->id) === (string) $role->id)>
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
                                value="{{ old('city') }}">
                            @error('city')
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
                        <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Thêm mới người dùng
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var icon  = btn.querySelector('i');
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    });
});
</script>
@endpush
