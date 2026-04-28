<!-- filepath: resources\views\layouts\partial\sidebar.blade.php -->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#" class="brand-link">
            <img src="{{ asset('assets/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
                class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">An Thuận Clinic</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
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
                                <div class="nav-link d-flex align-items-center justify-content-between p-0 parent-menu-item {{ $isActiveParent ? 'active' : '' }}"
                                    style="cursor: pointer;">

                                    <a href="{{ $item['url'] === '#' ? 'javascript:void(0)' : url($item['url']) }}"
                                        class="nav-link-title flex-grow-1">
                                        <i class="nav-icon {{ $item['icon'] ?? 'fa-solid fa-circle' }}"
                                            style="margin-top: 7px;"></i>
                                        <p class="mb-0">{{ $item['title'] }}</p>
                                    </a>

                                    <div class="dropdown-toggle-btn">
                                        <i class="nav-arrow fa-solid fa-chevron-right {{ $isActiveParent ? 'rotate-90' : '' }}"
                                            style="margin-top: 8px;"></i>
                                    </div>
                                </div>

                                <ul class="nav nav-treeview submenu {{ $isActiveParent ? 'show' : '' }}" id="submenu-{{ $index }}">
                                    @foreach($item['parent'] as $subItem)
                                        @php
                                            $childPattern = $subItem['active_pattern'] ?? '';
                                            $isActiveChild = ($childPattern && request()->is($childPattern));
                                        @endphp
                                        <li class="nav-item">
                                            <a href="{{ url($subItem['url']) }}" class="nav-link {{ $isActiveChild ? 'active' : '' }}">
                                                <i class="nav-icon {{ $subItem['icon'] ?? 'fa-regular fa-circle' }}"
                                                    style="margin-top: 7px;"></i>
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
</aside>

<style>
    .nav-link-title {
        display: flex !important;
        align-items: center;
        padding: 0.5rem 1rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.15s ease-in-out;
        height: 40px;
    }

    .nav-link-title:hover {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.1);
    }

    .nav-link-title.active {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.15);
    }

    .dropdown-toggle-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease-in-out;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        background: none !important;
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .dropdown-toggle-btn:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
    }

    .dropdown-toggle-btn:focus {
        box-shadow: none !important;
        outline: none !important;
    }

    .dropdown-toggle-btn:active {
        transform: scale(0.95);
    }

    .nav-arrow {
        transition: transform 0.3s ease-in-out;
        font-size: 0.75rem;
        position: relative;
        top: 1px;
    }

    .nav-arrow.rotate-90 {
        transform: rotate(90deg);
    }

    /* Ensure proper spacing and alignment */
    .nav-item .nav-link {
        display: flex;
        align-items: center;
        min-height: 40px;
        padding: 0.5rem 1rem;
        line-height: 1.5;
    }

    .nav-item .nav-link p {
        margin-bottom: 0;
        line-height: 1.5;
    }

    /* Icon alignment */
    .nav-icon {
        display: inline-block;
        width: 20px;
        height: 20px;
        text-align: center;
        font-size: 1rem;
        line-height: 1;
    }

    /* Fix the parent container alignment */
    .nav-link.d-flex {
        padding: 0 !important;
        height: 40px;
        overflow: hidden;
    }

    /* Improve the submenu items with proper indentation */
    .nav-treeview .nav-item .nav-link {
        padding-left: 3.5rem !important;
        position: relative;
        margin-left: 1rem;
        border-left: 2px solid rgba(255, 255, 255, 0.1);
    }

    .nav-treeview .nav-item .nav-link .nav-icon {
        position: absolute;
        left: 1.5rem;
        width: 16px;
        height: 16px;
        font-size: 0.875rem;
    }

    /* Add visual hierarchy with subtle background for submenu items */
    .nav-treeview .nav-item .nav-link {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .nav-treeview .nav-item .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15) !important;
        border-left-color: rgba(255, 255, 255, 0.3);
    }

    .nav-treeview .nav-item .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2) !important;
        border-left-color: #fff;
    }

    /* Submenu text styling for better hierarchy */
    .nav-treeview .nav-item .nav-link p {
        font-size: 0.9rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.85);
    }

    .nav-treeview .nav-item .nav-link:hover p,
    .nav-treeview .nav-item .nav-link.active p {
        color: #fff;
        font-weight: 500;
    }

    /* Adjust submenu container for better spacing */
    .nav-treeview {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }

    /* Global fixes for vertical alignment */
    .app-sidebar .nav-item a,
    .app-sidebar .nav-link-title,
    .app-sidebar .dropdown-toggle-btn {
        display: flex;
        align-items: center;
    }

    /* Submenu animation */
    .submenu {
        overflow: hidden;
        transition: max-height 0.3s ease-in-out;
    }

    .submenu:not(.show) {
        max-height: 0;
    }

    .submenu.show {
        max-height: 300px;
        /* Adjust based on your menu size */
    }

    /* Ripple effect for buttons */
    .dropdown-toggle-btn {
        position: relative;
        overflow: hidden;
    }

    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.2);
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
            overflow-y: auto;
            z-index: 1050 !important;
        }
    }
</style>

@push('scripts')
    <script src="{{ asset('assets/js/managers/sidebarAccountant.js') }}"></script>
@endpush
