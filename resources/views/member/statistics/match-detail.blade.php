@extends('layouts.member', [
    'title' => $title ?? __('matches.match_detail'),
])

@section('content')
    <div class="space-y-8 pb-12">
        {{-- Header / Scoreboard --}}
        <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-600/10 via-transparent to-brand-500/5"></div>

            <div class="relative p-8 md:p-12">
                <div class="flex flex-col items-center space-y-8">
                    {{-- Match Info Top --}}
                    <div class="flex flex-wrap justify-center gap-4 text-sm font-medium text-gray-500">
                        <span class="flex items-center gap-2 px-4 py-1.5 bg-gray-100 rounded-full">
                            <i class="fa-light fa-calendar"></i>
                            {{ $match->scheduled_at?->format('d. m. Y H:i') }}
                        </span>
                        <span class="flex items-center gap-2 px-4 py-1.5 bg-gray-100 rounded-full">
                            <i class="fa-light fa-trophy"></i>
                            {{ $match->match_type ? (__('matches.type_' . strtolower($match->match_type)) !== 'matches.type_' . strtolower($match->match_type) ? __('matches.type_' . strtolower($match->match_type)) : $match->match_type) : __('matches.type_mi') }}
                        </span>
                        @if($match->metadata['venue'] ?? $match->location)
                            <span class="flex items-center gap-2 px-4 py-1.5 bg-gray-100 rounded-full">
                                <i class="fa-light fa-location-dot text-brand-500"></i>
                                {{ $match->metadata['venue'] ?? $match->location }}
                            </span>
                        @endif
                        @if(!empty($match->metadata['referees']))
                            <span class="flex items-center gap-2 px-4 py-1.5 bg-gray-100 rounded-full">
                                <i class="fa-light fa-whistle text-brand-500"></i>
                                {{ $match->metadata['referees'] }}
                            </span>
                        @endif
                        @if(!empty($match->metadata['external_id']))
                            <a href="https://cz.basketball/zapas/{{ $match->metadata['external_id'] }}" target="_blank" class="flex items-center gap-2 px-4 py-1.5 bg-brand-50 text-brand-600 rounded-full hover:bg-brand-100 transition-colors">
                                <i class="fa-light fa-basketball"></i>
                                {{ __('matches.external_detail') }}
                                <i class="fa-light fa-up-right-from-square text-[10px]"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Teams and Score --}}
                    <div class="w-full flex flex-col md:flex-row items-center justify-between gap-8 md:gap-4 max-w-5xl">
                        <div class="flex-1 text-center md:text-right group/team">
                            <h2 class="text-xl md:text-2xl font-black text-gray-900 mb-2 tracking-tight group-hover/team:text-brand-600 transition-colors">
                                {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                            </h2>
                            <span class="px-4 py-1.5 bg-brand-100 text-brand-700 text-[10px] font-black rounded-full uppercase tracking-[0.2em] shadow-sm border border-brand-200/50">{{ __('matches.is_home') }}</span>
                        </div>

                        <div class="flex flex-col items-center group/score">
                            <div class="flex items-center gap-4 md:gap-8">
                                <div class="text-4xl md:text-6xl font-black tabular-nums tracking-tighter text-gray-900 drop-shadow-sm group-hover/score:scale-105 transition-transform duration-500">
                                    {{ $match->score_home ?? 0 }}
                                </div>
                                <div class="text-2xl md:text-4xl font-black text-brand-500 select-none">:</div>
                                <div class="text-4xl md:text-6xl font-black tabular-nums tracking-tighter text-gray-900 drop-shadow-sm group-hover/score:scale-105 transition-transform duration-500">
                                    {{ $match->score_away ?? 0 }}
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 text-center md:text-left group/team">
                            <h2 class="text-xl md:text-2xl font-black text-gray-900 mb-2 tracking-tight group-hover/team:text-brand-600 transition-colors">
                                {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                            </h2>
                            <span class="px-4 py-1.5 bg-gray-100 text-gray-600 text-[10px] font-black rounded-full uppercase tracking-[0.2em] shadow-sm border border-gray-200/50">{{ __('matches.is_away') }}</span>
                        </div>
                    </div>

                    {{-- Additional Info (Referees, Attendance) --}}
                    @if(!empty($match->metadata['referees']) || !empty($match->metadata['attendance']))
                        <div class="pt-8 border-t border-gray-100 w-full flex flex-wrap justify-center gap-x-12 gap-y-4">
                            @if(!empty($match->metadata['referees']))
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                        <i class="fa-light fa-whistle text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('matches.referees') }}</span>
                                        <span class="text-sm font-semibold text-gray-700">{{ $match->metadata['referees'] }}</span>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($match->metadata['attendance']))
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                        <i class="fa-light fa-users text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('matches.attendance') }}</span>
                                        <span class="text-sm font-semibold text-gray-700">{{ $match->metadata['attendance'] }}</span>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($match->metadata['commissioner']))
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                        <i class="fa-light fa-user-tie text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('matches.commissioner') }}</span>
                                        <span class="text-sm font-semibold text-gray-700">{{ $match->metadata['commissioner'] }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left Column: Pre-match / Analysis --}}
            <div class="lg:col-span-5 space-y-8 order-2 lg:order-1">
                <div class="flex flex-col gap-2 px-4 py-6 bg-gradient-to-r from-gray-50 to-transparent rounded-2xl border-l-4 border-brand-500 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center text-brand-600 shadow-sm">
                            <i class="fa-light fa-magnifying-glass-chart text-xl"></i>
                        </div>
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-widest">
                            {{ __('matches.pre_match_stats') }}
                        </h2>
                    </div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-13">Komplexní analýza před úvodním hvizdem</p>
                </div>

                {{-- Best Players (Lídři zápasu / Klíčoví hráči) --}}
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center gap-3">
                        <i class="fa-light fa-star text-brand-500"></i>
                        {{ __('matches.best_players') }}
                    </h3>

                    @php
                        $bestPlayers = $match->metadata['best_players_external'] ?? $match->metadata['best_players'] ?? [];
                    @endphp

                    @if(!empty($bestPlayers))
                        <div class="space-y-10">
                            @foreach($bestPlayers as $category => $players)
                                @if(is_array($players) && (isset($players['home']) || isset($players['away'])))
                                    @php
                                        $localizedCategory = __('matches.' . $category);
                                        if ($localizedCategory === 'matches.' . $category) {
                                            $localizedCategory = $players['label'] ?? $category;
                                        }
                                    @endphp
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-px flex-1 bg-gray-100"></div>
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">{{ $localizedCategory }}</span>
                                            <div class="h-px flex-1 bg-gray-100"></div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4">
                                            @foreach(['home', 'away'] as $side)
                                                @if(!empty($players[$side]))
                                                    <div class="group relative flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100 transition-all hover:shadow-xl hover:bg-white hover:border-brand-200 hover:-translate-y-1">
                                                        <div class="relative flex-shrink-0">
                                                            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-200 shadow-inner group-hover:scale-110 transition-transform duration-500">
                                                                @if(!empty($players[$side]['photo_url']))
                                                                    <img src="{{ $players[$side]['photo_url'] }}" alt="{{ $players[$side]['name'] }}" class="w-full h-full object-cover">
                                                                @else
                                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                        <i class="fa-light fa-user text-2xl"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full {{ ($side === 'home' && $match->is_home) || ($side === 'away' && ! $match->is_home) ? 'bg-brand-500' : 'bg-gray-400' }} text-white flex items-center justify-center text-xs shadow-lg border-2 border-white">
                                                                <i class="fa-solid fa-crown"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow min-w-0">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <span class="px-2 py-0.5 {{ ($side === 'home' && $match->is_home) || ($side === 'away' && ! $match->is_home) ? 'bg-brand-100 text-brand-700' : 'bg-gray-200 text-gray-600' }} text-[8px] font-black rounded-md uppercase tracking-widest">
                                                                    {{ ($side === 'home' && $match->is_home) || ($side === 'away' && ! $match->is_home) ? 'Náš hráč' : 'Soupeř' }}
                                                                </span>
                                                            </div>
                                                            <span class="text-base font-black text-gray-900 leading-tight block truncate group-hover:text-brand-600 transition-colors">{{ $players[$side]['name'] }}</span>
                                                        </div>
                                                        <div class="flex-shrink-0 text-right bg-white rounded-xl p-2 shadow-sm border border-gray-50 group-hover:border-brand-100 transition-all">
                                                            <span class="text-2xl font-black text-brand-600 tabular-nums leading-none">{{ $players[$side]['value'] ?? '' }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <i class="fa-light fa-user-slash text-4xl text-gray-200 mb-4 block"></i>
                            <p class="text-sm text-gray-400 italic">{{ __('matches.empty_best_players') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Team Comparison (Srovnání kádrů) --}}
                @php
                    $teamComparison = $match->metadata['team_comparison'] ?? [];
                @endphp

                @if(!empty($teamComparison))
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center gap-3">
                            <i class="fa-light fa-scale-balanced text-brand-500"></i>
                            {{ __('matches.team_comparison') }}
                        </h3>

                        <div class="space-y-6">
                            @foreach($teamComparison as $key => $data)
                                <div class="bg-gray-50 rounded-[2rem] p-8 border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:bg-white group overflow-hidden relative">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-brand-500/5 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-700"></div>

                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6 block text-center group-hover:text-brand-500 transition-colors relative">
                                        {{ $data['label'] ?? $key }}
                                    </span>

                                    <div class="flex items-center justify-between gap-6 relative">
                                        <div class="text-center flex-1">
                                            <span class="px-2 py-0.5 bg-brand-100 text-brand-700 text-[8px] font-black rounded-md uppercase tracking-widest block mb-2 mx-auto w-fit">
                                                {{ $match->is_home ? 'Domácí' : 'Soupeř' }}
                                            </span>
                                            <span class="text-4xl font-black text-brand-600 tabular-nums drop-shadow-sm">{{ $data['home'] }}</span>
                                        </div>

                                        <div class="flex flex-col items-center gap-2">
                                            <div class="h-12 w-px bg-gradient-to-b from-transparent via-gray-200 to-transparent"></div>
                                            <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                                            <div class="h-12 w-px bg-gradient-to-t from-transparent via-gray-200 to-transparent"></div>
                                        </div>

                                        <div class="text-center flex-1">
                                            <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[8px] font-black rounded-md uppercase tracking-widest block mb-2 mx-auto w-fit">
                                                {{ $match->is_home ? 'Soupeř' : 'Domácí' }}
                                            </span>
                                            <span class="text-4xl font-black text-gray-900 tabular-nums drop-shadow-sm">{{ $data['away'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Last Matches --}}
                @php
                    $lastMatches = $match->metadata['last_matches'] ?? [];
                @endphp

                @if(!empty($lastMatches['home']) || !empty($lastMatches['away']))
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center gap-3">
                            <i class="fa-light fa-history text-brand-500"></i>
                            {{ __('matches.last_matches') }}
                        </h3>

                        <div class="space-y-10">
                            {{-- Home Team Last Matches --}}
                            @if(!empty($lastMatches['home']))
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-4 mb-2">
                                            <div class="w-2 h-2 rounded-full bg-brand-500"></div>
                                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">
                                                {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                                            </h4>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($lastMatches['home'] as $m)
                                                <div class="flex items-center justify-between p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm text-sm group hover:bg-white hover:border-brand-200 hover:shadow-xl hover:-translate-y-1 transition-all">
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-widest">{{ $m['date'] }}</span>
                                                        <span class="font-bold text-gray-700 leading-tight group-hover:text-brand-600 transition-colors">{{ $m['team_home'] }} vs {{ $m['team_away'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-4">
                                                        <span class="text-xl font-black tabular-nums {{ (int)$m['score_home'] > (int)$m['score_away'] ? 'text-brand-600' : 'text-gray-900' }}">
                                                            {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                                        </span>
                                                        @if(!empty($m['external_id']))
                                                            <a href="https://cz.basketball/zapas/{{ $m['external_id'] }}" target="_blank" class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-300 hover:text-brand-500 hover:shadow-md border border-gray-100 hover:border-brand-100 transition-all">
                                                                <i class="fa-light fa-chevron-right text-sm"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                            @endif

                            {{-- Away Team Last Matches --}}
                            @if(!empty($lastMatches['away']))
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-4 mb-2">
                                            <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">
                                                {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                                            </h4>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($lastMatches['away'] as $m)
                                                <div class="flex items-center justify-between p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm text-sm group hover:bg-white hover:border-brand-200 hover:shadow-xl hover:-translate-y-1 transition-all">
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-widest">{{ $m['date'] }}</span>
                                                        <span class="font-bold text-gray-700 leading-tight group-hover:text-brand-600 transition-colors">{{ $m['team_home'] }} vs {{ $m['team_away'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-4">
                                                        <span class="text-xl font-black tabular-nums {{ (int)$m['score_home'] > (int)$m['score_away'] ? 'text-brand-600' : 'text-gray-900' }}">
                                                            {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                                        </span>
                                                        @if(!empty($m['external_id']))
                                                            <a href="https://cz.basketball/zapas/{{ $m['external_id'] }}" target="_blank" class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-300 hover:text-brand-500 hover:shadow-md border border-gray-100 hover:border-brand-100 transition-all">
                                                                <i class="fa-light fa-chevron-right text-sm"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Stats / Boxscore --}}
            <div class="lg:col-span-7 space-y-8 order-3 lg:order-2">
                <div class="flex flex-col gap-2 px-4 py-6 bg-gradient-to-r from-emerald-50/50 to-transparent rounded-2xl border-l-4 border-emerald-500 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                            <i class="fa-light fa-chart-simple text-xl"></i>
                        </div>
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-widest">
                            {{ __('matches.match_stats') }}
                        </h2>
                    </div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-13">Konečné výsledky a individuální výkony</p>
                </div>

                {{-- Win/Loss Badge Card (Result Summary) --}}
                @php
                    $isVictory = ($match->is_home && $match->score_home > $match->score_away) || (!$match->is_home && $match->score_away > $match->score_home);
                    $isDraw = $match->score_home == $match->score_away;

                    $bgClass = $isVictory ? 'bg-emerald-600' : ($isDraw ? 'bg-slate-600' : 'bg-rose-600');
                    $accentClass = $isVictory ? 'bg-emerald-500/20' : ($isDraw ? 'bg-slate-500/20' : 'bg-rose-500/20');
                    $glowClass = $isVictory ? 'shadow-emerald-500/20' : ($isDraw ? 'shadow-slate-500/20' : 'shadow-rose-500/20');
                @endphp

                <div class="{{ $bgClass }} rounded-3xl shadow-2xl {{ $glowClass }} p-8 md:p-10 text-white relative overflow-hidden group transition-all duration-500">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-80 h-80 bg-white/10 rounded-full blur-3xl transition-all duration-700 group-hover:bg-white/20 group-hover:scale-110"></div>
                    <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-black/20 rounded-full blur-2xl"></div>

                    <div class="relative flex flex-col md:flex-row items-center justify-between gap-10">
                        <div class="flex flex-col md:flex-row items-center gap-6 md:gap-10 text-center md:text-left">
                            @if($isVictory)
                                <div class="w-24 h-24 bg-white/20 rounded-3xl flex-shrink-0 flex items-center justify-center backdrop-blur-md shadow-inner border border-white/30 animate-bounce-subtle">
                                    <i class="fa-light fa-trophy-star text-5xl text-yellow-300 drop-shadow-[0_0_15px_rgba(253,224,71,0.5)]"></i>
                                </div>
                                <div class="flex flex-col items-center md:items-start">
                                    <h4 class="text-4xl font-black uppercase tracking-tighter mb-1">{{ __('matches.victory') }}</h4>
                                    <p class="text-emerald-50/80 font-medium text-sm md:text-base max-w-xs">Skvělý výkon! Tento zápas skončil vítězně.</p>
                                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-2">
                                        <span class="px-3 py-1.5 bg-white/20 rounded-xl text-[10px] font-black uppercase tracking-widest backdrop-blur-sm border border-white/10">Dominance</span>
                                        <span class="px-3 py-1.5 bg-white/20 rounded-xl text-[10px] font-black uppercase tracking-widest backdrop-blur-sm border border-white/10">Týmová práce</span>
                                    </div>
                                </div>
                            @elseif($isDraw)
                                 <div class="w-24 h-24 bg-white/20 rounded-3xl flex-shrink-0 flex items-center justify-center backdrop-blur-md shadow-inner border border-white/30">
                                    <i class="fa-light fa-handshake text-5xl"></i>
                                </div>
                                <div class="flex flex-col items-center md:items-start">
                                    <h4 class="text-4xl font-black uppercase tracking-tighter mb-1">{{ __('matches.draw') }}</h4>
                                    <p class="text-slate-50/80 font-medium text-sm md:text-base max-w-xs">Tento zápas skončil nerozhodně.</p>
                                </div>
                            @else
                                <div class="w-24 h-24 bg-white/20 rounded-3xl flex-shrink-0 flex items-center justify-center backdrop-blur-md shadow-inner border border-white/30">
                                    <i class="fa-light fa-basketball text-5xl text-brand-100"></i>
                                </div>
                                <div class="flex flex-col items-center md:items-start">
                                    <h4 class="text-4xl font-black uppercase tracking-tighter mb-1">{{ __('matches.loss') }}</h4>
                                    <p class="text-rose-50/80 font-medium text-sm md:text-base max-w-xs">Tentokrát to nevyšlo, ale příště budeme silnější!</p>
                                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-2">
                                        <span class="px-3 py-1.5 bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest backdrop-blur-sm border border-white/5">Zkušenost</span>
                                        <span class="px-3 py-1.5 bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest backdrop-blur-sm border border-white/5">Příště lépe</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-center justify-center bg-white/10 rounded-[2.5rem] p-10 backdrop-blur-md border border-white/20 min-w-[200px] shadow-2xl transition-all duration-500 group-hover:bg-white/15">
                            <span class="text-[10px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Konečné skóre</span>
                            <div class="flex items-center gap-4">
                                <span class="text-6xl font-black tabular-nums drop-shadow-lg leading-none">{{ $match->score_home }}</span>
                                <span class="text-3xl font-black text-white/30">:</span>
                                <span class="text-6xl font-black tabular-nums drop-shadow-lg leading-none">{{ $match->score_away }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Periods Detailed --}}
                @if(!empty($match->metadata['periods_detailed']))
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <i class="fa-light fa-list-ol text-brand-500"></i>
                            {{ __('matches.periods') }}
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($match->metadata['periods_detailed'] as $period)
                                <div class="flex flex-col items-center justify-center p-5 bg-gray-50 rounded-3xl border border-gray-100 shadow-sm transition-all hover:bg-white hover:border-brand-100 hover:shadow-xl hover:-translate-y-1 group">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 group-hover:text-brand-500 transition-colors">{{ $loop->iteration }}. čtvrtina</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-3xl font-black text-gray-900 tabular-nums">
                                            {{ $period['home'] }}
                                        </span>
                                        <span class="text-lg font-black text-gray-300">:</span>
                                        <span class="text-3xl font-black text-gray-900 tabular-nums">
                                            {{ $period['away'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                    @php
                        $boxscoreSets = \App\Models\StatisticSet::whereIn('slug', ['match-boxscore', 'match-boxscore-external'])->pluck('id')->toArray();
                        $allStats = \App\Models\StatisticRow::where('basketball_match_id', $match->id)
                            ->with(['player', 'team'])
                            ->whereIn('statistic_set_id', $boxscoreSets)
                            ->get();

                        $ourStatsRaw = $allStats->filter(function($s) use ($match) {
                             if ($s->team_id && $s->team_id == $match->team_id) return true;
                             $meta = is_array($s->source_metadata) ? $s->source_metadata : [];
                             return ($meta['is_opponent'] ?? false) === false;
                        });

                        // Statistiky soupeře z metadat (nový způsob) nebo z StatisticRow (fallback)
                        $opponentData = $match->metadata['opponent_boxscore'] ?? null;
                        if ($opponentData) {
                            $opponentStatsRaw = $opponentData['rows'] ?? [];
                        } else {
                            $opponentStatsRaw = $allStats->filter(function($s) {
                                if ($s->opponent_id) return true;
                                $meta = is_array($s->source_metadata) ? $s->source_metadata : [];
                                return ($meta['is_opponent'] ?? false) === true;
                            });
                        }

                        // Detekce, zda máme rozšířené statistiky
                        $hasExtended = false;
                        foreach($ourStatsRaw as $s) {
                            $v = is_object($s) ? $s->values : ($s['values'] ?? []);
                            if (!empty($v['rebounds']) || !empty($v['assists']) || !empty($v['steals']) || !empty($v['efficiency'])) {
                                $hasExtended = true; break;
                            }
                        }

                        // Pomocná funkce pro transformaci pro Alpine.js
                        $transformForJs = function($stats) {
                            return collect($stats)->map(function($s) {
                                $rowLabel = is_object($s) ? $s->row_label : ($s['row_label'] ?? '');
                                $values = is_object($s) ? $s->values : ($s['values'] ?? []);
                                $sourceMetadata = is_object($s) ? ($s->source_metadata ?? []) : ($s['metadata'] ?? []);
                                $player = is_object($s) ? $s->player : null;

                                return [
                                    'id' => is_object($s) ? $s->id : (isset($s['id']) ? $s['id'] : uniqid()),
                                    'name' => $player?->name ?? ($rowLabel ?: ($values['col_1'] ?? 'Hráč')),
                                    'number' => $values['col_0'] ?? ($sourceMetadata['jersey'] ?? ($sourceMetadata['player_number'] ?? '#')),
                                    'is_starter' => !empty($values['is_starter']) || !empty($sourceMetadata['is_starter']),
                                    'pts' => (int)($values['pts'] ?? ($values['points'] ?? 0)),
                                    'fg2_made' => (int)($values['fg2_made'] ?? 0),
                                    'fg3_made' => (int)($values['fg3_made'] ?? 0),
                                    'ft_made' => (int)($values['ft_made'] ?? 0),
                                    'ft_att' => (int)($values['ft_att'] ?? 0),
                                    'fouls' => (int)($values['fouls'] ?? ($values['f_minus'] ?? 0)),
                                    'plus_minus' => (int)($values['plus_minus'] ?? 0),
                                    'minutes' => $values['minutes'] ?? ($values['min'] ?? '-'),
                                    'rebounds' => (int)($values['rebounds'] ?? ($values['reb'] ?? 0)),
                                    'assists' => (int)($values['assists'] ?? ($values['ast'] ?? 0)),
                                    'steals' => (int)($values['steals'] ?? ($values['stl'] ?? 0)),
                                    'turnovers' => (int)($values['turnovers'] ?? ($values['tov'] ?? 0)),
                                    'blocks' => (int)($values['blocks'] ?? ($values['blk'] ?? 0)),
                                    'fouls_drawn' => (int)($values['fouls_drawn'] ?? ($values['f_plus'] ?? 0)),
                                    'efficiency' => (int)($values['efficiency'] ?? ($values['val'] ?? 0)),
                                    'is_special' => in_array(mb_strtolower($rowLabel), ['celkem', 'total', 'tým/trenéři', 'team/coaches']),
                                    'is_team' => in_array(mb_strtolower($rowLabel), ['tým/trenéři', 'team/coaches']),
                                    'row_label' => $rowLabel
                                ];
                            })->values()->toArray();
                        };

                        $ourStats = $transformForJs($ourStatsRaw);
                        $opponentStats = $transformForJs($opponentStatsRaw);
                    @endphp

                {{-- Boxscore Table Card --}}
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden"
                    x-data="{
                        activeTab: 'ours',
                        sortField: 'pts',
                        sortDirection: 'desc',
                        ourStats: {{ json_encode($ourStats) }},
                        opponentStats: {{ json_encode($opponentStats) }},
                        sortBy(field) {
                            if (this.sortField === field) {
                                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                            } else {
                                this.sortField = field;
                                this.sortDirection = 'desc';
                            }
                        },
                        getSortedStats(stats) {
                            const players = stats.filter(s => !s.is_special);
                            const specials = stats.filter(s => s.is_special);

                            players.sort((a, b) => {
                                let valA = a[this.sortField];
                                let valB = b[this.sortField];

                                if (typeof valA === 'string') valA = valA.toLowerCase();
                                if (typeof valB === 'string') valB = valB.toLowerCase();

                                if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                                if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                                return 0;
                            });

                            return [...players, ...specials];
                        }
                    }">
                        <div class="p-8 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                                    <i class="fa-light fa-chart-user text-brand-500"></i>
                                    {{ __('matches.boxscore') }}
                                </h3>

                                {{-- Custom Tabs Trigger --}}
                                <div class="flex p-1 bg-gray-200/50 rounded-xl">
                                    <button
                                        @click="activeTab = 'ours'"
                                        :class="activeTab === 'ours' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                        class="px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-2"
                                    >
                                        <i class="fa-light fa-shield-halved"></i>
                                        {{ $match->team->name }}
                                    </button>
                                    <button
                                        @click="activeTab = 'opponent'"
                                        :class="activeTab === 'opponent' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                        class="px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-2"
                                    >
                                        <i class="fa-light fa-shield"></i>
                                        {{ $match->opponent?->name ?? __('matches.opponent') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Our Team Tab --}}
                        <div x-show="activeTab === 'ours'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50/90 sticky top-0 z-10 backdrop-blur-md">
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">#</th>
                                            <th @click="sortBy('name')" class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 cursor-pointer hover:text-brand-500 transition-colors">
                                                Hráč
                                                <template x-if="sortField === 'name'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('fg2_made')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                2B
                                                <template x-if="sortField === 'fg2_made'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('fg3_made')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                3B
                                                <template x-if="sortField === 'fg3_made'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('ft_made')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                TH
                                                <template x-if="sortField === 'ft_made'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('fouls')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                F-
                                                <template x-if="sortField === 'fouls'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('pts')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                Body
                                                <template x-if="sortField === 'pts'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('plus_minus')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                +/-
                                                <template x-if="sortField === 'plus_minus'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            @if($hasExtended)
                                                <th @click="sortBy('minutes')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">Min</th>
                                                <th @click="sortBy('rebounds')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">REB</th>
                                                <th @click="sortBy('assists')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">AST</th>
                                                <th @click="sortBy('steals')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">STL</th>
                                                <th @click="sortBy('turnovers')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">TOV</th>
                                                <th @click="sortBy('blocks')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">BLK</th>
                                                <th @click="sortBy('fouls_drawn')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">F+</th>
                                                <th @click="sortBy('efficiency')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">VAL</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(stat, index) in getSortedStats(ourStats)" :key="stat.id">
                                            <tr :class="stat.is_special ? 'bg-gray-100/50' : 'hover:bg-brand-50/30 transition-colors'">
                                                <td class="px-6 py-4 text-center text-[10px] font-black text-gray-300">
                                                    <span x-show="!stat.is_special" x-text="(index + 1) + '.'"></span>
                                                    <i x-show="stat.is_special && !stat.is_team" class="fa-light fa-sigma text-gray-400"></i>
                                                    <i x-show="stat.is_team" class="fa-light fa-users-gear text-gray-400"></i>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-3">
                                                        <div x-show="!stat.is_special" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-400" x-text="stat.number"></div>
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold" :class="stat.is_special ? 'text-gray-900' : 'text-gray-700'" x-text="stat.name"></span>
                                                            <span x-show="stat.is_starter" class="text-[9px] font-bold text-brand-500 uppercase tracking-tighter">Základní pětka</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg2_made"></td>
                                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg3_made"></td>
                                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : (stat.ft_made + '/' + stat.ft_att)"></td>
                                                <td class="px-4 py-4 text-center text-sm font-bold text-red-500 tabular-nums" x-text="stat.fouls"></td>
                                                <td class="px-4 py-4 text-center">
                                                    <span class="text-base font-black text-gray-900 tabular-nums" x-text="stat.is_team ? '-' : stat.pts"></span>
                                                </td>
                                                <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.plus_minus"></td>
                                                @if($hasExtended)
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.minutes"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.rebounds"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.assists"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.steals"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums text-red-400" x-text="stat.is_team ? '-' : stat.turnovers"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.blocks"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-bold text-green-600 tabular-nums" x-text="stat.is_team ? '-' : stat.fouls_drawn"></td>
                                                    <td class="px-4 py-4 text-center">
                                                        <template x-if="!stat.is_team">
                                                            <span class="px-2 py-1 rounded-lg text-xs font-black tabular-nums"
                                                                :class="stat.efficiency >= 15 ? 'bg-green-100 text-green-700' : (stat.efficiency < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')"
                                                                x-text="stat.efficiency">
                                                            </span>
                                                        </template>
                                                        <template x-if="stat.is_team">
                                                            <span>-</span>
                                                        </template>
                                                    </td>
                                                @endif
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Opponent Tab --}}
                        <div x-show="activeTab === 'opponent'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50/90 sticky top-0 z-10 backdrop-blur-md">
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">#</th>
                                            <th @click="sortBy('name')" class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 cursor-pointer hover:text-brand-500 transition-colors">
                                                {{ $match->opponent?->name ?? 'Soupeř' }}
                                                <template x-if="sortField === 'name'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('fg2_made')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                2B
                                                <template x-if="sortField === 'fg2_made'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('fg3_made')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                3B
                                                <template x-if="sortField === 'fg3_made'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('ft_made')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                TH
                                                <template x-if="sortField === 'ft_made'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('fouls')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                F-
                                                <template x-if="sortField === 'fouls'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('pts')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                Body
                                                <template x-if="sortField === 'pts'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            <th @click="sortBy('plus_minus')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">
                                                +/-
                                                <template x-if="sortField === 'plus_minus'">
                                                    <i :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fa-light ml-1"></i>
                                                </template>
                                            </th>
                                            @if($hasExtended)
                                                <th @click="sortBy('minutes')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">Min</th>
                                                <th @click="sortBy('rebounds')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">REB</th>
                                                <th @click="sortBy('assists')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">AST</th>
                                                <th @click="sortBy('steals')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">STL</th>
                                                <th @click="sortBy('turnovers')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">TOV</th>
                                                <th @click="sortBy('blocks')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">BLK</th>
                                                <th @click="sortBy('fouls_drawn')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">F+</th>
                                                <th @click="sortBy('efficiency')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center cursor-pointer hover:text-brand-500 transition-colors">VAL</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(stat, index) in getSortedStats(opponentStats)" :key="stat.id">
                                            <tr :class="stat.is_special ? 'bg-gray-100/50' : 'hover:bg-brand-50/30 transition-colors'">
                                                <td class="px-6 py-4 text-center text-[10px] font-black text-gray-300">
                                                    <span x-show="!stat.is_special" x-text="(index + 1) + '.'"></span>
                                                    <i x-show="stat.is_special && !stat.is_team" class="fa-light fa-sigma text-gray-400"></i>
                                                    <i x-show="stat.is_team" class="fa-light fa-users-gear text-gray-400"></i>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-3">
                                                        <div x-show="!stat.is_special" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-400" x-text="stat.number"></div>
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold" :class="stat.is_special ? 'text-gray-900' : 'text-gray-700'" x-text="stat.name"></span>
                                                            <span x-show="stat.is_starter" class="text-[9px] font-bold text-brand-500 uppercase tracking-tighter">Základní pětka</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg2_made"></td>
                                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg3_made"></td>
                                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : (stat.ft_made + '/' + stat.ft_att)"></td>
                                                <td class="px-4 py-4 text-center text-sm font-bold text-red-500 tabular-nums" x-text="stat.fouls"></td>
                                                <td class="px-4 py-4 text-center">
                                                    <span class="text-base font-black text-gray-900 tabular-nums" x-text="stat.is_team ? '-' : stat.pts"></span>
                                                </td>
                                                <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.plus_minus"></td>
                                                @if($hasExtended)
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.minutes"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.rebounds"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.assists"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.steals"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums text-red-400" x-text="stat.is_team ? '-' : stat.turnovers"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.blocks"></td>
                                                    <td class="px-4 py-4 text-center text-sm font-bold text-green-600 tabular-nums" x-text="stat.is_team ? '-' : stat.fouls_drawn"></td>
                                                    <td class="px-4 py-4 text-center">
                                                        <template x-if="!stat.is_team">
                                                            <span class="px-2 py-1 rounded-lg text-xs font-black tabular-nums"
                                                                :class="stat.efficiency >= 15 ? 'bg-green-100 text-green-700' : (stat.efficiency < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')"
                                                                x-text="stat.efficiency">
                                                            </span>
                                                        </template>
                                                        <template x-if="stat.is_team">
                                                            <span>-</span>
                                                        </template>
                                                    </td>
                                                @endif
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- External Link --}}
                @if(!empty($match->metadata['external_id']))
                    <div class="flex justify-center pt-8">
                        <a href="https://cz.basketball/zapas/{{ $match->metadata['external_id'] }}" target="_blank" class="flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-brand-500 transition-colors group">
                            <i class="fa-light fa-external-link transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"></i>
                            Zobrazit zápas na cz.basketball
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
