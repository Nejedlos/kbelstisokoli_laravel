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

<style>
    :root {
        --color-primary: {{ $colors['red'] ?? '#e11d48' }};
        --color-primary-hover: {{ $colors['red_hover'] ?? '#be123c' }};
    }
</style>

<div class="fi-simple-layout min-h-screen flex items-center justify-center p-6 relative overflow-hidden bg-slate-950">
    {{-- Animované pozadí --}}
    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-10%] left-[-5%] w-[40rem] h-[40rem] bg-rose-600/10 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-15%] right-[-10%] w-[50rem] h-[50rem] bg-blue-600/5 rounded-full blur-[150px]"></div>
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
