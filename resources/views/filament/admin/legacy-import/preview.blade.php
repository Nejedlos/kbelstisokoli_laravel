<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold">{{ $data->tableName }}</h3>
        <span class="badge badge-info">{{ count($data->rows) }} řádků</span>
    </div>

    @if(!empty($data->warnings))
        <div class="p-3 text-sm rounded-lg bg-warning-50 text-warning-700 border border-warning-200">
            <ul class="list-disc list-inside">
                @foreach($data->warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    @foreach($data->columns as $column)
                        <th class="px-4 py-2 font-semibold">{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($data->rows, 0, 15) as $row)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        @foreach($data->columns as $column)
                            <td class="px-4 py-2">{{ $row->values[$column['key']] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(count($data->rows) > 15)
        <p class="text-xs text-center text-gray-500 italic">Zobrazeno prvních 15 řádků...</p>
    @endif
</div>
