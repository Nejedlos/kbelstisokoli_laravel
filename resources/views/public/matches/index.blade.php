@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('matches.title')"
        :subtitle="__('matches.subtitle')"
        :breadcrumbs="[__('matches.breadcrumbs') => null]"
    />

    <div class="bg-slate-50 border-b border-slate-200">
        <div class="container">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('public.matches.index', array_merge(request()->query(), ['type' => 'upcoming'])) }}"
                       class="py-6 border-b-2 font-black uppercase tracking-widest text-sm transition-colors {{ $type === 'upcoming' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary' }}">
                        {{ __('matches.upcoming') }}
                    </a>
                    <a href="{{ route('public.matches.index', array_merge(request()->query(), ['type' => 'latest'])) }}"
                       class="py-6 border-b-2 font-black uppercase tracking-widest text-sm transition-colors {{ $type === 'latest' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary' }}">
                        {{ __('matches.latest') }}
                    </a>
                </div>

                <form action="{{ route('public.matches.index') }}" method="GET" class="flex flex-wrap items-center gap-3 py-4 md:py-0">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <select name="team_id" class="bg-white border-slate-200 rounded-lg text-xs font-bold text-secondary focus:ring-primary focus:border-primary px-3 py-2" onchange="this.form.submit()">
                        <option value="">{{ __('matches.filter_all_teams') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ (string)$teamId === (string)$team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="season_id" class="bg-white border-slate-200 rounded-lg text-xs font-bold text-secondary focus:ring-primary focus:border-primary px-3 py-2" onchange="this.form.submit()">
                        <option value="">{{ __('matches.filter_all_seasons') }}</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ (string)$seasonId === (string)$season->id ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="match_type" class="bg-white border-slate-200 rounded-lg text-xs font-bold text-secondary focus:ring-primary focus:border-primary px-3 py-2" onchange="this.form.submit()">
                        <option value="">{{ __('matches.filter_all_types') }}</option>
                        @foreach($matchTypes as $key => $label)
                            <option value="{{ $key }}" {{ $matchType === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @if($teamId || $matchType || ($seasonId && (string)$seasonId !== (string)($seasons->where('name', \App\Models\Season::getExpectedCurrentSeasonName())->first()?->id)))
                        <a href="{{ route('public.matches.index', ['type' => $type]) }}" class="text-slate-400 hover:text-danger transition-colors" title="Zrušit filtry">
                            <i class="fa-light fa-circle-xmark text-lg"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="section-padding bg-bg">
        <div class="container">
            @if($matches->isEmpty())
                <x-empty-state
                    :title="$type === 'upcoming' ? __('matches.empty_upcoming') : __('matches.empty_latest')"
                    :subtitle="__('matches.empty_subtitle')"
                    icon="fa-calendar-days"
                    :primaryCta="['url' => route('public.teams.index'), 'label' => __('matches.empty_cta_teams')]"
                    :secondaryCta="['url' => route('public.contact.index'), 'label' => __('matches.empty_cta_contact')]"
                />

                <div class="mt-20 max-w-4xl mx-auto border-t border-slate-100 pt-16">
                    <div class="flex flex-col md:flex-row gap-12 items-center">
                        <div class="flex-1">
                            <h3 class="text-2xl font-black uppercase tracking-tighter mb-4 text-secondary">{{ __('matches.starter_title') }}</h3>
                            <p class="text-slate-500 leading-relaxed text-balance">
                                {{ __('matches.starter_text') }}
                            </p>
                        </div>
                        <div class="w-full md:w-64 flex-shrink-0">
                            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-center relative overflow-hidden group">
                                <div class="absolute inset-0 bg-primary opacity-0 group-hover:opacity-[0.03] transition-opacity"></div>
                                <i class="fa-light fa-arrows-rotate text-3xl text-primary mb-4 block"></i>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('matches.starter_sync') }}</div>
                                <div class="font-bold text-secondary tracking-tight">{{ __('matches.starter_source') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-6">
                    @foreach($matches as $match)
                        <x-match-card :match="$match" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $matches->appends(['type' => $type, 'team_id' => $teamId, 'season_id' => $seasonId, 'match_type' => $matchType])->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
