@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'fa-basketball-hoop',
    'backLabel' => 'Zpět na přihlášení',
    'backUrl' => '/admin/login',
    'showBack' => true,
    'maxWidth' => 'max-w-[28rem]',
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

<div class="auth-gradient min-h-screen flex items-center justify-center p-6 relative overflow-hidden"
     style="background-color: #020617 !important; background-image: radial-gradient(circle at 50% -20%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.25) 0%, transparent 50%), radial-gradient(circle at 0% 100%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.6) 0%, transparent 60%), radial-gradient(circle at 100% 100%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.2) 0%, transparent 60%) !important;">
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
        <div class="flex justify-center mb-8">
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
