@php
    $showRoleColumn = ($type ?? 'all') !== 'customer';
    $roleLabels = [
        'admin' => 'Quản trị viên',
        'staff' => 'Nhân viên',
        'customer' => 'Khách hàng',
    ];
    $currentUserId = auth()->id();
    $currentUser = auth()->user();
    // Admin thường (không bảo vệ) không được đặt lại mật khẩu cho quản trị viên khác
    // — chỉ admin hệ thống (bảo vệ) mới có quyền đó. Tính sẵn để ẩn/hiện nút reset.
    $currentUserIsNormalAdmin = $currentUser?->isAdmin() && ! (bool) $currentUser?->is_protected;
    $isStaffPage = ($type ?? 'all') === 'staff';
    $isCustomerPage = ($type ?? 'all') === 'customer';
    $showBulkCheckbox = $isCustomerPage;
    $emptyColspan = ($showRoleColumn ? 7 : 6) + ($showBulkCheckbox ? 1 : 0);
    $nameColumnLabel = $isCustomerPage ? 'Tên khách hàng' : 'Họ và tên';
@endphp

            <div class="account-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover account-table align-middle" id="accountUsersTable">
                        <thead>
                            <tr>
                                @if($showBulkCheckbox)
                                    <th style="width: 58px;">
                                        <input type="checkbox" class="form-check-input account-check hk-cb-all" id="accountCheckAll">
                                    </th>
                                @endif
                                <th style="width: 86px;">
                                    <button type="button" class="account-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="account-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="username">
                                        {{ $nameColumnLabel }} <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="email">
                                        Email <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="phone_number">
                                        Số điện thoại <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                @if($showRoleColumn)
                                    <th>
                                        <button type="button" class="account-sort-btn" data-sort-key="role_name">
                                            Vai trò <span class="account-sort-icon">↑↓</span>
                                        </button>
                                    </th>
                                @endif
                                <th>
                                    <button type="button" class="account-sort-btn" data-sort-key="status">
                                        Trạng thái <span class="account-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th class="text-end pe-4" style="width: 96px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $user)
                                @php
                                    $roleName = $user->roles->first()?->name;
                                    $roleLabel = $roleName ? ($roleLabels[$roleName] ?? $roleName) : 'Chưa có vai trò';
                                @endphp
                                <tr data-user-row="{{ $user->id }}" data-user-status="{{ $user->is_active ? '1' : '0' }}" data-protected="{{ $user->is_protected ? '1' : '0' }}">
                                    @if($showBulkCheckbox)
                                        <td>
                                            <input type="checkbox" class="form-check-input account-check account-row-check hk-cb-row" value="{{ $user->id }}">
                                        </td>
                                    @endif
                                    <td data-cell="id" data-sort-value="{{ $user->id }}">{{ $user->id }}</td>
                                    <td data-cell="username" data-sort-value="{{ $user->username }}">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <img src="{{ $user->avatar_display_url }}" alt="{{ $user->username }}"
                                                class="account-row-avatar" loading="lazy"
                                                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=random&color=fff';">
                                            <span class="fw-bold text-dark">{{ $user->username }}</span>
                                            @if($isStaffPage && $user->is_protected)
                                                <span class="system-admin-chip">Admin hệ thống</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-cell="email" data-sort-value="{{ $user->email }}">
                                        <span class="fw-semibold">{{ $user->email }}</span>
                                    </td>
                                    <td data-cell="phone_number" data-sort-value="{{ $user->phone_number }}">
                                        {{ $user->phone_number ?: 'Chưa cập nhật' }}
                                    </td>
                                    @if($showRoleColumn)
                                        <td data-cell="role_name" data-sort-value="{{ $roleLabel }}">
                                            <span class="role-badge" data-role="{{ $roleName ?? '' }}">{{ $roleLabel }}</span>
                                        </td>
                                    @endif
                                    <td data-cell="status" data-sort-value="{{ $user->is_active ? 1 : 0 }}">
                                        <span class="status-badge {{ $user->is_active ? 'status-badge--active' : 'status-badge--inactive' }}">
                                            {{ $user->is_active ? 'Hoạt động' : 'Ngưng hoạt động' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="account-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end account-row-menu">
                                                <button type="button" class="dropdown-item js-view-user"
                                                    data-show-url="{{ route(($routePrefix ?? 'admin.users') . '.show', $user->id) }}">
                                                    <i class="fa-regular fa-eye"></i> Xem
                                                </button>
                                                <a class="dropdown-item" href="{{ route(($routePrefix ?? 'admin.users') . '.edit', $user->id) }}">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                @if ($user->id !== $currentUserId && ! $user->is_protected
                                                    && ! ($currentUserIsNormalAdmin && $roleName === 'admin'))
                                                    <button type="button" class="dropdown-item js-reset-password"
                                                        data-reset-url="{{ route(($routePrefix ?? 'admin.users') . '.resetPassword', $user->id) }}"
                                                        data-username="{{ $user->username }}">
                                                        <i class="fa-solid fa-key"></i> Đặt lại mật khẩu
                                                    </button>
                                                @endif
                                                @if (!$isStaffPage && $user->id !== $currentUserId && ! $user->is_protected)
                                                    <button type="button" class="dropdown-item text-danger"
                                                        data-delete-url="{{ route(($routePrefix ?? 'admin.users') . '.destroy', $user->id) }}"
                                                        data-delete-name="{{ $user->username }}"
                                                        data-delete-type="{{ $itemLabelLower ?? 'tài khoản' }}">
                                                        <i class="fa-regular fa-trash-can"></i> Xóa
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
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

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $data,
                    'itemLabel'     => $itemLabelLower ?? 'tài khoản',
                    'bulkDeleteUrl' => null,
                    'bulkStatusUrl' => $isCustomerPage ? route('admin.customers.bulkUpdateStatus') : null,
                ])
            </div>
