@extends('layouts.member', [
    'title' => 'Nástěnka',
    'subtitle' => 'Vítejte v členské sekci klubu ###TEAM_NAME###.'
])

@section('content')
    <div class="space-y-10">
        <!-- KPI Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-member.kpi-card
                title="Čeká na potvrzení"
                :value="$pendingCount"
                icon="heroicon-o-exclamation-triangle"
                color="primary"
                :route="route('member.attendance.index')"
            />
            <x-member.kpi-card
                title="Moje týmy"
                :value="$myTeams->count()"
                icon="heroicon-o-users"
                color="secondary"
                :route="route('member.profile.edit')"
            />
            <x-member.kpi-card
                title="Moje platby"
                value="0 Kč"
                icon="heroicon-o-credit-card"
                color="info"
                :route="route('member.economy.index')"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Latest Events -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Nejbližší program</h3>
                    <a href="{{ route('member.attendance.index') }}" class="text-xs font-bold text-primary hover:underline uppercase tracking-widest">Zobrazit vše &rarr;</a>
                </div>

                @if($upcoming->isEmpty())
                    <div class="card p-10 text-center text-slate-400 italic">
                        Žádné nadcházející akce nebyly nalezeny.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($upcoming as $event)
                            <x-member.event-card :event="$event" />
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Side Panels -->
            <div class="space-y-10">
                <!-- My Teams -->
                <section class="space-y-4">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Moje týmy</h3>
                    @forelse($myTeams as $team)
                        <div class="card p-4 flex items-center justify-between">
                            <span class="font-bold text-secondary">{{ $team->name }}</span>
                            <span class="text-[10px] px-2 py-0.5 bg-slate-100 rounded-full font-black uppercase text-slate-500">Hráč</span>
                        </div>
                    @empty
                        <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-club text-center text-xs text-slate-400">
                            Nejste přiřazeni k žádnému týmu.
                        </div>
                    @endforelse
                </section>

                <!-- Coach Tools (if applicable) -->
                @if(auth()->user()->can('manage_teams') && count($coachTeams) > 0)
                    <section class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Správa týmů</h3>
                            <span class="text-[10px] px-2 py-0.5 bg-secondary text-white rounded-full font-black uppercase">Trenér</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($coachTeams as $team)
                                <a href="{{ route('member.teams.show', $team) }}" class="card p-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                                    <span class="font-bold text-secondary">{{ $team->name }}</span>
                                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 group-hover:text-primary transition-colors" />
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Profile Completion -->
                @if(!auth()->user()->playerProfile)
                    <section class="bg-primary/5 border border-primary/20 rounded-club p-6 space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-tight text-primary">Chybí hráčský profil</h3>
                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                            Váš účet zatím nemá přiřazený hráčský profil. Bez něj nemůžeme sledovat vaši docházku a statistiky.
                        </p>
                        <a href="{{ route('public.contact.index') }}" class="btn btn-primary w-full py-2 text-[10px]">Kontaktovat admina</a>
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection
