<div class="space-y-6">
    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/40 backdrop-blur-md p-4 rounded-3xl border border-slate-200/60 shadow-sm">
        <div class="space-y-1">
            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Sezóna</label>
            <select wire:model.live="seasonId" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-2 text-sm font-bold focus:ring-primary focus:border-primary outline-none transition-all">
                @foreach($seasons as $season)
                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Náš tým</label>
            <select wire:model.live="teamId" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-2 text-sm font-bold focus:ring-primary focus:border-primary outline-none transition-all">
                <option value="">{{ __('member.profile.section_settings.view_all_short') }}</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Standings Table -->
    @if($groupedStandings->isEmpty())
        <div class="bg-white/40 backdrop-blur-md p-10 rounded-3xl border border-slate-200/60 shadow-sm text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-light fa-table-list text-2xl text-slate-300"></i>
            </div>
            <h3 class="text-lg font-black uppercase tracking-tight text-secondary mb-1">Žádná data k zobrazení</h3>
            <p class="text-sm text-slate-500 italic">Pro vybrané filtry nebyla nalezena žádná tabulka pořadí.</p>
        </div>
    @else
        @foreach($groupedStandings as $url => $rows)
            @php
                $competitionName = $rows->first()->competition_name ?: 'Soutěž';
                // Pokusíme se najít, zda v této tabulce je náš vybraný tým (pro zvýraznění)
                $activeTeam = $teamId ? \App\Models\Team::find($teamId) : null;
                $activeTeamNameSuffix = $activeTeam ? (preg_match('/\b([a-gA-G])\b/', mb_strtolower($activeTeam->name), $m) ? strtoupper($m[1]) : null) : null;
            @endphp
            <div class="bg-white/60 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl overflow-hidden animate-fade-in">
                <div class="bg-slate-900/5 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-widest text-secondary flex items-center gap-3">
                        <i class="fa-light fa-trophy text-primary"></i>
                        {{ $competitionName }}
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                <th class="px-6 py-4 text-center w-16">#</th>
                                <th class="px-6 py-4">Tým</th>
                                <th class="px-4 py-4 text-center">Z</th>
                                <th class="px-4 py-4 text-center">V</th>
                                <th class="px-4 py-4 text-center">P</th>
                                <th class="px-4 py-4 text-center">Skóre</th>
                                <th class="px-6 py-4 text-center">Body</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($rows as $row)
                                @php
                                    $isOurTeam = false;
                                    $rowTeamLower = mb_strtolower($row->team_name);
                                    if (str_contains($rowTeamLower, 'kbely')) {
                                        if (!$activeTeamNameSuffix) {
                                            $isOurTeam = true;
                                        } else {
                                            $isOurTeam = str_ends_with($rowTeamLower, ' ' . mb_strtolower($activeTeamNameSuffix))
                                                       || str_contains($rowTeamLower, 'kbely ' . mb_strtolower($activeTeamNameSuffix));
                                        }
                                    }
                                @endphp
                                <tr class="group transition-colors {{ $isOurTeam ? 'bg-primary/5' : 'hover:bg-slate-50/50' }}">
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black {{ $isOurTeam ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 text-secondary' }}">
                                            {{ $row->rank }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm {{ $isOurTeam ? 'font-black text-primary' : 'font-bold text-secondary' }}">
                                                {{ $row->team_name }}
                                            </span>
                                            @if($isOurTeam)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[8px] font-black uppercase tracking-widest">
                                                    Náš tým
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center font-bold text-slate-600 text-sm">{{ $row->gp }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-emerald-600 text-sm">{{ $row->w }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-rose-600 text-sm">{{ $row->l }}</td>
                                    <td class="px-4 py-4 text-center font-medium text-slate-500 text-xs tracking-tighter">{{ $row->score }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-secondary">{{ $row->points }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
