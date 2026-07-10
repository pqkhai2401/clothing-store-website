

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
                                        <a href="{{ route('category.products', 'nam') }}" class="mega-heading-link">NAM</a>
                                    </h6>
                                    <hr class="mega-divider">
                                    <ul class="mega-list">
                                        <li><a href="{{ route('category.products', 'nam-ao-thun') }}">Áo thun</a></li>
                                        <li><a href="{{ route('category.products', 'nam-ao-so-mi') }}">Áo sơ mi</a></li>
                                        <li><a href="{{ route('category.products', 'nam-ao-polo') }}">Áo polo</a></li>
                                        <li><a href="{{ route('category.products', 'nam-ao-hoodie') }}">Áo hoodie</a></li>
                                        <li><a href="{{ route('category.products', 'nam-ao-khoac') }}">Áo khoác</a></li>
                                        <li><a href="{{ route('category.products', 'nam-ao-blazer') }}">Áo blazer</a></li>
                                        <li><a href="{{ route('category.products', 'nam-quan-jeans') }}">Quần jeans</a></li>
                                        <li><a href="{{ route('category.products', 'nam-quan-tay') }}">Quần tây</a></li>
                                        <li><a href="{{ route('category.products', 'nam-quan-short') }}">Quần short</a></li>
                                        <li><a href="{{ route('category.products', 'nam-quan-jogger') }}">Quần jogger</a></li>
                                    </ul>
                                </div>
                                <!-- Cột Nữ -->
                                <div class="mega-col">
                                    <h6 class="mega-heading">
                                        <a href="{{ route('category.products', 'nu') }}" class="mega-heading-link">NỮ</a>
                                    </h6>
                                    <hr class="mega-divider">
                                    <ul class="mega-list">
                                        <li><a href="{{ route('category.products', 'nu-ao-thun') }}">Áo thun</a></li>
                                        <li><a href="{{ route('category.products', 'nu-ao-so-mi') }}">Áo sơ mi</a></li>
                                        <li><a href="{{ route('category.products', 'nu-ao-polo') }}">Áo polo</a></li>
                                        <li><a href="{{ route('category.products', 'nu-ao-hoodie') }}">Áo hoodie</a></li>
                                        <li><a href="{{ route('category.products', 'nu-ao-khoac') }}">Áo khoác</a></li>
                                        <li><a href="{{ route('category.products', 'nu-ao-len') }}">Áo len</a></li>
                                        <li><a href="{{ route('category.products', 'nu-quan-jeans') }}">Quần jeans</a></li>
                                        <li><a href="{{ route('category.products', 'nu-quan-tay') }}">Quần tây</a></li>
                                        <li><a href="{{ route('category.products', 'nu-dam') }}">Đầm</a></li>
                                        <li><a href="{{ route('category.products', 'nu-vay') }}">Váy</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('new-arrivals') }}">Hàng mới về</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('collections') }}">Bộ sưu tập</a>
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
                    <span class="badge-count" id="cartCountBadge">{{ $cartCount ?? 0 }}</span>
                </a>

                <!-- User Account Dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="btn-icon d-flex align-items-center" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" title="Tài khoản">
                        @auth
                            <img
                                src="{{ auth()->user()->avatar_url ? asset('storage/' . auth()->user()->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->username) . '&background=random' }}"
                                alt="Ảnh đại diện"
                                class="rounded-circle me-2 nav-mini-avatar"
                            >
                            <span class="d-none d-lg-inline fw-medium" style="font-size: 14px;">Xin chào, {{ auth()->user()->username }}</span>
                        @else
                            <i class="bi bi-person"></i>
                        @endauth
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                        @auth
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">Đơn hàng của tôi</a></li>
                            @if(auth()->user()->can('access-admin'))
                                <li><a class="dropdown-item text-primary" href="{{ route('admin.dashboard') }}">Trang Quản Trị</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('auth.logout') }}"
                                       onclick="event.preventDefault(); this.closest('form').submit();">Đăng xuất</a>
                                </form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('auth.loginpage') }}">Đăng nhập</a></li>
                            <li><a class="dropdown-item" href="{{ route('auth.registerpage') }}">Đăng ký</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== Site Navigation Search (slide-down panel) ===== -->
    <div class="nav-search-backdrop" id="navSearchBackdrop"></div>

    <div class="nav-search-panel" id="navSearchPanel">
        <div class="container-fluid px-lg-5">
            <button class="nav-search-close" id="navSearchClose" title="Đóng" aria-label="Đóng tìm kiếm">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="nav-search-inner">
                <form action="{{ url('/search') }}" method="GET" class="nav-search-form" id="navSearchForm">
                    <i class="bi bi-search nav-search-icon"></i>
                    <input type="text" name="q" id="searchInput" class="nav-search-input"
                           placeholder="Bạn đang tìm kiếm sản phẩm nào?..." autocomplete="off"
                           value="{{ request('q') }}">
                </form>

                <!-- Live suggestions dropdown -->
                <div id="searchSuggestions" class="nav-search-suggestions" style="display:none;"></div>

                <!-- Popular keywords -->
                <div class="nav-search-trending" id="navSearchTrending">
                    <span class="nav-search-trending-label">Từ khóa phổ biến</span>
                    <div class="nav-search-tags">
                        <a href="{{ url('/search') }}?q=Áo sơ mi" class="nav-search-tag">Áo sơ mi</a>
                        <a href="{{ url('/search') }}?q=Áo khoác" class="nav-search-tag">Áo khoác</a>
                        <a href="{{ url('/search') }}?q=Quần jeans" class="nav-search-tag">Quần jeans</a>
                        <a href="{{ url('/search') }}?q=Đầm" class="nav-search-tag">Đầm</a>
                        <a href="{{ url('/search') }}?q=Áo hoodie" class="nav-search-tag">Áo hoodie</a>
                        <a href="{{ url('/search') }}?q=Áo blazer" class="nav-search-tag">Áo blazer</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
