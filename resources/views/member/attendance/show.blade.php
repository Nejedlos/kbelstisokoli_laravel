@extends('layouts.member', [
    'title' => __('member.attendance.detail_title') ?? 'Detail události',
    'subtitle' => $item->title ?? ($type === 'match' ? ($item->team?->name . ' vs ' . $item->opponent?->name) : 'Trénink')
])

@section('content')
<div class="space-y-8">
    <!-- Back button and Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('member.attendance.index') }}" class="btn btn-ghost text-xs uppercase tracking-widest font-black flex items-center gap-2">
            <i class="fa-light fa-arrow-left"></i>
            {{ __('member.attendance.back_to_program') ?? 'Zpět na program' }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- Main Column: Details & RSVP Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Event Card (Detail) -->
            <div class="card overflow-hidden">
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-4 sm:gap-5">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-slate-50 flex flex-col items-center justify-center text-secondary border border-slate-100 shadow-inner shrink-0">
                                <span class="text-[10px] sm:text-xs font-black uppercase tracking-tighter text-slate-400 mb-0.5">{{ $time->translatedFormat('M') }}</span>
                                <span class="text-xl sm:text-2xl font-black leading-none">{{ $time->format('d') }}</span>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-xl sm:text-2xl font-black text-secondary leading-tight tracking-tight">
                                    @if($type === 'match')
                                        {{ $item->team?->name }} <span class="text-primary italic mx-1">vs</span> {{ $item->opponent?->name }}
                                    @elseif($type === 'training')
                                        {{ __('member.attendance.event_types.training') }}
                                    @else
                                        {{ $item->getTranslation('title', app()->getLocale()) }}
                                    @endif
                                </h2>
                                <div class="flex flex-wrap items-center gap-y-2 gap-x-4 mt-2 text-[11px] sm:text-sm text-slate-500 font-bold italic opacity-80">
                                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                                        <i class="fa-light fa-clock text-primary"></i>
                                        {{ $time->format('H:i') }}
                                        @if(isset($item->ends_at))
                                            – {{ $item->ends_at->format('H:i') }}
                                        @endif
                                    </div>
                                    @if($item->location)
                                        <div class="flex items-center gap-1.5 truncate">
                                            <i class="fa-light fa-map-pin text-primary"></i>
                                            {{ $item->location }}
                                        </div>
                                    @endif
                                    @if($type === 'training')
                                        <div class="flex items-center gap-1.5 truncate">
                                            <i class="fa-light fa-users-viewfinder text-primary"></i>
                                            {{ $item->teams->pluck('name')->join(', ') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @php
                            $myStatus = $myAttendance?->planned_status ?? 'pending';
                            $statusColors = [
                                'pending' => 'bg-slate-100 text-slate-600',
                                'confirmed' => 'bg-success-100 text-success-700',
                                'declined' => 'bg-danger-100 text-danger-700',
                                'maybe' => 'bg-warning-100 text-warning-700',
                            ];
                        @endphp
                        <div class="px-4 py-2 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest {{ $statusColors[$myStatus] }} border border-current opacity-80 self-start md:self-center text-center">
                            {{ __('member.attendance.status.' . $myStatus) }}
                        </div>
                    </div>

                    @if($item->notes || $item->notes_public || $item->description)
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 text-slate-600 text-[13px] sm:text-sm leading-relaxed font-medium">
                            <div class="font-black uppercase tracking-widest text-[9px] sm:text-[10px] text-slate-400 mb-2.5 flex items-center gap-1.5">
                                <i class="fa-light fa-align-left text-primary"></i>
                                {{ __('member.attendance.notes_label') ?? 'Poznámky k události' }}
                            </div>
                            {!! nl2br(e($item->notes ?? ($item->notes_public ?? ($item->description ?? '')))) !!}
                        </div>
                    @endif
                </div>

                <!-- RSVP Form -->
                <div class="p-6 sm:p-8 bg-slate-50/50 border-t border-slate-100">
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-tight text-secondary mb-5 flex items-center gap-2">
                        <i class="fa-light fa-pen-to-square text-primary"></i>
                        {{ __('member.attendance.update_status_title') ?? 'Upravit moji účast' }}
                    </h3>

                    <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $item->id]) }}" method="POST" class="space-y-6" x-data="{ status: '{{ $myStatus }}' }">
                        @csrf
                        <div class="grid grid-cols-1 xs:grid-cols-3 gap-3">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="confirmed" class="peer sr-only" x-model="status" {{ $myStatus === 'confirmed' ? 'checked' : '' }}>
                                <div class="p-4 rounded-2xl border-2 border-slate-200 bg-white text-center transition-all group-hover:border-success-200 peer-checked:border-success-500 peer-checked:bg-success-50 shadow-sm min-h-[80px] flex flex-col items-center justify-center">
                                    <i class="fa-light fa-check-circle text-2xl text-slate-300 mb-1.5 block peer-checked:text-success-600"></i>
                                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-600 peer-checked:text-success-700 leading-none">{{ __('member.attendance.status.confirmed') }}</span>
                                </div>
                            </label>

                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="declined" class="peer sr-only" x-model="status" {{ $myStatus === 'declined' ? 'checked' : '' }}>
                                <div class="p-4 rounded-2xl border-2 border-slate-200 bg-white text-center transition-all group-hover:border-danger-200 peer-checked:border-danger-500 peer-checked:bg-danger-50 shadow-sm min-h-[80px] flex flex-col items-center justify-center">
                                    <i class="fa-light fa-times-circle text-2xl text-slate-300 mb-1.5 block peer-checked:text-danger-600"></i>
                                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-600 peer-checked:text-danger-700 leading-none">{{ __('member.attendance.status.declined') }}</span>
                                </div>
                            </label>

                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="maybe" class="peer sr-only" x-model="status" {{ $myStatus === 'maybe' ? 'checked' : '' }}>
                                <div class="p-4 rounded-2xl border-2 border-slate-200 bg-white text-center transition-all group-hover:border-warning-200 peer-checked:border-warning-500 peer-checked:bg-warning-50 shadow-sm min-h-[80px] flex flex-col items-center justify-center">
                                    <i class="fa-light fa-question-circle text-2xl text-slate-300 mb-1.5 block peer-checked:text-warning-600"></i>
                                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-600 peer-checked:text-warning-700 leading-none">{{ __('member.attendance.status.maybe') }}</span>
                                </div>
                            </label>
                        </div>

                        <!-- Excuse Reason (only for declined) -->
                        <div x-show="status === 'declined'" x-transition class="space-y-3">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5 block">
                                    {{ __('member.attendance.excuse_reason') }}
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach(__('member.attendance.excuse_reasons') as $key => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="excuse_reason" value="{{ $key }}" class="peer sr-only">
                                            <div class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-center text-xs font-bold text-slate-600 transition-all hover:bg-slate-50 peer-checked:border-danger-500 peer-checked:text-danger-700 peer-checked:bg-danger-50/50">
                                                {{ $label }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="note" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('member.attendance.note_field') ?? 'Důvod omluvy / Poznámka' }}</label>
                            <textarea id="note" name="note" rows="2" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary text-sm shadow-sm" placeholder="{{ __('member.attendance.note_placeholder') ?? 'Např. nemoc, práce, dovolená...' }}">{{ $myAttendance?->note }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary w-full sm:w-auto px-12 py-3 text-xs uppercase tracking-widest font-black shadow-lg shadow-primary/20">
                                {{ __('member.attendance.save_response') ?? 'Uložit odpověď' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar: Attendance List -->
        <div class="space-y-6">
            <h3 class="text-lg font-black uppercase tracking-tight text-secondary flex items-center gap-2">
                <i class="fa-light fa-users text-primary"></i>
                {{ __('member.attendance.who_comes_title') ?? 'Kdo přijde?' }}
            </h3>

            <!-- Stats Overview -->
            <div class="grid grid-cols-4 gap-2">
                <div class="bg-white p-3 rounded-2xl border border-slate-100 text-center shadow-sm" title="{{ __('member.attendance.status.confirmed') }}">
                    <div class="text-lg font-black text-success-600 leading-none">{{ $confirmed->count() }}</div>
                    <i class="fa-solid fa-circle-check text-[10px] text-success-200 mt-1"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl border border-slate-100 text-center shadow-sm" title="{{ __('member.attendance.status.declined') }}">
                    <div class="text-lg font-black text-danger-600 leading-none">{{ $declined->count() }}</div>
                    <i class="fa-solid fa-circle-xmark text-[10px] text-danger-200 mt-1"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl border border-slate-100 text-center shadow-sm" title="{{ __('member.attendance.status.maybe') }}">
                    <div class="text-lg font-black text-warning-600 leading-none">{{ $maybe->count() }}</div>
                    <i class="fa-solid fa-circle-question text-[10px] text-warning-200 mt-1"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl border border-slate-100 text-center shadow-sm" title="{{ __('member.attendance.status.pending') }}">
                    <div class="text-lg font-black text-slate-400 leading-none">{{ $pending->count() }}</div>
                    <i class="fa-solid fa-circle-minus text-[10px] text-slate-200 mt-1"></i>
                </div>
            </div>

            <!-- Attendance List Groups -->
            <div class="space-y-6 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar">
                @if($confirmed->isNotEmpty())
                    <div class="space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-widest text-success-600 flex items-center gap-1.5 ml-1">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ __('member.attendance.who_comes_title') }}
                        </div>
                        @foreach($confirmed as $p)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-100 shadow-sm {{ $p['is_me'] ? 'ring-1 ring-primary-100 bg-primary/5' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-success-50 text-success-600 flex items-center justify-center text-xs font-black">
                                    {{ strtoupper(substr($p['user']->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-secondary {{ $p['is_me'] ? 'text-primary' : '' }}">{{ $p['user']->name }} @if($p['is_me']) (Já) @endif</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($declined->isNotEmpty())
                    <div class="space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-widest text-danger-600 flex items-center gap-1.5 ml-1">
                            <i class="fa-solid fa-circle-xmark"></i>
                            {{ __('member.attendance.who_not_comes_title') }}
                        </div>
                        @foreach($declined as $p)
                            <div class="flex flex-col p-3 rounded-xl bg-white border border-slate-100 shadow-sm opacity-80 {{ $p['is_me'] ? 'ring-1 ring-primary-100 bg-primary/5' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-danger-50 text-danger-400 flex items-center justify-center text-xs font-black">
                                        {{ strtoupper(substr($p['user']->name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-bold text-slate-600">{{ $p['user']->name }}</span>
                                </div>
                                @if($p['attendance']?->note)
                                    <div class="ml-11 mt-1.5 p-2 rounded-lg bg-slate-50 border border-slate-100 text-[10px] text-slate-500 italic leading-snug">
                                        {{ $p['attendance']->note }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($maybe->isNotEmpty())
                    <div class="space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-widest text-warning-600 flex items-center gap-1.5 ml-1">
                            <i class="fa-solid fa-circle-question"></i>
                            {{ __('member.attendance.status.maybe') }}
                        </div>
                        @foreach($maybe as $p)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-100 shadow-sm {{ $p['is_me'] ? 'ring-1 ring-primary-100 bg-primary/5' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-warning-50 text-warning-600 flex items-center justify-center text-xs font-black">
                                    {{ strtoupper(substr($p['user']->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-secondary">{{ $p['user']->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($pending->isNotEmpty())
                    <div class="space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5 ml-1">
                            <i class="fa-solid fa-circle-minus"></i>
                            {{ __('member.attendance.who_pending_title') }}
                        </div>
                        @foreach($pending as $p)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-dashed border-slate-200 opacity-60">
                                <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center text-xs font-black border border-slate-100">
                                    {{ strtoupper(substr($p['user']->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-slate-400">{{ $p['user']->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
