<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'NOIR | Premium Fashion Store'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Ecommerce CSS -->
    <link href="{{ asset('css/ecommerce.css') }}" rel="stylesheet">

    @yield('css')
</head>
<body class="@yield('body_class')">

    @unless(View::hasSection('auth_standalone'))
        @include('partials.header')
    @endunless

    <!-- Main Content Area -->
    <main>
        @unless(View::hasSection('auth_standalone') || request()->routeIs('auth.registerpage') || request()->routeIs('auth.loginpage') || request()->routeIs('profile.index'))
            @if(View::hasSection('hide_flash_errors'))
                @include('partials.flash-message-success-only')
            @else
                @include('partials.flash-message')
            @endif
        @endunless
        @yield('content')
    </main>

    @unless(View::hasSection('auth_standalone'))
        @include('partials.footer')
    @endunless

    <!-- Back to Top -->
    <a href="#" class="back-to-top" id="backToTop" aria-label="Cuộn lên đầu trang">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- Global Toast Container (mọi trang) -->
    <div class="global-toast-container" id="globalToastContainer"></div>
    <style>
        .global-toast-container {
            position: fixed;
            top: 28px;
            right: 28px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .global-toast {
            background: var(--primary-color, #111);
            color: #fff;
            padding: 12px 20px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 240px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
            opacity: 0;
            transform: translateX(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            pointer-events: auto;
        }

        .global-toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .global-toast.success { background: #28a745; }
        .global-toast.error   { background: #d9534f; }
    </style>
    <script>
        window.showToast = function (message, type = 'default') {
            const container = document.getElementById('globalToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'global-toast ' + type;
            toast.innerHTML = '<i class="bi ' +
                (type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-x-circle' : 'bi-info-circle') +
                '"></i> ' + message;
            container.appendChild(toast);

            requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        };

        window.updateCartBadge = function (count) {
            document.querySelectorAll('#cartCountBadge').forEach(function (el) {
                el.textContent = count;
            });
        };
    </script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Global Wishlist Toggle (mọi trang) -->
    <script src="{{ asset('js/wishlist.js') }}"></script>

    <!-- Back to Top -->
    <script src="{{ asset('js/back-to-top.js') }}"></script>

    @stack('scripts')
    @include('layouts.components.confirm.global')
</body>
</html>
