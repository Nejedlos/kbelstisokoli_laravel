<?php

use App\Models\ExternalImportRun;
use function Livewire\Volt\{state, poll};

state([
    'runs' => fn () => ExternalImportRun::where('status', 'running')
        ->whereIn('run_type', ['player_sync_batch', 'player_sync_excesive', 'player_sync_all'])
        ->orderByDesc('started_at')
        ->get()
]);

poll(3);

?>

<div class="fixed top-0 left-0 right-0 z-[10000] flex flex-col gap-1 pointer-events-none p-2">
    @foreach($runs as $run)
        <div class="pointer-events-auto bg-brand-navy/90 backdrop-blur-xl border border-brand-gold/40 shadow-2xl rounded-xl px-4 py-3 flex items-center justify-between text-white max-w-4xl mx-auto w-full animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex items-center gap-4">
                {{-- Mini Basketball Animation --}}
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <div class="text-brand-gold animate-bounce">
                        <i class="fa-light fa-basketball text-2xl"></i>
                    </div>
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-4 h-1 bg-black/40 rounded-full blur-[2px] animate-pulse"></div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-gold/80 mb-0.5">
                        @if($run->run_type === 'player_sync_excesive')
                            Excesivní synchronizace hráčů
                        @else
                            Synchronizace dat
                        @endif
                    </div>
                    <div class="text-sm font-semibold truncate max-w-[250px] sm:max-w-[400px]">
                        {{ $run->current_item_label ?: 'Zpracovávám...' }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end gap-1.5 w-32 sm:w-64 ml-4">
                <div class="flex justify-between w-full text-[10px] font-bold font-mono">
                    <span class="text-white/60">{{ $run->imported_count }} / {{ $run->total_count ?: '?' }}</span>
                    <span class="text-brand-gold">{{ number_format($run->progress_percent, 1) }}%</span>
                </div>
                <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden border border-white/5 p-[1px]">
                    <div class="h-full bg-gradient-to-r from-brand-gold via-brand-orange to-brand-gold bg-[length:200%_auto] animate-gradient transition-all duration-700 rounded-full"
                         style="width: {{ $run->progress_percent }}%"></div>
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
