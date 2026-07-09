@extends('layouts.app')

@section('title', 'Quên mật khẩu | HK Store')
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
        justify-content: center;
        gap: 16px;
        margin: 14px 0 0;
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

    .auth-form-success {
        margin: 0 0 14px;
        padding: 10px 14px;
        color: #155724;
        background: #e6f4ea;
        border: 1px solid #c3e6cb;
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
    }
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="auth-box">
        <div class="auth-brand">HK STORE</div>
        <h1 class="auth-form-title">Quên mật khẩu</h1>
        <p class="auth-form-subtitle">Nhập email đã đăng ký để nhận mã xác thực (OTP) đặt lại mật khẩu.</p>

        @if (session('success'))
            <div class="auth-form-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('auth.password.send') }}" method="POST">
            @csrf

            <div class="auth-field">
                <input type="email" name="email" id="email"
                    class="auth-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder=" "
                    autocomplete="email"
                    autofocus>
                <label for="email" class="auth-floating-label">Email của bạn</label>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn auth-submit">Gửi mã xác thực</button>

            <div class="auth-links">
                <a href="{{ route('auth.loginpage') }}" class="auth-link">Quay lại đăng nhập</a>
            </div>
        </form>
    </div>
</section>
@endsection
