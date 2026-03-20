@props([
    'match'
])

@php
    $statusColors = [
        'planned' => 'bg-accent text-white',
        'scheduled' => 'bg-accent text-white',
        'finished' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white',
        'postponed' => 'bg-warning text-black',
    ];
    $statusLabels = [
        'planned' => __('matches.planned') ?? 'Plánováno',
        'scheduled' => __('matches.planned') ?? 'Plánováno',
        'finished' => __('matches.finished') ?? 'Odehráno',
        'cancelled' => __('matches.cancelled') ?? 'Zrušeno',
        'postponed' => __('matches.postponed') ?? 'Odloženo',
    ];
    $typeLabels = [
        'mistrovske' => 'mistrák',
        'poharove' => 'pohár',
        'pratelske' => 'přátelák',
        'TUR' => 'turnaj',
    ];
    $typeColors = [
        'mistrovske' => 'bg-blue-50 text-blue-600 border-blue-200',
        'poharove' => 'bg-purple-50 text-purple-600 border-purple-200',
        'TUR' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'pratelske' => 'bg-slate-50 text-slate-400 border-slate-200',
    ];
    $typeIcons = [
        'mistrovske' => 'fa-trophy',
        'poharove' => 'fa-medal',
        'TUR' => 'fa-flag-checkered',
        'pratelske' => 'fa-handshake',
    ];

    $isPlayed = $match->status === 'finished';
    $homeScore = $match->score_home ?? 0;
    $awayScore = $match->score_away ?? 0;
    $hasScore = isset($match->score_home) || isset($match->score_away);

    $isWin = $isPlayed && $hasScore && (($match->is_home && $homeScore > $awayScore) || (!$match->is_home && $awayScore > $homeScore));
    $isLoss = $isPlayed && $hasScore && (($match->is_home && $homeScore < $awayScore) || (!$match->is_home && $awayScore < $homeScore));
    $isDraw = $isPlayed && $hasScore && ($homeScore === $awayScore);
    $prediction = $match->prediction;

    $resultColor = 'border-l-primary';
    if ($isPlayed && $hasScore) {
        if ($isWin) $resultColor = 'border-l-success';
        elseif ($isLoss) $resultColor = 'border-l-danger';
        elseif ($isDraw) $resultColor = 'border-l-slate-400';
    }

    $branding = $branding ?? app(\App\Services\BrandingService::class)->getSettings();
@endphp

