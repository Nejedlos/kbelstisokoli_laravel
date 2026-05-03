<div class="space-y-4">
    @php
        $records = $getRecord()->records()->orderBy('status', 'desc')->get();
    @endphp

    <div class="fi-ta-content relative overflow-x-auto">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">IP adresa / Zdroj</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Počet</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">DKIM</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">SPF</span>
                    </th>
                    <th class="fi-ta-header-cell px-3 py-3.5">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Stav / Akce</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @forelse ($records as $record)
                    <tr>
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-950 dark:text-white">{{ $record->source_ip }}</span>
                                <span class="text-xs text-gray-500">{{ $record->header_from }}</span>
                            </div>
                        </td>
                        <td class="fi-ta-cell px-3 py-4 text-center">
                            {{ $record->count }}
                        </td>
                        <td class="fi-ta-cell px-3 py-4 text-center">
                            @if($record->dkim_aligned)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">PASS</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">FAIL</span>
                            @endif
                        </td>
                        <td class="fi-ta-cell px-3 py-4 text-center">
                            @if($record->spf_aligned)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">PASS</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">FAIL</span>
                            @endif
                        </td>
                        <td class="fi-ta-cell px-3 py-4">
                            @if($record->status === 'Critical')
                                <div class="p-2 rounded bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs text-red-800 dark:text-red-200">
                                    <strong>Kritické:</strong> {{ $record->recommended_action }}
                                </div>
                            @elseif($record->status === 'Warning')
                                <div class="p-2 rounded bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800 text-xs text-yellow-800 dark:text-yellow-200">
                                    <strong>Varování:</strong> {{ $record->recommended_action }}
                                </div>
                            @else
                                <span class="text-xs text-green-600">V pořádku</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                            Žádné záznamy k zobrazení.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
