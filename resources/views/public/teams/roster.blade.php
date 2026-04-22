@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('teams.roster_title')"
        :subtitle="__('teams.roster_subtitle')"
        :breadcrumbs="[__('teams.breadcrumbs') => route('public.teams.index'), __('teams.roster_breadcrumbs') => null]"
        image="assets/img/hero/hero-teams.webp"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($teams->isEmpty())
                <div class="max-w-3xl mx-auto text-center py-20">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-light fa-users-slash fa-3x text-slate-300"></i>
                    </div>
                    <h2 class="text-2xl font-black uppercase mb-4">{{ __('teams.empty') }}</h2>
                    <p class="text-slate-500 mb-8">{{ __('teams.detail.no_data') }}</p>
                    <a href="{{ route('public.teams.index') }}" class="btn btn-primary">{{ __('nav.teams') }}</a>
                </div>
            @else
                @foreach($teams as $team)
                    <div class="mb-24 last:mb-0" id="team-{{ $team->slug }}">
                        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                            <div>
                                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight mb-4">
                                    {{ $team->name }}
                                </h2>
                                <div class="flex items-center gap-4">
                                    <div class="h-1 w-20 bg-primary"></div>
                                    <span class="badge badge-outline uppercase tracking-widest text-xs py-1 px-3">
                                        {{ $team->category === 'senior' ? __('teams.senior_title') : __('teams.youth_title') }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('public.teams.show', $team->slug) }}" class="btn btn-outline btn-sm">
                                {{ __('teams.view_detail') }}
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($team->rosterPlayers as $playerProfile)
                                @php
                                    $user = $playerProfile->user;
                                    $photoUrl = $user->getPlayerPhotoUrl();
                                @endphp
                                <div class="card group overflow-hidden border-b-4 border-slate-200 hover:border-primary transition-all duration-300">
                                    <div class="relative aspect-[3/4] overflow-hidden bg-slate-100">
                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}"
                                                 alt="{{ $user->name }}"
                                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 p-8">
                                                <i class="fa-light fa-user fa-5x mb-4 opacity-20"></i>
                                                <span class="text-xs uppercase font-bold tracking-widest opacity-40">Photo pending</span>
                                            </div>
                                        @endif

                                        @if($playerProfile->jersey_number)
                                            <div class="absolute top-4 right-4 w-12 h-12 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-lg border border-slate-100">
                                                <span class="text-xl font-black text-secondary">#{{ $playerProfile->jersey_number }}</span>
                                            </div>
                                        @endif

                                        @if($playerProfile->position)
                                            <div class="absolute bottom-4 left-4">
                                                <span class="badge badge-primary uppercase text-[10px] tracking-widest px-2 py-1 shadow-md">
                                                    {{ $playerProfile->position->value }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-5 bg-white">
                                        <h3 class="text-lg font-black uppercase leading-tight group-hover:text-primary transition-colors mb-1">
                                            {{ $user->last_name }}
                                        </h3>
                                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wide">
                                            {{ $user->first_name }}
                                        </p>

                                        @if($playerProfile->height_cm || $playerProfile->position)
                                            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between text-[11px] uppercase tracking-wider font-bold text-slate-400">
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fa-light fa-arrows-up-down text-primary"></i>
                                                    <span>{{ $playerProfile->height_cm ? $playerProfile->height_cm . ' cm' : '---' }}</span>
                                                </div>
                                                <div>
                                                    {{ $playerProfile->position ? $playerProfile->position->getLabel() : '' }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- CTA sekce pro nábor --}}
    <div class="section-padding pt-0 bg-bg">
        <div class="container">
            <div class="bg-secondary rounded-[2rem] p-8 md:p-12 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <i class="fa-light fa-basketball fa-7x"></i>
                </div>
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="text-center md:text-left">
                        <h2 class="text-2xl md:text-3xl font-black uppercase mb-3">{{ __('teams.detail.how_to_join') }}</h2>
                        <p class="text-slate-300 max-w-xl">
                            {{ __('recruitment.hero_subheadline') }}
                        </p>
                    </div>
                    <a href="{{ route('public.recruitment.index') }}" class="btn btn-primary btn-lg shrink-0">
                        {{ __('recruitment.cta_contact_us') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
