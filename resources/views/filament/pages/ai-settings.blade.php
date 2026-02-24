<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Custom AI Status Header --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Status</p>
                    <div class="flex items-center gap-2">
                        @if($data['enabled'] ?? false)
                            <div class="w-2 h-2 rounded-full bg-success-500 shadow-[0_0_8px_rgba(34,197,94,0.5)] animate-pulse"></div>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Aktivní</span>
                        @else
                            <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                            <span class="text-sm font-bold text-gray-400">Neaktivní</span>
                        @endif
                    </div>
                </div>
                <i class="fa-light fa-signal-stream absolute -bottom-2 -right-2 text-4xl text-gray-50 dark:text-gray-800/50 group-hover:scale-110 transition-transform"></i>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Provider</p>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ strtoupper($data['provider'] ?? 'OpenAI') }}</span>
                        @if($data['use_database_settings'] ?? false)
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">DB</span>
                        @else
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">ENV</span>
                        @endif
                    </div>
                </div>
                <i class="fa-light fa-cloud-binary absolute -bottom-2 -right-2 text-4xl text-gray-50 dark:text-gray-800/50 group-hover:scale-110 transition-transform"></i>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Výchozí Model</p>
                    <span class="text-sm font-bold text-gray-900 dark:text-white truncate block">{{ $data['default_chat_model'] ?? 'gpt-4o-mini' }}</span>
                </div>
                <i class="fa-light fa-brain-circuit absolute -bottom-2 -right-2 text-4xl text-gray-50 dark:text-gray-800/50 group-hover:scale-110 transition-transform"></i>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Debug Mode</p>
                    <div class="flex items-center gap-2">
                        @if($data['debug_enabled'] ?? false)
                            <span class="text-sm font-bold text-warning-600">ZAPNUTO</span>
                        @else
                            <span class="text-sm font-bold text-gray-400">VYPNUTO</span>
                        @endif
                    </div>
                </div>
                <i class="fa-light fa-bug absolute -bottom-2 -right-2 text-4xl text-gray-50 dark:text-gray-800/50 group-hover:scale-110 transition-transform"></i>
            </div>
        </div>

        {{-- Main Settings Form --}}
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @foreach($this->getCachedFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>

        {{-- Debug & Logs Section --}}
        @if($data['debug_log_to_database'] ?? false)
            <div class="mt-12">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-light fa-list-timeline text-primary"></i>
                        Poslední AI aktivity
                    </h2>
                    <a href="#" class="text-xs text-primary hover:underline font-bold uppercase tracking-widest">Zobrazit vše</a>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-4 py-3 font-black uppercase text-[10px] text-gray-400">Datum</th>
                                <th class="px-4 py-3 font-black uppercase text-[10px] text-gray-400">Kontext</th>
                                <th class="px-4 py-3 font-black uppercase text-[10px] text-gray-400">Model</th>
                                <th class="px-4 py-3 font-black uppercase text-[10px] text-gray-400">Status</th>
                                <th class="px-4 py-3 font-black uppercase text-[10px] text-gray-400 text-right">Latence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @php
                                $logs = \App\Models\AiRequestLog::latest()->limit(5)->get();
                            @endphp
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $log->created_at->format('H:i:s d.m.') }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-700 dark:text-gray-300">{{ $log->context }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $log->model }}</td>
                                    <td class="px-4 py-3">
                                        @if($log->status === 'success')
                                            <span class="px-2 py-0.5 rounded-full bg-success-50 text-success-600 text-[10px] font-bold">OK</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-danger-50 text-danger-600 text-[10px] font-bold">ERR</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-400">{{ $log->latency_ms }}ms</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-gray-400 italic">
                                        Žádné záznamy o aktivitě nebyly nalezeny.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
