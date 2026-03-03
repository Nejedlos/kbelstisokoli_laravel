<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
    {{-- Header --}}
    <div class="text-center space-y-4">
        <h1 class="text-4xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
            Týmová sezóna
        </h1>
        <div class="flex justify-center items-center space-x-4">
            <select wire:model.change="teamId" class="bg-transparent border-none text-xl font-bold text-primary-600 focus:ring-0 cursor-pointer">
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            <span class="text-gray-300">|</span>
            <select wire:model.change="seasonId" class="bg-transparent border-none text-xl font-bold text-gray-600 focus:ring-0 cursor-pointer">
                @foreach($seasons as $season)
                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($summary)
    {{-- Key Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Record Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="fa-light fa-trophy text-8xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Bilance (V-P)</h3>
                <div class="text-5xl font-black text-gray-900 dark:text-white">
                    <span class="text-green-500">{{ $summary['wins'] ?? 0 }}</span>
                    <span class="text-gray-300 mx-1">-</span>
                    <span class="text-red-500">{{ $summary['losses'] ?? 0 }}</span>
                </div>
                <div class="mt-4 text-xs text-gray-400 font-bold uppercase tracking-tighter">
                    Forma:
                    @foreach($recentForm as $f)
                        <span @class(['text-green-500' => $f['result'] === 'W', 'text-red-500' => $f['result'] === 'L'])>{{ $f['result'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Points Avg Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700 relative overflow-hidden group text-center">
             <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Průměr bodů</h3>
             <div class="text-5xl font-black text-primary-600">
                {{ $summary['pts_avg'] ?? 0 }}
             </div>
             <div class="mt-4 flex justify-center space-x-4 text-xs uppercase font-bold tracking-tighter">
                <div class="text-gray-500">PRO: {{ $summary['pts_for'] ?? 0 }}</div>
                <div class="text-gray-300">|</div>
                <div class="text-gray-500">PROTI: {{ $summary['pts_against'] ?? 0 }}</div>
             </div>
        </div>

        {{-- Status Card --}}
        <div class="bg-primary-600 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
             <div class="absolute -right-4 -bottom-4 opacity-20">
                <i class="fa-light fa-basketball-hoop text-9xl"></i>
             </div>
             <h3 class="text-sm font-bold text-primary-200 uppercase tracking-widest mb-2">Aktuální stav</h3>
             <div class="text-2xl font-bold mb-4">Sezóna {{ $seasons->where('id', $seasonId)->first()?->name }}</div>
             <div class="text-xs text-primary-100 leading-relaxed italic opacity-80">
                Data jsou pravidelně synchronizována z oficiálního zdroje cz.basketball.
             </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {{-- Top Scorers Table --}}
        <div class="space-y-6">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <i class="fa-light fa-medal mr-3 text-yellow-500"></i> Top střelci týmu
            </h2>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Hráč</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">Z</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">B celkem</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-center">B/Z</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($topScorers as $scorer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 dark:text-gray-200">{{ $scorer['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500">{{ $scorer['gp'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-300">{{ $scorer['pts_total'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 px-3 py-1 rounded-full text-sm font-black">
                                    {{ $scorer['ppg'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Žádná data o hráčích</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Team Performance Chart --}}
        <div class="space-y-6" wire:ignore>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <i class="fa-light fa-chart-line mr-3 text-blue-500"></i> Bodová ofenzíva vs defenzíva
            </h2>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-8 h-[400px]">
                <div id="public-team-chart" class="w-full h-full"></div>
            </div>
        </div>
    </div>

    @script
    <script>
        const initChart = () => {
            const seriesData = @json($pointsSeries);
            if (!seriesData || seriesData.length === 0) return;

            const dates = seriesData.map(m => new Date(m.date).toLocaleDateString('cs-CZ'));
            const ptsFor = seriesData.map(m => m.pts_for || 0);
            const ptsAgainst = seriesData.map(m => m.pts_against || 0);

            new ApexCharts(document.querySelector("#public-team-chart"), {
                series: [
                    { name: 'My', data: ptsFor },
                    { name: 'Soupeř', data: ptsAgainst }
                ],
                chart: { type: 'area', height: '100%', toolbar: { show: false } },
                colors: ['#e63946', '#2196f3'],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: dates, labels: { show: false } },
                dataLabels: { enabled: false }
            }).render();
        };

        initChart();

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('statsLoaded', () => {
                document.querySelector("#public-team-chart").innerHTML = '';
                initChart();
            });
        });
    </script>
    @endscript
    @else
    <div class="bg-white dark:bg-gray-800 p-24 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 text-center space-y-6">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-50 dark:bg-gray-900 rounded-full">
            <i class="fa-light fa-database-slash text-4xl text-gray-300"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Žádná statistická data</h2>
            <p class="text-gray-500 max-w-md mx-auto mt-2">
                Pro vybraný tým a sezónu zatím nemáme v systému žádná synchronizovaná data.
            </p>
        </div>
    </div>
    @endif
</div>
