@extends('layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
    @include('admin.products.styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
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

    /* ── Rich text editor (Quill) ── */
    #descriptionEditor {
        min-height: 220px;
        background: #fff;
        font-size: 14px;
    }
    .ql-toolbar.ql-snow {
        border-radius: 6px 6px 0 0;
        background: #f9fafb;
    }
    .ql-container.ql-snow {
        border-radius: 0 0 6px 6px;
    }
    .edit-field.is-invalid .ql-toolbar,
    .edit-field.is-invalid .ql-container {
        border-color: #dc3545;
    }

    /* ── Media dropzone ── */
    .media-dropzone {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        padding: 28px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
        background: #fafafa;
    }
    .media-dropzone:hover, .media-dropzone.drag-over {
        border-color: #000;
        background: #f0f9ff;
    }
    .media-dropzone i { font-size: 26px; color: #9ca3af; }
    .media-dropzone p { margin: 8px 0 0; font-size: 13px; color: #6b7280; }
    .media-dropzone p strong { color: #000; }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .media-item {
        position: relative;
        aspect-ratio: 1 / 1;
        border-radius: 8px;
        overflow: hidden;
        border: 1.5px solid #e5e7eb;
        background: #f3f4f6;
    }
    .media-item img {
        width: 100%; height: 100%; object-fit: cover; display: block;
    }
    .media-item .media-remove {
        position: absolute; top: 5px; right: 5px;
        width: 22px; height: 22px; border-radius: 50%;
        background: rgba(0,0,0,0.6); color: #fff;
        border: 0; font-size: 11px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; z-index: 2; transition: background 0.15s;
    }
    .media-item .media-remove:hover { background: #dc2626; }
    .media-item .media-primary-badge {
        position: absolute; top: 5px; left: 5px;
        background: #6b7280; color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 2px 8px; border-radius: 99px;
        cursor: pointer; z-index: 2; transition: background 0.15s;
        border: 0;
    }
    .media-item.is-primary .media-primary-badge { background: #174761; }
    .media-item.is-primary { border-color: #000; box-shadow: 0 0 0 2px rgba(23,71,97,0.15); }

    /* ── hk-cat trigger inside form (form-select height) ── */
    .hk-cat-form .hk-cat-trigger {
        min-height: 37px;
        border-radius: 6px;
        font-size: 13px;
        border-color: #ced4da;
    }
    .hk-cat-form .hk-cat-trigger:hover,
    .hk-cat-form .hk-cat-trigger.is-open {
        border-color: #000;
    }
    .hk-cat-form .hk-cat-panel {
        width: 100%;
    }
    .hk-cat-form.is-invalid .hk-cat-trigger {
        border-color: #dc3545;
    }

    /* ── Sticky action bar ── */
    .sticky-action-bar {
        position: sticky;
        bottom: 16px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        z-index: 20;
    }
    .sticky-action-bar .btn { min-height: 42px; min-width: 150px; }
    @media (max-width: 991px) {
        .sticky-action-bar { flex-direction: column; }
        .sticky-action-bar .btn { width: 100%; }
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

            {{-- ── CỘT TRÁI (70%) ── --}}
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

                        <div class="edit-field mb-0 @error('description') is-invalid @enderror">
                            <label for="descriptionEditor">Mô tả <span class="text-danger">*</span></label>
                            <div id="descriptionEditor">{!! old('description') !!}</div>
                            <textarea id="description" name="description" class="d-none" required>{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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

            {{-- ── CỘT PHẢI (30%) ── --}}
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
                        @php
                            $oldGender = old('gender');
                            $oldGenderLabel = $genders[$oldGender] ?? '— Chọn giới tính —';
                        @endphp
                        <div class="edit-field">
                            <label>Giới tính <span class="text-danger">*</span></label>
                            <input type="hidden" name="gender" id="gender" value="{{ $oldGender ?? '' }}">
                            <div class="hk-cat-filter hk-cat-form w-100 @error('gender') is-invalid @enderror" id="hkGenderWrap">
                                <button type="button" class="hk-cat-trigger w-100" id="hkGenderTrigger">
                                    <span class="hk-cat-trigger-label" id="hkGenderLabel">{{ $oldGenderLabel }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="hkGenderPanel" hidden>
                                    <div class="hk-cat-list" id="hkGenderList">
                                        @foreach($genders as $value => $label)
                                            <button type="button" class="hk-cat-item {{ $oldGender === $value ? 'is-active' : '' }}"
                                                data-value="{{ $value }}"
                                                data-label="{{ $label }}">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('gender') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field mt-3">
                            <label>Chương trình giảm giá</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                        <option value="">Không giảm</option>
                                        <option value="percent" @selected(old('discount_type') === 'percent')>Giảm theo %</option>
                                        <option value="fixed" @selected(old('discount_type') === 'fixed')>Giảm tiền cố định</option>
                                    </select>
                                    @error('discount_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <input type="number" name="discount_value" min="0" step="1000"
                                        class="form-control @error('discount_value') is-invalid @enderror"
                                        value="{{ old('discount_value', 0) }}" placeholder="Giá trị giảm">
                                    @error('discount_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1">Bắt đầu</label>
                                    <input type="datetime-local" name="discount_start_at"
                                        class="form-control @error('discount_start_at') is-invalid @enderror"
                                        value="{{ old('discount_start_at') }}">
                                    @error('discount_start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted mb-1">Kết thúc</label>
                                    <input type="datetime-local" name="discount_end_at"
                                        class="form-control @error('discount_end_at') is-invalid @enderror"
                                        value="{{ old('discount_end_at') }}">
                                    @error('discount_end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-text">Giảm giá cấp sản phẩm sẽ tính trên giá bán của từng biến thể.</div>
                        </div>

                    </div>
                </div>

                {{-- Hình ảnh --}}
                <div class="card edit-card shadow-sm mb-4">
                    <div class="card-header">
                        <span class="fw-bold" style="font-size:14px;">Hình ảnh</span>
                    </div>
                    <div class="card-body p-4">

                        <div class="media-dropzone" id="mediaDropzone">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p><strong>Kéo thả ảnh vào đây</strong> hoặc bấm để chọn file</p>
                            <p class="mb-0">JPEG, PNG, WebP · Tối đa 2MB mỗi ảnh · Không giới hạn số lượng</p>
                        </div>
                        <input type="file" id="mediaFileInput" name="images[]" accept="image/*" multiple class="d-none">

                        <div class="media-grid" id="mediaGrid"></div>

                        <input type="hidden" name="primary_index" id="primaryIndexInput" value="0">
                        @error('images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <div class="form-text mt-2">Bấm nhãn <strong>Ảnh chính</strong> trên một ảnh để đặt làm ảnh đại diện sản phẩm.</div>

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

                {{-- Nút hành động (sticky) --}}
                <div class="sticky-action-bar">
                    <a href="{{ route('admin.products.list') }}" class="btn btn-light border fw-bold">
                        <i class="fa-solid fa-xmark me-1"></i> Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> Lưu sản phẩm
                    </button>
                </div>

            </div>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function () {

    /* ═══════════════════════════════════════════
       1. RICH TEXT EDITOR (Quill)
    ═══════════════════════════════════════════ */
    const descTextarea = document.getElementById('description');
    const quill = new Quill('#descriptionEditor', {
        theme: 'snow',
        placeholder: 'Nhập mô tả chi tiết sản phẩm...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': [1, 2, 3, false] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'image'],
                [{ 'align': [] }],
                ['clean'],
            ],
        },
    });

    quill.on('text-change', function () {
        descTextarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
    });

    /* ═══════════════════════════════════════════
       2. MEDIA DROPZONE (unlimited images + primary)
    ═══════════════════════════════════════════ */
    const dropzone      = document.getElementById('mediaDropzone');
    const fileInput      = document.getElementById('mediaFileInput');
    const grid           = document.getElementById('mediaGrid');
    const primaryInput   = document.getElementById('primaryIndexInput');
    let mediaFiles       = []; // { file, previewUrl }
    let primaryIndex     = 0;

    function renderGrid() {
        grid.innerHTML = '';
        mediaFiles.forEach(function (item, index) {
            const cell = document.createElement('div');
            cell.className = 'media-item' + (index === primaryIndex ? ' is-primary' : '');
            cell.innerHTML = `
                <img src="${item.previewUrl}" alt="">
                <button type="button" class="media-primary-badge" data-index="${index}">
                    ${index === primaryIndex ? 'Ảnh chính' : 'Đặt làm chính'}
                </button>
                <button type="button" class="media-remove" data-index="${index}" title="Xóa ảnh">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            grid.appendChild(cell);
        });
        primaryInput.value = primaryIndex;
        syncFileInput();
    }

    function syncFileInput() {
        const dt = new DataTransfer();
        mediaFiles.forEach(item => dt.items.add(item.file));
        fileInput.files = dt.files;
    }

    function addFiles(fileList) {
        Array.from(fileList).forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            mediaFiles.push({ file: file, previewUrl: URL.createObjectURL(file) });
        });
        renderGrid();
    }

    dropzone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        addFiles(this.files);
    });

    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('drag-over');
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        addFiles(e.dataTransfer.files);
    });

    grid.addEventListener('click', function (e) {
        const removeBtn  = e.target.closest('.media-remove');
        const primaryBtn = e.target.closest('.media-primary-badge');

        if (removeBtn) {
            const idx = parseInt(removeBtn.dataset.index, 10);
            mediaFiles.splice(idx, 1);
            if (primaryIndex >= mediaFiles.length) primaryIndex = 0;
            else if (primaryIndex > idx) primaryIndex--;
            renderGrid();
            return;
        }

        if (primaryBtn) {
            primaryIndex = parseInt(primaryBtn.dataset.index, 10);
            renderGrid();
        }
    });

    /* ═══════════════════════════════════════════
       3. HK-CAT DROPDOWNS (form mode)
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

    setupHkCat({
        triggerId: 'hkGenderTrigger',
        panelId:   'hkGenderPanel',
        labelId:   'hkGenderLabel',
        searchId:  null,
        listId:    'hkGenderList',
        hiddenId:  'gender',
        wrapId:    'hkGenderWrap',
    });

    /* validate required fields on submit */
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
        if (!document.getElementById('gender').value) {
            document.getElementById('hkGenderWrap').classList.add('is-invalid');
            ok = false;
        }
        if (!descTextarea.value.trim()) {
            document.querySelector('.ql-toolbar').style.borderColor = '#dc3545';
            document.querySelector('.ql-container').style.borderColor = '#dc3545';
            ok = false;
        }
        if (!ok) e.preventDefault();
    });

})();
</script>
@endpush
