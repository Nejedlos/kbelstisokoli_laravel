@props(['match'])

@php
    $prediction = $match->prediction;
    $teamComparison = $match->metadata['team_comparison'] ?? [];
    $lastMatches = $match->metadata['last_matches'] ?? [];
    $mutualMatches = $match->metadata['mutual_matches'] ?? [];
    $bestPlayers = $match->metadata['best_players'] ?? [];
    $hasScore = !is_null($match->score_home) && !is_null($match->score_away);

    $colorHsl = null;
    $bgHsl = null;
    $glowHsl = null;
    $borderHsl = null;
    $winChance = null;

    if ($prediction) {
        $winChance = round($prediction->probability_win * 100);
        // Dynamický výpočet barvy (0% = červená, 50% = oranžová, 100% = zelená)
        if ($winChance <= 50) {
            $hue = ($winChance / 50) * 35; // 0-35 (red to orange-ish)
        } else {
            $hue = 35 + (($winChance - 50) / 50) * (105); // 35-140 (orange to success green)
        }
        $colorHsl = "hsl({$hue}, 75%, 45%)";
        $bgHsl = "hsla({$hue}, 75%, 45%, 0.05)";
        $glowHsl = "hsla({$hue}, 75%, 45%, 0.2)";
        $borderHsl = "hsla({$hue}, 75%, 45%, 0.15)";
    }
@endphp

<div
    x-show="predictionOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
    x-cloak
