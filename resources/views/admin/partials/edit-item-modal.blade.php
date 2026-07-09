{{--
    Reusable "Edit item" modal for simple single-field modules (brands, colors).

    Required variables:
        $modalId          - e.g. 'editBrandModal'
        $submitBtnId      - e.g. 'editBrandSubmitBtn'
        $modalTitle       - e.g. 'Sửa thương hiệu'
        $fieldLabel       - e.g. 'Tên thương hiệu'
        $fieldId          - e.g. 'edit_brand_name'
        $fieldPlaceholder - e.g. 'Nhập tên thương hiệu...'
        $submitLabel      - e.g. 'Lưu thay đổi'
--}}

<div class="modal fade hk-add-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ $modalTitle }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-1">
                    <label for="{{ $fieldId }}" class="form-label">
                        {{ $fieldLabel }} <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="{{ $fieldId }}"
                        name="name"
                        class="form-control"
                        placeholder="{{ $fieldPlaceholder ?? '' }}"
                    >
                    <div class="invalid-feedback d-block" data-error-for="name"></div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-dark fw-semibold" id="{{ $submitBtnId }}">
                    <i class="fa-regular fa-pen-to-square me-1"></i> {{ $submitLabel ?? 'Lưu thay đổi' }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl    = document.getElementById('{{ $modalId }}');
    const nameInput  = document.getElementById('{{ $fieldId }}');
    const errorEl    = modalEl?.querySelector('[data-error-for="name"]');
    const submitBtn  = document.getElementById('{{ $submitBtnId }}');
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
        nameInput.classList.remove('is-invalid');
        if (errorEl) errorEl.textContent = '';
    }

    function showErrors(errors) {
        resetErrors();
        if (errors.name) {
            nameInput.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = Array.isArray(errors.name) ? errors.name[0] : errors.name;
            nameInput.focus();
        }
    }

    modalEl.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        nameInput.value  = btn.dataset.editName || '';
        currentUpdateUrl = btn.dataset.editUrl  || '';
        resetErrors();
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        nameInput.focus();
        nameInput.select();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        nameInput.value  = '';
        currentUpdateUrl = '';
        resetErrors();
    });

    submitBtn.addEventListener('click', async function () {
        if (!currentUpdateUrl) return;
        resetErrors();
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Đang lưu...';

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', nameInput.value.trim());

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
