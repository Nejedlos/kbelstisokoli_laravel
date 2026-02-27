@props([
    'match'
])

@php
    $statusColors = [
        'planned' => 'bg-accent text-white',
        'scheduled' => 'bg-accent text-white',
        'completed' => 'bg-success text-white',
        'played' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white',
        'postponed' => 'bg-warning text-black',
    ];
    $statusLabels = [
        'planned' => __('matches.planned') ?? 'Plánováno',
        'scheduled' => __('matches.planned') ?? 'Plánováno',
        'completed' => __('matches.completed') ?? 'Odehráno',
        'played' => __('matches.completed') ?? 'Odehráno',
        'cancelled' => __('matches.cancelled') ?? 'Zrušeno',
        'postponed' => __('matches.postponed') ?? 'Odloženo',
    ];
    $typeLabels = [
        'MI' => __('matches.type_mi'),
        'PO' => __('matches.type_po'),
        'TUR' => __('matches.type_tur'),
        'PRATEL' => __('matches.type_pratel'),
    ];
    $typeColors = [
        'MI' => 'bg-blue-50 text-blue-600 border-blue-200',
        'PO' => 'bg-purple-50 text-purple-600 border-purple-200',
        'TUR' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'PRATEL' => 'bg-slate-50 text-slate-400 border-slate-200',
    ];
    $typeIcons = [
        'MI' => 'fa-trophy',
        'PO' => 'fa-medal',
        'TUR' => 'fa-flag-checkered',
        'PRATEL' => 'fa-handshake',
    ];
@endphp

<div class="card card-hover overflow-hidden border-l-4 border-l-primary">
    <div class="p-5 md:p-8 flex flex-col md:flex-row md:items-center gap-6">
        <!-- Date & Time -->
        <div class="flex flex-row md:flex-col items-center md:items-start justify-between md:justify-center md:min-w-[120px] pb-4 md:pb-0 border-b md:border-b-0 md:border-r border-slate-100">
            <div class="flex flex-col">
                <div class="text-secondary font-black text-2xl leading-none">
                    {{ $match->scheduled_at->format('d. m.') }}
                </div>
                <div class="text-slate-500 font-bold uppercase tracking-widest text-xs mt-1">
                    {{ $match->scheduled_at->format('H:i') }}
                </div>
            </div>
            @if($match->match_type)
                <div class="mt-2 hidden md:block">
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded border {{ $typeColors[$match->match_type] ?? 'bg-slate-50 text-slate-400 border-slate-200' }}">
                        @if(isset($typeIcons[$match->match_type]))
                            <i class="fa-light {{ $typeIcons[$match->match_type] }} mr-1"></i>
                        @endif
                        {{ $typeLabels[$match->match_type] ?? $match->match_type }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Teams & Score -->
        <div class="flex-1 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                <div class="flex items-center gap-2 mb-1">
                    @php
                        $hasKlub = $match->teams->contains('slug', 'klub');
                        $teamNames = $match->teams->pluck('name')->join(' & ');
                    @endphp

                    @if($hasKlub || $match->teams->count() > 1)
                        <span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-[0.1em] border border-primary/20">
                            <i class="fa-light fa-users-crown mr-1"></i>
                            {{ $hasKlub ? 'CELÝ KLUB' : $teamNames }}
                        </span>
                    @else
                        @php $singleTeam = $match->teams->first() ?? $match->team; @endphp
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">{{ $singleTeam?->name }}</span>
                    @endif

                    @if($match->match_type)
                        <span class="text-[9px] font-black uppercase tracking-wider md:hidden px-1.5 py-0.5 rounded border {{ $typeColors[$match->match_type] ?? 'bg-slate-50 text-slate-400 border-slate-200' }}">
                            {{ $typeLabels[$match->match_type] ?? $match->match_type }}
                        </span>
                    @endif
                </div>
                <div class="text-xl md:text-2xl font-black uppercase tracking-tight text-secondary">
                    <span class="{{ $match->is_home ? 'text-primary' : '' }}">
                        {{ $match->is_home ? ($branding['club_short_name'] ?? 'Sokoli') : $match->opponent->name }}
                    </span>
                    <span class="text-slate-300 mx-2">vs</span>
                    <span class="{{ !$match->is_home ? 'text-primary' : '' }}">
                        {{ $match->is_home ? $match->opponent->name : ($branding['club_short_name'] ?? 'Sokoli') }}
                    </span>
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
                @if(in_array($match->status, ['completed', 'played']))
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
                        {{ $isWin ? __('matches.victory') : ($isLoss ? __('matches.loss') : __('matches.draw')) }}
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
            <a href="{{ route('public.matches.show', $match->id) }}" class="btn btn-outline-primary py-2 px-4 text-xs font-black">
                {{ __('matches.view_detail') }}
            </a>
        </div>
    </div>
</div>
