

<!-- Main Navigation Header -->
<header class="main-header">
    <nav class="navbar navbar-expand-lg py-0">
        <div class="container-fluid px-lg-5">
            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler border-0 shadow-none ps-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Brand Logo -->
            <a class="navbar-brand me-lg-5" href="{{ url('/') }}">
                HK Store
            </a>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">TRANG CHỦ</a>
                    </li>
                    <li class="nav-item dropdown mega-dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ url('/products') }}" id="productsDropdown" role="button">
                            Sản phẩm
                        </a>
                        <div class="mega-menu" aria-labelledby="productsDropdown">
                            <div class="mega-menu-inner">
                                <!-- Cột chung -->
                                <div class="mega-col mega-col-general">
                                    <ul class="mega-list">
                                        <li><a href="{{ url('/products') }}">Tất cả sản phẩm</a></li>
                                        <li><a href="{{ url('/products?sort=best-selling') }}">Sản phẩm bán chạy</a></li>
                                        <li><a href="{{ url('/products?sort=newest') }}">Sản phẩm mới nhất</a></li>
                                    </ul>
                                </div>
                                <div class="mega-col-separator"></div>
                                <!-- Cột Nam -->
                                <div class="mega-col">
                                    <h6 class="mega-heading">
                                        <a href="{{ route('category.products', 'ao') }}?gender=men" class="mega-heading-link">NAM</a>
                                    </h6>
                                    <hr class="mega-divider">
                                    <ul class="mega-list">
                                        <li><a href="{{ route('category.products', 'ao-thun') }}?gender=men">Áo thun</a></li>
                                        <li><a href="{{ route('category.products', 'ao-so-mi') }}?gender=men">Áo sơ mi</a></li>
                                        <li><a href="{{ route('category.products', 'ao-polo') }}?gender=men">Áo polo</a></li>
                                        <li><a href="{{ route('category.products', 'ao-hoodie-sweatshirt') }}?gender=men">Áo hoodie</a></li>
                                        <li><a href="{{ route('category.products', 'ao-khoac') }}?gender=men">Áo khoác</a></li>
                                        <li><a href="{{ route('category.products', 'ao-blazer') }}?gender=men">Áo blazer</a></li>
                                        <li><a href="{{ route('category.products', 'quan-jeans') }}?gender=men">Quần jeans</a></li>
                                        <li><a href="{{ route('category.products', 'quan-tay') }}?gender=men">Quần tây</a></li>
                                        <li><a href="{{ route('category.products', 'quan-short') }}?gender=men">Quần short</a></li>
                                        <li><a href="{{ route('category.products', 'quan-jogger') }}?gender=men">Quần jogger</a></li>
                                    </ul>
                                </div>
                                <!-- Cột Nữ -->
                                <div class="mega-col">
                                    <h6 class="mega-heading">
                                        <a href="{{ route('category.products', 'ao') }}?gender=women" class="mega-heading-link">NỮ</a>
                                    </h6>
                                    <hr class="mega-divider">
                                    <ul class="mega-list">
                                        <li><a href="{{ route('category.products', 'ao-thun') }}?gender=women">Áo thun</a></li>
                                        <li><a href="{{ route('category.products', 'ao-so-mi') }}?gender=women">Áo sơ mi</a></li>
                                        <li><a href="{{ route('category.products', 'ao-polo') }}?gender=women">Áo polo</a></li>
                                        <li><a href="{{ route('category.products', 'ao-hoodie-sweatshirt') }}?gender=women">Áo hoodie</a></li>
                                        <li><a href="{{ route('category.products', 'ao-khoac') }}?gender=women">Áo khoác</a></li>
                                        <li><a href="{{ route('category.products', 'ao-len') }}?gender=women">Áo len</a></li>
                                        <li><a href="{{ route('category.products', 'quan-jeans') }}?gender=women">Quần jeans</a></li>
                                        <li><a href="{{ route('category.products', 'quan-tay') }}?gender=women">Quần tây</a></li>
                                        <li><a href="{{ route('category.products', 'dam') }}?gender=women">Đầm</a></li>
                                        <li><a href="{{ route('category.products', 'vay') }}?gender=women">Váy</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/new-arrivals') }}">Hàng mới về</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/contact') }}">Liên hệ</a>
                    </li>
                </ul>
            </div>

            <!-- Right Utility Icons -->
            <div class="utility-icons d-flex align-items-center">
                <!-- Search Trigger -->
                <button class="btn-icon" id="searchTrigger" title="Tìm kiếm">
                    <i class="bi bi-search"></i>
                </button>

                <!-- Wishlist -->
                <a href="{{ url('/wishlist') }}" class="btn-icon" title="Danh sách yêu thích">
                    <i class="bi bi-heart"></i>
                    <span class="badge-count">{{ $wishlistCount ?? 0 }}</span>
                </a>

                <!-- Cart -->
                <a href="{{ url('/cart') }}" class="btn-icon" title="Giỏ hàng">
                    <i class="bi bi-bag"></i>
                    <span class="badge-count">0</span>
                </a>

                <!-- User Account Dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="btn-icon" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" title="Tài khoản">
                        <i class="bi bi-person"></i>
                        @auth
                            <span class="ms-1 d-none d-lg-inline fw-medium" style="font-size: 14px;">Xin chào, {{ auth()->user()->username }}</span>
                        @endauth
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                        @auth
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="{{ url('/orders') }}">Đơn hàng của tôi</a></li>
                            @if(auth()->user()->can('access-admin'))
                                <li><a class="dropdown-item text-primary" href="{{ route('admin.dashboard') }}">Trang Quản Trị</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('auth.logout') }}">Đăng xuất</a>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('auth.loginpage') }}">Đăng nhập</a></li>
                            @if (Route::has('register'))
                                <li><a class="dropdown-item" href="{{ route('register') }}">Đăng ký</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Search Overlay -->
<div class="search-overlay" id="searchOverlay">
    <span class="search-close" id="searchClose"><i class="bi bi-x"></i></span>
    <div class="container d-flex justify-content-center">
        <form action="{{ url('/search') }}" method="GET" class="w-100 max-w-600">
            <input type="text" name="q" class="search-input-field" placeholder="TÌM KIẾM SẢN PHẨM..." autocomplete="off">
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchTrigger = document.getElementById('searchTrigger');
        const searchOverlay = document.getElementById('searchOverlay');
        const searchClose = document.getElementById('searchClose');

        if (searchTrigger && searchOverlay && searchClose) {
            searchTrigger.addEventListener('click', function() {
                searchOverlay.style.display = 'flex';
                searchOverlay.querySelector('input').focus();
            });

            searchClose.addEventListener('click', function() {
                searchOverlay.style.display = 'none';
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    searchOverlay.style.display = 'none';
                }
            });
        }

        // Mega menu: click toggle for mobile, hover handled by CSS for desktop
        const megaDropdown = document.querySelector('.mega-dropdown');
        if (megaDropdown) {
            const toggle = megaDropdown.querySelector('.dropdown-toggle');
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    megaDropdown.classList.toggle('active');
                }
            });

            document.addEventListener('click', function(e) {
                if (!megaDropdown.contains(e.target)) {
                    megaDropdown.classList.remove('active');
                }
            });
        }
    });
</script>
@endpush