/* ===== Mini Avatar (Navbar) ===== */
.nav-mini-avatar {
    width: 32px;
    height: 32px;
    object-fit: cover;
    flex-shrink: 0;
}

/* ===== Site Navigation Search ===== */

.nav-search-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.35s ease, visibility 0.35s ease;
    z-index: 1015;
}
.nav-search-backdrop.is-active {
    opacity: 1;
    visibility: visible;
}

.nav-search-panel {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: var(--background-color);
    border-bottom: 1px solid var(--border-color);
    box-shadow: 0 24px 48px rgba(0, 0, 0, 0.08);
    z-index: 1080;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-16px);
    transition: opacity 0.35s ease, transform 0.35s ease, visibility 0.35s ease;
}
.nav-search-panel.is-active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.nav-search-panel .container-fluid {
    position: relative;
}

.nav-search-inner {
    max-width: 760px;
    margin: 0 auto;
    padding: 56px 0 44px;
}

.nav-search-close {
    position: absolute;
    top: 22px;
    right: 1rem;
    background: none;
    border: none;
    font-size: 18px;
    line-height: 1;
    color: var(--text-color);
    padding: 8px;
    cursor: pointer;
    transition: color 0.2s ease, transform 0.2s ease;
}
.nav-search-close:hover {
    color: var(--muted-text);
    transform: rotate(90deg);
}

.nav-search-form {
    display: flex;
    align-items: center;
    gap: 16px;
    border-bottom: 1px solid var(--text-color);
    padding-bottom: 16px;
}
.nav-search-icon {
    font-size: 20px;
    color: var(--muted-text);
    flex-shrink: 0;
}
.nav-search-input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    background: transparent;
    font-family: var(--font-serif);
    font-size: 28px;
    font-weight: 400;
    letter-spacing: 0.3px;
    color: var(--text-color);
}
.nav-search-input::placeholder {
    color: #bdbdbd;
    font-style: italic;
}

.nav-search-trending {
    margin-top: 28px;
}
.nav-search-trending-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--muted-text);
    margin-bottom: 14px;
}
.nav-search-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.nav-search-tag {
    display: inline-block;
    padding: 7px 18px;
    border: 1px solid var(--border-color);
    border-radius: 30px;
    font-size: 13px;
    color: var(--text-color);
    white-space: nowrap;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
.nav-search-tag:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}

