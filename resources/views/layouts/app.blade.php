<!doctype html>
<html lang="en">

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
</body>

</html>