<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <script>
        (function () {
            var t = localStorage.getItem('admin-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('assets/css/overlayscrollbars.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/apexcharts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tom-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tom-select.crm.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/listCS.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}" />

    @yield('css')
    @stack('styles')
</head>