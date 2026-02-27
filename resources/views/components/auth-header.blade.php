@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'fa-basketball-hoop',
])

@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $logoPath = $branding['logo_path'] ?? null;
    $clubName = $branding['club_name'] ?? 'Kbelští sokoli';
@endphp

<div class="auth-header-container animate-fade-in-down mb-6 md:mb-8">
    {{-- Decorative background icon (always visible, even when logo exists) --}}
    <div class="auth-header-visual mb-8" aria-hidden="true">
        <div class="auth-icon-aura"></div>
        <i class="fa-light {{ $icon }} auth-icon-bg animate-icon-drift"></i>
    </div>

    @if($logoPath)
        <div class="auth-logo-wrapper mb-6">
            <img src="{{ web_asset($logoPath) }}" class="auth-logo-img" alt="{{ $clubName }}">
        </div>
    @endif

    <h1 class="auth-title">{{ $title ?? $clubName }}</h1>
    @if($subtitle)
        <p class="auth-sub tracking-tight">{{ $subtitle }}</p>
    @endif
</div>
