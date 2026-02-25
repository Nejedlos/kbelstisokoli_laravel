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
</head>
<body class="min-h-screen flex flex-col bg-slate-50 error-bg antialiased">
    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
        <div class="max-w-2xl w-full">
            <div class="mb-12">
                <a href="{{ route('public.home') }}" class="inline-block transform hover:scale-105 transition-transform duration-300">
                    @if($branding['logo_path'] ?? null)
                        <img src="{{ asset('storage/' . $branding['logo_path']) }}" alt="{{ $branding['club_name'] ?? config('app.name') }}" class="h-20 md:h-24 w-auto mx-auto">
                    @else
                        <img src="{{ asset('assets/img/logo/logo-ks.svg') }}" alt="{{ $branding['club_name'] ?? config('app.name') }}" class="h-20 md:h-24 w-auto mx-auto">
                    @endif
                </a>
            </div>

            <!-- Error Code -->
            <div class="mb-4 inline-block px-4 py-1 rounded-full bg-primary/10 text-primary font-black text-sm uppercase tracking-widest">
                @yield('code')
            </div>

            <!-- Content -->
            <h1 class="text-4xl md:text-6xl font-display font-black uppercase tracking-tighter mb-6 text-secondary leading-none">
                @yield('headline')
            </h1>

            <p class="text-lg md:text-xl text-slate-600 mb-4 leading-relaxed max-w-lg mx-auto text-balance">
                @yield('message')
            </p>

            <p class="text-slate-400 font-medium italic mb-10">
                @yield('tagline')
            </p>

            <!-- Actions -->
            <div class="flex flex-wrap items-center justify-center gap-4 mb-12">
                @yield('actions')
            </div>

            <!-- Navigation Links -->
            <div class="pt-10 border-t border-slate-200 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-xl mx-auto">
                <a href="{{ route('public.news.index') }}" class="text-sm font-bold uppercase tracking-wider text-slate-400 hover:text-primary transition-colors">
                    {{ __('errors.cta.news') }}
                </a>
                <a href="{{ route('public.matches.index') }}" class="text-sm font-bold uppercase tracking-wider text-slate-400 hover:text-primary transition-colors">
                    {{ __('errors.cta.matches') }}
                </a>
                <a href="{{ route('public.teams.index') }}" class="text-sm font-bold uppercase tracking-wider text-slate-400 hover:text-primary transition-colors">
                    {{ __('errors.cta.teams') }}
                </a>
                <a href="{{ route('public.contact.index') }}" class="text-sm font-bold uppercase tracking-wider text-slate-400 hover:text-primary transition-colors">
                    {{ __('errors.cta.contact') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Footer Simple -->
    <footer class="p-8 text-center text-slate-400 text-sm">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </footer>
</body>
</html>
