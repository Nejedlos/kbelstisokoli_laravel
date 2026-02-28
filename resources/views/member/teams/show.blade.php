@extends('layouts.member', [
    'title' => $team->name,
    'subtitle' => __('member.teams.detail_subtitle')
])

@section('header_actions')
    <a href="{{ route('member.teams.index') }}" class="btn btn-outline py-2 text-xs">
        &larr; {{ __('member.teams.back_to_list') }}
    </a>
@endsection

@section('content')
    <div class="space-y-10">
        <!-- Team Stats Shell -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-member.kpi-card :title="__('member.teams.stats.players_in_team')" :value="$team->players_count ?? '-'" icon="heroicon-o-users" color="secondary" />
            <x-member.kpi-card :title="__('member.teams.stats.upcoming_matches')" :value="$upcomingMatches->count()" icon="heroicon-o-trophy" color="primary" />
            <x-member.kpi-card :title="__('member.teams.stats.trainings_week')" :value="$upcomingTrainings->count()" icon="heroicon-o-calendar" color="info" />
            <x-member.kpi-card :title="__('member.teams.stats.avg_attendance')" value="-%" icon="heroicon-o-chart-bar" color="success" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Upcoming Matches Attendance -->
            <section class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.teams.attendance.matches_title') }}</h3>
                @forelse($upcomingMatches as $match)
                    <div class="card p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                            <div>
                                <h4 class="font-bold text-secondary">vs {{ $match->opponent->name }}</h4>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    {{ $match->scheduled_at->format('d.m. H:i') }} • {{ $match->location ?: __('member.teams.attendance.location_not_specified') }}
                                </p>
                            </div>
                            <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-black uppercase tracking-widest text-slate-500">{{ __('member.teams.attendance.match_badge') }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-success-50 text-success-600 flex items-center justify-center font-black text-sm">
                                    {{ $match->confirmed_count }}
                                </div>
                                <span class="text-xs font-bold text-slate-600">{{ __('member.teams.attendance.confirmed') }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-right justify-end">
                                <span class="text-xs font-bold text-slate-600">{{ __('member.teams.attendance.declined') }}</span>
                                <div class="w-8 h-8 rounded-full bg-danger-50 text-danger-600 flex items-center justify-center font-black text-sm">
                                    {{ $match->declined_count }}
                                </div>
                            </div>
                        </div>
                        <div class="pt-2">
                            <a href="/admin/matches/{{ $match->id }}/edit" class="text-[10px] font-black uppercase text-primary hover:underline tracking-widest">{{ __('member.teams.attendance.admin_detail') }} &rarr;</a>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 italic text-sm p-8 bg-slate-50 rounded-club text-center">{{ __('member.teams.attendance.no_matches') }}</p>
                @endforelse
            </section>

            <!-- Upcoming Trainings -->
            <section class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.teams.attendance.trainings_title') }}</h3>
                <div class="space-y-4">
                    @forelse($upcomingTrainings as $training)
                        <div class="card p-5 flex items-center justify-between group hover:bg-slate-50 transition-colors cursor-default">
                            <div>
                                <h4 class="font-bold text-secondary">{{ $training->starts_at->translatedFormat('l d.m.') }}</h4>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    {{ $training->starts_at->format('H:i') }} - {{ $training->ends_at->format('H:i') }} • {{ $training->location ?: 'Kbely' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="px-3 py-1 bg-success-50 text-success-700 rounded-full font-black text-xs">
                                    {{ __('member.teams.attendance.confirmed_count', ['count' => $training->confirmed_count]) }}
                                </div>
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <i class="fa-light fa-chevron-right text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-400 italic text-sm p-8 bg-slate-50 rounded-club text-center">{{ __('member.teams.attendance.no_trainings') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
