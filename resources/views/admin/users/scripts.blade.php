<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const showRoleColumn = @json($showRoleColumn);
        const showAddressFields = @json($showAddressFields);
        const isStaffPage = @json($isStaffPage);
        const currentUserId = @json($currentUserId ?? null);
        const currentUserIsProtectedAdmin = @json($currentUserIsProtectedAdmin ?? false);
        const detailModalEl = document.getElementById('userDetailModal');
        const editModalEl = document.getElementById('userEditModal');
        const resetPasswordModalEl = document.getElementById('userResetPasswordModal');
        const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalEl);
        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
        const resetPasswordModal = resetPasswordModalEl ? bootstrap.Modal.getOrCreateInstance(resetPasswordModalEl) : null;
        const detailBody = document.getElementById('userDetailModalBody');
        const editForm = document.getElementById('userEditForm');
        const resetPasswordForm = document.getElementById('userResetPasswordForm');
        const statusSelect = document.getElementById('modal_is_active');
        const lockReasonRow = document.querySelector('[data-modal-lock-reason-row]');
        const lockReasonInput = document.getElementById('modal_lock_reason');
        function text(value, fallback = 'Chưa cập nhật') {
            return value === null || value === undefined || value === '' ? fallback : value;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        const TOAST_SVG = {
            success: '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            error:   '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            warning: '<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            danger:  '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        };

        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const typeClass = type === 'danger' ? 'toast-error' : `toast-${type}`;
            const svgPath   = TOAST_SVG[type] ?? TOAST_SVG.success;

            const toast = document.createElement('div');
            toast.className = `custom-toast server-toast ${typeClass}`;
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-icon">
                        <svg style="flex-shrink:0;min-width:22px;min-height:22px;display:block;" viewBox="0 0 24 24" width="22" height="22">
                            ${svgPath}
                        </svg>
                    </div>
                    <div class="toast-message">${escapeHtml(message)}</div>
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

        function setButtonLoading(button, loading, loadingText = 'Đang tải...') {
            if (!button) {
                return;
            }

            if (loading) {
                button.dataset.originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = loadingText;
                return;
            }

            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml || button.innerHTML;
            delete button.dataset.originalHtml;
        }

        async function parseJsonResponse(response, fallbackMessage) {
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                return response.json();
            }

            throw new Error(fallbackMessage);
        }

        function fetchUser(url) {
            return fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Không thể tải dữ liệu người dùng.');
                }

                return parseJsonResponse(response, 'Không thể tải dữ liệu người dùng.');
            });
        }

        function roleLabel(value) {
            const labels = {
                admin: 'Quản trị viên',
                staff: 'Nhân viên',
                customer: 'Khách hàng',
            };

            return labels[value] || value;
        }

        function detailField(label, value, opts = {}) {
            const colClass = opts.full ? 'col-12' : 'col-md-6';
            return `
                <div class="${colClass}">
                    <label class="form-label">${escapeHtml(label)}</label>
                    <div class="account-detail-value">${escapeHtml(text(value))}</div>
                </div>
            `;
        }

        function renderDetail(user) {
            const fields = [
                detailField('Họ và tên', user.username),
                detailField('Email', user.email),
                detailField('Số điện thoại', user.phone_number),
            ];

            if (showRoleColumn) {
                fields.push(detailField('Vai trò', roleLabel(user.role_name)));
            }

            if (user.is_protected) {
                fields.push(detailField('Bảo vệ', 'Admin hệ thống'));
            }

            fields.push(detailField('Trạng thái', user.status_label));

            if (!user.is_active) {
                fields.push(detailField('Lý do ngưng hoạt động', user.lock_reason || 'Chưa nhập lý do'));
            }

            if (showAddressFields) {
                fields.push(detailField('Tỉnh, Thành phố', user.city));
                fields.push(detailField('Phường, Xã', user.ward));
                fields.push(detailField('Số nhà', user.apartment_number));
            }

            fields.push(detailField('Ngày tạo', user.created_at));
            fields.push(detailField('Cập nhật lần cuối', user.updated_at));

            detailBody.innerHTML = `<div class="row g-3">${fields.join('')}</div>`;
        }

        function setField(name, value) {
            const field = editForm.elements[name];
            if (field) {
                field.value = value ?? '';
            }
        }

        function resetErrors() {
            editForm.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
            editForm.querySelectorAll('[data-error-for]').forEach(error => {
                error.textContent = '';
            });
            editForm.querySelector('[data-permission-error-row]')?.classList.add('d-none');
        }

        function showErrors(errors) {
            resetErrors();
            let firstInvalidField = null;

            Object.entries(errors || {}).forEach(([name, messages]) => {
                const field = editForm.elements[name];
                const error = editForm.querySelector(`[data-error-for="${name}"]`);

                if (field) {
                    field.classList.add('is-invalid');
                    firstInvalidField = firstInvalidField || field;
                }

                if (error) {
                    const msg = Array.isArray(messages) ? messages[0] : messages;
                    error.textContent = msg;
                    // Show permission error row if hidden
                    const permRow = error.closest('[data-permission-error-row]');
                    if (permRow) permRow.classList.remove('d-none');
                }
            });

            firstInvalidField?.focus();
        }

        function validateEditForm() {
            const errors = {};
            const get = (name) => {
                const field = editForm.elements[name];
                return field ? String(field.value ?? '').trim() : '';
            };

            // Họ và tên: bắt buộc, tối đa 255 ký tự
            const username = get('username');
            if (username === '') {
                errors.username = 'Vui lòng nhập họ và tên';
            } else if (username.length > 255) {
                errors.username = 'Họ và tên không được vượt quá 255 ký tự';
            }

            // Email: bắt buộc, đúng định dạng, tối đa 255 ký tự
            const email = get('email');
            if (email === '') {
                errors.email = 'Vui lòng nhập email';
            } else if (email.length > 255) {
                errors.email = 'Email không được vượt quá 255 ký tự';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.email = 'Email không đúng định dạng';
            }

            // Số điện thoại: không bắt buộc, tối đa 10 ký tự
            const phone = get('phone_number');
            if (phone !== '') {
                if (phone.length > 10) {
                    errors.phone_number = 'Số điện thoại không được vượt quá 10 ký tự';
                } else if (!/^[0-9+\-\s().]+$/.test(phone)) {
                    errors.phone_number = 'Số điện thoại không hợp lệ';
                }
            }

            // Vai trò: bắt buộc khi trang có cột vai trò và select đang mở
            const roleSelect = editForm.elements['role_id'];
            if (showRoleColumn && roleSelect && !roleSelect.disabled && String(roleSelect.value ?? '').trim() === '') {
                errors.role_id = 'Vui lòng chọn vai trò';
            }

            // Lý do ngưng hoạt động: không bắt buộc, tối đa 255 ký tự
            if (get('lock_reason').length > 255) {
                errors.lock_reason = 'Lý do ngưng hoạt động không được vượt quá 255 ký tự';
            }

            // Địa chỉ: không bắt buộc, tối đa 255 ký tự
            [
                ['city', 'Tỉnh, thành phố không được vượt quá 255 ký tự'],
                ['ward', 'Phường, xã không được vượt quá 255 ký tự'],
                ['apartment_number', 'Số nhà không được vượt quá 255 ký tự'],
            ].forEach(function ([name, message]) {
                if (editForm.elements[name] && get(name).length > 255) {
                    errors[name] = message;
                }
            });

            return errors;
        }

        function syncLockReason() {
            const locked = statusSelect.value === '0';
            lockReasonRow?.classList.toggle('d-none', !locked);

            if (!locked) {
                setField('lock_reason', '');
            }

            if (lockReasonInput) {
                lockReasonInput.disabled = !locked;
            }
        }

        function fillRoleOptions(roles, selectedRoleId) {
            const roleSelect = editForm.elements.role_id;

            if (!roleSelect || !Array.isArray(roles)) {
                return;
            }

            roleSelect.innerHTML = roles.map(role => {
                const selected = String(role.id) === String(selectedRoleId) ? 'selected' : '';
                return `<option value="${escapeHtml(role.id)}" ${selected}>${escapeHtml(roleLabel(role.name))}</option>`;
            }).join('');
        }

        function fillEditForm(data, updateUrl) {
            const user = data.user;
            const isSelf              = currentUserId !== null && String(user.id) === String(currentUserId);
            const targetIsProtected   = Boolean(user.is_protected) && user.role_name === 'admin';
            const targetIsNormalAdmin = ! Boolean(user.is_protected) && user.role_name === 'admin';
            const targetIsAdmin       = user.role_name === 'admin';

            // Determine access restrictions
            // Normal admin editing a protected admin → block all writes
            const blockAll = ! currentUserIsProtectedAdmin && targetIsProtected;
            // Normal admin editing another normal admin → block sensitive (status/role/password)
            const blockSensitive = ! currentUserIsProtectedAdmin && targetIsNormalAdmin && ! isSelf;
            // Protected admin editing another protected admin → role is locked, deactivate is locked
            const targetProtectedNotSelf = targetIsProtected && ! isSelf;

            editForm.action = updateUrl;
            editForm.dataset.currentUserId = user.id;
            resetErrors();

            setField('username', user.username);
            setField('email', user.email);
            setField('phone_number', user.phone_number);
            setField('role_id', user.role_id);
            setField('is_active', user.is_active ? '1' : '0');
            setField('lock_reason', user.lock_reason);
            setField('city', user.city);
            setField('ward', user.ward);
            setField('apartment_number', user.apartment_number);
            fillRoleOptions(data.roles, user.role_id);
            syncLockReason();

            // --- Status field ---
            const statusSel = editForm.elements['is_active'];
            if (statusSel) {
                statusSel.disabled = false;
                for (const opt of statusSel.options) { opt.disabled = false; }

                if (blockAll || blockSensitive) {
                    statusSel.disabled = true;
                } else if (isSelf) {
                    // Cannot lock yourself
                    const lockOpt = statusSel.querySelector('option[value="0"]');
                    if (lockOpt) lockOpt.disabled = true;
                } else if (targetProtectedNotSelf) {
                    // Even protected admin cannot lock another protected admin
                    const lockOpt = statusSel.querySelector('option[value="0"]');
                    if (lockOpt) lockOpt.disabled = true;
                }
            }

            // --- Role select ---
            const roleSelect     = editForm.elements['role_id'];
            const roleLockedNote = editForm.querySelector('[data-role-locked-note]');
            const roleField      = editForm.querySelector('[data-role-field]');
            if (roleSelect) {
                roleSelect.disabled = false;
                roleLockedNote?.classList.add('d-none');
                roleField?.classList.remove('role-field--locked');

                let lockRoleNote = '';
                if (isSelf) {
                    lockRoleNote = 'Không thể thay đổi vai trò của tài khoản đang đăng nhập';
                } else if (targetIsProtected) {
                    lockRoleNote = 'Không thể thay đổi vai trò của admin hệ thống';
                } else if (blockSensitive) {
                    lockRoleNote = 'Quản trị viên thường không có quyền đổi vai trò của quản trị viên khác';
                }

                if (lockRoleNote) {
                    roleSelect.disabled = true;
                    if (roleLockedNote) {
                        roleLockedNote.innerHTML = `<i class="fa-solid fa-lock" style="font-size:11px;color:#9ca3af;"></i> ${lockRoleNote}`;
                    }
                    roleLockedNote?.classList.remove('d-none');
                    roleField?.classList.add('role-field--locked');
                }
            }

            // --- is_protected checkbox (only rendered for protected admin) ---
            const protectedRow      = editForm.querySelector('[data-protected-row]');
            const protectedCheckbox = document.getElementById('modal_is_protected');
            const protectedHidden   = protectedRow?.querySelector('[data-protected-hidden]');
            if (protectedRow) {
                // Show when editing admin target (protected or normal), not self
                const showRow = currentUserIsProtectedAdmin && targetIsAdmin && ! isSelf;
                protectedRow.classList.toggle('d-none', ! showRow);
                const rowInputs = protectedRow.querySelectorAll('input');
                rowInputs.forEach(el => { el.disabled = ! showRow; });
                if (showRow && protectedCheckbox) {
                    protectedCheckbox.checked  = Boolean(user.is_protected);
                    protectedCheckbox.disabled = false;
                    if (protectedHidden) protectedHidden.disabled = false;
                }
            }

            // --- Permission error row ---
            const permRow = editForm.querySelector('[data-permission-error-row]');
            if (permRow) {
                permRow.classList.add('d-none');
                const permErr = permRow.querySelector('[data-error-for="permission"]');
                if (permErr) permErr.textContent = '';
            }

            // --- Info-only hint when blocked ---
            if (blockAll) {
                const hint = editForm.querySelector('[data-permission-error-row]');
                if (hint) {
                    hint.classList.remove('d-none');
                    const permErr = hint.querySelector('[data-error-for="permission"]');
                    if (permErr) permErr.textContent = 'Admin hệ thống được bảo vệ — bạn chỉ có thể chỉnh sửa thông tin cơ bản (tên, email, SĐT).';
                }
            }
        }

        function updateTableRow(user) {
            const row = document.querySelector(`[data-user-row="${user.id}"]`);
            if (!row) return;

            row.dataset.userStatus = user.is_active ? '1' : '0';
            row.dataset.protected = user.is_protected ? '1' : '0';

            const usernameCell = row.querySelector('[data-cell="username"]');
            if (usernameCell) {
                usernameCell.dataset.sortValue = user.username;
                const protectedChip = isStaffPage && user.is_protected
                    ? '<span class="system-admin-chip">Admin hệ thống</span>'
                    : '';
                usernameCell.innerHTML = `<div class="fw-bold text-dark d-flex align-items-center gap-2 flex-wrap"><span>${escapeHtml(user.username)}</span>${protectedChip}</div>`;
            }

            const emailCell = row.querySelector('[data-cell="email"]');
            if (emailCell) {
                emailCell.dataset.sortValue = user.email;
                emailCell.innerHTML = `<span class="fw-semibold">${escapeHtml(user.email)}</span>`;
            }

            const phoneCell = row.querySelector('[data-cell="phone_number"]');
            if (phoneCell) {
                phoneCell.dataset.sortValue = user.phone_number ?? '';
                phoneCell.textContent = user.phone_number || 'Chưa cập nhật';
            }

            if (showRoleColumn) {
                const roleCell = row.querySelector('[data-cell="role_name"]');
                if (roleCell) {
                    const rLabel = roleLabel(user.role_name);
                    roleCell.dataset.sortValue = rLabel;
                    roleCell.innerHTML = `<span class="role-badge" data-role="${escapeHtml(user.role_name ?? '')}">${escapeHtml(rLabel)}</span>`;
                }
            }

            const statusCell = row.querySelector('[data-cell="status"]');
            if (statusCell) {
                const isActive = Boolean(user.is_active);
                const statusClass = isActive ? 'status-badge--active' : 'status-badge--inactive';
                const statusText  = isActive ? 'Hoạt động' : 'Ngưng hoạt động';
                statusCell.dataset.sortValue = isActive ? '1' : '0';
                statusCell.innerHTML = `<span class="status-badge ${statusClass}">${statusText}</span>`;
            }

            row.querySelector('[data-delete-name]')?.setAttribute('data-delete-name', user.username);
        }

        document.addEventListener('click', async function (event) {
            const viewButton = event.target.closest('.js-view-user');
            if (!viewButton) return;

            event.preventDefault();
            setButtonLoading(viewButton, true);
                detailBody.innerHTML = '<div class="account-modal-loading">Đang tải thông tin người dùng...</div>';
                detailModal.show();

                try {
                const data = await fetchUser(viewButton.dataset.showUrl);
                    renderDetail(data.user);
                } catch (error) {
                    detailModal.hide();
                    showAlert(error.message, 'danger');
                } finally {
                setButtonLoading(viewButton, false);
                }
        });

        document.addEventListener('click', async function (event) {
            const editButton = event.target.closest('.js-edit-user');
            if (!editButton) return;

            event.preventDefault();
            setButtonLoading(editButton, true);

                try {
                const data = await fetchUser(editButton.dataset.showUrl);
                fillEditForm(data, editButton.dataset.updateUrl);
                    editModal.show();
                } catch (error) {
                    showAlert(error.message, 'danger');
                } finally {
                setButtonLoading(editButton, false);
                }
        });

        statusSelect.addEventListener('change', syncLockReason);

        detailModalEl.addEventListener('hidden.bs.modal', function () {
            detailBody.innerHTML = '';
        });

        editModalEl.addEventListener('hidden.bs.modal', function () {
            resetErrors();
            editForm.reset();
            syncLockReason();
        });

        editForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            resetErrors();

            // Kiểm tra hợp lệ phía client trước khi gửi để lưu vào DB
            const clientErrors = validateEditForm();
            if (Object.keys(clientErrors).length > 0) {
                showErrors(clientErrors);
                showAlert('Vui lòng kiểm tra lại thông tin đã nhập', 'warning');
                return;
            }

            const submitButton = editForm.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = 'Đang lưu...';

            // Build formData and re-include disabled fields so required validation passes
            const formData = new FormData(editForm);
            editForm.querySelectorAll('select[disabled], input[disabled][name]').forEach(function (el) {
                if (el.name && ! formData.has(el.name)) {
                    formData.set(el.name, el.value ?? '');
                }
            });

            try {
                const response = await fetch(editForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                const data = await parseJsonResponse(response, 'Không thể cập nhật người dùng.');

                if (response.status === 422) {
                    showErrors(data.errors || {});
                    return;
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể cập nhật người dùng.');
                }

                updateTableRow(data.user);
                editModal.hide();
                showAlert(data.message || 'Cập nhật người dùng thành công');
            } catch (error) {
                showAlert(error.message, 'danger');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });

        if (resetPasswordForm && resetPasswordModal) {
            function resetPasswordErrors() {
                resetPasswordForm.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
                resetPasswordForm.querySelectorAll('[data-error-for]').forEach(error => { error.textContent = ''; });
                resetPasswordForm.querySelector('[data-reset-permission-error-row]')?.classList.add('d-none');
            }

            function showResetPasswordErrors(errors) {
                resetPasswordErrors();
                let firstInvalidField = null;

                Object.entries(errors || {}).forEach(([name, messages]) => {
                    const field = resetPasswordForm.elements[name];
                    const error = resetPasswordForm.querySelector(`[data-error-for="${name}"]`);

                    if (field) {
                        field.classList.add('is-invalid');
                        firstInvalidField = firstInvalidField || field;
                    }

                    if (error) {
                        const msg = Array.isArray(messages) ? messages[0] : messages;
                        error.textContent = msg;
                        const permRow = error.closest('[data-reset-permission-error-row]');
                        if (permRow) permRow.classList.remove('d-none');
                    }
                });

                firstInvalidField?.focus();
            }

            document.addEventListener('click', function (event) {
                const resetButton = event.target.closest('.js-reset-password');
                if (!resetButton) return;

                event.preventDefault();
                resetPasswordErrors();
                resetPasswordForm.reset();
                resetPasswordForm.action = resetButton.dataset.resetUrl;
                const usernameEl = resetPasswordForm.querySelector('[data-reset-username]');
                if (usernameEl) usernameEl.textContent = resetButton.dataset.username || '';
                resetPasswordModal.show();
            });

            resetPasswordModalEl.addEventListener('hidden.bs.modal', function () {
                resetPasswordErrors();
                resetPasswordForm.reset();
            });

            resetPasswordForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                resetPasswordErrors();

                const password = resetPasswordForm.elements['password']?.value ?? '';
                const confirmation = resetPasswordForm.elements['password_confirmation']?.value ?? '';
                const clientErrors = {};
                if (password.length < 8) {
                    clientErrors.password = 'Mật khẩu phải có ít nhất 8 ký tự';
                } else if (password !== confirmation) {
                    clientErrors.password = 'Mật khẩu xác nhận không khớp';
                }

                if (Object.keys(clientErrors).length > 0) {
                    showResetPasswordErrors(clientErrors);
                    return;
                }

                const submitButton = resetPasswordForm.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = 'Đang lưu...';

                try {
                    const response = await fetch(resetPasswordForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: new FormData(resetPasswordForm),
                    });

                    const data = await parseJsonResponse(response, 'Không thể đặt lại mật khẩu.');

                    if (response.status === 422) {
                        showResetPasswordErrors(data.errors || {});
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(data.message || 'Không thể đặt lại mật khẩu.');
                    }

                    resetPasswordModal.hide();
                    showAlert(data.message || 'Đặt lại mật khẩu thành công');
                } catch (error) {
                    showAlert(error.message, 'danger');
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            });
        }
    });
</script>
