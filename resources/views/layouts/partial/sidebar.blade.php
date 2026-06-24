@php
    $currentUser = auth()->user();
    $accountName = $currentUser?->username ?: 'admin';
    $displayEmail = $currentUser?->email ?: 'admin@example.com';
    $userInitial = \Illuminate\Support\Str::of($accountName)->trim()->substr(0, 1)->upper();
    $roleLabel = $currentUser?->can('manage-staff') ? 'Quản trị viên' : 'Nhân viên';
    $profileUrl = $currentUser?->can('manage-staff') ? route('admin.staff.list') : '#';
    $brandUrl = $currentUser?->can('view-dashboard') ? route('admin.dashboard') : route('admin.customers.list');
@endphp

<aside class="app-sidebar admin-sidebar">
    <div class="sidebar-brand admin-sidebar-brand">
        <a href="{{ $brandUrl }}" class="brand-link admin-brand-link">
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
                <span class="account-name">{{ $accountName }}</span>
                <span class="account-email">{{ $displayEmail }}</span>
            </span>
            <i class="account-caret fa-solid fa-chevron-up"></i>
        </button>

        <div class="dropdown-menu sidebar-account-menu">
            <div class="account-menu-header">
                <span class="account-avatar account-avatar-lg">{{ $userInitial }}</span>
                <span class="account-summary">
                    <span class="account-name">{{ $accountName }}</span>
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
    /* ── Sidebar layout (structure only) ── */
    .admin-sidebar {
        overflow: visible;
        display: flex;
        flex-direction: column;
        background: var(--hk-sb-bg) !important;
        border-right: 1px solid var(--hk-sb-border) !important;
        box-shadow: none !important;
    }

    .admin-sidebar-brand {
        flex: 0 0 auto;
        height: 68px;
        padding: 12px 14px;
        background: var(--hk-sb-bg-brand) !important;
        border-bottom: 1px solid var(--hk-sb-border) !important;
    }

    .admin-brand-link {
        display: flex !important;
        align-items: center;
        gap: 10px;
        width: 100%;
        color: var(--hk-sb-text-hi) !important;
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
        background: var(--hk-sb-active) !important;
        color: #fff !important;
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
        color: var(--hk-sb-text-hi) !important;
        font-size: 13px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-brand-text small,
    .account-email {
        overflow: hidden;
        color: var(--hk-sb-muted) !important;
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
        border-radius: 7px;
        color: var(--hk-sb-text) !important;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        line-height: 1.3;
        position: relative;
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
        color: var(--hk-sb-icon) !important;
        font-size: 14px;
        text-align: center;
    }

    /* Hover */
    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link-title:hover {
        background: var(--hk-sb-hover) !important;
        color: var(--hk-sb-text-hi) !important;
    }

    /* Active */
    .admin-sidebar .nav-link.active,
    .admin-sidebar .parent-menu-item.active {
        background: var(--hk-sb-active-bg) !important;
        color: var(--hk-sb-active) !important;
    }

    .admin-sidebar .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 22%;
        height: 56%;
        width: 3px;
        background: var(--hk-sb-dot);
        border-radius: 0 3px 3px 0;
    }

    .admin-sidebar .parent-menu-item {
        height: auto !important;
        border-radius: 7px;
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
        border-radius: 7px;
        background: transparent !important;
        color: var(--hk-sb-icon) !important;
    }

    .admin-sidebar .dropdown-toggle-btn:hover {
        background: var(--hk-sb-hover) !important;
        color: var(--hk-sb-text-hi) !important;
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

    .admin-sidebar .submenu:not(.show) { max-height: 0; }
    .admin-sidebar .submenu.show { max-height: 360px; }

    .admin-sidebar .nav-treeview {
        padding: 3px 0 4px 18px;
    }

    .admin-sidebar .nav-treeview .nav-link {
        min-height: 34px;
        padding: 8px 10px;
        color: var(--hk-sb-text) !important;
        font-size: 12px;
    }

    /* Account section */
    .sidebar-account {
        position: relative;
        flex: 0 0 auto;
        padding: 10px;
        border-top: 1px solid var(--hk-sb-border) !important;
        background: var(--hk-sb-bg) !important;
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
        background: var(--hk-sb-acct-bg) !important;
        color: var(--hk-sb-text-hi) !important;
        text-align: left;
    }

    .sidebar-account-toggle:hover,
    .sidebar-account-toggle[aria-expanded="true"] {
        border-color: var(--hk-sb-border) !important;
        background: var(--hk-sb-hover) !important;
    }

    .account-caret {
        color: var(--hk-sb-icon) !important;
        font-size: 11px;
        transition: transform 0.2s ease;
    }

    .sidebar-account-toggle[aria-expanded="true"] .account-caret {
        transform: rotate(180deg);
    }

    .sidebar-account-menu {
        width: 224px;
        padding: 0;
        border: 1px solid var(--hk-sb-border) !important;
        border-radius: 8px;
        background: var(--hk-sb-bg) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35) !important;
        overflow: hidden;
    }

    .account-menu-header {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 8px;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid var(--hk-sb-border);
    }

    .account-avatar-lg {
        width: 34px;
        height: 34px;
    }

    .account-menu-section {
        padding: 6px 0;
        border-top: 1px solid var(--hk-sb-border);
    }

    .account-menu-section-last { padding: 6px 0; }

    .account-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 34px;
        padding: 8px 12px;
        border: 0;
        background: transparent;
        color: var(--hk-sb-text) !important;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.2;
        text-align: left;
        text-decoration: none;
    }

    .account-menu-item i {
        width: 16px;
        min-width: 16px;
        color: var(--hk-sb-icon) !important;
        font-size: 14px;
        text-align: center;
    }

    .account-menu-item:hover,
    .account-menu-item:focus {
        background: var(--hk-sb-hover) !important;
        color: var(--hk-sb-text-hi) !important;
        text-decoration: none;
    }

    .account-menu-logout:hover { color: #f87171 !important; }

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
            const STORAGE_KEY = 'admin-theme';

            function applyTheme(theme) {
                const isDark = theme === 'dark';

                /* 1. Set attribute on <html> — drives all CSS variables */
                document.documentElement.setAttribute('data-theme', theme);

                /* 2. Sync toggle button UI */
                if (toggle) {
                    const icon  = toggle.querySelector('i');
                    const label = toggle.querySelector('span');
                    toggle.setAttribute('aria-pressed', String(isDark));
                    if (icon)  icon.className  = isDark ? 'fa-regular fa-sun' : 'fa-regular fa-moon';
                    if (label) label.textContent = isDark ? 'Giao diện sáng' : 'Giao diện tối';
                }
            }

            /* Apply saved theme (html already has data-theme from anti-flash, just sync UI) */
            applyTheme(localStorage.getItem(STORAGE_KEY) || 'dark');

            /* Toggle on click */
            if (toggle) {
                toggle.addEventListener('click', function () {
                    const current = document.documentElement.getAttribute('data-theme') || 'dark';
                    const next    = current === 'dark' ? 'light' : 'dark';
                    localStorage.setItem(STORAGE_KEY, next);
                    applyTheme(next);
                });
            }
        });
    </script>
@endpush
