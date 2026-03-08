<div class="space-y-8" x-data="{ view: @entangle('view') }">
    {{-- Top Navigation & Selection --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
        {{-- View Switcher --}}
        <div class="flex p-1 bg-gray-100 dark:bg-gray-900 rounded-xl w-full md:w-auto">
            <button
                wire:click="setView('personal')"
                class="flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all"
                :class="view === 'personal' ? 'bg-white dark:bg-gray-800 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <i class="fa-light fa-user-chart mr-2"></i> Moje osobní
            </button>
            <button
                wire:click="setView('team')"
                class="flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all"
                :class="view === 'team' ? 'bg-white dark:bg-gray-800 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <i class="fa-light fa-users-viewfinder mr-2"></i> Týmový přehled
            </button>
            <button
                wire:click="setView('matches')"
                class="flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all"
                :class="view === 'matches' ? 'bg-white dark:bg-gray-800 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <i class="fa-light fa-calendar-lines mr-2"></i> Zápasy
            </button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <div class="relative min-w-[140px]">
                <select wire:model.change="seasonId" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary-500 py-2.5 pl-4 pr-10 appearance-none">
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                    <i class="fa-light fa-calendar-range text-xs"></i>
                </div>
            </div>

            <div class="relative min-w-[200px]">
                <select wire:model.change="teamId" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary-500 py-2.5 pl-4 pr-10 appearance-none">
                    <optgroup label="Moje týmy">
                        @foreach($userTeams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Ostatní týmy">
                        @foreach($allTeams as $team)
                            @if(!$userTeams->contains($team))
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endif
                        @endforeach
                    </optgroup>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                    <i class="fa-light fa-basketball text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    @if($view === 'personal')
        {{-- PERSONAL VIEW --}}
        @if($summary)
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4">
                @php
                    $cards = [
                        ['label' => 'Zápasy', 'value' => $summary['gp'] ?? 0, 'icon' => 'fa-basketball', 'color' => 'primary'],
                        ['label' => 'Body Celkem', 'value' => $summary['pts_total'] ?? 0, 'icon' => 'fa-bullseye', 'color' => 'blue'],
                        ['label' => 'PPG', 'value' => $summary['ppg'] ?? 0, 'icon' => 'fa-chart-scatter', 'color' => 'indigo'],
                        ['label' => 'Minuty Ø', 'value' => $summary['minutes_avg'] ?? 0, 'icon' => 'fa-clock', 'color' => 'emerald'],
                    ];

                    if (isset($summary['fg2_pct'])) $cards[] = ['label' => '2B %', 'value' => $summary['fg2_pct'] . '%', 'icon' => 'fa-arrow-progress', 'color' => 'orange'];
                    if (isset($summary['fg3_pct'])) $cards[] = ['label' => '3B %', 'value' => $summary['fg3_pct'] . '%', 'icon' => 'fa-arrow-up-right-dots', 'color' => 'violet'];
                    if (isset($summary['ft_pct'])) $cards[] = ['label' => 'TH %', 'value' => $summary['ft_pct'] . '%', 'icon' => 'fa-bullseye-arrow', 'color' => 'pink'];
                @endphp
                @foreach($cards as $card)
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center relative overflow-hidden">
                        <div class="absolute -right-2 -bottom-2 opacity-5">
                            <i class="fa-light {{ $card['icon'] }} text-5xl"></i>
                        </div>
                        <div class="text-[10px] text-gray-400 font-bold mb-1 uppercase tracking-widest text-center">{{ $card['label'] }}</div>
                        <div class="text-3xl font-black text-gray-800 dark:text-white">{{ $card['value'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Insights & Rankings --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Insights --}}
                <div class="lg:col-span-2 space-y-4">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest flex items-center px-1">
                        <i class="fa-light fa-lightbulb-on mr-2 text-yellow-500"></i> Sezónní postřehy
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($insights as $insight)
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex-shrink-0 flex items-center justify-center">
                                    @if($insight['type'] === 'best_match') <i class="fa-light fa-star text-yellow-500"></i>
                                    @elseif($insight['type'] === 'stability') <i class="fa-light fa-wave-pulse text-primary-500"></i>
                                    @elseif($insight['type'] === 'trend_up') <i class="fa-light fa-arrow-trend-up text-green-500"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-400">{{ $insight['label'] }}</div>
                                    <div class="text-sm font-black text-gray-800 dark:text-white">{{ $insight['value'] }}</div>
                                    @if(isset($insight['date']))
                                        <div class="text-[10px] text-gray-400 mt-1 italic">{{ $insight['date'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="sm:col-span-2 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 text-center text-gray-400 text-sm italic">
                                Zatím nemáme dostatek dat pro generování postřehů.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Rankings --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest flex items-center px-1">
                        <i class="fa-light fa-ranking-star mr-2 text-primary-500"></i> Týmový ranking
                    </h3>
                    <div class="bg-primary-600 p-6 rounded-2xl text-white shadow-lg shadow-primary-500/20 relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-10">
                            <i class="fa-light fa-medal text-9xl"></i>
                        </div>
                        <div class="space-y-4 relative z-10">
                            @foreach($rankings as $key => $rank)
                                @php
                                    $label = match($key) {
                                        'pts_total' => 'Body celkem',
                                        'ppg' => 'PPG (Ø body)',
                                        'gp' => 'Účast na zápasech',
                                        'minutes_avg' => 'Vytížení Ø',
                                        default => $key
                                    };
                                @endphp
                                <div class="flex justify-between items-end border-b border-primary-500 pb-2">
                                    <div class="text-[10px] font-bold uppercase opacity-80">{{ $label }}</div>
                                    <div class="font-black">
                                        <span class="text-xl">#{{ $rank['rank'] }}</span>
                                        <span class="text-[10px] opacity-60">/ {{ $rank['total'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                            @if(empty($rankings))
                                <div class="text-center py-4 opacity-80 italic text-sm">
                                    Rankings se počítají z kompletních statistik týmu.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" wire:ignore>
                {{-- Points Evolution --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 h-80 flex flex-col">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">
                        <i class="fa-light fa-chart-line mr-2"></i> Vývoj bodů zápas po zápase
                    </h3>
                    <div id="points-evolution-chart" class="flex-1"></div>
                </div>

                {{-- Shooting vs Team Avg --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 h-80 flex flex-col">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">
                        <i class="fa-light fa-chart-mixed mr-2"></i> Moje PPG vs Týmový průměr
                    </h3>
                    <div id="comparison-chart" class="flex-1"></div>
                </div>
            </div>

            {{-- Match Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">
                        Zápisník výkonů
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Zápas</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Body</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">2B</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">3B</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">TH</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">F</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">EF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($perGameSeries as $m)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $m['opponent'] }}</div>
                                    <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($m['date'])->format('d.m.Y') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-black text-primary-600">{{ $m['values']['pts'] ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    {{ ($m['values']['fg2_made'] ?? 0) }}/{{ ($m['values']['fg2_att'] ?? 0) }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    {{ ($m['values']['fg3_made'] ?? 0) }}/{{ ($m['values']['fg3_att'] ?? 0) }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    {{ ($m['values']['ft_made'] ?? 0) }}/{{ ($m['values']['ft_att'] ?? 0) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($m['values']['fouls'] ?? 0) >= 4 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-500' }}">
                                        {{ $m['values']['fouls'] ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-xs font-mono font-bold">{{ $m['values']['efficiency'] ?? 0 }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-800 p-20 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center space-y-4">
                <i class="fa-light fa-chart-user text-6xl text-gray-100 dark:text-gray-700"></i>
                <div class="text-gray-400 font-medium italic">Zatím nemáme nahrané žádné tvé osobní statistiky v této sezóně.</div>
            </div>
        @endif
    @elseif($view === 'team')
        {{-- TEAM VIEW --}}
        @if($teamSummary)
            {{-- Team Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Record --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="fa-light fa-trophy-star text-9xl"></i>
                    </div>
                    <div class="relative z-10 text-center md:text-left">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sezónní bilance</h3>
                        <div class="text-4xl font-black text-gray-800 dark:text-white">
                            <span class="text-green-500">{{ $teamSummary['wins'] ?? 0 }}</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-red-500">{{ $teamSummary['losses'] ?? 0 }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-gray-400">Celkem {{ $teamSummary['gp'] ?? 0 }} zápasů</div>
                    </div>
                </div>

                {{-- Points --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Průměrná ofenzíva</h3>
                    <div class="text-5xl font-black text-primary-600">
                        {{ $teamSummary['pts_avg'] ?? 0 }}
                    </div>
                    <div class="mt-4 flex justify-center gap-4 text-[10px] font-black uppercase text-gray-400">
                        <span>Skóre: {{ $teamSummary['pts_for'] }}</span>
                        <span class="text-gray-200">|</span>
                        <span>Inkasované: {{ $teamSummary['pts_against'] }}</span>
                    </div>
                </div>

                {{-- Form --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Aktuální forma</h3>
                    <div class="flex justify-center gap-2">
                        @foreach($recentForm as $f)
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shadow-sm"
                                @class([
                                    'bg-green-500 text-white' => $f['result'] === 'W',
                                    'bg-red-500 text-white' => $f['result'] === 'L'
                                ])
                                title="{{ $f['opponent'] }} ({{ $f['pts_for'] }}:{{ $f['pts_against'] }})"
                            >
                                {{ $f['result'] }}
                            </div>
                        @endforeach
                        @if(empty($recentForm))
                            <span class="text-gray-400 italic text-xs">Bez nedávných zápasů</span>
                        @endif
                    </div>
                    <div class="mt-4 text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Posledních {{ count($recentForm) }} zápasů</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Top Scorers --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-widest">Tahouni týmu (Body)</h3>
                        <i class="fa-light fa-fire-flame-curved text-orange-500"></i>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hráč</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Z</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">B celkem</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">B/Z</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700 text-sm">
                            @foreach($topScorers as $scorer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="px-6 py-3 font-bold text-gray-800 dark:text-gray-200">{{ $scorer['name'] }}</td>
                                <td class="px-6 py-3 text-center text-gray-500">{{ $scorer['gp'] }}</td>
                                <td class="px-6 py-3 text-center font-bold">{{ $scorer['pts_total'] }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="bg-primary-50 text-primary-600 px-2 py-0.5 rounded text-[10px] font-black">
                                        {{ $scorer['ppg'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Team Chart --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col h-[450px]" wire:ignore>
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Bodový vývoj týmu (vs Soupeři)</h3>
                    <div id="team-evolution-chart" class="flex-1"></div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-800 p-20 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center space-y-4">
                <i class="fa-light fa-users-slash text-6xl text-gray-100 dark:text-gray-700"></i>
                <div class="text-gray-400 font-medium italic">Tento tým v dané sezóně zatím nemá synchronizované týmové statistiky.</div>
            </div>
        @endif
    @elseif($view === 'matches')
        {{-- MATCHES VIEW --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">
                    Přehled zápasů týmu
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Datum</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Soupeř</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Místo</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Výsledek</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Skóre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @forelse($matches as $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($m['scheduled_at'])->format('d.m.Y') }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ \Carbon\Carbon::parse($m['scheduled_at'])->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                    {{ $m['opponent']['name'] ?? 'Neznámý soupeř' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded text-[10px] font-bold {{ $m['is_home'] ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-500' }}">
                                    {{ $m['is_home'] ? 'DOMA' : 'VENKU' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($m['status'] === 'finished')
                                    @php
                                        $isWin = $m['is_home'] ? ($m['score_home'] > $m['score_away']) : ($m['score_away'] > $m['score_home']);
                                    @endphp
                                    <span @class([
                                        'px-2 py-1 rounded text-[10px] font-black',
                                        'bg-green-50 text-green-600' => $isWin,
                                        'bg-red-50 text-red-600' => !$isWin
                                    ])>
                                        {{ $isWin ? 'VÝHRA' : 'PROHRA' }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded bg-gray-50 text-gray-400 text-[10px] font-bold">
                                        PLÁNOVÁNO
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($m['status'] === 'finished')
                                    <div class="text-sm font-black text-gray-800 dark:text-white">
                                        {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                    </div>
                                @else
                                    <div class="text-sm text-gray-300">- : -</div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                Žádné zápasy pro tento tým v této sezóně nebyly nalezeny.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ApexCharts Logic --}}
    @script
    <script>
        const colors = {
            primary: '#e63946',
            blue: '#2196f3',
            gray: '#94a3b8'
        };

        const initPersonalCharts = () => {
            const seriesData = @json($perGameSeries);
            if (!seriesData || seriesData.length === 0) return;

            const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
            const points = seriesData.map(m => m.values.pts || 0);
            const opponents = seriesData.map(m => m.opponent);

            // Points Evolution
            new ApexCharts(document.querySelector("#points-evolution-chart"), {
                series: [{ name: 'Moje body', data: points }],
                chart: { type: 'line', height: '100%', toolbar: { show: false }, zoom: { enabled: false } },
                stroke: { curve: 'smooth', width: 4, colors: [colors.primary] },
                colors: [colors.primary],
                markers: { size: 5, strokeColors: '#fff', strokeWidth: 2, hover: { size: 7 } },
                xaxis: { categories: dates, labels: { show: false }, tooltip: { enabled: false } },
                yaxis: { title: { text: 'Body' } },
                tooltip: {
                    y: { formatter: (val, { dataPointIndex }) => `${val} bodů vs ${opponents[dataPointIndex]}` }
                }
            }).render();

            // Comparison vs Team Avg
            const teamAvg = @json($teamAverages['pts_avg'] ?? 0);
            new ApexCharts(document.querySelector("#comparison-chart"), {
                series: [
                    { name: 'Moje body', type: 'column', data: points },
                    { name: 'Průměr týmu', type: 'line', data: Array(points.length).fill(teamAvg) }
                ],
                chart: { height: '100%', type: 'line', toolbar: { show: false } },
                colors: [colors.primary, colors.gray],
                stroke: { width: [0, 2], dashArray: [0, 5] },
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                xaxis: { categories: dates, labels: { show: false } },
                legend: { position: 'top' }
            }).render();
        };

        const initTeamCharts = () => {
            const seriesData = @json($pointsSeries);
            if (!seriesData || seriesData.length === 0) return;

            const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
            const ptsFor = seriesData.map(m => m.pts_for || 0);
            const ptsAgainst = seriesData.map(m => m.pts_against || 0);

            new ApexCharts(document.querySelector("#team-evolution-chart"), {
                series: [
                    { name: 'My', data: ptsFor },
                    { name: 'Soupeř', data: ptsAgainst }
                ],
                chart: { type: 'area', height: '100%', toolbar: { show: false } },
                colors: [colors.primary, colors.blue],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: dates, labels: { show: false } },
                dataLabels: { enabled: false }
            }).render();
        };

        // Initialize based on initial view
        $wire.view === 'personal' ? initPersonalCharts() : ($wire.view === 'team' ? initTeamCharts() : null);

        // Re-initialize on Livewire updates
        document.addEventListener('livewire:initialized', () => {
             Livewire.on('statsLoaded', () => {
                 // Clear charts before re-render if needed or just let Livewire refresh the DOM
                 // But since we use wire:ignore, we need to manual refresh
                 if (document.querySelector("#points-evolution-chart"))
                    document.querySelector("#points-evolution-chart").innerHTML = '';
                 if (document.querySelector("#comparison-chart"))
                    document.querySelector("#comparison-chart").innerHTML = '';
                 if (document.querySelector("#team-evolution-chart"))
                    document.querySelector("#team-evolution-chart").innerHTML = '';

                 if ($wire.view === 'personal') {
                    initPersonalCharts();
                 } else if ($wire.view === 'team') {
                    initTeamCharts();
                 }
             });
        });
    </script>
    @endscript
</div>
