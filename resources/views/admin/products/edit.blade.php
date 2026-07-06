@extends('layouts.admin')

@section('title', 'Sửa sản phẩm: ' . $product->name)

@push('styles')
    @include('admin.products.styles')
    <style>
    .create-header-title {
        font-size: 25px;
        font-weight: 800;
        color: #000 !important;
        margin-bottom: 4px;
    }
    .create-header-desc {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* ── Hình ảnh 3 slots ── */
    .img-slots {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .img-slot {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        aspect-ratio: 1 / 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
        overflow: hidden;
        position: relative;
        background: #fafafa;
    }
    .img-slot:hover, .img-slot.drag-over { border-color: #174761; background: #f0f9ff; }
    .img-slot .slot-placeholder { text-align: center; padding: 8px; pointer-events: none; }
    .img-slot .slot-placeholder i { font-size: 22px; color: #9ca3af; }
    .img-slot .slot-placeholder p { font-size: 11px; color: #9ca3af; margin: 4px 0 0; }
    .img-slot .slot-badge {
        position: absolute; top: 6px; left: 6px;
        background: #174761; color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 2px 7px; border-radius: 99px;
        z-index: 1;
    }
    .img-slot img.slot-preview {
        width: 100%; height: 100%;
        object-fit: cover;
        position: absolute; inset: 0;
    }
    .img-slot .slot-remove {
        display: none;
        position: absolute; top: 6px; right: 6px;
        width: 22px; height: 22px; border-radius: 50%;
        background: rgba(0,0,0,0.55); color: #fff;
        border: 0; font-size: 11px;
        align-items: center; justify-content: center;
        cursor: pointer; z-index: 2;
    }
    .img-slot.has-image .slot-remove { display: flex; }
    .img-slot.has-image .slot-placeholder { display: none; }

    /* ── hk-cat trigger inside form ── */
    .hk-cat-form .hk-cat-trigger {
        min-height: 37px;
        border-radius: 6px;
        font-size: 13px;
        border-color: #ced4da;
    }
    .hk-cat-form .hk-cat-trigger:hover,
    .hk-cat-form .hk-cat-trigger.is-open { border-color: #174761; }
    .hk-cat-form .hk-cat-panel { width: 100%; }
    .hk-cat-form.is-invalid .hk-cat-trigger { border-color: #dc3545; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="create-header-title">Sửa sản phẩm</h1>
        <p class="create-header-desc">ID: {{ $product->id }} — <strong>{{ $product->name }}</strong></p>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product->id) }}"
          enctype="multipart/form-data" id="editProductForm">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ── CỘT TRÁI ── --}}
            <div class="col-lg-8">

                {{-- Thông tin chung --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Thông tin chung</span>
                    </div>
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

                        <div class="edit-field mb-0">
                            <label for="description">Mô tả <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" rows="10"
                                class="form-control @error('description') is-invalid @enderror"
                                style="min-height: 220px; resize: vertical;"
                                required>{{ old('description', $product->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Quản lý biến thể --}}
                <div class="card edit-card shadow-sm">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Quản lý biến thể</span>
                    </div>
                    <div class="card-body p-4">
                        @include('admin.products.partials.variant-manager')
                    </div>
                </div>

            </div>

            {{-- ── CỘT PHẢI ── --}}
            <div class="col-lg-4">

                {{-- Phân loại --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Phân loại</span>
                    </div>
                    <div class="card-body p-4">

                        {{-- Danh mục --}}
                        @php
                            $curCatId    = old('category_id', $product->category_id);
                            $curCatLabel = '— Chọn danh mục —';
                            foreach ($categories as $cat) {
                                if ((string)$curCatId === (string)$cat->id) {
                                    $curCatLabel = $cat->name; break;
                                }
                                foreach ($cat->childrenCategories as $child) {
                                    if ((string)$curCatId === (string)$child->id) {
                                        $curCatLabel = $child->name; break 2;
                                    }
                                }
                            }
                        @endphp
                        <div class="edit-field">
                            <label>Danh mục <span class="text-danger">*</span></label>
                            <input type="hidden" name="category_id" id="categoryId" value="{{ $curCatId }}">
                            <div class="hk-cat-filter hk-cat-form w-100 @error('category_id') is-invalid @enderror" id="hkCategoryWrap">
                                <button type="button" class="hk-cat-trigger w-100" id="hkCategoryTrigger">
                                    <span class="hk-cat-trigger-label" id="hkCategoryLabel">{{ $curCatLabel }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="hkCategoryPanel" hidden>
                                    <div class="hk-cat-search-wrap">
                                        <i class="fa-solid fa-magnifying-glass hk-cat-search-icon"></i>
                                        <input type="text" class="hk-cat-search-input" id="hkCategorySearch" placeholder="Tìm danh mục..." autocomplete="off">
                                    </div>
                                    <div class="hk-cat-list" id="hkCategoryList">
                                        @foreach($categories as $cat)
                                            @if(!is_null($cat->parent_id)) @continue @endif
                                            @if($cat->childrenCategories->isEmpty())
                                                <button type="button" class="hk-cat-item {{ (string)$curCatId === (string)$cat->id ? 'is-active' : '' }}"
                                                    data-value="{{ $cat->id }}" data-label="{{ $cat->name }}">
                                                    {{ $cat->name }}
                                                </button>
                                            @else
                                                <div class="hk-cat-group-label px-3 pt-2 pb-1" style="font-size:11px;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">
                                                    {{ $cat->name }}
                                                </div>
                                                @foreach($cat->childrenCategories as $child)
                                                    <button type="button" class="hk-cat-item ps-4 {{ (string)$curCatId === (string)$child->id ? 'is-active' : '' }}"
                                                        data-value="{{ $child->id }}" data-label="{{ $child->name }}">
                                                        {{ $child->name }}
                                                    </button>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Thương hiệu --}}
                        @php
                            $curBrandId    = old('brand_id', $product->brand_id);
                            $curBrandLabel = '— Chọn thương hiệu —';
                            foreach ($brands as $brand) {
                                if ((string)$curBrandId === (string)$brand->id) {
                                    $curBrandLabel = $brand->name; break;
                                }
                            }
                        @endphp
                        <div class="edit-field">
                            <label>Thương hiệu <span class="text-danger">*</span></label>
                            <input type="hidden" name="brand_id" id="brandId" value="{{ $curBrandId }}">
                            <div class="hk-cat-filter hk-cat-form w-100 @error('brand_id') is-invalid @enderror" id="hkBrandWrap">
                                <button type="button" class="hk-cat-trigger w-100" id="hkBrandTrigger">
                                    <span class="hk-cat-trigger-label" id="hkBrandLabel">{{ $curBrandLabel }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="hkBrandPanel" hidden>
                                    <div class="hk-cat-search-wrap">
                                        <i class="fa-solid fa-magnifying-glass hk-cat-search-icon"></i>
                                        <input type="text" class="hk-cat-search-input" id="hkBrandSearch" placeholder="Tìm thương hiệu..." autocomplete="off">
                                    </div>
                                    <div class="hk-cat-list" id="hkBrandList">
                                        @foreach($brands as $brand)
                                            <button type="button" class="hk-cat-item {{ (string)$curBrandId === (string)$brand->id ? 'is-active' : '' }}"
                                                data-value="{{ $brand->id }}" data-label="{{ $brand->name }}">
                                                {{ $brand->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Giới tính --}}
                        <div class="edit-field">
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

                        {{-- Giá --}}
                        <div class="row g-3">
                            <div class="col-7">
                                <div class="edit-field mb-0">
                                    <label for="price">Giá gốc (₫) <span class="text-danger">*</span></label>
                                    <input type="number" id="price" name="price" min="0" step="1000"
                                        class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price', (int) $product->price) }}" required>
                                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="edit-field mb-0">
                                    <label for="discount">Giảm giá (%)</label>
                                    <input type="number" id="discount" name="discount" min="0" max="100"
                                        class="form-control @error('discount') is-invalid @enderror"
                                        value="{{ old('discount', $product->discount) }}" required>
                                    @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Hình ảnh --}}
                @php
                    $extraImages = $product->productImages->values();
                @endphp
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Hình ảnh</span>
                    </div>
                    <div class="card-body p-4">

                        <div class="img-slots mb-3">
                            {{-- Slot 0: Ảnh chính --}}
                            @php
                                $thumbSrc = $product->thumbnail
                                    ? (Str::startsWith($product->thumbnail, 'http') ? $product->thumbnail : asset($product->thumbnail))
                                    : null;
                            @endphp
                            <div class="img-slot {{ $thumbSrc ? 'has-image' : '' }}" id="slot0" data-slot="0">
                                <span class="slot-badge">Chính</span>
                                <div class="slot-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                    <p>Ảnh chính</p>
                                </div>
                                <img src="{{ $thumbSrc ?? '' }}" alt="" class="slot-preview {{ $thumbSrc ? '' : 'd-none' }}">
                                <button type="button" class="slot-remove" data-slot="0" title="Xóa ảnh">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Slot 1: Ảnh phụ 1 --}}
                            @php
                                $img2 = $extraImages->get(0);
                                $img2Src = $img2 ? (Str::startsWith($img2->image, 'http') ? $img2->image : asset($img2->image)) : null;
                            @endphp
                            <div class="img-slot {{ $img2Src ? 'has-image' : '' }}" id="slot1" data-slot="1">
                                <span class="slot-badge" style="background:#6b7280;">2</span>
                                <div class="slot-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                    <p>Ảnh phụ</p>
                                </div>
                                <img src="{{ $img2Src ?? '' }}" alt="" class="slot-preview {{ $img2Src ? '' : 'd-none' }}">
                                <button type="button" class="slot-remove" data-slot="1" title="Xóa ảnh">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Slot 2: Ảnh phụ 2 --}}
                            @php
                                $img3 = $extraImages->get(1);
                                $img3Src = $img3 ? (Str::startsWith($img3->image, 'http') ? $img3->image : asset($img3->image)) : null;
                            @endphp
                            <div class="img-slot {{ $img3Src ? 'has-image' : '' }}" id="slot2" data-slot="2">
                                <span class="slot-badge" style="background:#6b7280;">3</span>
                                <div class="slot-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                    <p>Ảnh phụ</p>
                                </div>
                                <img src="{{ $img3Src ?? '' }}" alt="" class="slot-preview {{ $img3Src ? '' : 'd-none' }}">
                                <button type="button" class="slot-remove" data-slot="2" title="Xóa ảnh">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <input type="file" id="fileInput0" name="thumbnail" accept="image/*" class="d-none @error('thumbnail') is-invalid @enderror">
                        <input type="file" id="fileInput1" name="image_2"   accept="image/*" class="d-none">
                        <input type="file" id="fileInput2" name="image_3"   accept="image/*" class="d-none">
                        @error('thumbnail') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <div class="form-text">JPEG, PNG, WebP · Tối đa 2MB · Để trống để giữ ảnh cũ</div>

                    </div>
                </div>

                {{-- Hoạt động --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Hoạt động</span>
                    </div>
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

                {{-- Nút hành động --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary fw-bold" style="min-height:42px;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật sản phẩm
                    </button>
                    <a href="{{ route('admin.products.list') }}" class="btn btn-light border fw-bold" style="min-height:42px;">
                        <i class="fa-solid fa-xmark me-1"></i> Hủy bỏ
                    </a>
                </div>

            </div>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script>
(function () {

    /* ═══════════════════════════════════════════
       1. IMAGE SLOTS
    ═══════════════════════════════════════════ */
    const slots = document.querySelectorAll('.img-slot');

    slots.forEach(function (slot) {
        const idx       = slot.dataset.slot;
        const input     = document.getElementById('fileInput' + idx);
        const preview   = slot.querySelector('.slot-preview');
        const removeBtn = slot.querySelector('.slot-remove');

        function setPreview(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                slot.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }

        slot.addEventListener('click', function (e) {
            if (e.target.closest('.slot-remove')) return;
            input.click();
        });

        input.addEventListener('change', function () {
            if (this.files[0]) setPreview(this.files[0]);
        });

        slot.addEventListener('dragover', function (e) {
            e.preventDefault();
            slot.classList.add('drag-over');
        });

        slot.addEventListener('dragleave', function () {
            slot.classList.remove('drag-over');
        });

        slot.addEventListener('drop', function (e) {
            e.preventDefault();
            slot.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                setPreview(file);
            }
        });

        removeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            preview.src = '';
            preview.classList.add('d-none');
            slot.classList.remove('has-image');
            input.value = '';
        });
    });

    /* ═══════════════════════════════════════════
       2. HK-CAT DROPDOWNS
    ═══════════════════════════════════════════ */
    function setupHkCat(opts) {
        const trigger = document.getElementById(opts.triggerId);
        const panel   = document.getElementById(opts.panelId);
        const label   = document.getElementById(opts.labelId);
        const search  = document.getElementById(opts.searchId);
        const list    = document.getElementById(opts.listId);
        const hidden  = document.getElementById(opts.hiddenId);
        const wrap    = document.getElementById(opts.wrapId);
        if (!trigger || !panel) return;

        function open() {
            panel.hidden = false;
            trigger.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            if (search) { search.value = ''; filterItems(''); search.focus(); }
        }

        function close() {
            panel.hidden = true;
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        function filterItems(q) {
            q = q.toLowerCase();
            list.querySelectorAll('.hk-cat-item').forEach(function (item) {
                item.style.display = item.dataset.label.toLowerCase().includes(q) ? '' : 'none';
            });
            list.querySelectorAll('.hk-cat-group-label').forEach(function (g) {
                let next = g.nextElementSibling, anyVisible = false;
                while (next && !next.classList.contains('hk-cat-group-label')) {
                    if (next.style.display !== 'none') anyVisible = true;
                    next = next.nextElementSibling;
                }
                g.style.display = anyVisible ? '' : 'none';
            });
        }

        trigger.addEventListener('click', function () { panel.hidden ? open() : close(); });

        if (search) search.addEventListener('input', function () { filterItems(this.value); });

        list.addEventListener('click', function (e) {
            const item = e.target.closest('.hk-cat-item');
            if (!item) return;
            hidden.value = item.dataset.value;
            label.textContent = item.dataset.label;
            list.querySelectorAll('.hk-cat-item').forEach(function (i) {
                i.classList.toggle('is-active', i === item);
            });
            if (wrap) wrap.classList.remove('is-invalid');
            close();
        });

        document.addEventListener('click', function (e) {
            if (!trigger.closest('.hk-cat-filter').contains(e.target)) close();
        });
    }

    setupHkCat({
        triggerId: 'hkCategoryTrigger', panelId: 'hkCategoryPanel',
        labelId:   'hkCategoryLabel',   searchId: 'hkCategorySearch',
        listId:    'hkCategoryList',    hiddenId: 'categoryId',
        wrapId:    'hkCategoryWrap',
    });

    setupHkCat({
        triggerId: 'hkBrandTrigger', panelId: 'hkBrandPanel',
        labelId:   'hkBrandLabel',   searchId: 'hkBrandSearch',
        listId:    'hkBrandList',    hiddenId: 'brandId',
        wrapId:    'hkBrandWrap',
    });

    /* validate on submit */
    document.getElementById('editProductForm').addEventListener('submit', function (e) {
        let ok = true;
        if (!document.getElementById('categoryId').value) {
            document.getElementById('hkCategoryWrap').classList.add('is-invalid');
            ok = false;
        }
        if (!document.getElementById('brandId').value) {
            document.getElementById('hkBrandWrap').classList.add('is-invalid');
            ok = false;
        }
        if (!ok) e.preventDefault();
    });

})();
</script>
@endpush
