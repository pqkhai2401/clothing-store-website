<!doctype html>
<html lang="en">
<script>
    /* Anti-flash: set theme before CSS renders */
    (function(){var t=localStorage.getItem('admin-theme')||'dark';document.documentElement.setAttribute('data-theme',t);}());
</script>

@include('layouts.partial.css')
@yield('css')

<body class="layout-fixed sidebar-expand-lg">
    <div class="app-wrapper">
        @include('layouts.partial.header')

        @include('layouts.partial.sidebar')

        <main class="app-main">
            @yield('content')
        </main>

        @include('layouts.partial.footer')
    </div>

    @include('layouts.partial.js')
    @stack('scripts')
    @stack('modals')
    @include('admin.partials.profile-modal')
</body>

</html>
