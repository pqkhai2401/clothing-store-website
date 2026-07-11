@extends('layouts.admin')

@section('title', 'Cài đặt website')

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="stg-section">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
             <div>
                <h1 class="product-header-title mb-2">Quản lý cài đặt hệ thống</h1>
                <p class="product-header-desc mb-0">
                    Quản lý logo, ten shop, địa chỉ, hotline và email liên hệ của website. Những thông tin này sẽ được hiển thị ở chân trang (footer) của website.
                </p>
            </div>
            <div class="row">
                {{-- Cột trái: Logo --}}
                <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
                    <div class="stg-logo-wrapper">
                        @if($setting->logo_url)
                            <img id="stg-logo-preview" src="{{ $setting->logo_url }}" alt="Logo" class="stg-logo-preview">
                        @else
                            <div id="stg-logo-preview" class="stg-logo-preview stg-logo-fallback">
                                {{ \Illuminate\Support\Str::of($setting->site_name)->trim()->substr(0, 2)->upper() }}
                            </div>
                        @endif

                        <input type="file" name="logo" id="stg-logo-input" accept="image/*" class="d-none">
                        <label for="stg-logo-input" class="stg-change-btn">Thay đổi ảnh</label>

                        @error('logo')
                            <div class="text-danger mt-2" style="font-size:12px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Cột phải: Thông tin thương hiệu --}}
                <div class="col-md-8 col-lg-9">
                    <h2 class="stg-title">Cài Đặt Website</h2>

                    <div class="mb-3">
                        <label class="stg-label">Tên shop</label>
                        <input type="text" name="site_name" class="stg-input @error('site_name') is-invalid @enderror"
                            value="{{ old('site_name', $setting->site_name) }}" required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="stg-label">Địa chỉ</label>
                        <input type="text" name="address" class="stg-input @error('address') is-invalid @enderror"
                            value="{{ old('address', $setting->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="stg-label">Hotline</label>
                        <input type="text" name="hotline" class="stg-input @error('hotline') is-invalid @enderror"
                            value="{{ old('hotline', $setting->hotline) }}">
                        @error('hotline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="stg-label">Email liên hệ</label>
                        <input type="email" name="email" class="stg-input @error('email') is-invalid @enderror"
                            value="{{ old('email', $setting->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="stg-divider"></div>

                    <button type="submit" class="stg-submit-btn">Cập nhật cài đặt</button>
                </div>
            </div>
        </form>
    </div>
</main>

<style>
.stg-section { max-width: 900px; }

.stg-back-btn {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    padding: 8px 16px; border: 1.5px solid var(--hk-border, #1a1a1a); border-radius: 0;
    background: var(--hk-bg-card, transparent); color: var(--hk-text-1, #1a1a1a);
    cursor: pointer; transition: background-color .2s ease, color .2s ease;
}
.stg-back-btn:hover { background: #000; border-color: #000; color: #fff; }
[data-theme="dark"] .stg-back-btn:hover { background: #fff; border-color: #fff; color: #000; }

.stg-title {
    font-size: 24px; font-weight: 800; letter-spacing: .3px; margin-bottom: 28px;
    color: var(--hk-text-1, #0f172a);
}

.stg-logo-wrapper { text-align: center; }
.stg-logo-preview {
    width: 160px; height: 160px; border-radius: 50%; object-fit: cover;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--hk-border, #e5e7eb); box-shadow: 0 0 0 4px var(--hk-bg-card, #fff);
}
.stg-logo-fallback {
    background: #111827; color: #fff; font-size: 44px; font-weight: 800;
}
.stg-change-btn {
    display: inline-block; margin-top: 14px; background: transparent;
    color: var(--hk-text-1, #1a1a1a); border: 1px solid var(--hk-border, #1a1a1a); border-radius: 0;
    padding: 6px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
    cursor: pointer; transition: all .2s ease;
}
.stg-change-btn:hover { background: #000; border-color: #000; color: #fff; }
[data-theme="dark"] .stg-change-btn:hover { background: #fff; border-color: #fff; color: #000; }

.stg-label {
    display: block; font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--hk-text-2, #64748b); margin-bottom: 6px;
}
.stg-input {
    width: 100%; border-radius: 0; border: 1px solid var(--hk-border, #d1d5db);
    padding: 9px 12px; font-size: 13px; background: var(--hk-bg-card, #fff); color: var(--hk-text-1, #0f172a);
    transition: border-color .2s ease;
}
.stg-input:focus { outline: none; border-color: #000; box-shadow: none; }
[data-theme="dark"] .stg-input:focus { border-color: #fff; }
.stg-input.is-invalid { border-color: #dc2626; }

.stg-divider { border-top: 1px solid var(--hk-border, #e5e7eb); margin: 20px 0; }

.stg-submit-btn {
    background: #000; color: #fff; border: none; border-radius: 0;
    padding: 11px 30px; font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.5px; transition: background-color .2s ease; width: 100%;
}
.stg-submit-btn:hover { background: #1f1f1f; }
[data-theme="dark"] .stg-submit-btn { background: #fff; color: #000; }
[data-theme="dark"] .stg-submit-btn:hover { background: #e5e7eb; }

@media (max-width: 576px) {
    .stg-title { font-size: 20px; margin-bottom: 20px; }
}
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('stg-logo-input');
        const preview = document.getElementById('stg-logo-preview');

        input.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const objectUrl = URL.createObjectURL(file);

            if (preview.tagName === 'IMG') {
                preview.src = objectUrl;
                return;
            }

            const img = document.createElement('img');
            img.id = 'stg-logo-preview';
            img.className = 'stg-logo-preview';
            img.src = objectUrl;
            img.alt = 'Logo';
            preview.replaceWith(img);
        });

        @if(session('success'))
            window.showToast?.(@json(session('success')), 'success');
        @endif
    });
</script>
@endpush
@endsection
