<x-filament-panels::page>
    <div class="flex flex-col xl:flex-row gap-6 items-start" wire:poll.3s="refreshConsoleLogs">
        {{-- Left Column: Content --}}
        <div class="flex-1 space-y-8 w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Externí Sync --}}
                <div class="space-y-4">
                    <h2 class="text-xl font-bold">Externí Statistiky (Aktivní sezóna)</h2>
                    @foreach($externalSync as $team)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-4">
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
                                <div class="flex space-x-1">
                                    <x-filament::button size="xs" color="gray" wire:click="runTeamSync({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Standardní synchronizace">
                                        Sync
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="warning" wire:click="runTeamSyncForce({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Force Sync (ignorovat hash)">
                                        Force
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="danger" wire:click="runTeamSyncFresh({{ $team['team_id'] }})" wire:loading.attr="disabled" title="Fresh Sync (přepsat data!)">
                                        Fresh
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
                                    <div class="text-xs text-gray-500">Unmatched</div>
                                </div>
                            </div>

                            @if($team['last_error'])
                                <div class="mt-4 p-2 bg-danger-50 dark:bg-danger-900/20 text-danger-700 dark:text-danger-400 text-[10px] rounded border border-danger-100 dark:border-danger-900/30">
                                    <strong>Last Error:</strong> {{ $team['last_error'] }}
                                </div>
                            @endif

                            <div class="mt-4 text-[10px] text-gray-400">
                                Naposledy synchronizováno: {{ $team['last_sync'] ? \Carbon\Carbon::parse($team['last_sync'])->diffForHumans() : 'Nikdy' }}
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Legacy Import --}}
                <div class="space-y-4">
                    <h2 class="text-xl font-bold">Legacy Import</h2>
                    @if($legacyImport)
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-bold mb-2">{{ $legacyImport['title'] }}</h3>
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
                            <x-filament::button color="info" size="sm" class="w-full" wire:click="mountAction('discoverSeasons')">
                                <i class="fa-light fa-magnifying-glass mr-1"></i> Spustit Discovery
                            </x-filament::button>

                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <x-filament::button color="gray" size="sm" tag="a" href="{{ \App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource::getUrl() }}">
                                    <i class="fa-light fa-list-bullet mr-1"></i> Historie
                                </x-filament::button>
                                <x-filament::button color="gray" size="sm" tag="a" href="{{ \App\Filament\Resources\ExternalEntityMappings\ExternalEntityMappingResource::getUrl() }}">
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
        <div class="w-full xl:w-[450px] sticky top-8 space-y-4">
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
