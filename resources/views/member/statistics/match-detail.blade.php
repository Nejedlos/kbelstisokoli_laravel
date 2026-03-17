@extends('layouts.member', [
    'title' => $title ?? __('matches.match_detail'),
])

@section('content')
    <div class="space-y-4 pb-12">
        @php
            $isVictory = $match->is_win;
            $isDraw = $match->is_draw;
            $hasScore = $match->has_score;

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

            // Logo logic
            $ourLogoPath = $branding['logo_path'] ?? 'assets/img/loga/logo_kbelsti_sokoli_velke.png';
            $ourLogo = web_asset($ourLogoPath);
            $opponentLogo = $match->opponent?->logo ? web_asset($match->opponent->logo) : null;

            $logoHome = $match->is_home ? $ourLogo : $opponentLogo;
            $logoAway = $match->is_home ? $opponentLogo : $ourLogo;

            $fallbackHome = $match->is_home ? 'fa-shield-halved' : 'fa-shield';
            $fallbackAway = $match->is_home ? 'fa-shield' : 'fa-shield-halved';
        @endphp

        <div class="{{ $bgClass }} rounded-[3rem] shadow-[0_30px_60px_-12px_rgba(0,0,0,0.3)] {{ $glowClass }} text-white relative overflow-hidden group transition-all duration-700 border border-white/10">
            {{-- Pokročilé dekorativní prvky na pozadí --}}
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-white/10 rounded-full blur-[100px] transition-all duration-1000 group-hover:bg-white/15 group-hover:scale-125"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-black/20 rounded-full blur-[80px] transition-all duration-1000 group-hover:scale-110"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-gradient-to-br from-white/5 via-transparent to-black/5 pointer-events-none"></div>

            {{-- Velké logo na pozadí pro vyplnění prostoru --}}
            <div class="absolute right-0 bottom-0 opacity-[0.03] pointer-events-none translate-x-1/4 translate-y-1/4 group-hover:scale-110 transition-transform duration-1000">
                <img src="{{ $ourLogo }}" class="w-[500px] h-[500px] object-contain grayscale invert" alt="">
            </div>

            <div class="relative p-6 md:p-12">
                <div class="flex flex-col items-center space-y-10 md:space-y-12">

                    {{-- Horní informační lišta - Plovoucí pilulky (výraznější) --}}
                    <div class="w-full flex flex-col sm:flex-row flex-wrap justify-center gap-2 md:gap-3 text-[10px] md:text-xs font-black uppercase tracking-[0.15em] md:tracking-[0.25em]">
                        <div class="w-full sm:w-auto flex items-center justify-center gap-2.5 px-5 py-3 sm:py-2.5 bg-white/15 border border-white/30 rounded-2xl sm:rounded-full shadow-2xl backdrop-blur-xl hover:bg-white/25 transition-all cursor-default group/pill">
                            <i class="fa-light fa-calendar text-white group-hover/pill:scale-110 transition-transform"></i>
                            <span class="text-white">{{ $match->scheduled_at?->format('d. m. Y H:i') }}</span>
                        </div>
                        <div class="w-full sm:w-auto flex items-center justify-center gap-2.5 px-5 py-3 sm:py-2.5 bg-white/15 border border-white/30 rounded-2xl sm:rounded-full shadow-2xl backdrop-blur-xl hover:bg-white/25 transition-all cursor-default group/pill">
                            <i class="fa-light fa-trophy text-white group-hover/pill:scale-110 transition-transform"></i>
                            <span class="text-white">
                                {{ $match->match_type ? (__('matches.type_' . strtolower($match->match_type)) !== 'matches.type_' . strtolower($match->match_type) ? __('matches.type_' . strtolower($match->match_type)) : $match->match_type) : __('matches.type_mi') }}
                            </span>
                        </div>
                        @if($match->metadata['venue'] ?? $match->location)
                            <div class="w-full sm:w-auto flex items-center justify-center gap-2.5 px-5 py-3 sm:py-2.5 bg-white/15 border border-white/30 rounded-2xl sm:rounded-full shadow-2xl backdrop-blur-xl hover:bg-white/25 transition-all cursor-default group/pill">
                                <i class="fa-light fa-location-dot text-white group-hover/pill:scale-110 transition-transform"></i>
                                <span class="text-white">{{ $match->metadata['venue'] ?? $match->location }}</span>
                            </div>
                        @endif
                        @if(!empty($match->metadata['external_id']))
                            <a href="https://cz.basketball/zapas/{{ $match->metadata['external_id'] }}" target="_blank"
                               class="w-full sm:w-auto flex items-center justify-center gap-2.5 px-6 py-3 sm:py-2.5 bg-white text-gray-950 font-black rounded-2xl sm:rounded-full shadow-2xl hover:bg-white hover:-translate-y-1 hover:scale-[1.02] sm:hover:scale-105 active:scale-95 transition-all group/ext">
                                <i class="fa-light fa-basketball text-brand-600 animate-pulse"></i>
                                <span>{{ __('matches.external_detail') }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Hlavní Scoreboard (Dominantní typografie) --}}
                    <div class="w-full max-w-6xl flex flex-col items-center gap-8">
                        <div class="w-full flex flex-col lg:flex-row items-center justify-between gap-8 lg:gap-12">

                            {{-- Tým 1 --}}
                            <div class="flex-1 flex flex-col items-center lg:items-end text-center lg:text-right group/team w-full min-w-0">
                                <div class="w-16 h-16 md:w-24 md:h-24 mb-3 md:mb-4 rounded-2xl md:rounded-[1.5rem] bg-white shadow-2xl flex items-center justify-center transition-all duration-700 group-hover/team:rotate-3 group-hover/team:scale-110 overflow-hidden p-2 md:p-3 border-4 border-white/20 relative shrink-0">
                                    @if($logoHome)
                                        <img src="{{ $logoHome }}" class="relative max-w-full max-h-full object-contain" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full items-center justify-center text-gray-300">
                                            <i class="fa-light {{ $fallbackHome }} text-2xl md:text-3xl"></i>
                                        </div>
                                    @else
                                        <i class="fa-light {{ $fallbackHome }} text-3xl md:text-5xl text-gray-200 relative"></i>
                                    @endif
                                </div>
                                <h2 class="text-xl md:text-3xl font-black text-white mb-1 md:mb-2 tracking-tighter group-hover/team:text-white transition-colors leading-none break-words w-full drop-shadow-2xl">
                                    {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                                </h2>
                                <span class="px-2 py-0.5 md:px-3 md:py-1 bg-black/40 text-white text-[8px] md:text-xs font-black rounded-lg uppercase tracking-[0.2em] border border-white/10 shadow-2xl backdrop-blur-md">
                                    {{ __('matches.is_home') }}
                                </span>
                            </div>

                            {{-- Skóre (Centrální prvek) --}}
                            <div class="flex flex-col items-center group/score shrink-0 z-10 mx-2 md:mx-4">
                                <div class="relative px-6 py-4 md:px-10 md:py-6 bg-white/10 rounded-[2rem] md:rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white/20 flex items-center gap-4 md:gap-8 transition-all duration-700 group-hover:bg-white/20 backdrop-blur-2xl group-hover:scale-105">
                                    <div class="relative text-5xl md:text-7xl font-black tabular-nums tracking-tighter text-white drop-shadow-[0_10px_10px_rgba(0,0,0,0.5)] leading-none">
                                        {{ $match->score_home ?? 0 }}
                                    </div>
                                    <div class="relative text-2xl md:text-4xl font-black text-white/40 select-none animate-pulse">:</div>
                                    <div class="relative text-5xl md:text-7xl font-black tabular-nums tracking-tighter text-white drop-shadow-[0_10px_10px_rgba(0,0,0,0.5)] leading-none">
                                        {{ $match->score_away ?? 0 }}
                                    </div>
                                </div>
                                <div class="mt-3 md:mt-4">
                                    <div class="px-3 py-1 md:px-4 md:py-1.5 bg-black/50 rounded-full backdrop-blur-xl border border-white/10 shadow-2xl">
                                        <span class="text-[8px] md:text-xs font-black uppercase tracking-[0.2em] md:tracking-[0.3em] text-white/80 whitespace-nowrap">
                                            {{ $hasScore ? __('matches.result_status.final') : __('matches.result_status.planned') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tým 2 --}}
                            <div class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left group/team w-full min-w-0">
                                <div class="w-16 h-16 md:w-24 md:h-24 mb-3 md:mb-4 rounded-2xl md:rounded-[1.5rem] bg-white shadow-2xl flex items-center justify-center transition-all duration-700 group-hover/team:-rotate-3 group-hover/team:scale-110 overflow-hidden p-2 md:p-3 border-4 border-white/20 relative shrink-0">
                                    @if($logoAway)
                                        <img src="{{ $logoAway }}" class="relative max-w-full max-h-full object-contain" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full items-center justify-center text-gray-300">
                                            <i class="fa-light {{ $fallbackAway }} text-2xl md:text-3xl"></i>
                                        </div>
                                    @else
                                        <i class="fa-light {{ $fallbackAway }} text-3xl md:text-5xl text-gray-200 relative"></i>
                                    @endif
                                </div>
                                <h2 class="text-xl md:text-3xl font-black text-white mb-1 md:mb-2 tracking-tighter group-hover/team:text-white transition-colors leading-none break-words w-full drop-shadow-2xl">
                                    {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                                </h2>
                                <span class="px-2 py-0.5 md:px-3 md:py-1 {{ !$match->is_home ? 'bg-brand-500' : 'bg-black/40' }} text-white text-[8px] md:text-xs font-black rounded-lg uppercase tracking-[0.2em] border border-white/10 shadow-2xl backdrop-blur-md">
                                    {{ __('matches.is_away') }}
                                </span>
                            </div>
                        </div>

                        {{-- Motivační sekce a Result Badge (Hero styl) --}}
                        @if($hasScore || !empty($motivationalMessage))
                            <div class="w-full flex flex-col items-center gap-6 pt-4">
                                {{-- Result Text & Message --}}
                                <div class="flex flex-col items-center gap-4 w-full">
                                    <div class="flex items-center gap-3 md:gap-4">
                                        @if($isVictory)
                                            <div class="w-10 h-10 md:w-14 md:h-14 bg-yellow-400/20 rounded-xl md:rounded-2xl flex items-center justify-center backdrop-blur-md border border-yellow-400/30 shadow-2xl shadow-yellow-400/20 animate-bounce-subtle">
                                                <i class="fa-light fa-trophy text-2xl md:text-3xl text-yellow-300 drop-shadow-[0_0_15px_rgba(253,224,71,0.8)]"></i>
                                            </div>
                                        @elseif($isDraw)
                                            <div class="w-10 h-10 md:w-14 md:h-14 bg-white/10 rounded-xl md:rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/20">
                                                <i class="fa-light fa-equals text-2xl md:text-3xl text-white"></i>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 md:w-14 md:h-14 bg-white/10 rounded-xl md:rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/20">
                                                <i class="fa-light fa-basketball text-2xl md:text-3xl text-white/80"></i>
                                            </div>
                                        @endif
                                        <h4 class="text-3xl md:text-5xl font-black uppercase tracking-tighter text-white drop-shadow-[0_5px_15px_rgba(0,0,0,0.3)]">{{ $resultText }}</h4>
                                    </div>

                                    @if($motivationalMessage)
                                        <p class="text-lg md:text-2xl font-bold text-white leading-tight italic text-center max-w-3xl px-6 drop-shadow-lg">
                                            "{{ $motivationalMessage }}"
                                        </p>
                                    @endif
                                </div>

                                {{-- Badges (Větší a výraznější) --}}
                                @if(!empty($badges))
                                    <div class="w-full flex flex-wrap justify-center gap-2 md:gap-3">
                                        @foreach($badges as $badge)
                                            <span class="w-full sm:w-auto px-5 md:px-6 py-2.5 md:py-3 bg-white/15 text-white text-[10px] md:text-xs font-black rounded-xl md:rounded-2xl uppercase tracking-[0.15em] md:tracking-[0.2em] backdrop-blur-xl border border-white/30 shadow-2xl flex items-center justify-center gap-3 hover:bg-white/25 hover:-translate-y-1 transition-all cursor-default text-center">
                                                <i class="fa-light fa-award text-lg {{ $isVictory ? 'text-yellow-300' : 'text-brand-300' }}"></i>
                                                {{ $badge }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Spodní lišta s doplňkovými informacemi (Jasná hierarchie) --}}
                    @if(!empty($match->metadata['referees']) || !empty($match->metadata['commissioner']) || !empty($match->metadata['attendance']))
                        <div class="pt-8 md:pt-10 border-t border-white/20 w-full max-w-5xl">
                                <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-20">
                                @if(!empty($match->metadata['referees']))
                                    <div class="w-full md:w-auto flex items-center justify-center md:justify-start gap-4 md:gap-5 group/info">
                                        <div class="w-12 h-12 md:w-13 md:h-13 rounded-2xl bg-white/10 flex items-center justify-center text-white border border-white/20 group-hover/info:bg-white/20 transition-all shadow-2xl shrink-0">
                                            <i class="fa-light fa-whistle text-xl md:text-2xl"></i>
                                        </div>
                                        <div class="flex flex-col leading-tight">
                                            <span class="text-[8px] md:text-[9px] font-black text-white/50 uppercase tracking-[0.3em] mb-1 md:mb-1.5">{{ __('matches.referees') }}</span>
                                            <span class="text-base md:text-lg font-black text-white group-hover/info:text-brand-200 transition-colors">{{ $match->metadata['referees'] }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($match->metadata['commissioner']))
                                    <div class="w-full md:w-auto flex items-center justify-center md:justify-start gap-4 md:gap-5 group/info">
                                        <div class="w-12 h-12 md:w-13 md:h-13 rounded-2xl bg-white/10 flex items-center justify-center text-white border border-white/20 group-hover/info:bg-white/20 transition-all shadow-2xl shrink-0">
                                            <i class="fa-light fa-user-tie text-xl md:text-2xl"></i>
                                        </div>
                                        <div class="flex flex-col leading-tight">
                                            <span class="text-[8px] md:text-[9px] font-black text-white/50 uppercase tracking-[0.3em] mb-1 md:mb-1.5">{{ __('matches.commissioner') }}</span>
                                            <span class="text-base md:text-lg font-black text-white group-hover/info:text-brand-200 transition-colors">{{ $match->metadata['commissioner'] }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($match->metadata['attendance']))
                                    <div class="w-full md:w-auto flex items-center justify-center md:justify-start gap-4 md:gap-5 group/info">
                                        <div class="w-12 h-12 md:w-13 md:h-13 rounded-2xl bg-white/10 flex items-center justify-center text-white border border-white/20 group-hover/info:bg-white/20 transition-all shadow-2xl shrink-0">
                                            <i class="fa-light fa-users text-xl md:text-2xl"></i>
                                        </div>
                                        <div class="flex flex-col leading-tight">
                                            <span class="text-[8px] md:text-[9px] font-black text-white/50 uppercase tracking-[0.3em] mb-1 md:mb-1.5">{{ __('matches.attendance') }}</span>
                                            <span class="text-base md:text-lg font-black text-white group-hover/info:text-brand-200 transition-colors">{{ $match->metadata['attendance'] }}</span>
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
                {{-- Predikce --}}
                @if($prediction)
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>

                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50 shadow-sm">
                                    <i class="fa-light fa-crystal-ball text-brand-500 text-xl"></i>
                                </div>
                                {{ $hasScore ? (__('matches.prediction.title_past') ?? 'Předzápasová predikce') : (__('matches.prediction.title') ?? 'Předzápasová predikce') }}
                            </div>

                            @php
                                $confColor = match($prediction->confidence) {
                                    'high' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'medium' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $confColor }}">
                                {{ __('matches.prediction.confidence_' . $prediction->confidence) ?? $prediction->confidence }}
                            </span>
                        </h3>

                        <div class="space-y-8">
                            {{-- Win Probability --}}
                            <div class="text-center">
                                <div class="text-5xl font-black text-brand-600 mb-2 drop-shadow-sm tabular-nums">
                                    {{ round($prediction->probability_win * 100) }}%
                                </div>
                                <div class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
                                    {{ __('matches.prediction.win_chance') ?? 'Šance na výhru' }}
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="relative pt-1">
                                <div class="overflow-hidden h-4 text-xs flex rounded-full bg-gray-100 shadow-inner p-1">
                                    <div style="width:{{ $prediction->probability_win * 100 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-brand-500 to-brand-600 rounded-full transition-all duration-1000"></div>
                                </div>
                                <div class="flex justify-between text-[10px] font-black text-gray-400 uppercase tracking-widest mt-3">
                                    <span>{{ $match->team->name }}</span>
                                    <span>{{ $match->opponent?->name }}</span>
                                </div>
                            </div>

                            {{-- Why we think so --}}
                            <div class="space-y-4 pt-4 border-t border-gray-50">
                                <h4 class="text-[11px] font-black text-gray-950 uppercase tracking-[0.2em]">
                                    {{ __('matches.prediction.why_title') ?? 'Proč si to myslíme' }}
                                </h4>
                                <ul class="space-y-3">
                                    @foreach($prediction->explanation_points as $point)
                                        <li class="flex items-start gap-3 text-sm text-gray-600 leading-relaxed">
                                            <i class="fa-light fa-check-circle text-brand-500 mt-1 shrink-0"></i>
                                            <span>{{ $point }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Methodology --}}
                            <div x-data="{ open: false }" class="pt-2">
                                <button @click="open = !open" class="text-[10px] font-black text-brand-500 uppercase tracking-widest flex items-center gap-2 hover:text-brand-600 transition-colors">
                                    <i class="fa-light fa-circle-info"></i>
                                    {{ __('matches.prediction.methodology') ?? 'Metodika' }}
                                    <i class="fa-light" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                                <div x-show="open" x-cloak class="mt-4 p-4 bg-gray-50 rounded-2xl text-[11px] text-gray-500 leading-relaxed space-y-2">
                                    <p>{{ __('matches.prediction.methodology_desc') ?? 'Predikce je založena na kombinaci Elo ratingu (dlouhodobá síla), aktuální formy (posledních 5 zápasů) a síly kádru. Výpočet se zpřesňuje s rostoucím množstvím dat.' }}</p>
                                    <p class="italic text-[10px]">{{ __('matches.prediction.disclaimer') ?? 'Jde o matematický model, nikoliv záruku výsledku. Basketbal je nevyzpytatelný!' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section Header --}}
                <div class="relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100 group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/20 shrink-0">
                            <i class="fa-light fa-magnifying-glass-chart text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight leading-none mb-1">
                                {{ __('matches.pre_match_stats') }}
                            </h2>
                            <p class="text-[9px] font-black text-brand-500 uppercase tracking-widest">{{ __('matches.pre_match_analysis_desc') ?? 'Hloubková analýza před zápasem' }}</p>
                        </div>
                    </div>
                </div>

                    {{-- Best Players (Lídři zápasu / Klíčoví hráči) --}}
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                        <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50 shadow-sm">
                                <i class="fa-light fa-star text-brand-500 text-xl"></i>
                            </div>
                            {{ __('matches.best_players') }}
                        </h3>

                        @php
                            $bestPlayers = $match->metadata['best_players_external'] ?? $match->metadata['best_players'] ?? [];
                        @endphp

                        @if(!empty($bestPlayers))
                            <div class="space-y-4">
                                @foreach($bestPlayers as $category => $players)
                                    @if(is_array($players) && (isset($players['home']) || isset($players['away'])))
                                        @php
                                            $localizedCategory = __('matches.' . $category);
                                            if ($localizedCategory === 'matches.' . $category) {
                                                $localizedCategory = $players['label'] ?? $category;
                                            }
                                        @endphp
                                        <div class="space-y-2">
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
                                                        <div class="group relative flex items-center gap-4 p-2 rounded-[2rem] {{ $cardBg }} border-2 transition-all hover:shadow-2xl hover:bg-white hover:-translate-y-1 overflow-hidden">
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
                                                                <span class="px-2 py-0.5 {{ $badgeClass }} text-[8px] font-black rounded uppercase tracking-widest mb-1.5 inline-block whitespace-nowrap">
                                                                    {{ $isOur ? 'Kbelský Sokol' : 'Soupeř' }}
                                                                </span>
                                                                <span class="text-sm md:text-base font-black text-gray-900 leading-tight block group-hover:text-brand-600 transition-colors break-words">{{ $players[$side]['name'] }}</span>
                                                            </div>

                                                            <div class="flex-shrink-0 text-right relative">
                                                                <div class="flex flex-col items-end">
                                                                    <span class="text-2xl font-black {{ $valColor }} tabular-nums leading-none mb-1">{{ $players[$side]['value'] ?? '' }}</span>
                                                                    <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">{{ __('matches.stats.value') }}</span>
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
                                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50 shadow-sm">
                                    <i class="fa-light fa-scale-balanced text-brand-500 text-xl"></i>
                                </div>
                                {{ __('matches.team_comparison') }}
                            </h3>

                        <div class="space-y-4">
                                @foreach($teamComparison as $key => $data)
                                    <div class="bg-gray-50 rounded-[2rem] p-2 border-2 border-gray-100 transition-all hover:shadow-2xl hover:bg-white hover:border-brand-100 group overflow-hidden relative">
                                        <div class="absolute top-0 right-0 w-24 h-24 bg-brand-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>

                                        <div class="text-center relative mb-4">
                                            <span class="inline-block px-4 py-1.5 bg-white shadow-sm border border-gray-100 rounded-full text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] md:tracking-[0.25em] group-hover:text-brand-500 transition-colors leading-tight">
                                                {{ $data['label'] ?? $key }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between gap-2 md:gap-6 relative">
                                            <div class="text-center flex-1 min-w-0">
                                                <span class="px-2 py-0.5 bg-brand-100 text-brand-700 text-[8px] font-black rounded uppercase tracking-widest block mb-2 mx-auto w-fit whitespace-nowrap">
                                                    {{ $match->is_home ? 'Doma' : 'Soupeř' }}
                                                </span>
                                                <span class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-brand-600 tabular-nums drop-shadow-sm block break-words">
                                                    {{ $data['home'] }}
                                                </span>
                                            </div>

                                            <div class="flex flex-col items-center gap-1 shrink-0 px-1">
                                                <div class="h-6 md:h-8 w-px bg-gradient-to-b from-transparent via-gray-200 to-transparent"></div>
                                                <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-brand-500 shadow-[0_0_10px_rgba(225,29,72,0.3)]"></div>
                                                <div class="h-6 md:h-8 w-px bg-gradient-to-t from-transparent via-gray-200 to-transparent"></div>
                                            </div>

                                            <div class="text-center flex-1 min-w-0">
                                                <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[8px] font-black rounded uppercase tracking-widest block mb-2 mx-auto w-fit whitespace-nowrap">
                                                    {{ $match->is_home ? 'Soupeř' : 'Doma' }}
                                                </span>
                                                <span class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tabular-nums drop-shadow-sm block break-words">
                                                    {{ $data['away'] }}
                                                </span>
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
                                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50 shadow-sm">
                                    <i class="fa-light fa-history text-brand-500 text-xl"></i>
                                </div>
                                {{ __('matches.last_matches') }}
                            </h3>

                            <div class="space-y-6">
                                {{-- Home Team Last Matches --}}
                                @if(!empty($lastMatches['home']))
                                    @php
                                        $homeTeamName = $match->is_home ? $match->team->name : ($match->opponent?->name ?? 'Soupeř');
                                    @endphp
                                    <div class="space-y-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-3 h-3 rounded-full bg-brand-500 shadow-lg shadow-brand-500/30"></div>
                                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest">
                                                {{ $homeTeamName }}
                                            </h4>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($lastMatches['home'] as $m)
                                                @php
                                                    $res = \App\Support\MatchResultHelper::for($m, $homeTeamName);
                                                @endphp
                                                <div class="group relative flex flex-col sm:flex-row sm:items-center justify-between p-3 sm:p-2.5 bg-gray-50 rounded-3xl sm:rounded-[2rem] border-2 border-gray-50 transition-all hover:bg-white hover:border-brand-200 hover:shadow-xl hover:-translate-y-0.5 gap-3 sm:gap-4 overflow-hidden">
                                                    {{-- Decorative background element --}}
                                                    <div class="absolute top-0 right-0 w-16 h-16 bg-brand-500/5 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-700"></div>

                                                    <div class="flex-1 min-w-0 relative z-10 text-center sm:text-left">
                                                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $m['date'] }}</span>
                                                        <h5 class="text-sm font-bold text-gray-700 group-hover:text-brand-600 transition-colors leading-tight break-words">
                                                            {{ $m['team_home'] }} <span class="text-brand-500/30 mx-1">vs</span> {{ $m['team_away'] }}
                                                        </h5>
                                                    </div>

                                                    <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 w-full sm:w-auto relative z-10">
                                                        <div class="bg-white px-4 py-2 sm:px-3 sm:py-1.5 rounded-2xl sm:rounded-xl shadow-inner border border-gray-100 shrink-0 flex-1 sm:flex-none text-center">
                                                            <span class="text-lg sm:text-base font-black tabular-nums tracking-tight {{ $res['textColor'] }}">
                                                                {{ $m['score_home'] }}<span class="mx-0.5 opacity-30">:</span>{{ $m['score_away'] }}
                                                            </span>
                                                        </div>
                                                        @if(!empty($m['external_id']))
                                                            <a href="https://cz.basketball/zapas/{{ $m['external_id'] }}" target="_blank" class="w-11 h-11 sm:w-9 sm:h-9 rounded-2xl sm:rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-300 hover:text-brand-500 hover:shadow-md hover:border-brand-100 border border-gray-100 transition-all shrink-0">
                                                                <i class="fa-light fa-chevron-right text-lg sm:text-base"></i>
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
                                    @php
                                        $awayTeamName = $match->is_home ? ($match->opponent?->name ?? 'Soupeř') : $match->team->name;
                                    @endphp
                                    <div class="space-y-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-3 h-3 rounded-full bg-gray-400 shadow-lg shadow-gray-400/30"></div>
                                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest">
                                                {{ $awayTeamName }}
                                            </h4>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($lastMatches['away'] as $m)
                                                @php
                                                    $res = \App\Support\MatchResultHelper::for($m, $awayTeamName);
                                                @endphp
                                                <div class="group relative flex flex-col sm:flex-row sm:items-center justify-between p-3 sm:p-2.5 bg-gray-50 rounded-3xl sm:rounded-[2rem] border-2 border-gray-50 transition-all hover:bg-white hover:border-brand-200 hover:shadow-xl hover:-translate-y-0.5 gap-3 sm:gap-4 overflow-hidden">
                                                    {{-- Decorative background element --}}
                                                    <div class="absolute top-0 right-0 w-16 h-16 bg-brand-500/5 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-700"></div>

                                                    <div class="flex-1 min-w-0 relative z-10 text-center sm:text-left">
                                                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $m['date'] }}</span>
                                                        <h5 class="text-sm font-bold text-gray-700 group-hover:text-brand-600 transition-colors leading-tight break-words">
                                                            {{ $m['team_home'] }} <span class="text-brand-500/30 mx-1">vs</span> {{ $m['team_away'] }}
                                                        </h5>
                                                    </div>

                                                    <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 w-full sm:w-auto relative z-10">
                                                        <div class="bg-white px-4 py-2 sm:px-3 sm:py-1.5 rounded-2xl sm:rounded-xl shadow-inner border border-gray-100 shrink-0 flex-1 sm:flex-none text-center">
                                                            <span class="text-lg sm:text-base font-black tabular-nums tracking-tight {{ $res['textColor'] }}">
                                                                {{ $m['score_home'] }}<span class="mx-0.5 opacity-30">:</span>{{ $m['score_away'] }}
                                                            </span>
                                                        </div>
                                                        @if(!empty($m['external_id']))
                                                            <a href="https://cz.basketball/zapas/{{ $m['external_id'] }}" target="_blank" class="w-11 h-11 sm:w-9 sm:h-9 rounded-2xl sm:rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-300 hover:text-brand-500 hover:shadow-md hover:border-brand-100 border border-gray-100 transition-all shrink-0">
                                                                <i class="fa-light fa-chevron-right text-lg sm:text-base"></i>
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
                        $statusGradient = 'from-emerald-500 to-emerald-600';
                        $statusShadow = 'shadow-emerald-500/20';
                        $statusPulse = 'bg-emerald-500/5';
                        $statusTextAccent = 'text-emerald-500';

                        if ($hasScore) {
                            if ($isVictory) {
                                $statusColor = 'success';
                                $statusText = 'Vítězná jízda potvrzena statistikami';
                                $statusGradient = 'from-emerald-500 to-emerald-600';
                                $statusShadow = 'shadow-emerald-500/20';
                                $statusPulse = 'bg-emerald-500/5';
                                $statusTextAccent = 'text-emerald-500';
                            } elseif ($isDraw) {
                                $statusColor = 'slate';
                                $statusText = 'Nerozhodný souboj v číslech';
                                $statusGradient = 'from-slate-500 to-slate-600';
                                $statusShadow = 'shadow-slate-500/20';
                                $statusPulse = 'bg-slate-500/5';
                                $statusTextAccent = 'text-slate-500';
                            } else {
                                $statusColor = 'danger';
                                $statusText = 'Analýza náročného utkání';
                                $statusGradient = 'from-rose-500 to-rose-600';
                                $statusShadow = 'shadow-rose-500/20';
                                $statusPulse = 'bg-rose-500/5';
                                $statusTextAccent = 'text-rose-500';
                            }
                        } else {
                            $statusColor = 'brand';
                            $statusText = 'Připraveni na ostrý start';
                            $statusGradient = 'from-brand-500 to-brand-600';
                            $statusShadow = 'shadow-brand-500/20';
                            $statusPulse = 'bg-brand-500/5';
                            $statusTextAccent = 'text-brand-500';
                        }
                    @endphp

                    {{-- Section Header --}}
                    <div class="relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-xl border border-gray-100 group">
                        <div class="absolute top-0 right-0 w-32 h-32 {{ $statusPulse }} rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>
                        <div class="relative flex items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $statusGradient }} flex items-center justify-center text-white shadow-lg {{ $statusShadow }} shrink-0">
                                <i class="fa-light fa-chart-simple text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight leading-none mb-1">
                                    {{ __('matches.match_stats') }}
                                </h2>
                                <p class="text-[9px] font-black {{ $statusTextAccent }} uppercase tracking-widest">{{ $statusText }}</p>
                            </div>
                        </div>
                    </div>


                    {{-- Periods Detailed --}}
                    @if(!empty($match->metadata['periods_detailed']))
                        <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                            <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center shrink-0 border border-brand-100/50 shadow-sm">
                                    <i class="fa-light fa-list-ol text-brand-500 text-xl"></i>
                                </div>
                                {{ __('matches.periods') }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
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
                                    <div class="flex flex-col items-center justify-center p-4 sm:p-3 md:p-4 rounded-[2rem] border-2 {{ $qBg }} transition-all hover:bg-white hover:border-brand-100 hover:shadow-2xl hover:-translate-y-1 group relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-16 h-16 {{ $qDot }} rounded-full -mr-8 -mt-8"></div>
                                        <span class="relative inline-block text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 group-hover:text-brand-500 transition-colors whitespace-nowrap">{{ $loop->iteration }}. {{ __('matches.period') ?? 'čtvrtina' }}</span>
                                        <div class="relative flex items-center gap-2 md:gap-3">
                                            <span class="text-3xl md:text-4xl font-black {{ $match->is_home ? $qText : 'text-gray-900' }} tabular-nums">
                                                {{ $qHome }}
                                            </span>
                                            <span class="text-lg md:text-xl font-black text-gray-300">:</span>
                                            <span class="text-3xl md:text-4xl font-black {{ !$match->is_home ? $qText : 'text-gray-900' }} tabular-nums">
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
                                <div x-show="ourStats.length > 0" class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse min-w-[800px]">
                                        <thead>
                                            <tr class="bg-gray-900 text-white sticky top-0 z-10">
                                                <th class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center border-r border-white/5 sticky left-0 bg-gray-900 z-20">#</th>
                                                <th @click="sortBy('name')" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest cursor-pointer hover:bg-gray-800 transition-colors group sticky left-[41px] bg-gray-900 z-20 border-r border-white/5">
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
                                                    <td class="px-4 py-1 text-center text-[10px] font-black text-gray-300 border-r border-gray-50 sticky left-0 bg-inherit z-10">
                                                        <span x-show="!stat.is_special" x-text="(index + 1) + '.'"></span>
                                                        <i x-show="stat.is_special && !stat.is_team" class="fa-light fa-sigma text-brand-500"></i>
                                                        <i x-show="stat.is_team" class="fa-light fa-users-gear text-gray-400"></i>
                                                    </td>
                                                    <td class="px-4 py-1 whitespace-nowrap sticky left-[41px] bg-inherit z-10 border-r border-gray-50 shadow-[4px_0_10px_-4px_rgba(0,0,0,0.05)]">
                                                        <div class="flex items-center gap-3">
                                                            <div x-show="!stat.is_special" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 shadow-inner shrink-0" x-text="stat.number"></div>
                                                            <div class="flex flex-col leading-tight min-w-0">
                                                                <span class="text-sm font-bold truncate" :class="stat.is_special ? 'text-gray-900' : 'text-gray-700'" x-text="stat.name"></span>
                                                                <span x-show="stat.is_starter" class="text-[7px] font-black text-brand-500 uppercase tracking-widest mt-0.5 whitespace-nowrap">Základní pětka</span>
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
                                <div x-show="ourStats.length === 0" class="py-12 text-center bg-gray-50 border-t border-gray-100">
                                    <i class="fa-light fa-chart-simple-slash text-5xl text-gray-200 mb-4 block"></i>
                                    <p class="text-sm font-bold text-gray-400 italic px-8">{{ __('matches.empty_stats') }}</p>
                                </div>
                            </div>

                            {{-- Opponent Tab --}}
                            <div x-show="activeTab === 'opponent'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <div x-show="opponentStats.length > 0" class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full text-left border-collapse min-w-[800px]">
                                        <thead>
                                            <tr class="bg-gray-900 text-white sticky top-0 z-10">
                                                <th class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center border-r border-white/5 sticky left-0 bg-gray-900 z-20">#</th>
                                                <th @click="sortBy('name')" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest cursor-pointer hover:bg-gray-800 transition-colors group sticky left-[41px] bg-gray-900 z-20 border-r border-white/5">
                                                    <div class="flex items-center gap-2">
                                                        Hráč
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
                                                    <td class="px-4 py-1 text-center text-[10px] font-black text-gray-300 border-r border-gray-50 sticky left-0 bg-inherit z-10">
                                                        <span x-show="!stat.is_special" x-text="(index + 1) + '.'"></span>
                                                        <i x-show="stat.is_special && !stat.is_team" class="fa-light fa-sigma text-brand-500"></i>
                                                        <i x-show="stat.is_team" class="fa-light fa-users-gear text-gray-400"></i>
                                                    </td>
                                                    <td class="px-4 py-1 whitespace-nowrap sticky left-[41px] bg-inherit z-10 border-r border-gray-50 shadow-[4px_0_10px_-4px_rgba(0,0,0,0.05)]">
                                                        <div class="flex items-center gap-3">
                                                            <div x-show="!stat.is_special" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 shadow-inner shrink-0" x-text="stat.number"></div>
                                                            <div class="flex flex-col leading-tight min-w-0">
                                                                <span class="text-sm font-bold truncate" :class="stat.is_special ? 'text-gray-900' : 'text-gray-700'" x-text="stat.name"></span>
                                                                <span x-show="stat.is_starter" class="text-[7px] font-black text-brand-500 uppercase tracking-widest mt-0.5 whitespace-nowrap">Základní pětka</span>
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
                                <div x-show="opponentStats.length === 0" class="py-12 text-center bg-gray-50 border-t border-gray-100">
                                    <i class="fa-light fa-chart-simple-slash text-5xl text-gray-200 mb-4 block"></i>
                                    <p class="text-sm font-bold text-gray-400 italic px-8">{{ __('matches.empty_opponent_stats') }}</p>
                                </div>
                            </div>

                </div>
            </div>
        </div>
    </div>
@endsection