.nav-search-suggestions {
    margin-top: 4px;
    max-height: 420px;
    overflow-y: auto;
}
.search-suggestion-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 0;
    text-decoration: none;
    color: var(--text-color);
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.15s;
}
.search-suggestion-item:last-child { border-bottom: none; }
.search-suggestion-item:hover { background: var(--hover-bg); }
.search-suggestion-img {
    width: 52px;
    height: 52px;
    object-fit: cover;
    flex-shrink: 0;
    background: #f5f5f5;
}
.search-suggestion-info { flex: 1; min-width: 0; }
.search-suggestion-category {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted-text);
    margin-bottom: 2px;
}
.search-suggestion-name {
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.search-suggestion-price {
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
    text-align: right;
}
.search-suggestion-price .s-sale { color: #d9534f; }
.search-suggestion-price .s-original {
    display: block;
    font-size: 10px;
    font-weight: 400;
    text-decoration: line-through;
    color: var(--muted-text);
}
.search-suggestions-footer {
    display: block;
    text-align: center;
    padding: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--primary-color);
    border-top: 1px solid var(--border-color);
    background: #fafafa;
    text-decoration: none;
}
.search-suggestions-footer:hover { background: var(--hover-bg); }
.search-no-result {
    padding: 20px 0;
    text-align: center;
    color: var(--muted-text);
    font-size: 13px;
}

body.nav-search-open {
    overflow: hidden;
}

@media (min-width: 992px) {
    .nav-search-close { right: 3rem; }
}

@media (max-width: 991.98px) {
    .nav-search-inner { padding: 44px 0 32px; }
    .nav-search-input { font-size: 20px; }
}
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchTrigger = document.getElementById('searchTrigger');
        const navSearchPanel = document.getElementById('navSearchPanel');
        const navSearchBackdrop = document.getElementById('navSearchBackdrop');
        const navSearchClose = document.getElementById('navSearchClose');
        const navSearchTrending = document.getElementById('navSearchTrending');
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');

        // ── Mở / đóng panel ──
        if (searchTrigger && navSearchPanel && navSearchBackdrop) {
            searchTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                openSearch();
            });

            navSearchClose.addEventListener('click', closeSearch);
            navSearchBackdrop.addEventListener('click', closeSearch);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSearch();
            });
        }

        function openSearch() {
            navSearchPanel.classList.add('is-active');
            navSearchBackdrop.classList.add('is-active');
            document.body.classList.add('nav-search-open');
            setTimeout(() => {
                if (!searchInput) return;
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            }, 320);
        }

        function closeSearch() {
            navSearchPanel.classList.remove('is-active');
            navSearchBackdrop.classList.remove('is-active');
            document.body.classList.remove('nav-search-open');
            hideSuggestions();
        }

        // ── Live suggestions ──
        let debounceTimer = null;

        if (searchInput && searchSuggestions) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const q = this.value.trim();
                if (q.length < 2) { hideSuggestions(); return; }
                debounceTimer = setTimeout(() => fetchSuggestions(q), 280);
            });
        }

        function fetchSuggestions(q) {
            fetch('/api/search/suggestions?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(items => renderSuggestions(items, q))
            .catch(() => hideSuggestions());
        }

        function renderSuggestions(items, q) {
            if (navSearchTrending) navSearchTrending.style.display = 'none';

            if (!items || items.length === 0) {
                searchSuggestions.innerHTML =
                    '<div class="search-no-result">Không tìm thấy sản phẩm phù hợp</div>';
                searchSuggestions.style.display = 'block';
                return;
            }

            let html = '';
            items.forEach(function(p) {
                const priceHtml = p.final
                    ? `<span class="s-sale">${p.final}</span><span class="s-original">${p.price}</span>`
                    : `<span>${p.price}</span>`;

                const imgSrc = p.image || 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=100&auto=format&fit=crop';

                html += `<a href="${p.url}" class="search-suggestion-item">
                    <img src="${imgSrc}" alt="${p.name}" class="search-suggestion-img" loading="lazy">
                    <div class="search-suggestion-info">
                        <div class="search-suggestion-category">${p.category}</div>
                        <div class="search-suggestion-name">${p.name}</div>
                    </div>
                    <div class="search-suggestion-price">${priceHtml}</div>
                </a>`;
            });

            html += `<a href="/search?q=${encodeURIComponent(q)}" class="search-suggestions-footer">
                Xem tất cả kết quả <i class="bi bi-arrow-right ms-1"></i>
            </a>`;

            searchSuggestions.innerHTML = html;
            searchSuggestions.style.display = 'block';
        }

        function hideSuggestions() {
            if (searchSuggestions) searchSuggestions.style.display = 'none';
            if (navSearchTrending) navSearchTrending.style.display = '';
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
