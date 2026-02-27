@props([
    'showBack' => true,
    'backUrl' => '/',
    'backLabel' => 'Zpět na hlavní stránku',
])

@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $clubShortName = $branding['club_short_name'] ?? 'Sokoli';
@endphp

<div class="mt-12 text-center animate-fade-in space-y-8" style="animation-delay: 0.4s">
    @if($showBack)
        <a href="{{ $backUrl }}" class="auth-footer-link-primary flex items-center justify-center gap-2 mx-auto group">
            <i class="fa-light fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            {{ $backLabel }}
        </a>
    @endif

    <div class="flex items-center justify-center gap-6 text-slate-400">
        <div class="h-px w-10 bg-gradient-to-r from-transparent to-white/10"></div>
        <p class="text-[min(2.5vw,10px)] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.4em] italic opacity-40 text-balance px-4 max-w-xs mx-auto">
            {{ $branding['club_name'] ?? 'Kbelští sokoli' }}
        </p>
        <div class="h-px w-10 bg-gradient-to-l from-transparent to-white/10"></div>
    </div>
</div>
