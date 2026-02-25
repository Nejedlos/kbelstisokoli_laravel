<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('app.name', 'Kbelští sokoli') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @php
        // Získání brandingu z BrandingService
        $brandingService = app(\App\Services\BrandingService::class);
        $branding = $brandingService->getSettings();
        $branding_css = $brandingService->getCssVariables();
    @endphp

    <meta name="theme-color" content="{{ $branding['colors']['red'] ?? '#e11d48' }}">
    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .error-bg {
            background-image: radial-gradient(circle at 20% 30%, rgba(225, 29, 72, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 80% 70%, rgba(22, 30, 47, 0.05) 0%, transparent 50%);
        }
    </style>
    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-slate-50 error-bg antialiased">
    <!-- Header -->
    <x-header :branding="$branding" :navigation="config('navigation.public', [])" />

    <main class="flex-1 flex flex-col items-center justify-center p-6 py-20 text-center">
        <div class="max-w-4xl w-full">
            <!-- Error Code -->
            <div class="mb-6 inline-block px-4 py-1 rounded-full bg-primary/10 text-primary font-black text-sm uppercase tracking-widest">
                @yield('code')
            </div>

            <!-- Content -->
            <h1 class="text-4xl md:text-7xl font-display font-black uppercase tracking-tighter mb-8 text-secondary leading-none">
                @yield('headline')
            </h1>

            <p class="text-xl md:text-2xl text-slate-600 mb-6 leading-relaxed max-w-2xl mx-auto text-balance">
                @yield('message')
            </p>

            <p class="text-slate-400 font-medium italic mb-12">
                @yield('tagline')
            </p>

            <!-- Actions -->
            <div class="flex flex-wrap items-center justify-center gap-4">
                @yield('actions')
            </div>

            @yield('extra')
        </div>
    </main>

    <!-- Footer -->
    <x-footer :branding="$branding" :navigation="config('navigation.public', [])" />

    @stack('scripts')
</body>
</html>
