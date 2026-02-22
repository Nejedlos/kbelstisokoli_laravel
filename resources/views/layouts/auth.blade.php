@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $colors = $branding['colors'] ?? [];

    $hexToRgb = function($hex) {
        $hex = str_replace('#', '', (string) $hex);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "{$r}, {$g}, {$b}";
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($branding['club_name'] ?? 'Kbelští sokoli') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <meta name="theme-color" content="{{ $colors['red'] ?? '#e11d48' }}">
    <style>{!! app(\App\Services\BrandingService::class)->getCssVariables() !!}</style>
    @vite(['resources/css/filament-auth.css', 'resources/js/app.js', 'resources/js/filament-auth.js', 'resources/js/filament-error-handler.js'])

    @stack('head')
</head>
<body class="antialiased">
    <div class="ks-auth-page auth-gradient w-full min-h-dvh flex items-center justify-center py-6 px-4 md:px-6 lg:px-8 relative overflow-x-hidden"
         style="
            background-color: #0f172a !important;
            background-image:
                radial-gradient(1200px 700px at 50% -10%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.40) 0%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.12) 40%, transparent 72%),
                radial-gradient(1400px 900px at 0% 100%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.28) 0%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.12) 50%, transparent 82%),
                radial-gradient(1200px 900px at 100% 100%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.26) 0%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.10) 55%, transparent 85%),
                radial-gradient(1000px 600px at 50% 10%, rgba(255, 255, 255, 0.08) 0%, transparent 70%) !important;
            background-attachment: fixed !important;
            background-size: cover !important;
        "
    >
        {{-- Background Elements (Tactical & Atmospheric) --}}
        <div class="absolute inset-0 z-0 pointer-events-none select-none overflow-hidden">
            {{-- Noise Texture --}}
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=\'0 0 256 256\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'noiseFilter\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.65\' numOctaves=\'3\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23noiseFilter)\'/%3E%3C/svg%3E');"></div>

            {{-- Tactical SVG Lines (Simplified Court) --}}
            <svg class="absolute inset-0 w-full h-full opacity-[0.05]" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice">
                <circle cx="500" cy="500" r="150" fill="none" stroke="white" stroke-width="1.5" />
                <line x1="0" y1="500" x2="1000" y2="500" stroke="white" stroke-width="1.5" />
                <rect x="250" y="0" width="500" height="200" fill="none" stroke="white" stroke-width="1.5" />
                <rect x="250" y="800" width="500" height="200" fill="none" stroke="white" stroke-width="1.5" />

                {{-- Tactic markers (X and O) --}}
                <g class="animate-pulse" style="animation-duration: 8s;">
                    <text x="220" y="270" fill="white" font-size="40" font-family="Instrument Sans" font-weight="900" opacity="0.3">X</text>
                    <text x="730" y="730" fill="white" font-size="40" font-family="Instrument Sans" font-weight="900" opacity="0.3">O</text>
                </g>
            </svg>

            {{-- Floating blurred blobs --}}
            <div class="floating-objects">
                <div class="floating-ball w-[40rem] h-[40rem] top-[-10%] left-[-10%] opacity-20" style="background: radial-gradient(circle, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.4) 0%, transparent 70%);"></div>
                <div class="floating-ball w-[50rem] h-[50rem] bottom-[-15%] right-[-15%] opacity-15" style="background: radial-gradient(circle, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.3) 0%, transparent 70%);"></div>
            </div>
        </div>

        <style>
            :root {
                /* Brand tokens */
                --brand-navy: {{ $colors['navy'] ?? '#0b1f3a' }};
                --brand-navy-rgb: {{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }};
                --brand-blue: {{ $colors['blue'] ?? '#2563eb' }};
                --brand-blue-rgb: {{ $hexToRgb($colors['blue'] ?? '#2563eb') }};
                --brand-red: {{ $colors['red'] ?? '#e11d48' }};
                --brand-red-rgb: {{ $hexToRgb($colors['red'] ?? '#e11d48') }};
                --brand-red-hover: {{ $colors['red_hover'] ?? '#be123c' }};
                --brand-white: #ffffff;

                /* UI tokens */
                --ui-text: rgba(255, 255, 255, 0.92);
                --ui-text-muted: rgba(255, 255, 255, 0.65);
                --ui-border: rgba(255, 255, 255, 0.18);
                --ui-surface: rgba(255, 255, 255, 0.80);
                --ui-surface-elevated: rgba(255, 255, 255, 0.90);
                --ui-success: #22c55e;
                --ui-danger: #ef4444;
                --ui-warning: #f59e0b;
            }
        </style>

        <div class="ks-auth-container w-full max-w-[22rem] sm:max-w-[28rem] md:max-w-[32rem] relative z-10 py-10">
            <!-- Jazykový přepínač -->
            <div class="fixed top-0 right-0 z-[9999] p-4 md:p-6">
                <div class="flex items-center gap-1 p-1 bg-white/10 border border-white/20 backdrop-blur-xl rounded-full shadow-2xl">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'cs']) }}"
                       class="px-5 py-3 md:px-4 md:py-2 rounded-full text-[11px] md:text-xs font-black uppercase tracking-widest transition-all {{ app()->getLocale() === 'cs' ? 'bg-primary text-white shadow-lg' : 'text-white/60 hover:text-white hover:bg-white/20' }}"
                       aria-label="Čeština">CZ</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                       class="px-5 py-3 md:px-4 md:py-2 rounded-full text-[11px] md:text-xs font-black uppercase tracking-widest transition-all {{ app()->getLocale() === 'en' ? 'bg-primary text-white shadow-lg' : 'text-white/60 hover:text-white hover:bg-white/20' }}"
                       aria-label="English">EN</a>
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
