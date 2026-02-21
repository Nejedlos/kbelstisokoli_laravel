@extends('layouts.member', [
    'title' => 'Historie docházky',
    'subtitle' => 'Přehled všech vašich dosavadních odpovědí a účastí.'
])

@section('content')
    <div class="space-y-6">
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Událost</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Datum</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Stav</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Poznámka</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Odpovězeno</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-secondary">
                                        @php $item = $attendance->attendable; @endphp
                                        @if($attendance->attendable_type === 'App\Models\BasketballMatch')
                                            {{ $item->team->name }} vs {{ $item->opponent->name }}
                                        @elseif($attendance->attendable_type === 'App\Models\Training')
                                            Trénink - {{ $item->team->name }}
                                        @else
                                            {{ $item->title ?? 'Neznámá akce' }}
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-0.5">
                                        {{ str_replace('App\Models\\', '', $attendance->attendable_type) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                    {{ $attendance->attendable?->scheduled_at?->format('d.m.Y H:i') ?? $attendance->attendable?->starts_at?->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $colors = [
                                            'pending' => 'bg-slate-100 text-slate-600',
                                            'confirmed' => 'bg-success-100 text-success-700',
                                            'declined' => 'bg-danger-100 text-danger-700',
                                            'maybe' => 'bg-warning-100 text-warning-700',
                                        ];
                                        $labels = [
                                            'pending' => 'Čeká',
                                            'confirmed' => 'Přijdu',
                                            'declined' => 'Omluven',
                                            'maybe' => 'Možná',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $colors[$attendance->status] ?? 'bg-slate-100' }}">
                                        {{ $labels[$attendance->status] ?? $attendance->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 italic">
                                    {{ $attendance->note ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs text-slate-400">
                                    {{ $attendance->responded_at?->format('d.m. H:i') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                    Zatím nemáte žádnou historii docházky.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $attendances->links() }}
        </div>
    </div>
@endsection
