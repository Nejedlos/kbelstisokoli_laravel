@props(['event', 'showActions' => true])

@php
    $type = $event['type'];
    $data = $event['data'];
    $time = $event['time'];
    $attendance = $data->attendances->first();
    $status = $attendance?->planned_status ?? 'pending';

    $confirmedCount = $data->confirmed_count ?? 0;
    $declinedCount = $data->declined_count ?? 0;
    $maybeCount = $data->maybe_count ?? 0;
    $totalCount = $data->expected_players_count ?? 0;
    $pendingCount = max(0, $totalCount - ($confirmedCount + $declinedCount + $maybeCount));

    $typeIcons = [
        'training' => 'dumbbell',
        'match' => 'basketball',
        'event' => 'star',
    ];

    $typeLabels = [
        'training' => 'Trénink',
        'match' => 'Zápas',
        'event' => 'Akce',
    ];

    $statusColors = [
        'pending' => 'bg-slate-100 text-slate-600',
        'confirmed' => 'bg-success-100 text-success-700',
        'declined' => 'bg-danger-100 text-danger-700',
        'maybe' => 'bg-warning-100 text-warning-700',
    ];

    $statusLabels = [
        'pending' => 'Čeká',
        'confirmed' => 'Přijdu',
        'declined' => 'Omluven',
        'maybe' => 'Možná',
    ];
@endphp

<div class="relative group">
    <div class="absolute inset-0 bg-white rounded-3xl border border-slate-200/60 shadow-lg shadow-slate-200/20 group-hover:shadow-xl group-hover:shadow-primary/5 group-hover:border-primary/20 transition-all duration-500"></div>
    <div class="absolute top-0 left-0 w-1 h-full bg-{{ $status === 'confirmed' ? 'emerald-500' : ($status === 'declined' ? 'rose-500' : 'slate-200') }} rounded-l-3xl"></div>

    <div class="relative p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <a href="{{ route('member.attendance.show', ['type' => $type, 'id' => $data->id]) }}" class="flex items-center gap-4 sm:gap-5 flex-1 w-full min-w-0">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-slate-50 flex flex-col items-center justify-center text-secondary leading-none border border-slate-100 shrink-0 group-hover:bg-white group-hover:shadow-md transition-all duration-500">
                <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ $time->translatedFormat('M') }}</span>
                <span class="text-lg sm:text-xl font-black tracking-tight">{{ $time->format('d') }}</span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-1 sm:mb-1.5">
                    <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-1.5 group-hover:text-primary transition-colors">
                        <i class="fa-light fa-{{ $typeIcons[$type] }} text-primary"></i>
                        {{ $typeLabels[$type] }}
                    </span>
                    <span class="px-2 py-0.5 sm:px-2.5 rounded-lg text-[8px] sm:text-[9px] font-black uppercase tracking-widest shadow-sm {{ $statusColors[$status] }}">
                        {{ $statusLabels[$status] }}
                    </span>
                </div>
                <h4 class="text-base sm:text-lg font-black text-secondary leading-tight truncate tracking-tight">
                    @if($type === 'match')
                        {{ $data->team?->name }} <span class="text-primary italic mx-1">vs</span> {{ $data->opponent?->name }}
                    @elseif($type === 'training')
                        {{ __('member.attendance.event_types.training') }}
                    @else
                        {{ $data->title }}
                    @endif
                </h4>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 sm:mt-2 text-[10px] sm:text-[11px] text-slate-500 font-bold italic opacity-80">
                    <span class="flex items-center gap-1.5"><i class="fa-light fa-clock text-primary"></i> {{ $time->format('H:i') }}</span>
                    @if($data->location)
                        <span class="flex items-center gap-1.5"><i class="fa-light fa-map-pin text-primary"></i> {{ Str::limit($data->location, 20) }}</span>
                    @endif
                </div>
            </div>
        </a>

        <!-- Stats & Actions Container -->
        <div class="flex items-center justify-between lg:justify-end gap-4 sm:gap-6 w-full lg:w-auto pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100">
            <!-- RSVP Stats -->
            <div class="flex items-center gap-3 sm:gap-4 bg-slate-50/50 px-3 sm:px-4 py-2 rounded-2xl border border-slate-100/50">
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.confirmed') }}">
                    <span class="text-[10px] sm:text-[11px] font-black text-emerald-600 leading-none mb-1 sm:mb-1.5">{{ $confirmedCount }}</span>
                    <i class="fa-solid fa-circle-check text-[9px] sm:text-[10px] text-emerald-200"></i>
                </div>
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.declined') }}">
                    <span class="text-[10px] sm:text-[11px] font-black text-rose-600 leading-none mb-1 sm:mb-1.5">{{ $declinedCount }}</span>
                    <i class="fa-solid fa-circle-xmark text-[9px] sm:text-[10px] text-rose-200"></i>
                </div>
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.pending') }}">
                    <span class="text-[10px] sm:text-[11px] font-black text-slate-400 leading-none mb-1 sm:mb-1.5">{{ $pendingCount }}</span>
                    <i class="fa-solid fa-circle-question text-[9px] sm:text-[10px] text-slate-200"></i>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($showActions)
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-emerald-500/20 active:scale-95 group/btn" title="Přijdu">
                            <i class="fa-light fa-check text-xl"></i>
                        </button>
                    </form>
                    <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="declined">
                        <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 group/btn" title="Nepřijdu">
                            <i class="fa-light fa-xmark text-xl"></i>
                        </button>
                    </form>
                    <a href="{{ route('member.attendance.show', ['type' => $type, 'id' => $data->id]) }}" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-400 hover:bg-primary hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-primary/20 active:scale-95 group/btn" title="Detail">
                        <i class="fa-light fa-chevron-right text-lg"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
