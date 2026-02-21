@props(['event', 'showActions' => true])

@php
    $type = $event['type'];
    $data = $event['data'];
    $time = $event['time'];
    $attendance = $data->attendances->first();
    $status = $attendance?->status ?? 'pending';

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

<div class="card p-5 border-l-4 {{ $status === 'pending' ? 'border-l-primary' : ($status === 'confirmed' ? 'border-l-success-500' : 'border-l-danger-500') }}">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-club bg-slate-50 flex flex-col items-center justify-center text-secondary leading-none border border-slate-100">
                <span class="text-[10px] font-black uppercase">{{ $time->translatedFormat('M') }}</span>
                <span class="text-lg font-black tracking-tighter">{{ $time->format('d') }}</span>
            </div>

            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1">
                        <x-dynamic-component :component="$typeIcons[$type]" class="w-3 h-3" />
                        {{ $typeLabels[$type] }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$status] }}">
                        {{ $statusLabels[$status] }}
                    </span>
                </div>
                <h4 class="text-base font-bold text-secondary leading-tight">
                    @if($type === 'match')
                        {{ $data->team->name }} vs {{ $data->opponent->name }}
                    @elseif($type === 'training')
                        Trénink - {{ $data->team->name }}
                    @else
                        {{ $data->title }}
                    @endif
                </h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 font-medium">
                    <x-heroicon-o-clock class="w-3 h-3" />
                    {{ $time->format('H:i') }}
                    @if($data->location)
                        <span class="mx-1">•</span>
                        <x-heroicon-o-map-pin class="w-3 h-3" />
                        {{ $data->location }}
                    @endif
                </p>
            </div>
        </div>

        @if($showActions)
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST" class="flex items-center gap-2 w-full">
                    @csrf
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-success-600 text-white rounded-club text-xs font-black uppercase tracking-widest hover:bg-success-700 transition-colors">
                        ANO
                    </button>
                </form>
                <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST" class="flex items-center gap-2 w-full">
                    @csrf
                    <input type="hidden" name="status" value="declined">
                    <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-danger-600 text-white rounded-club text-xs font-black uppercase tracking-widest hover:bg-danger-700 transition-colors">
                        NE
                    </button>
                </form>
                <a href="{{ route('member.attendance.index') }}" class="p-2 bg-slate-100 text-slate-400 rounded-club hover:bg-slate-200 transition-colors">
                    <x-heroicon-o-ellipsis-horizontal class="w-5 h-5" />
                </a>
            </div>
        @endif
    </div>
</div>
