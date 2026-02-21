<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo_title ?? $title ?? ($branding['club_name'] ?? config('app.name')) }}</title>
    <meta name="description" content="{{ $seo_description ?? $branding['slogan'] ?? '' }}">
    <meta name="keywords" content="{{ $seo_keywords ?? '' }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $og_title ?? $seo_title ?? $title ?? ($branding['club_name'] ?? config('app.name')) }}">
    <meta property="og:description" content="{{ $og_description ?? $seo_description ?? $branding['slogan'] ?? '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $og_image ?? ($branding['logo_path'] ? asset('storage/' . $branding['logo_path']) : '') }}">
    <meta property="og:site_name" content="{{ $branding['club_name'] ?? config('app.name') }}">

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
