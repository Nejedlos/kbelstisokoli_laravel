<div class="space-y-8">
    @if($showFilters)
        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/40 backdrop-blur-md p-4 rounded-3xl border border-slate-200/60 shadow-sm mb-8">
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">{{ __('general.season') ?? 'Sezóna' }}</label>
                <select wire:model.live="seasonId" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-2 text-sm font-bold focus:ring-primary focus:border-primary outline-none transition-all">
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">{{ __('general.our_team') ?? 'Náš tým' }}</label>
                <select wire:model.live="teamId" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-2 text-sm font-bold focus:ring-primary focus:border-primary outline-none transition-all">
                    <option value="">{{ __('general.view_all') ?? 'Všechny týmy' }}</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($groupedStandings->isEmpty())
        <div class="bg-white/40 backdrop-blur-md p-10 rounded-3xl border border-slate-200/60 shadow-sm text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-light fa-table-list text-2xl text-slate-300"></i>
            </div>
            <h3 class="text-lg font-black uppercase tracking-tight text-secondary mb-1">{{ __('general.no_standings') }}</h3>
            <p class="text-sm text-slate-500 italic">{{ __('general.no_standings_desc') }}</p>
        </div>
    @else
        @foreach($groupedStandings as $url => $rows)
            @php
                $competitionName = $rows->first()->competition_name;
                if (!$competitionName || $competitionName === 'Základní informace' || $competitionName === 'Competition Standing') {
                    $competitionName = __('general.competition') ?? 'Soutěž';
                }

                $activeTeam = $teamId ? \App\Models\Team::find($teamId) : null;
                $activeTeamNameSuffix = $activeTeam ? (preg_match('/\b([a-gA-G])\b/', mb_strtolower($activeTeam->name), $m) ? strtoupper($m[1]) : null) : null;

                // Najdeme index našeho týmu v kolekci $rows
                $ourTeamIndex = null;
                foreach($rows as $idx => $row) {
                    $rowTeamLower = mb_strtolower($row->team_name);
                    $match = false;
                    if (str_contains($rowTeamLower, 'kbely')) {
                        if (!$activeTeamNameSuffix) {
                            $match = true;
                        } else {
                            $match = str_ends_with($rowTeamLower, ' ' . mb_strtolower($activeTeamNameSuffix))
                                   || str_contains($rowTeamLower, 'kbely ' . mb_strtolower($activeTeamNameSuffix));
                        }
                    }
                    if ($match) {
                        $ourTeamIndex = $idx;
                        break;
                    }
                }

                $isExpanded = $expanded[$url] ?? false;
                $displayRows = $rows; // Bereme vždy všechny, o filtraci se postará Alpine
            @endphp
            <div x-data="{ expanded: false }"
                 class="bg-white/60 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl overflow-hidden animate-fade-in group/table transition-all duration-500">
                <div class="bg-slate-900/[0.02] px-6 py-4 border-b border-slate-100 flex items-center justify-between group-hover/table:bg-slate-900/[0.04] transition-colors">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.15em] text-secondary flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <i class="fa-light fa-trophy text-primary text-xs"></i>
                        </span>
                        {{ $competitionName }}
                    </h3>
                    @if($ourTeamIndex !== null)
                        <div class="flex items-center gap-2">
                             <span class="text-[9px] font-black uppercase tracking-widest text-primary bg-primary/10 px-2 py-1 rounded-full">
                                {{ $rows[$ourTeamIndex]->rank }}{{ __('general.rank_suffix') }}
                             </span>
                        </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                <th class="px-6 py-4 text-center w-16">#</th>
                                <th class="px-6 py-4">{{ __('general.team') ?? 'Tým' }}</th>
                                <th class="px-4 py-4 text-center">{{ $compact ? 'Z' : (__('general.gp') ?? 'Z') }}</th>
                                <th class="px-4 py-4 text-center">{{ $compact ? 'V' : (__('general.w') ?? 'V') }}</th>
                                <th class="px-4 py-4 text-center">{{ $compact ? 'P' : (__('general.l') ?? 'P') }}</th>
                                @if(!$compact)
                                    <th class="px-4 py-4 text-center">{{ __('general.score') ?? 'Skóre' }}</th>
                                @endif
                                <th class="px-6 py-4 text-center">{{ __('general.points') ?? 'Body' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($displayRows as $idx => $row)
                                @php
                                    $isOurTeam = ($ourTeamIndex === $idx);

                                    // Definice "výřezu"
                                    $sliceSize = 2; // Počet řádků nad a pod
                                    $isAlwaysVisible = false;

                                    if ($ourTeamIndex === null) {
                                        // Pokud nemáme náš tým, ukážeme top X
                                        $isAlwaysVisible = $idx < 5;
                                    } else {
                                        // Jinak okno kolem našeho týmu + top 1
                                        $isAlwaysVisible = ($idx === 0) ||
                                                           ($idx >= $ourTeamIndex - $sliceSize && $idx <= $ourTeamIndex + $sliceSize);
                                    }
                                @endphp

                                @if(!$isAlwaysVisible && $idx > 0 && ($ourTeamIndex === null || $idx < $ourTeamIndex - $sliceSize) && $idx === 1)
                                    {{-- Oddělovač mezi top 1 a oknem našeho týmu --}}
                                    <tr x-show="!expanded && {{ $ourTeamIndex > $sliceSize + 1 ? 'true' : 'false' }}" class="bg-slate-50/30">
                                        <td colspan="{{ $compact ? 6 : 7 }}" class="px-6 py-1 text-center text-[10px] text-slate-300 tracking-[1em] font-black">
                                            ...
                                        </td>
                                    </tr>
                                @endif

                                <tr
                                    x-show="expanded || {{ $isAlwaysVisible ? 'true' : 'false' }}"
                                    x-collapse.duration.500ms
                                    class="group transition-all {{ $isOurTeam ? 'bg-primary/[0.05] ring-1 ring-primary/20 ring-inset' : 'hover:bg-slate-50/50' }} {{ !$isAlwaysVisible ? 'animate-fade-in' : '' }}"
                                >
                                    <td class="px-6 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[10px] font-black {{ $isOurTeam ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-secondary group-hover:bg-white transition-colors' }}">
                                            {{ $row->rank }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm {{ $isOurTeam ? 'font-black text-primary' : 'font-bold text-secondary' }}">
                                                {{ $row->team_name }}
                                            </span>
                                            @if($isOurTeam)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary/20 text-primary text-[8px] font-black uppercase tracking-widest">
                                                    {{ __('general.our_team') ?? 'Náš tým' }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-600 text-sm">{{ $row->gp }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-emerald-600 text-sm">{{ $row->w }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-rose-600 text-sm">{{ $row->l }}</td>
                                    @if(!$compact)
                                        <td class="px-4 py-3 text-center font-medium text-slate-400 text-[11px] tracking-tighter">{{ \App\Support\MatchResultHelper::formatScore($row->score) }}</td>
                                    @endif
                                    <td class="px-6 py-3 text-center">
                                        <span class="text-sm font-black text-secondary">{{ $row->points }}</span>
                                    </td>
                                </tr>

                                @if(!$isAlwaysVisible && $idx < $rows->count() - 1 && $ourTeamIndex !== null && $idx === $ourTeamIndex + $sliceSize && $idx < $rows->count() - 1)
                                     {{-- Oddělovač po okně našeho týmu --}}
                                     <tr x-show="!expanded && {{ $ourTeamIndex + $sliceSize < $rows->count() - 1 ? 'true' : 'false' }}" class="bg-slate-50/30">
                                        <td colspan="{{ $compact ? 6 : 7 }}" class="px-6 py-1 text-center text-[10px] text-slate-300 tracking-[1em] font-black">
                                            ...
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($rows->count() > 5)
                     <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 text-center">
                        <button
                            @click="expanded = !expanded"
                            class="group/btn relative inline-flex items-center gap-2 px-6 py-2 rounded-full bg-white border border-slate-200 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-primary hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all"
                        >
                            <span x-text="expanded ? '{{ __('general.show_less') ?? 'Zobrazit méně' }}' : '{{ __('general.show_full_table') ?? 'Zobrazit celou tabulku' }}'"></span>
                            <i class="fa-light transition-transform duration-300" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
