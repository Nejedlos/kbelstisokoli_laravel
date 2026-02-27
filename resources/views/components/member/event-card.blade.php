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
        'training' => 'heroicon-o-academic-cap',
        'match' => 'heroicon-o-trophy',
        'event' => 'heroicon-o-star',
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

<div class="card p-5 border-l-4 {{ $status === 'confirmed' ? 'border-l-success-500' : ($status === 'declined' ? 'border-l-danger-500' : 'border-l-slate-200') }} hover:shadow-md transition-shadow">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <a href="{{ route('member.attendance.show', ['type' => $type, 'id' => $data->id]) }}" class="flex items-center gap-4 flex-1 w-full">
            <div class="w-12 h-12 rounded-club bg-slate-50 flex flex-col items-center justify-center text-secondary leading-none border border-slate-100 shrink-0">
                <span class="text-[10px] font-black uppercase tracking-tighter">{{ $time->translatedFormat('M') }}</span>
                <span class="text-lg font-black tracking-tighter">{{ $time->format('d') }}</span>
            </div>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1">
                        <x-dynamic-component :component="$typeIcons[$type]" class="w-3 h-3 text-primary" />
                        {{ $typeLabels[$type] }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$status] }}">
                        {{ $statusLabels[$status] }}
                    </span>
                </div>
                <h4 class="text-base font-bold text-secondary leading-tight truncate">
                    @if($type === 'match')
                        {{ $data->team?->name }} vs {{ $data->opponent?->name }}
                    @elseif($type === 'training')
                        {{ __('member.attendance.event_types.training') }}
                    @else
                        {{ $data->title }}
                    @endif
                </h4>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11px] text-slate-500 font-medium">
                    <span class="flex items-center gap-1"><i class="fa-light fa-clock text-primary"></i> {{ $time->format('H:i') }}</span>
                    @if($data->location)
                        <span class="flex items-center gap-1"><i class="fa-light fa-map-pin text-primary"></i> {{ Str::limit($data->location, 25) }}</span>
                    @endif
                </div>
            </div>
        </a>

        <!-- Stats & Actions Container -->
        <div class="flex items-center justify-between md:justify-end gap-2 sm:gap-4 w-full md:w-auto pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
            <!-- RSVP Stats -->
            <div class="flex items-center gap-3 sm:gap-4 bg-slate-50/50 px-3 py-1.5 rounded-xl border border-slate-100/50">
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.confirmed') }}">
                    <span class="text-[10px] font-black text-success-600 leading-none mb-1">{{ $confirmedCount }}</span>
                    <i class="fa-solid fa-circle-check text-[10px] text-success-200"></i>
                </div>
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.declined') }}">
                    <span class="text-[10px] font-black text-danger-600 leading-none mb-1">{{ $declinedCount }}</span>
                    <i class="fa-solid fa-circle-xmark text-[10px] text-danger-200"></i>
                </div>
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.pending') }}">
                    <span class="text-[10px] font-black text-slate-400 leading-none mb-1">{{ $pendingCount }}</span>
                    <i class="fa-solid fa-circle-question text-[10px] text-slate-200"></i>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($showActions)
                <div class="flex items-center gap-2">
                    <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-success-50 text-success-600 hover:bg-success-600 hover:text-white transition-all shadow-sm active:scale-90" title="Přijdu">
                            <i class="fa-light fa-check text-lg"></i>
                        </button>
                    </form>
                    <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="declined">
                        <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-danger-50 text-danger-600 hover:bg-danger-600 hover:text-white transition-all shadow-sm active:scale-90" title="Nepřijdu">
                            <i class="fa-light fa-xmark text-lg"></i>
                        </button>
                    </form>
                    <a href="{{ route('member.attendance.show', ['type' => $type, 'id' => $data->id]) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-400 hover:bg-primary hover:text-white transition-all shadow-sm active:scale-90" title="Detail">
                        <i class="fa-light fa-chevron-right text-lg"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
