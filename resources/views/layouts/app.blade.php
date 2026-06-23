<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'NOIR | Premium Fashion Store'))</title>

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
        @unless(View::hasSection('auth_standalone') || request()->routeIs('auth.registerpage') || request()->routeIs('auth.loginpage'))
            @include('partials.flash-message')
        @endunless
        @yield('content')
    </main>

    @unless(View::hasSection('auth_standalone'))
        @include('partials.newsletter')
        @include('partials.footer')
    @endunless

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
