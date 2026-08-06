@extends('layouts.member', [
    'title' => __('member.attendance.title'),
    'subtitle' => __('member.attendance.subtitle')
])

@section('content')
    @push('head')
    <style>
        .ks-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .ks-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .ks-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .ks-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
    @endpush
    <div class="space-y-6" x-data="{
        selectedEvents: [],
        selectAll() {
            @php
                $ids = $program->filter(fn($e) => $e['type'] !== 'match' || !$e['data']->has_score)
                               ->map(fn($e) => $e['type'].':'.$e['data']->id);
            @endphp
            this.selectedEvents = @js($ids->values());
        }
    }" x-effect="
        const isActive = selectedEvents.length > 0;
        isActive ? document.body.setAttribute('data-batch-active', 'true') : document.body.removeAttribute('data-batch-active');
        const fab = document.querySelector('.ks-fab-trigger');
        if (fab) {
            const isMobile = window.innerWidth < 768;
            if (isMobile && (isActive || document.body.hasAttribute('data-prediction-open'))) {
                fab.style.setProperty('display', 'none', 'important');
            } else {
                fab.style.setProperty('display', 'flex', 'important');
            }
        }
    ">
        @php
            $hasActiveFilters = collect($filters)->filter(fn($v, $k) => $k === 'year' || $k === 'month' ? !empty($v) : ($k === 'period' ? false : $v !== 'all'))->isNotEmpty();
        @endphp

        <!-- Period Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl w-fit">
                <a href="{{ route('member.attendance.index', array_merge(request()->query(), ['period' => 'upcoming'])) }}"
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-all {{ $filters['period'] === 'upcoming' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ __('member.attendance.filter.upcoming') }}
                </a>
                <a href="{{ route('member.attendance.index', array_merge(request()->query(), ['period' => 'past'])) }}"
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-all {{ $filters['period'] === 'past' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ __('member.attendance.filter.past') }}
                </a>
            </div>

            @if($program->isNotEmpty())
                <div class="flex items-center gap-2">
                    <button @click="selectAll()" class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-primary/10 hover:text-primary transition-all">
                        <i class="fa-light fa-check-double"></i> {{ __('member.attendance.bulk_actions.title') }} - {{ __('member.attendance.filter.all') }}
                    </button>
                    <button x-show="selectedEvents.length > 0" @click="selectedEvents = []" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-rose-400 hover:text-rose-600 transition-colors">
                         {{ __('member.attendance.bulk_actions.clear') }}
                    </button>
                </div>
            @endif
        </div>

        <div x-data="{ open: window.innerWidth >= 640 }" class="card mb-4 sm:mb-8 overflow-visible">
            <div class="p-4 sm:p-6">
                <div
                    @click="open = !open"
                    class="flex items-center justify-between cursor-pointer group/filter"
                    :class="open ? 'mb-4 sm:mb-6' : ''"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary/10 rounded-lg sm:rounded-xl flex items-center justify-center text-primary text-sm sm:text-base group-hover/filter:bg-primary group-hover/filter:text-white transition-all duration-300">
                            <i class="fa-light fa-filter"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-secondary text-sm sm:text-base leading-tight group-hover/filter:text-primary transition-colors duration-300">{{ __('member.attendance.filter.title') }}</h3>
                            <p class="text-[10px] sm:text-xs text-slate-500 leading-tight">{{ __('member.attendance.subtitle') }}</p>
                        </div>
                    </div>

                    <div class="text-slate-400 group-hover/filter:text-primary transition-all duration-300" :class="open ? 'rotate-180' : ''">
                        <i class="fa-light fa-chevron-down text-[10px] sm:text-xs"></i>
                    </div>
                </div>

                <div x-show="open" x-cloak>
                    <form action="{{ route('member.attendance.index') }}" method="GET">
                    <input type="hidden" name="period" value="{{ $filters['period'] }}">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <!-- Typ akce -->
                        <div class="space-y-1">
                            <label class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400 ml-1">
                                {{ __('member.attendance.filter.type') }}
                            </label>
                            <div class="relative">
                                <select name="type" onchange="this.form.submit()" class="appearance-none w-full bg-slate-50 border-0 rounded-xl px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                    @foreach(__('member.attendance.filter.types') as $key => $label)
                                        <option value="{{ $key }}" {{ $filters['type'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fa-light fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Docházka -->
                        <div class="space-y-1">
                            <label class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400 ml-1">
                                {{ __('member.attendance.filter.attendance') }}
                            </label>
                            <div class="relative">
                                <select name="attendance" onchange="this.form.submit()" class="appearance-none w-full bg-slate-50 border-0 rounded-xl px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                    <option value="all" {{ $filters['attendance'] == 'all' ? 'selected' : '' }}>{{ __('member.attendance.filter.all') }}</option>
                                    <option value="confirmed" {{ $filters['attendance'] == 'confirmed' ? 'selected' : '' }}>{{ __('member.attendance.filter.confirmed') }}</option>
                                    <option value="declined" {{ $filters['attendance'] == 'declined' ? 'selected' : '' }}>{{ __('member.attendance.filter.declined') }}</option>
                                    <option value="maybe" {{ $filters['attendance'] == 'maybe' ? 'selected' : '' }}>{{ __('member.attendance.filter.maybe') }}</option>
                                    <option value="none" {{ $filters['attendance'] == 'none' ? 'selected' : '' }}>{{ __('member.attendance.filter.none') }}</option>
                                </select>
                                <div class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fa-light fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Rok -->
                        <div class="space-y-1">
                            <label class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400 ml-1">
                                {{ __('member.attendance.filter.year') }}
                            </label>
                            <div class="relative">
                                <select name="year" onchange="this.form.submit()" class="appearance-none w-full bg-slate-50 border-0 rounded-xl px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                    <option value="">{{ __('member.attendance.filter.all') }}</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ $filters['year'] == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fa-light fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Měsíc -->
                        <div class="space-y-1">
                            <label class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400 ml-1">
                                {{ __('member.attendance.filter.month') }}
                            </label>
                            <div class="relative">
                                <select name="month" onchange="this.form.submit()" class="appearance-none w-full bg-slate-50 border-0 rounded-xl px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                    <option value="">{{ __('member.attendance.filter.all') }}</option>
                                    @foreach(__('member.attendance.filter.months') as $key => $label)
                                        <option value="{{ $key }}" {{ $filters['month'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fa-light fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($hasActiveFilters)
                        <div class="mt-4 sm:mt-6 flex justify-end">
                            <a href="{{ route('member.attendance.index') }}" class="inline-flex items-center gap-2 text-[10px] sm:text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-primary transition-colors group">
                                <i class="fa-light fa-rotate-left transition-transform group-hover:rotate-[-45deg]"></i>
                                {{ __('member.attendance.filter.reset') }}
                            </a>
                        </div>
                    @endif
                </form>
                </div>
            </div>
        </div>

        @if($program->isEmpty())
            <div class="card p-12 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-3xl">
                    <i class="fa-light fa-calendar-xmark"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-2">
                    @if($hasActiveFilters)
                        {{ __('member.attendance.filter.title') }}
                    @elseif($filters['period'] === 'past')
                        {{ __('member.attendance.no_events_past') }}
                    @else
                        {{ __('member.attendance.no_events') }}
                    @endif
                </h3>
                <p class="text-slate-500 max-w-sm mx-auto">
                    @if($hasActiveFilters)
                        {{ __('member.attendance.no_events_filter') }}
                    @elseif($filters['period'] === 'past')
                        {{ __('member.attendance.no_events_past_text') ?? __('member.attendance.no_events_filter') }}
                    @else
                        {{ __('member.attendance.no_events_text') }}
                    @endif
                </p>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach($program as $event)
                    <livewire:member.event-card :event="$event" :key="$event['type'].'-'.$event['data']->id" />
                @endforeach
            </div>
        @endif

        <div class="pt-6 border-t border-slate-200">
            <a href="{{ route('member.attendance.history') }}" class="btn btn-outline w-full sm:w-auto">
                {{ __('member.attendance.view_history') }}
            </a>
        </div>

        <x-member.bulk-attendance-panel />
    </div>
@endsection
