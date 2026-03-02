@extends('layouts.member', [
    'title' => __('member.attendance.history_title'),
    'subtitle' => __('member.attendance.history_subtitle')
])

@section('content')
    <div class="space-y-6">
        @php
            $hasActiveFilters = collect($filters)->filter(fn($v, $k) => $k === 'year' || $k === 'month' ? !empty($v) : $v !== 'all')->isNotEmpty();
        @endphp

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
                            <p class="text-[10px] sm:text-xs text-slate-500 leading-tight">{{ __('member.attendance.history_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="text-slate-400 group-hover/filter:text-primary transition-all duration-300" :class="open ? 'rotate-180' : ''">
                        <i class="fa-light fa-chevron-down text-[10px] sm:text-xs"></i>
                    </div>
                </div>

                <div x-show="open" x-cloak>
                    <form action="{{ route('member.attendance.history') }}" method="GET">
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
                            <a href="{{ route('member.attendance.history') }}" class="inline-flex items-center gap-2 text-[10px] sm:text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-primary transition-colors group">
                                <i class="fa-light fa-rotate-left transition-transform group-hover:rotate-[-45deg]"></i>
                                {{ __('member.attendance.filter.reset') }}
                            </a>
                        </div>
                    @endif
                </form>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            @php
                $statusColors = [
                    'pending' => 'bg-slate-100 text-slate-600',
                    'confirmed' => 'bg-success-100 text-success-700',
                    'declined' => 'bg-danger-100 text-danger-700',
                    'maybe' => 'bg-warning-100 text-warning-700',
                ];
                $statusLabels = [
                    'pending' => __('member.attendance.status.pending'),
                    'confirmed' => __('member.attendance.status.confirmed'),
                    'declined' => __('member.attendance.status.declined'),
                    'maybe' => __('member.attendance.status.maybe'),
                ];
            @endphp

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.attendance.table.event') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.attendance.table.date') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">{{ __('member.attendance.table.status') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.attendance.table.note') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">{{ __('member.attendance.table.responded') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-secondary">
                                        @php $item = $attendance->attendable; @endphp
                                        @if($attendance->attendable_type === 'App\Models\BasketballMatch')
                                            {{ $item?->team?->name }} vs {{ $item?->opponent?->name }}
                                        @elseif($attendance->attendable_type === 'App\Models\Training')
                                            {{ __('member.attendance.event_types.training') }} - {{ $item?->teams?->first()?->name }}
                                        @else
                                            {{ $item?->title ?? __('member.attendance.event_types.unknown') }}
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-0.5">
                                        {{ str_replace('App\Models\\', '', $attendance->attendable_type) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                    {{ $attendance->attendable?->scheduled_at?->format('d.m.Y H:i') ?? $attendance->attendable?->starts_at?->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusColors[$attendance->status] ?? 'bg-slate-100' }}">
                                        {{ $statusLabels[$attendance->status] ?? $attendance->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 italic">
                                    {{ $attendance->note ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs text-slate-400">
                                    {{ $attendance->responded_at?->format('d.m. H:i') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200 text-2xl">
                                        <i class="fa-light fa-calendar-xmark"></i>
                                    </div>
                                    <h3 class="font-bold text-secondary mb-1 text-sm">
                                        {{ $hasActiveFilters ? __('member.attendance.filter.title') : __('member.attendance.no_history') }}
                                    </h3>
                                    <p class="text-[10px] text-slate-400 max-w-xs mx-auto italic leading-tight">
                                        {{ $hasActiveFilters ? __('member.attendance.no_events_filter') : __('member.attendance.no_history') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile List -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($attendances as $attendance)
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-start gap-4">
                            <div class="min-w-0">
                                <div class="font-bold text-secondary text-sm leading-tight">
                                    @php $item = $attendance->attendable; @endphp
                                    @if($attendance->attendable_type === 'App\Models\BasketballMatch')
                                        {{ $item?->team?->name }} vs {{ $item?->opponent?->name }}
                                    @elseif($attendance->attendable_type === 'App\Models\Training')
                                        {{ __('member.attendance.event_types.training') }} - {{ $item?->teams?->first()?->name }}
                                    @else
                                        {{ $item?->title ?? __('member.attendance.event_types.unknown') }}
                                    @endif
                                </div>
                                <div class="text-[9px] font-black uppercase text-slate-400 tracking-widest mt-1">
                                    {{ str_replace('App\Models\\', '', $attendance->attendable_type) }}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest shrink-0 {{ $statusColors[$attendance->status] ?? 'bg-slate-100' }}">
                                {{ $statusLabels[$attendance->status] ?? $attendance->status }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                <i class="fa-light fa-calendar text-primary text-[10px]"></i>
                                {{ $attendance->attendable?->scheduled_at?->format('d.m.Y H:i') ?? $attendance->attendable?->starts_at?->format('d.m.Y H:i') }}
                            </div>
                            <div class="text-[10px] text-slate-400 italic">
                                {{ __('member.attendance.table.responded') }}: {{ $attendance->responded_at?->format('d.m. H:i') ?? '-' }}
                            </div>
                        </div>

                        @if($attendance->note)
                            <div class="bg-slate-50/80 p-2.5 rounded-xl text-xs text-slate-500 italic border border-slate-100/50">
                                <i class="fa-light fa-comment-lines mr-1.5 text-slate-300"></i>
                                {{ $attendance->note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200 text-2xl">
                            <i class="fa-light fa-calendar-xmark"></i>
                        </div>
                        <h3 class="font-bold text-secondary mb-1 text-sm">
                            {{ $hasActiveFilters ? __('member.attendance.filter.title') : __('member.attendance.no_history') }}
                        </h3>
                        <p class="text-[10px] text-slate-400 max-w-xs mx-auto italic leading-tight">
                            {{ $hasActiveFilters ? __('member.attendance.no_events_filter') : __('member.attendance.no_history') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6">
            {{ $attendances->links() }}
        </div>
    </div>
@endsection
