@extends('layouts.app')

@section('title', 'Thông tin cá nhân | HK Store')

@section('css')
<style>
    .profile-section {
        max-width: 700px;
        margin: 60px auto;
        padding: 0 15px;
    }

    .profile-section h2 {
        font-family: var(--font-serif);
        font-size: 1.8rem;
        text-align: center;
        margin-bottom: 40px;
        letter-spacing: 1px;
    }

    .profile-form .form-label {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-color);
        margin-bottom: 6px;
    }

    .profile-form .form-control,
    .profile-form .form-select {
        border-radius: 0;
        border: 1px solid var(--border-color);
        padding: 10px 14px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .profile-form .form-control:focus,
    .profile-form .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: none;
    }

    .profile-form .form-control[readonly] {
        background-color: var(--hover-bg);
        cursor: default;
    }

    .btn-update {
        background-color: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 0;
        padding: 12px 40px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        transition: background-color 0.3s ease;
        width: 100%;
    }

    .btn-update:hover {
        background-color: var(--secondary-color);
        color: #fff;
    }

    .btn-change-password {
        background-color: transparent;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        border-radius: 0;
        padding: 8px 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .btn-change-password:hover {
        background-color: var(--primary-color);
        color: #fff;
    }

    .password-section {
        border-top: 1px solid var(--border-color);
        margin-top: 30px;
        padding-top: 30px;
    }

    .password-display {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .password-dots {
        font-size: 14px;
        letter-spacing: 3px;
        color: var(--muted-text);
    }

    .change-password-form {
        display: none;
        margin-top: 20px;
    }

    .change-password-form.show {
        display: block;
    }

    .divider {
        border-top: 1px solid var(--border-color);
        margin: 30px 0;
    }
</style>
@endsection

@section('content')
<div class="profile-section">
    <h2>Thông Tin Cá Nhân</h2>

    {{-- Profile Update Form --}}
    <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="full_name" class="form-label">Họ và tên</label>
            <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name ?? $user->username) }}">
            @error('full_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="phone_number" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">
            @error('phone_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">Giới tính</label>
            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                <option value="" disabled {{ old('gender', $user->gender) ? '' : 'selected' }}>-- Chọn giới tính --</option>
                <option value="nam" {{ old('gender', $user->gender) === 'nam' ? 'selected' : '' }}>Nam</option>
                <option value="nu" {{ old('gender', $user->gender) === 'nu' ? 'selected' : '' }}>Nữ</option>
                <option value="khac" {{ old('gender', $user->gender) === 'khac' ? 'selected' : '' }}>Khác</option>
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password Section --}}
        <div class="password-section">
            <label class="form-label">Mật khẩu</label>
            <div class="password-display">
                <span class="password-dots">••••••••</span>
                <button type="button" class="btn-change-password" id="toggleChangePassword">Đổi mật khẩu</button>
            </div>
        </div>

        <div class="divider"></div>

        <button type="submit" class="btn btn-update">Cập nhật tài khoản</button>
    </form>

    {{-- Change Password Form --}}
    <div class="change-password-form" id="changePasswordForm">
        <form action="{{ route('profile.change-password') }}" method="POST" class="profile-form">
            @csrf
            @method('PUT')

            <h5 style="font-family: var(--font-serif); margin-bottom: 20px;">Đổi Mật Khẩu</h5>

            <div class="mb-3">
                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Mật khẩu mới</label>
                <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password">
                @error('new_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" id="new_password_confirmation" name="new_password_confirmation">
                @error('new_password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-update">Xác nhận đổi mật khẩu</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleChangePassword');
        const form = document.getElementById('changePasswordForm');

        toggleBtn.addEventListener('click', function() {
            form.classList.toggle('show');
            this.textContent = form.classList.contains('show') ? 'Hủy' : 'Đổi mật khẩu';
        });

        @if($errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation'))
            form.classList.add('show');
            toggleBtn.textContent = 'Hủy';
        @endif
    });
</script>
@endpush
