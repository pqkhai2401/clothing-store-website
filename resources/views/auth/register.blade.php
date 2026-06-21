@extends('layouts.app')

@section('title', 'Đăng ký | HK Store')

@section('css')
<style>
    .auth-section {
        min-height: 78vh;
        display: flex;
        justify-content: center;
        padding: 28px 16px 72px;
        background: #ffffff;
    }

    .auth-panel {
        width: 100%;
        max-width: 580px;
        margin: 0 auto;
    }

    .auth-logo-slot {
        width: 172px;
        height: 38px;
        margin-bottom: 14px;
    }

    .auth-title {
        margin: 0 0 20px;
        color: #000000;
        font-size: 36px;
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: 0;
    }

    .auth-benefit-label {
        margin: 0 0 8px;
        color: #000000;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.4;
    }

    .auth-benefits {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 28px;
    }

    .auth-benefit-card {
        min-height: 72px;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        color: #000000;
        background: #ffffff;
        border: 1px solid #dedede;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
    }

    .auth-benefit-card i {
        width: 30px;
        color: #111111;
        font-size: 26px;
        text-align: center;
    }

    .auth-benefit-card span {
        display: block;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.2;
    }

    /* ── Section label ── */
    .auth-section-label {
        display: flex;
        align-items: left;
        margin: 0 0 20px;
    }

    .auth-section-label::before,
    .auth-section-label::after {
        content: "";
        flex: 1;
        height: 1px;

    }

    .auth-section-label span {
        font-size: 15px;
        font-weight: 700;
        color: #2554d9;
        white-space: nowrap;
    }

    /* ── Fields ── */
    .auth-field {
        position: relative;
        margin-bottom: 14px;
    }

    .auth-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }

    .auth-row .auth-field { margin-bottom: 0; }

    .auth-input {
        width: 100%;
        height: 52px;
        padding: 0 22px;
        color: #111111;
        background: #f0f0f0;
        border: 1.5px solid transparent;
        border-radius: 999px;
        font-size: 16px;
        font-weight: 600;
        outline: none;
        box-shadow: none;
        transition: border-color .15s, background .15s;
    }

    .auth-input::placeholder {
        color: #aaaaaa;
        font-weight: 600;
    }

    .auth-input:focus {
        background: #ffffff;
        border-color: #111111;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, .07);
    }

    .auth-input.is-invalid {
        border-color: #dc3545;
        background: #fff5f5;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 16px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111111;
        background: transparent;
        border: 0;
        transform: translateY(-50%);
        cursor: pointer;
    }

    .password-toggle:hover { color: #2554d9; }
    .password-field .auth-input { padding-right: 54px; }

    /* ── Submit ── */
    .auth-submit {
        width: 100%;
        min-height: 50px;
        margin-top: 4px;
        color: #ffffff;
        background: #000000;
        border: 1px solid #000000;
        border-radius: 999px;
        font-size: 16px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        cursor: pointer;
        transition: background .15s;
    }

    .auth-submit:hover,
    .auth-submit:focus { background: #222222; border-color: #222222; color: #fff; }

    /* ── Links ── */
    .auth-links {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 20px 0 28px;
    }

    .auth-link {
        color: #123bdc;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
    }

    .auth-link:hover { color: #000000; text-decoration: underline; }

    /* ── Bottom Google ── */
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 20px;
        color: #111111;
        font-size: 14px;
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
        min-height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #1f1f1f;
        background: #ffffff;
        border: 1px solid #d8d8d8;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        transition: background .15s, border-color .15s;
    }

    .google-login-btn:hover { color: #000; background: #f8f8f8; border-color: #bfbfbf; }
    .google-icon { width: 22px; height: 22px; flex: 0 0 auto; }

    .invalid-feedback {
        margin: 5px 0 0 18px;
        font-size: 13px;
        font-weight: 600;
        color: #dc3545;
        display: block;
    }

    @media (max-width: 575.98px) {
        .auth-section { min-height: auto; padding: 22px 16px 48px; }
        .auth-title { font-size: 28px; }
        .auth-benefits { grid-template-columns: 1fr; }
        .auth-row { grid-template-columns: 1fr; }
        .auth-benefit-card span { font-size: 16px; }
        .auth-links { flex-direction: column; gap: 10px; }
    }
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="auth-panel">
        <div class="auth-logo-slot" aria-label="Logo"></div>

        {{-- FIXED TOP --}}
        <h1 class="auth-title">Rất nhiều đặc quyền và quyền lợi mua sắm đang chờ bạn</h1>

        <p class="auth-benefit-label">Quyền lợi dành riêng cho bạn khi tham gia HK Store</p>
        <div class="auth-benefits">
            <div class="auth-benefit-card">
                <i class="bi bi-percent" aria-hidden="true"></i>
                <span>Voucher<br>ưu đãi</span>
            </div>
            <div class="auth-benefit-card">
                <i class="bi bi-gift" aria-hidden="true"></i>
                <span>Quà tặng<br>độc quyền</span>
            </div>
        </div>

     
        {{-- FORM ĐĂNG KÝ --}}
        @if (session('error'))
            <div class="alert alert-danger mb-3" style="border-radius: 12px; font-size: 14px;">{{ session('error') }}</div>
        @endif

        <form action="{{ route('auth.register') }}" method="POST">
            @csrf

            <div class="auth-row">
                <div class="auth-field">
                    <label for="display_name" class="visually-hidden">Họ và tên</label>
                    <input type="text" name="display_name" id="display_name"
                        class="auth-input @error('display_name') is-invalid @enderror"
                        value="{{ old('display_name') }}"
                        placeholder="Họ và tên"
                        autocomplete="name" autofocus>
                    @error('display_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="auth-field">
                    <label for="phone_number" class="visually-hidden">Số điện thoại</label>
                    <input type="tel" name="phone_number" id="phone_number"
                        class="auth-input @error('phone_number') is-invalid @enderror"
                        value="{{ old('phone_number') }}"
                        placeholder="Số điện thoại"
                        autocomplete="tel">
                    @error('phone_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="auth-field">
                <label for="reg_email" class="visually-hidden">Email</label>
                <input type="email" name="email" id="reg_email"
                    class="auth-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="Email"
                    autocomplete="email">
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field password-field">
                <label for="reg_password" class="visually-hidden">Mật khẩu</label>
                <input type="password" name="password" id="reg_password"
                    class="auth-input @error('password') is-invalid @enderror"
                    placeholder="Mật khẩu"
                    autocomplete="new-password">
                <button type="button" class="password-toggle" data-toggle-password="reg_password" aria-label="Hiện mật khẩu">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn auth-submit">Đăng ký tài khoản</button>

            <div class="auth-links">
                <a href="{{ route('auth.loginpage') }}" class="auth-link">Đăng nhập</a>
                <a href="{{ route('auth.password.request') }}" class="auth-link">Quên mật khẩu?</a>
            </div>
        </form>

        {{-- FIXED BOTTOM --}}
        <div class="auth-divider">Hoặc</div>
        
        {{-- SECTION LABEL --}}
        <div class="auth-section-label">
            <span>Đăng nhập hoặc đăng ký (miễn phí)</span>
        </div>

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
