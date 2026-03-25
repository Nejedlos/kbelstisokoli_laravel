@extends('layouts.public')

@section('content')
    {{-- Dynamický obsah z CMS --}}
    @if(isset($homePage) && !empty($homePage->content))
        {{-- Dekorační prvky na pozadí pro desktop --}}
        <div class="hidden lg:block fixed left-4 top-1/2 -rotate-90 text-[10px] font-black tracking-[0.5em] opacity-20 uppercase pointer-events-none select-none z-0">
            Est. 1921 • Kbely Basketball • Sokol Kbely
        </div>

        <x-page-blocks :blocks="$homePage->content" />
    @else
        {{-- Fallback: Původní fixní struktura (pro případ, že není v DB žádná homepage) --}}

        {{-- Dekorační prvky na pozadí pro desktop --}}
        <div class="hidden lg:block fixed left-4 top-1/2 -rotate-90 text-[10px] font-black tracking-[0.5em] opacity-20 uppercase pointer-events-none select-none z-0">
            Est. 1921 • Kbely Basketball • Sokol Kbely
        </div>

        {{-- Hero Sekce --}}
        @php
            $heroData = [
                'eyebrow' => 'Sokol Kbely • C & E',
                'headline' => "Více než jen\nbasketbal.",
                'subheadline' => 'Hrajeme pro radost, bojujeme jako jeden tým. Přidej se k nám!',
                'cta_label' => 'Chci se přidat',
                'cta_url' => route('public.news.index'),
                'cta_secondary_label' => 'Naše týmy',
                'cta_secondary_url' => route('public.teams.index'),
                'show_upcoming_events' => true,
                'variant' => 'standard',
                'alignment' => 'left',
                'overlay' => true,
                'image_url' => 'assets/img/home/home-hero-mobile.jpg',
            ];
        @endphp
        <x-public.blocks.hero :data="$heroData" />

        {{-- Partner Strip --}}
        <x-partner-strip />

        {{-- Impact Stats --}}
        <x-public.blocks.impact_stats :data="[
            'title' => __('general.stats.title_main'),
            'subtitle' => __('general.stats.subtitle_main')
        ]" />

        {{-- Novinky --}}
        <div class="section-padding bg-white relative overflow-hidden">
            {{-- Decoration --}}
            <div class="absolute top-0 right-0 w-1/3 h-full bg-slate-50 -skew-x-12 translate-x-1/2 pointer-events-none z-0"></div>

            <div class="container relative z-10">
                <x-section-heading
                    :title="__('general.nav.news')"
                    :subtitle="__('general.blocks.news_subtitle')"
                    align="left"
                />
                <x-public.blocks.news_listing :data="['limit' => 3, 'show_button' => true]" />
            </div>
        </div>

        {{-- Featured Recruitment --}}
        <x-public.blocks.featured_split :data="[
            'alignment' => 'left',
            'image_url' => 'assets/img/home/recruitment-split.jpg',
            'title' => __('general.recruitment.home_title'),
            'subtitle' => __('general.recruitment.home_subtitle')
        ]" />

        {{-- Zápasy --}}
        <div class="section-padding bg-slate-900 text-white relative">
            <div class="container">
                <div class="mb-12">
                    <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tighter mb-4 text-center">
                        {{ __('general.nav.matches') }}
                    </h2>
                    <p class="text-slate-400 text-center max-w-2xl mx-auto font-medium">
                        {{ __('general.blocks.matches_subtitle') }}
                    </p>
                </div>
                <x-public.blocks.matches_listing :data="['limit' => 4, 'show_button' => true]" />
            </div>
        </div>

        {{-- Social Mosaic --}}
        <x-public.blocks.social_mosaic :data="[]" />

        {{-- Quick Facts --}}
        <div class="section-padding bg-slate-50 border-t border-slate-100 relative">
             {{-- Decoration --}}
            <div class="absolute bottom-0 left-0 w-64 h-64 opacity-5 pointer-events-none">
                <i class="fa-light fa-basketball text-[15rem] -translate-x-1/2 translate-y-1/2"></i>
            </div>

            <div class="container relative z-10">
                <x-section-heading
                    :title="__('general.quick_facts_title')"
                    :subtitle="__('general.quick_facts_subtitle')"
                    align="center"
                />
                <x-quick-facts :branding="$branding ?? []" />
            </div>
        </div>
    @endif
@endsection
