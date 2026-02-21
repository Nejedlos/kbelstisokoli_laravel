@props([
    'match'
])

@php
    $statusColors = [
        'planned' => 'bg-accent text-white',
        'completed' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white',
        'postponed' => 'bg-warning text-black',
    ];
    $statusLabels = [
        'planned' => 'Plánováno',
        'completed' => 'Odehráno',
        'cancelled' => 'Zrušeno',
        'postponed' => 'Odloženo',
    ];
@endphp

<div class="card card-hover overflow-hidden border-l-4 border-l-primary">
    <div class="p-5 md:p-8 flex flex-col md:flex-row md:items-center gap-6">
        <!-- Date & Time -->
        <div class="flex flex-row md:flex-col items-center md:items-start justify-between md:justify-center md:min-w-[120px] pb-4 md:pb-0 border-b md:border-b-0 md:border-r border-slate-100">
            <div class="text-secondary font-black text-2xl leading-none">
                {{ $match->scheduled_at->format('d. m.') }}
            </div>
            <div class="text-slate-500 font-bold uppercase tracking-widest text-xs mt-1">
                {{ $match->scheduled_at->format('H:i') }}
            </div>
        </div>

        <!-- Teams & Score -->
        <div class="flex-1 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-1">{{ $match->team->name }}</span>
                <div class="text-xl md:text-2xl font-black uppercase tracking-tight text-secondary">
                    {{ $match->is_home ? $branding['club_short_name'] ?? 'Sokoli' : $match->opponent->name }}
                    <span class="text-slate-300 mx-2">vs</span>
                    {{ $match->is_home ? $match->opponent->name : ($branding['club_short_name'] ?? 'Sokoli') }}
                </div>
                <div class="flex items-center mt-2 text-slate-500 text-sm font-medium italic">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $match->location ?? 'Místo neuvedeno' }}
                </div>
            </div>

            <!-- Result -->
            <div class="flex flex-col items-center sm:items-end min-w-[100px]">
                @if($match->status === 'completed')
                    <div class="flex items-center gap-2">
                        <div class="text-3xl md:text-4xl font-black tabular-nums tracking-tighter text-secondary">
                            {{ $match->score_home }} : {{ $match->score_away }}
                        </div>
                    </div>
                    @php
                        $isWin = ($match->is_home && $match->score_home > $match->score_away) || (!$match->is_home && $match->score_away > $match->score_home);
                        $isLoss = ($match->is_home && $match->score_home < $match->score_away) || (!$match->is_home && $match->score_away < $match->score_home);
                    @endphp
                    <span class="text-[10px] font-black uppercase tracking-widest mt-1 {{ $isWin ? 'text-success' : ($isLoss ? 'text-danger' : 'text-slate-400') }}">
                        {{ $isWin ? 'Vítězství' : ($isLoss ? 'Prohra' : 'Remíza') }}
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusColors[$match->status] ?? 'bg-slate-100' }}">
                        {{ $statusLabels[$match->status] ?? $match->status }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Action -->
        <div class="md:ml-4 flex items-center justify-center">
            <a href="{{ route('public.matches.show', $match->id) }}" class="btn btn-outline py-2 px-4 text-xs font-black">
                Detaily
            </a>
        </div>
    </div>
</div>
