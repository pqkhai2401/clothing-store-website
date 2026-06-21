@php
    $currentUser = auth()->user();
    $displayName = $currentUser?->name ?: 'Admin';
    $displayEmail = $currentUser?->email ?: 'admin@example.com';
    $userInitial = \Illuminate\Support\Str::of($displayName)->trim()->substr(0, 1)->upper();
    $roleLabel = $currentUser?->role?->name === 'admin' ? 'Quản trị viên' : 'Nhân viên';
    $profileUrl = $currentUser ? route('admin.users.edit', $currentUser->id) : '#';
@endphp

<aside class="app-sidebar admin-sidebar">
    <div class="sidebar-brand admin-sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link admin-brand-link">
            <span class="admin-brand-mark">
                {{ $userInitial }}
            </span>
            <span class="brand-text admin-brand-text">
                <strong>HK Store</strong>
                <small>{{ $roleLabel }}</small>
            </span>
        </a>
    </div>

    <div class="sidebar-wrapper admin-sidebar-nav">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                @if(isset($menu) && is_array($menu))
                    @foreach($menu as $index => $item)
                        @php
                            $hasChildren = isset($item['parent']) && count($item['parent']) > 0;
                            $isActiveParent = false;

                            if ($hasChildren) {
                                foreach ($item['parent'] as $child) {
                                    $pattern = $child['active_pattern'] ?? '';
                                    if ($pattern && request()->is($pattern)) {
                                        $isActiveParent = true;
                                        break;
                                    }
                                }
                            } else {
                                $pattern = $item['active_pattern'] ?? '';
                                if ($pattern && request()->is($pattern)) {
                                    $isActiveParent = true;
                                }
                            }
                        @endphp

                        <li class="nav-item">
                            @if($hasChildren)
                                <div class="nav-link d-flex align-items-center justify-content-between p-0 parent-menu-item {{ $isActiveParent ? 'active' : '' }}">
                                    <a href="{{ $item['url'] === '#' ? 'javascript:void(0)' : url($item['url']) }}"
                                        class="nav-link-title flex-grow-1">
                                        <i class="nav-icon {{ $item['icon'] ?? 'fa-solid fa-circle' }}"></i>
                                        <p class="mb-0">{{ $item['title'] }}</p>
                                    </a>

                                    <button type="button" class="dropdown-toggle-btn" aria-label="Mở menu con">
                                        <i class="nav-arrow fa-solid fa-chevron-right {{ $isActiveParent ? 'rotate-90' : '' }}"></i>
                                    </button>
                                </div>

                                <ul class="nav nav-treeview submenu {{ $isActiveParent ? 'show' : '' }}" id="submenu-{{ $index }}">
                                    @foreach($item['parent'] as $subItem)
                                        @php
                                            $childPattern = $subItem['active_pattern'] ?? '';
                                            $isActiveChild = ($childPattern && request()->is($childPattern));
                                        @endphp
                                        <li class="nav-item">
                                            <a href="{{ url($subItem['url']) }}" class="nav-link {{ $isActiveChild ? 'active' : '' }}">
                                                <i class="nav-icon {{ $subItem['icon'] ?? 'fa-regular fa-circle' }}"></i>
                                                <p>{{ $subItem['title'] }}</p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <a href="{{ url($item['url']) }}" class="nav-link {{ $isActiveParent ? 'active' : '' }}">
                                    <i class="nav-icon {{ $item['icon'] ?? 'fa-solid fa-circle' }}"></i>
                                    <p>{{ $item['title'] }}</p>
                                </a>
                            @endif
                        </li>
                    @endforeach
                @endif
            </ul>
        </nav>
    </div>

    <div class="sidebar-account dropup">
        <button class="sidebar-account-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static"
            aria-expanded="false">
            <span class="account-avatar">{{ $userInitial }}</span>
            <span class="account-summary">
                <span class="account-name">{{ $displayName }}</span>
                <span class="account-email">{{ $displayEmail }}</span>
            </span>
            <i class="account-caret fa-solid fa-chevron-up"></i>
        </button>

        <div class="dropdown-menu sidebar-account-menu">
            <div class="account-menu-header">
                <span class="account-avatar account-avatar-lg">{{ $userInitial }}</span>
                <span class="account-summary">
                    <span class="account-name">{{ $displayName }}</span>
                    <span class="account-email">{{ $displayEmail }}</span>
                </span>
            </div>

            <div class="account-menu-section">
                <a class="account-menu-item" href="{{ $profileUrl }}">
                    <i class="fa-regular fa-circle-user"></i>
                    <span>Hồ sơ</span>
                </a>
                <button class="account-menu-item" type="button" data-admin-theme-toggle aria-pressed="false">
                    <i class="fa-regular fa-moon"></i>
                    <span>Giao diện tối</span>
                </button>
                <button class="account-menu-item" type="button">
                    <i class="fa-solid fa-gear"></i>
                    <span>Cài đặt</span>
                </button>
            </div>

            <div class="account-menu-section account-menu-section-last">
                <a class="account-menu-item account-menu-logout" href="{{ route('auth.logout') }}">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </div>
    </div>
