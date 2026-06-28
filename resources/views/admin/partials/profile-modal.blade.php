{{-- ═══════════════════════════════════════════════════════════════
     Profile Modal — CSS
     ═══════════════════════════════════════════════════════════════ --}}
<style>
/* ── Overlay ────────────────────────────────────────────────── */
#profileModal.modal .modal-dialog {
    max-width: 720px;
}

#profileModal .modal-content {
    border: none;
    border-radius: 16px !important;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    overflow: hidden;
}

/* ── Header ─────────────────────────────────────────────────── */
#profileModal .modal-header {
    padding: 24px 28px 20px;
    border-bottom: 1px solid #E2E8F0;
    background: #ffffff;
}

#profileModal .pm-title {
    font-size: 18px;
    font-weight: 800;
    color: #020617;
    margin: 0 0 2px;
    line-height: 1.3;
}

#profileModal .pm-subtitle {
    font-size: 13px;
    color: #64748B;
    margin: 0;
}

#profileModal .btn-close { opacity: 0.5; }
#profileModal .btn-close:hover { opacity: 1; }

/* ── Body ───────────────────────────────────────────────────── */
#profileModal .modal-body {
    padding: 0;
    max-height: 68vh;
    overflow-y: auto;
}

#profileModal .modal-body::-webkit-scrollbar { width: 5px; }
#profileModal .modal-body::-webkit-scrollbar-track { background: transparent; }
#profileModal .modal-body::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }

/* ── Section ────────────────────────────────────────────────── */
.pm-section {
    padding: 24px 28px;
    border-bottom: 1px solid #F1F5F9;
}

.pm-section:last-child {
    border-bottom: 0;
}

.pm-section-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #000000;
    margin: 0 0 18px;
}

/* ── Grid ───────────────────────────────────────────────────── */
.pm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 20px;
}

.pm-grid .pm-full {
    grid-column: 1 / -1;
}

@media (max-width: 576px) {
    .pm-grid { grid-template-columns: 1fr; }
    .pm-grid .pm-full { grid-column: 1; }
}

/* ── Form group ─────────────────────────────────────────────── */
.pm-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.pm-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.pm-input {
    height: 40px;
    padding: 0 12px;
    border: 1px solid #D8E0EA;
    border-radius: 8px;
    font-size: 14px;
    color: #0F172A;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    width: 100%;
}

.pm-input:focus {
    border-color: #16A34A;
    box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
}

.pm-input.pm-readonly {
    background: #F8FAFC;
    color: #64748B;
    cursor: default;
}

.pm-input.pm-readonly:focus {
    border-color: #D8E0EA;
    box-shadow: none;
}

.pm-input.is-invalid {
    border-color: #EF4444 !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,0.10) !important;
}

.pm-error {
    font-size: 12px;
    color: #EF4444;
    margin: 0;
    display: none;
}

.pm-error.show { display: block; }

/* ── Password wrapper ───────────────────────────────────────── */
.pm-pw-wrap {
    position: relative;
}

.pm-pw-wrap .pm-input {
    padding-right: 40px;
}

.pm-pw-eye {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    color: #94A3B8;
    cursor: pointer;
    line-height: 1;
    font-size: 15px;
    display: flex;
    align-items: center;
}

.pm-pw-eye:hover { color: #475569; }

/* ── Footer ─────────────────────────────────────────────────── */
#profileModal .modal-footer {
    padding: 16px 28px;
    border-top: 1px solid #E2E8F0;
    background: #F8FAFC;
    gap: 10px;
}

.pm-btn-cancel {
    padding: 9px 20px;
    border-radius: 10px;
    border: 1px solid #D8E0EA;
    background: #ffffff;
    color: #0F172A;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}

.pm-btn-cancel:hover {
    background: #F1F5F9;
    border-color: #CBD5E1;
}

.pm-btn-save {
    padding: 9px 22px;
    border-radius: 10px;
    border: none;
    background: #16A34A;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.pm-btn-save:hover:not(:disabled) { background: #15803D; }

.pm-btn-save:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* ── Toast ──────────────────────────────────────────────────── */
#pmToast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1090;
    min-width: 300px;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    display: none;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.14);
    animation: pmSlideIn 0.25s ease;
}

#pmToast.show { display: flex; }

#pmToast.pm-toast-success {
    background: #ECFDF5;
    border: 1px solid #86EFAC;
    color: #15803D;
}

#pmToast.pm-toast-error {
    background: #FEF2F2;
    border: 1px solid #FCA5A5;
    color: #B91C1C;
}

@keyframes pmSlideIn {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ── Dark mode ──────────────────────────────────────────────── */
[data-theme="dark"] #profileModal .modal-content {
    background: #0F1B31;
    border: 1px solid #22324D;
}

