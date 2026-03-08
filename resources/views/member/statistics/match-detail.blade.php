@extends('layouts.member', [
    'title' => $title ?? __('matches.match_detail'),
])

@section('content')
    <div class="max-w-7xl mx-auto space-y-8 pb-12">
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
                                <i class="fa-light fa-location-dot"></i>
                                {{ $match->metadata['venue'] ?? $match->location }}
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
                        {{-- Home Team (Vždy vlevo) --}}
                        <div class="flex-1 text-center md:text-right">
                            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                                {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                            </h2>
                            <span class="px-3 py-1 bg-brand-100 text-brand-700 text-xs font-bold rounded-full uppercase tracking-wider">{{ __('matches.is_home') }}</span>
                        </div>

                        {{-- Score --}}
                        <div class="flex flex-col items-center">
                            <div class="flex items-center gap-6">
                                <div class="text-6xl md:text-8xl font-black tabular-nums tracking-tighter text-gray-900">
                                    {{ $match->score_home ?? 0 }}
                                </div>
                                <div class="text-3xl font-light text-gray-300">:</div>
                                <div class="text-6xl md:text-8xl font-black tabular-nums tracking-tighter text-gray-900">
                                    {{ $match->score_away ?? 0 }}
                                </div>
                            </div>

                            @if(!empty($match->metadata['periods_detailed']))
                                <div class="mt-6 flex flex-wrap justify-center gap-3">
                                    @foreach($match->metadata['periods_detailed'] as $period)
                                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-100 shadow-sm">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ __('matches.after_period') }} {{ $loop->iteration }}.Q</span>
                                            <span class="text-sm font-bold text-gray-700 tabular-nums">
                                                {{ $period['home'] }}:{{ $period['away'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(!empty($match->metadata['periods']))
                                <div class="mt-4 text-sm font-medium text-gray-500 bg-gray-50 px-4 py-2 rounded-full border border-gray-100">
                                    ({{ $match->metadata['periods'] }})
                                </div>
                            @endif
                        </div>

                        {{-- Away Team (Vždy vpravo) --}}
                        <div class="flex-1 text-center md:text-left">
                            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                                {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                            </h2>
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase tracking-wider">{{ __('matches.is_away') }}</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Best Players Column --}}
            <div class="lg:col-span-1 space-y-8">
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                        <i class="fa-light fa-star text-brand-500"></i>
                        {{ __('matches.best_players') }}
                    </h3>

                    @if(!empty($match->metadata['best_players']))
                        <div class="space-y-6">
                            @foreach($match->metadata['best_players'] as $bestPlayer)
                                <div class="group relative flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100 transition-all hover:shadow-md hover:bg-white hover:border-brand-200">
                                    <div class="relative">
                                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-200 shadow-inner">
                                            @if(!empty($bestPlayer['photo_url']))
                                                <img src="{{ $bestPlayer['photo_url'] }}" alt="{{ $bestPlayer['name'] }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <i class="fa-light fa-user text-2xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center text-[10px] shadow-lg">
                                            <i class="fa-solid fa-crown"></i>
                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-brand-500 uppercase tracking-widest mb-0.5">{{ $bestPlayer['team'] }}</span>
                                        <span class="text-base font-bold text-gray-900 leading-tight">{{ $bestPlayer['name'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <i class="fa-light fa-user-slash text-4xl text-gray-200 mb-4 block"></i>
                            <p class="text-sm text-gray-400 italic">Data o nejlepších hráčích nejsou k dispozici.</p>
                        </div>
                    @endif
                </div>

                {{-- Win/Loss Badge Card --}}
                <div class="bg-brand-600 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-all group-hover:bg-white/20"></div>

                    @php
                        $isVictory = ($match->is_home && $match->score_home > $match->score_away) || (!$match->is_home && $match->score_away > $match->score_home);
                        $isDraw = $match->score_home == $match->score_away;
                    @endphp

                    <div class="relative flex flex-col items-center text-center space-y-4">
                        @if($isVictory)
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md mb-2">
                                <i class="fa-light fa-trophy-star text-3xl"></i>
                            </div>
                            <h4 class="text-2xl font-black uppercase tracking-wider">{{ __('matches.victory') }}</h4>
                            <p class="text-brand-100 text-sm">Skvělý výkon! Tento zápas skončil vítězně.</p>
                        @elseif($isDraw)
                             <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md mb-2">
                                <i class="fa-light fa-handshake text-3xl"></i>
                            </div>
                            <h4 class="text-2xl font-black uppercase tracking-wider">{{ __('matches.draw') }}</h4>
                            <p class="text-brand-100 text-sm">Tento zápas skončil nerozhodně.</p>
                        @else
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md mb-2">
                                <i class="fa-light fa-basketball text-3xl"></i>
                            </div>
                            <h4 class="text-2xl font-black uppercase tracking-wider">{{ __('matches.loss') }}</h4>
                            <p class="text-brand-100 text-sm">Tentokrát to nevyšlo, ale příště budeme silnější!</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats / Boxscore Column --}}
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                            <i class="fa-light fa-chart-user text-brand-500"></i>
                            {{ __('matches.boxscore') }}
                        </h3>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 bg-white border border-gray-200 rounded-full text-[10px] font-bold text-gray-500 uppercase">{{ $match->team->name }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Hráč</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Body</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Min</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">REB</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">AST</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">STL</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">TOV</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">BLK</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">F+</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">F-</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">TH</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">2B</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">3B</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">VAL</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @php
                                    $allStats = \App\Models\StatisticRow::where('basketball_match_id', $match->id)
                                        ->with(['player', 'team'])
                                        ->whereHas('set', fn($q) => $q->where('slug', 'match-boxscore'))
                                        ->get();

                                    $homeStats = $allStats->filter(fn($s) => $match->is_home ? $s->team_id === $match->team_id : $s->source_metadata['is_opponent'] ?? false);
                                    $awayStats = $allStats->filter(fn($s) => $match->is_home ? $s->source_metadata['is_opponent'] ?? false : $s->team_id === $match->team_id);

                                    // Pokud nejsou rozděleny (např. stará data), použijeme vše
                                    if ($homeStats->isEmpty() && $awayStats->isEmpty()) {
                                        $homeStats = $allStats;
                                    }
                                @endphp

                                @if($homeStats->isNotEmpty())
                                    <tr class="bg-gray-100/50">
                                        <td colspan="14" class="px-6 py-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                            {{ $match->is_home ? $match->team->name : $match->opponent?->name }}
                                        </td>
                                    </tr>
                                    @foreach($homeStats as $stat)
                                        @include('member.statistics.partials.boxscore-row', ['stat' => $stat])
                                    @endforeach
                                @endif

                                @if($awayStats->isNotEmpty())
                                    <tr class="bg-gray-100/50">
                                        <td colspan="14" class="px-6 py-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                            {{ $match->is_home ? $match->opponent?->name : $match->team->name }}
                                        </td>
                                    </tr>
                                    @foreach($awayStats as $stat)
                                        @include('member.statistics.partials.boxscore-row', ['stat' => $stat])
                                    @endforeach
                                @endif

                                @if($allStats->isEmpty())
                                    <tr>
                                        <td colspan="14" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                                            Statistiky hráčů pro tento zápas nejsou v databázi uloženy.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- External Link --}}
                @if(!empty($match->metadata['external_id']))
                    <div class="flex justify-center">
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
