@extends('layouts.admin')

@section('title', 'Quản lý màu sắc')

@push('styles')
    @include('admin.colors.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý màu sắc</h1>
                <p class="product-header-desc mb-0">Danh sách màu sắc dùng cho biến thể sản phẩm.</p>
                <div class="product-header-actions">
                    <button type="button" class="btn btn-dark product-action-btn"
                        data-bs-toggle="modal" data-bs-target="#addColorModal">
                        <i class="fa-solid fa-plus me-1"></i> Thêm màu sắc
                    </button>
                    <a href="{{ route('admin.colors.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.colors.list') }}" id="colorSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="colorRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên màu sắc..." autocomplete="off">
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.colors.partials.table')
            </div>
        </section>

        {{-- Add Color Modal --}}
        <div class="modal fade hk-add-modal" id="addColorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
                <div class="modal-content">
                    <form id="addColorForm" method="POST" autocomplete="off">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title">Thêm màu sắc mới</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="add_color_name" class="form-label">
                                    Tên màu sắc <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="add_color_name" name="name" class="form-control"
                                    placeholder="Ví dụ: Đen, Trắng, Xanh navy...">
                                <div class="invalid-feedback d-block" data-error-for="name"></div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Mã màu HEX</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" id="add_color_picker" value="#000000"
                                        class="color-hex-picker" title="Chọn màu">
                                    <input type="text" id="add_color_hex_code" name="hex_code"
                                        class="form-control color-hex-input"
                                        placeholder="#000000" maxlength="7" autocomplete="off">
                                </div>
                                <div class="invalid-feedback d-block" data-error-for="hex_code"></div>
                                <div class="form-text">Ví dụ: #000000 (Đen), #FFFFFF (Trắng), #000080 (Xanh navy)</div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-end">
                            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-dark fw-semibold" id="addColorSubmitBtn">
                                <i class="fa-solid fa-plus me-1"></i> Thêm màu sắc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Color Modal --}}
        <div class="modal fade hk-add-modal" id="editColorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Sửa màu sắc</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_color_name" class="form-label">
                                Tên màu sắc <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="edit_color_name" name="name" class="form-control"
                                placeholder="Nhập tên màu sắc...">
                            <div class="invalid-feedback d-block" data-error-for="name"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Mã màu HEX</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="edit_color_picker" value="#000000"
                                    class="color-hex-picker" title="Chọn màu">
                                <input type="text" id="edit_color_hex_code" name="hex_code"
                                    class="form-control color-hex-input"
                                    placeholder="#000000" maxlength="7" autocomplete="off">
                            </div>
                            <div class="invalid-feedback d-block" data-error-for="hex_code"></div>
                            <div class="form-text">Ví dụ: #000000 (Đen), #FFFFFF (Trắng), #000080 (Xanh navy)</div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-dark fw-semibold" id="editColorSubmitBtn">
                            <i class="fa-regular fa-pen-to-square me-1"></i> Lưu thay đổi
                        </button>
                    </div>
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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

        function syncColorPicker(picker, textInput) {
            picker.addEventListener('input', () => {
                textInput.value = picker.value.toUpperCase();
            });
            textInput.addEventListener('input', () => {
                const val = textInput.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(val)) picker.value = val;
            });
        }

        function resetErrors(modalEl) {
            modalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalEl.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; });
        }

        function showErrors(modalEl, errors) {
            resetErrors(modalEl);
            let first = null;
            Object.entries(errors || {}).forEach(([name, messages]) => {
                const field   = modalEl.querySelector(`[name="${name}"]`);
                const errorEl = modalEl.querySelector(`[data-error-for="${name}"]`);
                if (field) { field.classList.add('is-invalid'); first = first || field; }
                if (errorEl) errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
            });
            first?.focus();
        }

        // ── Add modal ──────────────────────────────────────────────────
        const addModalEl   = document.getElementById('addColorModal');
        const addForm      = document.getElementById('addColorForm');
        const addSubmitBtn = document.getElementById('addColorSubmitBtn');
        const addNameInput = document.getElementById('add_color_name');
        const addPicker    = document.getElementById('add_color_picker');
        const addHexInput  = document.getElementById('add_color_hex_code');

        if (addModalEl && addForm && addSubmitBtn) {
            const addLabelHtml = addSubmitBtn.innerHTML;
            syncColorPicker(addPicker, addHexInput);

            addModalEl.addEventListener('shown.bs.modal', () => addNameInput?.focus());
            addModalEl.addEventListener('hidden.bs.modal', () => {
                addForm.reset();
                if (addPicker)  addPicker.value  = '#000000';
                if (addHexInput) addHexInput.value = '';
                resetErrors(addModalEl);
            });

            addForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                resetErrors(addModalEl);
                addSubmitBtn.disabled = true;
                addSubmitBtn.innerHTML = 'Đang thêm...';

                try {
                    const res  = await fetch('{{ route("admin.colors.store") }}', {
                        method:  'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                        body:    new FormData(addForm),
                    });
                    const data = await res.json();
                    if (res.status === 422) { showErrors(addModalEl, data.errors || {}); return; }
                    if (!res.ok) throw new Error(data.message || 'Có lỗi xảy ra.');
                    bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
                    showToast(data.message || 'Thêm thành công');
                    window.reloadAdminTable?.();
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    addSubmitBtn.disabled = false;
                    addSubmitBtn.innerHTML = addLabelHtml;
                }
            });
        }

        // ── Edit modal ─────────────────────────────────────────────────
        const editModalEl   = document.getElementById('editColorModal');
        const editSubmitBtn = document.getElementById('editColorSubmitBtn');
        const editNameInput = document.getElementById('edit_color_name');
        const editPicker    = document.getElementById('edit_color_picker');
        const editHexInput  = document.getElementById('edit_color_hex_code');
        let   currentUpdateUrl = '';

        if (editModalEl && editSubmitBtn) {
            const editLabelHtml = editSubmitBtn.innerHTML;
            syncColorPicker(editPicker, editHexInput);

            editModalEl.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                if (!btn) return;
                editNameInput.value = btn.dataset.editName || '';
                const hex = btn.dataset.editHex || '';
                editHexInput.value  = hex;
                editPicker.value    = /^#[0-9A-Fa-f]{6}$/.test(hex) ? hex : '#000000';
                currentUpdateUrl    = btn.dataset.editUrl || '';
                resetErrors(editModalEl);
            });

            editModalEl.addEventListener('shown.bs.modal', () => { editNameInput?.focus(); editNameInput?.select(); });
            editModalEl.addEventListener('hidden.bs.modal', () => { currentUpdateUrl = ''; resetErrors(editModalEl); });

            editSubmitBtn.addEventListener('click', async function () {
                if (!currentUpdateUrl) return;
                resetErrors(editModalEl);
                editSubmitBtn.disabled = true;
                editSubmitBtn.innerHTML = 'Đang lưu...';

                const formData = new FormData();
                formData.append('_method',   'PUT');
                formData.append('name',      editNameInput.value.trim());
                formData.append('hex_code',  editHexInput.value.trim());

                try {
                    const res  = await fetch(currentUpdateUrl, {
                        method:  'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                        body:    formData,
                    });
                    const data = await res.json();
                    if (res.status === 422) { showErrors(editModalEl, data.errors || {}); return; }
                    if (!res.ok) throw new Error(data.message || 'Có lỗi xảy ra.');
                    bootstrap.Modal.getOrCreateInstance(editModalEl).hide();
                    showToast(data.message || 'Cập nhật thành công');
                    window.reloadAdminTable?.();
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    editSubmitBtn.disabled = false;
                    editSubmitBtn.innerHTML = editLabelHtml;
                }
            });
        }
    })();
    </script>
@endpush
