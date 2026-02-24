@extends('layouts.member', [
    'title' => __('member.teams.title'),
    'subtitle' => __('member.teams.subtitle')
])

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($teams as $team)
            <div class="card overflow-hidden flex flex-col">
                <div class="p-6 bg-secondary text-white">
                    <h3 class="text-xl font-black uppercase tracking-tight mb-1">{{ $team->name }}</h3>
                    <p class="text-xs text-white/50 font-bold uppercase tracking-widest">{{ $team->category ?: __('member.teams.no_category') }}</p>
                </div>
                <div class="p-6 flex-1 space-y-4">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 bg-slate-50 rounded-club border border-slate-100">
                            <span class="block text-lg font-black text-secondary">{{ $team->players_count }}</span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('member.teams.stats.players') }}</span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-club border border-slate-100">
                            <span class="block text-lg font-black text-secondary">{{ $team->trainings_count }}</span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('member.teams.stats.trainings') }}</span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-club border border-slate-100">
                            <span class="block text-lg font-black text-secondary">{{ $team->games_count }}</span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('member.teams.stats.matches') }}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    <a href="{{ route('member.teams.show', $team) }}" class="btn btn-primary w-full py-2 text-xs">
                        {{ __('member.teams.view_detail') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state :title="__('member.teams.no_teams')" :subtitle="__('member.teams.no_teams_text')" />
            </div>
        @endforelse
    </div>
@endsection
