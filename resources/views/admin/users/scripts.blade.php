<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const showRoleColumn = @json($showRoleColumn);
        const showAddressFields = @json($showAddressFields);
        const isStaffPage = @json($isStaffPage);
        const currentUserId = @json($currentUserId ?? null);
        const detailModalEl = document.getElementById('userDetailModal');
        const editModalEl = document.getElementById('userEditModal');
        const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalEl);
        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
        const detailBody = document.getElementById('userDetailModalBody');
        const editForm = document.getElementById('userEditForm');
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

        function readonlyRow(label, value, muted = false) {
            const style = muted ? ' style="opacity:0.45"' : '';
            return `
                <div class="account-modal-readonly"${style}>
                    <div class="account-modal-readonly-label">${escapeHtml(label)}</div>
                    <div class="account-modal-readonly-value">${escapeHtml(text(value))}</div>
                </div>
            `;
        }

        function renderDetail(user) {
            const rows = [
                readonlyRow('ID', user.id, true),
                readonlyRow('Họ và tên', user.username),
                readonlyRow('Email', user.email),
                readonlyRow('Số điện thoại', user.phone_number),
            ];

            if (showRoleColumn) {
                rows.push(readonlyRow('Vai trò', roleLabel(user.role_name)));
            }

            if (user.is_protected) {
                rows.push(readonlyRow('Bảo vệ', 'Admin hệ thống'));
            }

            rows.push(readonlyRow('Trạng thái', user.status_label));

            if (!user.is_active) {
                rows.push(readonlyRow('Lý do ngưng hoạt động', user.lock_reason || 'Chưa nhập lý do'));
            }

            if (showAddressFields) {
                rows.push(readonlyRow('Tỉnh, Thành phố', user.city));
                rows.push(readonlyRow('Phường, Xã', user.ward));
                rows.push(readonlyRow('Số nhà', user.apartment_number));
            }

            rows.push(readonlyRow('Ngày tạo', user.created_at));
            rows.push(readonlyRow('Cập nhật lần cuối', user.updated_at));

            detailBody.innerHTML = rows.join('');
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
                    error.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            });

            firstInvalidField?.focus();
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
            const isProtectedByOther = Boolean(user.is_protected) && currentUserId !== null && String(user.id) !== String(currentUserId);

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
            setField('password', '');
            setField('password_confirmation', '');
            fillRoleOptions(data.roles, user.role_id);
            syncLockReason();

            if (isStaffPage) {
                const statusSelect = editForm.elements['is_active'];
                if (statusSelect) {
                    for (const opt of statusSelect.options) {
                        opt.disabled = (opt.value === '1' && !user.is_active);
                    }
                }
            }

            const roleSelect     = editForm.elements['role_id'];
            const roleLockedNote = editForm.querySelector('[data-role-locked-note]');
            const roleField      = editForm.querySelector('[data-role-field]');
            if (roleSelect) {
                roleSelect.disabled = false;
                roleLockedNote?.classList.add('d-none');
                roleField?.classList.remove('role-field--locked');
                if (isStaffPage && currentUserId !== null && String(user.id) === String(currentUserId)) {
                    roleSelect.disabled = true;
                    if (roleLockedNote) {
                        roleLockedNote.innerHTML = '<i class="fa-solid fa-lock" style="font-size:11px;color:#9ca3af;"></i> Không thể thay đổi vai trò của tài khoản đang đăng nhập';
                    }
                    roleLockedNote?.classList.remove('d-none');
                    roleField?.classList.add('role-field--locked');
                }

                if (isProtectedByOther) {
                    roleSelect.disabled = true;
                    if (roleLockedNote) {
                        roleLockedNote.innerHTML = '<i class="fa-solid fa-lock" style="font-size:11px;color:#9ca3af;"></i> Admin hệ thống được bảo vệ, không thể đổi vai trò';
                    }
                    roleLockedNote?.classList.remove('d-none');
                    roleField?.classList.add('role-field--locked');
                }
            }

            ['password', 'password_confirmation'].forEach(function (fieldName) {
                const field = editForm.elements[fieldName];
                if (field) {
                    field.disabled = isProtectedByOther;
                }
            });
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

        document.querySelectorAll('.js-view-user').forEach(button => {
            button.addEventListener('click', async function () {
                setButtonLoading(this, true);
                detailBody.innerHTML = '<div class="account-modal-loading">Đang tải thông tin người dùng...</div>';
                detailModal.show();

                try {
                    const data = await fetchUser(this.dataset.showUrl);
                    renderDetail(data.user);
                } catch (error) {
                    detailModal.hide();
                    showAlert(error.message, 'danger');
                } finally {
                    setButtonLoading(this, false);
                }
            });
        });

        document.querySelectorAll('.js-edit-user').forEach(button => {
            button.addEventListener('click', async function () {
                setButtonLoading(this, true);

                try {
                    const data = await fetchUser(this.dataset.showUrl);
                    fillEditForm(data, this.dataset.updateUrl);
                    editModal.show();
                } catch (error) {
                    showAlert(error.message, 'danger');
                } finally {
                    setButtonLoading(this, false);
                }
            });
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

            const submitButton = editForm.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = 'Đang lưu...';

            try {
                const response = await fetch(editForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(editForm),
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
    });
</script>
