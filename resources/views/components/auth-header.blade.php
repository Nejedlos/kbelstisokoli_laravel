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
    @if($logoPath)
        <div class="auth-logo-wrapper mb-6">
            <img src="{{ asset('storage/' . $logoPath) }}" class="auth-logo-img" alt="{{ $clubName }}">
        </div>
    @else
        <div class="auth-icon-container mb-6">
            <i class="fa-duotone fa-light {{ $icon }} text-5xl text-primary icon-bounce icon-glow"></i>
        </div>
    @endif

    <h1 class="auth-title">{{ $title ?? $clubName }}</h1>
    @if($subtitle)
        <p class="auth-sub tracking-tight">{{ $subtitle }}</p>
    @endif
</div>
