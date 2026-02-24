<x-filament-panels::page>
    <div class="flex flex-col md:flex-row gap-8 items-start">
        {{-- Levý sloupec: Příkazy --}}
        <div class="flex-1 w-full space-y-10">
            @foreach($commandGroups as $groupLabel => $commands)
                <div class="space-y-4">
                    <div class="flex items-center gap-3 px-2">
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                        <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 dark:text-gray-600 whitespace-nowrap">
                            {{ $groupLabel }}
                        </h2>
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($commands as $cmdKey => $config)
                            <div
                                class="group relative flex flex-col h-full border border-gray-100 dark:border-white/5 rounded-2xl p-4 bg-white dark:bg-[#0d1117] shadow-sm transition-all hover:shadow-xl hover:shadow-primary-500/5 hover:border-primary-500/50"
                                x-data="{
                                    flags: [],
                                    selectValue: '',
                                    loading: false
                                }"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="shrink-0 p-2.5 rounded-xl bg-gray-50 dark:bg-white/5 text-gray-400 group-hover:text-primary-500 group-hover:bg-primary-500/10 transition-all duration-300">
                                        <x-filament::icon :icon="$config['icon']" class="h-4.5 w-4.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-[13.5px] tracking-tight leading-tight text-gray-900 dark:text-gray-100 truncate">{{ $config['label'] }}</h3>
                                        <div class="flex items-center gap-2">
                                            <code class="text-[9px] text-gray-400 font-mono uppercase tracking-tighter opacity-60">{{ $cmdKey }}</code>
                                            <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                                            <span class="text-[8px] text-gray-400 uppercase font-bold tracking-widest">{{ $config['type'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 flex-grow line-clamp-2 leading-relaxed">
                                    {{ $config['desc'] ?? '' }}
                                </p>

                                @if(isset($config['flags']) || isset($config['select']))
                                    <div class="space-y-4 mb-4 p-3 bg-gray-50/50 dark:bg-white/[0.02] rounded-xl border border-gray-100/50 dark:border-white/5">
                                        @if(isset($config['flags']))
                                            <div class="flex flex-wrap gap-x-4 gap-y-2">
                                                @foreach($config['flags'] as $flag => $flagLabel)
                                                    <label class="flex items-center gap-2 cursor-pointer group/label">
                                                        <x-filament::input.checkbox
                                                            x-model="flags"
                                                            value="{{ $flag }}"
                                                            class="h-3 w-3 rounded text-primary-600 focus:ring-primary-500 border-gray-300"
                                                        />
                                                        <span class="text-[10px] text-gray-600 dark:text-gray-400 group-hover/label:text-primary-500 transition-colors">{{ $flagLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(isset($config['select']))
                                            <div class="space-y-1.5">
                                                <label class="text-[9px] uppercase tracking-wider font-bold text-gray-400">
                                                    {{ $config['select']['label'] }}
                                                </label>
                                                <x-filament::input.select x-model="selectValue" class="w-full text-[11px] py-1 h-8">
                                                    <option value="">-- vybrat --</option>
                                                    @foreach($config['select']['options'] as $val => $label)
                                                        <option value="{{ $val }}">{{ $label }}</option>
                                                    @endforeach
                                                </x-filament::input.select>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <x-filament::button
                                    wire:loading.attr="disabled"
                                    x-on:click="
                                        loading = true;
                                        $wire.run('{{ $cmdKey }}', '{{ $config['type'] }}', flags, '{{ $config['select']['name'] ?? '' }}', selectValue)
                                            .then(() => {
                                                const outputEl = document.getElementById('console-output');
                                                if (outputEl) outputEl.scrollTop = outputEl.scrollHeight;
                                            })
                                            .finally(() => loading = false);
                                    "
                                    @output-updated.window="
                                        const outputEl = document.getElementById('console-output');
                                        if (outputEl) outputEl.scrollTop = outputEl.scrollHeight;
                                    "
                                    color="{{ $config['color'] ?? 'primary' }}"
                                    class="w-full text-[11px] font-bold py-1.5 shadow-sm rounded-lg"
                                    x-bind:disabled="loading"
                                    size="sm"
                                >
                                    <div class="flex items-center justify-center gap-2">
                                        <template x-if="!loading">
                                            <div class="flex items-center gap-2">
                                                <x-filament::icon icon="fal-play" class="h-3 w-3 opacity-70 group-hover:opacity-100 transition-opacity" />
                                                <span x-text="'Spustit'"></span>
                                            </div>
                                        </template>
                                        <template x-if="loading">
                                            <div class="flex items-center gap-2">
                                                <x-filament::loading-indicator class="h-3 w-3" />
                                                <span x-text="'Pracuji...'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </x-filament::button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pravý sloupec: Terminál --}}
        <div class="w-full md:w-[380px] lg:w-[450px] xl:w-[550px] md:sticky md:top-24 shrink-0">
            <div
                class="terminal-window rounded-2xl overflow-hidden border border-gray-800 shadow-2xl bg-[#010409] transition-all"
                x-data="{
                    copyToClipboard() {
                        const el = document.getElementById('console-output');
                        navigator.clipboard.writeText(el.innerText).then(() => {
                            $tooltip('Zkopírováno!', { timeout: 2000 });
                        });
                    }
                }"
                x-init="
                    $watch('$wire.output', value => {
                        const el = document.getElementById('console-output');
                        if (el) el.scrollTop = el.scrollHeight;
                    })
                "
            >
                {{-- Hlavička terminálu --}}
                <div class="terminal-header bg-[#0d1117] px-5 py-3.5 flex items-center justify-between border-b border-gray-800">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56] opacity-80"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e] opacity-80"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f] opacity-80"></div>
                        </div>
                        <div class="flex items-center gap-2 ml-2">
                            <x-filament::icon icon="fal-terminal" class="h-3 w-3 text-gray-600" />
                            <span class="text-[9px] text-gray-600 font-mono uppercase tracking-[0.2em] font-black">System Console</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div wire:loading wire:target="run" class="mr-2">
                            <x-filament::loading-indicator class="h-4 w-4 text-primary-500" />
                        </div>
                        <div class="flex border border-gray-800 rounded-lg overflow-hidden">
                            <button
                                type="button"
                                x-on:click="copyToClipboard()"
                                class="p-2 hover:bg-gray-800 text-gray-500 hover:text-white transition-colors"
                                title="Zkopírovat vše"
                            >
                                <x-filament::icon icon="fal-copy" class="h-3.5 w-3.5" />
                            </button>
                            <div class="w-px bg-gray-800"></div>
                            <button
                                type="button"
                                wire:click="clearOutput"
                                class="p-2 hover:bg-gray-800 text-gray-500 hover:text-red-400 transition-colors"
                                title="Vymazat konzoli"
                            >
                                <x-filament::icon icon="fal-eraser" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Vnitřek terminálu --}}
                <div
                    id="console-output"
                    class="p-6 text-[#c9d1d9] font-mono text-[11px] leading-[1.6] min-h-[500px] md:min-h-[600px] max-h-[80vh] overflow-y-auto whitespace-pre-wrap shadow-inner custom-scrollbar selection:bg-primary-500/40 selection:text-white"
                    wire:stream="output"
                >
                    @if(empty($output))
                        <div class="flex flex-col items-center justify-center h-[400px] text-gray-700 select-none">
                            <x-filament::icon icon="fal-terminal" class="h-12 w-12 mb-6 opacity-10" />
                            <span class="italic text-[11px] font-medium opacity-30 tracking-tight">Kbelští sokoli System Console [v1.0.0]</span>
                            <div class="mt-8 flex gap-2 items-center opacity-30 text-[11px]">
                                <span class="px-2 py-0.5 rounded border border-gray-800 font-mono italic">admin@kbelsti-sokoli:~$</span>
                                <span class="animate-pulse w-2 h-4 bg-gray-700"></span>
                            </div>
                        </div>
                    @else
                        {{ $output }}
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
