@cacheFragment('block_hero_' . ($data['media_asset_id'] ?? 'none') . '_' . md5($data['headline'] ?? '') . '_' . app()->getLocale(), 3600)
@php
    $asset = isset($data['media_asset_id']) ? \App\Models\MediaAsset::find($data['media_asset_id']) : null;
    $imageUrl = $asset ? $asset->getUrl('large') : ($data['image_url'] ?? null);
    $videoUrl = $data['video_url'] ?? null;
    $variant = $data['variant'] ?? 'standard';
    $alignment = $data['alignment'] ?? ($variant === 'centered' ? 'center' : 'left');

    $webmUrl = $videoUrl && str_contains($videoUrl, '.mp4') ? str_replace('.mp4', '.webm', $videoUrl) : null;
@endphp

<section @class([
    'block-hero relative overflow-hidden min-h-[60vh] flex items-center',
    'bg-secondary text-white' => $variant !== 'minimal',
    'bg-white text-secondary' => $variant === 'minimal',
    'hero-gradient' => $variant === 'standard' && !$imageUrl && !$videoUrl,
    'py-20 md:py-32' => $variant === 'centered',
    'py-16 md:py-24' => $variant !== 'centered',
]) x-data="{ videoLoaded: false }">
    {{-- Background Image / Video / Overlay --}}
    @if(($imageUrl || $videoUrl) && $variant !== 'minimal')
        <div class="absolute inset-0 z-0 bg-secondary">
            {{-- Image-First: Prioritní <x-picture> element pro mobil i desktop --}}
            <x-picture
                :src="$imageUrl"
                class="absolute inset-0 w-full h-full object-cover"
                :alt="$asset->alt_text ?? ($data['headline'] ?? '')"
                decoding="async"
                fetchpriority="high"
                loading="eager"
                sizes="100vw"
            />

            @if($videoUrl)
                {{-- Video-Later: Optimalizované načítání videa --}}
                <div class="hidden sm:block">
                    <video
                        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 js-hero-video"
                        :class="videoLoaded ? 'opacity-100' : 'opacity-0'"
                        autoplay
                        muted
                        loop
                        playsinline
                        poster="{{ $imageUrl }}"
                        preload="auto"
                        aria-hidden="true"
                    >
                        @if($webmUrl)
                            <source src="{{ asset($webmUrl) }}" type="video/webm">
                        @endif
                        <source src="{{ asset($videoUrl) }}" type="video/mp4">
                    </video>
                </div>

                <script>
                    (function(){
                        const initVideo = function() {
                            const video = document.querySelector('.js-hero-video');
                            if (!video) return;

                            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                            if (prefersReduced || window.innerWidth < 640) {
                                video.remove();
                                return;
                            }

                            // Pokud už video hraje nebo je načtené, rovnou zobrazíme
                            if (video.readyState >= 3) {
                                video.dispatchEvent(new CustomEvent('video-ready'));
                            }

                            video.addEventListener('loadeddata', function() {
                                // Vyvoláme event pro Alpine
                                const section = video.closest('.block-hero');
                                if (section && section.__x) {
                                    section._x_dataStack[0].videoLoaded = true;
                                } else {
                                    // Fallback bez Alpine
                                    video.style.opacity = '1';
                                }
                            }, { once: true });

                            // Záložní zobrazení po 2 sekundách, i kdyby event nepřišel
                            setTimeout(() => {
                                const section = video.closest('.block-hero');
                                if (section && section.__x) {
                                    section._x_dataStack[0].videoLoaded = true;
                                } else {
                                    video.style.opacity = '1';
                                }
                            }, 2000);
                        };

                        if (document.readyState !== 'loading') {
                            initVideo();
                        } else {
                            document.addEventListener('DOMContentLoaded', initVideo);
                        }
                    })();
                </script>
            @endif

            @if($data['overlay'] ?? true)
                <div class="absolute inset-0 bg-gradient-to-r from-secondary/95 via-secondary/70 to-transparent z-[1]"></div>
            @endif
        </div>
    @elseif($variant === 'standard')
        <div class="absolute inset-0 z-0 hero-mesh opacity-20"></div>
    @endif

    {{-- Branding Watermark --}}
    @php
        $branding = app(\App\Services\BrandingService::class)->getSettings();
        $teamLogo = $branding['team_logo'] ?? null;
    @endphp

    @if($teamLogo['enabled_hero_watermark'] ?? false)
        <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center overflow-hidden">
            <picture class="opacity-[var(--watermark-opacity)] transition-opacity duration-1000" style="--watermark-opacity: {{ $teamLogo['watermark_opacity'] ?? 0.08 }}">
                <source srcset="{{ web_asset($teamLogo['paths']['velke'] ?? '', true) }}" type="image/webp">
                <img src="{{ web_asset($teamLogo['paths']['velke'] ?? '', false) }}" alt="" class="w-[120%] h-[120%] object-contain max-w-none grayscale brightness-200">
            </picture>
        </div>
    @endif

    {{-- Decor elements for "sexy" look --}}
    @if($variant !== 'minimal')
        <div class="absolute top-0 right-0 w-1/2 h-full bg-primary/5 -skew-x-12 translate-x-1/3 pointer-events-none z-0"></div>
        <div class="absolute bottom-0 right-0 w-1/4 h-1/2 border-r-[16px] border-b-[16px] border-primary/10 mr-8 mb-8 pointer-events-none z-0"></div>
        <div class="absolute top-1/2 left-0 w-64 h-64 bg-primary/10 blur-[120px] rounded-full pointer-events-none z-0"></div>
    @endif

    <div class="container relative z-10">
        @if(isset($breadcrumbs) && $breadcrumbs)
            <div @class([
                'mb-4',
                'flex justify-center' => $alignment === 'center',
                'flex justify-end' => $alignment === 'right',
            ])>
                <x-breadcrumbs :breadcrumbs="$breadcrumbs" variant="light" />
            </div>
        @endif

        <div @class([
            'max-w-3xl' => $alignment === 'left',
            'mx-auto text-center max-w-4xl' => $alignment === 'center',
            'ml-auto text-right max-w-3xl' => $alignment === 'right',
        ])>
            @if($data['eyebrow'] ?? null)
                <div @class([
                    'mb-10 inline-flex items-center px-4 py-2.5 sm:px-6 sm:py-3 rounded-full text-[11px] sm:text-[13px] font-display font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] max-w-[calc(100vw-2rem)] sm:max-w-full overflow-hidden leading-tight sm:leading-none shadow-2xl transition-all duration-300 hover:scale-[1.02] group/badge',
                    'bg-white/95 backdrop-blur-sm text-secondary shadow-black/40' => $variant !== 'minimal',
                    'bg-white text-secondary border border-secondary/10 shadow-secondary/10' => $variant === 'minimal',
                ])>
                    @if($teamLogo['enabled_hero'] ?? true)
                        <picture class="mr-3 sm:mr-5 shrink-0 transition-transform duration-500 group-hover/badge:rotate-3"
                                 style="opacity: {{ $teamLogo['hero_opacity'] ?? 1.0 }};">
                            <source srcset="{{ web_asset($teamLogo['paths']['mini'] ?? '', true) }}" type="image/webp">
                            <img src="{{ web_asset($teamLogo['paths']['mini'] ?? '', false) }}"
                                 alt="Kbelští sokoli"
                                 class="object-contain {{ $teamLogo['shadow_enabled'] ? 'drop-shadow-xl' : '' }}"
                                 style="height: clamp(56px, 10vw, {{ $teamLogo['sizes']['hero'] }}px); width: auto; border-radius: {{ $teamLogo['border_radius'] === 'full' ? '9999px' : ($teamLogo['border_radius'] === 'lg' ? '1rem' : ($teamLogo['border_radius'] === 'md' ? '0.5rem' : ($teamLogo['border_radius'] === 'sm' ? '0.25rem' : '0'))) }};"
                            >
                        </picture>
                    @else
                        <span class="relative flex h-2 w-2 mr-4 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                        </span>
                    @endif
                    <span class="relative block text-pretty">
                        @if(str_contains($data['eyebrow'], '•'))
                            @php
                                [$part1, $part2] = explode('•', $data['eyebrow'], 2);
                            @endphp
                            <span class="inline">{{ trim($part1) }}</span>
                            <span class="mx-1.5 opacity-60">•</span>
                            <span class="inline text-primary">{{ trim($part2) }}</span>
                        @else
                            {{ $data['eyebrow'] }}
                        @endif
                    </span>
                </div>
            @endif

            @if($data['headline'] ?? null)
                <h1 class="text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-black mb-6 leading-display uppercase tracking-tight text-balance">
                    {!! nl2br(e($data['headline'])) !!}
                </h1>
            @endif

            @if($data['subheadline'] ?? null)
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl mb-10 text-white/80 font-medium leading-relaxed italic border-l-4 border-primary pl-4 md:pl-6 py-2 text-balance">
                    {{ $data['subheadline'] }}
                </p>
            @endif

            @php
                $showEvents = $data['show_upcoming_events'] ?? false;
                $upcomingEvents = $showEvents ? \App\Support\HeroEventsHelper::getUpcomingEvents() : collect();
            @endphp

            @if($showEvents && $upcomingEvents->isNotEmpty())
                <div class="mb-10 {{ $alignment === 'center' ? 'text-center' : ($alignment === 'right' ? 'text-right' : 'text-left') }}">
                    <div class="inline-flex items-center gap-2 mb-4">
                        <span class="w-8 h-px bg-primary/40"></span>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary/80">
                            {{ __('general.upcoming_events_title') }}
                        </h4>
                        <span class="w-8 h-px bg-primary/40"></span>
                    </div>

                    <div class="flex flex-col gap-4 max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '') }}">
                        @foreach($upcomingEvents as $event)
                            <a href="{{ $event['url'] }}" class="group/event relative flex items-center gap-5 bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-5 transition-all duration-500 hover:bg-white/10 hover:border-primary/40 hover:-translate-y-1.5 shadow-2xl overflow-hidden">
                                {{-- Background glow on hover --}}
                                <div class="absolute -inset-1 bg-gradient-to-r from-primary/0 via-primary/5 to-primary/0 opacity-0 group-hover/event:opacity-100 transition-opacity duration-700 blur-xl pointer-events-none"></div>

                                {{-- Date Box --}}
                                <div class="relative flex flex-col items-center justify-center w-16 h-16 shrink-0 rounded-2xl bg-white/5 text-white border border-white/10 group-hover/event:bg-primary group-hover/event:text-white group-hover/event:border-primary transition-all duration-500 shadow-lg">
                                    <span class="text-[11px] font-black uppercase leading-none mb-1.5 opacity-60 group-hover/event:opacity-100 tracking-wider">{{ $event['date']->translatedFormat('D') }}</span>
                                    <span class="text-xl font-black leading-none">{{ $event['date']->format('d.m.') }}</span>
                                </div>

                                {{-- Content --}}
                                <div class="relative flex flex-col min-w-0 pr-4 sm:pr-8">
                                    <div class="flex items-center gap-3 mb-1.5">
                                        <span class="text-[11px] font-black uppercase tracking-widest text-primary">{{ $event['team_short'] }}</span>
                                        <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                        @if($event['date']->isToday())
                                            <div class="flex items-center gap-1.5">
                                                <span class="relative flex h-2 w-2">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                                                </span>
                                                <span class="text-[11px] font-black uppercase text-primary animate-pulse">{{ __('general.today') }}</span>
                                            </div>
                                        @else
                                            <span class="text-[11px] font-bold text-white/50 group-hover/event:text-white/80 transition-colors">{{ $event['date']->format('H:i') }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-base md:text-xl font-black text-white group-hover/event:text-primary transition-colors mb-1 leading-tight">
                                        @if($event['type'] === 'match')
                                            <i class="fa-light fa-basketball mr-2 opacity-60 group-hover/event:rotate-[15deg] transition-transform duration-500 inline-block"></i>
                                            {{ $event['title'] }}
                                        @else
                                            <i class="fa-light fa-calendar-star mr-2 opacity-60 group-hover/event:scale-110 transition-transform duration-500 inline-block"></i>
                                            {{ $event['title'] }}
                                        @endif
                                    </h3>
                                    @if($event['location'])
                                        <div class="flex items-center gap-1.5 text-[11px] text-white/40 group-hover/event:text-white/60 transition-colors">
                                            <i class="fa-light fa-location-dot shrink-0"></i>
                                            <span class="line-clamp-1">{{ $event['location'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Arrow --}}
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 opacity-0 -translate-x-4 group-hover/event:opacity-100 group-hover/event:translate-x-0 transition-all duration-500 text-primary hidden md:block">
                                    <i class="fa-light fa-arrow-right text-2xl"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(($data['cta_label'] ?? null) || ($data['cta_secondary_label'] ?? null) || ($data['cta_tertiary_label'] ?? null))
                <div class="flex flex-wrap items-center gap-4 sm:gap-6 {{ $alignment === 'center' ? 'justify-center' : ($alignment === 'right' ? 'justify-end' : '') }}">
                    @if($data['cta_label'] ?? null)
                        <a href="{{ $data['cta_url'] ?? '#' }}"
                           class="btn btn-primary btn-glow group w-full sm:w-auto"
                           data-track-click="hero_cta"
                           data-track-label="Primary: {{ $data['cta_label'] }}"
                           data-track-category="conversion">
                            <span>{{ $data['cta_label'] }}</span>
                            <i class="fa-light fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @endif

                    @if($data['cta_secondary_label'] ?? null)
                        @php $isExternalSecondary = str_contains($data['cta_secondary_url'] ?? '', 'basketkbely.cz'); @endphp
                        <a href="{{ $data['cta_secondary_url'] ?? '#' }}"
                           @if($isExternalSecondary) target="_blank" rel="noopener" @endif
                           class="btn btn-outline-white w-full sm:w-auto group"
                           data-track-click="{{ $isExternalSecondary ? 'external_link' : 'hero_cta' }}"
                           data-track-label="Secondary: {{ $data['cta_secondary_label'] }}"
                           data-track-category="{{ $isExternalSecondary ? 'external' : 'engagement' }}">
                            <span>{{ $data['cta_secondary_label'] }}</span>
                            @if($isExternalSecondary)
                                <i class="fa-light fa-arrow-up-right ml-2 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform opacity-70"></i>
                            @endif
                        </a>
                    @endif

                    @if($data['cta_tertiary_label'] ?? null)
                        <a href="{{ $data['cta_tertiary_url'] ?? '#' }}"
                           target="_blank"
                           rel="noopener"
                           class="inline-flex items-center gap-4 text-[min(4.2vw,0.875rem)] sm:text-sm font-black uppercase tracking-widest-responsive text-white hover:text-white transition-all group py-4 px-5 sm:py-3 sm:px-6 bg-secondary/80 sm:bg-secondary/40 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl mt-4 sm:mt-0 leading-tight"
                           data-track-click="external_link"
                           data-track-label="Tertiary: {{ $data['cta_tertiary_label'] }}"
                           data-track-category="external">
                            <span class="relative border-b border-white/30 group-hover:border-primary transition-colors pb-1 leading-tight text-balance">
                                {{ $data['cta_tertiary_label'] }}
                            </span>
                            <span class="w-12 h-12 sm:w-10 sm:h-10 rounded-full bg-primary flex items-center justify-center shrink-0 transition-all duration-300 group-hover:scale-110 shadow-lg shadow-primary/40">
                                <i class="fa-light fa-arrow-up-right text-lg sm:text-sm text-white group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform duration-300"></i>
                            </span>
                        </a>
                    @endif
                </div>

                @if($data['microtext'] ?? null)
                    <div class="mt-6 text-sm text-white/60 max-w-xl {{ $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '') }} leading-snug">
                        {{ $data['microtext'] }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Basketball graphic element --}}
    @if($variant === 'standard')
        <div class="hidden lg:block absolute right-12 top-1/2 -translate-y-1/2 w-1/3 opacity-20 pointer-events-none group-hover:rotate-45 transition-transform duration-1000">
            <i class="fa-light fa-basketball text-[25rem] rotate-12 text-primary/20"></i>
        </div>
    @endif
</section>
@endCacheFragment

{{-- Partner Strip --}}
<x-partner-strip />
