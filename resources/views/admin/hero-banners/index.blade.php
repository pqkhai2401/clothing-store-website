@extends('layouts.admin')

@section('title', 'Banner trang chủ')

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Banner trang chủ</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">Quản lý các banner hero hiển thị ở đầu trang chủ. Bật nhiều banner cùng lúc sẽ hiển thị dạng slider tự động chuyển.</p>
        </div>
        <div class="product-header-actions">
            <a href="{{ route('admin.hero-banners.create') }}" class="btn btn-dark product-action-btn">
                <i class="fa-solid fa-plus me-1"></i> Thêm banner
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 60px;">ID</th>
                            <th style="width: 140px;">Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả ngắn</th>
                            <th class="text-center" style="width: 90px;">Thứ tự</th>
                            <th style="width: 130px;">Trạng thái</th>
                            <th class="text-end pe-3" style="width: 170px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($heroBanners as $banner)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $banner->id }}</td>
                                <td>
                                    <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}"
                                         class="img-thumbnail" style="width: 110px; height: 55px; object-fit: cover;">
                                </td>
                                <td><strong class="text-primary">{{ $banner->title ?: '—' }}</strong></td>
                                <td class="text-muted" style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $banner->subtitle }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill" style="font-size: 12px;">{{ $banner->sort_order }}</span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.hero-banners.toggleStatus', $banner->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm border-0 p-0" style="background:none;" title="Bấm để bật/tắt hiển thị">
                                            @if($banner->is_active)
                                                <span class="badge bg-success" style="font-size: 11px; padding: 5px 8px;">Đang hiển thị</span>
                                            @else
                                                <span class="badge bg-secondary" style="font-size: 11px; padding: 5px 8px;">Đang tắt</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.hero-banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.hero-banners.destroy', $banner->id) }}" method="POST"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa banner này không?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có banner nào. Trang chủ đang hiển thị banner mặc định.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($heroBanners->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end py-3">
                {{ $heroBanners->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
