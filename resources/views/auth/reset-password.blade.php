@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu | HK Store')
@section('auth_standalone', true)
@section('body_class', 'auth-standalone-body')

@section('css')
<style>
    .auth-standalone-body {
        min-height: 100vh;
        background: #ffffff;
    }

    .auth-section {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 16px;
        background: #ffffff;
    }

    .auth-box {
        width: 100%;
        max-width: 480px;
        padding: 30px 32px;
        background: #ffffff;
        border: 1px solid #d8d8d8;
        border-radius: 0;
    }

    .auth-brand {
        margin: 0 0 20px;
        color: #000000;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: .08em;
    }

    .auth-form-title {
        margin: 0 0 8px;
        color: #111111;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
    }

    .auth-form-subtitle {
        margin: 0 0 20px;
        color: #666666;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
    }

    .auth-field {
        position: relative;
        margin-bottom: 10px;
    }

    .auth-input {
        width: 100%;
        height: 48px;
        padding: 0 22px;
        color: #111111;
        background: #ffffff;
        border: 1px solid #d8d8d8;
        border-radius: 0;
        font-size: 16px;
        font-weight: 600;
        outline: none;
        box-shadow: none;
    }

    .auth-input::placeholder {
        color: transparent;
    }

    .auth-input:hover {
        border-color: #111111;
    }

    .auth-input:focus {
        border-color: #111111;
    }

    .auth-input.is-invalid {
        border-color: #e60012;
    }

    .auth-floating-label {
        position: absolute;
        top: 24px;
        left: 16px;
        z-index: 2;
        max-width: calc(100% - 72px);
        padding: 0 6px;
        color: #777777;
        background: #ffffff;
        font-size: 16px;
        font-weight: 600;
        line-height: 1;
        pointer-events: none;
        transform: translateY(-50%);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .auth-input:focus + .auth-floating-label,
    .auth-input:not(:placeholder-shown) + .auth-floating-label {
        top: 0;
        color: #555555;
        font-size: 13px;
    }

    .auth-input.is-invalid + .auth-floating-label {
        color: #e60012;
    }

    .password-toggle {
        position: absolute;
        top: 24px;
        right: 12px;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111111;
        background: transparent;
        border: 0;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 3;
    }

    .password-field .auth-input {
        padding-right: 52px;
    }

    .auth-submit {
        width: 100%;
        min-height: 44px;
        margin-top: 6px;
        color: #ffffff;
        background: #000000;
        border: 1px solid #000000;
        border-radius: 0;
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        transition: background .18s, color .18s;
    }

    .auth-submit:hover {
        outline: 2px solid #000000;
        outline-offset: 2px;
    }

    .invalid-feedback,
    .auth-form-error {
        margin: 6px 0 0 0;
        color: #e60012;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
    }

    .auth-form-error {
        margin-bottom: 14px;
    }

    .invalid-feedback::before,
    .auth-form-error::before {
        content: "!";
        width: 16px;
        height: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 16px;
        color: #ffffff;
        background: #e60012;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 800;
    }

    @media (max-width: 575.98px) {
        .auth-section {
            padding: 16px;
        }

        .auth-box {
            padding: 24px 18px;
        }
    }
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="auth-box">
        <div class="auth-brand">HK STORE</div>
        <h1 class="auth-form-title">Đặt lại mật khẩu</h1>
        <p class="auth-form-subtitle">Tạo mật khẩu mới cho tài khoản của bạn (tối thiểu 8 ký tự).</p>

        <form action="{{ route('auth.password.update') }}" method="POST">
            @csrf

            <div class="auth-field password-field">
                <input type="password" name="password" id="password"
                    class="auth-input @error('password') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="new-password"
                    autofocus>
                <label for="password" class="auth-floating-label">Mật khẩu mới</label>
                <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Hiện mật khẩu">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field password-field">
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="auth-input"
                    placeholder=" "
                    autocomplete="new-password">
                <label for="password_confirmation" class="auth-floating-label">Xác nhận mật khẩu</label>
                <button type="button" class="password-toggle" data-toggle-password="password_confirmation" aria-label="Hiện mật khẩu">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn auth-submit">Cập nhật mật khẩu</button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.togglePassword);
            var icon  = btn.querySelector('i');
            if (!input || !icon) return;
            var hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !hidden);
            icon.classList.toggle('bi-eye-slash', hidden);
            btn.setAttribute('aria-label', hidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });
</script>
@endpush
