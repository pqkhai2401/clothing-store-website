@extends('layouts.admin')

@section('title', 'Sửa sản phẩm: ' . $product->name)

@section('css')
<style>
    .edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
    .edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
    .edit-field { margin-bottom: 18px; }
    .edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .edit-field .form-control,
    .edit-field .form-select { font-size: 13px; border-color: var(--hk-border, #ced4da); background: var(--hk-bg-input, #fff); color: var(--hk-text-1, #111); }
    .edit-field .form-control:focus,
    .edit-field .form-select:focus { border-color: #174761; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
    .edit-field .form-text { font-size: 12px; color: var(--hk-text-3, #6b7280); margin-top: 4px; }
    .section-label { font-size: 11px; font-weight: 800; color: #174761; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px; padding-bottom: 6px; border-bottom: 2px solid #e5e7eb; }
    .thumb-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid var(--hk-border, #e5e7eb); }
    .thumb-placeholder { width: 120px; height: 120px; border-radius: 6px; border: 1px dashed var(--hk-border, #d1d5db); background: var(--hk-bg-th, #f9fafb); display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 28px; }
    .edit-actions { display: flex; gap: 10px; padding-top: 10px; }
    .edit-actions .btn { min-height: 38px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 20px; }
    .form-check-label { font-size: 13px; font-weight: 600; }
</style>
@endsection

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa sản phẩm</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">ID: {{ $product->id }} — <strong>{{ $product->name }}</strong></p>
        </div>
        <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.products.list') }}" class="text-decoration-none">Sản phẩm</a> <span class="mx-1">/</span> Sửa</div>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Cột trái: thông tin chính --}}
            <div class="col-lg-8">
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Thông tin cơ bản</span></div>
                    <div class="card-body p-4">
                        <div class="edit-field">
                            <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $product->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug"
                                class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug', $product->slug) }}"
                                placeholder="Tự động sinh từ tên nếu để trống">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Để trống để tự động tạo từ tên.</div>
                        </div>

                        <div class="edit-field">
                            <label for="description">Mô tả sản phẩm <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" rows="5"
                                class="form-control @error('description') is-invalid @enderror"
                                required>{{ old('description', $product->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="card edit-card shadow-sm">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Giá &amp; Phân loại</span></div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="edit-field mb-0">
                                    <label for="price">Giá gốc (₫) <span class="text-danger">*</span></label>
                                    <input type="number" id="price" name="price" min="0" step="1000"
                                        class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price', (int) $product->price) }}" required>
                                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="edit-field mb-0">
                                    <label for="discount">Giảm giá (%) <span class="text-danger">*</span></label>
                                    <input type="number" id="discount" name="discount" min="0" max="100"
                                        class="form-control @error('discount') is-invalid @enderror"
                                        value="{{ old('discount', $product->discount) }}" required>
                                    @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">0 = không giảm giá. Tối đa 100%.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="edit-field mb-0">
                                    <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                                    <select id="category_id" name="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror" required>
                                        <option value="">— Chọn danh mục —</option>
                                        @foreach($categories as $cat)
                                            @if(is_null($cat->parent_id))
                                                <optgroup label="{{ $cat->name }}">
                                                    <option value="{{ $cat->id }}"
                                                        {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                                        {{ $cat->name }}
                                                    </option>
                                                @foreach($cat->childrenCategories as $child)
                                                    <option value="{{ $child->id }}"
                                                        {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                                        &nbsp;&nbsp;↳ {{ $child->name }}
                                                    </option>
                                                @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="edit-field mb-0">
                                    <label for="brand_id">Thương hiệu <span class="text-danger">*</span></label>
                                    <select id="brand_id" name="brand_id"
                                        class="form-select @error('brand_id') is-invalid @enderror" required>
                                        <option value="">— Chọn thương hiệu —</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="edit-field mb-0">
                                    <label for="gender">Giới tính <span class="text-danger">*</span></label>
                                    <select id="gender" name="gender"
                                        class="form-select @error('gender') is-invalid @enderror" required>
                                        @foreach($genders as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('gender', $product->gender) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột phải: ảnh & trạng thái --}}
            <div class="col-lg-4">
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Ảnh đại diện</span></div>
                    <div class="card-body p-4">
                        {{-- Preview ảnh hiện tại --}}
                        <div class="mb-3">
                            @if($product->thumbnail)
                                <img id="thumbPreview" src="{{ Str::startsWith($product->thumbnail, 'http') ? $product->thumbnail : asset($product->thumbnail) }}"
                                    class="thumb-preview" alt="Ảnh hiện tại">
                            @else
                                <div id="thumbPlaceholder" class="thumb-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                                <img id="thumbPreview" src="" class="thumb-preview d-none" alt="">
                            @endif
                            <p class="mt-2 mb-0" style="font-size:11px; color:#6b7280;">Ảnh hiện tại</p>
                        </div>

                        <div class="edit-field mb-0">
                            <label for="thumbnail">Upload ảnh mới</label>
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                                class="form-control @error('thumbnail') is-invalid @enderror">
                            @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">JPEG, PNG, WebP. Tối đa 3MB. Để trống để giữ ảnh cũ.</div>
                        </div>
                    </div>
                </div>

                <div class="card edit-card shadow-sm">
                    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Trạng thái</span></div>
                    <div class="card-body p-4">
                        <div class="edit-field">
                            <label class="form-check-label d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="form-check-input mt-0"
                                    {{ old('status', $product->status) ? 'checked' : '' }}>
                                Đang bán (hiển thị trên website)
                            </label>
                        </div>

                        <div class="edit-field mb-0">
                            <label class="form-check-label d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input mt-0"
                                    {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                Sản phẩm nổi bật
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="edit-actions mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật sản phẩm
            </button>
            <a href="{{ route('admin.products.list') }}" class="btn btn-light border">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script>
    // Preview ảnh khi chọn file mới
    document.getElementById('thumbnail').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('thumbPreview');
            const placeholder = document.getElementById('thumbPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
