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
                    <x-member.event-card :event="$event" />
                @endforeach
            </div>
        @endif

        <div class="pt-6 border-t border-slate-200">
            <a href="{{ route('member.attendance.history') }}" class="btn btn-outline w-full sm:w-auto">
                {{ __('member.attendance.view_history') }}
            </a>
        </div>

        <!-- Bulk Actions Panel (Modern Glassmorphism / Bottom Sheet) -->
        <div x-show="selectedEvents.length > 0"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="translate-y-full opacity-0 sm:scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
             x-transition:leave-end="translate-y-full opacity-0 sm:scale-95"
             class="fixed bottom-0 sm:bottom-8 left-0 sm:left-1/2 sm:-translate-x-1/2 z-[9999] w-full sm:w-[calc(100%-1.5rem)] sm:max-w-[840px] sm:px-4 pointer-events-none"
             x-cloak>
             <div class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-3xl rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-[0_-15px_40px_-10px_rgba(0,0,0,0.1),0_30px_60px_-12px_rgba(0,0,0,0.25)] border-t sm:border border-white p-4 sm:p-5">
                <!-- Mobile Drag Handle -->
                <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-5"></div>

                <!-- Inner glow/shine -->
                <div class="absolute -top-24 -left-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <!-- Info Badge -->
                    <div class="flex items-center gap-3 bg-slate-50/80 px-4 py-3 sm:py-2.5 rounded-2xl border border-slate-200/50 w-full sm:w-auto shrink-0">
                        <div class="relative">
                            <div class="absolute inset-0 bg-primary/20 rounded-xl blur-md animate-pulse"></div>
                            <div class="relative w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/30">
                                <i class="fa-light fa-layer-group text-lg"></i>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-1">
                                {{ __('member.attendance.bulk_actions.title') }}
                            </span>
                            <span class="text-sm font-black text-secondary leading-none">
                                {!! __('member.attendance.bulk_actions.selected', ['count' => '<span x-text="selectedEvents.length" class="text-primary"></span>']) !!}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 w-full sm:flex-1 sm:justify-end pb-4 sm:pb-0" x-data="{ openDecline: false }">
                        <form action="{{ route('member.attendance.bulk-store') }}" method="POST" class="flex-1 sm:flex-none">
                            @csrf
                            <template x-for="id in selectedEvents" :key="id">
                                <input type="hidden" name="events[]" :value="id">
                            </template>
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" @click="$dispatch('loading-start')" class="group/btn w-full sm:w-auto h-11 px-3.5 sm:px-5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-widest transition-all hover:shadow-xl hover:shadow-emerald-500/30 flex items-center justify-center gap-2 active:scale-95">
                                <i class="fa-light fa-check-circle text-base group-hover/btn:scale-110 transition-transform"></i>
                                <span class="hidden xs:inline">{{ __('member.attendance.bulk_actions.confirm') }}</span>
                                <span class="xs:hidden">{{ __('member.attendance.status.confirmed') }}</span>
                            </button>
                        </form>

                        <div class="flex-1 sm:flex-none relative">
                            <button @click="openDecline = !openDecline"
                                    id="decline-bulk-btn"
                                    type="button"
                                    class="group/btn w-full sm:w-auto h-11 px-3.5 sm:px-5 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-widest transition-all hover:shadow-xl hover:shadow-rose-500/30 flex items-center justify-center gap-2 active:scale-95">
                                <i class="fa-light fa-times-circle text-base group-hover/btn:scale-110 transition-transform"></i>
                                <span class="hidden xs:inline">{{ __('member.attendance.bulk_actions.decline') }}</span>
                                <span class="xs:hidden">{{ __('member.attendance.status.declined') }}</span>
                                <i class="fa-light fa-chevron-up text-[10px] ml-1 transition-transform" :class="openDecline ? 'rotate-180' : ''"></i>
                            </button>

                            <!-- Decline Reasons Dropdown (Teleported for visibility) -->
                            <template x-teleport="body">
                                <div id="bulk-excuse-dropdown"
                                     x-show="openDecline"
                                     x-floating.top.fixed="'#decline-bulk-btn'"
                                     x-effect="if (openDecline) { $nextTick(() => { const el = document.getElementById('bulk-excuse-dropdown'); if (el) el.dispatchEvent(new CustomEvent('reposition')); }) }"
                                     @click.away="openDecline = false"
                                     @reposition.window="if (openDecline) { /* logic is in autoUpdate, but this forces Alpine to keep it alive */ }"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                     class="w-max bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-[10001]"
                                     x-cloak>
                                    <div class="p-2 overflow-y-auto ks-scrollbar" style="max-height: inherit;">
                                        <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 mb-1">
                                            {{ __('member.attendance.excuse_reason') }}
                                        </div>
                                        @foreach(\App\Enums\ExcuseReason::cases() as $reason)
                                            <form action="{{ route('member.attendance.bulk-store') }}" method="POST">
                                                @csrf
                                                <template x-for="id in selectedEvents" :key="id">
                                                    <input type="hidden" name="events[]" :value="id">
                                                </template>
                                                <input type="hidden" name="status" value="declined">
                                                <input type="hidden" name="excuse_reason" value="{{ $reason->value }}">
                                                <button type="submit" @click="$dispatch('loading-start')" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-rose-50 rounded-xl transition-colors group/item text-left">
                                                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500 group-hover/item:bg-rose-100 group-hover/item:text-rose-600 transition-colors">
                                                        <i class="{{ $reason->getIcon() }} text-sm"></i>
                                                    </div>
                                                    <span class="text-xs font-bold text-secondary group-hover/item:text-rose-700 transition-colors">{{ $reason->getLabel() }}</span>
                                                </button>
                                            </form>
                                        @endforeach
                                        <div class="mt-1 pt-1 border-t border-slate-100">
                                            <form action="{{ route('member.attendance.bulk-store') }}" method="POST">
                                                @csrf
                                                <template x-for="id in selectedEvents" :key="id">
                                                    <input type="hidden" name="events[]" :value="id">
                                                </template>
                                                <input type="hidden" name="status" value="declined">
                                                <button type="submit" @click="$dispatch('loading-start')" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 rounded-xl transition-colors group/item text-left">
                                                     <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover/item:bg-slate-100 transition-colors">
                                                        <i class="fa-light fa-minus text-sm"></i>
                                                    </div>
                                                    <span class="text-xs font-bold text-slate-500">{{ __('member.attendance.bulk_actions.decline_no_reason') }}</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button @click="selectedEvents = []" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all shrink-0 border border-slate-200/50" title="{{ __('member.attendance.bulk_actions.clear') }}">
                            <i class="fa-light fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
