<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    <meta property="og:type" content="{{ $seo['og_type'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:locale" content="{{ $seo['og_locale'] }}">
    @if($seo['og_image'])
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif
    <meta property="og:site_name" content="{{ $seo['site_name'] }}">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
    <meta name="twitter:title" content="{{ $seo['og_title'] }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] }}">
    @if($seo['og_image'])
        <meta name="twitter:image" content="{{ $seo['og_image'] }}">
        <meta name="twitter:image:alt" content="{{ $seo['twitter_image_alt'] }}">
    @endif

    <!-- Structured Data -->
    @foreach($seo['structured_data'] as $schema)
        <script type="application/ld+json">
            {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endforeach

    <meta name="theme-color" content="{{ $branding['colors']['red'] ?? '#e11d48' }}">
    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(isset($head_code))
        {!! $head_code !!}
    @endif

    @stack('head')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50">
    <x-announcement-bar :announcements="$announcements ?? []" />

    <!-- Header -->
    <x-header :branding="$branding ?? []" :navigation="config('navigation.public', [])" />

    <!-- Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <x-footer :branding="$branding ?? []" :navigation="config('navigation.public', [])" />

    <x-back-to-top />

    @if(isset($footer_code))
        {!! $footer_code !!}
    @endif

    @stack('scripts')
</body>
</html>
