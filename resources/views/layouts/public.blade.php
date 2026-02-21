<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($branding['club_name'] ?? config('app.name')) }}</title>
    <meta name="theme-color" content="{{ $branding['colors']['red'] ?? '#e11d48' }}">
    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(isset($head_code))
        {!! $head_code !!}
    @endif

    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-slate-50">
    <!-- Header -->
    <x-header :branding="$branding ?? []" :navigation="config('navigation.public', [])" />

    <!-- Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <x-footer :branding="$branding ?? []" :navigation="config('navigation.public', [])" />

    @if(isset($footer_code))
        {!! $footer_code !!}
    @endif

    @stack('scripts')
</body>
</html>
