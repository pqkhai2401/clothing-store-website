@extends('layouts.admin')

@section('title', $pageTitle ?? 'Quản lý tài khoản')

@section('css')
    <style>
        .account-table thead th {
            background: #ffffff;
            color: #111827;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .account-table tbody td {
            color: #374151;
            font-size: 13px;
            height: 42px;
        }

        .account-table tbody tr:nth-child(odd) {
            background: #f3f3f3;
        }

        .page-action-btn {
            min-height: 32px;
            padding: 7px 14px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .page-action-btn.btn-primary {
            background: #174761;
            border-color: #174761;
        }

        .page-action-btn.btn-outline-secondary {
            color: #174761;
            border-color: #9fb3bf;
            background: #ffffff;
        }

        .status-badge {
            border-radius: 2px;
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
            padding: 4px 6px;
        }

        .account-modal .modal-content {
            border: 1px solid #d8dee6;
            border-radius: 4px;
            max-height: calc(100dvh - 3.5rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .account-modal .modal-content > form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .account-modal .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
        }

        .account-modal .modal-header {
            background: #ffffff;
            border-bottom: 1px solid #d8dee6;
            flex-shrink: 0;
        }

        .account-modal .modal-footer {
            flex-shrink: 0;
        }

        .account-modal .modal-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .account-modal .form-label {
            font-size: 13px;
            font-weight: 700;
        }

        .account-modal .form-control,
        .account-modal .form-select {
            border-radius: 3px;
            font-size: 14px;
        }

        .account-modal .is-invalid {
            border-color: #dc3545;
        }

        .account-modal-readonly {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            border: 1px solid #d8dee6;
            border-bottom: 0;
        }

        .account-modal-readonly:last-child {
            border-bottom: 1px solid #d8dee6;
        }

        .account-modal-readonly-label {
            padding: 9px 12px;
            background: #e9ecef;
            border-right: 1px solid #d8dee6;
            font-size: 13px;
            font-weight: 700;
        }

        .account-modal-readonly-value {
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 600;
            word-break: break-word;
        }

        .account-ajax-alert {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 2000;
            min-width: 280px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
        }

        .account-modal-loading {
            padding: 28px 12px;
            color: #6b7280;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    @php
        $showRoleColumn = ($type ?? 'all') !== 'customer';
        $showAddressFields = ($type ?? 'all') === 'staff';
        $emptyColspan = $showRoleColumn ? 7 : 6;
        $nameColumnLabel = ($type ?? 'all') === 'customer' ? 'Tên khách hàng' : 'Họ và tên';
        $breadcrumbLabel = ($type ?? 'all') === 'customer' ? 'Khách hàng' : 'Nhân sự';
        $roleLabels = [
            'admin' => 'Quản trị viên',
            'staff' => 'Nhân viên',
            'customer' => 'Khách hàng',
        ];
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <h1 class="h4 fw-bold mb-0 text-capitalize" style="color:#174761;">{{ $pageTitle ?? 'Quản lý tài khoản' }}</h1>
            </div>

            <div class="small text-muted">
                Trang chủ <span class="mx-1">/</span> {{ $breadcrumbLabel }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <a href="{{ route(($routePrefix ?? 'admin.users').'.create') }}" class="btn btn-primary page-action-btn">
                <i class="fa-solid fa-plus me-1"></i> {{ $createLabel ?? 'Thêm tài khoản' }}
            </a>
            <a href="{{ route(($routePrefix ?? 'admin.users').'.trash') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-trash me-1"></i> THÙNG RÁC
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route(($routePrefix ?? 'admin.users').'.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control"
                                    value="{{ request('keyword') }}"
                                    placeholder="Tìm theo họ tên, email hoặc số điện thoại">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered account-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th>{{ $nameColumnLabel }}</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                @if($showRoleColumn)
                                    <th>Vai trò</th>
                                @endif
                                <th>Trạng thái</th>
                                <th class="text-center pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $user)
                                @php
                                    $roleName = $user->role?->name;
                                @endphp
                                <tr data-user-row="{{ $user->id }}">
                                    <td class="ps-3">{{ $user->id }}</td>
                                    <td data-cell="username">
                                        <div class="fw-bold text-dark">{{ $user->username }}</div>
                                    </td>
                                    <td data-cell="email">
                                        <span class="fw-semibold">{{ $user->email }}</span>
                                    </td>
                                    <td data-cell="phone_number">
                                        {{ $user->phone_number ?: 'Chưa cập nhật' }}
                                    </td>
                                    @if($showRoleColumn)
                                        <td data-cell="role_name">
                                            {{ $roleName ? ($roleLabels[$roleName] ?? $roleName) : 'Chưa có vai trò' }}
                                        </td>
                                    @endif
                                    <td data-cell="status">
                                        <span class="badge status-badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $user->is_active ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-info js-view-user"
                                                title="Xem chi tiết"
                                                data-show-url="{{ route(($routePrefix ?? 'admin.users').'.show', $user->id) }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning js-edit-user"
                                                title="Sửa"
                                                data-show-url="{{ route(($routePrefix ?? 'admin.users').'.show', $user->id) }}"
                                                data-update-url="{{ route(($routePrefix ?? 'admin.users').'.update', $user->id) }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                title="Xóa"
                                                data-delete-url="{{ route(($routePrefix ?? 'admin.users').'.destroy', $user->id) }}"
                                                data-delete-name="{{ $user->username }}"
                                                data-delete-type="{{ $itemLabelLower ?? 'tài khoản' }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $emptyColspan }}" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px;"></i>
                                        <div class="fw-semibold text-muted">Chưa có {{ $itemLabelLower ?? 'tài khoản' }} phù hợp</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $data])
            </div>
        </div>

        <div id="accountAjaxAlert" class="alert alert-success account-ajax-alert d-none" role="alert"></div>

        <div class="modal fade account-modal" id="userDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Thông tin người dùng</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <div id="userDetailModalBody"></div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Quay lại</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade account-modal" id="userEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="userEditForm" method="POST" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h2 class="modal-title">Cập nhật người dùng</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="modal_username" class="form-label">Họ và tên</label>
                                    <input type="text" name="username" id="modal_username" class="form-control">
                                    <div class="invalid-feedback d-block" data-error-for="username"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_email" class="form-label">Email</label>
                                    <input type="email" name="email" id="modal_email" class="form-control">
                                    <div class="invalid-feedback d-block" data-error-for="email"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_phone_number" class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone_number" id="modal_phone_number" class="form-control">
                                    <div class="invalid-feedback d-block" data-error-for="phone_number"></div>
                                </div>

                                @if($showRoleColumn)
                                    <div class="col-md-6">
                                        <label for="modal_role_id" class="form-label">Vai trò</label>
                                        <select name="role_id" id="modal_role_id" class="form-select">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $roleLabels[$role->name] ?? $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback d-block" data-error-for="role_id"></div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label for="modal_is_active" class="form-label">Trạng thái</label>
                                    <select name="is_active" id="modal_is_active" class="form-select">
                                        <option value="1">Đang hoạt động</option>
                                        <option value="0">Đã khóa</option>
                                    </select>
                                    <div class="invalid-feedback d-block" data-error-for="is_active"></div>
                                </div>

                                <div class="col-md-6 d-none" data-modal-lock-reason-row>
                                    <label for="modal_lock_reason" class="form-label">Lý do khóa tài khoản</label>
                                    <input type="text" name="lock_reason" id="modal_lock_reason" class="form-control">
                                    <div class="invalid-feedback d-block" data-error-for="lock_reason"></div>
                                </div>

                                @if($showAddressFields)
                                    <div class="col-md-6">
                                        <label for="modal_city" class="form-label">Tỉnh, Thành phố</label>
                                        <input type="text" name="city" id="modal_city" class="form-control">
                                        <div class="invalid-feedback d-block" data-error-for="city"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="modal_district" class="form-label">Quận, Huyện</label>
                                        <input type="text" name="district" id="modal_district" class="form-control">
                                        <div class="invalid-feedback d-block" data-error-for="district"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="modal_ward" class="form-label">Phường, Xã</label>
                                        <input type="text" name="ward" id="modal_ward" class="form-control">
                                        <div class="invalid-feedback d-block" data-error-for="ward"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="modal_apartment_number" class="form-label">Số nhà</label>
                                        <input type="text" name="apartment_number" id="modal_apartment_number" class="form-control">
                                        <div class="invalid-feedback d-block" data-error-for="apartment_number"></div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label for="modal_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" name="password" id="modal_password" class="form-control" autocomplete="new-password">
                                    <div class="invalid-feedback d-block" data-error-for="password"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="modal_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="password_confirmation" id="modal_password_confirmation" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const showRoleColumn = @json($showRoleColumn);
            const showAddressFields = @json($showAddressFields);
            const detailModalEl = document.getElementById('userDetailModal');
            const editModalEl = document.getElementById('userEditModal');
            const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalEl);
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            const detailBody = document.getElementById('userDetailModalBody');
            const editForm = document.getElementById('userEditForm');
            const statusSelect = document.getElementById('modal_is_active');
            const lockReasonRow = document.querySelector('[data-modal-lock-reason-row]');
            const lockReasonInput = document.getElementById('modal_lock_reason');
            const alertBox = document.getElementById('accountAjaxAlert');

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

            function showAlert(message, type = 'success') {
                alertBox.className = `alert alert-${type} account-ajax-alert`;
                alertBox.textContent = message;

                window.clearTimeout(showAlert.timer);
                showAlert.timer = window.setTimeout(() => {
                    alertBox.classList.add('d-none');
                }, 2500);
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

            function readonlyRow(label, value) {
                return `
                    <div class="account-modal-readonly">
                        <div class="account-modal-readonly-label">${escapeHtml(label)}</div>
                        <div class="account-modal-readonly-value">${escapeHtml(text(value))}</div>
                    </div>
                `;
            }

            function renderDetail(user) {
                const rows = [
                    readonlyRow('ID', user.id),
                    readonlyRow('Họ và tên', user.username),
                    readonlyRow('Email', user.email),
                    readonlyRow('Số điện thoại', user.phone_number),
                ];

                if (showRoleColumn) {
                    rows.push(readonlyRow('Vai trò', roleLabel(user.role_name)));
                }

                rows.push(readonlyRow('Trạng thái', user.status_label));

                if (!user.is_active) {
                    rows.push(readonlyRow('Lý do khóa tài khoản', user.lock_reason || 'Chưa nhập lý do'));
                }

                if (showAddressFields) {
                    rows.push(readonlyRow('Tỉnh, Thành phố', user.city));
                    rows.push(readonlyRow('Quận, Huyện', user.district));
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
                setField('district', user.district);
                setField('ward', user.ward);
                setField('apartment_number', user.apartment_number);
                setField('password', '');
                setField('password_confirmation', '');
                fillRoleOptions(data.roles, user.role_id);
                syncLockReason();
            }

            function updateTableRow(user) {
                const row = document.querySelector(`[data-user-row="${user.id}"]`);

                if (!row) {
                    return;
                }

                row.querySelector('[data-cell="username"]').innerHTML = `<div class="fw-bold text-dark">${escapeHtml(user.username)}</div>`;
                row.querySelector('[data-cell="email"]').innerHTML = `<span class="fw-semibold">${escapeHtml(user.email)}</span>`;
                row.querySelector('[data-cell="phone_number"]').textContent = text(user.phone_number);

                if (showRoleColumn && row.querySelector('[data-cell="role_name"]')) {
                    row.querySelector('[data-cell="role_name"]').textContent = text(roleLabel(user.role_name), 'Chưa có vai trò');
                }

                const statusClass = user.is_active ? 'text-bg-success' : 'text-bg-secondary';
                row.querySelector('[data-cell="status"]').innerHTML = `
                    <span class="badge status-badge ${statusClass}">
                        ${escapeHtml(user.status_label)}
                    </span>
                `;

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
@endpush
