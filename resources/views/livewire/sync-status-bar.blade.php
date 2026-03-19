<div wire:poll.{{ $pollingInterval }} @sync-started.window="$wire.$refresh()">
    @if($runs->isNotEmpty())
        <div x-data="{
                 collapsed: @entangle('isCollapsed'),
                 init() {
                     const stored = sessionStorage.getItem('sync_bar_collapsed');
                     if (stored !== null) {
                         this.collapsed = stored === 'true';
                     }
                 },
                 toggle() {
                     this.collapsed = !this.collapsed;
                     sessionStorage.setItem('sync_bar_collapsed', this.collapsed);
                 }
             }"
             class="fixed top-0 left-0 right-0 z-[10000] flex flex-col gap-1 pointer-events-none p-2 transition-all duration-500 ease-in-out origin-top"
             :class="collapsed ? 'translate-y-[-88%] opacity-40 scale-[0.98] hover:translate-y-[-82%] hover:opacity-100 hover:scale-100' : 'translate-y-0 opacity-100 scale-100'">

            {{-- Ovládací handle --}}
            <div class="absolute top-full left-1/2 -translate-x-1/2 pointer-events-auto mt-[-4px]">
                <button @click="toggle()"
                        class="group bg-slate-900/90 backdrop-blur-xl border border-white/10 text-white/50 hover:text-white px-6 py-1.5 rounded-b-2xl text-[9px] font-black uppercase tracking-[0.3em] transition-all shadow-[0_10px_30px_rgba(0,0,0,0.4)] flex items-center gap-3 ring-1 ring-white/5">
                    <template x-if="!collapsed">
                        <span class="flex items-center gap-2"><i class="fa-light fa-chevron-up text-rose-500 transition-transform group-hover:-translate-y-0.5"></i> SCHOVAT PANEL</span>
                    </template>
                    <template x-if="collapsed">
                        <span class="flex items-center gap-2 animate-pulse text-rose-400 font-bold"><i class="fa-light fa-chevron-down text-rose-500 transition-transform group-hover:translate-y-0.5"></i> ZOBRAZIT PRŮBĚH ({{ $runs->count() }})</span>
                    </template>
                </button>
            </div>

            @foreach($runs as $run)
                <div class="pointer-events-auto bg-slate-900/95 backdrop-blur-3xl border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.6)] rounded-3xl px-6 py-5 flex items-center justify-between text-white max-w-4xl mx-auto w-full animate-in fade-in slide-in-from-top-4 duration-700 ring-1 ring-white/5 hover:bg-slate-900 transition-colors">
                    <div class="flex items-center gap-6">
                        {{-- Mini Basketball Animation --}}
                        <div class="relative w-14 h-14 flex items-center justify-center bg-white/5 rounded-2xl border border-white/10 shrink-0 shadow-inner">
                            <div class="text-rose-500 animate-bounce">
                                <i class="fa-light fa-basketball text-2xl"></i>
                            </div>
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-5 h-1 bg-black/60 rounded-full blur-[3px] animate-pulse"></div>
                        </div>

                        <div>
                            <div class="text-[10px] font-black uppercase tracking-[0.25em] text-rose-400 mb-1 flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                </span>
                                @if(str_contains($run->run_type, 'player'))
                                    Synchronizace hráčů
                                @elseif(str_contains($run->run_type, 'team'))
                                    Synchronizace týmu
                                @elseif(str_contains($run->run_type, 'match'))
                                    Synchronizace zápasu
                                @elseif(str_contains($run->run_type, 'batch_recent'))
                                    Rychlá synchronizace (Recent)
                                @elseif(str_contains($run->run_type, 'batch_baseline'))
                                    Kompletní synchronizace (Baseline)
                                @else
                                    Synchronizace dat
                                @endif
                            </div>
                            <div class="text-sm font-black text-white tracking-tight truncate max-w-[300px] sm:max-w-[500px] leading-tight" title="{{ $run->current_item_label }}">
                                {{ $run->current_item_label ?: 'Inicializace procesu...' }}
                            </div>
                            <div class="text-[9px] text-white/30 font-mono mt-0.5 flex items-center gap-2">
                                <span>Aktivita: {{ $run->updated_at->format('H:i:s') }}</span>
                                @if($run->updated_at->diffInMinutes(now()) > 2)
                                    <span class="text-rose-500 animate-pulse font-bold">Možná zaseknuto!</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2 w-36 sm:w-72 ml-4">
                        <div class="flex justify-between w-full text-[11px] font-black font-mono tracking-tighter items-center">
                            <span class="text-white/40">{{ $run->imported_count }}<span class="text-white/20 mx-1">/</span>{{ $run->total_count ?: '?' }}</span>
                            <div class="flex items-center gap-3">
                                @if(in_array($run->run_type, ['player_detail', 'match_detail', 'team_page']))
                                    <button
                                        wire:click="skipRun({{ $run->id }})"
                                        class="text-white/20 hover:text-amber-500 transition-colors flex items-center gap-1 group/skip"
                                        title="Přeskočit tuto položku a pokračovat dále"
                                    >
                                        <i class="fa-light fa-forward-step text-sm"></i>
                                        <span class="hidden group-hover/skip:inline text-[9px] uppercase tracking-wider">Přeskočit</span>
                                    </button>
                                @endif

                                <button
                                    wire:click="cancelRun({{ $run->id }})"
                                    wire:confirm="Opravdu chcete přerušit tento proces?"
                                    class="text-white/20 hover:text-rose-500 transition-colors flex items-center gap-1 group/btn"
                                    title="Přerušit proces"
                                >
                                    <i class="fa-light fa-circle-xmark text-sm"></i>
                                    <span class="hidden group-hover/btn:inline text-[9px] uppercase tracking-wider">Zrušit</span>
                                </button>
                                <span class="text-rose-400">{{ number_format($run->progress_percent, 1) }}%</span>
                            </div>
                        </div>
                        <div class="w-full h-2.5 bg-white/5 rounded-full overflow-hidden border border-white/10 p-[2px] shadow-inner">
                            <div class="h-full bg-gradient-to-r from-rose-600 via-rose-400 to-rose-600 bg-[length:200%_auto] animate-gradient transition-all duration-1000 ease-out rounded-full shadow-[0_0_15px_rgba(244,63,94,0.4)]"
                                 style="width: {{ max(2, $run->progress_percent) }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <style>
            @keyframes gradient {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            .animate-gradient {
                animation: gradient 3s ease infinite;
            }
        </style>
    @endif
</div>
