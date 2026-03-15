<div class="space-y-12">
    @if(!empty($externalStats))
        {{-- SEZÓNNÍ STATISTIKY --}}
        <div class="space-y-6">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center px-1">
                <i class="fa-light fa-history mr-3 text-primary-500"></i> Historie a kariéra (cz.basketball)
            </h3>
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sezóna / Soutěž</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tým</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Z</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">B Ø</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">EF Ø</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">DOS Ø</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center text-primary-500">TH %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($externalStats as $stat)
                                <tr @class([
                                    'hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors',
                                    'bg-primary-50/30 dark:bg-primary-900/10 font-bold' => $stat['is_career_total']
                                ])>
                                    <td class="px-6 py-4">
                                        @if($stat['is_career_total'])
                                            <span class="text-primary-600 uppercase tracking-wider">Celkem kariéra</span>
                                        @else
                                            <div class="text-sm font-black text-gray-800 dark:text-white">{{ $stat['season_label'] }}</div>
                                            <div class="text-[10px] text-gray-400 font-medium">{{ $stat['competition_label'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $stat['team_name'] ?: '-' }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-gray-800 dark:text-white">{{ $stat['games_played'] }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-gray-800 dark:text-white">{{ number_format($stat['points_avg'], 1, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-bold text-orange-500">{{ number_format($stat['valuation_avg'] ?? 0, 1, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-gray-600 dark:text-gray-400">{{ number_format($stat['rebounds_total_avg'] ?? 0, 1, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-center">
                                        @if($stat['free_throws_pct'])
                                            <span class="px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs font-black">
                                                {{ number_format($stat['free_throws_pct'], 1, ',', ' ') }}%
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($externalMatches))
        {{-- POSLEDNÍ ZÁPASY --}}
        <div class="space-y-6">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center px-1">
                <i class="fa-light fa-basketball-hoop mr-3 text-orange-500"></i> Poslední zápasy (cz.basketball)
            </h3>
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Datum / Soutěž</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Soupeř</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Body</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">EF</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">DOS</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">AS</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center text-emerald-500">Z</th>
                                <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">F-</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($externalMatches as $match)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-black text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($match['match_date'])->format('d.m.Y') }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium">{{ $match['competition_label'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($match['external_match_id'])
                                            <a href="https://cz.basketball/zapas/{{ $match['external_match_id'] }}" target="_blank" class="text-sm font-bold text-primary-600 hover:text-primary-700 flex items-center gap-2 group">
                                                {{ $match['opponent_name'] }}
                                                <i class="fa-light fa-external-link text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                            </a>
                                        @else
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $match['opponent_name'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center mx-auto text-sm font-black text-gray-800 dark:text-white border border-gray-100 dark:border-gray-700">
                                            {{ $match['points'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-bold text-orange-500">{{ $match['valuation'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-xs text-gray-500 font-bold">{{ $match['rebounds_total'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-xs text-gray-500 font-bold">{{ $match['assists'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-xs text-emerald-500 font-black">{{ $match['steals'] ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span @class([
                                            'px-2 py-0.5 rounded text-[10px] font-black',
                                            'bg-red-50 text-red-600' => ($match['fouls'] ?? 0) >= 4,
                                            'bg-gray-50 text-gray-400' => ($match['fouls'] ?? 0) < 4
                                        ])>
                                            {{ $match['fouls'] ?? 0 }} F
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border border-gray-100 dark:border-gray-800 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 flex-shrink-0">
                    <i class="fa-light fa-info-circle"></i>
                </div>
                <p class="text-[11px] text-gray-500 font-medium leading-relaxed">
                    Tyto statistiky jsou synchronizovány z externího portálu <a href="https://cz.basketball" target="_blank" class="text-primary-600 font-bold hover:underline">cz.basketball</a>.
                    Mohou se lišit od našich interních výpočtů v závislosti na kvalitě a rychlosti dodání dat ze strany ČBF.
                </p>
            </div>
        </div>
    @endif
</div>
