@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'fa-basketball-hoop',
    'backLabel' => 'Zpět na přihlášení',
    'backUrl' => '/admin/login',
    'showBack' => true,
    // Responsivní maximální šířka formuláře (užší na mobilu, širší na desktopu)
    'maxWidth' => 'max-w-[22rem] sm:max-w-[26rem] md:max-w-[28rem]',
])

@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $colors = $branding['colors'];

    $hexToRgb = function($hex) {
        $hex = str_replace('#', '', $hex);
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

<div class="auth-gradient min-h-dvh flex items-start md:items-center justify-center py-10 px-4 md:px-6 lg:px-8 relative overflow-hidden"
     style="
        /* Výrazně světlejší varianta gradientu inspirovaná 'under-construction' vzhledem */
        background-color: #0f172a !important; /* slate-900 jako světlejší základ než téměř černá */
        background-image:
            radial-gradient(1200px 700px at 50% -10%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.50) 0%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.18) 40%, transparent 72%),
            radial-gradient(1400px 900px at 0% 100%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.28) 0%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.12) 50%, transparent 82%),
            radial-gradient(1200px 900px at 100% 100%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.26) 0%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.10) 55%, transparent 85%),
            radial-gradient(1000px 600px at 50% 10%, rgba(255, 255, 255, 0.12) 0%, transparent 70%)
        !important;
        background-attachment: fixed !important;
        background-size: cover !important;
    ">
    <style>
        :root {
            --color-primary: {{ $colors['red'] ?? '#e11d48' }};
            --color-primary-rgb: {{ $hexToRgb($colors['red'] ?? '#e11d48') }};
            --color-brand-red: {{ $colors['red'] ?? '#e11d48' }};
            --color-brand-red-rgb: {{ $hexToRgb($colors['red'] ?? '#e11d48') }};
            --color-brand-navy: {{ $colors['navy'] ?? '#0b1f3a' }};
            --color-brand-navy-rgb: {{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }};
            --color-brand-blue: {{ $colors['blue'] ?? '#2563eb' }};
            --color-brand-blue-rgb: {{ $hexToRgb($colors['blue'] ?? '#2563eb') }};
        }
    </style>
    {{-- Background Objects like in 2FA layout --}}
    <div class="floating-objects pointer-events-none">
        <div class="floating-ball w-64 h-64 top-[-10%] left-[-5%]"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] right-[-10%]"></div>
    </div>

    <div class="w-full {{ $maxWidth }} relative z-10">
        {{-- Language Switcher --}}
        <div class="flex justify-center mb-6 md:mb-8">
            @if (class_exists('BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch'))
                <livewire:filament-language-switch />
            @endif
        </div>

        {{-- Header --}}
        <x-auth-header :title="$title" :subtitle="$subtitle" :icon="$icon" />

        {{-- Form Surface --}}
        <div class="glass-card animate-fade-in-down" style="animation-delay: 0.1s">
            <div class="relative">
                {{ $slot }}
            </div>
        </div>

        {{-- Footer / Back Link --}}
        @if($showBack)
            <x-auth-footer :back-label="$backLabel" :back-url="$backUrl" :show-back="true" />
        @else
            <x-auth-footer :show-back="false" />
        @endif
    </div>
</div>
