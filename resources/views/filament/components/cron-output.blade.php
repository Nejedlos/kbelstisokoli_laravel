<div class="p-4 bg-slate-900 text-slate-100 font-mono text-xs rounded-lg overflow-auto max-h-[60vh]">
    @if($record->output)
        <pre class="whitespace-pre-wrap">{{ $record->output }}</pre>
    @elseif($record->error_message)
        <div class="text-danger-400">
            <p class="font-bold mb-2">Chybová hláška:</p>
            <pre class="whitespace-pre-wrap">{{ $record->error_message }}</pre>
        </div>
    @else
        <p class="italic text-slate-500 text-center py-4">Úloha nevygenerovala žádný výstup.</p>
    @endif
</div>
