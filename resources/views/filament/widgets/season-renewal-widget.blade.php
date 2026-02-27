<div>
    <div class="fi-section rounded-club overflow-hidden border border-gray-200 bg-white p-0 dark:border-white/10 dark:bg-gray-900 shadow-sm">
        {{-- Zdravotní sekce (Stále viditelná pro admina) --}}
        <div class="p-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black uppercase tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-light fa-heart-pulse text-primary-600"></i>
                        @if($isNejedly)
                            Ahoj Michale, tady je stav kbelstisokoli.cz
                        @else
                            Stav a zdraví systému
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Přehled klíčových metrik a integrity dat v reálném čase</p>
                </div>
                @if($isDebug)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-full dark:bg-amber-900/40 dark:text-amber-400 flex items-center gap-1.5 shadow-sm border border-amber-200/50 dark:border-amber-800/30">
                        <i class="fa-solid fa-bug animate-pulse"></i> Debug Mode
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Mismatche --}}
                <div class="relative group p-5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-danger-200 dark:hover:border-danger-800/50 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-danger-100 flex items-center justify-center dark:bg-danger-900/20 shadow-inner">
                            <i class="fa-light fa-person-circle-exclamation text-xl text-danger-600"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-0.5">Rozpory v docházce</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $mismatchesCount }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-[10px] text-gray-400">Za posledních 30 dní</div>
                </div>

                {{-- Chybějící konfigurace --}}
                <div class="relative group p-5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-warning-200 dark:hover:border-warning-800/50 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-warning-100 flex items-center justify-center dark:bg-warning-900/20 shadow-inner">
                            <i class="fa-light fa-user-slash text-xl text-warning-600"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-0.5">Uživatelé bez platby</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $usersWithoutConfig }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-[10px] text-gray-400 text-truncate">Aktivní účty v probíhající sezóně</div>
                </div>

                {{-- Celkový dluh --}}
                <div class="relative group p-5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-primary-200 dark:hover:border-primary-800/50 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center dark:bg-primary-900/20 shadow-inner">
                            <i class="fa-light fa-money-bill-transfer text-xl text-primary-600"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-0.5">Otevřené pohledávky</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $totalDebt }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-[10px] text-gray-400">K dnešnímu dni</div>
                </div>

                {{-- Cron status --}}
                <div class="relative group p-5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-{{ $cronOk ? 'success' : 'danger' }}-200 dark:hover:border-{{ $cronOk ? 'success' : 'danger' }}-800/50 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl {{ $cronOk ? 'bg-success-100' : 'bg-danger-100' }} flex items-center justify-center {{ $cronOk ? 'dark:bg-success-900/20' : 'dark:bg-danger-900/20' }} shadow-inner">
                            <i class="fa-light fa-clock text-xl {{ $cronOk ? 'text-success-600' : 'text-danger-600' }}"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-0.5">Status úloh (Cron)</div>
                            <div class="text-sm font-black text-gray-900 dark:text-white leading-tight">{{ $lastCronRun }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-[10px] {{ $cronOk ? 'text-success-600' : 'text-danger-600' }} font-bold uppercase tracking-wider">
                        {{ $cronOk ? 'Vše v pořádku' : 'Systémová chyba' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Sekce obnovy sezóny (Podmíněně viditelná) --}}
        @if($showRenewalWarning)
        <div class="border-t border-warning-200 bg-warning-50 p-6 dark:border-warning-800/30 dark:bg-warning-950/20 transition-all">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-warning-100 flex items-center justify-center dark:bg-warning-900/40">
                    <i class="fa-light fa-calendar-circle-exclamation text-2xl text-warning-600 dark:text-warning-400"></i>
                </div>

                <div class="flex-1 text-center md:text-left">
                    <h4 class="text-lg font-black text-warning-900 tracking-tight dark:text-warning-100 uppercase">
                        Chybí konfigurace pro novou sezónu {{ $expectedSeason }}
                    </h4>
                    <p class="text-sm text-warning-800 mt-1 dark:text-warning-300">
                        Pro nadcházející sezónu ještě nebyly inicializovány finanční profily členů. Bez toho nebude možné správně účtovat příspěvky a sledovat docházku.
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <a href="{{ $renewalUrl }}" class="fi-btn fi-btn-color-warning fi-size-md relative inline-grid grid-flow-col items-center justify-center font-black uppercase tracking-widest outline-none transition duration-75 focus-visible:ring-2 rounded-club py-2.5 px-6 bg-amber-600 text-white hover:bg-amber-500 shadow-md border border-amber-700/50" style="background-color: #d97706 !important; color: white !important;">
                        <span class="flex items-center gap-2">
                            <i class="fa-light fa-arrows-rotate fa-fw"></i>
                            Inicializovat sezónu
                        </span>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
