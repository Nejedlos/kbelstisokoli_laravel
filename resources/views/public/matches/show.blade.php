@extends('layouts.public')

@section('content')
    @php
        $statusColors = [
            'planned' => 'bg-white text-secondary',
            'scheduled' => 'bg-white text-secondary',
            'finished' => 'bg-success text-white',
            'cancelled' => 'bg-danger text-white',
            'postponed' => 'bg-warning text-black',
        ];
        $statusLabels = [
            'planned' => __('matches.planned'),
            'scheduled' => __('matches.planned'),
            'finished' => __('matches.finished'),
            'cancelled' => __('matches.cancelled'),
            'postponed' => __('matches.postponed'),
        ];

        $hasKlub = $match->teams->contains('slug', 'klub');
        $teamNames = $match->teams->pluck('name')->join(' & ');
        $mainTeamName = ($hasKlub || $match->teams->count() > 1) ? ($hasKlub ? 'Sokoli (Celý klub)' : $teamNames) : $match->official_team_name;
    @endphp
    <x-page-header
        :title="$mainTeamName . ' ' . __('matches.vs') . ' ' . $match->official_opponent_name"
        :subtitle="$match->scheduled_at->format('d. m. Y H:i') . ' | ' . ($match->location ?? __('matches.location_not_specified'))"
        :breadcrumbs="[__('matches.breadcrumbs') => route('public.matches.index'), __('matches.view_detail') => null]"
        image="assets/img/hero/hero-match-detail.webp"
    />

    <div class="section-padding bg-bg">
        <div class="container max-w-5xl">
            <!-- Match Center Hero -->
            <div class="card overflow-hidden mb-12 border-t-8 border-t-primary">
                <div class="bg-secondary text-white p-8 md:p-16">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-12">
                        <!-- Home Team -->
                        <div class="flex-1 flex flex-col items-center text-center">
                            @php
                                $brandingResolver = app(\App\Services\TeamBrandingResolver::class);
                                $homeBranding = $brandingResolver->getMatchLogo($match, true);
                                $awayBranding = $brandingResolver->getMatchLogo($match, false);
                                $teamLogoSettings = $branding['team_logo'] ?? null;
                                $isMatchDetailLogoEnabled = $teamLogoSettings['enabled_match_detail'] ?? true;
                            @endphp
                            <div @class([
                                "w-24 h-24 md:w-32 md:h-32 rounded-club flex items-center justify-center mb-6 border overflow-hidden p-4",
                                "bg-white border-white" => $match->is_home,
                                "bg-white/10 border-white/20" => !$match->is_home,
                            ])>
                                @if($match->is_home)
                                    @if($isMatchDetailLogoEnabled && $homeBranding)
                                        <picture>
                                            <source srcset="{{ $homeBranding['logo_url_large_webp'] }}" type="image/webp">
                                            <img src="{{ $homeBranding['logo_url_large'] }}"
                                                 class="max-w-full max-h-full object-contain drop-shadow-2xl"
                                                 alt="{{ $homeBranding['alt'] }}"
                                                 style="height: {{ $teamLogoSettings['sizes']['match_detail'] ?? 56 }}px; width: auto;">
                                        </picture>
                                    @elseif($branding['logo_path'] ?? null)
                                        <img src="{{ web_asset($branding['logo_path']) }}" class="max-w-[80%] max-h-[80%] object-contain" alt="{{ $branding['club_name'] ?? 'Sokoli' }}">
                                    @else
                                        <i class="fa-light fa-shield-halved text-4xl opacity-20"></i>
                                    @endif
                                @else
                                    @if($match->opponent->logo)
                                        <img src="{{ web_asset($match->opponent->logo) }}" class="max-w-[80%] max-h-[80%] object-contain" alt="{{ $match->opponent->name }}">
                                    @else
                                        <i class="fa-light fa-shield-halved text-4xl opacity-20"></i>
                                    @endif
                                @endif
                            </div>
                            <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tight">
                                {{ $match->is_home ? $match->official_team_name : $match->official_opponent_name }}
                            </h3>
                        </div>

                        <div class="flex flex-col items-center min-w-[150px]">
                            @if($match->status === 'finished' && ($match->score_home !== null || $match->score_away !== null))
                                <div @class([
                                    "text-6xl md:text-8xl font-black tabular-nums tracking-tighter leading-none mb-4 flex items-center gap-6 md:gap-12",
                                    "text-emerald-400" => $match->is_win,
                                    "text-rose-400" => $match->is_loss,
                                    "text-white" => !$match->is_win && !$match->is_loss,
                                ])>
                                    <span>{{ $match->score_home ?? 0 }}</span>
                                    <span class="opacity-30">:</span>
                                    <span>{{ $match->score_away ?? 0 }}</span>
                                </div>
                                @if($match->is_win)
                                    <div class="mb-4 px-4 py-1 rounded-full bg-emerald-500 text-white text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-900/20 ring-4 ring-emerald-500/20">
                                        <i class="fa-light fa-trophy-star mr-1.5"></i> {{ __('matches.result_v') }}
                                    </div>
                                @elseif($match->is_loss)
                                    <div class="mb-4 px-4 py-1 rounded-full bg-rose-500 text-white text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-900/20 ring-4 ring-rose-500/20">
                                        <i class="fa-light fa-face-frown mr-1.5"></i> {{ __('matches.result_p') }}
                                    </div>
                                @elseif($match->is_draw)
                                    <div class="mb-4 px-4 py-1 rounded-full bg-slate-500 text-white text-xs font-black uppercase tracking-[0.2em]">
                                        <i class="fa-light fa-handshake mr-1.5"></i> {{ __('matches.result_r') }}
                                    </div>
                                @endif
                                <div class="text-[10px] font-black uppercase tracking-widest text-primary/60 mb-6">
                                    {{ __('matches.match_result') }}
                                </div>
                            @else
                                <div class="text-4xl md:text-5xl font-black opacity-30 mb-4 uppercase tracking-widest italic">VS</div>
                            @endif
                            @php
                                $isPast = $match->scheduled_at ? $match->scheduled_at->isPast() : ($match->season_id < 3);
                            @endphp
                            @if(($match->status === 'planned' || $match->status === 'scheduled') && $isPast)
                                <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-blue-600 text-white">
                                    {{ __('matches.action_took_place') }}
                                </span>
                            @else
                                <span @class([
                                    'px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest',
                                    'bg-blue-600 text-white' => $match->status === 'finished' && $match->score_home === null && $match->score_away === null,
                                    $statusColors[$match->status] ?? 'bg-slate-700' => !($match->status === 'finished' && $match->score_home === null && $match->score_away === null)
                                ])>
                                    {{ ($match->status === 'finished' && $match->score_home === null && $match->score_away === null) ? __('matches.action_took_place') : ($statusLabels[$match->status] ?? $match->status) }}
                                </span>
                            @endif
                            @if($match->status === 'finished' && $match->score_home === null && $match->score_away === null)
                                <div class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                                    {{ __('matches.result_missing') }}
                                </div>
                            @endif
                        </div>

                        <!-- Away Team -->
                        <div class="flex-1 flex flex-col items-center text-center">
                            <div @class([
                                "w-24 h-24 md:w-32 md:h-32 rounded-club flex items-center justify-center mb-6 border overflow-hidden p-4",
                                "bg-white border-white" => !$match->is_home,
                                "bg-white/10 border-white/20" => $match->is_home,
                            ])>
                                @if(!$match->is_home)
                                    @if($isMatchDetailLogoEnabled && $awayBranding)
                                        <picture>
                                            <source srcset="{{ $awayBranding['logo_url_large_webp'] }}" type="image/webp">
                                            <img src="{{ $awayBranding['logo_url_large'] }}"
                                                 class="max-w-full max-h-full object-contain drop-shadow-2xl"
                                                 alt="{{ $awayBranding['alt'] }}"
                                                 style="height: {{ $teamLogoSettings['sizes']['match_detail'] ?? 56 }}px; width: auto;">
                                        </picture>
                                    @elseif($branding['logo_path'] ?? null)
                                        <img src="{{ web_asset($branding['logo_path']) }}" class="max-w-[80%] max-h-[80%] object-contain" alt="{{ $branding['club_name'] ?? 'Sokoli' }}">
                                    @else
                                        <i class="fa-light fa-shield-halved text-4xl opacity-20"></i>
                                    @endif
                                @else
                                    @if($match->opponent->logo)
                                        <img src="{{ web_asset($match->opponent->logo) }}" class="max-w-[80%] max-h-[80%] object-contain" alt="{{ $match->opponent->name }}">
                                    @else
                                        <i class="fa-light fa-shield-halved text-4xl opacity-20"></i>
                                    @endif
                                @endif
                            </div>
                            <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tight">
                                {{ $match->is_home ? $match->official_opponent_name : $match->official_team_name }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Match Meta Info -->
                <div class="bg-white border-t border-slate-100 p-6 flex flex-wrap items-center justify-center gap-x-12 gap-y-4 text-sm font-bold uppercase tracking-widest text-slate-500">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $match->scheduled_at->format('d. m. Y') }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $match->scheduled_at->format('H:i') }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $match->location ?? 'Místo neuvedeno' }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ $match->match_type_label }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ $match->season->name }}
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    @if($match->notes_public)
                        <section class="card p-8">
                            <h2 class="text-2xl font-black uppercase tracking-tight mb-6 border-b border-slate-100 pb-4">
                                {{ app()->getLocale() === 'cs' ? 'Informace k zápasu' : 'Match information' }}
                            </h2>
                            <div class="prose prose-slate max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-tight prose-a:text-primary">
                                {!! nl2br(e($match->notes_public)) !!}
                            </div>
                        </section>
                    @endif

                    @php
                        $prediction = $match->prediction;
                        $isPlayed = $match->status === 'finished';
                        $winChance = $prediction ? round($prediction->probability_win * 100) : null;

                        $colorHsl = null;
                        if ($winChance !== null) {
                            if ($winChance <= 50) {
                                $hue = ($winChance / 50) * 35;
                            } else {
                                $hue = 35 + (($winChance - 50) / 50) * 105;
                            }
                            $colorHsl = "hsl({$hue}, 75%, 45%)";
                        }
                    @endphp

                    <section class="card p-8 border-t-4 transition-colors duration-1000" style="border-top-color: {{ $colorHsl ?? '#f1f5f9' }}">
                        <h2 class="text-2xl font-black uppercase tracking-tight mb-6 border-b border-slate-100 pb-4">
                            {{ $isPlayed ? (app()->getLocale() === 'cs' ? 'Reportáž ze zápasu' : 'Match report') : (app()->getLocale() === 'cs' ? 'Předzápasová analýza' : 'Pre-match analysis') }}
                        </h2>

                        @if($prediction)
                            <div class="space-y-10">
                                <!-- Prediction Stats -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch">
                                    <div class="md:col-span-5 p-8 rounded-[2rem] border flex flex-col items-center justify-center text-center shadow-inner relative overflow-hidden group/pc transition-all duration-1000" style="background-color: {{ $colorHsl ? str_replace(')', ', 0.08)', str_replace('hsl', 'hsla', $colorHsl)) : '#f8fafc' }}; border-color: {{ $colorHsl ? str_replace(')', ', 0.2)', str_replace('hsl', 'hsla', $colorHsl)) : '#e2e8f0' }}; box-shadow: inset 0 2px 4px 0 {{ $colorHsl ? str_replace(')', ', 0.05)', str_replace('hsl', 'hsla', $colorHsl)) : 'rgba(0,0,0,0.05)' }}">
                                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-20 rounded-full -mr-16 -mt-16 group-hover/pc:scale-150 transition-transform duration-700"></div>
                                        <div class="relative z-10">
                                            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-4 mx-auto border" style="border-color: {{ $colorHsl ? str_replace(')', ', 0.2)', str_replace('hsl', 'hsla', $colorHsl)) : '#e2e8f0' }}">
                                                <i class="fa-light fa-crystal-ball text-xl animate-pulse" style="color: {{ $colorHsl }}"></i>
                                            </div>
                                            <div class="text-6xl font-black mb-1 tabular-nums tracking-tighter" style="color: {{ $colorHsl }}">
                                                {{ $winChance }}%
                                            </div>
                                            <div class="text-[10px] font-black uppercase tracking-widest mb-6" style="color: {{ $colorHsl }}; opacity: 0.6">
                                                {{ __('matches.prediction.win_chance') ?? 'Šance na výhru' }}
                                            </div>

                                            @php
                                                $confColor = match($prediction->confidence) {
                                                    'high' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                    'medium' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                                };
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm {{ $confColor }}">
                                                {{ __('matches.prediction.confidence_' . $prediction->confidence) ?? $prediction->confidence }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="md:col-span-7 flex flex-col justify-center space-y-6">
                                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden shadow-inner p-0.5">
                                            <div class="h-full rounded-full shadow-lg transition-all duration-1000" style="width: {{ $winChance }}%; background-color: {{ $colorHsl }}"></div>
                                        </div>

                                        <div class="space-y-4">
                                            <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-secondary flex items-center gap-2">
                                                <i class="fa-light fa-list-check text-primary"></i>
                                                {{ __('matches.prediction.why_title') ?? 'Proč si to myslíme' }}
                                            </h4>
                                            <ul class="space-y-3">
                                                @foreach($prediction->explanation_points as $point)
                                                    <li class="flex items-start gap-3 text-sm text-slate-600 font-medium leading-relaxed group/li">
                                                        <i class="fa-light fa-circle-check mt-1 text-primary shrink-0 group-hover/li:scale-110 transition-transform"></i>
                                                        {{ $point }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Motivational Quote -->
                                <div class="relative p-8 md:p-14 rounded-[3rem] overflow-hidden text-center shadow-2xl group transition-all duration-1000 bg-secondary" style="box-shadow: 0 25px 50px -12px {{ $colorHsl ? str_replace(')', ', 0.2)', str_replace('hsl', 'hsla', $colorHsl)) : 'rgba(0,0,0,0.1)' }}">
                                    <!-- Dynamic Background Elements -->
                                    <div class="absolute top-0 right-0 w-80 h-80 rounded-full -mr-40 -mt-40 blur-[100px] animate-pulse opacity-20" style="background-color: {{ $colorHsl ?? 'white' }}"></div>
                                    <div class="absolute bottom-0 left-0 w-64 h-64 rounded-full -ml-32 -mb-32 blur-[80px] opacity-10" style="background-color: {{ $colorHsl ?? 'white' }}"></div>
                                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full opacity-40 bg-gradient-to-br from-secondary via-secondary to-slate-900/80"></div>

                                    <div class="relative z-10">
                                        <div class="mb-8 inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-sm shadow-inner group-hover:scale-110 transition-transform duration-500">
                                            @if($winChance > 65)
                                                <i class="fa-light fa-fire text-white text-3xl animate-bounce" style="animation-duration: 3s;"></i>
                                            @elseif($winChance < 35)
                                                <i class="fa-light fa-shield text-white text-3xl"></i>
                                            @else
                                                <i class="fa-light fa-quote-left text-3xl" style="color: {{ $colorHsl }}"></i>
                                            @endif
                                        </div>

                                        <blockquote class="text-2xl md:text-4xl font-black text-white leading-[1.1] mb-8 tracking-tight drop-shadow-sm italic">
                                            "{{ $match->motivational_quote }}"
                                        </blockquote>

                                        <div class="flex items-center justify-center gap-4">
                                            <div class="h-px w-8 bg-gradient-to-r from-transparent to-white/50"></div>
                                            <div class="text-white font-black uppercase tracking-[0.3em] text-[11px] py-1 px-3 rounded-full bg-white/10 border border-white/20 backdrop-blur-md" style="border-color: {{ $colorHsl ? str_replace(')', ', 0.4)', str_replace('hsl', 'hsla', $colorHsl)) : 'rgba(255,255,255,0.2)' }}">
                                                @if($winChance > 65)
                                                    {{ __('motivational.pre_match.labels.high') }}
                                                @elseif($winChance < 35)
                                                    {{ __('motivational.pre_match.labels.low') }}
                                                @else
                                                    {{ __('motivational.pre_match.labels.medium') }}
                                                @endif
                                            </div>
                                            <div class="h-px w-8 bg-gradient-to-l from-transparent to-white/50"></div>
                                        </div>
                                    </div>

                                    <!-- Decorative Basketball Icon -->
                                    <div class="absolute -bottom-6 -right-6 opacity-[0.05] rotate-12 group-hover:rotate-0 transition-transform duration-1000">
                                        <i class="fa-light fa-basketball text-9xl text-white"></i>
                                    </div>
                                </div>
                            </div>
                        @elseif($isPlayed && $match->statisticRows->count() > 0)
                            @php
                                $sortedRows = $match->statisticRows->sortByDesc(function($row) {
                                    return (int) ($row->values['pts'] ?? 0);
                                });
                            @endphp
                            <div class="space-y-10">
                                <!-- Post-Match Vibe -->
                                <div @class([
                                    'relative p-6 md:p-10 rounded-[2rem] overflow-hidden text-center border',
                                    'bg-success/10 border-success/20' => $match->is_win,
                                    'bg-rose-50 border-rose-100' => $match->is_loss,
                                    'bg-slate-50 border-slate-200' => !$match->is_win && !$match->is_loss,
                                ])>
                                    @if($match->is_win)
                                        <div class="absolute top-0 right-0 w-64 h-64 bg-success/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                                        <i class="fa-light fa-star text-success/30 text-4xl mb-6 block"></i>
                                    @elseif($match->is_loss)
                                        <div class="absolute top-0 right-0 w-64 h-64 bg-rose-500/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                                        <i class="fa-light fa-shield-heart text-rose-300 text-4xl mb-6 block"></i>
                                    @else
                                        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-500/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                                        <i class="fa-light fa-handshake text-slate-300 text-4xl mb-6 block"></i>
                                    @endif

                                    <blockquote @class([
                                        'text-xl md:text-2xl font-black leading-tight mb-4 relative z-10',
                                        'text-secondary' => $match->is_win,
                                        'text-slate-800' => !$match->is_win,
                                    ])>
                                        "{{ $match->post_match_vibe }}"
                                    </blockquote>
                                    <div @class([
                                        'font-black uppercase tracking-widest text-[10px]',
                                        'text-success' => $match->is_win,
                                        'text-rose-500' => $match->is_loss,
                                        'text-slate-500' => $match->is_draw,
                                    ])>
                                        @if($match->is_win)
                                            {{ __('motivational.post_match.labels.win') }}
                                        @elseif($match->is_loss)
                                            {{ __('motivational.post_match.labels.loss') }}
                                        @else
                                            {{ __('motivational.post_match.labels.draw') }}
                                        @endif
                                    </div>
                                </div>

                                <!-- TOP Players -->
                                <div class="space-y-6">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-secondary flex items-center gap-2">
                                        <i class="fa-light fa-trophy text-primary"></i>
                                        Nejlepší střelci zápasu
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        @foreach($sortedRows->take(3) as $row)
                                            <div class="p-6 bg-white rounded-[2rem] border border-slate-100 flex flex-col items-center text-center group hover:border-primary/30 transition-colors shadow-sm">
                                                <div class="relative mb-4">
                                                    <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-100 border-2 border-white shadow-sm">
                                                        @if($row->player?->getAvatarUrl())
                                                            <img src="{{ $row->player->getAvatarUrl() }}" alt="{{ $row->row_label }}" class="w-full h-full object-cover">
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-300">
                                                                <i class="fa-light fa-user text-2xl"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="absolute -bottom-1 -right-1 bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black border-2 border-white">
                                                        {{ $loop->iteration }}
                                                    </div>
                                                </div>
                                                <div class="font-black text-secondary text-sm mb-1 line-clamp-1">
                                                    {{ $row->row_label }}
                                                </div>
                                                <div class="text-primary font-black text-lg">
                                                    {{ $row->values['pts'] ?? 0 }} <span class="text-[10px] uppercase tracking-tighter">bodů</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Quick Stats -->
                                @php
                                    $totalPoints = $match->statisticRows->sum(fn($r) => (int)($r->values['pts'] ?? 0));
                                    $totalThrees = $match->statisticRows->sum(fn($r) => (int)($r->values['fg3_made'] ?? 0));
                                    $scorersCount = $match->statisticRows->filter(fn($r) => ($r->values['pts'] ?? 0) > 0)->count();
                                @endphp
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4">
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                        <div class="text-2xl font-black text-secondary">{{ $totalPoints }}</div>
                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Týmové body</div>
                                    </div>
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                        <div class="text-2xl font-black text-secondary">{{ $totalThrees }}</div>
                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Trojky týmu</div>
                                    </div>
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center col-span-2 sm:col-span-1">
                                        <div class="text-2xl font-black text-secondary">{{ $scorersCount }}</div>
                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Hráčů skórovalo</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <x-empty-state
                                :title="$isPlayed ? (app()->getLocale() === 'cs' ? 'Reportáž připravujeme' : 'Report in preparation') : (app()->getLocale() === 'cs' ? 'Analýza se připravuje' : 'Analysis in preparation')"
                                :subtitle="$isPlayed ? (app()->getLocale() === 'cs' ? 'Podrobné statistiky a komentář k zápasu budou doplněny co nejdříve po jeho skončení.' : 'Detailed statistics and match commentary will be added as soon as possible after the game.') : (app()->getLocale() === 'cs' ? 'Předzápasová predikce a motivační hlášky budou k dispozici brzy.' : 'Pre-match prediction and motivational quotes will be available soon.')"
                            />
                        @endif
                    </section>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    @if(!$isPlayed)
                        <!-- Additional Info Widget -->
                        <aside class="card p-6 bg-secondary text-white">
                            <h3 class="text-lg font-black uppercase tracking-tight mb-4 text-primary">{{ __('matches.important_info') }}</h3>
                            <ul class="space-y-4 text-sm font-medium">
                                <li class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="opacity-60">{{ __('matches.meeting_time') }}</span>
                                    <span>{{ $match->meeting_at->format('H:i') }}</span>
                                </li>
                                <li class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="opacity-60">{{ __('matches.jerseys') }}</span>
                                    <span>{{ $match->jerseys_info }}</span>
                                </li>
                            </ul>
                        </aside>
                    @endif

                    <!-- Partner Badge -->
                    @php
                        $matchPartners = app(\App\Services\PartnerService::class)->getMatchPartners();
                    @endphp

                    @if($matchPartners->isNotEmpty())
                        <aside class="card p-6 border-l-4 border-l-primary/30 shadow-sm">
                            <h3 class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 mb-6 leading-none">
                                {{ __('partners.match_label') }}
                            </h3>
                            <div class="space-y-6">
                                @foreach($matchPartners as $partner)
                                    <div class="group/mpartner">
                                        <a href="{{ $partner->website_url ?? '#' }}"
                                           @if($partner->opened_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                           class="block mb-2">
                                            <picture>
                                                @if($partner->logo_path_webp)
                                                    <source srcset="{{ asset($partner->logo_path_webp) }}" type="image/webp">
                                                @endif
                                                <img src="{{ asset($partner->logo_path_png ?? $partner->logo_path_webp) }}"
                                                     alt="{{ $partner->name }}"
                                                     class="object-contain transition-all duration-300 hover:scale-105"
                                                     style="max-height: 40px; width: auto;">
                                            </picture>
                                        </a>
                                        @php $partnerLabel = $partner->getTranslation('label', app()->getLocale()); @endphp
                                        @if($partnerLabel)
                                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tight leading-none">
                                                {{ $partnerLabel }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </aside>
                    @endif

                    <!-- Back Link -->
                    <a href="{{ route('public.matches.index') }}" class="btn btn-outline-primary w-full py-4 uppercase tracking-widest font-black text-sm">
                        &larr; {{ app()->getLocale() === 'cs' ? 'Zpět na seznam zápasů' : 'Back to match list' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Placeholder pro budoucí interaktivitu (např. auto-refresh skóre ze svazu)
</script>
@endpush
