<div class="space-y-8 relative" x-data="{ view: @entangle('view') }" x-init="
    console.log('MyStatistics initialized');
">
    <style>
        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(-10%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
            50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
        }
        .animate-bounce-subtle {
            animation: bounce-subtle 2s infinite;
        }
    </style>
    {{-- Top Navigation & Selection --}}
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-[2rem] shadow-xl shadow-gray-200/40 dark:shadow-none border border-white/20 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4 sticky top-4 z-30">
        {{-- View Switcher --}}
        <div class="flex p-1.5 bg-gray-100/50 dark:bg-gray-900/50 rounded-2xl w-full md:w-auto border border-gray-200/30 dark:border-gray-800">
            <a
                href="{{ route('member.statistics.me') }}"
                @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                class="flex-1 md:flex-none px-6 py-2.5 rounded-xl text-xs font-black transition-all text-center uppercase tracking-wider"
                :class="view === 'personal' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <i class="fa-light fa-user-chart mr-2"></i> Osobní
            </a>
            <a
                href="{{ route('member.statistics.players') }}"
                @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                class="flex-1 md:flex-none px-6 py-2.5 rounded-xl text-xs font-black transition-all text-center uppercase tracking-wider"
                :class="view === 'team' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <i class="fa-light fa-users-viewfinder mr-2"></i> Tým
            </a>
            <a
                href="{{ route('member.statistics.matches') }}"
                @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                class="flex-1 md:flex-none px-6 py-2.5 rounded-xl text-xs font-black transition-all text-center uppercase tracking-wider"
                :class="view === 'matches' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <i class="fa-light fa-calendar-lines mr-2"></i> Zápasy
            </a>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto px-2 pb-2 md:pb-0">
            <div class="relative min-w-[140px]">
                <select
                    wire:model.live="seasonId"
                    @change="window.dispatchEvent(new CustomEvent('loading-start'))"
                    class="w-full bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 rounded-2xl text-xs font-black focus:ring-2 focus:ring-primary-500 py-2.5 pl-4 pr-10 appearance-none shadow-sm cursor-pointer hover:border-primary-200 transition-colors uppercase tracking-tight"
                >
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-primary-500">
                    <i class="fa-light fa-calendar-range text-xs"></i>
                </div>
            </div>

            <div class="relative min-w-[200px]">
                <select
                    wire:model.live="teamId"
                    @change="window.dispatchEvent(new CustomEvent('loading-start'))"
                    class="w-full bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 rounded-2xl text-xs font-black focus:ring-2 focus:ring-primary-500 py-2.5 pl-4 pr-10 appearance-none shadow-sm cursor-pointer hover:border-primary-200 transition-colors uppercase tracking-tight"
                >
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
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-primary-500">
                    <i class="fa-light fa-basketball text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    @if($view === 'personal')
        {{-- PERSONAL VIEW --}}
        @if($selectedUserId && $selectedUserId != Auth::id())
            @php $viewedUser = \App\Models\User::find($selectedUserId); @endphp
            <div class="flex items-center gap-4 mb-10 bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 text-2xl font-black overflow-hidden border-4 border-white dark:border-gray-700">
                    @if($viewedUser?->getFilamentAvatarUrl())
                        <img src="{{ $viewedUser->getFilamentAvatarUrl() }}" class="w-full h-full object-cover">
                    @else
                        {{ substr($viewedUser?->name ?? '?', 0, 1) }}
                    @endif
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">PROFIL SPOLUHRÁČE</div>
                    <div class="text-2xl font-black text-gray-800 dark:text-white">{{ $viewedUser?->name ?? '?' }}</div>
                </div>
                <button wire:click="showPlayerStats({{ Auth::id() }})" class="ml-auto bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-700 px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-3 border border-gray-100 dark:border-gray-800">
                    <i class="fa-light fa-arrow-left"></i> Zpět na můj profil
                </button>
            </div>
        @endif
        @if($summary)
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-6">
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

                    $colorClasses = [
                        'primary' => 'from-primary-500 to-primary-600 text-primary-50',
                        'blue' => 'from-blue-500 to-blue-600 text-blue-50',
                        'indigo' => 'from-indigo-500 to-indigo-600 text-indigo-50',
                        'emerald' => 'from-emerald-500 to-emerald-600 text-emerald-50',
                        'orange' => 'from-orange-500 to-orange-600 text-orange-50',
                        'violet' => 'from-violet-500 to-violet-600 text-violet-50',
                        'pink' => 'from-pink-500 to-pink-600 text-pink-50',
                    ];
                @endphp
                        @foreach($cards as $card)
                            <div wire:key="card-{{ $loop->index }}" class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col items-center justify-center relative overflow-hidden group hover:scale-[1.03] transition-all">
                        <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity">
                            <i class="fa-light {{ $card['icon'] }} text-7xl"></i>
                        </div>
                        <div class="text-[10px] text-gray-400 font-black mb-1 uppercase tracking-[0.2em] text-center">{{ $card['label'] }}</div>
                        <div @class([
                            'text-4xl font-black bg-clip-text text-transparent bg-gradient-to-br',
                            $colorClasses[$card['color']] ?? 'from-gray-700 to-gray-900 dark:from-white dark:to-gray-400'
                        ])>
                            {{ $card['value'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Insights & Rankings --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Insights --}}
                <div class="lg:col-span-2 space-y-6">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center px-1">
                        <i class="fa-light fa-lightbulb-on mr-3 text-yellow-500"></i> Sezónní postřehy
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @forelse($insights as $insight)
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex items-start gap-5 group hover:scale-[1.03] transition-all cursor-default">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-900 flex-shrink-0 flex items-center justify-center shadow-inner group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 transition-colors">
                                    @if($insight['type'] === 'best_match') <i class="fa-light fa-star text-2xl text-yellow-500 drop-shadow-sm group-hover:rotate-12 transition-transform"></i>
                                    @elseif($insight['type'] === 'stability') <i class="fa-light fa-wave-pulse text-2xl text-primary-500 drop-shadow-sm group-hover:scale-110 transition-transform"></i>
                                    @elseif($insight['type'] === 'trend_up') <i class="fa-light fa-arrow-trend-up text-2xl text-green-500 drop-shadow-sm group-hover:-translate-y-1 transition-transform"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $insight['label'] }}</div>
                                    <div class="text-lg font-black text-gray-800 dark:text-white leading-tight group-hover:text-primary-600 transition-colors">{{ $insight['value'] }}</div>
                                    @if(isset($insight['date']))
                                        <div class="text-[10px] text-primary-500 mt-2 font-bold flex items-center">
                                            <i class="fa-light fa-calendar-day mr-1.5 opacity-50"></i> {{ $insight['date'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="sm:col-span-2 bg-gray-50/50 dark:bg-gray-900/50 p-10 rounded-[2rem] border-2 border-dashed border-gray-200 dark:border-gray-700 text-center text-gray-400 text-sm font-bold italic">
                                Zatím nemáme dostatek dat pro generování postřehů.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Rankings --}}
                <div class="space-y-6">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center px-1">
                        <i class="fa-light fa-ranking-star mr-3 text-primary-500"></i> Týmový ranking
                    </h3>
                    <div class="bg-gradient-to-br from-primary-600 to-primary-800 p-8 rounded-[2rem] text-white shadow-2xl shadow-primary-500/30 relative overflow-hidden group hover:scale-[1.02] transition-all">
                        <div class="absolute -right-8 -bottom-8 opacity-10 group-hover:rotate-12 group-hover:scale-110 transition-all">
                            <i class="fa-light fa-medal text-[12rem]"></i>
                        </div>
                        <div class="space-y-6 relative z-10">
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
                                <div class="flex justify-between items-end border-b border-white/10 pb-3 hover:border-white/30 transition-colors">
                                    <div class="text-[10px] font-black uppercase tracking-widest opacity-70">{{ $label }}</div>
                                    <div class="font-black text-right">
                                        <span class="text-3xl drop-shadow-md">#{{ $rank['rank'] }}</span>
                                        <span class="text-xs opacity-50 block mt-0.5">z {{ $rank['total'] }} hráčů</span>
                                    </div>
                                </div>
                            @endforeach
                            @if(empty($rankings))
                                <div class="text-center py-6 opacity-80 italic text-sm font-bold">
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
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">#</th>
                                <th wire:click="sortBy('date')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest cursor-pointer hover:text-primary-500 transition-colors">
                                    Zápas
                                    @if($sortField === 'date')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('pts')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                    Body
                                    @if($sortField === 'pts')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('fg2_made')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                    2B
                                    @if($sortField === 'fg2_made')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('fg3_made')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                    3B
                                    @if($sortField === 'fg3_made')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('ft_made')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                    TH
                                    @if($sortField === 'ft_made')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('fouls')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                    F
                                    @if($sortField === 'fouls')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('efficiency')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                    EF
                                    @if($sortField === 'efficiency')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($perGameSeries as $m)
                            @php
                                $res = \App\Support\MatchResultHelper::getResult($m['is_home'], $m['score_home'] ?? null, $m['score_away'] ?? null);
                                $isWin = $res['isWin'];
                                $isDraw = $res['isDraw'];
                                $isLoss = $res['isLoss'];
                            @endphp
                            <tr
                                wire:key="row-{{ $loop->index }}"
                                @class([
                                    'hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-all border-l-[6px] relative group',
                                    'border-emerald-500 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10' => $isWin,
                                    'border-rose-400/30' => $isLoss,
                                    'border-gray-300' => $isDraw,
                                    'border-transparent' => !$isWin && !$isLoss && !$isDraw,
                                ])
                            >
                                <td class="px-6 py-5 text-center text-[10px] font-black text-gray-300">
                                    {{ $loop->iteration }}.
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div @class([
                                            'flex flex-col items-center justify-center w-10 h-10 rounded-xl shadow-sm border transition-transform group-hover:scale-110',
                                            'bg-emerald-50 border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800' => $isWin,
                                            'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700' => !$isWin,
                                        ])>
                                            <i @class([
                                                'fa-light text-base',
                                                'fa-house-chimney text-blue-500' => $m['is_home'],
                                                'fa-bus-simple text-orange-500' => !$m['is_home'],
                                            ])></i>
                                        </div>
                                        <div>
                                            <div @class([
                                                'text-sm font-black transition-colors',
                                                'text-emerald-700 dark:text-emerald-400' => $isWin,
                                                'text-gray-800 dark:text-gray-200' => !$isWin,
                                            ])>
                                                {{ $m['opponent'] }}
                                                @if($isWin)
                                                    <i class="fa-light fa-trophy-star ml-1 text-yellow-500 text-xs animate-bounce-subtle"></i>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-gray-400 flex items-center gap-2">
                                                <span class="font-bold uppercase tracking-wider">{{ \Carbon\Carbon::parse($m['date'])->format('d. m. Y') }}</span>
                                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                <span @class(['font-black tracking-widest', 'text-blue-500/80' => $m['is_home'], 'text-orange-500/80' => !$m['is_home']])>
                                                    {{ $m['is_home'] ? 'DOMA' : 'VENKU' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div @class([
                                        'text-2xl font-black transition-transform group-hover:scale-110',
                                        'text-emerald-600 dark:text-emerald-400 drop-shadow-sm' => $isWin,
                                        'text-primary-600' => !$isWin,
                                    ])>
                                        {{ $m['values']['pts'] ?? 0 }}
                                    </div>
                                    <div class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">Body</div>
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
                <div class="text-gray-400 font-medium italic">Zatím nemáme nahrané žádné tvé osobní statistiky pro sezónu {{ $activeSeasonName }} a tým {{ $activeTeamName }}.</div>
            </div>
        @endif

        {{-- EXTERNAL STATS --}}
        @if(!empty($externalStats) || !empty($externalMatches))
            <div class="mt-16">
                @include('member.statistics.partials.external-stats-view')
            </div>
        @endif
    @elseif($view === 'team')
        {{-- TEAM VIEW --}}
        @if($teamSummary)
            {{-- Team Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Record --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.02] transition-all">
                    <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:opacity-[0.08] transition-all group-hover:rotate-12 group-hover:scale-125">
                        <i class="fa-light fa-trophy-star text-[12rem]"></i>
                    </div>
                    <div class="relative z-10 text-center md:text-left">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Sezónní bilance</h3>
                        <div class="flex items-end justify-center md:justify-start gap-2">
                            <div class="text-6xl font-black text-green-500 drop-shadow-sm">{{ $teamSummary['wins'] ?? 0 }}</div>
                            <div class="text-2xl font-black text-gray-200 mb-1">/</div>
                            <div class="text-4xl font-black text-red-400 mb-1">{{ $teamSummary['losses'] ?? 0 }}</div>
                        </div>
                        <div class="mt-4 inline-flex items-center px-3 py-1 bg-gray-50 dark:bg-gray-900 rounded-full text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <i class="fa-light fa-basketball mr-2"></i> Celkem {{ $teamSummary['gp'] ?? 0 }} zápasů
                        </div>
                    </div>
                </div>

                {{-- Points --}}
                <div class="bg-primary-600 p-8 rounded-3xl shadow-xl shadow-primary-500/20 text-center relative overflow-hidden group hover:scale-[1.02] transition-all">
                    <div class="absolute -left-4 -top-4 opacity-10 group-hover:scale-110 transition-transform">
                        <i class="fa-light fa-fire-flame-curved text-8xl text-white"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-white/60 uppercase tracking-[0.2em] mb-4 relative z-10">Průměrná ofenzíva</h3>
                    <div class="text-7xl font-black text-white drop-shadow-md relative z-10">
                        {{ $teamSummary['pts_avg'] ?? 0 }}
                    </div>
                    <div class="mt-6 flex justify-center gap-4 text-[10px] font-black uppercase text-white/60 relative z-10">
                        <span class="bg-white/10 px-3 py-1 rounded-lg">Dáno: {{ $teamSummary['pts_for'] }}</span>
                        <span class="bg-white/10 px-3 py-1 rounded-lg">Dostáno: {{ $teamSummary['pts_against'] }}</span>
                    </div>
                </div>

                {{-- Form --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 text-center flex flex-col justify-center items-center group hover:scale-[1.02] transition-all">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6">Aktuální forma</h3>
                    <div class="flex justify-center gap-3">
                        @foreach($recentForm as $f)
                            <div
                                wire:key="form-{{ $loop->index }}"
                                class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm shadow-md transition-transform hover:scale-110 cursor-help"
                                @class([
                                    'bg-gradient-to-br from-green-400 to-green-600 text-white ring-4 ring-green-500/10' => $f['result'] === 'W',
                                    'bg-gradient-to-br from-red-400 to-red-600 text-white ring-4 ring-red-500/10' => $f['result'] === 'L'
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
                    <div class="mt-6 text-[10px] font-black text-gray-300 uppercase tracking-widest">Posledních {{ count($recentForm) }} zápasů</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Player Statistics --}}
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 overflow-hidden group">
                    <div class="p-8 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50">
                        <h3 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-[0.2em]">Hráčské statistiky <span class="text-primary-500">(Sezóna)</span></h3>
                        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-500">
                            <i class="fa-light fa-users-viewfinder"></i>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-gray-900/30">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">#</th>
                                    <th wire:click="sortBy('name')" class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest cursor-pointer hover:text-primary-500 transition-colors">
                                        Hráč
                                        @if($sortField === 'name')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('gp')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        Z
                                        @if($sortField === 'gp')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('pts_total')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        B celkem
                                        @if($sortField === 'pts_total')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('ppg')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        B/Z
                                        @if($sortField === 'ppg')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('minutes_avg')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        Min Ø
                                        @if($sortField === 'minutes_avg')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('fg2_pct')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        2B%
                                        @if($sortField === 'fg2_pct')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('fg3_pct')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        3B%
                                        @if($sortField === 'fg3_pct')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('ft_pct')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors">
                                        TH%
                                        @if($sortField === 'ft_pct')
                                            <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700 text-sm">
                                @foreach($topScorers as $scorer)
                                <tr wire:key="scorer-{{ $loop->index }}" class="hover:bg-primary-50/30 dark:hover:bg-primary-900/10 transition-colors group/row">
                                    <td class="px-6 py-4 text-center text-[10px] font-black text-gray-300">
                                        {{ $loop->iteration }}.
                                    </td>
                                    <td class="px-6 py-4 font-black text-gray-800 dark:text-gray-200 group-hover/row:text-primary-600 transition-colors">
                                        @if(isset($scorer['user_id']))
                                            <button wire:click="showPlayerStats({{ $scorer['user_id'] }})" class="hover:underline text-left">
                                                {{ $scorer['name'] }}
                                            </button>
                                        @else
                                            {{ $scorer['name'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['gp'] }}</td>
                                    <td class="px-4 py-4 text-center font-black text-lg">{{ $scorer['pts_total'] }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="bg-primary-600 text-white px-3 py-1 rounded-lg text-xs font-black shadow-sm group-hover/row:scale-110 transition-transform inline-block">
                                            {{ $scorer['ppg'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['minutes_avg'] }}</td>
                                    <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['fg2_pct'] }}%</td>
                                    <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['fg3_pct'] }}%</td>
                                    <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['ft_pct'] }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Team Chart --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col h-[480px] group" wire:ignore>
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Bodový vývoj týmu <span class="text-primary-500">(vs Soupeři)</span></h3>
                        <i class="fa-light fa-chart-area text-primary-500 opacity-30 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                    <div id="team-evolution-chart" class="flex-1"></div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-800 p-20 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center space-y-4">
                <i class="fa-light fa-users-slash text-6xl text-gray-100 dark:text-gray-700"></i>
                <div class="text-gray-400 font-medium italic">Tým {{ $activeTeamName }} v sezóně {{ $activeSeasonName }} zatím nemá synchronizované týmové statistiky.</div>
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
                            <th wire:click="sortBy('date')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest cursor-pointer hover:text-primary-500 transition-colors">
                                Datum
                                @if($sortField === 'date' || $sortField === 'scheduled_at')
                                    <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Soupeř</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Místo</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Výsledek</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Skóre</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @forelse($matches as $m)
                        @php
                            $isOdehrano = in_array($m['status'], ['finished', 'completed', 'played']);
                            $res = \App\Support\MatchResultHelper::getResult($m['is_home'], $m['score_home'] ?? null, $m['score_away'] ?? null);
                            $hasScore = isset($m['score_home']) && isset($m['score_away']);
                            $isWin = $res['isWin'] && $isOdehrano;
                            $isDraw = $res['isDraw'] && $isOdehrano;
                            $isLoss = $res['isLoss'] && $isOdehrano;

                            $scheduledAt = $m['scheduled_at'] ? \Carbon\Carbon::parse($m['scheduled_at'])->timezone(config('app.timezone')) : null;
                            $isPast = $scheduledAt ? $scheduledAt->isPast() : ($m['season_id'] < 3);
                            $isActionTookPlace = ($isOdehrano && !$hasScore) || (!$isOdehrano && $isPast);

                            $hasDetail = !empty($m['metadata']['external_id']) || !empty($m['metadata']['season_external_match_id']) || $isOdehrano;

                            $typeIcon = match($m['match_type'] ?? '') {
                                'mistrovske' => 'fa-trophy text-amber-500',
                                'poharove' => 'fa-award text-indigo-500',
                                'pratelske' => 'fa-handshake text-green-500',
                                'TUR' => 'fa-globe text-blue-500',
                                default => 'fa-basketball text-gray-400'
                            };

                            $typeLabel = match($m['match_type'] ?? '') {
                                'mistrovske' => 'Mistrovské',
                                'poharove' => 'Pohárové',
                                'pratelske' => 'Přátelské',
                                'TUR' => 'Turnaj',
                                default => 'Ostatní'
                            };
                        @endphp
                        <tr
                            wire:key="match-{{ $m['id'] ?? $loop->index }}"
                            @class([
                                'hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-all border-l-[6px] group relative',
                                'border-emerald-500 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10' => $isWin,
                                'border-rose-400/30' => $isLoss,
                                'border-gray-300' => $isDraw,
                                'border-blue-400/30 bg-blue-50/10 dark:bg-blue-900/5' => $isActionTookPlace,
                                'border-transparent' => !$isWin && !$isLoss && !$isDraw && !$isActionTookPlace,
                            ])
                        >
                            <td class="px-6 py-5">
                                <div class="text-sm font-black text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    @if(!empty($m['metadata']['external_id']))
                                        <a href="https://cz.basketball/zapas/{{ $m['metadata']['external_id'] }}" target="_blank" class="text-brand-500 hover:text-brand-600 transition-colors" title="{{ __('matches.external_detail') }}">
                                            <i class="fa-light fa-basketball text-xs"></i>
                                        </a>
                                    @elseif(!empty($m['metadata']['season_external_match_id']))
                                        <i class="fa-light fa-cloud-arrow-down text-blue-400 text-xs" title="Synchronizováno z externího zdroje"></i>
                                    @endif
                                    {{ $scheduledAt ? $scheduledAt->format('d. m. Y') : '-' }}
                                </div>
                                <div class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">
                                    {{ $scheduledAt ? $scheduledAt->format('H:i') : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div @class([
                                        'w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center shadow-sm border transition-transform group-hover:scale-110',
                                        'border-emerald-100 dark:border-emerald-900' => $isWin,
                                        'border-gray-100 dark:border-gray-700' => !$isWin,
                                    ])>
                                        <i class="fa-light {{ $typeIcon }} text-base"></i>
                                    </div>
                                    <div>
                                        <div @class([
                                            'text-sm font-black transition-colors',
                                            'text-emerald-700 dark:text-emerald-400' => $isWin,
                                            'text-gray-800 dark:text-gray-200' => !$isWin,
                                        ])>
                                            {{ $m['opponent']['name'] ?? 'Neznámý soupeř' }}
                                            @if($isWin)
                                                <i class="fa-light fa-trophy-star ml-1 text-yellow-500 text-xs animate-bounce-subtle"></i>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-gray-400 uppercase font-black tracking-widest opacity-70">
                                            {{ $typeLabel }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="flex flex-col items-center gap-1.5 transition-transform group-hover:scale-105">
                                    <i @class([
                                        'fa-light text-xl',
                                        'fa-house-chimney text-blue-500 drop-shadow-sm' => $m['is_home'],
                                        'fa-bus-simple text-orange-500 drop-shadow-sm' => !$m['is_home'],
                                    ])></i>
                                    <span @class([
                                        'text-[9px] font-black uppercase tracking-[0.2em]',
                                        'text-blue-600/80 dark:text-blue-400/80' => $m['is_home'],
                                        'text-orange-600/80 dark:text-orange-400/80' => !$m['is_home'],
                                    ])>
                                        {{ $m['is_home'] ? __('matches.home_upper') : __('matches.away_upper') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($isWin || $isLoss || $isDraw)
                                    <div @class([
                                        'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase shadow-sm border transition-all group-hover:scale-105',
                                        'bg-green-500 text-white border-green-400 ring-4 ring-green-500/10' => $isWin,
                                        'bg-gray-100 text-gray-500 border-gray-200' => $isDraw,
                                        'bg-red-50 text-red-500 border-red-100' => $isLoss
                                    ])>
                                        @if($isWin) <i class="fa-light fa-trophy-star text-xs"></i>
                                        @elseif($isDraw) <i class="fa-light fa-equals text-xs"></i>
                                        @else <i class="fa-light fa-circle-xmark text-xs"></i>
                                        @endif
                                        {{ $isWin ? __('matches.result_v') : ($isDraw ? __('matches.result_r') : __('matches.result_p')) }}
                                    </div>
                                @elseif($isActionTookPlace)
                                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-blue-500 border border-blue-100 text-[10px] font-black uppercase shadow-sm">
                                        <i class="fa-light fa-calendar-check"></i>
                                        {{ __('matches.action_took_place') }}
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gray-50 text-gray-400 border border-gray-100 text-[10px] font-black uppercase">
                                        <i class="fa-light fa-calendar-clock"></i>
                                        {{ __('matches.planned') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($hasScore)
                                    <div @class([
                                        'text-2xl font-black transition-transform group-hover:scale-110',
                                        'text-green-600 dark:text-green-400 drop-shadow-sm' => $isWin,
                                        'text-red-500/80 dark:text-red-400/80' => $isLoss,
                                        'text-gray-800 dark:text-white' => !$isWin && !$isLoss,
                                    ])>
                                        {{ $m['score_home'] }}<span class="text-gray-300 mx-1">:</span>{{ $m['score_away'] }}
                                    </div>
                                @else
                                    <div class="text-lg text-gray-200 font-black">- : -</div>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if($hasDetail && \Illuminate\Support\Facades\Route::has('member.statistics.matches.show'))
                                    <a
                                        href="{{ route('member.statistics.matches.show', $m['id']) }}"
                                        @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-primary-500 hover:text-white transition-all shadow-sm group/btn font-bold text-[10px] uppercase tracking-wider"
                                        title="Detail zápasu"
                                    >
                                        <span>{{ __('matches.match_detail') }}</span>
                                        <i class="fa-light fa-chevron-right transition-transform group-hover/btn:translate-x-0.5"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                Žádné zápasy pro tým {{ $activeTeamName }} v sezóně {{ $activeSeasonName }} nebyly nalezeny.
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
            console.log('initPersonalCharts starting');
            try {
                const seriesData = $wire.perGameSeries;
                if (!seriesData || seriesData.length === 0) {
                    console.log('initPersonalCharts - no series data');
                    return;
                }

                const chartEl1 = document.querySelector("#points-evolution-chart");
                const chartEl2 = document.querySelector("#comparison-chart");

                if (!chartEl1 || !chartEl2) {
                    console.warn('initPersonalCharts - chart elements not found in DOM');
                    return;
                }

                const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
                const points = seriesData.map(m => (m.values ? m.values.pts : 0) || 0);
                const opponents = seriesData.map(m => m.opponent);

                // Points Evolution
                new ApexCharts(chartEl1, {
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
                const teamAvg = $wire.teamAverages ? ($wire.teamAverages.pts_avg ?? 0) : 0;
                new ApexCharts(chartEl2, {
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
                console.log('initPersonalCharts finished');
            } catch (e) {
                console.error('initPersonalCharts error:', e);
            }
        };

        const initTeamCharts = () => {
            console.log('initTeamCharts starting');
            try {
                const seriesData = $wire.pointsSeries;
                if (!seriesData || seriesData.length === 0) {
                    console.log('initTeamCharts - no series data');
                    return;
                }

                const chartEl = document.querySelector("#team-evolution-chart");
                if (!chartEl) {
                    console.warn('initTeamCharts - chart element not found in DOM');
                    return;
                }

                const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
                const ptsFor = seriesData.map(m => m.pts_for || 0);
                const ptsAgainst = seriesData.map(m => m.pts_against || 0);

                new ApexCharts(chartEl, {
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
                console.log('initTeamCharts finished');
            } catch (e) {
                console.error('initTeamCharts error:', e);
            }
        };

        // Initialize based on initial view
        $wire.view === 'personal' ? initPersonalCharts() : ($wire.view === 'team' ? initTeamCharts() : null);

        // Re-initialize on Livewire updates
        $wire.on('statsLoaded', () => {
            console.log('statsLoaded event received');

            // Force hide global loader
            window.dispatchEvent(new CustomEvent('loading-stop'));

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
    </script>
    @endscript
</div>
