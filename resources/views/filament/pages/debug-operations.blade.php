<x-filament-panels::page>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start" wire:poll.{{ $pollingInterval }}="refreshConsoleLogs">
        {{-- Left Column: Content --}}
        <div class="space-y-8 w-full">
            <div class="grid grid-cols-2 gap-4">
                @foreach($health as $key => $h)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $h['label'] }}</span>
                            <div @class([
                                'w-3 h-3 rounded-full',
                                'bg-success-500' => $h['ok'],
                                'bg-danger-500' => !$h['ok'],
                                'animate-pulse' => isset($h['warning']) && $h['warning'],
                            ])></div>
                        </div>
                        <div class="text-lg font-bold truncate" title="{{ $h['msg'] }}">
                            {{ $h['ok'] ? 'OK' : 'FAIL' }}
                        </div>
                        <div class="text-[10px] text-gray-400 truncate mt-1">
                            {{ $h['msg'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 space-y-4">
                <h2 class="text-xl font-bold">Externí Statistiky (Aktivní sezóna: {{ $activeSeason?->name ?? 'Není vybrána' }})</h2>
                <div class="space-y-6">
                    @foreach($externalSync as $team)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-4">
                                <div>
                                    <h3 class="text-lg font-bold">{{ is_array($team['team_name']) ? ($team['team_name']['cs'] ?? $team['team_name']['en']) : $team['team_name'] }}</h3>
                                    <span @class([
                                        'text-xs px-2 py-0.5 rounded-full',
                                        'bg-success-100 text-success-700 font-medium' => $team['enabled'],
                                        'bg-gray-100 text-gray-700 font-medium' => !$team['enabled'],
                                    ])>
                                        {{ $team['enabled'] ? 'Synchronizace zapnuta' : 'Synchronizace vypnuta' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    <x-filament::button size="xs" color="gray" wire:click="runTeamSync({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Standardní synchronizace">
                                        <i class="fa-light fa-arrows-rotate mr-1"></i> Sync
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="warning" wire:click="runTeamSyncForce({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Force Sync (ignorovat hash)">
                                        <i class="fa-light fa-bolt mr-1"></i> Force
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="danger" wire:click="runTeamSyncFresh({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Fresh Sync (přepsat data!)">
                                        <i class="fa-light fa-trash-can-arrow-up mr-1"></i> Fresh
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="info" wire:click="runTeamSyncAiFresh({{ $team['team_id'] }})" wire:loading.attr="disabled" title="AI Fresh Sync (přepsat data pomocí AI!)">
                                        <i class="fa-light fa-sparkles mr-1"></i> AI
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="danger" variant="outline" wire:click="clearTeamErrors({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Smazat historii chyb (vyčistit Last Error)">
                                        <i class="fa-light fa-trash"></i>
                                    </x-filament::button>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold">{{ $team['match_count'] }}</div>
                                    <div class="text-xs text-gray-500">Zápasů</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $team['stat_rows_count'] }}</div>
                                    <div class="text-xs text-gray-500">Stat. řádků</div>
                                </div>
                                <div>
                                    <div @class(['text-2xl font-bold', 'text-danger-500' => $team['unmatched_count'] > 0])>
                                        {{ $team['unmatched_count'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center justify-center gap-1">
                                        Unmatched
                                        <i class="fa-light fa-circle-info text-[10px] cursor-help opacity-70 hover:opacity-100" title="Hráči nalezení na cz.basketball, které systém nedokázal automaticky spárovat s našimi uživateli. Statistiky jsou uloženy u dočasných profilů (Ghosts). Pro nápravu klikněte na tlačítko 'Párování' níže, přiřaďte hráče ručně a systém data automaticky přepočítá."></i>
                                    </div>
                                </div>
                            </div>

                            @if($team['last_error'])
                                <div
                                    x-data="{ expanded: false }"
                                    class="mt-4 bg-danger-50 dark:bg-danger-900/10 text-danger-700 dark:text-danger-400 text-[10px] rounded-lg border border-danger-200 dark:border-danger-900/30 overflow-hidden shadow-sm"
                                >
                                    {{-- Error Header --}}
                                    <div class="flex flex-col sm:flex-row justify-between items-center px-3 py-2 bg-danger-100/50 dark:bg-danger-900/40 border-b border-danger-100 dark:border-danger-900/30 gap-2">
                                        <div
                                            class="flex items-center gap-1.5 font-bold uppercase tracking-wide opacity-80 cursor-pointer select-none group"
                                            x-on:click="expanded = !expanded"
                                        >
                                            <i class="fa-light fa-chevron-right transition-transform duration-200 text-[8px]" :class="{ 'rotate-90': expanded }"></i>
                                            <i class="fa-light fa-circle-exclamation text-danger-500"></i>
                                            Last Error (Run #{{ $team['last_error_id'] }})
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            @if(isset($team['last_error_metadata']['debug_html_file']))
                                                <button
                                                    wire:click="downloadDebugHtml({{ $team['last_error_id'] }})"
                                                    class="flex items-center gap-1.5 px-2 py-1 bg-sky-700 dark:bg-sky-600 hover:bg-sky-800 dark:hover:bg-sky-500 text-white rounded-md shadow-sm transition-all font-bold uppercase tracking-wider text-[9px] active:scale-95 border border-sky-800/20 dark:border-sky-400/20"
                                                    title="Stáhnout sanitizovaný HTML fragment, který AI zpracovávala"
                                                >
                                                    <i class="fa-light fa-file-code"></i> HTML
                                                </button>
                                            @endif
                                            <button
                                                x-on:click="window.navigator.clipboard.writeText($el.closest('.mt-4').querySelector('.font-mono-full').innerText.trim()); $tooltip('Zkopírováno do schránky', { timeout: 2000 })"
                                                class="flex items-center gap-1.5 px-2.5 py-1.5 bg-primary-600 dark:bg-primary-500 hover:bg-primary-700 dark:hover:bg-primary-400 text-white rounded-md shadow-md transition-all font-bold uppercase tracking-wider text-[9px] active:scale-95 shadow-primary-500/20"
                                                title="Zkopírovat kompletní chybovou hlášku"
                                            >
                                                <i class="fa-light fa-copy"></i> Copy Error
                                            </button>
                                            <a href="{{ \App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource::getUrl('edit', ['record' => $team['last_error_id']]) }}" class="font-bold hover:underline flex items-center gap-1 text-danger-700 dark:text-danger-400 whitespace-nowrap px-1">
                                                Detail <i class="fa-light fa-arrow-right-long"></i>
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Error Content --}}
                                    <div class="p-3">
                                        @if(isset($team['last_error_metadata']['url']))
                                            <div class="mb-2 p-1.5 bg-white/50 dark:bg-black/20 rounded border border-danger-100/50 dark:border-danger-900/20 break-all flex items-start gap-2">
                                                <span class="font-bold shrink-0 uppercase tracking-tighter opacity-60">URL:</span>
                                                <span class="truncate">{{ $team['last_error_metadata']['url'] }}</span>
                                            </div>
                                        @endif

                                        {{-- Excerpt (shown when collapsed) --}}
                                        <div x-show="!expanded" x-on:click="expanded = true" class="cursor-pointer opacity-70 hover:opacity-100 transition-opacity flex items-center gap-2 group">
                                            <div class="truncate font-mono flex-1">
                                                {{ $team['last_error'] }}
                                            </div>
                                            <span class="text-[8px] uppercase font-bold text-danger-400 dark:text-danger-600 group-hover:underline decoration-1 underline-offset-2 shrink-0">Zobrazit více <i class="fa-light fa-caret-down"></i></span>
                                        </div>

                                        {{-- Full content (shown when expanded) --}}
                                        <div x-show="expanded" x-cloak x-collapse>
                                            <div class="relative">
                                                <div class="max-h-[300px] overflow-y-auto whitespace-pre-wrap font-mono font-mono-full leading-relaxed pr-2 scrollbar-thin scrollbar-thumb-danger-200 dark:scrollbar-thumb-danger-900 selection:bg-danger-500/20 selection:text-danger-900 dark:selection:text-danger-100">
                                                    {{ $team['last_error'] }}
                                                </div>
                                                <div class="absolute bottom-0 right-0 w-8 h-8 bg-gradient-to-tl from-danger-50 dark:from-gray-900/10 pointer-events-none"></div>
                                            </div>
                                            <div class="mt-2 text-center border-t border-danger-100/30 dark:border-danger-900/30 pt-2">
                                                <button x-on:click="expanded = false" class="text-[8px] uppercase font-bold text-danger-400 dark:text-danger-600 hover:underline">
                                                    <i class="fa-light fa-caret-up"></i> Zabalit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 text-[10px] text-gray-400">
                                Naposledy synchronizováno: {{ $team['last_sync'] ? \Carbon\Carbon::parse($team['last_sync'])->diffForHumans() : 'Nikdy' }}
                            </div>
                        </div>
                    @endforeach

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-4">
                            <div>
                                <h3 class="text-lg font-bold">Hloubková synchronizace hráčů</h3>
                                <p class="text-xs text-gray-500">
                                    Profilové údaje, fotografie a historie kariéry z cz.basketball.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold">{{ $playerSync['total'] }}</div>
                                <div class="text-xs text-gray-500">Hráčů s mapováním</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-success-500">{{ $playerSync['synced'] }}</div>
                                <div class="text-xs text-gray-500">Synchronizováno</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium">
                                    {{ $playerSync['last_sync'] ? \Illuminate\Support\Carbon::parse($playerSync['last_sync'])->diffForHumans() : 'Nikdy' }}
                                </div>
                                <div class="text-xs text-gray-500">Poslední sync</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-8">
                {{-- Legacy Import --}}
                <div class="space-y-4">
                    <h2 class="text-xl font-bold">Legacy Import</h2>
                    @if($legacyImport)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-bold">{{ $legacyImport['title'] }}</h3>
                                @if($legacyImport['status'] === 'running')
                                    <button
                                        wire:click="cancelLegacyImport({{ $legacyImport['id'] }})"
                                        wire:confirm="Opravdu chcete přerušit tento import?"
                                        class="text-danger-600 hover:text-danger-500 transition-colors flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest"
                                        title="Přerušit import"
                                    >
                                        <i class="fa-light fa-circle-xmark"></i>
                                        Zrušit
                                    </button>
                                @endif
                            </div>
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $legacyImport['progress'] }}%"></div>
                                </div>
                                <span class="text-sm font-medium">{{ $legacyImport['progress'] }}%</span>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-xl font-bold text-success-500">{{ $legacyImport['success'] }}</div>
                                    <div class="text-xs text-gray-500">Úspěch</div>
                                </div>
                                <div>
                                    <div class="text-xl font-bold text-danger-500">{{ $legacyImport['failed'] }}</div>
                                    <div class="text-xs text-gray-500">Chyba</div>
                                </div>
                                <div>
                                    <div class="text-xl font-bold">{{ $legacyImport['total'] }}</div>
                                    <div class="text-xs text-gray-500">Celkem</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ \App\Filament\Resources\LegacyImportBatches\LegacyImportBatchResource::getUrl('view', ['record' => $legacyImport['id']]) }}" class="text-sm text-primary-600 hover:underline font-medium">
                                    Detail dávky →
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center bg-gray-50 dark:bg-gray-900/20 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-400">
                            Nenalezena žádná dávka legacy importu.
                        </div>
                    @endif
                </div>

                {{-- Season Discovery --}}
                <div class="space-y-4">
                    <h2 class="text-xl font-bold">Season Backfill</h2>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold">Prázdné sezóny</h3>
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-bold',
                                'bg-warning-100 text-warning-700' => $discoveryStats['empty_count'] > 0,
                                'bg-success-100 text-success-700' => $discoveryStats['empty_count'] === 0,
                            ])>
                                {{ $discoveryStats['empty_count'] }} k prověření
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                            Identifikováno {{ $discoveryStats['empty_count'] }} sezón, které nemají konfiguraci nebo data. Discovery zkusí najít 'y' parametr na cz.basketball.
                        </p>

                        <div class="pt-2 space-y-2">
                            <x-filament::button color="info" size="sm" class="w-full" wire:click="mountAction('discoverSeasons')" title="Prohledá externí web a najde ID chybějících sezón pro konfiguraci">
                                <i class="fa-light fa-magnifying-glass mr-1"></i> Spustit Discovery
                            </x-filament::button>

                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <x-filament::button color="primary" size="sm" wire:click="mountAction('syncAllSeasons')" title="Spustí import dat pro vybrané (nebo všechny) nakonfigurované sezóny">
                                    <i class="fa-light fa-arrows-rotate mr-1"></i> Import sezón
                                </x-filament::button>
                                <x-filament::button color="warning" size="sm" wire:click="mountAction('recomputeAllSeasons')" title="Přepočítá statistiky pro vybrané (nebo všechny) sezóny">
                                    <i class="fa-light fa-gauge-high mr-1"></i> Přepočet sezón
                                </x-filament::button>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <x-filament::button color="gray" size="sm" tag="a" href="{{ \App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource::getUrl() }}" title="Historie všech importů a discovery běhů">
                                    <i class="fa-light fa-list-bullet mr-1"></i> Historie
                                </x-filament::button>
                                <x-filament::button color="gray" size="sm" tag="a" href="{{ \App\Filament\Resources\ExternalEntityMappings\ExternalEntityMappingResource::getUrl() }}" title="Správa párování externích entit na naše modely">
                                    <i class="fa-light fa-users mr-1"></i> Párování
                                </x-filament::button>
                            </div>
                        </div>

                        <div class="mt-4 text-[10px] text-gray-400">
                            Poslední discovery: {{ $discoveryStats['last_discover'] ? \Carbon\Carbon::parse($discoveryStats['last_discover'])->diffForHumans() : 'Nikdy' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audit Log Table --}}
            <div class="mt-12 space-y-4">
                <h2 class="text-xl font-bold">Poslední běhy importu (Audit)</h2>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 dark:bg-gray-900 font-medium">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Sezóna</th>
                                    <th class="px-4 py-3">Tým</th>
                                    <th class="px-4 py-3">Typ</th>
                                    <th class="px-4 py-3">Cíl (ID)</th>
                                    <th class="px-4 py-3">Stav</th>
                                    <th class="px-4 py-3">Statistiky</th>
                                    <th class="px-4 py-3">Dokončeno</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($auditLogs as $log)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <a href="{{ \App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource::getUrl('edit', ['record' => $log->id]) }}" class="font-bold text-primary-600 hover:underline">
                                                #{{ $log->id }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-bold text-gray-600 dark:text-gray-400">
                                            {{ $log->season?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 font-medium">{{ $log->team?->name ?? 'Klub (Global)' }}</td>
                                        <td class="px-4 py-3 font-mono text-[10px] uppercase text-gray-500">
                                            {{ $log->run_type }}
                                            @if($log->metadata['force'] ?? false)
                                                <span class="text-warning-600 font-bold">[FORCE]</span>
                                            @endif
                                            @if($log->metadata['fresh'] ?? false)
                                                <span class="text-danger-600 font-bold">[FRESH]</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs truncate max-w-[150px]" title="{{ $log->target_external_id }}">
                                            {{ $log->target_external_id ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                                'bg-success-100 text-success-700' => $log->status === 'success',
                                                'bg-danger-100 text-danger-700' => $log->status === 'failed',
                                                'bg-warning-100 text-warning-700' => $log->status === 'partial_failed',
                                                'bg-gray-100 text-gray-700' => $log->status === 'skipped',
                                                'bg-blue-100 text-blue-700' => $log->status === 'running',
                                            ])>
                                                {{ $log->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-[10px] text-gray-600 dark:text-gray-400">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-success-600 font-bold" title="Importováno">✓ {{ $log->imported_count }}</span>
                                                @php $failed = max(0, $log->extracted_count - $log->imported_count); @endphp
                                                @if($failed > 0)
                                                    <span class="text-danger-600 font-bold" title="Chyby">✗ {{ $failed }}</span>
                                                @endif
                                                @if($log->logs_count > 0)
                                                    <span class="text-primary-600 font-bold italic" title="Detailní logy změn">
                                                        <i class="fa-light fa-list-check"></i> {{ $log->logs_count }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            {{ $log->finished_at ? $log->finished_at->diffForHumans() : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-right">
                    <a href="{{ \App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource::getUrl() }}" class="text-sm text-primary-600 hover:underline font-medium">
                        Zobrazit celou historii →
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Terminal --}}
        <div class="w-full sticky top-8 space-y-4">
            <div class="flex items-center justify-between px-2">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500 flex items-center gap-2">
                    <i class="fa-light fa-terminal w-4 h-4"></i>
                    Live Terminal
                </h2>
                <button
                    wire:click="clearConsoleLogs"
                    class="text-[10px] text-gray-400 hover:text-danger-500 transition-colors uppercase font-bold"
                >
                    Clear Console
                </button>
            </div>

            <div class="bg-black rounded-xl border border-gray-800 shadow-2xl overflow-hidden flex flex-col h-[600px] xl:h-[calc(100vh-12rem)]">
                <div class="flex items-center gap-1.5 px-4 py-3 border-b border-gray-900 bg-gray-950/50">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/50"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500/50"></div>
                    <span class="ml-2 text-[10px] font-mono text-gray-600">kbelstisokoli-stream-v1</span>
                </div>

                <div
                    id="console-output"
                    class="flex-1 overflow-y-auto p-4 font-mono text-[11px] leading-relaxed scrollbar-thin scrollbar-thumb-gray-800"
                    x-data="{
                        scrollToBottom() {
                            this.$el.scrollTop = this.$el.scrollHeight;
                        }
                    }"
                    x-init="scrollToBottom()"
                    @console-updated.window="scrollToBottom()"
                >
                    <div class="space-y-0.5 whitespace-pre-wrap selection:bg-primary-500/30">
                        {!! $consoleOutput ?: '<span class="text-gray-700 italic">No logs available. Start an operation to see output...</span>' !!}
                        <span class="inline-block w-2 h-4 bg-primary-500/50 animate-pulse align-middle ml-1"></span>
                    </div>
                </div>
                <div class="p-2 border-t border-gray-900 bg-gray-950/30 flex justify-between items-center">
                    <span class="text-[9px] text-gray-600">Auto-scrolling enabled</span>
                    <button
                        x-on:click="window.navigator.clipboard.writeText(document.getElementById('console-output').innerText)"
                        class="text-[9px] text-primary-500 hover:text-primary-400 font-bold uppercase"
                    >
                        Copy Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
