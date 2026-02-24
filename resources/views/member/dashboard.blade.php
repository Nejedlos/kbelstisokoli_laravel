@extends('layouts.member', [
    'title' => __('dashboard.title'),
    'subtitle' => __('dashboard.subtitle')
])

@section('content')
    <div class="space-y-10">
        <!-- Profile Summary -->
        <section class="card sport-card-accent p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="relative">
                    @if(!empty($avatarUrl))
                        <img src="{{ $avatarUrl }}" alt="avatar" class="w-20 h-20 rounded-full object-cover border-4 border-slate-100">
                    @else
                        <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-3xl font-black border-4 border-slate-100">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-black text-secondary leading-none">{{ $user->display_name ?? $user->name }}</h2>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($user->roles->pluck('name') as $role)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600">{{ $role }}</span>
                                @endforeach
                                @if($user->membership_status)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-success-50 text-success-700">{{ __('member.profile.player_card.active_member') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('member.profile.edit') }}" class="btn btn-outline text-xs"><i class="fa-light fa-user-gear mr-1.5"></i> {{ __('member.dashboard.actions.edit_profile') }}</a>
                            <a href="{{ route('member.attendance.index') }}" class="btn btn-outline text-xs"><i class="fa-light fa-calendar-star mr-1.5"></i> {{ __('member.dashboard.actions.my_program') }}</a>
                            <a href="{{ route('member.economy.index') }}" class="btn btn-primary text-xs"><i class="fa-light fa-credit-card mr-1.5"></i> {{ __('member.dashboard.actions.payments') }}</a>
                            <a href="{{ route('member.contact.coach.form') }}" class="btn btn-outline text-xs"><i class="fa-light fa-whistle mr-1.5"></i> {{ __('member.feedback.contact_coach_title') }}</a>
                            <a href="{{ route('member.contact.admin.form') }}" class="btn btn-outline text-xs"><i class="fa-light fa-envelope mr-1.5"></i> {{ __('member.feedback.contact_admin_title') }}</a>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-3 rounded-club bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.dashboard.profile.payment_vs') }}</div>
                            <div class="font-bold text-secondary">{{ $user->payment_vs ?: '-' }}</div>
                        </div>
                        <div class="p-3 rounded-club bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.dashboard.profile.member_id') }}</div>
                            <div class="font-bold text-secondary">{{ $user->club_member_id ?: '-' }}</div>
                        </div>
                        <div class="p-3 rounded-club bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.dashboard.profile.teams') }}</div>
                            <div class="font-bold text-secondary">
                                @if($myTeams->count() > 0)
                                    {{ $myTeams->pluck('name')->implode(', ') }}
                                @else
                                    <span class="text-slate-400">{{ __('member.dashboard.profile.no_teams') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- KPI Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-member.kpi-card
                :title="__('dashboard.kpi.pending')"
                :value="$pendingCount"
                icon="heroicon-o-exclamation-triangle"
                color="primary"
                :route="route('member.attendance.index')"
            />
            <x-member.kpi-card
                :title="__('dashboard.kpi.my_teams')"
                :value="$myTeams->count()"
                icon="heroicon-o-users"
                color="secondary"
                :route="route('member.teams.index')"
            />
            <x-member.kpi-card
                :title="__('dashboard.kpi.my_payments')"
                :value="number_format($economySummary['total_to_pay'] ?? 0, 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-credit-card"
                color="info"
                :route="route('member.economy.index')"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Upcoming Program -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('dashboard.upcoming_program') }}</h3>
                    <a href="{{ route('member.attendance.index') }}" class="text-xs font-bold text-primary hover:underline uppercase tracking-widest">{{ __('dashboard.view_all') }} &rarr;</a>
                </div>

                @if($upcoming->isEmpty())
                    <div class="card p-10 text-center text-slate-400 italic">
                        {{ __('dashboard.no_upcoming') }}
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
                <!-- Activity -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.dashboard.activity.title') }}</h3>
                        <a href="{{ route('member.notifications.index') }}" class="text-[10px] font-black uppercase tracking-widest text-primary hover:underline">{{ __('member.dashboard.activity.view_all') }}</a>
                    </div>
                    @if($notifications->isEmpty())
                        <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-club text-center text-xs text-slate-400">
                            {{ __('member.dashboard.activity.empty') }}
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($notifications as $n)
                                <a href="{{ data_get($n->data, 'action_url', route('member.notifications.index')) }}" class="card p-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                                    <div>
                                        <div class="font-bold text-secondary">{{ data_get($n->data, 'title', __('member.notifications.default_title')) }}</div>
                                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $n->created_at->diffForHumans() }}</div>
                                    </div>
                                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 group-hover:text-primary transition-colors" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Economy Summary -->
                <section class="space-y-4">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.dashboard.economy.title') }}</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 rounded-club bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.economy.kpi.total_to_pay') }}</div>
                            <div class="text-xl font-black text-secondary">{{ number_format($economySummary['total_to_pay'] ?? 0, 0, ',', ' ') }} Kč</div>
                        </div>
                        <div class="p-4 rounded-club bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.economy.kpi.overdue') }}</div>
                            <div class="text-xl font-black {{ ($economySummary['overdue_amount'] ?? 0) > 0 ? 'text-danger-600' : 'text-secondary' }}">{{ number_format($economySummary['overdue_amount'] ?? 0, 0, ',', ' ') }} Kč</div>
                        </div>
                    </div>
                    <a href="{{ route('member.economy.index') }}" class="btn btn-outline w-full py-2 text-xs">{{ __('member.dashboard.economy.cta') }}</a>
                </section>

                <!-- Coach Tools (if applicable) -->
                @if(auth()->user()->can('manage_teams') && count($coachTeams) > 0)
                    <section class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('dashboard.coach_tools') }}</h3>
                            <span class="text-[10px] px-2 py-0.5 bg-secondary text-white rounded-full font-black uppercase">{{ __('dashboard.coach_badge') }}</span>
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
                        <h3 class="text-sm font-black uppercase tracking-tight text-primary">{{ __('dashboard.missing_profile') }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                            {{ __('dashboard.missing_profile_text') }}
                        </p>
                        <a href="{{ route('public.contact.index') }}" class="btn btn-primary w-full py-2 text-[10px]">{{ __('dashboard.contact_admin') }}</a>
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection
