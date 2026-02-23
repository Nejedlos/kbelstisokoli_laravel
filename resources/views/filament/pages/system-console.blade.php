<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Výstup z konzole (Terminál) - nahoře pro lepší přehled při spouštění --}}
        <div
            class="terminal-window rounded-xl overflow-hidden border border-gray-800 shadow-2xl bg-[#0d1117] transition-all"
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
            <div class="terminal-header bg-[#161b22] px-4 py-2.5 flex items-center justify-between border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56] shadow-sm shadow-red-900/50"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e] shadow-sm shadow-yellow-900/50"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f] shadow-sm shadow-green-900/50"></div>
                    </div>
                    <div class="flex items-center gap-2 ml-2">
                        <x-filament::icon icon="fal-terminal" class="h-3.5 w-3.5 text-gray-500" />
                        <span class="text-[11px] text-gray-500 font-mono uppercase tracking-widest font-bold">bash — kbelsti-sokoli — system-console</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div wire:loading wire:target="run" class="mr-2">
                        <x-filament::loading-indicator class="h-4 w-4 text-primary-500" />
                    </div>
                    <div class="flex border border-gray-700 rounded-lg overflow-hidden">
                        <button
                            type="button"
                            x-on:click="copyToClipboard()"
                            class="p-1.5 hover:bg-gray-800 text-gray-400 hover:text-white transition-colors"
                            title="Zkopírovat vše"
                        >
                            <x-filament::icon icon="fal-copy" class="h-3.5 w-3.5" />
                        </button>
                        <div class="w-px bg-gray-700"></div>
                        <button
                            type="button"
                            wire:click="clearOutput"
                            class="p-1.5 hover:bg-gray-800 text-gray-400 hover:text-red-400 transition-colors"
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
                class="p-6 text-[#e6edf3] font-mono text-[13px] leading-relaxed min-h-[350px] max-h-[600px] overflow-y-auto whitespace-pre-wrap shadow-inner custom-scrollbar selection:bg-primary-500/30 selection:text-white"
            >
                @if(empty($output))
                    <div class="flex flex-col items-center justify-center h-[300px] text-gray-700 select-none">
                        <x-filament::icon icon="fal-terminal" class="h-16 w-16 mb-6 opacity-10" />
                        <span class="italic text-sm font-medium opacity-30 tracking-tight">Kbelští sokoli System Console [Version 1.0.0]</span>
                        <span class="text-xs opacity-20 mt-1">(c) 2026 Michal Nejedlý. Všechna práva vyhrazena.</span>
                        <div class="mt-8 flex gap-2 items-center opacity-30 text-xs">
                            <span class="px-2 py-0.5 rounded border border-gray-800 font-mono italic">admin@kbelsti-sokoli:~$</span>
                            <span class="animate-pulse w-2 h-4 bg-gray-700"></span>
                        </div>
                    </div>
                @else
                    {{ $output }}
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @foreach($commandGroups as $groupLabel => $commands)
                <x-filament::section collapsible>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="fal-layer-group" class="h-5 w-5 text-primary-500" />
                            {{ $groupLabel }}
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($commands as $cmdKey => $config)
                            <div
                                class="flex flex-col h-full border border-gray-200 dark:border-gray-700 rounded-xl p-5 bg-white dark:bg-gray-900 shadow-sm transition-all hover:shadow-md hover:border-primary-500/30"
                                x-data="{
                                    flags: [],
                                    selectValue: '',
                                    loading: false
                                }"
                            >
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="flex flex-col">
                                        <h3 class="font-bold text-base leading-tight">{{ $config['label'] }}</h3>
                                        <code class="text-[10px] text-gray-400 font-mono mt-1 opacity-70">{{ $cmdKey }}</code>
                                    </div>
                                    @if(isset($config['icon']))
                                        <div class="shrink-0 p-2 rounded-lg bg-{{ $config['color'] ?? 'primary' }}-50 dark:bg-{{ $config['color'] ?? 'primary' }}-900/20 text-{{ $config['color'] ?? 'primary' }}-600">
                                            <x-filament::icon :icon="$config['icon']" class="h-4 w-4" />
                                        </div>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-5 flex-grow line-clamp-2">
                                    {{ $config['desc'] ?? '' }}
                                </p>

                                <div class="space-y-4 mt-auto pt-4 border-t border-gray-100 dark:border-gray-800">
                                    @if(isset($config['flags']))
                                        <div class="flex flex-wrap gap-x-4 gap-y-2">
                                            @foreach($config['flags'] as $flag => $flagLabel)
                                                <label class="flex items-center gap-2 cursor-pointer group">
                                                    <x-filament::input.checkbox
                                                        x-model="flags"
                                                        value="{{ $flag }}"
                                                        class="h-3 w-3 rounded text-primary-600 focus:ring-primary-500 border-gray-300"
                                                    />
                                                    <span class="text-[11px] text-gray-600 dark:text-gray-400 group-hover:text-primary-500 transition-colors">{{ $flagLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(isset($config['select']))
                                        <div class="space-y-1.5">
                                            <label class="fi-fo-field-label text-[11px] uppercase tracking-wider font-semibold opacity-60">
                                                {{ $config['select']['label'] }}
                                            </label>
                                            <x-filament::input.select x-model="selectValue" class="w-full text-xs py-1">
                                                <option value="">-- vybrat --</option>
                                                @foreach($config['select']['options'] as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </x-filament::input.select>
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
                                        color="{{ $config['color'] ?? 'primary' }}"
                                        class="w-full text-xs font-bold py-2 shadow-sm"
                                        x-bind:disabled="loading"
                                        size="sm"
                                    >
                                        <div class="flex items-center justify-center gap-2">
                                            <template x-if="!loading">
                                                <x-filament::icon icon="fal-play" class="h-3 w-3" />
                                            </template>
                                            <template x-if="loading">
                                                <x-filament::loading-indicator class="h-3 w-3" />
                                            </template>
                                            <span x-text="loading ? 'Pracuji...' : 'Spustit'"></span>
                                        </div>
                                    </x-filament::button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0d1117;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 10px;
            border: 2px solid #0d1117;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #8b949e;
        }

        /* Animace pro blikající kurzor by byla fajn, ale nechme to jednoduché */
    </style>
</x-filament-panels::page>
