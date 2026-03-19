<div class="space-y-8 relative" wire:init="init" x-data="{ view: @entangle('view') }" x-init="
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
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-[2rem] shadow-xl shadow-gray-200/40 dark:shadow-none border border-white/20 dark:border-gray-700 flex flex-col xl:flex-row justify-between items-center gap-4 sticky top-4 z-30">
        {{-- View Switcher & Toggle --}}
        <div class="flex flex-col md:flex-row items-center gap-4 w-full xl:w-auto">
            <div class="flex p-1.5 bg-gray-100/50 dark:bg-gray-900/50 rounded-2xl w-full md:w-auto border border-gray-200/30 dark:border-gray-800">
                <a
                    href="{{ route('member.statistics.me') }}"
                    @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                    class="flex-1 md:flex-none px-6 py-2.5 rounded-xl text-xs font-black transition-all text-center uppercase tracking-wider"
                    :class="view === 'personal' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >
                    <i class="fa-light fa-user-chart mr-2"></i> Osobní
                </a>
                <button
                    wire:click="$set('view', 'career')"
                    @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                    class="flex-1 md:flex-none px-6 py-2.5 rounded-xl text-xs font-black transition-all text-center uppercase tracking-wider"
                    :class="view === 'career' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >
                    <i class="fa-light fa-sparkles mr-2"></i> Kariéra
                </button>
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

            {{-- Toggle for Avg/Total --}}
            <div class="flex p-1 bg-gray-100/50 dark:bg-gray-900/50 rounded-2xl border border-gray-200/30 dark:border-gray-800 backdrop-blur-sm shadow-inner shrink-0 w-full md:w-auto">
                <button
                    wire:click="$set('statsView', 'avg')"
                    @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                    class="flex-1 md:flex-none px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 {{ $statsView === 'avg' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200' }}"
                >
                    <i class="fa-light fa-chart-line-up"></i> Průměry
                </button>
                <button
                    wire:click="$set('statsView', 'total')"
                    @click="window.dispatchEvent(new CustomEvent('loading-start'))"
                    class="flex-1 md:flex-none px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 {{ $statsView === 'total' ? 'bg-white dark:bg-gray-800 shadow-md text-primary-600 scale-[1.02]' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200' }}"
                >
                    <i class="fa-light fa-sigma"></i> Celkem
                </button>
            </div>
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
                    <option value="all">Všechny týmy</option>
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

    @if(!$readyToLoad)
        <div class="flex flex-col items-center justify-center p-32 space-y-6 bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm rounded-[4rem] border border-dashed border-gray-200 dark:border-gray-700 animate-pulse">
             <div class="relative">
                 <i class="fa-light fa-basketball fa-spin text-7xl text-primary-500 opacity-20" style="animation-duration: 3s;"></i>
                 <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fa-light fa-chart-mixed text-4xl text-primary-600 animate-bounce-subtle"></i>
                 </div>
             </div>
             <div class="flex flex-col items-center gap-2">
                 <div class="text-gray-400 font-black uppercase tracking-[0.3em] text-[11px]">Sestavujeme tvé statistiky</div>
                 <div class="text-[9px] text-gray-400/60 font-bold uppercase tracking-widest italic">Chvilku strpení, stahujeme nejnovější data...</div>
             </div>
        </div>
    @elseif($view === 'personal')
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
        @if(($summary || $isActiveInSelectedTeam) && $view === 'personal')
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-6">
                @php
                    $gpValue = $summary['gp'] ?? 0;
                    if ($teamMatchesCount > 0) {
                        $gpValue = $gpValue . ' / ' . $teamMatchesCount;
                    }

                    $isAvg = $statsView === 'avg';

                    $cards = [
                        [
                            'label' => 'Zápasy',
                            'value' => $gpValue,
                            'icon' => 'fa-basketball',
                            'color' => 'primary',
                            'tooltip' => 'Poměr odehraných zápasů k celkovému počtu odehraných zápasů týmu v sezóně.',
                            'percent' => $teamMatchesCount > 0 ? ($summary['gp'] ?? 0) / $teamMatchesCount * 100 : 0
                        ],
                        [
                            'label' => $isAvg ? 'PPG (Body Ø)' : 'Body Celkem',
                            'value' => $isAvg ? ($summary['ppg'] ?? 0) : ($summary['pts_total'] ?? 0),
                            'icon' => 'fa-bullseye',
                            'color' => 'blue',
                            'tooltip' => $isAvg ? 'Průměrný počet bodů na jeden zápas (Points Per Game).' : 'Celkový počet bodů vstřelených v této sezóně.',
                            'percent' => $isAvg ? (min(30, ($summary['ppg'] ?? 0)) / 30 * 100) : (min(300, ($summary['pts_total'] ?? 0)) / 300 * 100)
                        ],
                    ];

                    if (isset($summary['minutes_avg']) && $summary['minutes_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'Minuty Ø' : 'Minuty Celkem',
                            'value' => $isAvg ? $summary['minutes_avg'] : ($summary['minutes_total'] ?? 0),
                            'icon' => 'fa-clock',
                            'color' => 'emerald',
                            'tooltip' => $isAvg ? 'Průměrný čas strávený na hřišti v jednom zápase.' : 'Celkový počet minut odehraných v této sezóně.',
                            'percent' => $isAvg ? (min(40, ($summary['minutes_avg'] ?? 0)) / 40 * 100) : (min(600, ($summary['minutes_total'] ?? 0)) / 600 * 100)
                        ];
                    }

                    if (isset($summary['efficiency_avg']) && $summary['efficiency_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'VAL Ø' : 'VAL Celkem',
                            'value' => $isAvg ? $summary['efficiency_avg'] : ($summary['efficiency_total'] ?? 0),
                            'icon' => 'fa-bolt',
                            'color' => 'orange',
                            'tooltip' => $isAvg ? 'Průměrná validita (efektivita) na zápas. Souhrnný index výkonu.' : 'Celková nasbíraná validita za celou sezónu.',
                            'percent' => $isAvg ? (min(30, ($summary['efficiency_avg'] ?? 0)) / 30 * 100) : (min(400, ($summary['efficiency_total'] ?? 0)) / 400 * 100)
                        ];
                    }

                    if (isset($summary['rebounds_avg']) && $summary['rebounds_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'Doskoky Ø' : 'Doskoky Celkem',
                            'value' => $isAvg ? $summary['rebounds_avg'] : ($summary['rebounds_total'] ?? 0),
                            'icon' => 'fa-hand-back-point-up',
                            'color' => 'violet',
                            'tooltip' => $isAvg ? 'Průměrný počet doskoků (útočné + obranné) na zápas.' : 'Celkový počet doskoků v této sezóně.',
                            'percent' => $isAvg ? (min(15, ($summary['rebounds_avg'] ?? 0)) / 15 * 100) : (min(200, ($summary['rebounds_total'] ?? 0)) / 200 * 100)
                        ];
                    }

                    if (isset($summary['assists_avg']) && $summary['assists_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'Asistence Ø' : 'Asistence Celkem',
                            'value' => $isAvg ? $summary['assists_avg'] : ($summary['assists_total'] ?? 0),
                            'icon' => 'fa-hands-passing',
                            'color' => 'indigo',
                            'tooltip' => $isAvg ? 'Průměrný počet přihrávek na koš na zápas.' : 'Celkový počet asistencí v této sezóně.',
                            'percent' => $isAvg ? (min(10, ($summary['assists_avg'] ?? 0)) / 10 * 100) : (min(150, ($summary['assists_total'] ?? 0)) / 150 * 100)
                        ];
                    }

                    if (isset($summary['steals_avg']) && $summary['steals_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'Zisky Ø' : 'Zisky Celkem',
                            'value' => $isAvg ? $summary['steals_avg'] : ($summary['steals_total'] ?? 0),
                            'icon' => 'fa-hand-sparkles',
                            'color' => 'emerald',
                            'tooltip' => $isAvg ? 'Průměrný počet získaných míčů na zápas.' : 'Celkový počet získaných míčů v této sezóně.',
                            'percent' => $isAvg ? (min(5, ($summary['steals_avg'] ?? 0)) / 5 * 100) : (min(80, ($summary['steals_total'] ?? 0)) / 80 * 100)
                        ];
                    }

                    if (isset($summary['blocks_avg']) && $summary['blocks_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'Bloky Ø' : 'Bloky Celkem',
                            'value' => $isAvg ? $summary['blocks_avg'] : ($summary['blocks_total'] ?? 0),
                            'icon' => 'fa-shield-halved',
                            'color' => 'blue',
                            'tooltip' => $isAvg ? 'Průměrný počet zblokovaných střel soupeře na zápas.' : 'Celkový počet bloků v této sezóně.',
                            'percent' => $isAvg ? (min(3, ($summary['blocks_avg'] ?? 0)) / 3 * 100) : (min(50, ($summary['blocks_total'] ?? 0)) / 50 * 100)
                        ];
                    }

                    // 2B
                    $fg2_pct = $summary['fg2_pct'] ?? null;
                    $fg2_total = $summary['fg2_total'] ?? 0;
                    $fg2_att_total = $summary['fg2_att_total'] ?? 0;
                    $fg2_avg = $summary['fg2_avg'] ?? 0;

                    if ($fg2_att_total > 0) {
                        $cards[] = [
                            'label' => '2B Úspěšnost',
                            'value' => $isAvg ? $fg2_pct . '%' : "{$fg2_total} / {$fg2_att_total}",
                            'icon' => 'fa-arrow-progress',
                            'color' => 'orange',
                            'percent' => $fg2_pct,
                            'tooltip' => $isAvg
                                ? "Úspěšnost střelby za 2 body: {$fg2_total} proměněno z {$fg2_att_total} pokusů."
                                : "Celkem {$fg2_total} proměněných / {$fg2_att_total} pokusů ({$fg2_pct}%)."
                        ];
                    } elseif ($fg2_total > 0 || ($isAvg && $fg2_avg > 0)) {
                        $cards[] = [
                            'label' => $isAvg ? '2B Ø' : '2B Celkem',
                            'value' => $isAvg ? $fg2_avg : $fg2_total,
                            'icon' => 'fa-arrow-progress',
                            'color' => 'orange',
                            'tooltip' => $isAvg ? 'Průměrný počet košů za 2 body na zápas.' : 'Celkový počet proměněných střel za 2 body.',
                            'percent' => $isAvg ? (min(10, $fg2_avg) / 10 * 100) : (min(150, $fg2_total) / 150 * 100)
                        ];
                    }

                    // 3B
                    $fg3_pct = $summary['fg3_pct'] ?? null;
                    $fg3_total = $summary['fg3_total'] ?? 0;
                    $fg3_att_total = $summary['fg3_att_total'] ?? 0;
                    $fg3_avg = $summary['fg3_avg'] ?? 0;

                    if ($fg3_att_total > 0) {
                        $cards[] = [
                            'label' => '3B Úspěšnost',
                            'value' => $isAvg ? $fg3_pct . '%' : "{$fg3_total} / {$fg3_att_total}",
                            'icon' => 'fa-arrow-up-right-dots',
                            'color' => 'violet',
                            'percent' => $fg3_pct,
                            'tooltip' => $isAvg
                                ? "Úspěšnost střelby za 3 body: {$fg3_total} proměněno z {$fg3_att_total} pokusů."
                                : "Celkem {$fg3_total} proměněných / {$fg3_att_total} pokusů ({$fg3_pct}%)."
                        ];
                    } elseif ($fg3_total > 0 || ($isAvg && $fg3_avg > 0)) {
                        $cards[] = [
                            'label' => $isAvg ? '3B Ø' : '3B Celkem',
                            'value' => $isAvg ? $fg3_avg : $fg3_total,
                            'icon' => 'fa-arrow-up-right-dots',
                            'color' => 'violet',
                            'tooltip' => $isAvg ? 'Průměrný počet košů za 3 body na zápas.' : 'Celkový počet proměněných střel za 3 body.',
                            'percent' => $isAvg ? (min(5, $fg3_avg) / 5 * 100) : (min(80, $fg3_total) / 80 * 100)
                        ];
                    }

                    // TH
                    $ft_pct = $summary['ft_pct'] ?? null;
                    $ft_total = $summary['ft_total'] ?? 0;
                    $ft_att_total = $summary['ft_att_total'] ?? 0;
                    $ft_avg = $summary['ft_avg'] ?? 0;

                    if ($ft_att_total > 0) {
                        $cards[] = [
                            'label' => 'TH Úspěšnost',
                            'value' => $isAvg ? $ft_pct . '%' : "{$ft_total} / {$ft_att_total}",
                            'subValue' => null,
                            'icon' => 'fa-bullseye-arrow',
                            'color' => 'pink',
                            'percent' => $ft_pct,
                            'tooltip' => $isAvg
                                ? "Úspěšnost trestných hodů: {$ft_total} proměněno z {$ft_att_total} pokusů."
                                : "Celkem {$ft_total} proměněných / {$ft_att_total} pokusů ({$ft_pct}%)."
                        ];
                    } elseif ($ft_total > 0 || ($isAvg && $ft_avg > 0)) {
                        $cards[] = [
                            'label' => $isAvg ? 'TH Ø' : 'TH Celkem',
                            'value' => $isAvg ? $ft_avg : $ft_total,
                            'icon' => 'fa-bullseye-arrow',
                            'color' => 'pink',
                            'tooltip' => $isAvg ? 'Průměrný počet proměněných trestných hodů na zápas.' : 'Celkový počet proměněných trestných hodů.',
                            'percent' => $isAvg ? (min(5, $ft_avg) / 5 * 100) : (min(80, $ft_total) / 80 * 100)
                        ];
                    }

                    if (isset($summary['fouls_avg']) && $summary['fouls_avg'] > 0) {
                        $cards[] = [
                            'label' => $isAvg ? 'Fauly Ø' : 'Fauly Celkem',
                            'value' => $isAvg ? $summary['fouls_avg'] : ($summary['fouls_total'] ?? 0),
                            'icon' => 'fa-hand-back-fist',
                            'color' => 'red',
                            'tooltip' => $isAvg ? 'Průměrný počet osobních chyb na zápas.' : 'Celkový počet spáchaných faulů v této sezóně.',
                            'percent' => $isAvg ? (min(5, ($summary['fouls_avg'] ?? 0)) / 5 * 100) : (min(80, ($summary['fouls_total'] ?? 0)) / 80 * 100)
                        ];
                    }

                    // Vždy chceme aspoň 6 karet na desktopu, tak jich pár přidáme, pokud chybí
                    if (count($cards) < 6) {
                         // Přidáme fauly nebo zisky i s nulou, pokud je jich málo a máme odehrané zápasy
                         if (($summary['gp'] ?? 0) > 0 && !collect($cards)->contains('label', 'Fauly Ø') && !collect($cards)->contains('label', 'Fauly Celkem')) {
                             $cards[] = [
                                'label' => $isAvg ? 'Fauly Ø' : 'Fauly Celkem',
                                'value' => $isAvg ? ($summary['fouls_avg'] ?? 0) : ($summary['fouls_total'] ?? 0),
                                'icon' => 'fa-hand-back-fist',
                                'color' => 'red',
                                'tooltip' => 'Osobní chyby.',
                                'percent' => $isAvg ? (min(5, ($summary['fouls_avg'] ?? 0)) / 5 * 100) : (min(80, ($summary['fouls_total'] ?? 0)) / 80 * 100)
                             ];
                         }
                    }

                    $colorClasses = [
                        'primary' => 'text-red-600 dark:text-red-400',
                        'blue' => 'text-blue-600 dark:text-blue-400',
                        'indigo' => 'text-indigo-600 dark:text-indigo-400',
                        'emerald' => 'text-emerald-600 dark:text-emerald-400',
                        'orange' => 'text-orange-600 dark:text-orange-400',
                        'violet' => 'text-violet-600 dark:text-violet-400',
                        'pink' => 'text-pink-600 dark:text-pink-400',
                        'red' => 'text-red-600 dark:text-red-400',
                    ];

                    $bgSoftClasses = [
                        'primary' => 'bg-red-50 dark:bg-red-900/20',
                        'blue' => 'bg-blue-50 dark:bg-blue-900/20',
                        'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20',
                        'emerald' => 'bg-emerald-50 dark:bg-emerald-900/20',
                        'orange' => 'bg-orange-50 dark:bg-orange-900/20',
                        'violet' => 'bg-violet-50 dark:bg-violet-900/20',
                        'pink' => 'bg-pink-50 dark:bg-pink-900/20',
                        'red' => 'bg-red-50 dark:bg-red-900/20',
                    ];

                    $barBgClasses = [
                        'primary' => 'bg-red-600',
                        'blue' => 'bg-blue-600',
                        'indigo' => 'bg-indigo-600',
                        'emerald' => 'bg-emerald-600',
                        'orange' => 'bg-orange-600',
                        'violet' => 'bg-violet-600',
                        'pink' => 'bg-pink-600',
                        'red' => 'bg-red-600',
                    ];

                    // Safelist pro Tailwind v4 (aby viděl třídy použité v dynamických polích)
                    // bg-red-600 bg-blue-600 bg-indigo-600 bg-emerald-600 bg-orange-600 bg-violet-600 bg-pink-600
                    // text-red-600 text-blue-600 text-indigo-600 text-emerald-600 text-orange-600 text-violet-600 text-pink-600
                    // bg-red-50 bg-blue-50 bg-indigo-50 bg-emerald-50 bg-orange-50 bg-violet-50 bg-pink-50 bg-amber-50 bg-sky-50 bg-rose-50
                    // bg-red-500 bg-blue-500 bg-indigo-500 bg-emerald-500 bg-orange-500 bg-violet-500 bg-pink-500 bg-amber-500 bg-sky-500 bg-rose-500
                    // text-red-600 text-blue-600 text-indigo-600 text-emerald-600 text-orange-600 text-violet-600 text-pink-600 text-amber-600 text-sky-600 text-rose-600
                    // text-red-400 text-blue-400 text-indigo-400 text-emerald-400 text-orange-400 text-violet-400 text-pink-400 text-amber-400 text-sky-400 text-rose-400
                    // text-red-500 text-blue-500 text-indigo-500 text-emerald-500 text-orange-500 text-violet-500 text-pink-500 text-amber-500 text-sky-500 text-rose-500
                @endphp
                @foreach($cards as $card)
                    <div wire:key="card-{{ $loop->index }}" class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col relative overflow-hidden group hover:scale-[1.03] transition-all duration-300">
                        {{-- Background Icon decoration --}}
                        <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.08] transition-all duration-500 {{ $colorClasses[$card['color']] ?? 'text-gray-400' }} group-hover:rotate-12 group-hover:scale-110">
                            <i class="fa-light {{ $card['icon'] }} text-7xl"></i>
                        </div>

                        {{-- Header with Icon and Label --}}
                        <div class="flex justify-between items-center mb-3 relative z-10">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl {{ $bgSoftClasses[$card['color']] ?? 'bg-gray-50' }} flex items-center justify-center {{ $colorClasses[$card['color']] ?? 'text-gray-400' }} group-hover:scale-110 transition-transform shadow-sm">
                                    <i class="fa-light {{ $card['icon'] }} text-base"></i>
                                </div>
                                <div class="text-[10px] text-gray-950 dark:text-white font-black uppercase tracking-[0.2em] leading-tight">{{ $card['label'] }}</div>
                            </div>

                            <div class="text-gray-400/30 dark:text-gray-500/30 hover:text-primary-500 transition-colors cursor-help p-1 group/hint" x-tooltip="'{{ $card['tooltip'] }}'">
                                <i class="fa-solid fa-circle-question text-[10px]"></i>
                            </div>
                        </div>

                        <div class="relative z-10">
                            <div @class([
                                'font-black relative z-10',
                                'text-2xl sm:text-3xl' => strlen((string)$card['value']) <= 5,
                                'text-lg sm:text-xl' => strlen((string)$card['value']) > 5,
                                $colorClasses[$card['color']] ?? 'text-gray-900 dark:text-white'
                            ])>
                                {{ $card['value'] }}
                            </div>

                            @if(isset($card['subValue']))
                                <div class="text-[9px] font-bold opacity-50 mt-1 uppercase tracking-widest {{ $colorClasses[$card['color']] ?? 'text-gray-400' }}">
                                    {{ $card['subValue'] }}
                                </div>
                            @endif
                        </div>

                        {{-- Progress Bar if available --}}
                        @if(isset($card['percent']))
                            <div class="mt-4 h-2 w-full bg-gray-200/50 dark:bg-gray-700/50 rounded-full overflow-hidden relative z-10 shadow-inner">
                                <div
                                    class="h-full {{ $barBgClasses[$card['color']] ?? 'bg-primary' }} rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(0,0,0,0.2)]"
                                    style="width: {{ $card['percent'] }}%; background-color: var(--color-{{ $card['color'] }}-600);"
                                ></div>
                            </div>
                        @else
                            <div class="mt-6"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Insights & Rankings --}}
            <div class="space-y-8">
                {{-- Insights --}}
                <div class="space-y-6">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center px-1">
                        <i class="fa-light fa-lightbulb-on mr-3 text-yellow-500"></i> Sezónní postřehy
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($insights as $insight)
                            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex items-start gap-5 group hover:scale-[1.03] transition-all cursor-default">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-900 flex-shrink-0 flex items-center justify-center shadow-inner group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 transition-colors">
                                    @if($insight['type'] === 'best_match') <i class="fa-light fa-star text-2xl text-yellow-500 drop-shadow-sm group-hover:rotate-12 transition-transform"></i>
                                    @elseif($insight['type'] === 'stability') <i class="fa-light fa-wave-pulse text-2xl text-primary-500 drop-shadow-sm group-hover:scale-110 transition-transform"></i>
                                    @elseif($insight['type'] === 'trend_up') <i class="fa-light fa-arrow-trend-up text-2xl text-green-500 drop-shadow-sm group-hover:-translate-y-1 transition-transform"></i>
                                    @elseif($insight['type'] === 'trend_down') <i class="fa-light fa-arrow-trend-down text-2xl text-red-500 drop-shadow-sm group-hover:translate-y-1 transition-transform"></i>
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
                            <div class="sm:col-span-2 lg:col-span-3 bg-gray-50/50 dark:bg-gray-900/50 p-10 rounded-[2rem] border-2 border-dashed border-gray-200 dark:border-gray-700 text-center text-gray-400 text-sm font-bold italic">
                                Zatím nemáme dostatek dat pro generování postřehů.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Rankings --}}
                <div class="space-y-6">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] flex items-center px-1">
                        <i class="fa-light fa-ranking-star mr-3 text-primary-500"></i> Moje pozice v týmu
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                        @foreach($rankings as $key => $rank)
                            @php
                                $info = match($key) {
                                    'pts_total' => [
                                        'label' => 'Body celkem',
                                        'icon' => 'fa-bullseye',
                                        'color' => 'blue',
                                        'bg' => 'bg-blue-600',
                                        'hint' => 'Celkový součet všech nastřílených bodů v sezóně.'
                                    ],
                                    'ppg' => [
                                        'label' => 'PPG (Ø body)',
                                        'icon' => 'fa-chart-scatter',
                                        'color' => 'indigo',
                                        'bg' => 'bg-indigo-600',
                                        'hint' => 'Průměrný počet bodů nastřílených na jeden zápas.'
                                    ],
                                    'gp' => [
                                        'label' => 'Účast na zápasech',
                                        'icon' => 'fa-basketball',
                                        'color' => 'red',
                                        'bg' => 'bg-red-600',
                                        'hint' => 'Počet odehraných zápasů z celkového počtu v sezóně.'
                                    ],
                                    'minutes_avg' => [
                                        'label' => 'Vytížení Ø',
                                        'icon' => 'fa-clock',
                                        'color' => 'emerald',
                                        'bg' => 'bg-emerald-600',
                                        'hint' => 'Průměrný čas strávený na hřišti za jeden zápas.'
                                    ],
                                    'efficiency_avg' => [
                                        'label' => 'EF Ø (Valuace)',
                                        'icon' => 'fa-bolt',
                                        'color' => 'orange',
                                        'bg' => 'bg-orange-600',
                                        'hint' => 'Komplexní index užitečnosti hráče založený na všech akcích.'
                                    ],
                                    'rebounds_avg' => [
                                        'label' => 'Doskoky Ø',
                                        'icon' => 'fa-hand-back-point-up',
                                        'color' => 'violet',
                                        'bg' => 'bg-violet-600',
                                        'hint' => 'Průměrný počet doskočených míčů (útočné + obranné).'
                                    ],
                                    'fg3_total' => [
                                        'label' => 'Trojky Celkem',
                                        'icon' => 'fa-arrow-up-right-dots',
                                        'color' => 'indigo',
                                        'bg' => 'bg-indigo-600',
                                        'hint' => 'Celkový počet proměněných střel za 3 body v sezóně.'
                                    ],
                                    'ft_pct' => [
                                        'label' => 'TH Úspěšnost',
                                        'icon' => 'fa-bullseye-arrow',
                                        'color' => 'pink',
                                        'bg' => 'bg-pink-600',
                                        'hint' => 'Procento proměněných trestných hodů ze všech pokusů.'
                                    ],
                                    'fouls_avg' => [
                                        'label' => 'Fauly Ø',
                                        'icon' => 'fa-user-slash',
                                        'color' => 'red',
                                        'bg' => 'bg-red-600',
                                        'hint' => 'Průměrný počet spáchaných faulů na jeden zápas.'
                                    ],
                                    default => ['label' => $key, 'icon' => 'fa-medal', 'color' => 'gray', 'bg' => 'bg-gray-600', 'hint' => 'Individuální postavení v týmové statistice.']
                                };

                                $percentile = $rank['total'] > 0 ? (1 - ($rank['rank'] - 1) / $rank['total']) * 100 : 0;
                                $isTop3 = $rank['rank'] <= 3;
                                $isFirst = $rank['rank'] === 1;

                                $colorHex = match($info['color']) {
                                    'blue' => 'text-blue-600 dark:text-blue-400',
                                    'indigo' => 'text-indigo-600 dark:text-indigo-400',
                                    'red' => 'text-red-600 dark:text-red-400',
                                    'emerald' => 'text-emerald-600 dark:text-emerald-400',
                                    'orange' => 'text-orange-600 dark:text-orange-400',
                                    'violet' => 'text-violet-600 dark:text-violet-400',
                                    'pink' => 'text-pink-600 dark:text-pink-400',
                                    default => 'text-gray-600 dark:text-gray-400'
                                };

                                $bgSoft = match($info['color']) {
                                    'blue' => 'bg-blue-50 dark:bg-blue-900/20',
                                    'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20',
                                    'red' => 'bg-red-50 dark:bg-red-900/20',
                                    'emerald' => 'bg-emerald-50 dark:bg-emerald-900/20',
                                    'orange' => 'bg-orange-50 dark:bg-orange-900/20',
                                    'violet' => 'bg-violet-50 dark:bg-violet-900/20',
                                    'pink' => 'bg-pink-50 dark:bg-pink-900/20',
                                    default => 'bg-gray-50 dark:bg-gray-900/20'
                                };
                            @endphp
                            <div wire:key="rank-{{ $key }}" class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none flex flex-col relative overflow-hidden group hover:scale-[1.03] transition-all duration-300">
                                {{-- Background Icon decoration --}}
                                <div class="absolute -right-4 -bottom-4 opacity-[0.05] group-hover:opacity-[0.1] group-hover:rotate-12 group-hover:scale-110 transition-all {{ $colorHex }}">
                                    <i class="fa-light {{ $info['icon'] }} text-7xl"></i>
                                </div>

                                {{-- Header --}}
                                <div class="flex justify-between items-start mb-4 relative z-10">
                                    <div class="w-10 h-10 rounded-xl {{ $bgSoft }} flex items-center justify-center {{ $colorHex }} group-hover:scale-110 transition-transform shadow-sm">
                                        <i class="fa-light {{ $info['icon'] }} text-lg"></i>
                                    </div>
                                    @if($isTop3)
                                        <div class="flex -space-x-1 animate-pulse">
                                            @if($isFirst)
                                                <i class="fa-solid fa-crown text-yellow-400 text-sm drop-shadow-[0_0_8px_rgba(250,204,21,0.5)]"></i>
                                            @else
                                                <i class="fa-solid fa-medal {{ $rank['rank'] == 2 ? 'text-gray-400' : 'text-amber-600' }} text-sm"></i>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="relative z-10">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 leading-tight">{{ $info['label'] }}</div>
                                    @if($rank['has_data'])
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-4xl font-black {{ $colorHex }} drop-shadow-sm group-hover:tracking-wider transition-all">#{{ $rank['rank'] }}</span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">z {{ $rank['total'] }}</span>
                                        </div>
                                        @if(isset($info['hint']))
                                            <p class="text-[9px] text-gray-400/80 dark:text-gray-500 font-medium leading-relaxed mt-1 italic group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors">
                                                {{ $info['hint'] }}
                                            </p>
                                        @endif

                                        {{-- Contextual stats --}}
                                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[9px] font-bold uppercase tracking-tighter text-gray-400/60 dark:text-gray-500/60 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors border-t border-gray-50 dark:border-gray-700/50 pt-2">
                                            <div class="flex items-center gap-1">
                                                <span class="opacity-70">Ty:</span>
                                                <span class="{{ $colorHex }} font-black">{{ $rank['value'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="opacity-70">Medián:</span>
                                                <span class="text-gray-600 dark:text-gray-300">{{ $rank['median'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="opacity-70">Průměr:</span>
                                                <span class="text-gray-600 dark:text-gray-300">{{ $rank['average'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="opacity-70">Nejlepší:</span>
                                                <span class="text-gray-600 dark:text-gray-300">{{ $rank['best'] }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-6 flex flex-col items-center justify-center text-center">
                                            <i class="fa-light fa-database-slash text-gray-200 dark:text-gray-700 text-3xl mb-2"></i>
                                            <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest italic">Data nedostupná</div>
                                            <p class="text-[8px] text-gray-400/60 mt-1">Tato soutěž nevede detailní statistiky.</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Visual Progress Bar --}}
                                @if($rank['has_data'])
                                    <div class="mt-6 h-2 w-full bg-gray-200/50 dark:bg-gray-700/50 rounded-full overflow-hidden relative z-10 shadow-inner">
                                        <div
                                            class="h-full {{ $info['bg'] }} rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(0,0,0,0.2)]"
                                            style="width: {{ $percentile }}%; background-color: var(--color-{{ $info['color'] }}-600);"
                                        ></div>
                                    </div>
                                    <div class="mt-2 flex justify-between items-center relative z-10">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                                            {{ $rank['rank'] == 1 ? 'Lídr týmu' : ($percentile >= 80 ? 'Špička týmu' : 'Pozice v týmu') }}
                                        </span>
                                        <span class="text-[10px] font-black {{ $colorHex }}">
                                            {{ round($percentile) }}%
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-auto pt-4 border-t border-gray-50 dark:border-gray-700/50">
                                         <div class="text-[8px] text-gray-300 dark:text-gray-600 font-medium italic">Ranking u této metriky nelze pro tento tým vypočítat.</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if(empty($rankings))
                            <div class="col-span-full bg-white dark:bg-gray-800 p-12 rounded-[2rem] border-2 border-dashed border-gray-100 dark:border-gray-700 text-center flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-300">
                                    <i class="fa-light fa-chart-simple text-3xl"></i>
                                </div>
                                <div class="max-w-xs mx-auto">
                                    <div class="text-gray-800 dark:text-white font-black uppercase tracking-widest text-sm mb-1">Rankings nejsou připraveny</div>
                                    <p class="text-xs text-gray-500 leading-relaxed italic">Počítají se z kompletních statistik celého týmu za celou sezónu.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="space-y-6">
                {{-- Row 1: Evolution Charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" wire:ignore>
                    {{-- Points Evolution --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[380px] h-auto flex flex-col group hover:shadow-md transition-shadow overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                                <i class="fa-light fa-chart-line mr-2 text-primary-500"></i> Vývoj bodů zápas po zápase
                            </h3>
                            <div class="px-2 py-0.5 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-[8px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest">
                                Útok
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                            Tato křivka ukazuje tvoji střeleckou potenci. Snaž se udržet body nad čárkovanou linií tvého průměru – to je cesta k růstu!
                        </p>
                        <div id="points-evolution-chart" class="flex-1"></div>
                    </div>

                    {{-- Efficiency Evolution --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[380px] h-auto flex flex-col group hover:shadow-md transition-shadow overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                                <i class="fa-light fa-bolt mr-2 text-orange-500"></i> Index užitečnosti (VAL)
                            </h3>
                            <div class="px-2 py-0.5 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-[8px] font-black text-orange-600 dark:text-orange-400 uppercase tracking-widest">
                                Komplexnost
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                            VAL zohledňuje vše: body, doskoky, asistence i dobrou obranu. Vysoká a stabilní efektivita je to, co trenéři milují nejvíc.
                        </p>
                        <div id="efficiency-evolution-chart" class="flex-1"></div>
                    </div>
                </div>

                {{-- Row 2: Analysis Charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" wire:ignore>
                    {{-- Skill Radar --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[420px] h-auto flex flex-col group hover:shadow-md transition-shadow @if(empty($rankings)) hidden @endif overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                                <i class="fa-light fa-radar mr-2 text-indigo-500"></i> Profil tvých dovedností
                            </h3>
                            <div class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-[8px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                                Percentil v týmu
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                            Tvůj otisk v týmu. Čím širší plocha, tím jsi pro soupeře nebezpečnější a komplexnější hráč. Kde máš rezervy?
                        </p>
                        <div id="skill-radar-chart" class="flex-1"></div>
                    </div>

                    {{-- Comparison vs Team Avg --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[420px] h-auto flex flex-col group hover:shadow-md transition-shadow overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                                <i class="fa-light fa-chart-mixed mr-2 text-blue-500"></i> Moje PPG vs Tým
                            </h3>
                            <div class="px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">
                                Srovnání
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed text-center">
                            Jak moc tvoříš ofenzívu týmu? Čárový graf ukazuje průměr celého týmu. Buď tím, kdo ho táhne nahoru!
                        </p>
                        <div id="comparison-chart" class="flex-1"></div>
                    </div>

                    {{-- Shots Distribution --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[420px] h-auto flex flex-col group hover:shadow-md transition-shadow overflow-hidden">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                                <i class="fa-light fa-bullseye-arrow mr-2 text-pink-500"></i> Rozložení tvých bodů
                            </h3>
                            <div class="px-2 py-0.5 rounded-lg bg-pink-50 dark:bg-pink-900/20 text-[8px] font-black text-pink-600 dark:text-pink-400 uppercase tracking-widest">
                                Styl hry
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                            Odkud hrozíš nejvíc? Jsi ostrostřelec z dálky, nebo dříč pod košem? Vyváženost z tebe dělá těžko bránitelného hráče.
                        </p>
                        <div id="shots-distribution-chart" class="flex-1"></div>
                    </div>
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
                                <th wire:click="sortBy('fg2_made')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Dvojky - proměněné střely'">
                                    2B
                                    @if($sortField === 'fg2_made')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('fg3_made')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Trojky - proměněné střely'">
                                    3B
                                    @if($sortField === 'fg3_made')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('ft_made')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Trestné hody (Free Throws)'">
                                    TH
                                    @if($sortField === 'ft_made')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('steals')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Zisky (Steals)'">
                                    Z
                                    @if($sortField === 'steals')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('turnovers')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Ztráty (Turnovers)'">
                                    ZT
                                    @if($sortField === 'turnovers')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('blocks')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Bloky (Blocks)'">
                                    BL
                                    @if($sortField === 'blocks')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('fouls')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Spáchané fauly (Fouls)'">
                                    F
                                    @if($sortField === 'fouls')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('efficiency')" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Index efektivity (VAL - Valuation)'">
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
                                <td class="px-6 py-4 text-center text-xs text-gray-500 font-medium">
                                    @if(isset($m['values']['fg2_att']) && $m['values']['fg2_att'] > 0)
                                        {{ ($m['values']['fg2_made'] ?? 0) }} / {{ $m['values']['fg2_att'] }}
                                    @else
                                        {{ ($m['values']['fg2_made'] ?? 0) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500 font-medium">
                                    @if(isset($m['values']['fg3_att']) && $m['values']['fg3_att'] > 0)
                                        {{ ($m['values']['fg3_made'] ?? 0) }} / {{ $m['values']['fg3_att'] }}
                                    @else
                                        {{ ($m['values']['fg3_made'] ?? 0) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500 font-medium">
                                    @if(isset($m['values']['ft_att']) && $m['values']['ft_att'] > 0)
                                        {{ ($m['values']['ft_made'] ?? 0) }} / {{ $m['values']['ft_att'] }}
                                    @else
                                        {{ ($m['values']['ft_made'] ?? 0) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-[10px] font-black text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-lg inline-block min-w-[1.5rem]">
                                        {{ $m['values']['steals'] ?? 0 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-[10px] font-black text-rose-500 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded-lg inline-block min-w-[1.5rem]">
                                        {{ $m['values']['turnovers'] ?? 0 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-[10px] font-black text-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-lg inline-block min-w-[1.5rem]">
                                        {{ $m['values']['blocks'] ?? 0 }}
                                    </div>
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
                <div class="text-gray-400 font-medium italic">Pro sezónu {{ $activeSeasonName }} a tým {{ $activeTeamName }} nejsou v databázi pro tohoto hráče žádná data.</div>
            </div>
        @endif
        @elseif($view === 'personal')
            {{-- Empty State pokud nejsou data (případně i pro neaktivní týmy) --}}
            <div class="bg-white dark:bg-gray-800 p-20 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center space-y-4">
                <i class="fa-light fa-chart-user text-6xl text-gray-100 dark:text-gray-700"></i>
                <div class="text-gray-400 font-medium italic">Pro sezónu {{ $activeSeasonName }} a tým {{ $activeTeamName }} nejsou v databázi pro tohoto hráče žádná data.</div>
            </div>
        @endif

        {{-- EXTERNAL STATS --}}
        @if(!empty($externalStats) || !empty($externalMatches))
            <div class="mt-16">
                @include('member.statistics.partials.external-stats-view')
            </div>
        @endif
    @elseif($view === 'career')
        <div class="space-y-8" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
            {{-- Career KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $careerCards = [
                        ['label' => 'Zápasy celkem', 'value' => $careerSummary['total_gp'] ?? 0, 'icon' => 'fa-basketball', 'color' => 'blue', 'unit' => 'Z', 'gradient' => 'from-blue-500/10 to-blue-600/5'],
                        ['label' => 'Body celkem', 'value' => $careerSummary['total_pts'] ?? 0, 'icon' => 'fa-sigma', 'color' => 'orange', 'unit' => 'B', 'gradient' => 'from-orange-500/10 to-orange-600/5'],
                        ['label' => 'Kariérní průměr', 'value' => number_format($careerSummary['ppg_avg'] ?? 0, 1, ',', ' '), 'icon' => 'fa-chart-line-up', 'color' => 'emerald', 'unit' => 'B/Z', 'gradient' => 'from-emerald-500/10 to-emerald-600/5'],
                        ['label' => 'Počet sezón', 'value' => $careerSummary['seasons_count'] ?? 0, 'icon' => 'fa-calendar-star', 'color' => 'purple', 'unit' => 'S', 'gradient' => 'from-purple-500/10 to-purple-600/5'],
                    ];

                    // Mapování barev na Tailwind třídy (pro Tailwind v4 je lepší nepoužívat dynamické složení tříd v šabloně u barev)
                    $colorMap = [
                        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100/50 dark:border-blue-800/50', 'glow' => 'bg-blue-500/5'],
                        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/30', 'text' => 'text-orange-600 dark:text-orange-400', 'border' => 'border-orange-100/50 dark:border-orange-800/50', 'glow' => 'bg-orange-500/5'],
                        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100/50 dark:border-emerald-800/50', 'glow' => 'bg-emerald-500/5'],
                        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/30', 'text' => 'text-purple-600 dark:text-purple-400', 'border' => 'border-purple-100/50 dark:border-purple-800/50', 'glow' => 'bg-purple-500/5'],
                    ];
                @endphp

                @foreach($careerCards as $card)
                    @php $c = $colorMap[$card['color']]; @endphp
                    <div class="group relative overflow-hidden bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-6 sm:p-8 rounded-[2.5rem] sm:rounded-[3rem] shadow-xl shadow-gray-200/40 dark:shadow-none border border-white/40 dark:border-gray-700/50 hover:scale-[1.02] transition-all duration-500">
                        <div class="absolute -right-4 -top-4 w-32 h-32 {{ $c['glow'] }} rounded-full blur-3xl group-hover:opacity-100 opacity-50 transition-opacity"></div>
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 {{ $c['bg'] }} rounded-2xl flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform duration-500 shadow-sm border {{ $c['border'] }}">
                                <i class="fa-light {{ $card['icon'] }} text-xl sm:text-2xl {{ $c['text'] }}"></i>
                            </div>
                            <span class="text-[9px] sm:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">{{ $card['label'] }}</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $card['value'] }}</span>
                                @if($card['unit'])
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $card['unit'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Career Charts --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                {{-- PPG History Chart --}}
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-8 rounded-[3rem] shadow-xl border border-white/40 dark:border-gray-700/50">
                    <div class="flex justify-between items-center mb-8 px-2">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight uppercase">Vývoj výkonnosti</h3>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Průměrné body a efektivita napříč sezónami</p>
                        </div>
                        <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center border border-primary-100/50 dark:border-primary-800/50">
                            <i class="fa-light fa-chart-line text-primary-600 dark:text-primary-400"></i>
                        </div>
                    </div>
                    <div id="career-performance-chart" style="min-height: 350px;" wire:ignore></div>
                </div>

                {{-- Games Played History Chart --}}
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-8 rounded-[3rem] shadow-xl border border-white/40 dark:border-gray-700/50">
                    <div class="flex justify-between items-center mb-8 px-2">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight uppercase">Zápasové vytížení</h3>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Počet odehraných zápasů za sezónu</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 rounded-2xl flex items-center justify-center border border-orange-100/50 dark:border-orange-800/50">
                            <i class="fa-light fa-basketball-hoop text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div id="career-gp-chart" style="min-height: 350px;" wire:ignore></div>
                </div>
            </div>

            {{-- Best Seasons Highlights --}}
            @if($careerSummary['best_ppg_season'] || $careerSummary['best_eff_season'])
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @if($careerSummary['best_ppg_season'])
                <div class="group relative bg-gradient-to-br from-primary-600 to-primary-800 p-1 rounded-[3rem] shadow-2xl overflow-hidden shadow-primary-500/20">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                    <div class="relative bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm p-8 rounded-[2.9rem] h-full flex items-center justify-between overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-primary-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                        <div class="relative z-10">
                            <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em]">Nejlepší bodová sezóna</span>
                            <h4 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $careerSummary['best_ppg_season']['season'] }}</h4>
                            <div class="flex items-center gap-4 mt-4">
                                <div class="flex flex-col">
                                    <span class="text-2xl font-black text-primary-600">{{ $careerSummary['best_ppg_season']['ppg'] }}</span>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Body / Zápas</span>
                                </div>
                                <div class="w-px h-8 bg-gray-200 dark:bg-gray-800"></div>
                                <div class="flex flex-col">
                                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $careerSummary['best_ppg_season']['gp'] }}</span>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Zápasů</span>
                                </div>
                            </div>
                        </div>
                        <div class="relative z-10 text-primary-100 group-hover:scale-110 group-hover:rotate-12 transition-all duration-700">
                             <i class="fa-light fa-crown text-7xl opacity-20"></i>
                        </div>
                    </div>
                </div>
                @endif

                @if($careerSummary['best_eff_season'])
                <div class="group relative bg-gradient-to-br from-emerald-600 to-emerald-800 p-1 rounded-[3rem] shadow-2xl overflow-hidden shadow-emerald-500/20">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                    <div class="relative bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm p-8 rounded-[2.9rem] h-full flex items-center justify-between overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                        <div class="relative z-10">
                            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em]">Nejužitečnější sezóna</span>
                            <h4 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $careerSummary['best_eff_season']['season'] }}</h4>
                            <div class="flex items-center gap-4 mt-4">
                                <div class="flex flex-col">
                                    <span class="text-2xl font-black text-emerald-600">{{ $careerSummary['best_eff_season']['efficiency_avg'] }}</span>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Užitečnost Ø</span>
                                </div>
                                <div class="w-px h-8 bg-gray-200 dark:bg-gray-800"></div>
                                <div class="flex flex-col">
                                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $careerSummary['best_eff_season']['gp'] }}</span>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Zápasů</span>
                                </div>
                            </div>
                        </div>
                        <div class="relative z-10 text-emerald-100 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700">
                             <i class="fa-light fa-rocket text-7xl opacity-20"></i>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Career History Table --}}
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-[3rem] shadow-xl border border-white/40 dark:border-gray-700/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 dark:border-gray-700/50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight uppercase">Sezónní historie</h3>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Přehled všech odehraných sezón v klubu</p>
                    </div>
                    <div class="flex gap-2">
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 text-[10px] font-black uppercase text-gray-500 tracking-widest">
                            Celkem {{ count($careerHistory) }} sezón
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/50">
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Sezóna</th>
                                <th class="px-6 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Zápasy</th>
                                <th class="px-6 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Body celkem</th>
                                <th class="px-6 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">B / Z</th>
                                <th class="px-6 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Užitečnost</th>
                                <th class="px-6 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Doskoky</th>
                                <th class="px-6 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Asistence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach(collect($careerHistory)->sortByDesc('season') as $row)
                                <tr class="group hover:bg-primary-50/30 dark:hover:bg-primary-900/10 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-500 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/50 group-hover:text-primary-600 transition-colors">
                                                {{ substr($row['season'], 2, 2) }}
                                            </div>
                                            <span class="text-sm font-black text-gray-900 dark:text-white">{{ $row['season'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $row['gp'] }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">{{ $row['pts_total'] }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs font-black">
                                            {{ number_format($row['ppg'], 1) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($row['efficiency_avg'], 1) }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="text-sm font-medium text-gray-500">{{ number_format($row['rebounds_avg'], 1) }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="text-sm font-medium text-gray-500">{{ number_format($row['assists_avg'], 1) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($view === 'team')
        {{-- TEAM VIEW --}}
        @if($teamSummary && ($teamSummary['gp'] ?? 0) > 0)
            {{-- 2. Team Heroes (Leaders) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @php
                    $heroes = [
                        ['key' => 'scorers', 'label' => 'Král střelců', 'icon' => 'fa-bullseye', 'color' => 'blue', 'metric' => 'ppg', 'unit' => 'PPG', 'hint' => 'Nejlepší průměr bodů na zápas'],
                        ['key' => 'mvp', 'label' => 'MVP Týmu', 'icon' => 'fa-bolt', 'color' => 'orange', 'metric' => 'efficiency_avg', 'unit' => 'VAL', 'hint' => 'Nejvyšší průměrná užitečnost'],
                        ['key' => 'total_points', 'label' => 'Kanonýr sezóny', 'icon' => 'fa-fire-flame-curved', 'color' => 'red', 'metric' => 'pts_total', 'unit' => 'BODY', 'hint' => 'Nejvyšší celkový počet bodů v sezóně'],
                        ['key' => 'ironman', 'label' => 'Železný muž', 'icon' => 'fa-dumbbell', 'color' => 'emerald', 'metric' => 'gp', 'unit' => 'ZÁP', 'hint' => 'Hráč s nejvíce odehranými zápasy v sezóně'],
                        ['key' => 'snipers', 'label' => 'Odstřelovač', 'icon' => 'fa-crosshairs', 'color' => 'violet', 'metric' => 'fg3_total', 'unit' => '3B', 'hint' => 'Hráč s nejvíce proměněnými trojkami v sezóně'],
                        ['key' => 'th_kings', 'label' => 'Trestač týmu', 'icon' => 'fa-bullseye-arrow', 'color' => 'pink', 'metric' => 'ft_pct', 'unit' => '% TH', 'hint' => 'Nejlepší úspěšnost trestných hodů'],
                        ['key' => 'rebounders', 'label' => 'Vládce podkoše', 'icon' => 'fa-hand-back-point-up', 'color' => 'indigo', 'metric' => 'rebounds_avg', 'unit' => 'D Ø', 'hint' => 'Nejvíce doskoků na zápas'],
                        ['key' => 'passers', 'label' => 'Generál nahrávek', 'icon' => 'fa-share-all', 'color' => 'amber', 'metric' => 'assists_avg', 'unit' => 'A Ø', 'hint' => 'Nejvíce asistencí na zápas'],
                        ['key' => 'defenders', 'label' => 'Zloděj míčů', 'icon' => 'fa-hand-holding-magic', 'color' => 'sky', 'metric' => 'steals_avg', 'unit' => 'Z Ø', 'hint' => 'Nejvíce získaných míčů na zápas'],
                    ];
                @endphp
                @foreach($heroes as $h)
                    @php $player = $teamLeaders[$h['key']] ?? null; @endphp
                    @if($player && ($player[$h['metric']] ?? 0) > 0)
                        <div
                            wire:click="loadPlayerStats({{ $player['player_id'] }})"
                            class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.03] transition-all cursor-pointer"
                        >
                            <div class="absolute top-4 right-4 text-gray-400/30 dark:text-gray-500/30 hover:text-primary-500 transition-colors cursor-help p-1 group/hint" x-tooltip="'{{ $h['hint'] }}'">
                                <i class="fa-solid fa-circle-question text-[10px]"></i>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-{{ $h['color'] }}-50 dark:bg-{{ $h['color'] }}-900/20 flex items-center justify-center text-{{ $h['color'] }}-500 text-xl group-hover:scale-110 transition-transform">
                                    <i class="fa-light {{ $h['icon'] }}"></i>
                                </div>
                                <div>
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $h['label'] }}</h4>
                                    <div class="text-sm font-black text-gray-800 dark:text-white truncate max-w-[120px] group-hover:text-primary-500 transition-colors">
                                        {{ $player['name'] ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="text-2xl font-black text-{{ $h['color'] }}-600 dark:text-{{ $h['color'] }}-400">{{ $player[$h['metric']] ?? 0 }}</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $h['unit'] }}</span>
                            </div>
                            <div class="absolute bottom-0 left-0 w-full h-1 bg-{{ $h['color'] }}-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- 3. Team Analytics Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                {{-- Points Distribution (Donut) --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col h-[400px] group" wire:ignore>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                            <i class="fa-light fa-chart-pie mr-2 text-blue-500"></i> Příspěvek k bodům <span class="text-primary-500">(Top 5)</span>
                        </h3>
                    </div>
                    <div id="points-distribution-chart" class="flex-1"></div>
                </div>

                {{-- Efficiency Comparison (Bar) --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col h-[400px] group" wire:ignore>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                            <i class="fa-light fa-bolt mr-2 text-orange-500"></i> Srovnání efektivity <span class="text-primary-500">(VAL Ø)</span>
                        </h3>
                    </div>
                    <div id="efficiency-comparison-chart" class="flex-1"></div>
                </div>

                {{-- Team Evolution (Area) --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col h-[400px] group" wire:ignore>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                            <i class="fa-light fa-chart-area mr-2 text-emerald-500"></i> Vývoj skóre <span class="text-primary-500">(Dáno vs Dostáno)</span>
                        </h3>
                    </div>
                    <div id="team-evolution-chart" class="flex-1"></div>
                </div>
            </div>

            {{-- 4. Full Team Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 overflow-hidden mb-12">
                <div class="p-8 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50">
                    <h3 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-[0.2em]">Hráčské statistiky <span class="text-primary-500">(Kompletní srovnání)</span></h3>
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-500">
                        <i class="fa-light fa-users-viewfinder"></i>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    @php
                        $maxPts = collect($topScorers)->max('pts_total') ?: 1;
                        $maxPpg = collect($topScorers)->max('ppg') ?: 1;
                        $maxEf = collect($topScorers)->max('efficiency_avg') ?: 1;
                        $maxReb = collect($topScorers)->max('rebounds_avg') ?: 1;
                        $maxAss = collect($topScorers)->max('assists_avg') ?: 1;
                    @endphp
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
                                <th wire:click="sortBy('gp')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Odehrané zápasy (Games Played)'">
                                    Z
                                    @if($sortField === 'gp')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('pts_total')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Celkový počet bodů v sezóně'">
                                    B celkem
                                    @if($sortField === 'pts_total')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('ppg')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Průměr bodů na zápas (Points Per Game)'">
                                    B/Z
                                    @if($sortField === 'ppg')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('efficiency_avg')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Index efektivity (VAL - Valuation) průměr'">
                                    EF Ø
                                    @if($sortField === 'efficiency_avg')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('rebounds_avg')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Průměr doskoků na zápas'">
                                    D Ø
                                    @if($sortField === 'rebounds_avg')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('assists_avg')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Průměr asistencí na zápas'">
                                    A Ø
                                    @if($sortField === 'assists_avg')
                                        <i class="fa-light fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('ft_pct')" class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:text-primary-500 transition-colors" x-tooltip="'Úspěšnost trestných hodů'">
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
                                    <button wire:click="showPlayerStats({{ $scorer['player_id'] }})" class="hover:underline text-left">
                                        {{ $scorer['name'] }}
                                    </button>
                                </td>
                                <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['gp'] }}</td>
                                <td class="px-4 py-4 text-center font-black text-lg" @if($maxPts > 0 && $scorer['pts_total'] > 0) style="background-color: rgba(59, 130, 246, {{ ($scorer['pts_total'] / $maxPts) * 0.15 }})" @endif>{{ $scorer['pts_total'] }}</td>
                                <td class="px-4 py-4 text-center" @if($maxPpg > 0 && $scorer['ppg'] > 0) style="background-color: rgba(16, 185, 129, {{ ($scorer['ppg'] / $maxPpg) * 0.15 }})" @endif>
                                    <span class="bg-primary-600 text-white px-3 py-1 rounded-lg text-xs font-black shadow-sm group-hover/row:scale-110 transition-transform inline-block">
                                        {{ $scorer['ppg'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center font-bold text-orange-600 dark:text-orange-400" @if($maxEf > 0 && $scorer['efficiency_avg'] > 0) style="background-color: rgba(245, 158, 11, {{ ($scorer['efficiency_avg'] / $maxEf) * 0.15 }})" @endif>{{ $scorer['efficiency_avg'] }}</td>
                                <td class="px-4 py-4 text-center font-bold text-indigo-600 dark:text-indigo-400" @if($maxReb > 0 && $scorer['rebounds_avg'] > 0) style="background-color: rgba(79, 70, 229, {{ ($scorer['rebounds_avg'] / $maxReb) * 0.15 }})" @endif>{{ $scorer['rebounds_avg'] }}</td>
                                <td class="px-4 py-4 text-center font-bold text-violet-600 dark:text-violet-400" @if($maxAss > 0 && $scorer['assists_avg'] > 0) style="background-color: rgba(139, 92, 246, {{ ($scorer['assists_avg'] / $maxAss) * 0.15 }})" @endif>{{ $scorer['assists_avg'] }}</td>
                                <td class="px-4 py-4 text-center text-gray-500 font-bold">{{ $scorer['ft_pct'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-800 p-20 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center space-y-4">
                <i class="fa-light fa-users-slash text-6xl text-gray-100 dark:text-gray-700"></i>
                <div class="text-gray-400 font-medium italic">Pro sezónu {{ $activeSeasonName }} a tým {{ $activeTeamName }} nejsou v databázi žádná týmová data.</div>
            </div>
        @endif
    @elseif($view === 'matches')
        {{-- MATCHES VIEW --}}
        @if($teamSummary && ($teamSummary['gp'] ?? 0) > 0)
            {{-- 1. Team Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                {{-- Record --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.02] transition-all">
                    <div class="absolute top-4 right-4 flex items-center gap-2">
                        @if(($teamSummary['gp'] ?? 0) != ($calculatedSummary['gp'] ?? 0))
                            <div class="text-amber-500 animate-pulse cursor-help p-1" x-tooltip="'Pozor: Oficiální tabulka uvádí {{ $teamSummary['gp'] }} zápasů, ale v našem itineráři je jich zatím jen {{ $calculatedSummary['gp'] }} s výsledkem. Některé zápasy se ještě synchronizují.'">
                                <i class="fa-solid fa-circle-exclamation text-[12px]"></i>
                            </div>
                        @endif
                        <div class="text-gray-300 dark:text-gray-600 hover:text-primary-500 transition-colors cursor-help p-1" x-tooltip="'Počet výher ku počtu proher v sezóně.'">
                            <i class="fa-solid fa-circle-question text-[10px]"></i>
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:opacity-[0.08] transition-all group-hover:rotate-12 group-hover:scale-125">
                        <i class="fa-light fa-trophy-star text-[10rem]"></i>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Sezónní bilance</h3>
                        <div class="flex items-end gap-2">
                            <div class="text-5xl font-black text-emerald-500">{{ $teamSummary['wins'] ?? 0 }}</div>
                            <div class="text-xl font-black text-gray-200 dark:text-gray-700 mb-1">/</div>
                            <div class="text-3xl font-black text-rose-500">{{ $teamSummary['losses'] ?? 0 }}</div>
                        </div>
                        <div class="mt-4 inline-flex items-center px-3 py-1 bg-gray-50 dark:bg-gray-900 rounded-full text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <i class="fa-light fa-basketball mr-2 text-primary-500"></i> Celkem {{ $teamSummary['gp'] ?? 0 }} zápasů
                        </div>
                    </div>
                </div>

                {{-- Points (Offense) --}}
                <div class="bg-emerald-600 p-6 rounded-[2rem] shadow-lg shadow-emerald-500/20 text-center relative overflow-hidden group hover:scale-[1.02] transition-all border border-emerald-500/20">
                    <div class="absolute top-4 right-4 text-white/40 hover:text-white transition-colors cursor-help" x-tooltip="'Průměrný počet vstřelených bodů na zápas za celý tým.'">
                        <i class="fa-solid fa-circle-question text-[10px]"></i>
                    </div>
                    <div class="absolute -left-4 -top-4 opacity-10 group-hover:scale-110 transition-transform">
                        <i class="fa-light fa-fire-flame-curved text-7xl text-white"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-white/60 uppercase tracking-[0.2em] mb-4 relative z-10">Průměrná ofenzíva</h3>
                    <div class="text-6xl font-black text-white drop-shadow-md relative z-10">
                        {{ $teamSummary['pts_avg'] ?? 0 }}
                    </div>
                    <div class="mt-4 flex justify-center gap-4 text-[9px] font-black uppercase text-white/60 relative z-10">
                        <span class="bg-white/10 px-2 py-1 rounded-lg backdrop-blur-sm border border-white/5">Celkem dáno: {{ $teamSummary['pts_for'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- Defense (Danger) --}}
                <div class="bg-rose-600 p-6 rounded-[2rem] shadow-lg shadow-rose-500/20 text-center relative overflow-hidden group hover:scale-[1.02] transition-all border border-rose-500/20">
                    <div class="absolute top-4 right-4 text-white/40 hover:text-white transition-colors cursor-help" x-tooltip="'Průměrný počet obdržených bodů na zápas.'">
                        <i class="fa-solid fa-circle-question text-[10px]"></i>
                    </div>
                    <div class="absolute -left-4 -top-4 opacity-10 group-hover:scale-110 transition-transform">
                        <i class="fa-light fa-shield-slash text-7xl text-white"></i>
                    </div>
                    <h3 class="text-[10px] font-black text-white/60 uppercase tracking-[0.2em] mb-4 relative z-10">Průměrná defenzíva</h3>
                    <div class="text-6xl font-black text-white drop-shadow-md relative z-10">
                        {{ $teamSummary['pts_against_avg'] ?? 0 }}
                    </div>
                    <div class="mt-4 flex justify-center gap-4 text-[9px] font-black uppercase text-white/60 relative z-10">
                        <span class="bg-white/10 px-2 py-1 rounded-lg backdrop-blur-sm border border-white/5">Celkem obdrženo: {{ $teamSummary['pts_against'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- Form (Refined) --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 flex flex-col justify-center items-center group hover:scale-[1.02] transition-all relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity">
                        <i class="fa-light fa-chart-line text-7xl text-primary-500"></i>
                    </div>

                    @php
                        $streak = 0;
                        $streakType = null;
                        if (!empty($recentForm)) {
                            $lastResult = end($recentForm)['result'];
                            $streakType = $lastResult;
                            foreach (array_reverse($recentForm) as $f) {
                                if ($f['result'] === $lastResult) {
                                    $streak++;
                                } else {
                                    break;
                                }
                            }
                        }
                    @endphp

                    <div class="flex flex-col items-center mb-6">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Aktuální forma</h3>
                        @if($streak > 1)
                            <span @class([
                                'px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm',
                                'bg-emerald-500 text-white shadow-emerald-500/20' => $streakType === 'W',
                                'bg-rose-500 text-white shadow-rose-500/20' => $streakType === 'L',
                            ])>
                                <i class="fa-solid {{ $streakType === 'W' ? 'fa-fire' : 'fa-snowflake' }} mr-1"></i>
                                {{ $streak }}x {{ $streakType === 'W' ? 'VÝHRA' : 'PROHRA' }}
                            </span>
                        @endif
                    </div>

                    <div class="flex justify-center gap-2.5 mb-6">
                        @foreach($recentForm as $f)
                            <div
                                wire:key="form-{{ $loop->index }}"
                                @class([
                                    'w-9 h-9 rounded-xl flex items-center justify-center font-black text-[11px] shadow-lg transition-all hover:scale-115 cursor-help border-2',
                                    'bg-gradient-to-br from-emerald-400 to-emerald-600 border-emerald-300/50 text-white shadow-emerald-500/20' => $f['result'] === 'W',
                                    'bg-gradient-to-br from-rose-400 to-rose-600 border-rose-300/50 text-white shadow-rose-500/20' => $f['result'] === 'L'
                                ])
                                x-tooltip="'{{ $f['opponent'] }} ({{ $f['pts_for'] }}:{{ $f['pts_against'] }})'"
                            >
                                {{ $f['result'] }}
                            </div>
                        @endforeach
                        @if(empty($recentForm))
                            <span class="text-gray-400 italic text-xs">Bez nedávných zápasů</span>
                        @endif
                    </div>

                    @if($teamFormSummary)
                        <div class="px-4 py-1.5 rounded-full bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center gap-3">
                            <span class="text-[11px] font-black text-emerald-600 dark:text-emerald-400">{{ $teamFormSummary['avg_pts_for'] }}</span>
                            <span class="text-[10px] text-gray-300">:</span>
                            <span class="text-[11px] font-black text-rose-600 dark:text-rose-400">{{ $teamFormSummary['avg_pts_against'] }}</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Ø PPG</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Extremes & Location Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                {{-- Biggest Win --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.03] transition-all">
                    <div class="absolute top-4 right-4 text-gray-400/30 dark:text-gray-500/30 hover:text-emerald-500 transition-colors cursor-help p-1 group/hint" x-tooltip="'Nejvyšší bodový rozdíl ve vítězném zápase'">
                        <i class="fa-solid fa-circle-question text-[10px]"></i>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-500 text-xl shadow-inner">
                            <i class="fa-light fa-star-exclamation"></i>
                        </div>
                        <div>
                            <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Největší výhra</h4>
                            <div class="text-sm font-black text-gray-800 dark:text-white truncate max-w-[150px]">
                                {{ $matchStats['biggest_win']['opponent'] ?? 'Žádná data' }}
                            </div>
                        </div>
                    </div>
                    @if($matchStats['biggest_win'])
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $matchStats['biggest_win']['score'] }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">(+{{ $matchStats['biggest_win']['margin'] }})</span>
                            </div>
                            <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest">
                                {{ \Carbon\Carbon::parse($matchStats['biggest_win']['date'])->format('d.m.Y') }}
                            </div>
                        </div>
                    @else
                        <div class="mt-4 text-xs italic text-gray-400">Zatím se neoslavovala žádná výhra</div>
                    @endif
                </div>

                {{-- Biggest Loss --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.03] transition-all">
                    <div class="absolute top-4 right-4 text-gray-400/30 dark:text-gray-500/30 hover:text-rose-500 transition-colors cursor-help p-1 group/hint" x-tooltip="'Nejvyšší bodový rozdíl v prohraném zápase'">
                        <i class="fa-solid fa-circle-question text-[10px]"></i>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500 text-xl shadow-inner">
                            <i class="fa-light fa-circle-xmark"></i>
                        </div>
                        <div>
                            <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Nejtěžší prohra</h4>
                            <div class="text-sm font-black text-gray-800 dark:text-white truncate max-w-[150px]">
                                {{ $matchStats['biggest_loss']['opponent'] ?? 'Žádná data' }}
                            </div>
                        </div>
                    </div>
                    @if($matchStats['biggest_loss'])
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $matchStats['biggest_loss']['score'] }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">(-{{ $matchStats['biggest_loss']['margin'] }})</span>
                            </div>
                            <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest">
                                {{ \Carbon\Carbon::parse($matchStats['biggest_loss']['date'])->format('d.m.Y') }}
                            </div>
                        </div>
                    @else
                        <div class="mt-4 text-xs italic text-gray-400">Bez prohry – jedeme bomby!</div>
                    @endif
                </div>

                {{-- Home Performance --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.03] transition-all">
                    <div class="absolute top-4 right-4 text-gray-400/30 dark:text-gray-500/30 hover:text-blue-500 transition-colors cursor-help p-1 group/hint" x-tooltip="'Bilance zápasů na domácí palubovce'">
                        <i class="fa-solid fa-circle-question text-[10px]"></i>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 text-xl shadow-inner">
                            <i class="fa-light fa-house-chimney-heart"></i>
                        </div>
                        <div>
                            <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Domácí tvrz</h4>
                            <div class="text-sm font-black text-gray-800 dark:text-white">
                                {{ $matchStats['home_balance']['wins'] ?? 0 }}V - {{ $matchStats['home_balance']['losses'] ?? 0 }}P
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-black text-gray-400 uppercase">Úspěšnost</span>
                            <span class="text-xs font-black text-blue-600 dark:text-blue-400">{{ $matchStats['home_balance']['pct'] ?? 0 }}%</span>
                        </div>
                        <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner p-0.5 border border-gray-50 dark:border-gray-600">
                            <div class="h-full bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.5)] transition-all duration-1000" style="width: {{ $matchStats['home_balance']['pct'] ?? 0 }}%; background-color: var(--color-blue-600);"></div>
                        </div>
                    </div>
                </div>

                {{-- Away Performance --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 relative overflow-hidden group hover:scale-[1.03] transition-all">
                    <div class="absolute top-4 right-4 text-gray-400/30 dark:text-gray-500/30 hover:text-orange-500 transition-colors cursor-help p-1 group/hint" x-tooltip="'Bilance zápasů na palubovkách soupeřů'">
                        <i class="fa-solid fa-circle-question text-[10px]"></i>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-500 text-xl shadow-inner">
                            <i class="fa-light fa-bus"></i>
                        </div>
                        <div>
                            <h4 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Venkovní mise</h4>
                            <div class="text-sm font-black text-gray-800 dark:text-white">
                                {{ $matchStats['away_balance']['wins'] ?? 0 }}V - {{ $matchStats['away_balance']['losses'] ?? 0 }}P
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-black text-gray-400 uppercase">Úspěšnost</span>
                            <span class="text-xs font-black text-orange-600 dark:text-orange-400">{{ $matchStats['away_balance']['pct'] ?? 0 }}%</span>
                        </div>
                        <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner p-0.5 border border-gray-50 dark:border-gray-600">
                            <div class="h-full bg-orange-500 rounded-full shadow-[0_0_8px_rgba(245,158,11,0.5)] transition-all duration-1000" style="width: {{ $matchStats['away_balance']['pct'] ?? 0 }}%; background-color: var(--color-orange-600);"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Charts --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8" wire:ignore>
                {{-- Team Points Evolution --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 h-[450px] flex flex-col group hover:shadow-xl transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                            <i class="fa-light fa-chart-line mr-2 text-primary-500"></i> Vývoj skóre v čase
                        </h3>
                        <div class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-[8px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                            Útok vs Obrana
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                        Sledujte, jak se vyvíjí ofenzivní síla týmu ve srovnání s obdrženými body. Ideální je udržet červenou linku nad šedou.
                    </p>
                    <div id="team-points-series-chart" class="flex-1 min-h-0"></div>
                </div>

                {{-- Chart 2: Match Margin --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 h-[450px] flex flex-col group hover:shadow-xl transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                            <i class="fa-light fa-chart-bar mr-2 text-amber-500"></i> Bodový rozdíl zápasů
                        </h3>
                        <div class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-[8px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">
                            Dominance
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                        Výška sloupce ukazuje o kolik bodů tým vyhrál (zelená) nebo prohrál (červená). Delší zelené sloupce značí jasnou převahu.
                    </p>
                    <div id="team-match-margin-chart" class="flex-1 min-h-0"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12" wire:ignore>
                {{-- Chart 3: Home/Away Success --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 h-[400px] flex flex-col group hover:shadow-xl transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                            <i class="fa-light fa-house-chimney mr-2 text-blue-500"></i> Úspěšnost Doma vs Venku
                        </h3>
                    </div>
                    <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                        Porovnání procentuální úspěšnosti výher podle místa konání zápasu.
                    </p>
                    <div id="home-away-success-chart" class="flex-1 min-h-0"></div>
                </div>

                {{-- Chart 4: Scoring by location --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 h-[400px] flex flex-col group hover:shadow-xl transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                            <i class="fa-light fa-basketball-hoop mr-2 text-emerald-500"></i> Ofenzíva Doma vs Venku
                        </h3>
                    </div>
                    <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                        Průměrný počet vstřelených bodů v závislosti na tom, zda hrajeme doma nebo venku.
                    </p>
                    <div id="home-away-points-chart" class="flex-1 min-h-0"></div>
                </div>

                {{-- Chart 5: Results Distribution --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] shadow-lg shadow-gray-100 dark:shadow-none border border-gray-50 dark:border-gray-700 h-[400px] flex flex-col group hover:shadow-xl transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest flex items-center">
                            <i class="fa-light fa-pie-chart mr-2 text-violet-500"></i> Rozdělení výsledků
                        </h3>
                    </div>
                    <p class="text-[10px] text-gray-400 italic mb-4 leading-relaxed">
                        Celkový poměr výher a proher v probíhající sezóně.
                    </p>
                    <div id="team-win-loss-donut-chart" class="flex-1 min-h-0"></div>
                </div>
            </div>
        @endif

        {{-- MATCHES TABLE --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden group/table">
            <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-gray-50/50 to-transparent dark:from-gray-900/10">
                <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest flex items-center">
                    <i class="fa-light fa-list-timeline mr-2 text-primary-500"></i> Detailní itinerář zápasů
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
                                Pro sezónu {{ $activeSeasonName }} a tým {{ $activeTeamName }} nebyly nalezeny žádné zápasy.
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
            orange: '#f59e0b',
            indigo: '#6366f1',
            violet: '#8b5cf6',
            pink: '#ec4899',
            emerald: '#10b981',
            rose: '#f43f5e',
            gray: '#94a3b8'
        };

        const initPersonalCharts = () => {
            console.log('initPersonalCharts starting');
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded!');
                return;
            }

            try {
                const seriesData = $wire.chartSeries;
                if (!seriesData || seriesData.length === 0) {
                    console.log('initPersonalCharts - no series data');
                    return;
                }

                const chartEl1 = document.querySelector("#points-evolution-chart");
                const chartEl2 = document.querySelector("#comparison-chart");
                const chartEl3 = document.querySelector("#efficiency-evolution-chart");
                const chartEl4 = document.querySelector("#skill-radar-chart");
                const chartEl5 = document.querySelector("#shots-distribution-chart");

                const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
                const points = seriesData.map(m => (m.values ? m.values.pts : 0) || 0);
                const efficiency = seriesData.map(m => (m.values ? m.values.efficiency : 0) || 0);
                const opponents = seriesData.map(m => m.opponent);
                const ppgAvg = $wire.summary ? ($wire.summary.ppg ?? 0) : 0;

                if (chartEl1) {
                    chartEl1.innerHTML = '';
                    new ApexCharts(chartEl1, {
                        series: [{ name: 'Body', data: points }],
                        chart: { type: 'line', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
                        stroke: { curve: 'smooth', width: 4, colors: [colors.primary] },
                        colors: [colors.primary],
                        markers: { size: 5, strokeColors: '#fff', strokeWidth: 2 },
                        xaxis: { categories: dates, labels: { show: false } },
                        yaxis: { min: 0 },
                        annotations: {
                            yaxis: [{
                                y: ppgAvg,
                                borderColor: colors.primary,
                                label: {
                                    text: 'Tvůj průměr: ' + ppgAvg,
                                    style: { color: '#fff', background: colors.primary }
                                }
                            }]
                        },
                        tooltip: {
                            y: { formatter: (val, { dataPointIndex }) => `${val} bodů vs ${opponents[dataPointIndex]}` }
                        }
                    }).render();
                }

                if (chartEl3) {
                    chartEl3.innerHTML = '';
                    const effAvg = $wire.summary ? ($wire.summary.efficiency_avg ?? 0) : 0;

                    new ApexCharts(chartEl3, {
                        series: [{ name: 'Index užitečnosti (VAL)', data: efficiency }],
                        chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
                        stroke: { curve: 'smooth', width: 3, colors: [colors.orange] },
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 }
                        },
                        colors: [colors.orange],
                        xaxis: { categories: dates, labels: { show: false } },
                        yaxis: { min: 0 },
                        annotations: {
                            yaxis: [{
                                y: effAvg,
                                borderColor: colors.orange,
                                strokeDashArray: 4,
                                label: {
                                    text: 'Průměrná VAL: ' + effAvg,
                                    style: { color: '#fff', background: colors.orange }
                                }
                            }]
                        },
                        tooltip: {
                            y: { formatter: (val, { dataPointIndex }) => `${val} VAL vs ${opponents[dataPointIndex]}` }
                        }
                    }).render();
                }

                if (chartEl2) {
                    chartEl2.innerHTML = '';
                    const teamAvg = $wire.teamAverages ? ($wire.teamAverages.pts_avg ?? 0) : 0;

                    new ApexCharts(chartEl2, {
                        series: [
                            { name: 'Moje body', type: 'column', data: points },
                            { name: 'Průměr týmu', type: 'line', data: Array(points.length).fill(teamAvg) }
                        ],
                        chart: { height: '100%', type: 'line', toolbar: { show: false }, zoom: { enabled: false } },
                        colors: [colors.primary, colors.gray],
                        stroke: { width: [0, 2], dashArray: [0, 5] },
                        plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                        xaxis: { categories: dates, labels: { show: false } },
                        yaxis: { min: 0 },
                        legend: { position: 'top', fontSize: '10px', fontWeight: 'bold' }
                    }).render();
                }

                if (chartEl4 && $wire.rankings) {
                    chartEl4.innerHTML = '';
                    const rankings = $wire.rankings;
                    const labels = [];
                    const data = [];

                    const mapping = {
                        'pts_total': 'Body Celkem',
                        'ppg': 'Body (PPG)',
                        'efficiency_avg': 'Efektivita (VAL)',
                        'rebounds_avg': 'Doskoky',
                        'gp': 'Účast (GP)',
                        'minutes_avg': 'Vytížení (Min)',
                        'fg3_total': 'Trojky',
                        'ft_pct': 'TH%',
                        'fouls_avg': 'Fauly'
                    };

                    Object.keys(rankings).forEach(key => {
                        if (mapping[key]) {
                            labels.push(mapping[key]);
                            // Percentil: (total - rank + 1) / total * 100
                            const rank = rankings[key];
                            const percentile = ((rank.total - rank.rank + 1) / rank.total) * 100;
                            data.push(Math.round(percentile));
                        }
                    });

                    new ApexCharts(chartEl4, {
                        series: [{ name: 'Tvůj percentil v týmu', data: data }],
                        chart: { type: 'radar', height: '100%', toolbar: { show: false }, zoom: { enabled: false } },
                        colors: [colors.indigo],
                        xaxis: {
                            categories: labels,
                            labels: {
                                style: { fontSize: '9px', fontWeight: 'bold', colors: Array(labels.length).fill('#94a3b8') }
                            }
                        },
                        yaxis: { min: 0, max: 100, tickAmount: 5, labels: { show: false } },
                        fill: { opacity: 0.4 },
                        markers: { size: 4, strokeWidth: 2, colors: [colors.indigo] },
                        plotOptions: { radar: { polygons: { strokeColors: '#e2e8f0', connectorColors: '#e2e8f0' } } }
                    }).render();
                }

                if (chartEl5 && $wire.summary) {
                    chartEl5.innerHTML = '';
                    const s = $wire.summary;
                    const fg2Pts = (s.fg2_total || 0) * 2;
                    const fg3Pts = (s.fg3_total || 0) * 3;
                    const ftPts = (s.ft_total || 0);

                    if (fg2Pts + fg3Pts + ftPts > 0) {
                        new ApexCharts(chartEl5, {
                            series: [fg2Pts, fg3Pts, ftPts],
                            labels: ['2B Body', '3B Body', 'TH Body'],
                            chart: { type: 'donut', height: '100%' },
                            colors: [colors.orange, colors.violet, colors.pink],
                            legend: { position: 'bottom', fontSize: '10px', fontWeight: 'bold' },
                            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Celkem', formatter: () => s.pts_total } } } } },
                            dataLabels: { enabled: false }
                        }).render();
                    } else {
                        chartEl5.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-[10px] italic">Žádná data o střelbě</div>';
                    }
                }

                console.log('initPersonalCharts finished');
            } catch (e) {
                console.error('initPersonalCharts error:', e);
            }
        };

        const initTeamCharts = () => {
            console.log('initTeamCharts starting');
            if (typeof ApexCharts === 'undefined') return;

            try {
                // 1. Team Evolution (Area)
                const seriesData = $wire.pointsSeries;
                const chartEl1 = document.querySelector("#team-evolution-chart");
                if (chartEl1 && seriesData && seriesData.length > 0) {
                    chartEl1.innerHTML = '';
                    const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
                    const ptsFor = seriesData.map(m => m.pts_for || 0);
                    const ptsAgainst = seriesData.map(m => m.pts_against || 0);

                    new ApexCharts(chartEl1, {
                        series: [
                            { name: 'My', data: ptsFor },
                            { name: 'Soupeř', data: ptsAgainst }
                        ],
                        chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: true } },
                        colors: [colors.primary, colors.blue],
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
                        stroke: { curve: 'smooth', width: 2 },
                        xaxis: { categories: dates, labels: { show: false } },
                        yaxis: { min: 0 },
                        dataLabels: { enabled: false }
                    }).render();
                }

                // 2. Points Distribution (Donut)
                const distribution = $wire.pointsContribution;
                const chartEl2 = document.querySelector("#points-distribution-chart");
                if (chartEl2 && distribution && distribution.values && distribution.values.length > 0) {
                    chartEl2.innerHTML = '';
                    new ApexCharts(chartEl2, {
                        series: distribution.values,
                        labels: distribution.labels,
                        chart: { type: 'donut', height: '100%' },
                        colors: [colors.primary, colors.blue, colors.orange, colors.indigo, colors.violet, colors.gray],
                        legend: { position: 'bottom', fontSize: '10px', fontWeight: 'bold' },
                        plotOptions: { pie: { donut: { size: '65%' } } },
                        dataLabels: { enabled: false }
                    }).render();
                }

                // 3. Efficiency Comparison (Bar)
                const efComp = $wire.efficiencyComparison;
                const chartEl3 = document.querySelector("#efficiency-comparison-chart");
                if (chartEl3 && efComp && efComp.length > 0) {
                    chartEl3.innerHTML = '';
                    new ApexCharts(chartEl3, {
                        series: [{ name: 'VAL Ø', data: efComp.map(p => p.efficiency_avg) }],
                        chart: { type: 'bar', height: '100%', toolbar: { show: false }, zoom: { enabled: false } },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
                        colors: [colors.orange],
                        xaxis: { categories: efComp.map(p => p.name), labels: { show: false } },
                        dataLabels: { enabled: true, formatter: (val) => val, style: { fontSize: '10px' } }
                    }).render();
                }

                console.log('initTeamCharts finished');
            } catch (e) {
                console.error('initTeamCharts error:', e);
            }
        };

        const initMatchesCharts = () => {
            console.log('initMatchesCharts starting');
            if (typeof ApexCharts === 'undefined') return;

            try {
                const seriesData = $wire.pointsSeries;
                if (!seriesData || seriesData.length === 0) return;

                const matchStats = $wire.matchStats;

                const chartEl1 = document.querySelector("#team-points-series-chart");
                const chartEl2 = document.querySelector("#team-match-margin-chart");
                const chartEl3 = document.querySelector("#home-away-success-chart");
                const chartEl4 = document.querySelector("#home-away-points-chart");
                const chartEl5 = document.querySelector("#team-win-loss-donut-chart");

                if (chartEl1) {
                    chartEl1.innerHTML = '';
                    const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
                    const ptsFor = seriesData.map(m => m.pts_for);
                    const ptsAgainst = seriesData.map(m => m.pts_against);
                    const opponents = seriesData.map(m => m.opponent);

                    new ApexCharts(chartEl1, {
                        series: [
                            { name: 'Dáno', data: ptsFor },
                            { name: 'Obdrženo', data: ptsAgainst }
                        ],
                        chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                        stroke: { curve: 'smooth', width: [4, 2] },
                        colors: [colors.primary, colors.gray],
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0 }
                        },
                        xaxis: { categories: dates, labels: { show: false } },
                        yaxis: { min: 0 },
                        legend: { position: 'top', fontSize: '10px', fontWeight: 'bold' },
                        tooltip: {
                            y: { formatter: (val, { seriesIndex, dataPointIndex }) => `${val} bodů vs ${opponents[dataPointIndex]}` }
                        }
                    }).render();
                }

                if (chartEl2) {
                    chartEl2.innerHTML = '';
                    const margins = seriesData.map(m => m.pts_for - m.pts_against);
                    const opponents = seriesData.map(m => m.opponent);

                    new ApexCharts(chartEl2, {
                        series: [{ name: 'Bodový rozdíl', data: margins }],
                        chart: { type: 'bar', height: 320, toolbar: { show: false }, zoom: { enabled: false } },
                        plotOptions: {
                            bar: {
                                colors: {
                                    ranges: [
                                        { from: -100, to: -1, color: colors.rose },
                                        { from: 0, to: 100, color: colors.emerald }
                                    ]
                                },
                                borderRadius: 4
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (val) => (val > 0 ? '+' : '') + val,
                            offsetY: -20,
                            style: { fontSize: '9px', fontWeight: 'bold', colors: ["#304758"] }
                        },
                        xaxis: { categories: opponents, labels: { show: false } },
                        yaxis: {
                            title: { text: 'Bodový rozdíl', style: { fontSize: '10px' } },
                            labels: { formatter: (val) => (val > 0 ? '+' : '') + val }
                        },
                        tooltip: {
                            y: { formatter: (val) => (val > 0 ? 'Výhra o ' : 'Prohra o ') + Math.abs(val) + ' bodů' }
                        }
                    }).render();
                }

                if (chartEl3 && matchStats) {
                    chartEl3.innerHTML = '';
                    new ApexCharts(chartEl3, {
                        series: [matchStats.home_balance.pct, matchStats.away_balance.pct],
                        chart: { type: 'donut', height: 280 },
                        labels: ['Doma', 'Venku'],
                        colors: [colors.blue, colors.orange],
                        plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Průměr', formatter: () => Math.round((matchStats.home_balance.pct + matchStats.away_balance.pct)/2) + '%' } } } } },
                        legend: { position: 'bottom' }
                    }).render();
                }

                if (chartEl4 && seriesData) {
                    chartEl4.innerHTML = '';
                    const homeMatches = seriesData.filter(m => m.is_home);
                    const awayMatches = seriesData.filter(m => !m.is_home);

                    const avgHomeFor = homeMatches.length > 0 ? Math.round(homeMatches.reduce((acc, m) => acc + m.pts_for, 0) / homeMatches.length * 10) / 10 : 0;
                    const avgAwayFor = awayMatches.length > 0 ? Math.round(awayMatches.reduce((acc, m) => acc + m.pts_for, 0) / awayMatches.length * 10) / 10 : 0;
                    const avgHomeAgainst = homeMatches.length > 0 ? Math.round(homeMatches.reduce((acc, m) => acc + m.pts_against, 0) / homeMatches.length * 10) / 10 : 0;
                    const avgAwayAgainst = awayMatches.length > 0 ? Math.round(awayMatches.reduce((acc, m) => acc + m.pts_against, 0) / awayMatches.length * 10) / 10 : 0;

                    new ApexCharts(chartEl4, {
                        series: [
                            { name: 'Dáno Ø', data: [avgHomeFor, avgAwayFor] },
                            { name: 'Dostáno Ø', data: [avgHomeAgainst, avgAwayAgainst] }
                        ],
                        chart: { type: 'bar', height: 280, toolbar: { show: false } },
                        xaxis: { categories: ['Doma', 'Venku'] },
                        colors: [colors.emerald, colors.rose],
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
                        dataLabels: { enabled: true, style: { fontSize: '10px' } }
                    }).render();
                }

                if (chartEl5 && matchStats) {
                    chartEl5.innerHTML = '';
                    const wins = matchStats.home_balance.wins + matchStats.away_balance.wins;
                    const losses = matchStats.home_balance.losses + matchStats.away_balance.losses;

                    new ApexCharts(chartEl5, {
                        series: [wins, losses],
                        chart: { type: 'donut', height: 280 },
                        labels: ['Výhry', 'Prohry'],
                        colors: [colors.emerald, colors.rose],
                        plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Zápasy', formatter: () => wins + losses } } } } },
                        legend: { position: 'bottom' }
                    }).render();
                }

                console.log('initMatchesCharts finished');
            } catch (e) {
                console.error('initMatchesCharts error:', e);
            }
        };

        const initCareerCharts = () => {
            console.log('initCareerCharts starting');
            if (typeof ApexCharts === 'undefined') return;

            try {
                const history = $wire.careerHistory;
                if (!history || history.length === 0) return;

                const seasons = history.map(h => h.season);
                const ppg = history.map(h => h.ppg);
                const efficiency = history.map(h => h.efficiency_avg);
                const gp = history.map(h => h.gp);

                // 1. Performance Chart (PPG + Efficiency)
                const chartEl1 = document.querySelector("#career-performance-chart");
                if (chartEl1) {
                    chartEl1.innerHTML = '';
                    new ApexCharts(chartEl1, {
                        series: [
                            { name: 'Body / Zápas', type: 'column', data: ppg },
                            { name: 'Užitečnost Ø', type: 'line', data: efficiency }
                        ],
                        chart: {
                            height: 350,
                            type: 'line',
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 800 },
                            fontFamily: 'inherit'
                        },
                        stroke: { width: [0, 4], curve: 'smooth' },
                        colors: [colors.primary, colors.emerald],
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [0],
                            style: { fontSize: '10px', fontWeight: 'bold', colors: ['#fff'] },
                            offsetY: -5
                        },
                        labels: seasons,
                        xaxis: {
                            type: 'category',
                            labels: {
                                rotate: -45,
                                rotateAlways: false,
                                style: { fontSize: '10px', fontWeight: 'bold', colors: '#94a3b8' }
                            }
                        },
                        yaxis: [
                            { title: { text: 'Body / Zápas', style: { color: colors.primary, fontWeight: 900 } } },
                            { opposite: true, title: { text: 'Užitečnost', style: { color: colors.emerald, fontWeight: 900 } } }
                        ],
                        legend: { position: 'top', horizontalAlign: 'left', fontSize: '10px', fontWeight: 'bold' },
                        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                        tooltip: { shared: true, intersect: false }
                    }).render();
                }

                // 2. Games Played Chart
                const chartEl2 = document.querySelector("#career-gp-chart");
                if (chartEl2) {
                    chartEl2.innerHTML = '';
                    new ApexCharts(chartEl2, {
                        series: [{ name: 'Odehrané zápasy', data: gp }],
                        chart: { height: 350, type: 'area', toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
                        dataLabels: { enabled: true, style: { fontSize: '10px', fontWeight: 'bold' } },
                        stroke: { curve: 'stepline', width: 3 },
                        colors: [colors.orange],
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                        xaxis: { categories: seasons, labels: { style: { fontSize: '10px', fontWeight: 'bold', colors: '#94a3b8' } } },
                        yaxis: { min: 0, title: { text: 'Počet zápasů', style: { color: colors.orange, fontWeight: 900 } } },
                        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                    }).render();
                }
            } catch (e) {
                console.error('initCareerCharts error:', e);
            }
        };

        const initCharts = () => {
            if ($wire.view === 'personal') initPersonalCharts();
            else if ($wire.view === 'team') initTeamCharts();
            else if ($wire.view === 'matches') initMatchesCharts();
            else if ($wire.view === 'career') initCareerCharts();
        };

        // Initialize based on initial view
        setTimeout(() => {
            initCharts();
        }, 100);

        // Re-initialize on Livewire updates
        $wire.on('statsLoaded', () => {
            console.log('statsLoaded event received');
            window.dispatchEvent(new CustomEvent('loading-stop'));
            setTimeout(() => {
                initCharts();
            }, 50);
        });
    </script>
    @endscript
</div>
