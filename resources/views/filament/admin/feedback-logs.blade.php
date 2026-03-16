@php
    $logs = $getState() ?? [];
    $allLogs = [];
    if (!empty($logs['console'])) {
        foreach($logs['console'] as $l) {
            $allLogs[] = [
                'type' => $l['level'] ?? 'log',
                'timestamp' => $l['timestamp'] ?? now()->toIso8601String(),
                'data' => [$l['message'] ?? 'No message']
            ];
        }
    }
    if (!empty($logs['errors'])) {
        foreach($logs['errors'] as $e) {
            $msg = ($e['type'] ?? 'Error') . ': ' . ($e['message'] ?? 'No message');
            if (!empty($e['filename'])) $msg .= " in {$e['filename']}:" . ($e['lineno'] ?? '?');
            $allLogs[] = [
                'type' => 'error',
                'timestamp' => $e['timestamp'] ?? now()->toIso8601String(),
                'data' => [$msg, $e['stack'] ?? null, $e['reason'] ?? null]
            ];
        }
    }

    // Pokud jsou logy postaru (pole), převedeme je na allLogs
    if (empty($allLogs) && is_array($logs) && !isset($logs['console']) && !isset($logs['errors'])) {
        $allLogs = $logs;
    }

    // Seřadit podle času, pokud existuje timestamp
    usort($allLogs, function($a, $b) {
        $ta = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
        $tb = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
        return $ta - $tb;
    });
@endphp

<div class="space-y-4" x-data="{
    copyToClipboard() {
        const text = this.$refs.logContainer.innerText;
        navigator.clipboard.writeText(text).then(() => {
            new FilamentNotification()
                .title('Zkopírováno do schránky')
                .success()
                .send();
        });
    }
}">
    <div class="flex justify-end">
        <button
            type="button"
            @click="copyToClipboard()"
            class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors border border-slate-200"
        >
            <i class="fa-light fa-copy"></i>
            Kopírovat vše
        </button>
    </div>

    @if(empty($allLogs))
        <div class="p-4 bg-slate-50 rounded-xl text-slate-500 text-sm italic border border-slate-100">
            Žádné záznamy k zobrazení.
        </div>
    @else
        <div x-ref="logContainer" class="flex flex-col gap-2 font-mono text-xs max-h-[600px] overflow-y-auto custom-scrollbar p-4 bg-slate-900 rounded-xl">
            @foreach($allLogs as $log)
                <div class="flex gap-3 border-b border-slate-800 pb-2 last:border-0 hover:bg-slate-800/50 transition-colors px-1">
                    <span class="text-slate-500 shrink-0">[{{ date('H:i:s', strtotime($log['timestamp'] ?? 'now')) }}]</span>
                    <span @class([
                        'font-bold shrink-0 w-20 uppercase',
                        'text-blue-400' => in_array($log['type'] ?? '', ['info', 'log']),
                        'text-yellow-400' => ($log['type'] ?? '') === 'warn',
                        'text-red-400' => in_array($log['type'] ?? '', ['error', 'exception', 'promise-rejection']),
                        'text-emerald-400' => ($log['type'] ?? '') === 'debug',
                        'text-slate-400' => !in_array($log['type'] ?? '', ['info', 'log', 'warn', 'error', 'exception', 'promise-rejection', 'debug']),
                    ])>{{ $log['type'] ?? 'LOG' }}</span>
                    <div class="flex flex-col gap-1 text-slate-300 flex-1 overflow-hidden">
                        @foreach($log['data'] ?? [] as $dataItem)
                            @if($dataItem)
                                <pre class="whitespace-pre-wrap break-all">{{ is_string($dataItem) ? $dataItem : json_encode($dataItem, JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>
