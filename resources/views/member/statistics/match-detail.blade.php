@extends('layouts.member', [
    'title' => $title ?? __('matches.match_detail'),
])

@section('content')
    <div class="space-y-4 pb-12">
        @php
            $isVictory = ($match->is_home && $match->score_home > $match->score_away) || (!$match->is_home && $match->score_away > $match->score_home);
            $isDraw = $match->score_home == $match->score_away;
            $hasScore = !is_null($match->score_home) && !is_null($match->score_away);

            if ($hasScore) {
                $bgClass = $isVictory
                    ? 'bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700'
                    : ($isDraw ? 'bg-gradient-to-br from-slate-600 via-slate-600 to-gray-700' : 'bg-gradient-to-br from-rose-600 via-rose-600 to-pink-700');

                $glowClass = $isVictory ? 'shadow-emerald-500/40' : ($isDraw ? 'shadow-slate-500/40' : 'shadow-rose-500/40');
                $resultText = $isVictory ? __('matches.victory') : ($isDraw ? __('matches.draw') : __('matches.loss'));

                // Random motivational message
                $messages = $isVictory ? __('matches.win_messages') : ($isDraw ? [] : __('matches.loss_messages'));
                $motivationalMessage = !empty($messages) ? $messages[array_rand($messages)] : null;

                // Random badges
                $allBadges = $isVictory ? __('matches.win_badges') : ($isDraw ? [] : __('matches.loss_badges'));
                $badges = [];
                if (!empty($allBadges)) {
                    $keys = array_rand($allBadges, min(2, count($allBadges)));
                    if (is_array($keys)) {
                        foreach($keys as $k) $badges[] = $allBadges[$k];
                    } else {
                        $badges[] = $allBadges[$keys];
                    }
                }
            } else {
                $bgClass = 'bg-gradient-to-br from-brand-600 via-brand-600 to-brand-800';
                $glowClass = 'shadow-brand-500/40';
                $resultText = __('matches.planned');
                $motivationalMessage = null;
                $badges = [];
            }
        @endphp

        <div class="{{ $bgClass }} rounded-[2rem] shadow-[0_20px_40px_-10px_rgba(0,0,0,0.2)] {{ $glowClass }} text-white relative overflow-hidden group transition-all duration-700 border border-white/10">
            {{-- Pokročilé dekorativní prvky na pozadí --}}
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/5 rounded-full blur-[80px] transition-all duration-1000 group-hover:bg-white/10 group-hover:scale-125"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-black/10 rounded-full blur-[60px] transition-all duration-1000 group-hover:scale-110"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-gradient-to-br from-white/5 via-transparent to-black/5 pointer-events-none"></div>

            <div class="relative p-6 md:p-10">
                <div class="flex flex-col items-center space-y-8 md:space-y-10">

                    {{-- Horní informační lišta - Plovoucí pilulky (kompaktnější) --}}
                    <div class="flex flex-wrap justify-center gap-2 text-[9px] md:text-[10px] font-black uppercase tracking-[0.2em]">
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/10 border border-white/20 rounded-full shadow-lg backdrop-blur-md hover:bg-white/20 transition-all cursor-default group/pill">
                            <i class="fa-light fa-calendar text-white/70 group-hover/pill:text-white transition-colors"></i>
                            <span class="text-white/80 group-hover/pill:text-white transition-colors">{{ $match->scheduled_at?->format('d. m. Y H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/10 border border-white/20 rounded-full shadow-lg backdrop-blur-md hover:bg-white/20 transition-all cursor-default group/pill">
                            <i class="fa-light fa-trophy text-white/70 group-hover/pill:text-white transition-colors"></i>
                            <span class="text-white/80 group-hover/pill:text-white transition-colors">
                                {{ $match->match_type ? (__('matches.type_' . strtolower($match->match_type)) !== 'matches.type_' . strtolower($match->match_type) ? __('matches.type_' . strtolower($match->match_type)) : $match->match_type) : __('matches.type_mi') }}
                            </span>
                        </div>
                        @if($match->metadata['venue'] ?? $match->location)
                            <div class="flex items-center gap-2 px-4 py-2 bg-white/10 border border-white/20 rounded-full shadow-lg backdrop-blur-md hover:bg-white/20 transition-all cursor-default group/pill">
                                <i class="fa-light fa-location-dot text-white/70 group-hover/pill:text-white transition-colors"></i>
                                <span class="text-white/80 group-hover/pill:text-white transition-colors">{{ $match->metadata['venue'] ?? $match->location }}</span>
                            </div>
                        @endif
                        @if(!empty($match->metadata['external_id']))
                            <a href="https://cz.basketball/zapas/{{ $match->metadata['external_id'] }}" target="_blank"
                               class="flex items-center gap-2 px-4 py-2 bg-white text-gray-950 font-black rounded-full shadow-xl hover:bg-gray-100 hover:-translate-y-0.5 transition-all group/ext">
                                <i class="fa-light fa-basketball text-brand-600 animate-pulse"></i>
                                <span>{{ __('matches.external_detail') }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Hlavní Scoreboard (více kompaktní a stabilní) --}}
                    <div class="w-full max-w-4xl flex flex-col items-center gap-6">
                        <div class="w-full flex flex-col md:flex-row items-center justify-between gap-4 md:gap-8">

                            {{-- Tým 1 --}}
                            <div class="flex-1 flex flex-col items-center md:items-end text-center md:text-right group/team w-full min-w-0">
                                <div class="w-12 h-12 md:w-16 md:h-16 mb-2 rounded-xl bg-white shadow-lg flex items-center justify-center transition-all duration-500 group-hover/team:scale-105 overflow-hidden p-1 border border-white/20 relative shrink-0">
                                    <div class="absolute inset-0 bg-gray-50/50"></div>
                                    @php
                                        $ourLogo = isset($branding['logo_path']) ? (str_starts_with($branding['logo_path'], 'branding/') ? asset('uploads/'.$branding['logo_path']) : asset($branding['logo_path'])) : asset('assets/img/loga/logo_kbelsti_sokoli_velke.png');
                                    @endphp
                                    @if($match->is_home)
                                        <img src="{{ $ourLogo }}" class="relative max-w-full max-h-full object-contain" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full items-center justify-center text-gray-300">
                                            <i class="fa-light fa-shield-halved text-xl"></i>
                                        </div>
                                    @elseif(!$match->is_home && $match->opponent?->logo)
                                        <img src="{{ asset($match->opponent->logo) }}" class="relative max-w-full max-h-full object-contain" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full items-center justify-center text-gray-300">
                                            <i class="fa-light fa-shield text-xl"></i>
                                        </div>
                                    @else
                                        <i class="fa-light fa-shield-halved text-xl md:text-2xl text-gray-300 relative"></i>
                                    @endif
                                </div>
                                <h2 class="text-sm md:text-lg font-black text-white mb-0.5 tracking-tight group-hover/team:text-white transition-colors leading-tight break-words w-full">
                                    {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                                </h2>
                                <span class="px-1.5 py-0.5 {{ $match->is_home ? 'bg-brand-500/60' : 'bg-black/30' }} text-white text-[6px] md:text-[7px] font-black rounded uppercase tracking-widest border border-white/10 shadow-inner">
                                    {{ __('matches.is_home') }}
                                </span>
                            </div>

                            {{-- Skóre --}}
                            <div class="flex flex-col items-center group/score shrink-0 z-10 mx-2 md:mx-4">
                                <div class="relative px-4 py-2 md:px-6 md:py-4 bg-white/15 rounded-xl md:rounded-[1.5rem] shadow-xl border border-white/25 flex items-center gap-2 md:gap-4 transition-all duration-500 group-hover:bg-white/20 backdrop-blur-xl">
                                    <div class="relative text-2xl md:text-5xl font-black tabular-nums tracking-tighter text-white drop-shadow-lg leading-none">
                                        {{ $match->score_home ?? 0 }}
                                    </div>
                                    <div class="relative text-lg md:text-2xl font-black text-white/40 select-none animate-pulse">:</div>
                                    <div class="relative text-2xl md:text-5xl font-black tabular-nums tracking-tighter text-white drop-shadow-lg leading-none">
                                        {{ $match->score_away ?? 0 }}
                                    </div>
                                </div>
                                <div class="mt-1.5">
                                    <div class="px-2 py-0.5 bg-black/40 rounded-full backdrop-blur-md border border-white/10 shadow-lg">
                                        <span class="text-[6px] md:text-[7px] font-black uppercase tracking-[0.2em] text-white/60 whitespace-nowrap">{{ $hasScore ? 'Konečný výsledek' : 'Zatím neodehráno' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tým 2 --}}
                            <div class="flex-1 flex flex-col items-center md:items-start text-center md:text-left group/team w-full min-w-0">
                                <div class="w-12 h-12 md:w-16 md:h-16 mb-2 rounded-xl bg-white shadow-lg flex items-center justify-center transition-all duration-500 group-hover/team:scale-105 overflow-hidden p-1 border border-white/20 relative shrink-0">
                                    <div class="absolute inset-0 bg-gray-50/50"></div>
                                    @php
                                        $ourLogo = isset($branding['logo_path']) ? (str_starts_with($branding['logo_path'], 'branding/') ? asset('uploads/'.$branding['logo_path']) : asset($branding['logo_path'])) : asset('assets/img/loga/logo_kbelsti_sokoli_velke.png');
                                    @endphp
                                    @if(!$match->is_home)
                                        <img src="{{ $ourLogo }}" class="relative max-w-full max-h-full object-contain" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full items-center justify-center text-gray-300">
                                            <i class="fa-light fa-shield-halved text-xl"></i>
                                        </div>
                                    @elseif($match->is_home && $match->opponent?->logo)
                                        <img src="{{ asset($match->opponent->logo) }}" class="relative max-w-full max-h-full object-contain" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full items-center justify-center text-gray-300">
                                            <i class="fa-light fa-shield text-xl"></i>
                                        </div>
                                    @else
                                        <i class="fa-light fa-shield text-xl md:text-2xl text-gray-300 relative"></i>
                                    @endif
                                </div>
                                <h2 class="text-sm md:text-lg font-black text-white mb-0.5 tracking-tight group-hover/team:text-white transition-colors leading-tight break-words w-full">
                                    {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                                </h2>
                                <span class="px-1.5 py-0.5 {{ !$match->is_home ? 'bg-brand-500/60' : 'bg-black/30' }} text-white text-[6px] md:text-[7px] font-black rounded uppercase tracking-widest border border-white/10 shadow-inner">
                                    {{ __('matches.is_away') }}
                                </span>
                            </div>
                        </div>

                        {{-- Motivační sekce a Result Badge (vše kompaktněji) --}}
                        @if($hasScore || !empty($motivationalMessage))
                            <div class="w-full flex flex-col items-center gap-4 pt-2">
                                {{-- Result Text & Message --}}
                                <div class="flex flex-col items-center gap-2 w-full">
                                    <div class="flex items-center gap-3">
                                        @if($isVictory)
                                            <i class="fa-light fa-trophy text-2xl text-yellow-300 drop-shadow-[0_0_8px_rgba(253,224,71,0.5)]"></i>
                                        @elseif($isDraw)
                                            <i class="fa-light fa-equals text-2xl text-white/80"></i>
                                        @else
                                            <i class="fa-light fa-basketball text-2xl text-white/60"></i>
                                        @endif
                                        <h4 class="text-2xl md:text-4xl font-black uppercase tracking-tighter text-white drop-shadow-lg">{{ $resultText }}</h4>
                                    </div>

                                    @if($motivationalMessage)
                                        <p class="text-sm md:text-base font-bold text-white/90 leading-tight italic text-center max-w-lg px-4">
                                            "{{ $motivationalMessage }}"
                                        </p>
                                    @endif
                                </div>

                                {{-- Badges --}}
                                @if(!empty($badges))
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @foreach($badges as $badge)
                                            <span class="px-3 py-1.5 bg-white/10 text-white text-[8px] font-black rounded-lg uppercase tracking-widest backdrop-blur-md border border-white/15 shadow-lg flex items-center gap-2 hover:bg-white/20 transition-all">
                                                <i class="fa-light fa-award {{ $isVictory ? 'text-yellow-300' : 'text-brand-300' }}"></i>
                                                {{ $badge }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Spodní lišta s doplňkovými informacemi (velmi kompaktní) --}}
                    @if(!empty($match->metadata['referees']) || !empty($match->metadata['commissioner']) || !empty($match->metadata['attendance']))
                        <div class="pt-6 border-t border-white/10 w-full max-w-4xl">
                                <div class="flex flex-col md:flex-row items-center justify-center gap-6 md:gap-12">
                                @if(!empty($match->metadata['referees']))
                                    <div class="flex items-center gap-3 group/info">
                                        <i class="fa-light fa-whistle text-white/50 group-hover/info:text-white transition-colors"></i>
                                        <div class="flex flex-col leading-none">
                                            <span class="text-[7px] font-black text-white/40 uppercase tracking-widest mb-0.5">{{ __('matches.referees') }}</span>
                                            <span class="text-xs font-bold text-white/80 group-hover/info:text-white transition-colors">{{ $match->metadata['referees'] }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($match->metadata['commissioner']))
                                    <div class="flex items-center gap-3 group/info">
                                        <i class="fa-light fa-user-tie text-white/50 group-hover/info:text-white transition-colors"></i>
                                        <div class="flex flex-col leading-none">
                                            <span class="text-[7px] font-black text-white/40 uppercase tracking-widest mb-0.5">{{ __('matches.commissioner') }}</span>
                                            <span class="text-xs font-bold text-white/80 group-hover/info:text-white transition-colors">{{ $match->metadata['commissioner'] }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($match->metadata['attendance']))
                                    <div class="flex items-center gap-3 group/info">
                                        <i class="fa-light fa-users text-white/50 group-hover/info:text-white transition-colors"></i>
                                        <div class="flex flex-col leading-none">
                                            <span class="text-[7px] font-black text-white/40 uppercase tracking-widest mb-0.5">{{ __('matches.attendance') }}</span>
                                            <span class="text-xs font-bold text-white/80 group-hover/info:text-white transition-colors">{{ $match->metadata['attendance'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-8">
            {{-- Left Column: Pre-match / Analysis --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5 min-w-0 space-y-4">
                {{-- Section Header --}}
                <div class="relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100 group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/20 shrink-0">
                            <i class="fa-light fa-magnifying-glass-chart text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight leading-none mb-1 truncate">
                                {{ __('matches.pre_match_stats') }}
                            </h2>
                            <p class="text-[9px] font-black text-brand-500 uppercase tracking-widest truncate">{{ __('matches.pre_match_analysis_desc') ?? 'Hloubková analýza před zápasem' }}</p>
                        </div>
                    </div>
                </div>

                    {{-- Best Players (Lídři zápasu / Klíčoví hráči) --}}
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                        <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50">
                                <i class="fa-light fa-star text-brand-500 text-lg"></i>
                            </div>
                            {{ __('matches.best_players') }}
                        </h3>

                        @php
                            $bestPlayers = $match->metadata['best_players_external'] ?? $match->metadata['best_players'] ?? [];
                        @endphp

                        @if(!empty($bestPlayers))
                            <div class="space-y-6">
                                @foreach($bestPlayers as $category => $players)
                                    @if(is_array($players) && (isset($players['home']) || isset($players['away'])))
                                        @php
                                            $localizedCategory = __('matches.' . $category);
                                            if ($localizedCategory === 'matches.' . $category) {
                                                $localizedCategory = $players['label'] ?? $category;
                                            }
                                        @endphp
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-4">
                                                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-100 to-transparent"></div>
                                                <span class="text-[11px] font-black text-gray-400 uppercase tracking-[0.25em] px-4">{{ $localizedCategory }}</span>
                                                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-100 to-transparent"></div>
                                            </div>

                                            <div class="flex flex-col gap-2">
                                                @foreach(['home', 'away'] as $side)
                                                    @if(!empty($players[$side]))
                                                        @php
                                                            $isOur = ($side === 'home' && $match->is_home) || ($side === 'away' && ! $match->is_home);
                                                            $scoreHome = (int)($match->score_home ?? 0);
                                                            $scoreAway = (int)($match->score_away ?? 0);

                                                            $sideWinner = ($side === 'home' && $scoreHome > $scoreAway) || ($side === 'away' && $scoreAway > $scoreHome);
                                                            $sideLoser = ($side === 'home' && $scoreHome < $scoreAway) || ($side === 'away' && $scoreAway < $scoreHome);
                                                            $sideDraw = $hasScore && $scoreHome == $scoreAway;

                                                            if (!$hasScore) {
                                                                $cardBg = $isOur ? 'bg-brand-50/50 border-brand-100' : 'bg-gray-50 border-gray-100';
                                                                $badgeClass = $isOur ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-600';
                                                                $crownClass = $isOur ? 'bg-brand-500' : 'bg-gray-400';
                                                                $valColor = 'text-brand-600';
                                                            } elseif ($sideDraw) {
                                                                $cardBg = 'bg-slate-50/50 border-slate-100';
                                                                $badgeClass = 'bg-slate-500 text-white';
                                                                $crownClass = 'bg-slate-400';
                                                                $valColor = 'text-slate-600';
                                                            } elseif ($sideWinner) {
                                                                $cardBg = 'bg-emerald-50/50 border-emerald-100';
                                                                $badgeClass = 'bg-emerald-500 text-white';
                                                                $crownClass = 'bg-emerald-500';
                                                                $valColor = 'text-emerald-600';
                                                            } else {
                                                                $cardBg = 'bg-rose-50/50 border-rose-100';
                                                                $badgeClass = 'bg-rose-500 text-white';
                                                                $crownClass = 'bg-rose-400';
                                                                $valColor = 'text-rose-600';
                                                            }
                                                        @endphp
                                                        <div class="group relative flex items-center gap-5 p-2 rounded-[2rem] {{ $cardBg }} border-2 transition-all hover:shadow-2xl hover:bg-white hover:-translate-y-1 overflow-hidden">
                                                            @if($sideWinner || ($isOur && !$hasScore))
                                                                <div class="absolute top-0 right-0 w-24 h-24 {{ $sideWinner ? 'bg-emerald-500/5' : 'bg-brand-500/5' }} rounded-full -mr-8 -mt-8"></div>
                                                            @endif

                                                            <div class="relative flex-shrink-0">
                                                                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-white shadow-md group-hover:scale-110 transition-transform duration-500 border border-white">
                                                                    @if(!empty($players[$side]['photo_url']))
                                                                        <img src="{{ $players[$side]['photo_url'] }}" alt="{{ $players[$side]['name'] }}" class="w-full h-full object-cover">
                                                                    @else
                                                                        <div class="w-full h-full flex items-center justify-center text-gray-200">
                                                                            <i class="fa-light fa-user text-3xl"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full {{ $crownClass }} text-white flex items-center justify-center text-xs shadow-lg border-2 border-white">
                                                                    <i class="fa-solid fa-crown"></i>
                                                                </div>
                                                            </div>

                                                            <div class="flex-grow min-w-0 relative">
                                                                <span class="px-2 py-0.5 {{ $badgeClass }} text-[8px] font-black rounded uppercase tracking-widest mb-1.5 inline-block">
                                                                    {{ $isOur ? 'Kbelský Sokol' : 'Soupeř' }}
                                                                </span>
                                                                <span class="text-lg font-black text-gray-900 leading-tight block truncate group-hover:text-brand-600 transition-colors">{{ $players[$side]['name'] }}</span>
                                                            </div>

                                                            <div class="flex-shrink-0 text-right relative">
                                                                <div class="flex flex-col items-end">
                                                                    <span class="text-2xl font-black {{ $valColor }} tabular-nums leading-none mb-1">{{ $players[$side]['value'] ?? '' }}</span>
                                                                    <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">HODNOTA</span>
                                                                </div>
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
                            <div class="py-12 text-center bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-100">
                                <i class="fa-light fa-user-slash text-5xl text-gray-200 mb-4 block"></i>
                                <p class="text-sm font-bold text-gray-400 italic px-8">{{ __('matches.empty_best_players') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Team Comparison (Srovnání kádrů) --}}
                    @php
                        $teamComparison = $match->metadata['team_comparison'] ?? [];
                    @endphp

                    @if(!empty($teamComparison))
                        <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                            <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50">
                                    <i class="fa-light fa-scale-balanced text-brand-500 text-lg"></i>
                                </div>
                                {{ __('matches.team_comparison') }}
                            </h3>

                        <div class="space-y-4">
                                @foreach($teamComparison as $key => $data)
                                    <div class="bg-gray-50 rounded-[2rem] p-3 border-2 border-gray-100 transition-all hover:shadow-2xl hover:bg-white hover:border-brand-100 group overflow-hidden relative">
                                        <div class="absolute top-0 right-0 w-24 h-24 bg-brand-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>

                                        <div class="text-center relative mb-4">
                                            <span class="px-4 py-1.5 bg-white shadow-sm border border-gray-100 rounded-full text-[10px] font-black text-gray-400 uppercase tracking-[0.25em] group-hover:text-brand-500 transition-colors">
                                                {{ $data['label'] ?? $key }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between gap-6 relative">
                                            <div class="text-center flex-1">
                                                <span class="px-2 py-0.5 bg-brand-100 text-brand-700 text-[8px] font-black rounded uppercase tracking-widest block mb-3 mx-auto w-fit">
                                                    {{ $match->is_home ? 'Doma' : 'Soupeř' }}
                                                </span>
                                                <span class="text-4xl font-black text-brand-600 tabular-nums drop-shadow-sm">{{ $data['home'] }}</span>
                                            </div>

                                            <div class="flex flex-col items-center gap-2">
                                                <div class="h-8 w-px bg-gradient-to-b from-transparent via-gray-200 to-transparent"></div>
                                                <div class="w-3 h-3 rounded-full bg-brand-500 shadow-[0_0_10px_rgba(225,29,72,0.3)]"></div>
                                                <div class="h-8 w-px bg-gradient-to-t from-transparent via-gray-200 to-transparent"></div>
                                            </div>

                                            <div class="text-center flex-1">
                                                <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[8px] font-black rounded uppercase tracking-widest block mb-3 mx-auto w-fit">
                                                    {{ $match->is_home ? 'Soupeř' : 'Doma' }}
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
                        <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                            <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50">
                                    <i class="fa-light fa-history text-brand-500 text-lg"></i>
                                </div>
                                {{ __('matches.last_matches') }}
                            </h3>

                            <div class="space-y-6">
                                {{-- Home Team Last Matches --}}
                                @if(!empty($lastMatches['home']))
                                    <div class="space-y-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-3 h-3 rounded-full bg-brand-500 shadow-lg shadow-brand-500/30"></div>
                                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest">
                                                {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                                            </h4>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($lastMatches['home'] as $m)
                                                <div class="group flex items-center justify-between p-2 bg-gray-50 rounded-[2rem] border-2 border-gray-50 shadow-sm text-sm transition-all hover:bg-white hover:border-brand-200 hover:shadow-2xl hover:-translate-y-1">
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-black text-gray-400 mb-1.5 uppercase tracking-widest">{{ $m['date'] }}</span>
                                                        <span class="text-base font-bold text-gray-700 leading-tight group-hover:text-brand-600 transition-colors">{{ $m['team_home'] }} vs {{ $m['team_away'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-5">
                                                        <div class="bg-white px-4 py-2 rounded-2xl shadow-inner border border-gray-100">
                                                            <span class="text-xl font-black tabular-nums {{ (int)$m['score_home'] > (int)$m['score_away'] ? 'text-brand-600' : 'text-gray-900' }}">
                                                                {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                                            </span>
                                                        </div>
                                                        @if(!empty($m['external_id']))
                                                            <a href="https://cz.basketball/zapas/{{ $m['external_id'] }}" target="_blank" class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-300 hover:text-brand-500 hover:shadow-xl hover:border-brand-100 border border-gray-100 transition-all">
                                                                <i class="fa-light fa-chevron-right text-lg"></i>
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
                                    <div class="space-y-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-3 h-3 rounded-full bg-gray-400 shadow-lg shadow-gray-400/30"></div>
                                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest">
                                                {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                                            </h4>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($lastMatches['away'] as $m)
                                                <div class="group flex items-center justify-between p-2 bg-gray-50 rounded-[2rem] border-2 border-gray-50 shadow-sm text-sm transition-all hover:bg-white hover:border-brand-200 hover:shadow-2xl hover:-translate-y-1">
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-black text-gray-400 mb-1.5 uppercase tracking-widest">{{ $m['date'] }}</span>
                                                        <span class="text-base font-bold text-gray-700 leading-tight group-hover:text-brand-600 transition-colors">{{ $m['team_home'] }} vs {{ $m['team_away'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-5">
                                                        <div class="bg-white px-4 py-2 rounded-2xl shadow-inner border border-gray-100">
                                                            <span class="text-xl font-black tabular-nums {{ (int)$m['score_home'] > (int)$m['score_away'] ? 'text-brand-600' : 'text-gray-900' }}">
                                                                {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                                            </span>
                                                        </div>
                                                        @if(!empty($m['external_id']))
                                                            <a href="https://cz.basketball/zapas/{{ $m['external_id'] }}" target="_blank" class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-300 hover:text-brand-500 hover:shadow-xl hover:border-brand-100 border border-gray-100 transition-all">
                                                                <i class="fa-light fa-chevron-right text-lg"></i>
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
                <div class="lg:col-span-7 min-w-0 space-y-4">
                    @php
                        $statusColor = 'success';
                        $statusText = 'Výsledky a statistiky po zápase';

                        if ($hasScore) {
                            if ($isVictory) {
                                $statusColor = 'success';
                                $statusText = 'Vítězná jízda potvrzena statistikami';
                            } elseif ($isDraw) {
                                $statusColor = 'slate';
                                $statusText = 'Nerozhodný souboj v číslech';
                            } else {
                                $statusColor = 'danger';
                                $statusText = 'Analýza náročného utkání';
                            }
                        } else {
                            $statusColor = 'brand';
                            $statusText = 'Připraveni na ostrý start';
                        }
                    @endphp

                    {{-- Section Header --}}
                    <div class="relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100 group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-{{ $statusColor === 'danger' ? 'rose' : ($statusColor === 'success' ? 'emerald' : $statusColor) }}-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>
                        <div class="relative flex items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-{{ $statusColor === 'danger' ? 'rose' : ($statusColor === 'success' ? 'emerald' : $statusColor) }}-500 to-{{ $statusColor === 'danger' ? 'rose' : ($statusColor === 'success' ? 'emerald' : $statusColor) }}-600 flex items-center justify-center text-white shadow-lg shadow-{{ $statusColor === 'danger' ? 'rose' : ($statusColor === 'success' ? 'emerald' : $statusColor) }}-500/20 shrink-0">
                                <i class="fa-light fa-chart-simple text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight leading-none mb-1 truncate">
                                    {{ __('matches.match_stats') }}
                                </h2>
                                <p class="text-[9px] font-black text-{{ $statusColor === 'danger' ? 'rose' : ($statusColor === 'success' ? 'emerald' : $statusColor) }}-500 uppercase tracking-widest truncate">{{ $statusText }}</p>
                            </div>
                        </div>
                    </div>


                    {{-- Periods Detailed --}}
                    @if(!empty($match->metadata['periods_detailed']))
                        <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                            <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50">
                                    <i class="fa-light fa-list-ol text-brand-500 text-lg"></i>
                                </div>
                                {{ __('matches.periods') }}
                            </h3>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach($match->metadata['periods_detailed'] as $period)
                                    @php
                                        $qHome = (int)$period['home'];
                                        $qAway = (int)$period['away'];
                                        $ourQScore = $match->is_home ? $qHome : $qAway;
                                        $oppQScore = $match->is_home ? $qAway : $qHome;

                                        if ($ourQScore > $oppQScore) {
                                            $qBg = 'bg-emerald-50/50 border-emerald-100';
                                            $qText = 'text-emerald-600';
                                            $qDot = 'bg-emerald-500/10';
                                            $qBadge = 'bg-emerald-500 text-white';
                                        } elseif ($ourQScore < $oppQScore) {
                                            $qBg = 'bg-rose-50/50 border-rose-100';
                                            $qText = 'text-rose-600';
                                            $qDot = 'bg-rose-500/10';
                                            $qBadge = 'bg-rose-500 text-white';
                                        } else {
                                            $qBg = 'bg-slate-50/50 border-slate-100';
                                            $qText = 'text-slate-600';
                                            $qDot = 'bg-slate-500/10';
                                            $qBadge = 'bg-slate-500 text-white';
                                        }
                                    @endphp
                                    <div class="flex flex-col items-center justify-center p-2 rounded-[2rem] border-2 {{ $qBg }} transition-all hover:bg-white hover:border-brand-100 hover:shadow-2xl hover:-translate-y-1 group relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-16 h-16 {{ $qDot }} rounded-full -mr-8 -mt-8"></div>
                                        <span class="relative text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 group-hover:text-brand-500 transition-colors">{{ $loop->iteration }}. {{ __('matches.period') ?? 'čtvrtina' }}</span>
                                        <div class="relative flex items-center gap-3">
                                            <span class="text-4xl font-black {{ $match->is_home ? $qText : 'text-gray-900' }} tabular-nums">
                                                {{ $qHome }}
                                            </span>
                                            <span class="text-xl font-black text-gray-300">:</span>
                                            <span class="text-4xl font-black {{ !$match->is_home ? $qText : 'text-gray-900' }} tabular-nums">
                                                {{ $qAway }}
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

                        $hasExtended = false;
                        foreach($ourStatsRaw as $s) {
                            $v = is_object($s) ? $s->values : ($s['values'] ?? []);
                            if (!empty($v['rebounds']) || !empty($v['assists']) || !empty($v['steals']) || !empty($v['efficiency'])) {
                                $hasExtended = true; break;
                            }
                        }

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
                    <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-100"
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
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center border border-gray-100 shrink-0">
                                            <i class="fa-light fa-chart-user text-brand-500 text-lg"></i>
                                        </div>
                                        {{ __('matches.boxscore') }}
                                    </h3>

                                    {{-- Custom Tabs Trigger --}}
                                    <div class="flex p-1 bg-gray-200/50 rounded-xl shadow-inner border border-gray-200/50">
                                        <button
                                            @click="activeTab = 'ours'"
                                            :class="activeTab === 'ours' ? 'bg-white text-brand-600 shadow-md scale-[1.02]' : 'text-gray-500 hover:text-gray-700'"
                                            class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all flex items-center gap-2.5"
                                        >
                                            <i class="fa-light fa-shield-halved"></i>
                                            {{ $match->team->name }}
                                        </button>
                                        <button
                                            @click="activeTab = 'opponent'"
                                            :class="activeTab === 'opponent' ? 'bg-white text-brand-600 shadow-md scale-[1.02]' : 'text-gray-500 hover:text-gray-700'"
                                            class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all flex items-center gap-2.5"
                                        >
                                            <i class="fa-light fa-shield"></i>
                                            {{ $match->opponent?->name ?? __('matches.opponent') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Our Team Tab --}}
                            <div x-show="activeTab === 'ours'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse min-w-[800px]">
                                        <thead>
                                            <tr class="bg-gray-900 text-white sticky top-0 z-10">
                                                <th class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center border-r border-white/5">#</th>
                                                <th @click="sortBy('name')" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest cursor-pointer hover:bg-gray-800 transition-colors group">
                                                    <div class="flex items-center gap-2">
                                                        Hráč
                                                        <i :class="sortField === 'name' ? (sortDirection === 'asc' ? 'fa-sort-up text-brand-400' : 'fa-sort-down text-brand-400') : 'fa-sort text-gray-600'" class="fa-light"></i>
                                                    </div>
                                                </th>
                                                @php
                                                    $cols = [
                                                        'fg2_made' => '2B',
                                                        'fg3_made' => '3B',
                                                        'ft_made' => 'TH',
                                                        'fouls' => 'F-',
                                                        'pts' => 'Body',
                                                        'plus_minus' => '+/-',
                                                    ];
                                                    if($hasExtended) {
                                                        $cols = array_merge($cols, [
                                                            'minutes' => 'MIN',
                                                            'rebounds' => 'REB',
                                                            'assists' => 'AST',
                                                            'steals' => 'STL',
                                                            'turnovers' => 'TOV',
                                                            'blocks' => 'BLK',
                                                            'fouls_drawn' => 'F+',
                                                            'efficiency' => 'VAL'
                                                        ]);
                                                    }
                                                @endphp
                                                @foreach($cols as $key => $label)
                                                    <th @click="sortBy('{{ $key }}')" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-center cursor-pointer hover:bg-gray-800 transition-colors group">
                                                        <div class="flex flex-col items-center gap-1">
                                                            {{ $label }}
                                                            <i :class="sortField === '{{ $key }}' ? (sortDirection === 'asc' ? 'fa-sort-up text-brand-400' : 'fa-sort-down text-brand-400') : 'fa-sort text-gray-600'" class="fa-light text-[8px]"></i>
                                                        </div>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(stat, index) in getSortedStats(ourStats)" :key="stat.id">
                                                <tr :class="stat.is_special ? 'bg-gray-50 font-bold' : 'hover:bg-brand-50/30 transition-colors'">
                                                    <td class="px-4 py-1 text-center text-[10px] font-black text-gray-300 border-r border-gray-50">
                                                        <span x-show="!stat.is_special" x-text="(index + 1) + '.'"></span>
                                                        <i x-show="stat.is_special && !stat.is_team" class="fa-light fa-sigma text-brand-500"></i>
                                                        <i x-show="stat.is_team" class="fa-light fa-users-gear text-gray-400"></i>
                                                    </td>
                                                    <td class="px-4 py-1 whitespace-nowrap">
                                                        <div class="flex items-center gap-3">
                                                            <div x-show="!stat.is_special" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 shadow-inner" x-text="stat.number"></div>
                                                            <div class="flex flex-col leading-tight">
                                                                <span class="text-sm font-bold" :class="stat.is_special ? 'text-gray-900' : 'text-gray-700'" x-text="stat.name"></span>
                                                                <span x-show="stat.is_starter" class="text-[7px] font-black text-brand-500 uppercase tracking-widest mt-0.5">Základní pětka</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-1 text-center text-xs font-bold text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg2_made"></td>
                                                    <td class="px-3 py-1 text-center text-xs font-bold text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg3_made"></td>
                                                    <td class="px-3 py-1 text-center text-xs font-bold text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : (stat.ft_made + '/' + stat.ft_att)"></td>
                                                    <td class="px-3 py-1 text-center text-sm font-black text-rose-500 tabular-nums" x-text="stat.fouls"></td>
                                                    <td class="px-3 py-1 text-center">
                                                        <span class="text-base font-black text-gray-900 tabular-nums px-2 py-1 bg-gray-50 rounded-lg shadow-sm" x-text="stat.is_team ? '-' : stat.pts"></span>
                                                    </td>
                                                    <td class="px-3 py-1 text-center text-sm font-bold tabular-nums" :class="stat.plus_minus > 0 ? 'text-emerald-600' : (stat.plus_minus < 0 ? 'text-rose-600' : 'text-gray-400')" x-text="stat.is_team ? '-' : (stat.plus_minus > 0 ? '+' + stat.plus_minus : stat.plus_minus)"></td>

                                                    @if($hasExtended)
                                                        <td class="px-3 py-1 text-center text-xs font-medium text-gray-400 tabular-nums" x-text="stat.is_team ? '-' : stat.minutes"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.rebounds"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.assists"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.steals"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-rose-400 tabular-nums" x-text="stat.is_team ? '-' : stat.turnovers"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.blocks"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-emerald-600 tabular-nums" x-text="stat.is_team ? '-' : stat.fouls_drawn"></td>
                                                        <td class="px-3 py-1 text-center">
                                                            <template x-if="!stat.is_team">
                                                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black tabular-nums shadow-sm border border-white"
                                                                    :class="stat.efficiency >= 15 ? 'bg-emerald-500 text-white shadow-emerald-500/20' : (stat.efficiency < 0 ? 'bg-rose-500 text-white shadow-rose-500/20' : 'bg-white text-gray-700')"
                                                                    x-text="stat.efficiency">
                                                                </span>
                                                            </template>
                                                            <template x-if="stat.is_team">
                                                                <span class="text-gray-300">-</span>
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
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse min-w-[800px]">
                                        <thead>
                                            <tr class="bg-gray-900 text-white sticky top-0 z-10">
                                                <th class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center border-r border-white/5">#</th>
                                                <th @click="sortBy('name')" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest cursor-pointer hover:bg-gray-800 transition-colors group">
                                                    <div class="flex items-center gap-2">
                                                        {{ $match->opponent?->name ?? 'Soupeř' }}
                                                        <i :class="sortField === 'name' ? (sortDirection === 'asc' ? 'fa-sort-up text-brand-400' : 'fa-sort-down text-brand-400') : 'fa-sort text-gray-600'" class="fa-light"></i>
                                                    </div>
                                                </th>
                                                @foreach($cols as $key => $label)
                                                    <th @click="sortBy('{{ $key }}')" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-center cursor-pointer hover:bg-gray-800 transition-colors group">
                                                        <div class="flex flex-col items-center gap-1">
                                                            {{ $label }}
                                                            <i :class="sortField === '{{ $key }}' ? (sortDirection === 'asc' ? 'fa-sort-up text-brand-400' : 'fa-sort-down text-brand-400') : 'fa-sort text-gray-600'" class="fa-light text-[8px]"></i>
                                                        </div>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(stat, index) in getSortedStats(opponentStats)" :key="stat.id">
                                                <tr :class="stat.is_special ? 'bg-gray-50 font-bold' : 'hover:bg-brand-50/30 transition-colors'">
                                                    <td class="px-4 py-1 text-center text-[10px] font-black text-gray-300 border-r border-gray-50">
                                                        <span x-show="!stat.is_special" x-text="(index + 1) + '.'"></span>
                                                        <i x-show="stat.is_special && !stat.is_team" class="fa-light fa-sigma text-brand-500"></i>
                                                        <i x-show="stat.is_team" class="fa-light fa-users-gear text-gray-400"></i>
                                                    </td>
                                                    <td class="px-4 py-1 whitespace-nowrap">
                                                        <div class="flex items-center gap-3">
                                                            <div x-show="!stat.is_special" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 shadow-inner" x-text="stat.number"></div>
                                                            <div class="flex flex-col leading-tight">
                                                                <span class="text-sm font-bold" :class="stat.is_special ? 'text-gray-900' : 'text-gray-700'" x-text="stat.name"></span>
                                                                <span x-show="stat.is_starter" class="text-[7px] font-black text-brand-500 uppercase tracking-widest mt-0.5">Základní pětka</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-1 text-center text-xs font-bold text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg2_made"></td>
                                                    <td class="px-3 py-1 text-center text-xs font-bold text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : stat.fg3_made"></td>
                                                    <td class="px-3 py-1 text-center text-xs font-bold text-gray-500 tabular-nums" x-text="stat.is_team ? '-' : (stat.ft_made + '/' + stat.ft_att)"></td>
                                                    <td class="px-3 py-1 text-center text-sm font-black text-rose-500 tabular-nums" x-text="stat.fouls"></td>
                                                    <td class="px-3 py-1 text-center">
                                                        <span class="text-base font-black text-gray-900 tabular-nums px-2 py-1 bg-gray-50 rounded-lg shadow-sm" x-text="stat.is_team ? '-' : stat.pts"></span>
                                                    </td>
                                                    <td class="px-3 py-1 text-center text-sm font-bold tabular-nums" :class="stat.plus_minus > 0 ? 'text-emerald-600' : (stat.plus_minus < 0 ? 'text-rose-600' : 'text-gray-400')" x-text="stat.is_team ? '-' : (stat.plus_minus > 0 ? '+' + stat.plus_minus : stat.plus_minus)"></td>

                                                    @if($hasExtended)
                                                        <td class="px-3 py-1 text-center text-xs font-medium text-gray-400 tabular-nums" x-text="stat.is_team ? '-' : stat.minutes"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.rebounds"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.assists"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.steals"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-rose-400 tabular-nums" x-text="stat.is_team ? '-' : stat.turnovers"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-gray-600 tabular-nums" x-text="stat.is_team ? '-' : stat.blocks"></td>
                                                        <td class="px-3 py-1 text-center text-xs font-bold text-emerald-600 tabular-nums" x-text="stat.is_team ? '-' : stat.fouls_drawn"></td>
                                                        <td class="px-3 py-1 text-center">
                                                            <template x-if="!stat.is_team">
                                                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black tabular-nums shadow-sm border border-white"
                                                                    :class="stat.efficiency >= 15 ? 'bg-emerald-500 text-white shadow-emerald-500/20' : (stat.efficiency < 0 ? 'bg-rose-500 text-white shadow-rose-500/20' : 'bg-white text-gray-700')"
                                                                    x-text="stat.efficiency">
                                                                </span>
                                                            </template>
                                                            <template x-if="stat.is_team">
                                                                <span class="text-gray-300">-</span>
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

                </div>
            </div>
        </div>
    </div>
@endsection
