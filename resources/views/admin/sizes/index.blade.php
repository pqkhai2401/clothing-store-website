@extends('layouts.admin')

@section('title', 'Quản lý kích thước')

@push('styles')
    @include('admin.sizes.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý kích thước</h1>
                <p class="product-header-desc mb-0">
                    Danh sách kích thước cho các nhóm sản phẩm Nam/Nữ đang kinh doanh.
                </p>
                <div class="product-header-actions">
                    <button type="button" class="btn btn-dark product-action-btn" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                        <i class="fa-solid fa-plus me-1"></i> Thêm kích thước
                    </button>
                    <a href="{{ route('admin.sizes.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.sizes.list') }}" id="sizeSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="sizeRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên kích thước hoặc nhóm danh mục..." autocomplete="off">
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.sizes.partials.table')
            </div>
        </section>

        <div class="modal fade hk-add-modal" id="editSizeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Sửa kích thước</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>

                    <div class="modal-body">
                        <div class="edit-field">
                            <label for="edit_size_name">Tên kích thước <span class="text-danger">*</span></label>
                            <input type="text" id="edit_size_name" name="name"
                                class="form-control"
                                placeholder="Ví dụ: XS, S, M, L, 36, 37...">
                            <div class="invalid-feedback d-block" data-error-for="name"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="edit-field">
                                    <label for="edit_size_category_group">Nhóm danh mục <span class="text-danger">*</span></label>
                                    <select id="edit_size_category_group" name="category_group" class="form-select">
                                        @foreach($categoryGroups as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback d-block" data-error-for="category_group"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="edit-field">
                                    <label for="edit_size_sort_weight">Thứ tự hiển thị</label>
                                    <input type="number" min="0" id="edit_size_sort_weight" name="sort_weight"
                                        class="form-control" value="0">
                                    <div class="invalid-feedback d-block" data-error-for="sort_weight"></div>
                                </div>
                            </div>
                        </div>

                        <div class="edit-field mb-0">
                            <label for="edit_size_status">Trạng thái <span class="text-danger">*</span></label>
                            <select id="edit_size_status" name="status" class="form-select">
                                <option value="1">Hoạt động</option>
                                <option value="0">Ẩn</option>
                            </select>
                            <div class="invalid-feedback d-block" data-error-for="status"></div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-dark fw-semibold" id="editSizeSubmitBtn">
                            <i class="fa-regular fa-pen-to-square me-1"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade hk-add-modal" id="addSizeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.sizes.store') }}" autocomplete="off">
                        @csrf

                        <div class="modal-header">
                            <h2 class="modal-title">Thêm kích thước</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>

                        <div class="modal-body">
                            <div class="edit-field">
                                <label for="add_size_name">Tên kích thước <span class="text-danger">*</span></label>
                                <input type="text" id="add_size_name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Ví dụ: XS, S, M, L, 36, 37..." required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="edit-field">
                                        <label for="add_size_category_group">Nhóm danh mục <span class="text-danger">*</span></label>
                                        <select id="add_size_category_group" name="category_group"
                                            class="form-select @error('category_group') is-invalid @enderror" required>
                                            @foreach($categoryGroups as $value => $label)
                                                <option value="{{ $value }}" @selected(old('category_group', 'quan_ao') === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_group')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="edit-field">
                                        <label for="add_size_sort_weight">Thứ tự hiển thị</label>
                                        <input type="number" min="0" id="add_size_sort_weight" name="sort_weight"
                                            class="form-control @error('sort_weight') is-invalid @enderror"
                                            value="{{ old('sort_weight', 0) }}">
                                        @error('sort_weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="edit-field mb-0">
                                <label for="add_size_status">Trạng thái <span class="text-danger">*</span></label>
                                <select id="add_size_status" name="status"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="1" @selected((string) old('status', '1') === '1')>Hoạt động</option>
                                    <option value="0" @selected((string) old('status') === '0')>Ẩn</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer justify-content-end">
                            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-dark fw-semibold">
                                <i class="fa-solid fa-plus me-1"></i> Thêm kích thước
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        const modalEl       = document.getElementById('editSizeModal');
        const nameInput     = document.getElementById('edit_size_name');
        const groupSelect   = document.getElementById('edit_size_category_group');
        const weightInput   = document.getElementById('edit_size_sort_weight');
        const statusSelect  = document.getElementById('edit_size_status');
        const submitBtn     = document.getElementById('editSizeSubmitBtn');
        if (!modalEl || !nameInput || !submitBtn) return;

        const csrfToken       = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const submitLabelHtml = submitBtn.innerHTML;
        let currentUpdateUrl  = '';

        const ICONS = {
            success: '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            error:   '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        };

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-icon">
                        <svg style="flex-shrink:0;display:block;" viewBox="0 0 24 24" width="22" height="22">
                            ${ICONS[type] ?? ICONS.success}
                        </svg>
                    </div>
                    <div class="toast-message">${message}</div>
                </div>
                <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    toast.classList.add('hiding');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        function resetErrors() {
            modalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalEl.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; });
        }

        function showErrors(errors) {
            resetErrors();
            let first = null;
            Object.entries(errors || {}).forEach(([name, messages]) => {
                const field   = modalEl.querySelector(`[name="${name}"]`);
                const errorEl = modalEl.querySelector(`[data-error-for="${name}"]`);
                if (field) { field.classList.add('is-invalid'); first = first || field; }
                if (errorEl) errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
            });
            first?.focus();
        }

        modalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            nameInput.value             = btn.dataset.editName          || '';
            groupSelect.value           = btn.dataset.editCategoryGroup || '';
            weightInput.value           = btn.dataset.editSortWeight    ?? '0';
            statusSelect.value          = btn.dataset.editStatus        ?? '1';
            currentUpdateUrl            = btn.dataset.editUrl           || '';
            resetErrors();
        });

        modalEl.addEventListener('shown.bs.modal', function () {
            nameInput.focus();
            nameInput.select();
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            currentUpdateUrl = '';
            resetErrors();
        });

        submitBtn.addEventListener('click', async function () {
            if (!currentUpdateUrl) return;
            resetErrors();
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang lưu...';

            const formData = new FormData();
            formData.append('_method',        'PUT');
            formData.append('name',           nameInput.value.trim());
            formData.append('category_group', groupSelect.value);
            formData.append('sort_weight',    weightInput.value);
            formData.append('status',         statusSelect.value);

            try {
                const res  = await fetch(currentUpdateUrl, {
                    method:  'POST',
                    headers: {
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN':     csrfToken,
                    },
                    body: formData,
                });

                const data = await res.json();

                if (res.status === 422) {
                    showErrors(data.errors || {});
                    return;
                }

                if (!res.ok) {
                    throw new Error(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                }

                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                showToast(data.message || 'Cập nhật thành công');
                window.reloadAdminTable?.();

            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitLabelHtml;
            }
        });
    })();
    </script>
@endpush
