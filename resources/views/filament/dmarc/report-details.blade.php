<div class="space-y-6">
    @php
        $records = $getRecord()->records()
            ->with('knownSender')
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 1 WHEN severity = 'high' THEN 2 WHEN severity = 'medium' THEN 3 ELSE 4 END")
            ->get();
    @endphp

    <div class="fi-ta-content relative overflow-x-auto">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5 border dark:border-white/5 rounded-xl overflow-hidden">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Odesílatel / IP</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Analýza</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Riziko</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Doporučení a detaily</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @forelse ($records as $record)
                    @php
                        $analysis = $record->analysis;
                        $recs = $record->recommendations;
                        $severity = $record->severity ?? 'info';
                        $severityColor = match($severity) {
                            'critical' => 'text-red-700 bg-red-50 ring-red-600/20',
                            'high' => 'text-orange-700 bg-orange-50 ring-orange-600/20',
                            'medium' => 'text-yellow-700 bg-yellow-50 ring-yellow-600/20',
                            'low' => 'text-blue-700 bg-blue-50 ring-blue-600/20',
                            default => 'text-gray-700 bg-gray-50 ring-gray-600/20',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 align-top">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-950 dark:text-white">{{ $record->source_ip }}</span>
                                    @if($record->knownSender)
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            <i class="fa-light fa-shield-check mr-1"></i> {{ $record->knownSender->name }}
                                        </span>
                                    @endif
                                </div>
                                @if(isset($analysis['reverse_dns']) && $analysis['reverse_dns'])
                                    <span class="text-xs text-gray-500 italic">{{ $analysis['reverse_dns'] }}</span>
                                @endif
                                <span class="text-xs text-gray-400">Header From: {{ $record->header_from }}</span>
                                <span class="text-xs font-medium mt-2">Počet zpráv: {{ $record->count }}</span>
                            </div>
                        </td>
                        <td class="fi-ta-cell px-3 py-4 align-top text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="flex gap-1">
                                    <span class="text-[10px] font-bold uppercase text-gray-400">DKIM:</span>
                                    @if($record->dkim_aligned)
                                        <span class="text-[10px] font-bold text-green-600">PASS</span>
                                    @else
                                        <span class="text-[10px] font-bold text-red-600">FAIL</span>
                                    @endif
                                </div>
                                <div class="flex gap-1">
                                    <span class="text-[10px] font-bold uppercase text-gray-400">SPF:</span>
                                    @if($record->spf_aligned)
                                        <span class="text-[10px] font-bold text-green-600">PASS</span>
                                    @else
                                        <span class="text-[10px] font-bold text-red-600">FAIL</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $severityColor }}">
                                        {{ strtoupper($severity) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="fi-ta-cell px-3 py-4 align-top text-center">
                            @if(isset($record->risk_score))
                            <div class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-{{ $record->risk_score > 70 ? 'red' : ($record->risk_score > 30 ? 'yellow' : 'green') }}-600 bg-{{ $record->risk_score > 70 ? 'red' : ($record->risk_score > 30 ? 'yellow' : 'green') }}-200">
                                            {{ $record->risk_score }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                                    <div style="width:{{ $record->risk_score }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-{{ $record->risk_score > 70 ? 'red' : ($record->risk_score > 30 ? 'yellow' : 'green') }}-500"></div>
                                </div>
                            </div>
                            @endif
                        </td>
                        <td class="fi-ta-cell px-3 py-4 align-top">
                            <div class="space-y-3">
                                @if(isset($recs['summary']))
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $recs['summary'] }}
                                    </div>
                                @endif

                                @if(isset($recs['probable_cause']))
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        <strong class="text-gray-800 dark:text-gray-200">Pravděpodobná příčina:</strong> {{ $recs['probable_cause'] }}
                                    </div>
                                @endif

                                @if(!empty($recs['what_to_check'] ?? []))
                                    <div class="text-xs">
                                        <strong class="text-gray-800 dark:text-gray-200">Co ověřit:</strong>
                                        <ul class="list-disc list-inside mt-1 text-gray-600 dark:text-gray-400 space-y-0.5">
                                            @foreach($recs['what_to_check'] as $step)
                                                <li>{{ $step }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if(!empty($recs['how_to_fix'] ?? []))
                                    <div class="text-xs p-2 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-lg">
                                        <strong class="text-blue-900 dark:text-blue-300">Jak opravit:</strong>
                                        <ul class="list-disc list-inside mt-1 text-blue-800 dark:text-blue-400 space-y-0.5">
                                            @foreach($recs['how_to_fix'] as $step)
                                                <li>{{ $step }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 italic">
                            Žádné záznamy k zobrazení.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
