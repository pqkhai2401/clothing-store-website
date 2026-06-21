@extends('layouts.app')

@section('title', 'Sign In | HK Store')

@section('css')
<style>
    .auth-section {
        min-height: 68vh;
        display: flex;
        align-items: center;
        padding: 72px 0;
        background: #f7f7f5;
    }

    .auth-panel {
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
        padding: 40px;
        background: #ffffff;
        border: 1px solid var(--border-color);
    }

    .auth-title {
        margin-bottom: 8px;
        font-family: var(--font-serif);
        font-size: 34px;
        font-weight: 400;
        text-transform: uppercase;
    }

    .auth-subtitle {
        margin-bottom: 32px;
        color: var(--muted-text);
        font-size: 13px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .auth-panel .form-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .auth-panel .form-control {
        min-height: 48px;
        border-radius: 0;
    }

    .auth-panel .form-check-label {
        color: var(--muted-text);
        font-size: 13px;
    }

    @media (max-width: 575.98px) {
        .auth-section {
            padding: 48px 16px;
        }

        .auth-panel {
            padding: 28px 22px;
        }
    }
</style>
@endsection

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-panel">
            <h1 class="auth-title">Đăng nhập</h1>
            <div class="auth-subtitle">Đăng nhập bằng tài khoản HK store của bạn</div>

            <form action="{{ route('auth.login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        autocomplete="current-password"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Quên mật khẩu</label>
                </div>

                <button type="submit" class="btn btn-black w-100">Đăng nhập</button>
            </form>
        </div>
    </div>
</section>
@endsection
