@extends('layouts.admin')

@section('title', 'Quản lý nhà cung cấp')

@push('styles')
    @include('admin.suppliers.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý nhà cung cấp</h1>
                <p class="product-header-desc mb-0">Danh sách nhà cung cấp dùng cho phiếu nhập kho.</p>
            </div>

            <form method="GET" action="{{ route('admin.suppliers.list') }}" id="supplierSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-filters-row">
                    <div class="product-toolbar-left">
                        <input type="search" name="search" data-admin-search id="supplierRealtimeSearch"
                            class="form-control product-search"
                            value="{{ $keyword }}"
                            placeholder="Tìm kiếm theo tên, SĐT, email..." autocomplete="off">
                    </div>
                    <div class="product-toolbar-right product-header-actions">
                        <a href="{{ route('admin.suppliers.trash') }}" class="btn btn-light border product-action-btn">
                            <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                        </a>
                        <button type="button" class="btn btn-dark product-action-btn"
                            data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                            <i class="fa-solid fa-plus me-1"></i> Thêm nhà cung cấp
                        </button>
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.suppliers.partials.table')
            </div>
        </section>

        {{-- Add Supplier Modal --}}
        <div class="modal fade hk-add-modal" id="addSupplierModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                <div class="modal-content">
                    <form id="addSupplierForm" method="POST" autocomplete="off">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title">Thêm nhà cung cấp mới</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="add_supplier_name" class="form-label">
                                    Tên nhà cung cấp <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="add_supplier_name" name="name" class="form-control"
                                    placeholder="Ví dụ: Công ty TNHH Dệt May ABC">
                                <div class="invalid-feedback d-block" data-error-for="name"></div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="add_supplier_phone" class="form-label">Số điện thoại</label>
                                    <input type="text" id="add_supplier_phone" name="phone" class="form-control" placeholder="0901234567">
                                    <div class="invalid-feedback d-block" data-error-for="phone"></div>
                                </div>
                                <div class="col-6">
                                    <label for="add_supplier_email" class="form-label">Email</label>
                                    <input type="email" id="add_supplier_email" name="email" class="form-control" placeholder="contact@supplier.com">
                                    <div class="invalid-feedback d-block" data-error-for="email"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="add_supplier_address" class="form-label">Địa chỉ</label>
                                <input type="text" id="add_supplier_address" name="address" class="form-control" placeholder="Địa chỉ nhà cung cấp">
                                <div class="invalid-feedback d-block" data-error-for="address"></div>
                            </div>
                            <div class="mb-0">
                                <label for="add_supplier_note" class="form-label">Ghi chú</label>
                                <textarea id="add_supplier_note" name="note" rows="2" class="form-control" placeholder="Ghi chú thêm..."></textarea>
                                <div class="invalid-feedback d-block" data-error-for="note"></div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-end">
                            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-dark fw-semibold" id="addSupplierSubmitBtn">
                                <i class="fa-solid fa-plus me-1"></i> Thêm nhà cung cấp
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Supplier Modal --}}
        <div class="modal fade hk-add-modal" id="editSupplierModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Sửa nhà cung cấp</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_supplier_name" class="form-label">
                                Tên nhà cung cấp <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="edit_supplier_name" name="name" class="form-control">
                            <div class="invalid-feedback d-block" data-error-for="name"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="edit_supplier_phone" class="form-label">Số điện thoại</label>
                                <input type="text" id="edit_supplier_phone" name="phone" class="form-control">
                                <div class="invalid-feedback d-block" data-error-for="phone"></div>
                            </div>
                            <div class="col-6">
                                <label for="edit_supplier_email" class="form-label">Email</label>
                                <input type="email" id="edit_supplier_email" name="email" class="form-control">
                                <div class="invalid-feedback d-block" data-error-for="email"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_supplier_address" class="form-label">Địa chỉ</label>
                            <input type="text" id="edit_supplier_address" name="address" class="form-control">
                            <div class="invalid-feedback d-block" data-error-for="address"></div>
                        </div>
                        <div class="mb-0">
                            <label for="edit_supplier_note" class="form-label">Ghi chú</label>
                            <textarea id="edit_supplier_note" name="note" rows="2" class="form-control"></textarea>
                            <div class="invalid-feedback d-block" data-error-for="note"></div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-dark fw-semibold" id="editSupplierSubmitBtn">
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

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="toast-content">
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
        const addModalEl   = document.getElementById('addSupplierModal');
        const addForm      = document.getElementById('addSupplierForm');
        const addSubmitBtn = document.getElementById('addSupplierSubmitBtn');
        const addNameInput = document.getElementById('add_supplier_name');

        if (addModalEl && addForm && addSubmitBtn) {
            const addLabelHtml = addSubmitBtn.innerHTML;

            addModalEl.addEventListener('shown.bs.modal', () => addNameInput?.focus());
            addModalEl.addEventListener('hidden.bs.modal', () => {
                addForm.reset();
                resetErrors(addModalEl);
            });

            addForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                resetErrors(addModalEl);
                addSubmitBtn.disabled = true;
                addSubmitBtn.innerHTML = 'Đang thêm...';

                try {
                    const res  = await fetch('{{ route("admin.suppliers.store") }}', {
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
        const editModalEl    = document.getElementById('editSupplierModal');
        const editSubmitBtn  = document.getElementById('editSupplierSubmitBtn');
        const editNameInput  = document.getElementById('edit_supplier_name');
        const editPhoneInput = document.getElementById('edit_supplier_phone');
        const editEmailInput = document.getElementById('edit_supplier_email');
        const editAddrInput  = document.getElementById('edit_supplier_address');
        const editNoteInput  = document.getElementById('edit_supplier_note');
        let   currentUpdateUrl = '';

        if (editModalEl && editSubmitBtn) {
            const editLabelHtml = editSubmitBtn.innerHTML;

            editModalEl.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                if (!btn) return;
                editNameInput.value  = btn.dataset.editName || '';
                editPhoneInput.value = btn.dataset.editPhone || '';
                editEmailInput.value = btn.dataset.editEmail || '';
                editAddrInput.value  = btn.dataset.editAddress || '';
                editNoteInput.value  = btn.dataset.editNote || '';
                currentUpdateUrl     = btn.dataset.editUrl || '';
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
                formData.append('_method', 'PUT');
                formData.append('name',    editNameInput.value.trim());
                formData.append('phone',   editPhoneInput.value.trim());
                formData.append('email',   editEmailInput.value.trim());
                formData.append('address', editAddrInput.value.trim());
                formData.append('note',    editNoteInput.value.trim());

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
    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function closeAllPanels(except) {
            document.querySelectorAll('.supplier-status-panel').forEach(function (p) {
                if (p === except) return;
                p.hidden = true;
                p.closest('.supplier-status-dropdown')?.querySelector('.supplier-status-trigger')?.classList.remove('is-open');
            });
        }

        document.addEventListener('click', async function (e) {
            const trigger = e.target.closest('.supplier-status-trigger');
            if (trigger) {
                const dropdown = trigger.closest('.supplier-status-dropdown');
                const panel = dropdown.querySelector('.supplier-status-panel');
                const willOpen = panel.hidden;
                closeAllPanels();
                panel.hidden = !willOpen;
                trigger.classList.toggle('is-open', willOpen);
                return;
            }

            const item = e.target.closest('.supplier-status-panel .hk-cat-item');
            if (item) {
                const dropdown = item.closest('.supplier-status-dropdown');
                const btn = dropdown.querySelector('.supplier-status-trigger');
                const newValue = item.dataset.value;
                const newCss = item.dataset.css;
                closeAllPanels();

                if (newValue === btn.dataset.value) return;

                const previousValue = btn.dataset.value;
                const previousCss = Array.from(btn.classList).find(c => c.startsWith('status-badge--'));
                btn.disabled = true;

                try {
                    const res = await fetch(dropdown.dataset.toggleUrl, {
                        method: 'POST',
                        headers: {
                            'Accept':           'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN':     csrf,
                        },
                        body: new URLSearchParams({ _method: 'PATCH' }),
                    });

                    if (!res.ok) throw new Error('toggle failed');

                    btn.className = 'status-badge supplier-status-trigger ' + newCss;
                    btn.dataset.value = newValue;
                    btn.querySelector('.supplier-status-trigger-label').textContent = item.textContent;
                    dropdown.querySelectorAll('.hk-cat-item').forEach(function (b) {
                        b.classList.toggle('is-active', b === item);
                    });
                } catch {
                    window.showAlert('Không thể cập nhật trạng thái. Vui lòng thử lại.', 'Lỗi', 'danger');
                    btn.className = 'status-badge supplier-status-trigger ' + (previousCss ?? '');
                    btn.dataset.value = previousValue;
                } finally {
                    btn.disabled = false;
                }
                return;
            }

            if (!e.target.closest('.supplier-status-dropdown')) {
                closeAllPanels();
            }
        });
    })();
    </script>
@endpush
