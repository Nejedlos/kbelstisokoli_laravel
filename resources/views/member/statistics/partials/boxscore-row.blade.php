@php
    // Robustní přístup: funguje pro model StatisticRow i pro prosté pole z JSONu
    $rowLabel = is_object($stat) ? $stat->row_label : ($stat['row_label'] ?? '');
    $values = is_object($stat) ? $stat->values : ($stat['values'] ?? []);
    $sourceMetadata = is_object($stat) ? ($stat->source_metadata ?? []) : ($stat['metadata'] ?? []);
    $player = is_object($stat) ? $stat->player : null;

    $isTotalRow = in_array(mb_strtolower($rowLabel), ['celkem', 'total']);
    $isTeamRow = in_array(mb_strtolower($rowLabel), ['tým/trenéři', 'team/coaches']);
    $isSpecialRow = $isTotalRow || $isTeamRow;
@endphp

<tr class="hover:bg-brand-50/30 transition-colors {{ $isSpecialRow ? 'bg-gray-100/50' : '' }}">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center gap-3">
            @if(!$isSpecialRow)
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-400">
                    {{ $values['col_0'] ?? ($sourceMetadata['jersey'] ?? ($sourceMetadata['player_number'] ?? '#')) }}
                </div>
            @else
                <div class="w-8 h-8 flex items-center justify-center text-gray-400">
                    <i class="fa-light {{ $isTotalRow ? 'fa-sigma' : 'fa-users-gear' }}"></i>
                </div>
            @endif
            <div class="flex flex-col">
                <span class="text-sm font-bold {{ $isSpecialRow ? 'text-gray-900' : 'text-gray-700' }}">
                    {{ $player?->name ?? ($rowLabel ?: ($values['col_1'] ?? 'Hráč')) }}
                </span>
                @if(!empty($values['is_starter']) || !empty($sourceMetadata['is_starter']))
                    <span class="text-[9px] font-bold text-brand-500 uppercase tracking-tighter">Základní pětka</span>
                @endif
            </div>
        </div>
    </td>
    <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums">
        {{ !$isTeamRow ? ($values['fg2_made'] ?? 0) : '-' }}
    </td>
    <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums">
        {{ !$isTeamRow ? ($values['fg3_made'] ?? 0) : '-' }}
    </td>
    <td class="px-4 py-4 text-center text-xs font-medium text-gray-500 tabular-nums">
        @if($isTeamRow)
            -
        @else
            {{ $values['ft_made'] ?? 0 }}/{{ $values['ft_att'] ?? 0 }}
        @endif
    </td>
    <td class="px-4 py-4 text-center text-sm font-bold text-red-500 tabular-nums">
        {{ $values['fouls'] ?? ($values['f_minus'] ?? 0) }}
    </td>
    <td class="px-4 py-4 text-center">
        <span class="text-base font-black text-gray-900 tabular-nums">
            {{ !$isTeamRow ? ($values['pts'] ?? ($values['points'] ?? 0)) : '-' }}
        </span>
    </td>
    <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
        {{ !$isTeamRow ? ($values['plus_minus'] ?? 0) : '-' }}
    </td>
    @if(isset($showExtended) && $showExtended)
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
            {{ !$isTeamRow ? ($values['minutes'] ?? ($values['min'] ?? '-')) : '-' }}
        </td>
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
            {{ !$isTeamRow ? ($values['rebounds'] ?? ($values['reb'] ?? 0)) : '-' }}
        </td>
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
            {{ !$isTeamRow ? ($values['assists'] ?? ($values['ast'] ?? 0)) : '-' }}
        </td>
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
            {{ !$isTeamRow ? ($values['steals'] ?? ($values['stl'] ?? 0)) : '-' }}
        </td>
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums text-red-400">
            {{ !$isTeamRow ? ($values['turnovers'] ?? ($values['tov'] ?? 0)) : '-' }}
        </td>
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-500 tabular-nums">
            {{ !$isTeamRow ? ($values['blocks'] ?? ($values['blk'] ?? 0)) : '-' }}
        </td>
        <td class="px-4 py-4 text-center text-sm font-bold text-green-600 tabular-nums">
            {{ !$isTeamRow ? ($values['fouls_drawn'] ?? ($values['f_plus'] ?? 0)) : '-' }}
        </td>
        <td class="px-4 py-4 text-center">
            @if($isTeamRow)
                -
            @else
                @php
                    $val = $values['efficiency'] ?? ($values['val'] ?? 0);
                @endphp
                <span class="px-2 py-1 rounded-lg text-xs font-black tabular-nums {{ $val >= 15 ? 'bg-green-100 text-green-700' : ($val < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ $val }}
                </span>
            @endif
        </td>
    @endif
</tr>
