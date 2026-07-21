@extends('layouts.admin')

@section('title', 'Banner trang chủ')

@push('styles')
    @include('admin.products.styles')
    <style>
        .hero-banner-table { min-width: 900px; }

        .hero-banner-thumb {
            width: 110px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .hero-banner-row-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: #0F172A;
            background: #F1F5F9;
            font-size: 13px;
            transition: background .15s, color .15s;
        }
        .hero-banner-row-action-btn:hover {
            background: #E2E8F0;
            color: #020617;
        }
        .hero-banner-row-action-btn.text-danger:hover {
            background: #FEF2F2;
            color: #DC2626;
        }

        .hero-banner-status-btn {
            border: 0;
            background: none;
            padding: 0;
            cursor: pointer;
        }

        [data-theme="dark"] .hero-banner-row-action-btn {
            background: #101C33 !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .hero-banner-row-action-btn:hover {
            background: #162843 !important;
            color: #fff !important;
        }
        [data-theme="dark"] .hero-banner-row-action-btn.text-danger:hover {
            background: rgba(239, 68, 68, 0.15) !important;
            color: #F87171 !important;
        }
        [data-theme="dark"] .hero-banner-thumb {
            border-color: #22324D !important;
        }
    </style>
@endpush

@section('content')
<div class="product-admin-page container-fluid py-4">
    <x-notification />

    <section class="px-3 px-md-4">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="product-header-title mb-2">Banner trang chủ</h1>
                <p class="product-header-desc mb-0">Quản lý các banner hero hiển thị ở đầu trang chủ. Bật nhiều banner cùng lúc sẽ hiển thị dạng slider tự động chuyển.</p>
            </div>
            <div class="product-header-actions">
                <a href="{{ route('admin.hero-banners.create') }}" class="btn btn-dark product-action-btn">
                    <i class="fa-solid fa-plus me-1"></i> Thêm banner
                </a>
            </div>
        </div>

        <div class="product-table-wrap mt-4">
            <div class="table-responsive">
                <table class="table table-hover product-table hero-banner-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 140px;">Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả ngắn</th>
                            <th class="text-center" style="width: 90px;">Thứ tự</th>
                            <th style="width: 150px;">Trạng thái</th>
                            <th class="text-center" style="width: 110px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($heroBanners as $banner)
                            <tr>
                                <td class="fw-bold">{{ $banner->id }}</td>
                                <td>
                                    <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" class="hero-banner-thumb">
                                </td>
                                <td><strong class="text-dark">{{ $banner->title ?: '—' }}</strong></td>
                                <td class="text-muted" style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $banner->subtitle }}
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-badge--inactive">{{ $banner->sort_order }}</span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.hero-banners.toggleStatus', $banner->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="hero-banner-status-btn" title="Bấm để bật/tắt hiển thị">
                                            @if($banner->is_active)
                                                <span class="status-badge status-badge--active">Đang hiển thị</span>
                                            @else
                                                <span class="status-badge status-badge--inactive">Đang tắt</span>
                                            @endif
                                        </button>
                                    </form>
                                    @if($banner->isExpired())
                                        <span class="status-badge status-badge--draft mt-1 d-inline-block" title="end_date đã qua — trang chủ tự ẩn banner này dù đang bật">
                                            <i class="fa-solid fa-clock me-1"></i>Đã hết hạn
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('admin.hero-banners.edit', $banner->id) }}" class="hero-banner-row-action-btn" title="Sửa">
                                            <i class="fa-solid fa-pen-clip"></i>
                                        </a>
                                        <button type="button" class="hero-banner-row-action-btn text-danger"
                                            data-delete-url="{{ route('admin.hero-banners.destroy', $banner->id) }}"
                                            data-delete-name="{{ $banner->title ?: '#'.$banner->id }}"
                                            data-delete-type="banner"
                                            title="Xóa">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fa-solid fa-images text-muted mb-3" style="font-size:42px;display:block;"></i>
                                    <div class="fw-semibold text-muted">Chưa có banner nào. Trang chủ đang hiển thị banner mặc định.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($heroBanners->hasPages())
            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                {{ $heroBanners->links() }}
            </div>
        @endif
    </section>
</div>

@push('modals')
    @include('layouts.components.confirm.delete')
@endpush
@endsection
