@extends('layouts.app')

@section('title', 'Quên mật khẩu | HK Store')

@section('css')
<style>
    .forgot-section {
        min-height: 62vh;
        display: flex;
        align-items: center;
        padding: 72px 16px;
        background: #f7f7f5;
    }

    .forgot-card {
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
        padding: 36px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<section class="forgot-section">
    <div class="forgot-card">
        <h1 class="h3 fw-bold mb-2">Quên mật khẩu</h1>
        <p class="text-muted mb-4">
            Chức năng đặt lại mật khẩu qua email chưa được cấu hình SMTP. Bạn có thể đăng nhập bằng Google nếu email đã liên kết với tài khoản Google.
        </p>

        <a href="{{ route('auth.loginpage') }}" class="btn btn-dark w-100">Quay lại đăng nhập</a>
    </div>
</section>
@endsection