[data-theme="dark"] #profileModal .modal-header {
    background: #0C1830;
    border-color: #22324D;
}

[data-theme="dark"] #profileModal .pm-title { color: #F8FAFC; }
[data-theme="dark"] #profileModal .pm-subtitle { color: #c8c8c8; }
[data-theme="dark"] #profileModal .btn-close { filter: invert(1); }

[data-theme="dark"] .pm-section { border-color: #22324D; }
[data-theme="dark"] .pm-section-title { color: #ffffff; }
[data-theme="dark"] .pm-label { color: #94A3B8; }

[data-theme="dark"] .pm-input {
    background: #101C33;
    border-color: #2A3B59;
    color: #E2E8F0;
}

[data-theme="dark"] .pm-input:focus {
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
}

[data-theme="dark"] .pm-input.pm-readonly {
    background: #0C1830;
    color: #64748B;
}

[data-theme="dark"] .pm-input.pm-readonly:focus {
    border-color: #2A3B59;
    box-shadow: none;
}

[data-theme="dark"] .pm-pw-eye { color: #64748B; }
[data-theme="dark"] .pm-pw-eye:hover { color: #94A3B8; }

[data-theme="dark"] #profileModal .modal-footer {
    background: #0C1830;
    border-color: #22324D;
}

[data-theme="dark"] .pm-btn-cancel {
    background: #16243A;
    border-color: #2A3B59;
    color: #E2E8F0;
}

[data-theme="dark"] .pm-btn-cancel:hover {
    background: #1D3150;
}

[data-theme="dark"] #pmToast.pm-toast-success {
    background: rgba(34,197,94,0.14);
    border-color: rgba(34,197,94,0.35);
    color: #86EFAC;
}

[data-theme="dark"] #pmToast.pm-toast-error {
    background: rgba(239,68,68,0.14);
    border-color: rgba(239,68,68,0.35);
    color: #FCA5A5;
}

[data-theme="dark"] #profileModal .modal-body::-webkit-scrollbar-thumb {
    background: #22324D;
}
</style>

{{-- ═══════════════════════════════════════════════════════════════
     Toast
     ═══════════════════════════════════════════════════════════════ --}}
<div id="pmToast" role="alert">
    <i id="pmToastIcon" class="fa-solid fa-circle-check"></i>
    <span id="pmToastMsg"></span>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     Profile Modal HTML
     ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header">
                <div class="flex-grow-1">
                    <p class="pm-title" id="profileModalLabel">Hồ sơ của tôi</p>
                    <p class="pm-subtitle">Quản lý thông tin tài khoản và địa chỉ liên hệ của bạn.</p>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                <form id="profileForm" novalidate>
                    @csrf

                    {{-- ── 1. Thông tin tài khoản ── --}}
                    <div class="pm-section">
                        <p class="pm-section-title">Thông tin tài khoản</p>

                        <div class="pm-grid">
                            {{-- Họ và tên --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_username">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="pm-input" id="profile_username" name="username"
                                    placeholder="Nhập họ và tên..." autocomplete="name">
                                <p class="pm-error" id="err_username"></p>
                            </div>

                            {{-- Email --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="pm-input" id="profile_email" name="email"
                                    placeholder="Nhập email..." autocomplete="email">
                                <p class="pm-error" id="err_email"></p>
                            </div>

                            {{-- Số điện thoại --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_phone_number">Số điện thoại</label>
                                <input type="tel" class="pm-input" id="profile_phone_number" name="phone_number"
                                    placeholder="Nhập số điện thoại..." autocomplete="tel">
                                <p class="pm-error" id="err_phone_number"></p>
                            </div>

                            {{-- Vai trò --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_role">Vai trò</label>
                                <input type="text" class="pm-input pm-readonly" id="profile_role"
                                    readonly tabindex="-1">
                            </div>

                            {{-- Trạng thái --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_status">Trạng thái</label>
                                <input type="text" class="pm-input pm-readonly" id="profile_status"
                                    readonly tabindex="-1">
                            </div>
                        </div>
                    </div>

                    {{-- ── 2. Đổi mật khẩu ── --}}
                    <div class="pm-section">
                        <p class="pm-section-title">Đổi mật khẩu</p>
                        <p style="font-size:13px; color:#64748B; margin: -10px 0 16px;">
                            Để trống nếu không muốn đổi mật khẩu.
                        </p>

                        <div class="pm-grid">
                            {{-- Mật khẩu hiện tại --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_current_password">Mật khẩu hiện tại</label>
                                <div class="pm-pw-wrap">
                                    <input type="password" class="pm-input" id="profile_current_password"
                                        name="current_password" placeholder="Nhập mật khẩu hiện tại..."
                                        autocomplete="current-password">
                                    <button type="button" class="pm-pw-eye" data-pw-toggle="profile_current_password"
                                        tabindex="-1" title="Hiện/ẩn mật khẩu">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                <p class="pm-error" id="err_current_password"></p>
                            </div>

                            {{-- Mật khẩu mới --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_password">Mật khẩu mới</label>
                                <div class="pm-pw-wrap">
                                    <input type="password" class="pm-input" id="profile_password"
                                        name="password" placeholder="Tối thiểu 8 ký tự..."
                                        autocomplete="new-password">
                                    <button type="button" class="pm-pw-eye" data-pw-toggle="profile_password"
                                        tabindex="-1" title="Hiện/ẩn mật khẩu">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                <p class="pm-error" id="err_password"></p>
                            </div>

                            {{-- Xác nhận mật khẩu --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_password_confirmation">Xác nhận mật khẩu mới</label>
                                <div class="pm-pw-wrap">
                                    <input type="password" class="pm-input" id="profile_password_confirmation"
                                        name="password_confirmation" placeholder="Nhập lại mật khẩu mới..."
                                        autocomplete="new-password">
                                    <button type="button" class="pm-pw-eye" data-pw-toggle="profile_password_confirmation"
                                        tabindex="-1" title="Hiện/ẩn mật khẩu">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── 3. Thông tin liên hệ ── --}}
                    <div class="pm-section">
                        <p class="pm-section-title">Thông tin liên hệ</p>

                        <div class="pm-grid">
                            {{-- Tỉnh / Thành phố --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_city">Tỉnh / Thành phố</label>
                                <input type="text" class="pm-input" id="profile_city" name="city"
                                    placeholder="Nhập tỉnh/thành phố...">
                                <p class="pm-error" id="err_city"></p>
                            </div>

                            {{-- Phường / Xã --}}
                            <div class="pm-group">
                                <label class="pm-label" for="profile_ward">Phường / Xã</label>
                                <input type="text" class="pm-input" id="profile_ward" name="ward"
                                    placeholder="Nhập phường/xã...">
                                <p class="pm-error" id="err_ward"></p>
                            </div>

                            {{-- Địa chỉ cụ thể --}}
                            <div class="pm-group pm-full">
                                <label class="pm-label" for="profile_apartment_number">Địa chỉ cụ thể</label>
                                <input type="text" class="pm-input" id="profile_apartment_number"
                                    name="apartment_number"
                                    placeholder="Nhập số nhà, tên đường...">
                                <p class="pm-error" id="err_apartment_number"></p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer justify-content-end">
                <button type="button" class="pm-btn-cancel" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="pm-btn-save" id="profileSaveBtn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span id="profileSaveTxt">Lưu thay đổi</span>
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     Profile Modal — JavaScript
     ═══════════════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    const PROFILE_URL = '{{ route("admin.profile") }}';
    const UPDATE_URL  = '{{ route("admin.profile.update") }}';
    const CSRF_TOKEN  = '{{ csrf_token() }}';

    const modal        = document.getElementById('profileModal');
    const form         = document.getElementById('profileForm');
    const saveBtn      = document.getElementById('profileSaveBtn');
    const saveTxt      = document.getElementById('profileSaveTxt');
    const bsModal      = modal ? new bootstrap.Modal(modal) : null;

    // ── Error helpers ─────────────────────────────────────────────
    const FIELDS = [
        'username', 'email', 'phone_number',
        'current_password', 'password',
        'city', 'ward', 'apartment_number',
    ];

    function clearErrors() {
        FIELDS.forEach(key => {
            const input = document.getElementById('profile_' + key);
            const err   = document.getElementById('err_' + key);
            if (input) input.classList.remove('is-invalid');
            if (err)   { err.textContent = ''; err.classList.remove('show'); }
        });
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([key, messages]) => {
            const input = document.getElementById('profile_' + key);
            const err   = document.getElementById('err_' + key);
            if (input) input.classList.add('is-invalid');
            if (err)   { err.textContent = messages[0]; err.classList.add('show'); }
        });
        // Scroll to first error
        const firstErr = form.querySelector('.is-invalid');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ── Toast ─────────────────────────────────────────────────────
    let toastTimer = null;
    function showToast(msg, type = 'success') {
        const toast   = document.getElementById('pmToast');
        const toastMsg  = document.getElementById('pmToastMsg');
        const toastIcon = document.getElementById('pmToastIcon');
        if (!toast) return;

        clearTimeout(toastTimer);
        toast.className = 'show pm-toast-' + type;
        toastIcon.className = type === 'success'
            ? 'fa-solid fa-circle-check'
            : 'fa-solid fa-circle-exclamation';
        toastMsg.textContent = msg;

        toastTimer = setTimeout(() => { toast.className = ''; }, 3500);
    }

    // ── Fill form from API data ───────────────────────────────────
    function fillForm(data) {
        document.getElementById('profile_username').value     = data.username     ?? '';
        document.getElementById('profile_email').value        = data.email        ?? '';
        document.getElementById('profile_phone_number').value = data.phone_number ?? '';
        document.getElementById('profile_role').value         = data.role_label   ?? '';
        document.getElementById('profile_status').value       = data.status_label ?? '';
        document.getElementById('profile_city').value         = data.city         ?? '';
        document.getElementById('profile_ward').value         = data.ward         ?? '';
        document.getElementById('profile_apartment_number').value = data.apartment_number ?? '';

        // Email field: only admin can edit
        const emailInput = document.getElementById('profile_email');
        if (data.can_edit_email) {
            emailInput.classList.remove('pm-readonly');
            emailInput.removeAttribute('readonly');
            emailInput.removeAttribute('title');
        } else {
            emailInput.classList.add('pm-readonly');
            emailInput.setAttribute('readonly', '');
            emailInput.setAttribute('title', 'Chỉ quản trị viên mới có thể thay đổi email.');
        }

        // Clear password fields on open
        ['profile_current_password', 'profile_password', 'profile_password_confirmation']
            .forEach(id => { document.getElementById(id).value = ''; });
    }

    // ── Open modal: fetch data then show ─────────────────────────
    async function openProfile() {
        clearErrors();
        setSaving(false);

        try {
            const res  = await fetch(PROFILE_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            });
            if (!res.ok) throw new Error('Không thể tải dữ liệu hồ sơ.');
            const data = await res.json();
            fillForm(data);
            bsModal?.show();
        } catch (e) {
            showToast(e.message || 'Có lỗi xảy ra khi tải hồ sơ.', 'error');
        }
    }

    // ── Save btn loading state ────────────────────────────────────
    function setSaving(loading) {
        saveBtn.disabled = loading;
        saveTxt.textContent = loading ? 'Đang lưu...' : 'Lưu thay đổi';
        const icon = saveBtn.querySelector('i');
        if (icon) {
            icon.className = loading
                ? 'fa-solid fa-spinner fa-spin'
                : 'fa-solid fa-floppy-disk';
        }
    }

    // ── Submit ────────────────────────────────────────────────────
    async function handleSubmit() {
        clearErrors();
        setSaving(true);

        const formData = new FormData(form);
        // Remove empty password fields to avoid validation trigger
        if (!formData.get('password')) {
            formData.delete('password');
            formData.delete('password_confirmation');
            formData.delete('current_password');
        }

        try {
            const res  = await fetch(UPDATE_URL, {
                method: 'PATCH',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
            });

            const data = await res.json();

            if (res.status === 422) {
                showErrors(data.errors ?? {});
                return;
            }

            if (!res.ok || !data.success) {
                showToast(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                return;
            }

            // Success
            bsModal?.hide();
            showToast(data.message ?? 'Cập nhật hồ sơ thành công.');

            // Update sidebar account name & email if present
            if (data.user?.username) {
                document.querySelectorAll('.account-name').forEach(el => {
                    el.textContent = data.user.username;
                });
                const initials = document.querySelectorAll('.account-avatar');
                initials.forEach(el => {
                    el.textContent = data.user.username.charAt(0).toUpperCase();
                });
            }

        } catch (e) {
            showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
        } finally {
            setSaving(false);
        }
    }

    // ── Password eye toggle ───────────────────────────────────────
    document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.pwToggle);
            if (!input) return;
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            const icon = this.querySelector('i');
            if (icon) icon.className = isText ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
        });
    });

    // ── Event listeners ───────────────────────────────────────────
    document.querySelectorAll('[data-profile-open]').forEach(el => {
        el.addEventListener('click', openProfile);
    });

    if (saveBtn) saveBtn.addEventListener('click', handleSubmit);

    if (modal) {
        modal.addEventListener('hidden.bs.modal', () => {
            clearErrors();
            setSaving(false);
        });
    }

    // Clear field error on user input
    FIELDS.forEach(key => {
        const input = document.getElementById('profile_' + key);
        if (!input) return;
        input.addEventListener('input', () => {
            input.classList.remove('is-invalid');
            const err = document.getElementById('err_' + key);
            if (err) { err.textContent = ''; err.classList.remove('show'); }
        });
    });

})();
</script>
