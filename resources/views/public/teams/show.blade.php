@extends('layouts.public')

@section('content')
    <x-page-header
        :title="$team->name"
        :subtitle="match($team->slug) {
            'muzi-a' => __('teams.detail.muzi_a_subtitle'),
            'muzi-b' => __('teams.detail.muzi_b_subtitle'),
            'muzi-c' => __('teams.detail.muzi_c_subtitle'),
            'muzi-e' => __('teams.detail.muzi_e_subtitle'),
            default => $team->name
        }"
        :breadcrumbs="[__('teams.breadcrumbs') => route('public.teams.index'), $team->name => null]"
        :image="'assets/img/teams/' . $team->slug . '-header.jpg'"
        alignment="left"
    />

    <div class="section-padding bg-white">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                {{-- Levý sloupec - O týmu --}}
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-black uppercase tracking-tight mb-6">{{ __('teams.detail.about') }}</h2>
                    <div class="prose prose-lg text-slate-600 max-w-none mb-12">
                        <p>{{ $team->description }}</p>
                        <p>{{ __('teams.detail.' . str_replace('-', '_', $team->slug) . '_about') }}</p>
                    </div>

                    <h3 class="text-2xl font-black uppercase tracking-tight mb-6">{{ __('teams.detail.suitable_for') }}</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
                        @foreach(__('teams.detail.' . str_replace('-', '_', $team->slug) . '_suitable') as $item)
                            <li class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl">
                                <i class="fa-light fa-check-circle text-primary"></i>
                                <span class="text-sm font-bold text-secondary">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <h3 class="text-2xl font-black uppercase tracking-tight mb-6">{{ __('teams.detail.how_to_join') }}</h3>
                    <div class="space-y-6 mb-12">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-black flex-shrink-0">1</div>
                            <div>
                                <h5 class="font-bold mb-1">{{ __('teams.detail.step_1_title') }}</h5>
                                <p class="text-slate-600 text-sm">{{ __('teams.detail.step_1_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-black flex-shrink-0">2</div>
                            <div>
                                <h5 class="font-bold mb-1">{{ __('teams.detail.step_2_title') }}</h5>
                                <p class="text-slate-600 text-sm">{{ __('teams.detail.step_2_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-black flex-shrink-0">3</div>
                            <div>
                                <h5 class="font-bold mb-1">{{ __('teams.detail.step_3_title') }}</h5>
                                <p class="text-slate-600 text-sm">{{ __('teams.detail.step_3_desc') }}</p>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-2xl font-black uppercase tracking-tight mb-6">{{ __('teams.detail.standings') ?? 'Tabulka soutěže' }}</h3>
                    <div class="mb-12">
                        @livewire('public-standings-table', ['teamId' => $team->id, 'showFilters' => false, 'compact' => true])
                    </div>

                    @if($team->rosterPlayers->isNotEmpty())
                        <h2 class="text-2xl font-black uppercase tracking-tight mb-6">{{ __('teams.detail.roster') }}</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-12">
                            @foreach($team->rosterPlayers as $playerProfile)
                                @php
                                    $user = $playerProfile->user;
                                    $photoUrl = $user->getPlayerPhotoUrl();
                                @endphp
                                <div class="card group flex flex-col h-full overflow-hidden border-b-4 border-slate-200 hover:border-primary transition-all duration-300">
                                    <div class="relative w-full aspect-[3/4] overflow-hidden bg-slate-100 shrink-0">
                                        <x-player-photo :user="$user" :photo-url="$photoUrl" size="md" />

                                        @if($playerProfile->jersey_number)
                                            <div class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-lg border border-slate-100">
                                                <span class="text-base font-black text-secondary">#{{ $playerProfile->jersey_number }}</span>
                                            </div>
                                        @endif

                                        @if($playerProfile->position)
                                            <div class="absolute bottom-3 left-3">
                                                <span class="badge badge-primary uppercase text-[9px] tracking-widest px-2 py-0.5 shadow-md">
                                                    {{ $playerProfile->position->value }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4 bg-white flex-grow">
                                        <h3 class="text-base font-black uppercase leading-tight group-hover:text-primary transition-colors mb-0.5">
                                            {{ $user->last_name }}
                                        </h3>
                                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">
                                            {{ $user->first_name }}
                                        </p>

                                        @if($playerProfile->height_cm || $playerProfile->position)
                                            <div class="mt-3 pt-3 border-t border-slate-50 flex items-center justify-between text-[10px] uppercase tracking-wider font-bold text-slate-400">
                                                <div class="flex items-center gap-1">
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
                    @endif
                </div>

                {{-- Pravý sloupec - Info panel --}}
                <div>
                    <div class="bg-slate-50 rounded-3xl p-8 sticky top-24">
                        <h4 class="text-xl font-black uppercase tracking-tight mb-6">{{ __('teams.detail.info') }}</h4>

                        <div class="space-y-6 mb-8">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-trophy text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.competition') }}</span>
                                    <span class="font-bold text-secondary">
                                        {{ __('teams.detail.' . str_replace('-', '_', $team->slug) . '_comp') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-user-plus text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.recruitment_status') }}</span>
                                    <span class="badge badge-success uppercase tracking-widest text-[10px]">{{ __('teams.detail.status_open') }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-calendar-clock text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.training_time') }}</span>
                                    <span class="font-bold text-secondary">{{ __('teams.detail.training_schedule') }}</span>
                                </div>
                            </div>

                            @if($team->coaches->isNotEmpty())
                                @foreach($team->coaches as $coach)
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                            <i class="fa-light fa-user-tie text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.coach') }}</span>
                                            <span class="font-bold text-secondary">{{ $coach->name }}</span>
                                        </div>
                                    </div>

                                    @if($coach->phone)
                                        <div class="flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                                <i class="fa-light fa-phone text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.phone') }}</span>
                                                <a href="tel:{{ str_replace(' ', '', $coach->phone) }}" class="font-bold text-secondary hover:text-primary transition-colors">{{ $coach->phone }}</a>
                                            </div>
                                        </div>
                                    @endif

                                    @if($coach->pivot->email || $coach->email)
                                        <div class="flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                                <i class="fa-light fa-envelope text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.email') }}</span>
                                                <a href="mailto:{{ $coach->pivot->email ?: $coach->email }}" class="font-bold text-secondary hover:text-primary transition-colors break-all text-sm">{{ $coach->pivot->email ?: $coach->email }}</a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                        <i class="fa-light fa-envelope text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.contact') }}</span>
                                        <a href="{{ route('public.recruitment.join', ['team' => $team->slug]) }}" class="font-bold text-primary hover:underline">{{ __('teams.detail.recruitment_contact') }}</a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3">
                            <a href="{{ route('public.recruitment.join', ['team' => $team->slug]) }}" class="btn btn-primary w-full">
                                <i class="fa-light fa-paper-plane mr-2"></i> {{ __('teams.detail.cta_join') }}
                            </a>
                            <a href="{{ route('public.matches.index') }}" class="btn btn-outline w-full border-slate-200 hover:border-primary">
                                <i class="fa-light fa-calendar-days mr-2"></i> {{ __('teams.detail.cta_matches') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Galerie --}}
    <div class="section-padding bg-bg overflow-hidden">
        <div class="container text-center">
            <x-section-heading
                title="{{ __('teams.detail.gallery') }}"
                :subtitle="__('teams.detail.gallery_subtitle')"
                alignment="center"
            />

            @if($randomPhotos->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($randomPhotos as $photo)
                        <a
                            href="{{ $photo->getUrl('optimized') }}"
                            class="spotlight group relative aspect-square overflow-hidden rounded-2xl bg-slate-200"
                            data-group="team-gallery"
                            data-caption="{{ $photo->title }}"
                        >
                            <img
                                src="{{ $photo->getUrl('thumb') }}"
                                alt="{{ $photo->alt_text ?: $photo->title }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-4">
                                <p class="text-white text-xs font-bold text-left line-clamp-2">
                                    {{ $photo->title }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-12">
                    <a href="{{ route('public.galleries.index') }}" class="btn btn-outline border-slate-200 hover:border-primary">
                        <i class="fa-light fa-images mr-2"></i> {{ __('teams.detail.view_all_galleries') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 opacity-40 grayscale group hover:grayscale-0 transition-all duration-500">
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse"></div>
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse delay-75"></div>
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse delay-150"></div>
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse delay-300"></div>
                </div>
                <p class="mt-8 text-slate-400 italic">{{ __('teams.detail.no_data') }}</p>
            @endif
        </div>
    </div>

    {{-- CTA --}}
    <div class="bg-primary py-16">
        <div class="container">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 text-white text-center md:text-left">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tight mb-2">{{ __('teams.detail.join_team_title', ['team' => $team->name]) }}</h2>
                    <p class="text-white/80">{{ __('teams.detail.join_team_desc') }}</p>
                </div>
                <a href="{{ route('public.recruitment.join', ['team' => $team->slug]) }}" class="btn bg-white text-primary hover:bg-secondary hover:text-white btn-lg">
                    {{ __('teams.detail.cta_join') }}
                </a>
            </div>
        </div>
    </div>
@endsection
