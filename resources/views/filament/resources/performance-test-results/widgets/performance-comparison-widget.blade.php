<x-filament-widgets::widget>
    <x-filament::section :icon="\App\Support\IconHelper::get(\App\Support\IconHelper::DASHBOARD)" heading="Srovnání výkonnostních scénářů">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3 px-4 text-xs font-black uppercase tracking-widest text-slate-500">Sekce / Stránka</th>
                        <th class="py-3 px-4 text-xs font-black uppercase tracking-widest text-slate-500 text-center">Standard</th>
                        <th class="py-3 px-4 text-xs font-black uppercase tracking-widest text-slate-500 text-center">Aggressive</th>
                        <th class="py-3 px-4 text-xs font-black uppercase tracking-widest text-slate-500 text-center">Ultra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($comparison as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ match($row['section']) { 'public' => 'bg-success/10 text-success', 'member' => 'bg-info/10 text-info', 'admin' => 'bg-warning/10 text-warning', default => 'bg-gray/10 text-gray' } }}">
                                        {{ $row['section'] }}
                                    </span>
                                    <span class="font-bold text-slate-700">{{ $row['label'] }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($row['standard'])
                                    <div class="text-sm font-medium text-slate-600">{{ round($row['standard']->duration_ms) }} ms</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $row['standard']->query_count }} dotazů</div>
                                @else
                                    <span class="text-slate-300 italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($row['aggressive'])
                                    <div class="text-sm font-medium {{ ($row['aggressive_gain'] ?? 0) > 0 ? 'text-success' : 'text-slate-600' }}">
                                        {{ round($row['aggressive']->duration_ms) }} ms
                                    </div>
                                    @if(isset($row['aggressive_gain']))
                                        <div class="text-[10px] font-black {{ $row['aggressive_gain'] > 0 ? 'text-success' : 'text-slate-400' }}">
                                            @if($row['aggressive_gain'] > 0) <i class="fa-light fa-caret-up mr-0.5"></i> @endif
                                            {{ $row['aggressive_gain'] }}%
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-300 italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($row['ultra'])
                                    <div class="text-sm font-medium {{ ($row['ultra_gain'] ?? 0) > 0 ? 'text-success' : 'text-slate-600' }}">
                                        {{ round($row['ultra']->duration_ms) }} ms
                                    </div>
                                    @if(isset($row['ultra_gain']))
                                        <div class="text-[10px] font-black {{ $row['ultra_gain'] > 0 ? 'text-success' : 'text-slate-400' }}">
                                            @if($row['ultra_gain'] > 0) <i class="fa-light fa-caret-up mr-0.5"></i> @endif
                                            {{ $row['ultra_gain'] }}%
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-300 italic text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400 italic">
                                Zatím nebyly provedeny žádné testy výkonu. Spusťte je pomocí tlačítek v záhlaví.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