</aside>

<style>
    .admin-sidebar {
        overflow: visible;
        display: flex;
        flex-direction: column;
        background: #ffffff !important;
        border-right: 1px solid #e5e7eb;
        color: #111827;
        box-shadow: none !important;
    }

    .admin-sidebar-brand {
        flex: 0 0 auto;
        height: 68px;
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    .admin-brand-link {
        display: flex !important;
        align-items: center;
        gap: 10px;
        width: 100%;
        color: #111827 !important;
        text-decoration: none;
    }

    .admin-brand-mark,
    .account-avatar {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #0891a3;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
    }

    .admin-brand-text,
    .account-summary {
        min-width: 0;
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .admin-brand-text strong,
    .account-name {
        overflow: hidden;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-brand-text small,
    .account-email {
        overflow: hidden;
        color: #4b5563;
        font-size: 11px;
        font-weight: 500;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-sidebar-nav {
        flex: 1 1 auto;
        height: auto !important;
        min-height: 0;
        padding: 10px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .admin-sidebar .nav-item {
        margin-bottom: 3px;
    }

    .admin-sidebar .nav-item .nav-link,
    .admin-sidebar .nav-link-title {
        display: flex !important;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        padding: 9px 10px;
        border-radius: 8px;
        color: #111827 !important;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        line-height: 1.3;
    }

    .admin-sidebar .nav-link p,
    .admin-sidebar .nav-link-title p {
        min-width: 0;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-sidebar .nav-icon {
        width: 18px;
        min-width: 18px;
        color: #111827;
        font-size: 14px;
        text-align: center;
    }

    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link-title:hover,
    .admin-sidebar .parent-menu-item.active,
    .admin-sidebar .nav-link.active {
        background: #f3f4f6 !important;
        color: #030712 !important;
    }

    .admin-sidebar .parent-menu-item {
        height: auto !important;
        border-radius: 8px;
        overflow: hidden;
    }

    .admin-sidebar .dropdown-toggle-btn {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        min-width: 34px;
        min-height: 38px;
        padding: 0;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #111827;
    }

    .admin-sidebar .dropdown-toggle-btn:hover {
        background: #e5e7eb;
    }

    .admin-sidebar .nav-arrow {
        font-size: 11px;
        transition: transform 0.2s ease;
    }

    .admin-sidebar .nav-arrow.rotate-90 {
        transform: rotate(90deg);
    }

    .admin-sidebar .submenu {
        overflow: hidden;
        transition: max-height 0.25s ease;
    }

    .admin-sidebar .submenu:not(.show) {
        max-height: 0;
    }

    .admin-sidebar .submenu.show {
        max-height: 320px;
    }

    .admin-sidebar .nav-treeview {
        padding: 3px 0 4px 18px;
    }

    .admin-sidebar .nav-treeview .nav-link {
        min-height: 34px;
        padding: 8px 10px;
        color: #374151 !important;
        font-size: 12px;
    }

    .sidebar-account {
        position: relative;
        flex: 0 0 auto;
        padding: 10px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        z-index: 10;
    }

    .sidebar-account-toggle {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) 16px;
        align-items: center;
        gap: 8px;
        width: 100%;
        min-height: 48px;
        padding: 8px;
        border: 1px solid transparent;
        border-radius: 8px;
        background: #f9fafb;
        color: #111827;
        text-align: left;
    }

    .sidebar-account-toggle:hover,
    .sidebar-account-toggle[aria-expanded="true"] {
        border-color: #e5e7eb;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .account-caret {
        color: #111827;
        font-size: 11px;
        transition: transform 0.2s ease;
    }

    .sidebar-account-toggle[aria-expanded="true"] .account-caret {
        transform: rotate(180deg);
    }

    .sidebar-account-menu {
        width: 224px;
        padding: 0;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.14);
        overflow: hidden;
    }

    .account-menu-header {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 8px;
        align-items: center;
        padding: 10px;
    }

    .account-avatar-lg {
        width: 34px;
        height: 34px;
    }

    .account-menu-section {
        padding: 6px 0;
        border-top: 1px solid #e5e7eb;
    }

    .account-menu-section-last {
        padding: 6px 0;
    }

    .account-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 34px;
        padding: 8px 12px;
        border: 0;
        background: transparent;
        color: #111827;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.2;
        text-align: left;
        text-decoration: none;
    }

    .account-menu-item i {
        width: 16px;
        min-width: 16px;
        color: #6b7280;
        font-size: 14px;
        text-align: center;
    }

    .account-menu-item:hover,
    .account-menu-item:focus {
        background: #f3f4f6;
        color: #030712;
        text-decoration: none;
    }

    .account-menu-logout {
        color: #111827;
    }

    .admin-dark-mode {
        background: #111827;
        color: #e5e7eb;
    }

    .admin-dark-mode .admin-sidebar,
    .admin-dark-mode .sidebar-account,
    .admin-dark-mode .sidebar-account-menu {
        background: #111827 !important;
        border-color: #374151;
    }

    .admin-dark-mode .admin-sidebar .nav-link,
    .admin-dark-mode .admin-sidebar .nav-link-title,
    .admin-dark-mode .admin-sidebar .nav-icon,
    .admin-dark-mode .admin-sidebar .dropdown-toggle-btn,
    .admin-dark-mode .admin-brand-text strong,
    .admin-dark-mode .account-name,
    .admin-dark-mode .account-menu-item,
    .admin-dark-mode .account-caret {
        color: #f9fafb !important;
    }

    .admin-dark-mode .admin-brand-text small,
    .admin-dark-mode .account-email {
        color: #cbd5e1;
    }

    .admin-dark-mode .sidebar-account-toggle,
    .admin-dark-mode .admin-sidebar .nav-link:hover,
    .admin-dark-mode .admin-sidebar .nav-link-title:hover,
    .admin-dark-mode .admin-sidebar .nav-link.active,
    .admin-dark-mode .admin-sidebar .parent-menu-item.active,
    .admin-dark-mode .account-menu-item:hover,
    .admin-dark-mode .account-menu-item:focus {
        background: #1f2937 !important;
        border-color: #374151;
    }

    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(17, 24, 39, 0.12);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }

    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    @media (max-width: 991.98px) {
        .app-sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh !important;
            overflow: visible !important;
            z-index: 1050 !important;
        }
    }
</style>

@push('scripts')
    <script src="{{ asset('assets/js/managers/sidebarAccountant.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.querySelector('[data-admin-theme-toggle]');
            const storageKey = 'hk-admin-theme';

            function applyTheme(theme) {
                const isDark = theme === 'dark';
                document.body.classList.toggle('admin-dark-mode', isDark);

                if (toggle) {
                    toggle.setAttribute('aria-pressed', String(isDark));
                    const icon = toggle.querySelector('i');
                    if (icon) {
                        icon.className = isDark ? 'fa-regular fa-sun' : 'fa-regular fa-moon';
                    }
                }
            }

            applyTheme(localStorage.getItem(storageKey) || 'light');

            if (toggle) {
                toggle.addEventListener('click', function () {
                    const nextTheme = document.body.classList.contains('admin-dark-mode') ? 'light' : 'dark';
                    localStorage.setItem(storageKey, nextTheme);
                    applyTheme(nextTheme);
                });
            }
        });
    </script>
@endpush
