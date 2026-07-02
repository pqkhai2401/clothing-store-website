@extends('layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
    @include('admin.products.styles')
    <style>
    /* ── Page header ── */
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

    /* ── hk-cat trigger inside form (form-select height) ── */
    .hk-cat-form .hk-cat-trigger {
        min-height: 37px;
        border-radius: 6px;
        font-size: 13px;
        border-color: #ced4da;
    }
    .hk-cat-form .hk-cat-trigger:hover,
    .hk-cat-form .hk-cat-trigger.is-open {
        border-color: #174761;
    }
    .hk-cat-form .hk-cat-panel {
        width: 100%;
    }
    .hk-cat-form.is-invalid .hk-cat-trigger {
        border-color: #dc3545;
    }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="create-header-title">Thêm sản phẩm mới</h1>
            <p class="create-header-desc">Điền đầy đủ thông tin để tạo sản phẩm mới</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="createProductForm">
        @csrf

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
                                value="{{ old('name') }}"
                                placeholder="Nhập tên sản phẩm"
                                required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug"
                                class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug') }}"
                                placeholder="Tự động sinh từ tên nếu để trống">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Để trống để tự động tạo từ tên sản phẩm.</div>
                        </div>

                        <div class="edit-field mb-0">
                            <label for="description">Mô tả <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" rows="10"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Nhập mô tả chi tiết sản phẩm..."
                                style="min-height: 220px; resize: vertical;"
                                required>{{ old('description') }}</textarea>
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
                            $oldCatId = old('category_id');
                            $oldCatLabel = '— Chọn danh mục —';
                            foreach ($categories as $cat) {
                                if ((string)$oldCatId === (string)$cat->id) {
                                    $oldCatLabel = $cat->name; break;
                                }
                                foreach ($cat->childrenCategories as $child) {
                                    if ((string)$oldCatId === (string)$child->id) {
                                        $oldCatLabel = $child->name; break 2;
                                    }
                                }
                            }
                        @endphp
                        <div class="edit-field">
                            <label>Danh mục <span class="text-danger">*</span></label>
                            <input type="hidden" name="category_id" id="categoryId" value="{{ $oldCatId ?? '' }}">
                            <div class="hk-cat-filter hk-cat-form w-100 @error('category_id') is-invalid @enderror" id="hkCategoryWrap">
                                <button type="button" class="hk-cat-trigger w-100" id="hkCategoryTrigger">
                                    <span class="hk-cat-trigger-label" id="hkCategoryLabel">{{ $oldCatLabel }}</span>
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
                                                <button type="button" class="hk-cat-item"
                                                    data-value="{{ $cat->id }}"
                                                    data-label="{{ $cat->name }}">
                                                    {{ $cat->name }}
                                                </button>
                                            @else
                                                <div class="hk-cat-group-label px-3 pt-2 pb-1" style="font-size:11px;font-weight:800;color:#010101;text-transform:uppercase;letter-spacing:.05em;">
                                                    {{ $cat->name }}
                                                </div>
                                                @foreach($cat->childrenCategories as $child)
                                                    <button type="button" class="hk-cat-item ps-4"
                                                        data-value="{{ $child->id }}"
                                                        data-label="{{ $child->name }}">
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
                            $oldBrandId = old('brand_id');
                            $oldBrandLabel = '— Chọn thương hiệu —';
                            foreach ($brands as $brand) {
                                if ((string)$oldBrandId === (string)$brand->id) {
                                    $oldBrandLabel = $brand->name; break;
                                }
                            }
                        @endphp
                        <div class="edit-field">
                            <label>Thương hiệu <span class="text-danger">*</span></label>
                            <input type="hidden" name="brand_id" id="brandId" value="{{ $oldBrandId ?? '' }}">
                            <div class="hk-cat-filter hk-cat-form w-100 @error('brand_id') is-invalid @enderror" id="hkBrandWrap">
                                <button type="button" class="hk-cat-trigger w-100" id="hkBrandTrigger">
                                    <span class="hk-cat-trigger-label" id="hkBrandLabel">{{ $oldBrandLabel }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="hkBrandPanel" hidden>
                                    <div class="hk-cat-search-wrap">
                                        <i class="fa-solid fa-magnifying-glass hk-cat-search-icon"></i>
                                        <input type="text" class="hk-cat-search-input" id="hkBrandSearch" placeholder="Tìm thương hiệu..." autocomplete="off">
                                    </div>
                                    <div class="hk-cat-list" id="hkBrandList">
                                        @foreach($brands as $brand)
                                            <button type="button" class="hk-cat-item"
                                                data-value="{{ $brand->id }}"
                                                data-label="{{ $brand->name }}">
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
                                        {{ old('gender') === $value ? 'selected' : '' }}>
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
                                        value="{{ old('price', 0) }}" required>
                                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="edit-field mb-0">
                                    <label for="discount">Giảm giá (%)</label>
                                    <input type="number" id="discount" name="discount" min="0" max="100"
                                        class="form-control @error('discount') is-invalid @enderror"
                                        value="{{ old('discount', 0) }}" required>
                                    @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Hình ảnh --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Hình ảnh</span>
                    </div>
                    <div class="card-body p-4">

                        <div class="img-slots mb-3">
                            {{-- Slot 1: Ảnh chính --}}
                            <div class="img-slot" id="slot0" data-slot="0">
                                <span class="slot-badge">Chính</span>
                                <div class="slot-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                    <p>Ảnh chính</p>
                                </div>
                                <img src="" alt="" class="slot-preview d-none">
                                <button type="button" class="slot-remove" data-slot="0" title="Xóa ảnh">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            {{-- Slot 2 --}}
                            <div class="img-slot" id="slot1" data-slot="1">
                                <span class="slot-badge" style="background:#6b7280;">2</span>
                                <div class="slot-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                    <p>Ảnh phụ</p>
                                </div>
                                <img src="" alt="" class="slot-preview d-none">
                                <button type="button" class="slot-remove" data-slot="1" title="Xóa ảnh">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            {{-- Slot 3 --}}
                            <div class="img-slot" id="slot2" data-slot="2">
                                <span class="slot-badge" style="background:#6b7280;">3</span>
                                <div class="slot-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                    <p>Ảnh phụ</p>
                                </div>
                                <img src="" alt="" class="slot-preview d-none">
                                <button type="button" class="slot-remove" data-slot="2" title="Xóa ảnh">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Hidden file inputs --}}
                        <input type="file" id="fileInput0" name="thumbnail" accept="image/*" class="d-none @error('thumbnail') is-invalid @enderror">
                        <input type="file" id="fileInput1" name="image_2"   accept="image/*" class="d-none">
                        <input type="file" id="fileInput2" name="image_3"   accept="image/*" class="d-none">
                        @error('thumbnail') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <div class="form-text">JPEG, PNG, WebP · Tối đa 2MB mỗi ảnh</div>

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
                                    {{ old('status', true) ? 'checked' : '' }}>
                                Đang bán (hiển thị trên website)
                            </label>
                        </div>
                        <div class="edit-field mb-0">
                            <label class="form-check-label d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input mt-0"
                                    {{ old('is_featured') ? 'checked' : '' }}>
                                Sản phẩm nổi bật
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Nút hành động --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary fw-bold" style="min-height:42px;">
                        <i class="fa-solid fa-plus me-1"></i> Lưu sản phẩm
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
        const idx     = slot.dataset.slot;
        const input   = document.getElementById('fileInput' + idx);
        const preview = slot.querySelector('.slot-preview');
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
       2. HK-CAT DROPDOWNS (form mode)
    ═══════════════════════════════════════════ */
    function setupHkCat(opts) {
        /* opts: { triggerId, panelId, labelId, searchId, listId, hiddenId, wrapId } */
        const trigger  = document.getElementById(opts.triggerId);
        const panel    = document.getElementById(opts.panelId);
        const label    = document.getElementById(opts.labelId);
        const search   = document.getElementById(opts.searchId);
        const list     = document.getElementById(opts.listId);
        const hidden   = document.getElementById(opts.hiddenId);
        const wrap     = document.getElementById(opts.wrapId);
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
            const items = list.querySelectorAll('.hk-cat-item');
            const groups = list.querySelectorAll('.hk-cat-group-label');
            q = q.toLowerCase();
            items.forEach(function (item) {
                const match = item.dataset.label.toLowerCase().includes(q);
                item.style.display = match ? '' : 'none';
            });
            /* hide group labels if all children hidden */
            groups.forEach(function (g) {
                let next = g.nextElementSibling;
                let anyVisible = false;
                while (next && !next.classList.contains('hk-cat-group-label')) {
                    if (next.style.display !== 'none') anyVisible = true;
                    next = next.nextElementSibling;
                }
                g.style.display = anyVisible ? '' : 'none';
            });
        }

        trigger.addEventListener('click', function () {
            panel.hidden ? open() : close();
        });

        if (search) {
            search.addEventListener('input', function () {
                filterItems(this.value);
            });
        }

        list.addEventListener('click', function (e) {
            const item = e.target.closest('.hk-cat-item');
            if (!item) return;
            const val  = item.dataset.value;
            const lbl  = item.dataset.label;
            hidden.value = val;
            label.textContent = lbl;
            list.querySelectorAll('.hk-cat-item').forEach(function (i) {
                i.classList.toggle('is-active', i === item);
            });
            /* remove invalid state */
            if (wrap) wrap.classList.remove('is-invalid');
            close();
        });

        /* close on outside click */
        document.addEventListener('click', function (e) {
            if (!trigger.closest('.hk-cat-filter').contains(e.target)) close();
        });

        /* mark active on initial load (old()) */
        if (hidden.value) {
            list.querySelectorAll('.hk-cat-item').forEach(function (i) {
                if (i.dataset.value === hidden.value) i.classList.add('is-active');
            });
        }
    }

    setupHkCat({
        triggerId: 'hkCategoryTrigger',
        panelId:   'hkCategoryPanel',
        labelId:   'hkCategoryLabel',
        searchId:  'hkCategorySearch',
        listId:    'hkCategoryList',
        hiddenId:  'categoryId',
        wrapId:    'hkCategoryWrap',
    });

    setupHkCat({
        triggerId: 'hkBrandTrigger',
        panelId:   'hkBrandPanel',
        labelId:   'hkBrandLabel',
        searchId:  'hkBrandSearch',
        listId:    'hkBrandList',
        hiddenId:  'brandId',
        wrapId:    'hkBrandWrap',
    });

    /* validate required dropdowns on submit */
    document.getElementById('createProductForm').addEventListener('submit', function (e) {
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
