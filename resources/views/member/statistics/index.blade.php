@extends('layouts.member', [
    'title' => $title ?? __('nav.statistics'),
])

@section('content')
    <div class="space-y-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-member.kpi-card
                :title="__('nav.my_statistics')"
                value="-"
                icon="chart-bar"
                color="primary"
                :route="route('member.statistics.me')"
            />
            <x-member.kpi-card
                :title="__('nav.players_statistics')"
                value="-"
                icon="users"
                color="secondary"
                :route="route('member.statistics.players')"
            />
            <x-member.kpi-card
                :title="__('nav.matches_statistics')"
                value="-"
                icon="trophy"
                color="info"
                :route="route('member.statistics.matches')"
            />
        </div>

        <div class="card p-12 text-center space-y-4 bg-white/60 backdrop-blur-sm border-dashed">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-light fa-chart-line text-4xl text-slate-300"></i>
            </div>
            <h3 class="text-xl font-black uppercase tracking-tight text-secondary">{{ __('Právě připravujeme') }}</h3>
            <p class="text-slate-500 max-w-md mx-auto italic font-medium">
                {{ __('Na této sekci právě pracujeme. Již brzy zde uvidíte své body, doskoky i výsledky celého týmu.') }}
            </p>
        </div>
    </div>
@endsection
