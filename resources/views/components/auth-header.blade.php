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

<div class="auth-header-container animate-fade-in-down mb-8">
    {{-- Decorative background icon (always visible, even when logo exists) --}}
    <div class="auth-header-visual mb-8" aria-hidden="true">
        <i class="fa-duotone fa-light {{ $icon }} auth-icon-bg"></i>
    </div>

    @if($logoPath)
        <div class="auth-logo-wrapper mb-6">
            <img src="{{ asset('storage/' . $logoPath) }}" class="auth-logo-img" alt="{{ $clubName }}">
        </div>
    @endif

    <h1 class="auth-title">{{ $title ?? $clubName }}</h1>
    @if($subtitle)
        <p class="auth-sub tracking-tight">{{ $subtitle }}</p>
    @endif
</div>
