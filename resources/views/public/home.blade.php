@extends('layouts.public')

@section('content')
    {{-- Hero Sekce --}}
    @php
        $heroData = [
            'eyebrow' => 'Sokol Kbely • C & E',
            'headline' => "Více než jen\nbasketbal.",
            'subheadline' => 'Hrajeme pro radost, bojujeme jako jeden tým. Přidej se k nám!',
            'cta_label' => 'Chci se přidat',
            'cta_url' => route('public.recruitment.index'),
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

    {{-- Novinky --}}
    <div class="section-padding bg-white">
        <div class="container">
            <x-section-heading
                :title="__('general.nav.news')"
                :subtitle="__('general.blocks.news_subtitle')"
                align="left"
            />
            <x-public.blocks.news_listing :data="['limit' => 3, 'show_button' => true]" />
        </div>
    </div>

    {{-- Zápasy --}}
    <div class="section-padding bg-slate-50 border-t border-slate-100">
        <div class="container">
            <x-section-heading
                :title="__('general.nav.matches')"
                :subtitle="__('general.blocks.matches_subtitle')"
                align="center"
            />
            <x-public.blocks.matches_listing :data="['limit' => 4, 'show_button' => true]" />
        </div>
    </div>

    {{-- Quick Facts --}}
    <div class="section-padding bg-white border-t border-slate-100">
        <div class="container">
            <x-section-heading
                :title="__('general.quick_facts_title')"
                :subtitle="__('general.quick_facts_subtitle')"
                align="center"
            />
            <x-quick-facts :branding="$branding ?? []" />
        </div>
    </div>
@endsection
