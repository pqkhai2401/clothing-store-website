@extends('layouts.admin')

@section('title', 'Thêm Bộ sưu tập mới')

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Thêm Bộ sưu tập mới</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">Tạo bộ sưu tập thời trang theo mùa và gán sản phẩm tương ứng.</p>
        </div>
        <div class="small text-muted">
            Trang chủ <span class="mx-1">/</span> 
            <a href="{{ route('admin.collections.list') }}" class="text-decoration-none">Bộ sưu tập</a> 
            <span class="mx-1">/</span> Thêm mới
        </div>
    </div>

    <form method="POST" action="{{ route('admin.collections.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Panel: Info -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <span class="fw-bold text-uppercase text-secondary" style="font-size: 13px;">Thông tin chung</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên bộ sưu tập <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Ví dụ: Bộ sưu tập Xuân 2026" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label fw-bold">Đường dẫn thân thiện (Slug)</label>
                            <input type="text" id="slug" name="slug" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   value="{{ old('slug') }}" placeholder="Tự động tạo từ tên nếu bỏ trống">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả bộ sưu tập</label>
                            <textarea id="description" name="description" rows="4" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Mô tả phong cách, chất liệu, ý nghĩa của BST này...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="banner" class="form-label fw-bold">Ảnh Banner</label>
                            <input type="file" id="banner" name="banner" 
                                   class="form-control @error('banner') is-invalid @enderror" accept="image/*">
                            <div class="form-text">Định dạng hỗ trợ: jpg, png, webp. Dung lượng tối đa: 2MB. Tỉ lệ ảnh ngang (ví dụ: 1200x500).</div>
                            @error('banner')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Trạng thái hiển thị</label>
                            <div class="form-check form-switch form-check-inline mt-1">
                                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                                <label class="form-check-label fw-semibold" for="status">Kích hoạt hiển thị trên Website</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Products Selection -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100 d-flex flex-column">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-uppercase text-secondary" style="font-size: 13px;">Gán sản phẩm vào BST</span>
                        <span class="badge bg-dark rounded-pill" id="selectedCount">Đã chọn: 0</span>
                    </div>
                    <div class="card-body p-0 flex-grow-1" style="max-height: 420px; overflow-y: auto;">
                        <div class="p-3 border-bottom bg-light">
                            <input type="text" id="productSearch" class="form-control form-control-sm" placeholder="Tìm nhanh sản phẩm theo tên...">
                        </div>
                        <div class="list-group list-group-flush" id="productList">
                            @forelse($products as $product)
                                <label class="list-group-item d-flex align-items-center gap-3 py-2 px-3 product-item" style="cursor: pointer;">
                                    <input class="form-check-input flex-shrink-0 product-checkbox" type="checkbox" name="products[]" value="{{ $product->id }}">
                                    @if($product->thumbnail)
                                        <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" 
                                             class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="text-truncate fw-semibold product-name" style="font-size: 13px;">{{ $product->name }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Danh mục: {{ $product->category->name ?? 'N/A' }} | Giá: {{ $product->min_variant_price ? 'Từ '.number_format($product->min_variant_price, 0, ',', '.').'đ' : 'Liên hệ' }}
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="text-center py-4 text-muted">Không có sản phẩm nào khả dụng.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2 border-top">
                        <a href="{{ route('admin.collections.list') }}" class="btn btn-light border fw-bold" style="min-height: 40px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary fw-bold" style="min-height: 40px; min-width: 120px;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu lại
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('productSearch');
        const productItems = document.querySelectorAll('.product-item');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const selectedCount = document.getElementById('selectedCount');

        // Tìm kiếm nhanh
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                productItems.forEach(item => {
                    const name = item.querySelector('.product-name').textContent.toLowerCase();
                    if (name.includes(query)) {
                        item.classList.remove('d-none');
                        item.classList.add('d-flex');
                    } else {
                        item.classList.remove('d-flex');
                        item.classList.add('d-none');
                    }
                });
            });
        }

        // Cập nhật số lượng đã chọn
        function updateCount() {
            const count = Array.from(checkboxes).filter(cb => cb.checked).length;
            selectedCount.textContent = `Đã chọn: ${count}`;
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCount);
        });
    });
</script>
@endsection
