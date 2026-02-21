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
<body class="antialiased">
    @yield('content')

    @stack('scripts')
</body>
</html>
