@extends('layouts.public')

@section('content')
    @if($page)
        <x-page-blocks :blocks="$page->content ?? []" animate="true" />
    @else
        <div class="bg-secondary text-white py-20 md:py-32 relative overflow-hidden">
            <div class="absolute inset-0 hero-mesh opacity-10"></div>
            <div class="container relative z-10 text-center">
                <h1 class="text-4xl md:text-6xl font-black mb-6 uppercase tracking-tighter leading-display">{{ __('general.home.welcome') }}</h1>
                <p class="text-xl text-white/70 mb-10 max-w-2xl mx-auto leading-relaxed text-balance">{{ __('general.home.no_content') }}</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('public.teams.index') }}" class="btn btn-primary btn-glow px-10">
                        {{ __('nav.team') }}
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-white px-10">
                        {{ __('nav.member_section') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="section-padding bg-bg">
            <div class="container">
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
