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
                :value="$summary['total_to_pay'] . ' Kč'"
                icon="heroicon-o-banknotes"
                color="primary"
            />
            <x-member.kpi-card
                title="Po splatnosti"
                :value="$summary['overdue'] . ' Kč'"
                icon="heroicon-o-clock"
                color="danger"
            />
            <x-member.kpi-card
                title="Uhrazeno (sezóna)"
                :value="$summary['paid'] . ' Kč'"
                icon="heroicon-o-check-badge"
                color="success"
            />
        </div>

        <!-- Info / Disclaimer -->
        <div class="bg-primary/5 border border-primary/20 rounded-club p-6 flex flex-col md:flex-row items-center gap-6">
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                <x-heroicon-o-information-circle class="w-6 h-6" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-black uppercase tracking-tight text-primary mb-1">Modul plateb se připravuje</h4>
                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                    Pracujeme na automatizovaném systému plateb a příspěvků. Brzy zde uvidíte své variabilní symboly, historii plateb a stav vašeho členského konta. Aktuálně prosím řešte platby přímo s vedením klubu nebo trenérem.
                </p>
            </div>
        </div>

        <!-- Placeholder History -->
        <div class="space-y-6">
            <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Historie plateb</h3>

            <div class="card p-12 text-center border-dashed border-2 border-slate-200 bg-transparent shadow-none">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                    <x-heroicon-o-rectangle-stack class="w-8 h-8" />
                </div>
                <p class="text-slate-400 italic text-sm">Zatím nebyly zaznamenány žádné platební pohyby.</p>
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
