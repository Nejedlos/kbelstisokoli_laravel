<tr class="hover:bg-brand-50/30 transition-colors">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-400">
                {{ $stat->values['col_0'] ?? ($stat->metadata['jersey'] ?? ($stat->source_metadata['player_number'] ?? '#')) }}
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold text-gray-900">
                    {{ $stat->player?->name ?? ($stat->row_label ?: ($stat->values['col_1'] ?? 'Hráč')) }}
                </span>
                @if(!empty($stat->values['is_starter']) || !empty($stat->source_metadata['is_starter']))
                    <span class="text-[9px] font-bold text-brand-500 uppercase tracking-tighter">Základní pětka</span>
                @endif
            </div>
        </div>
    </td>
    <td class="px-4 py-4 text-center">
        <span class="text-base font-black text-gray-900 tabular-nums">
            {{ $stat->values['pts'] ?? ($stat->values['points'] ?? 0) }}
        </span>
    </td>
    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
        {{ $stat->values['minutes'] ?? ($stat->values['min'] ?? '-') }}
    </td>
    <td class="px-4 py-4 text-center text-sm font-bold text-green-600 tabular-nums">
        {{ $stat->values['fouls_drawn'] ?? ($stat->values['f_plus'] ?? 0) }}
    </td>
    <td class="px-4 py-4 text-center text-sm font-bold text-red-500 tabular-nums">
        {{ $stat->values['fouls'] ?? ($stat->values['f_minus'] ?? 0) }}
    </td>
    <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums">
        {{ $stat->values['ft_made'] ?? 0 }}/{{ $stat->values['ft_att'] ?? 0 }}
    </td>
    <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums">
        {{ $stat->values['fg2_made'] ?? 0 }}/{{ $stat->values['fg2_att'] ?? 0 }}
    </td>
    <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums">
        {{ $stat->values['fg3_made'] ?? 0 }}/{{ $stat->values['fg3_att'] ?? 0 }}
    </td>
    <td class="px-4 py-4 text-center">
        @php
            $val = $stat->values['efficiency'] ?? ($stat->values['val'] ?? 0);
        @endphp
        <span class="px-2 py-1 rounded-lg text-xs font-black tabular-nums {{ $val >= 15 ? 'bg-green-100 text-green-700' : ($val < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
            {{ $val }}
        </span>
    </td>
</tr>
