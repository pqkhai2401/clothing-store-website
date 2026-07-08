@extends('layouts.app')

@section('title', 'Thông tin cá nhân | HK Store')

@section('hide_flash_errors', true)

@section('css')
<style>
    .profile-section {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 15px;
    }

    .profile-section h2 {
        font-family: var(--font-serif);
        font-size: 1.5rem;
        text-align: center;
        margin-bottom: 30px;
        letter-spacing: 1px;
    }

    .profile-form .form-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-color);
        margin-bottom: 4px;
    }

    .profile-form .form-control,
    .profile-form .form-select {
        border-radius: 0;
        border: 1px solid var(--border-color);
        padding: 8px 12px;
        font-size: 13px;
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
        padding: 10px 30px;
        font-size: 12px;
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
        padding: 6px 16px;
        font-size: 11px;
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
        margin-top: 20px;
        padding-top: 20px;
    }

    .password-display {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .password-dots {
        font-size: 13px;
        letter-spacing: 3px;
        color: var(--muted-text);
    }

    .change-password-form {
        display: none;
        margin-top: 15px;
    }

    .change-password-form.show {
        display: block;
    }

    .divider {
        border-top: 1px solid var(--border-color);
        margin: 20px 0;
    }

    /* Khu vực ảnh đại diện bên trái */
    .avatar-wrapper {
        text-align: center;
    }

    .avatar-preview {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border-radius: 50%;
    }

    .btn-change-avatar {
        display: inline-block;
        margin-top: 14px;
        background-color: transparent;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        border-radius: 0;
        padding: 6px 16px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-change-avatar:hover {
        background-color: var(--primary-color);
        color: #fff;
    }

    @media (max-width: 576px) {
        .profile-section {
            max-width: 100%;
            margin: 20px auto;
            padding: 0 12px;
        }

        .profile-section h2 {
            font-size: 1.25rem;
            margin-bottom: 20px;
        }

        .profile-form .form-control,
        .profile-form .form-select {
            padding: 8px 10px;
            font-size: 13px;
        }

        .profile-form .mb-3 {
            margin-bottom: 12px !important;
        }

        .password-display {
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn-update {
            padding: 10px 20px;
        }

        .password-section {
            margin-top: 15px;
            padding-top: 15px;
        }

        .divider {
            margin: 15px 0;
        }
    }
</style>
@endsection

@section('content')
<div class="profile-section">
    <h2>Thông Tin Cá Nhân</h2>

    {{-- Profile Update Form --}}
    {{-- enctype="multipart/form-data" là bắt buộc để form có thể gửi được file ảnh lên server --}}
    <form action="{{ route('profile.update') }}" method="POST" class="profile-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Cột trái: Ảnh đại diện --}}
            <div class="col-md-5 col-lg-4 mb-4 mb-md-0">
                <div class="avatar-wrapper">
                    <img
                        id="avatar-preview"
                        src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=random' }}"
                        alt="Ảnh đại diện"
                        class="avatar-preview rounded-circle img-thumbnail"
                    >

                    {{-- Input file bị ẩn, được kích hoạt gián tiếp qua nút bấm --}}
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" class="d-none">

                    <div>
                        <label for="avatar-input" class="btn-change-avatar">Thay đổi ảnh</label>
                    </div>

                    @error('avatar')
                        <div class="text-danger mt-2" style="font-size: 12px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Cột phải: Thông tin cá nhân --}}
            <div class="col-md-7 col-lg-8">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}">
                    @error('username')
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
                    <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                </div>

                @if(!$user->google_id)
                {{-- Password Section --}}
                <div class="password-section">
                    <label class="form-label">Mật khẩu</label>
                    <div class="password-display">
                        <span class="password-dots">********</span>
                        <button type="button" class="btn-change-password" id="toggleChangePassword">Đổi mật khẩu</button>
                    </div>
                </div>
                @endif

                <div class="divider"></div>

                <button type="submit" class="btn btn-update">Cập nhật tài khoản</button>
            </div>
        </div>
    </form>

    @if(!$user->google_id)
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
    @endif
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

        // Xem trước ảnh đại diện ngay khi người dùng chọn file (Real-time Preview)
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');

        avatarInput.addEventListener('change', function(event) {
            const selectedFile = event.target.files[0];

            // Nếu người dùng không chọn file nào thì không xử lý gì thêm
            if (!selectedFile) {
                return;
            }

            // Dùng URL.createObjectURL để tạo một đường dẫn tạm trỏ tới file trên máy người dùng
            // và gán trực tiếp vào thuộc tính src của thẻ <img> để xem trước
            const objectUrl = URL.createObjectURL(selectedFile);
            avatarPreview.src = objectUrl;
        });
    });
</script>
@endpush
