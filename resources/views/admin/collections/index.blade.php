@extends('layouts.admin')

@section('title', 'Quản lý Bộ sưu tập')

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Quản lý Bộ sưu tập</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">Danh sách bộ sưu tập thời trang theo mùa và chủ đề.</p>
        </div>
        <div class="product-header-actions">
            <a href="{{ route('admin.collections.create') }}" class="btn btn-dark product-action-btn">
                <i class="fa-solid fa-plus me-1"></i> Thêm bộ sưu tập
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
                            <th style="width: 120px;">Banner</th>
                            <th>Tên bộ sưu tập</th>
                            <th>Slug</th>
                            <th>Mô tả</th>
                            <th class="text-center" style="width: 150px;">Số sản phẩm</th>
                            <th style="width: 130px;">Trạng thái</th>
                            <th class="text-end pe-3" style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $collection)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $collection->id }}</td>
                                <td>
                                    @if($collection->banner)
                                        <img src="{{ asset($collection->banner) }}" alt="{{ $collection->name }}" 
                                             class="img-thumbnail" style="width: 100px; height: 50px; object-fit: cover;">
                                    @else
                                        <span class="text-muted">Không có ảnh</span>
                                    @endif
                                </td>
                                <td><strong class="text-primary">{{ $collection->name }}</strong></td>
                                <td><code>{{ $collection->slug }}</code></td>
                                <td class="text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $collection->description }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill" style="font-size: 12px;">{{ $collection->products_count }}</span>
                                </td>
                                <td>
                                    @if($collection->status)
                                        <span class="badge bg-success" style="font-size: 11px; padding: 5px 8px;">Hiển thị</span>
                                    @else
                                        <span class="badge bg-danger" style="font-size: 11px; padding: 5px 8px;">Ẩn</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.collections.edit', $collection->id) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.collections.destroy', $collection->id) }}" method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ sưu tập này không?');" class="d-inline">
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
                                <td colspan="8" class="text-center py-4 text-muted">Không có bộ sưu tập nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($collections->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end py-3">
                {{ $collections->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