>
    <!-- Backdrop -->
    <div
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
        @click="predictionOpen = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="predictionOpen"
        x-transition:enter="transition ease-out duration-500 transform"
        x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
        class="relative w-full max-w-[740px] bg-white/98 backdrop-blur-3xl rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] overflow-hidden border border-white/40 flex flex-col max-h-[92vh] sm:max-h-[90vh]"
    >
        <!-- Header -->
        <div class="relative px-6 sm:px-8 py-6 bg-secondary text-white shrink-0 overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-primary/20 rounded-full -mr-24 -mt-24 blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-primary/10 rounded-full -ml-16 -mb-16 blur-2xl"></div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20 shadow-xl backdrop-blur-md transition-colors {{ !$colorHsl ? 'text-brand-400' : '' }}"
                        @if($colorHsl) style="color: {{ $colorHsl }};" @endif
                    >
                        <i class="fa-light fa-crystal-ball text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-tight leading-none mb-1">
                            {{ $hasScore ? (__('matches.prediction.title_past') ?? 'Předzápasová predikce') : (__('matches.prediction.title') ?? 'Předzápasová predikce') }}
                        </h3>
                        <p class="text-[10px] font-black uppercase tracking-widest {{ !$colorHsl ? 'text-brand-400' : '' }}" @if($colorHsl) style="color: {{ $colorHsl }}; opacity: 0.8;" @endif>{{ $match->team->name }} vs {{ $match->opponent?->name }}</p>
                    </div>
                </div>

                <button @click="predictionOpen = false" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-all text-white/70 hover:text-white">
                    <i class="fa-light fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto custom-scrollbar p-6 sm:p-8 space-y-8 bg-slate-50/30">
            @if($prediction)
                <div class="space-y-8">
                    <!-- Probability Section -->
                    <div class="relative p-6 sm:p-8 bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700 opacity-50" style="background-color: {{ $bgHsl }};"></div>

                        <div class="flex flex-col items-center gap-6 relative">
                            <div class="text-center">
                                <div class="text-6xl font-black mb-2 tabular-nums drop-shadow-sm" style="color: {{ $colorHsl }};">
                                    {{ $winChance }}%
                                </div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                    {{ __('matches.prediction.win_chance') ?? 'Šance na výhru' }}
                                </div>
                            </div>

                            <div class="w-full">
                                <div class="overflow-hidden h-5 flex rounded-full bg-slate-100 shadow-inner p-1">
                                    <div
                                        style="width:{{ $prediction->probability_win * 100 }}%; background-color: {{ $colorHsl }}; box-shadow: 0 0 15px {{ $glowHsl }};"
                                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center rounded-full transition-all duration-1000 animate-pulse"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-[10px] font-black text-slate-400 uppercase tracking-widest mt-3 px-1">
                                    <span>{{ $match->team->name }}</span>
                                    <span>{{ $match->opponent?->name }}</span>
                                </div>
                            </div>

                            @php
                                $confColor = match($prediction->confidence) {
                                    'high' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'medium' => 'bg-amber-50 text-amber-700 border-amber-100',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <div class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $confColor }} shadow-sm">
                                {{ __('matches.prediction.confidence_label') ?? 'Důvěra' }}: {{ __('matches.prediction.confidence_' . $prediction->confidence) ?? $prediction->confidence }}
                            </div>
                        </div>
                    </div>

                    <!-- Motivational Quote -->
                    <div class="p-6 bg-secondary rounded-[2rem] text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                        <i class="fa-light fa-quote-left text-primary/20 text-2xl mb-3 block"></i>
                        <p class="text-white font-black italic relative z-10 leading-snug">
                            "{{ $match->motivational_quote }}"
                        </p>
                    </div>

                    <!-- Why section -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 px-1">
                            <i class="fa-light fa-magnifying-glass-chart" style="color: {{ $colorHsl }};"></i>
                            {{ __('matches.prediction.why_title') ?? 'Proč si to myslíme' }}
                        </h4>
                        <ul class="grid grid-cols-1 gap-3">
                            @foreach($prediction->explanation_points as $point)
                                <li class="flex items-start gap-3 p-4 bg-white rounded-2xl border border-slate-100 text-sm text-slate-600 leading-relaxed shadow-sm hover:shadow-md transition-shadow">
                                    <i class="fa-light fa-circle-check mt-0.5 shrink-0" style="color: {{ $colorHsl }};"></i>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(!empty($bestPlayers))
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 px-1">
                        <i class="fa-light fa-star text-brand-500"></i>
                        {{ __('matches.prediction.best_players_title') ?? 'Nejlepší hráči' }}
                    </h4>

                    <div class="space-y-6">
                        @foreach($bestPlayers as $categoryKey => $categoryData)
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] px-4">{{ $categoryData['label'] ?? $categoryKey }}</span>
                                    <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach(['home', 'away'] as $side)
                                        @php
                                            $player = $categoryData[$side] ?? null;
                                            $isHome = $side === 'home';
                                        @endphp
                                        @if($player)
                                            <div class="group relative flex items-center gap-4 p-3 rounded-[2rem] {{ $isHome ? 'bg-brand-50/50 border-brand-100' : 'bg-slate-50 border-slate-100' }} border-2 transition-all hover:shadow-xl hover:bg-white hover:-translate-y-1 overflow-hidden">
                                                @if($isHome)
                                                    <div class="absolute top-0 right-0 w-24 h-24 bg-brand-500/5 rounded-full -mr-8 -mt-8"></div>
                                                @endif

                                                <div class="relative flex-shrink-0">
                                                    @php
                                                        $pPhotoUrl = $player['photo_url'] ?? null;
                                                        $pExtId = $player['external_id'] ?? null;

                                                        // Zkusíme najít lokální fotku soupeře na disku
                                                        if (!empty($pExtId)) {
                                                            $oppPath = config('filesystems.uploads.dir', 'uploads') . '/opponents/' . $pExtId . '.jpg';
                                                            $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.uploads.disk', 'public_path'));
                                                            if ($disk->exists($oppPath)) {
                                                                $pPhotoUrl = asset($oppPath);
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="w-14 h-14 rounded-2xl overflow-hidden bg-white shadow-md group-hover:scale-110 transition-transform duration-500 border border-white relative">
                                                        @if(!empty($pPhotoUrl))
                                                            <img src="{{ $pPhotoUrl }}" alt="{{ $player['name'] }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <div class="hidden w-full h-full items-center justify-center bg-slate-100 text-slate-300">
                                                                <i class="fa-light fa-user text-xl"></i>
                                                            </div>
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-300">
                                                                <i class="fa-light fa-user text-xl"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full {{ $isHome ? 'bg-brand-500' : 'bg-slate-400' }} text-white flex items-center justify-center text-[10px] shadow-lg border-2 border-white">
                                                        <i class="fa-solid fa-crown"></i>
                                                    </div>
                                                </div>

                                                <div class="flex-grow min-w-0 relative">
                                                    <span class="px-2 py-0.5 {{ $isHome ? 'bg-brand-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[8px] font-black rounded uppercase tracking-widest mb-1 inline-block whitespace-nowrap">
                                                        {{ $isHome ? ($match->team->name) : ($match->opponent?->name ?? 'Soupeř') }}
                                                    </span>
                                                    <span class="text-sm font-black text-slate-900 leading-tight block group-hover:text-brand-600 transition-colors truncate">{{ $player['name'] }}</span>
                                                </div>

                                                <div class="flex-shrink-0 text-right relative">
                                                    <div class="flex flex-col items-end">
                                                        <span class="text-xl font-black text-brand-600 tabular-nums leading-none mb-1">{{ $player['value'] }}</span>
                                                        <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest">HODNOTA</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($teamComparison))
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 px-1">
                        <i class="fa-light fa-scale-balanced text-brand-500"></i>
                        {{ __('matches.team_comparison') ?? 'Srovnání kádrů' }}
                    </h4>

                    <div class="grid grid-cols-1 gap-3">
                        @foreach($teamComparison as $key => $data)
                            <div class="bg-white rounded-[2rem] p-4 border border-slate-100 shadow-sm group hover:border-brand-100 transition-all relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-20 h-20 bg-brand-500/5 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-700"></div>

                                <div class="text-center relative mb-4">
                                    <span class="inline-block px-4 py-1.5 bg-slate-50 border border-slate-100 rounded-full text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] group-hover:text-brand-500 transition-colors leading-tight">
                                        {{ $data['label'] ?? $key }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 relative px-4">
                                    <div class="text-center flex-1 min-w-0">
                                        <span class="text-xl sm:text-2xl font-black text-brand-600 tabular-nums block break-words">
                                            {{ $data['home'] }}
                                        </span>
                                    </div>

                                    <div class="flex flex-col items-center gap-1 shrink-0 px-1 opacity-20">
                                        <div class="h-4 w-px bg-slate-400"></div>
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
                                        <div class="h-4 w-px bg-slate-400"></div>
                                    </div>

                                    <div class="text-center flex-1 min-w-0">
                                        <span class="text-xl sm:text-2xl font-black text-slate-900 tabular-nums block break-words">
                                            {{ $data['away'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($lastMatches['home']) || !empty($lastMatches['away']))
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 px-1">
                        <i class="fa-light fa-chart-line text-brand-500"></i>
                        {{ __('matches.prediction.form_title') ?? 'Předzápasová bilance (forma)' }}
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['home', 'away'] as $side)
                            @php
                                $sideMatches = array_slice($lastMatches[$side] ?? [], 0, 5);
                                $teamName = $side === 'home' ? $match->team->name : ($match->opponent?->name ?? 'Soupeř');
                            @endphp
                            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $teamName }}</span>
                                    <div class="flex gap-1.5">
                                        @foreach($sideMatches as $m)
                                            @php
                                                $res = \App\Support\MatchResultHelper::for($m, $teamName);
                                            @endphp
                                            <div
                                                class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-black text-white shadow-sm {{ $res['bgColor'] }}"
                                                title="{{ $m['team_home'] }} vs {{ $m['team_away'] }} ({{ $m['score_home'] }}:{{ $m['score_away'] }})"
                                            >
                                                {{ $res['resultLetter'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    @foreach($sideMatches as $m)
                                        @php
                                            $res = \App\Support\MatchResultHelper::for($m, $teamName);
                                        @endphp
                                        <div class="flex items-center justify-between text-[11px] py-1 border-b border-slate-50 last:border-0">
                                            <span class="text-slate-500 font-bold truncate pr-4 max-w-[140px]">{{ str_contains(mb_strtolower($m['team_home']), mb_strtolower($teamName)) ? $m['team_away'] : $m['team_home'] }}</span>
                                            <span class="font-black tabular-nums {{ $res['textColor'] }}">
                                                {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($mutualMatches))
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 px-1">
                        <i class="fa-light fa-swords text-brand-500"></i>
                        {{ __('matches.prediction.mutual_matches_title') ?? 'Vzájemné zápasy' }}
                    </h4>

                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[11px]">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-5 py-3">{{ __('matches.date_time') }}</th>
                                        <th class="px-5 py-3">{{ __('matches.prediction.h2h_match') }}</th>
                                        <th class="px-5 py-3 text-center">{{ __('matches.score') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($mutualMatches as $m)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap text-slate-500 font-bold italic">{{ $m['date'] }}</td>
                                            <td class="px-5 py-4">
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="font-black text-slate-900">{{ $m['team_home'] }}</span>
                                                    <span class="font-black text-slate-900">{{ $m['team_away'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <div class="inline-flex items-center justify-center px-3 py-1 bg-slate-100 rounded-lg font-black tabular-nums text-slate-700">
                                                    {{ $m['score_home'] }}:{{ $m['score_away'] }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Methodology Footer -->
            <div class="pt-4 border-t border-slate-100">
                <div class="p-5 bg-white/50 rounded-2xl border border-slate-100/50 text-[11px] text-slate-500 leading-relaxed space-y-2 italic">
                    <p>{{ __('matches.prediction.methodology_desc') ?? 'Predikce je založena na kombinaci Elo ratingu (dlouhodobá síla), aktuální formy (posledních 5 zápasů) a síly kádru. Výpočet se zpřesňuje s rostoucím množstvím dat.' }}</p>
                    <p class="text-[10px] font-bold text-brand-500/70">{{ __('matches.prediction.disclaimer') ?? 'Jde o matematický model, nikoliv záruku výsledku. Basketbal je nevyzpytatelný!' }}</p>
                </div>
            </div>
        </div>

        <!-- Sticky Footer CTA -->
        <div class="p-6 bg-white border-t border-slate-100 shrink-0 flex justify-center">
            <button
                @click="predictionOpen = false"
                class="btn btn-secondary w-full sm:w-auto px-12 py-3 text-xs uppercase tracking-widest font-black shadow-lg shadow-slate-200"
            >
                {{ __('matches.prediction.close') }}
            </button>
        </div>
    </div>
</div>
