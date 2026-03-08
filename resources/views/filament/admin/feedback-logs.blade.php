<div class="space-y-4">
    @if(empty($logs))
        <div class="p-4 bg-slate-50 rounded-xl text-slate-500 text-sm italic border border-slate-100">
            Žádné logy k zobrazení.
        </div>
    @else
        <div class="flex flex-col gap-2 font-mono text-xs max-h-[500px] overflow-y-auto custom-scrollbar p-4 bg-slate-900 rounded-xl">
            @foreach($logs as $log)
                <div class="flex gap-3 border-b border-slate-800 pb-2 last:border-0">
                    <span class="text-slate-500 shrink-0">[{{ date('H:i:s', strtotime($log['timestamp'])) }}]</span>
                    <span @class([
                        'font-bold shrink-0 w-16 uppercase',
                        'text-blue-400' => $log['type'] === 'info' || $log['type'] === 'log',
                        'text-yellow-400' => $log['type'] === 'warn',
                        'text-red-400' => $log['type'] === 'error' || $log['type'] === 'exception',
                        'text-emerald-400' => $log['type'] === 'debug',
                    ])>{{ $log['type'] }}</span>
                    <div class="flex flex-col gap-1 text-slate-300">
                        @foreach($log['data'] as $dataItem)
                            <pre class="whitespace-pre-wrap break-all">{{ is_string($dataItem) ? $dataItem : json_encode($dataItem, JSON_PRETTY_PRINT) }}</pre>
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
