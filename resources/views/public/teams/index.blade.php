@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('teams.title')"
        :subtitle="__('teams.subtitle')"
        :breadcrumbs="[__('teams.breadcrumbs') => null]"
        image="assets/img/teams/teams-header.jpg"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            {{-- Hlavní týmy s náborem --}}
            @if($mainTeams->isNotEmpty())
                <div class="mb-20">
                    <x-section-heading
                        :title="__('teams.main_teams')"
                        alignment="center"
                        class="mb-10"
                    />
                    <div class="flex flex-wrap justify-center gap-8">
                        @foreach($mainTeams as $team)
                            <div class="w-full md:w-[calc(50%-1rem)] lg:w-[calc(50%-1rem)] max-w-md flex">
                                <div class="card card-hover group flex flex-col h-full border-t-4 {{ $loop->index % 2 == 0 ? 'border-primary' : 'border-secondary' }} w-full">
                                    <div class="p-8 flex-1 flex flex-col">
                                        <div class="flex justify-between items-start mb-6 gap-4">
                                            <h3 class="text-2xl font-black uppercase tracking-tighter group-hover:text-primary transition-colors leading-tight">
                                                {{ $team->name }}
                                            </h3>
                                            <span class="badge badge-outline uppercase tracking-widest text-[10px] py-1 px-3 shrink-0">
                                                {{ match($team->slug) {
                                                    'muzi-a' => '2. liga A',
                                                    'muzi-b' => 'Přebor A',
                                                    'muzi-c' => 'Přebor B',
                                                    'muzi-d' => '1. třída',
                                                    'muzi-e' => '3. třída B',
                                                    default => ''
                                                } }}
                                            </span>
                                        </div>

                                        <p class="text-slate-600 mb-8 leading-relaxed text-sm flex-1">
                                            {{ $team->description }}
                                        </p>

                                        <div class="space-y-4 mb-8">
                                            <div class="flex items-start gap-3">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    <i class="fa-light fa-medal text-xs text-primary"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.competition') }}</span>
                                                    <span class="text-sm font-bold text-secondary">
                                                        {{ match($team->slug) {
                                                            'muzi-a' => '2. liga (skupina A)',
                                                            'muzi-b' => 'Pražský přebor',
                                                            'muzi-c' => 'Pražský přebor B',
                                                            'muzi-d' => '1. třída',
                                                            'muzi-e' => '3. třída B',
                                                            default => ''
                                                        } }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-3">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    <i class="fa-light fa-users text-xs text-primary"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.player_type') }}</span>
                                                    <span class="text-sm font-bold text-secondary">
                                                        {{ __('teams.detail.' . str_replace('-', '_', $team->slug) . '_type') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-3">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    <i class="fa-light fa-bolt text-xs text-primary"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.tempo_atmos') }}</span>
                                                    <span class="text-sm font-bold text-secondary">
                                                        {{ __('teams.detail.' . str_replace('-', '_', $team->slug) . '_tempo') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('public.teams.show', $team->slug) }}" class="btn {{ $loop->index % 2 == 0 ? 'btn-primary' : 'btn-secondary' }} w-full">
                                            {{ __('teams.view_detail') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Ostatní týmy --}}
            @if($otherTeams->isNotEmpty())
                <div class="mb-24">
                    <div class="max-w-3xl mx-auto text-center mb-10">
                        <h3 class="text-2xl font-black uppercase tracking-tighter">{{ __('teams.other_teams') }}</h3>
                        <div class="w-16 h-1 bg-primary mx-auto mt-4"></div>
                    </div>
                    <div class="flex flex-wrap justify-center gap-6">
                        @foreach($otherTeams as $team)
                            <div class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)] max-w-sm flex">
                                <div class="card card-hover group flex flex-col h-full border-t-2 border-slate-200 w-full">
                                    <div class="p-6 flex-1 flex flex-col">
                                        <div class="flex justify-between items-start mb-4 gap-4">
                                            <h4 class="text-xl font-black uppercase tracking-tighter group-hover:text-primary transition-colors leading-tight">
                                                {{ $team->name }}
                                            </h4>
                                            <span class="badge badge-outline uppercase tracking-widest text-[9px] py-0.5 px-2 shrink-0">
                                                {{ match($team->slug) {
                                                    'muzi-a' => '2. liga A',
                                                    'muzi-b' => 'Přebor A',
                                                    'muzi-c' => 'Přebor B',
                                                    'muzi-d' => '1. třída',
                                                    'muzi-e' => '3. třída B',
                                                    default => ''
                                                } }}
                                            </span>
                                        </div>

                                        <p class="text-slate-500 mb-6 leading-relaxed text-xs flex-1">
                                            {{ Str::limit($team->description, 100) }}
                                        </p>

                                        <a href="{{ route('public.teams.show', $team->slug) }}" class="btn btn-outline btn-sm w-full">
                                            {{ __('teams.view_detail') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pro koho je který tým vhodný --}}
            <div class="mb-20">
                <x-section-heading
                    :title="__('teams.detail.suitable_title')"
                    :subtitle="__('teams.detail.suitable_subtitle')"
                    alignment="center"
                />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-8 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-6">
                            <i class="fa-light fa-basketball fa-2x text-primary"></i>
                        </div>
                        <h4 class="text-xl font-black uppercase mb-3">{{ __('teams.detail.suitable_regular') }}</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ __('teams.detail.suitable_regular_desc') }}</p>
                    </div>

                    <div class="p-8 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mb-6">
                            <i class="fa-light fa-comments fa-2x text-secondary"></i>
                        </div>
                        <h4 class="text-xl font-black uppercase mb-3">{{ __('teams.detail.suitable_return') }}</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ __('teams.detail.suitable_return_desc') }}</p>
                    </div>

                    <div class="p-8 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mb-6">
                            <i class="fa-light fa-user-group fa-2x text-slate-400"></i>
                        </div>
                        <h4 class="text-xl font-black uppercase mb-3">{{ __('teams.detail.suitable_crew') }}</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ __('teams.detail.suitable_crew_desc') }}</p>
                    </div>
                </div>
            </div>

            {{-- Jak se přidat --}}
            <div class="bg-secondary rounded-[2rem] p-8 md:p-16 text-white relative overflow-hidden mb-20">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <i class="fa-light fa-file-signature fa-9x"></i>
                </div>

                <div class="relative z-10 max-w-3xl">
                    <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tighter mb-6">{{ __('teams.detail.how_to_join') }}</h2>
                    <p class="text-lg text-slate-300 mb-10 leading-relaxed">
                        {{ __('recruitment.hero_subheadline') }}
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('public.recruitment.index') }}"
                           class="btn btn-primary btn-lg"
                           data-track-click="conversion_intent"
                           data-track-label="Join Us - Section"
                           data-track-category="conversion">{{ __('recruitment.cta_contact_us') }}</a>
                        <a href="{{ $branding['recruitment_url'] }}"
                           target="_blank"
                           rel="noopener"
                           class="btn btn-outline-white btn-lg group"
                           data-track-click="external_link"
                           data-track-label="Youth Recruitment - Section"
                           data-track-category="external">
                            <span>{{ __('recruitment.youth_recruitment_cta') }}</span>
                            <i class="fa-light fa-arrow-up-right ml-2 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- FAQ --}}
            <div class="max-w-3xl mx-auto">
                <h2 class="text-2xl font-black uppercase text-center mb-10">{{ __('teams.detail.faq_title') }}</h2>

                <div class="space-y-4">
                    <div class="p-6 bg-white rounded-xl border border-slate-100">
                        <h5 class="font-bold mb-2">{{ __('teams.detail.faq_exp_q') }}</h5>
                        <p class="text-slate-600 text-sm">{{ __('teams.detail.faq_exp_a') }}</p>
                    </div>

                    <div class="p-6 bg-white rounded-xl border border-slate-100">
                        <h5 class="font-bold mb-2">{{ __('teams.detail.faq_try_q') }}</h5>
                        <p class="text-slate-600 text-sm">{{ __('teams.detail.faq_try_a') }}</p>
                    </div>

                    <div class="p-6 bg-white rounded-xl border border-slate-100">
                        <h5 class="font-bold mb-2">{{ __('teams.detail.faq_contact_q') }}</h5>
                        <p class="text-slate-600 text-sm">{{ __('teams.detail.faq_contact_a') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
