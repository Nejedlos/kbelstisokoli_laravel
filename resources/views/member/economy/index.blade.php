@extends('layouts.member', [
    'title' => 'Platby a příspěvky',
    'subtitle' => 'Přehled vašich členských příspěvků a plateb klubu ###TEAM_NAME###.'
])

@section('content')
    <div class="space-y-10">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-member.kpi-card
                title="K úhradě celkem"
                :value="number_format($summary['total_to_pay'], 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-banknotes"
                color="primary"
            />
            <x-member.kpi-card
                title="Po splatnosti"
                :value="number_format($summary['overdue_amount'], 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-clock"
                color="danger"
            />
            <x-member.kpi-card
                title="Uhrazeno celkem"
                :value="number_format($summary['paid_total'], 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-check-badge"
                color="success"
            />
        </div>

        <!-- Open Charges (To Pay) -->
        <div class="space-y-6">
            <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Položky k úhradě</h3>

            @if($openCharges->isEmpty())
                <div class="card p-8 text-center border-dashed border-2 border-slate-200 bg-transparent shadow-none">
                    <p class="text-slate-400 italic text-sm">Aktuálně nemáte žádné neuhrazené předpisy. Skvělá práce!</p>
                </div>
            @else
                <div class="flex flex-col gap-4">
                    @foreach($openCharges as $charge)
                        <div class="card p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-l-4 {{ $charge->is_overdue ? 'border-l-danger' : 'border-l-primary' }}">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-slate-100 text-slate-500">
                                        {{ match($charge->charge_type) {
                                            'membership_fee' => 'Příspěvek',
                                            'camp_fee' => 'Soustředění',
                                            'tournament_fee' => 'Turnaj',
                                            default => 'Ostatní'
                                        } }}
                                    </span>
                                    @if($charge->is_overdue)
                                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-danger-50 text-danger-600">Po splatnosti</span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-lg text-secondary">{{ $charge->title }}</h4>
                                @if($charge->due_date)
                                    <p class="text-xs text-slate-500 font-medium">Splatnost do: <span class="font-bold">{{ $charge->due_date->format('d. m. Y') }}</span></p>
                                @endif
                            </div>

                            <div class="flex flex-row md:flex-col items-end justify-between md:justify-center gap-2">
                                <div class="text-right leading-tight">
                                    <div class="text-2xl font-black text-secondary">{{ number_format($charge->amount_remaining, 0, ',', ' ') }} Kč</div>
                                    @if($charge->amount_paid > 0)
                                        <div class="text-[10px] font-bold text-success uppercase">Zaplaceno: {{ number_format($charge->amount_paid, 0, ',', ' ') }} Kč</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- History (Recent Payments) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Poslední platby</h3>
                @if($recentPayments->isEmpty())
                    <p class="text-sm text-slate-400 italic">Zatím nebyly zaznamenány žádné platby.</p>
                @else
                    <div class="space-y-3">
                        @foreach($recentPayments as $payment)
                            <div class="bg-white rounded-club p-4 border border-slate-100 shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center text-success">
                                        <x-heroicon-o-arrow-down-left class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-secondary">{{ number_format($payment->amount, 0, ',', ' ') }} Kč</div>
                                        <div class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">{{ $payment->paid_at->format('d. m. Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-slate-50 text-slate-400">
                                        {{ $payment->variable_symbol ?: 'Bez VS' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Uhrazené položky</h3>
                @if($paidCharges->isEmpty())
                    <p class="text-sm text-slate-400 italic">Nemáte žádné plně uhrazené předpisy.</p>
                @else
                    <div class="space-y-3">
                        @foreach($paidCharges as $charge)
                            <div class="bg-white rounded-club p-4 border border-slate-100 shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                        <x-heroicon-o-check class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-600">{{ $charge->title }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">Uhrazeno {{ $charge->updated_at->format('d. m. Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right font-black text-success text-sm">OK</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Bank Info (Placeholder) -->
        <div class="card p-8 bg-secondary text-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h3 class="text-xl font-black uppercase tracking-tight text-primary leading-none">Bankovní spojení</h3>
                    <p class="text-sm text-slate-300 font-medium">Pro běžné členské příspěvky prosím používejte náš transparentní účet:</p>

                    <div class="space-y-2 pt-4">
                        <div class="flex justify-between border-b border-white/10 pb-2">
                            <span class="text-xs font-black uppercase tracking-widest text-white/40">Číslo účtu</span>
                            <span class="font-bold">123456789 / 0100</span>
                        </div>
                        <div class="flex justify-between border-b border-white/10 pb-2">
                            <span class="text-xs font-black uppercase tracking-widest text-white/40">Banka</span>
                            <span class="font-bold">Komerční banka a.s.</span>
                        </div>
                        <div class="flex justify-between border-b border-white/10 pb-2">
                            <span class="text-xs font-black uppercase tracking-widest text-white/40">Zpráva pro příjemce</span>
                            <span class="font-bold">Jméno a příjmení člena</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <div class="w-48 h-48 bg-white rounded-club p-4 flex items-center justify-center text-slate-300 relative">
                        <x-heroicon-o-qr-code class="w-32 h-32" />
                        <span class="absolute bottom-4 text-[10px] font-black uppercase tracking-widest text-slate-400">QR Platba</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
