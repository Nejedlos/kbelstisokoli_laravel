@extends('layouts.public')

@section('content')
    {{-- 1. Hero Sekce (Vždy nahoře) --}}
    @if(isset($homePage) && !empty($homePage->content))
        @php
            $heroBlock = collect($homePage->content)->firstWhere('type', 'hero');
        @endphp
        @if($heroBlock)
            <x-page-blocks :blocks="[$heroBlock]" />
        @endif
    @else
        @php
            $heroData = [
                'eyebrow' => __('general.home_hero.eyebrow'),
                'headline' => __('general.home_hero.headline'),
                'subheadline' => __('general.home_hero.subheadline'),
                'cta_label' => __('general.home_hero.cta_label'),
                'cta_url' => route('public.news.index'),
                'cta_secondary_label' => __('general.home_hero.cta_secondary_label'),
                'cta_secondary_url' => route('public.teams.index'),
                'show_upcoming_events' => true,
                'variant' => 'standard',
                'alignment' => 'left',
                'overlay' => true,
                'image_url' => 'assets/img/home/home-hero-mobile.jpg',
            ];
        @endphp
        <x-public.blocks.hero :data="$heroData" />
    @endif

    {{-- 3. Tabulky soutěží (Vždy viditelné, hodně nahoře) --}}
    <div class="section-padding bg-white relative overflow-hidden border-b border-slate-100">
        <div class="container">
            <x-section-heading
                :title="__('general.standings_title') ?? 'Tabulky soutěží'"
                :subtitle="__('general.standings_subtitle') ?? 'Jak si vedou naše týmy v aktuální sezóně'"
                align="center"
            />
            <div class="max-w-4xl mx-auto">
                @livewire('public.standings-table', ['showFilters' => false, 'limit' => 5])

                <div class="mt-12 text-center">
                    <a href="{{ route('public.teams.index') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-secondary text-white font-black uppercase tracking-widest text-xs hover:bg-primary hover:shadow-xl hover:shadow-primary/20 transition-all active:scale-95">
                        {{ __('general.view_all_teams') ?? 'Prohlédnout všechny týmy' }}
                        <i class="fa-light fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Quick Facts (Základní informace - Vždy viditelné) --}}
    <div class="section-padding bg-slate-50 border-t border-slate-100 relative">
        {{-- Decoration --}}
        <div class="absolute bottom-0 left-0 w-64 h-64 opacity-5 pointer-events-none">
            <i class="fa-light fa-basketball text-[15rem] -translate-x-1/2 translate-y-1/2"></i>
        </div>

        <div class="container relative z-10">
            <x-section-heading
                :title="__('general.quick_facts_subtitle')"
                :subtitle="__('general.quick_facts_title')"
                align="center"
            />
            <x-quick-facts :branding="$branding ?? []" />
        </div>
    </div>

    {{-- 5. Dynamický obsah z CMS (Zbytek bloků kromě Hero) nebo Fallback --}}
    @if(isset($homePage) && !empty($homePage->content))
        @php
            $otherBlocks = collect($homePage->content)->reject(fn($b) => $b['type'] === 'hero')->values()->all();
        @endphp
        <x-page-blocks :blocks="$otherBlocks" />
    @else
        {{-- Fallback: Původní fixní struktura (zbytek) --}}

        {{-- Impact Stats --}}
        <x-public.blocks.impact_stats :data="[
            'title' => __('general.stats.title_main'),
            'subtitle' => __('general.stats.subtitle_main')
        ]" />

        {{-- Novinky --}}
        <div class="section-padding bg-white relative overflow-hidden">
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
            'title' => __('general.recruitment.funnel_title'),
            'subtitle' => __('general.recruitment.funnel_subtitle')
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
    @endif
@endsection
