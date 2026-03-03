<div class="space-y-8">
    {{-- Header with Selection --}}
    <div class="flex flex-col md:flex-row justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 md:mb-0">
            <i class="fa-light fa-chart-line-up mr-2 text-primary-500"></i> Moje statistiky
        </h2>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full md:w-auto">
            <div class="relative flex-1 sm:w-48">
                <select wire:model.change="seasonId" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary-500 py-2 pl-3 pr-10 appearance-none">
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                    <i class="fa-light fa-chevron-down text-xs"></i>
                </div>
            </div>

            <div class="relative flex-1 sm:w-48">
                <select wire:model.change="teamId" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary-500 py-2 pl-3 pr-10 appearance-none">
                    <option value="">Všechny týmy</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                    <i class="fa-light fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    @if($summary)
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        @php
            $cards = [
                ['label' => 'Zápasy', 'value' => $summary['gp'] ?? 0, 'icon' => 'fa-basketball'],
                ['label' => 'Body', 'value' => $summary['pts_total'] ?? 0, 'icon' => 'fa-bullseye'],
                ['label' => 'Průměr', 'value' => $summary['ppg'] ?? 0, 'icon' => 'fa-calculator'],
                ['label' => 'Minuty Ø', 'value' => $summary['minutes_avg'] ?? 0, 'icon' => 'fa-clock'],
                ['label' => '2B %', 'value' => ($summary['fg2_pct'] ?? 0) . '%', 'icon' => 'fa-arrow-progress'],
                ['label' => '3B %', 'value' => ($summary['fg3_pct'] ?? 0) . '%', 'icon' => 'fa-arrow-up-right-dots'],
                ['label' => 'TH %', 'value' => ($summary['ft_pct'] ?? 0) . '%', 'icon' => 'fa-bullseye-arrow'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1 uppercase tracking-wider">
                {{ $card['label'] }}
            </div>
            <div class="text-2xl font-black text-primary-600 dark:text-primary-400">
                {{ $card['value'] }}
            </div>
            <div class="mt-2 text-gray-400">
                <i class="fa-light {{ $card['icon'] }} text-xs opacity-50"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Graphs Container --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Points Timeline --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 h-80 flex flex-col">
            <h3 class="font-bold text-gray-700 dark:text-gray-300 mb-4 uppercase text-xs tracking-widest flex items-center">
                <i class="fa-light fa-chart-line mr-2"></i> Vývoj bodů v sezóně
            </h3>
            <div class="flex-1 w-full bg-gray-50 dark:bg-gray-900/50 rounded-lg flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700">
                <div id="points-chart-skeleton" class="text-gray-400 text-xs text-center px-4">
                    <p class="mb-2">Zde se vykreslí spojnicový graf ApexCharts</p>
                    <div class="flex space-x-1 justify-center">
                        <div class="w-1 h-4 bg-primary-200 rounded"></div>
                        <div class="w-1 h-8 bg-primary-300 rounded"></div>
                        <div class="w-1 h-12 bg-primary-400 rounded"></div>
                        <div class="w-1 h-6 bg-primary-300 rounded"></div>
                        <div class="w-1 h-10 bg-primary-500 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shooting Splits --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 h-80 flex flex-col">
            <h3 class="font-bold text-gray-700 dark:text-gray-300 mb-4 uppercase text-xs tracking-widest flex items-center">
                <i class="fa-light fa-chart-bar mr-2"></i> Střelba (2B / 3B / TH)
            </h3>
            <div class="flex-1 w-full bg-gray-50 dark:bg-gray-900/50 rounded-lg flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700">
                 <div id="shooting-chart-skeleton" class="text-gray-400 text-xs text-center px-4">
                    <p class="mb-2">Zde se vykreslí sloupcový graf úspěšnosti</p>
                    <div class="flex space-x-4 justify-center items-end">
                        <div class="w-8 h-20 bg-blue-400 rounded-t opacity-50"></div>
                        <div class="w-8 h-12 bg-red-400 rounded-t opacity-50"></div>
                        <div class="w-8 h-16 bg-green-400 rounded-t opacity-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Match Log --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-bold text-gray-700 dark:text-gray-300 uppercase text-xs tracking-widest flex items-center">
                <i class="fa-light fa-list-ol mr-2"></i> Zápas po zápase
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="px-6 py-3 font-medium">Datum</th>
                        <th class="px-6 py-3 font-medium">Soupeř</th>
                        <th class="px-6 py-3 font-medium text-center">Body</th>
                        <th class="px-6 py-3 font-medium text-center">2B</th>
                        <th class="px-6 py-3 font-medium text-center">3B</th>
                        <th class="px-6 py-3 font-medium text-center">TH</th>
                        <th class="px-6 py-3 font-medium text-center">F</th>
                        <th class="px-6 py-3 font-medium text-center">VAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($perGameSeries as $match)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($match['date'])->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-200">
                            {{ $match['opponent'] }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-primary-600 dark:text-primary-400">
                            {{ $match['values']['pts'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">
                            {{ ($match['values']['fg2_made'] ?? 0) }}/{{ ($match['values']['fg2_att'] ?? 0) }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">
                            {{ ($match['values']['fg3_made'] ?? 0) }}/{{ ($match['values']['fg3_att'] ?? 0) }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">
                            {{ ($match['values']['ft_made'] ?? 0) }}/{{ ($match['values']['ft_att'] ?? 0) }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">
                            {{ $match['values']['fouls'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-center font-mono text-xs">
                            {{ $match['values']['efficiency'] ?? 0 }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic">
                            Pro zvolenou kombinaci sezóny a týmu nebyly nalezeny žádné statistiky.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 p-12 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
        <i class="fa-light fa-chart-simple text-4xl text-gray-200 dark:text-gray-700 mb-4 block"></i>
        <h3 class="text-lg font-bold text-gray-600 dark:text-gray-400">Žádná data k dispozici</h3>
        <p class="text-gray-400 text-sm mt-2">Zkuste vybrat jinou sezónu nebo tým.</p>
    </div>
    @endif
</div>
