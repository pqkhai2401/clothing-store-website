

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
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/products') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/new-arrivals') }}">New Arrivals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/men') }}">Men</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/women') }}">Women</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/contact') }}">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Right Utility Icons -->
            <div class="utility-icons d-flex align-items-center">
                <!-- Search Trigger -->
                <button class="btn-icon" id="searchTrigger" title="Search">
                    <i class="bi bi-search"></i>
                </button>

                <!-- Wishlist -->
                <a href="{{ url('/wishlist') }}" class="btn-icon" title="Wishlist">
                    <i class="bi bi-heart"></i>
                    <span class="badge-count">0</span>
                </a>

                <!-- Cart -->
                <a href="{{ url('/cart') }}" class="btn-icon" title="Shopping Cart">
                    <i class="bi bi-bag"></i>
                    <span class="badge-count">0</span>
                </a>

                <!-- User Account Dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="btn-icon" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                        <i class="bi bi-person"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                        @auth
                            <li><a class="dropdown-item" href="{{ url('/profile') }}">My Profile</a></li>
                            <li><a class="dropdown-item" href="{{ url('/orders') }}">My Orders</a></li>
                            @if(auth()->user()->isAdmin())
                                <li><a class="dropdown-item text-primary" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('auth.logout') }}">Log Out</a>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('auth.loginpage') }}">Log In</a></li>
                            @if (Route::has('register'))
                                <li><a class="dropdown-item" href="{{ route('register') }}">Register</a></li>
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
            <input type="text" name="q" class="search-input-field" placeholder="SEARCH FOR PRODUCTS..." autocomplete="off">
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

            // Close search on Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    searchOverlay.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
