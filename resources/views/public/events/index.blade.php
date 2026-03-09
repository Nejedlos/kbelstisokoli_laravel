@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('events.title')"
        :subtitle="__('events.subtitle')"
        :breadcrumbs="[__('events.breadcrumbs') => null]"
        image="assets/img/hero/hero-events.webp"
    />

    <div class="bg-slate-50 border-b border-slate-200">
        <div class="container">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('public.events.index', array_merge(request()->query(), ['type' => 'upcoming'])) }}"
                       class="py-6 border-b-2 font-black uppercase tracking-widest text-sm transition-colors {{ $type === 'upcoming' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary' }}">
                        {{ __('events.upcoming') }}
                    </a>
                    <a href="{{ route('public.events.index', array_merge(request()->query(), ['type' => 'past'])) }}"
                       class="py-6 border-b-2 font-black uppercase tracking-widest text-sm transition-colors {{ $type === 'past' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary' }}">
                        {{ __('events.past') }}
                    </a>
                </div>

                <form action="{{ route('public.events.index') }}" method="GET" class="flex flex-wrap items-center gap-4 py-4 md:py-0">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <i class="fa-light fa-users text-xs"></i>
                        </div>
                        <select name="team_id" class="bg-white border-slate-200 pl-9 pr-8 py-2 rounded-lg text-xs font-bold text-secondary focus:ring-primary focus:border-primary appearance-none cursor-pointer transition-all hover:border-slate-300 shadow-sm" onchange="this.form.submit()">
                            <option value="">{{ __('events.filter_all_teams') }}</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ (string)$teamId === (string)$team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-light fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <i class="fa-light fa-tags text-xs"></i>
                        </div>
                        <select name="event_type" class="bg-white border-slate-200 pl-9 pr-8 py-2 rounded-lg text-xs font-bold text-secondary focus:ring-primary focus:border-primary appearance-none cursor-pointer transition-all hover:border-slate-300 shadow-sm" onchange="this.form.submit()">
                            <option value="">{{ __('events.filter_all_types') }}</option>
                            @foreach($eventTypes as $key => $label)
                                <option value="{{ $key }}" {{ $eventType === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-light fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>

                    @if($teamId || $eventType)
                        <a href="{{ route('public.events.index', ['type' => $type]) }}" class="flex items-center gap-2 px-3 py-2 bg-slate-100 hover:bg-danger/10 text-slate-500 hover:text-danger rounded-lg transition-all text-[10px] font-black uppercase tracking-widest border border-slate-200 shadow-sm" title="Zrušit filtry">
                            <i class="fa-light fa-circle-xmark text-sm"></i>
                            <span class="hidden sm:inline">Zrušit filtry</span>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="section-padding bg-bg">
        <div class="container">
            @if($events->isEmpty())
                <x-empty-state
                    :title="$type === 'upcoming' ? __('events.empty_upcoming') : __('events.empty_past')"
                    :subtitle="__('events.empty_subtitle')"
                    icon="fa-calendar-star"
                    :primaryCta="['url' => route('public.teams.index'), 'label' => __('events.empty_cta_teams')]"
                    :secondaryCta="['url' => route('public.contact.index'), 'label' => __('events.empty_cta_contact')]"
                />
            @else
                <div class="grid grid-cols-1 gap-6">
                    @foreach($events as $event)
                        <x-event-card :event="$event" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $events->appends(['type' => $type, 'team_id' => $teamId, 'event_type' => $eventType])->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