<div class="card card-hover overflow-hidden border-l-4 {{ $resultColor }} group">
    <div class="p-5 md:p-8 flex flex-col md:flex-row md:items-center gap-6 relative">
        <div class="absolute top-0 right-0 p-4 opacity-[0.03] pointer-events-none group-hover:opacity-[0.07] transition-opacity">
            @if(isset($typeIcons[$match->match_type]))
                <i class="fa-light {{ $typeIcons[$match->match_type] }} text-7xl"></i>
            @endif
        </div>
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
                    @endif

                    <span class="flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest {{ $match->is_home ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                        <i class="fa-light {{ $match->is_home ? 'fa-house-chimney' : 'fa-bus' }} text-[10px]"></i>
                        {{ $match->is_home ? __('matches.home') : __('matches.away') }}
                    </span>

                    @if($match->match_type)
                        <span class="text-[9px] font-black uppercase tracking-wider md:hidden px-1.5 py-0.5 rounded border {{ $typeColors[$match->match_type] ?? 'bg-slate-50 text-slate-400 border-slate-200' }}">
                            {{ $typeLabels[$match->match_type] ?? $match->match_type }}
                        </span>
                    @endif
                </div>
                <div class="text-xl md:text-2xl font-black uppercase tracking-tight text-secondary flex items-center gap-3">
                    @php
                        $brandingResolver = app(\App\Services\TeamBrandingResolver::class);
                        $homeBranding = $brandingResolver->getMatchLogo($match, true);
                        $awayBranding = $brandingResolver->getMatchLogo($match, false);
                        $isMatchLogoEnabled = $branding['team_logo']['enabled_match_cards'] ?? true;
                    @endphp

                    @if($isMatchLogoEnabled && $homeBranding)
                        <picture class="shrink-0">
                            <source srcset="{{ $homeBranding['logo_url_webp'] }}" type="image/webp">
                            <img src="{{ $homeBranding['logo_url'] }}" alt="" class="object-contain" style="height: {{ $branding['team_logo']['sizes']['match_card'] ?? 36 }}px; width: auto;">
                        </picture>
                    @endif

                    <span class="{{ $match->is_home ? 'text-primary' : '' }}">
                        {{ $match->is_home ? $match->official_team_name : $match->official_opponent_name }}
                    </span>
                    <span class="text-slate-300 mx-1">vs</span>
                    <span class="{{ !$match->is_home ? 'text-primary' : '' }}">
                        {{ $match->is_home ? $match->official_opponent_name : $match->official_team_name }}
                    </span>

                    @if($isMatchLogoEnabled && $awayBranding)
                        <picture class="shrink-0">
                            <source srcset="{{ $awayBranding['logo_url_webp'] }}" type="image/webp">
                            <img src="{{ $awayBranding['logo_url'] }}" alt="" class="object-contain" style="height: {{ $branding['team_logo']['sizes']['match_card'] ?? 36 }}px; width: auto;">
                        </picture>
                    @endif
                </div>
                <div class="flex items-center mt-2 text-slate-500 text-sm font-medium italic">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $match->location ?? 'Místo neuvedeno' }}
                </div>
            </div>

            <div class="flex flex-row flex-wrap items-center justify-center sm:justify-end min-w-[120px] gap-2">
                @if($isPlayed && $hasScore)
                    <div class="flex flex-col items-center sm:items-end">
                        <div class="flex items-center gap-2">
                            <div class="text-3xl md:text-4xl font-black tabular-nums tracking-tighter {{ $isWin ? 'text-success' : ($isLoss ? 'text-danger' : 'text-secondary') }} flex items-center gap-2">
                                <span>{{ $match->score_home ?? 0 }}</span>
                                <span class="opacity-30">:</span>
                                <span>{{ $match->score_away ?? 0 }}</span>
                            </div>
                        </div>
                        <span class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest mt-1 {{ $isWin ? 'text-success' : ($isLoss ? 'text-danger' : 'text-slate-400') }}">
                            @if($isWin)
                                <i class="fa-light fa-trophy-star text-xs"></i>
                            @elseif($isLoss)
                                <i class="fa-light fa-face-frown text-xs"></i>
                            @else
                                <i class="fa-light fa-handshake text-xs"></i>
                            @endif
                            {{ $isWin ? __('matches.victory') : ($isLoss ? __('matches.loss') : __('matches.draw')) }}
                        </span>
                    </div>
                @elseif($isPlayed)
                    <div class="flex flex-col items-center sm:items-end">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 border border-blue-100">
                            {{ __('matches.action_took_place') }}
                        </span>
                        <span class="text-[9px] font-bold text-slate-400 mt-1 italic uppercase tracking-tighter">
                            {{ __('matches.result_missing') }}
                        </span>
                    </div>
                @else
                    @php
                        $isPast = $match->scheduled_at ? $match->scheduled_at->isPast() : ($match->season_id < 3);
                    @endphp
                    @if($isPast)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 border border-blue-100">
                            {{ __('matches.action_took_place') }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$match->status] ?? 'bg-slate-100' }}">
                            {{ $statusLabels[$match->status] ?? $match->status }}
                        </span>
                    @endif
                @endif

                @if(!$isPlayed && $prediction)
                    @php
                        $winChance = round($prediction->probability_win * 100);
                        if ($winChance <= 50) {
                            $hue = ($winChance / 50) * 35;
                        } else {
                            $hue = 35 + (($winChance - 50) / 50) * (105);
                        }
                        $colorHsl = "hsl({$hue}, 75%, 45%)";
                        $bgHsl = "hsla({$hue}, 75%, 45%, 0.05)";
                        $borderHsl = "hsla({$hue}, 75%, 45%, 0.15)";
                    @endphp
                    <div class="flex items-center gap-2 px-2 py-1 rounded-2xl border shadow-sm transition-all group/pred"
                         style="background: linear-gradient(135deg, {{ $bgHsl }}, white); border-color: {{ $borderHsl }};">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-light fa-crystal-ball text-[10px] animate-pulse" style="color: {{ $colorHsl }};"></i>
                            <span class="text-[9px] font-black uppercase tracking-widest" style="color: {{ $colorHsl }};">{{ $winChance }}%</span>
                        </div>
                        <div class="h-2 w-px opacity-30" style="background-color: {{ $colorHsl }};"></div>
                        <div class="flex -space-x-1">
                            @php
                                $confColor = match($prediction->confidence) {
                                    'high' => 'text-emerald-500',
                                    'medium' => 'text-amber-500',
                                    default => 'text-slate-400',
                                };
                            @endphp
                            <i class="fa-solid fa-circle text-[5px] {{ $confColor }}"></i>
                        </div>
                    </div>
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
