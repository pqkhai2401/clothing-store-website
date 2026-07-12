@extends('layouts.app')

@section('title', 'Đăng nhập | HK Store')
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

    .auth-links {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 14px 0 18px;
    }

    .auth-link {
        color: #123bdc;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
    }

    .auth-link:hover {
        text-decoration: underline;
    }

    .auth-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 14px;
        color: #555555;
        font-size: 13px;
        font-weight: 700;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #d8d8d8;
    }

    .google-login-btn {
        width: 100%;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #1f1f1f;
        background: #ffffff;
        border: 1px solid #d8d8d8;
        border-radius: 0;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
    }

    .google-icon {
        width: 22px;
        height: 22px;
        flex: 0 0 auto;
    }

    .auth-form-success {
        margin: 0 0 14px;
        padding: 10px 14px;
        color: #155724;
        background: #e6f4ea;
        border: 1px solid #c3e6cb;
        font-size: 13px;
        font-weight: 700;
    }

    .auth-form-warning {
        margin: 0 0 14px;
        padding: 10px 14px;
        color: #7a5b00;
        background: #fff6e0;
        border: 1px solid #ffe08a;
        font-size: 13px;
        font-weight: 700;
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

        .auth-links {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="auth-box">
        <div class="auth-brand">HK STORE</div>
        <h1 class="auth-form-title">Đăng nhập</h1>
        <p class="auth-form-subtitle">Nhập Email/SĐT và mật khẩu để truy cập tài khoản của bạn.</p>

        @if (session('success'))
            <div class="auth-form-success">{{ session('success') }}</div>
        @endif

        @if (session('warning'))
            <div class="auth-form-warning">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('auth.login') }}" method="POST">
            @csrf

            <div class="auth-field">
                <input type="text" name="login" id="login"
                    class="auth-input @error('login') is-invalid @enderror"
                    value="{{ old('login') }}"
                    placeholder=" "
                    autocomplete="username"
                    autofocus>
                <label for="login" class="auth-floating-label">Email/SĐT của bạn</label>
                @error('login')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field password-field">
                <input type="password" name="password" id="password"
                    class="auth-input @error('password') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="current-password">
                <label for="password" class="auth-floating-label">Mật khẩu</label>
                <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Hiện mật khẩu">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            @error('auth')
                <div class="auth-form-error">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn auth-submit">Đăng nhập</button>

            <div class="auth-links">
                <a href="{{ route('auth.registerpage') }}" class="auth-link">Đăng ký tài khoản mới</a>
                <a href="{{ route('auth.password.request') }}" class="auth-link">Quên mật khẩu?</a>
            </div>
        </form>

        <div class="auth-divider">Hoặc</div>

        <a href="{{ route('auth.google.redirect') }}" class="google-login-btn">
            <svg class="google-icon" viewBox="0 0 18 18" aria-hidden="true">
                <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z"/>
                <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.84.86-3.04.86-2.34 0-4.32-1.58-5.03-3.7H.96v2.34A9 9 0 0 0 9 18z"/>
                <path fill="#FBBC05" d="M3.97 10.72A5.41 5.41 0 0 1 3.69 9c0-.6.1-1.18.28-1.72V4.94H.96A9 9 0 0 0 0 9c0 1.45.35 2.82.96 4.06l3.01-2.34z"/>
                <path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C13.46.9 11.43 0 9 0A9 9 0 0 0 .96 4.94l3.01 2.34C4.68 5.16 6.66 3.58 9 3.58z"/>
            </svg>
            <span>Đăng nhập bằng Google</span>
        </a>
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
