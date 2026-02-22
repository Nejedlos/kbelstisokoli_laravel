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
@endphp

<div class="auth-gradient fi-simple-layout min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <style>
        :root {
            --color-primary: {{ $colors['red'] ?? '#e11d48' }};
            --color-primary-hover: {{ $colors['red_hover'] ?? '#be123c' }};
            --color-brand-red: {{ $colors['red'] ?? '#e11d48' }};
        }
    </style>
    {{-- Background Objects like in 2FA layout --}}
    <div class="floating-objects pointer-events-none">
        <div class="floating-ball w-64 h-64 top-[-10%] left-[-5%]"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] right-[-10%] opacity-5"></div>
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
