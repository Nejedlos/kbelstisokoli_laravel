@php
    $statisticSet = \App\Models\StatisticSet::with(['rows' => function($q) use ($data) {
        $q->where('is_visible', true)->limit($data['limit'] ?? 10);
    }])->find($data['statistic_set_id']);

    $columns = $statisticSet->column_config ?? [];
@endphp

<section class="stats-table-block py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        @if(!empty($data['title']))
            <h2 class="text-3xl font-bold mb-8 text-primary">{{ $data['title'] }}</h2>
        @endif

        @if($statisticSet)
            <div class="overflow-x-auto shadow-lg rounded-lg">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                #
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Účastník
                            </th>
                            @foreach($columns as $col)
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ $col['label'] ?? $col['key'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statisticSet->rows as $row)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <div class="font-bold text-gray-900">
                                        {{ $row->player?->name ?? $row->team?->name ?? $row->row_label }}
                                    </div>
                                </td>
                                @foreach($columns as $col)
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        @php
                                            $val = $row->values[$col['key']] ?? '-';
                                            if (($col['type'] ?? '') === 'percentage' && is_numeric($val)) {
                                                $val .= '%';
                                            }
                                        @endphp
                                        {{ $val }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 2 }}" class="px-5 py-5 bg-white text-sm text-center italic text-gray-500">
                                    Žádná data nejsou k dispozici.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($data['show_link']))
                <div class="mt-4 text-right">
                    <a href="#" class="text-primary hover:underline font-semibold">Zobrazit celou tabulku &rarr;</a>
                </div>
            @endif
        @else
            <div class="p-8 text-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <p class="text-gray-500 italic">Statistická tabulka nebyla vybrána nebo není publikována.</p>
            </div>
        @endif
    </div>
</section>
