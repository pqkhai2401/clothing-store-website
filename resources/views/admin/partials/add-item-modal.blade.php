{{--
    Reusable "Add item" modal for simple single-field modules (brands, colors, sizes).

    Required variables:
        $modalId       - HTML id of the modal element, e.g. 'addBrandModal'
        $formId        - HTML id of the form element, e.g. 'addBrandForm'
        $submitBtnId   - HTML id of the submit button, e.g. 'addBrandSubmitBtn'
        $modalTitle    - Title shown in modal header, e.g. 'Thêm thương hiệu'
        $fieldLabel    - Label text for the input, e.g. 'Tên thương hiệu'
        $fieldName     - Input name attribute, e.g. 'name'
        $fieldId       - Input HTML id, e.g. 'add_brand_name'
        $fieldPlaceholder - Placeholder text
        $storeUrl      - Route URL for POST, e.g. route('admin.brands.store')
        $submitLabel   - Button label, e.g. 'Thêm thương hiệu'
        $successLabel  - Label used in success fallback, e.g. 'thương hiệu'
--}}

<div class="modal fade hk-add-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content">
            <form id="{{ $formId }}" method="POST" autocomplete="off">
                @csrf

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
                            name="{{ $fieldName }}"
                            id="{{ $fieldId }}"
                            class="form-control"
                            placeholder="{{ $fieldPlaceholder ?? '' }}"
                        >
                        <div class="invalid-feedback d-block" data-error-for="{{ $fieldName }}"></div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-dark fw-semibold" id="{{ $submitBtnId }}">
                        <i class="fa-solid fa-plus me-1"></i> {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('{{ $modalId }}');
    const form      = document.getElementById('{{ $formId }}');
    const submitBtn = document.getElementById('{{ $submitBtnId }}');
    if (!modalEl || !form || !submitBtn) return;

    const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const storeUrl      = '{{ $storeUrl }}';
    const submitLabelHtml = submitBtn.innerHTML;

    const ICONS = {
        success: '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        error:   '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
    };

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const typeClass = type === 'error' ? 'toast-error' : 'toast-success';
        const toast = document.createElement('div');
        toast.className = `custom-toast server-toast ${typeClass}`;
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
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; });
    }

    function showErrors(errors) {
        resetErrors();
        let first = null;
        Object.entries(errors || {}).forEach(([name, messages]) => {
            const field   = form.elements[name];
            const errorEl = form.querySelector(`[data-error-for="${name}"]`);
            if (field) { field.classList.add('is-invalid'); first = first || field; }
            if (errorEl) errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
        first?.focus();
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        form.elements['{{ $fieldName }}']?.focus();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        form.reset();
        resetErrors();
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        resetErrors();
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Đang thêm...';

        try {
            const res  = await fetch(storeUrl, {
                method:  'POST',
                headers: {
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     csrfToken,
                },
                body: new FormData(form),
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
            showToast(data.message || 'Thêm thành công');
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
