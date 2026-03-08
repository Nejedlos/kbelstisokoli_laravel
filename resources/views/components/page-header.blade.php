@props([
    'title',
    'subtitle' => null,
    'image' => null,
    'alignment' => 'center',
    'breadcrumbs' => [],
    'showLogo' => true
])

@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $teamLogo = $branding['team_logo'] ?? null;
    $isLogoEnabled = $showLogo && ($teamLogo['enabled_page_headers'] ?? true);
@endphp

<div class="page-header bg-secondary text-white py-16 md:py-24 relative overflow-hidden">
    @if($image)
        <div class="absolute inset-0 z-0 opacity-30">
            <x-picture
                :src="$image"
                class="w-full h-full object-cover"
                alt="{{ $title }}"
                loading="eager"
                fetchpriority="high"
            />
        </div>
    @endif

    <div class="container relative z-10 text-{{ $alignment }}">
        @if($isLogoEnabled)
            <div class="mb-6 flex justify-{{ $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'end' : 'start') }}">
                <div class="bg-white p-2 rounded-full shadow-2xl border border-white/20 transition-all hover:scale-105 group/logo">
                    <picture class="transition-transform duration-500 group-hover/logo:rotate-3">
                        <source srcset="{{ web_asset($teamLogo['paths']['mini'] ?? '', true) }}" type="image/webp">
                        <img src="{{ web_asset($teamLogo['paths']['mini'] ?? '', false) }}"
                             alt="Kbelští sokoli C & E"
                             class="object-contain"
                             style="height: {{ $teamLogo['sizes']['page_header'] ?? 40 }}px; width: auto;">
                    </picture>
                </div>
            </div>
        @endif

        @if(!empty($breadcrumbs))
            <div class="mb-4 flex justify-{{ $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'end' : 'start') }}">
                <x-breadcrumbs :breadcrumbs="array_merge(['Úvod' => route('public.home')], $breadcrumbs)" variant="light" />
            </div>
        @endif

        <h1 class="text-4xl md:text-6xl font-black mb-4 uppercase tracking-tight leading-none">{{ $title }}</h1>

        @if($subtitle)
            <p class="text-lg md:text-xl text-slate-300 max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</div>
