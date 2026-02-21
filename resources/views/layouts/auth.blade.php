<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? 'Zabezpečení účtu' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <meta name="theme-color" content="{{ $branding['colors']['red'] ?? '#e11d48' }}">
    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="antialiased text-white/90">
    <div class="auth-gradient">
        <!-- Floating Background Objects -->
        <div class="floating-objects pointer-events-none">
            <div class="floating-ball w-64 h-64 top-[-10%] left-[-5%]"></div>
            <div class="floating-ball w-96 h-96 bottom-[-15%] right-[-10%] opacity-5"></div>
        </div>

        <div class="w-full max-w-md relative z-10">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
