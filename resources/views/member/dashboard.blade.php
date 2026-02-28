@extends('layouts.member', [
    'title' => __('dashboard.title'),
    'subtitle' => __('dashboard.subtitle')
])

@section('content')
    <div class="space-y-10">
        <!-- Profile Summary -->
        <section class="relative group rounded-[2.5rem]">
            <!-- Background Layer -->
            <div class="absolute inset-0 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-xl shadow-slate-200/40"></div>

            <!-- Decorative Elements (Clipped) -->
            <div class="absolute inset-0 rounded-[2.5rem] overflow-hidden pointer-events-none">
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -mr-20 -mt-20 group-hover:bg-primary/10 transition-colors duration-700"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-secondary/5 rounded-full blur-3xl -ml-10 -mb-10"></div>
            </div>

            <div class="relative p-6 sm:p-8 md:p-12 flex flex-col lg:flex-row lg:items-center gap-6 sm:gap-10">
                <!-- Avatar Section -->
                <div class="relative flex-shrink-0 flex justify-center lg:justify-start">
                    <div class="absolute -inset-2 bg-gradient-to-tr from-primary to-accent rounded-[2rem] opacity-20 blur-xl group-hover:opacity-40 transition-opacity duration-700"></div>
                    <div class="relative w-28 h-28 sm:w-32 sm:h-32 md:w-40 md:h-40 rounded-[2rem] overflow-hidden border-4 border-white shadow-2xl group-hover:scale-[1.02] transition-transform duration-500">
                        <img src="{{ $avatarUrl }}" alt="avatar" class="w-full h-full object-cover">

                        <!-- Edit Overlay -->
                        <a href="{{ route('member.profile.edit') }}" class="absolute inset-0 bg-secondary/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center text-white">
                            <i class="fa-light fa-camera-retro text-2xl mb-2"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ __('member.profile.avatar.change') }}</span>
                        </a>
                    </div>

                    <!-- Status Badge -->
                    <div class="absolute -bottom-2 right-1/2 translate-x-1/2 lg:right-0 lg:translate-x-1/4 px-3 sm:px-4 py-1.5 bg-white rounded-xl shadow-lg border border-slate-100 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-secondary">{{ __('member.profile.player_card.active_member') }}</span>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="flex-1 space-y-6 text-center lg:text-left">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
                            @foreach($user->roles->pluck('name') as $role)
                                <span class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-[0.2em] shadow-sm">{{ $role }}</span>
                            @endforeach
                            <span class="text-[10px] font-bold text-slate-400 italic">{{ __('member.dashboard.profile.member_id') }}: <span class="text-secondary">{{ $user->club_member_id ?: '-' }}</span></span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-secondary leading-tight tracking-tight">
                            {{ $user->display_name ?? $user->name }}
                        </h2>
                    </div>

                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-2">
                        <a href="{{ route('member.attendance.index') }}" class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 rounded-2xl bg-primary text-white text-[11px] font-black uppercase tracking-widest hover:bg-primary-hover shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all w-full xs:w-auto">
                            <i class="fa-light fa-calendar-star"></i>
                            {{ __('member.dashboard.actions.my_program') }}
                        </a>
                        <a href="{{ route('member.economy.index') }}" class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 rounded-2xl bg-white border border-slate-200 text-secondary text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 hover:-translate-y-0.5 transition-all shadow-sm w-full xs:w-auto">
                            <i class="fa-light fa-credit-card"></i>
                            {{ __('member.dashboard.actions.payments') }}
                        </a>
                        <div class="h-11 w-px bg-slate-100 mx-1 hidden md:block"></div>
                        <a href="{{ route('member.profile.edit') }}" class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 rounded-2xl bg-slate-100 text-slate-600 text-[11px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all w-full xs:w-auto">
                            <i class="fa-light fa-user-gear"></i>
                            {{ __('member.dashboard.actions.edit_profile') }}
                        </a>
                    </div>
                </div>

                <!-- Secondary Stats -->
                <div class="lg:w-64 space-y-4 pt-6 lg:pt-0 lg:border-l lg:border-slate-100 lg:pl-10 grid grid-cols-2 lg:grid-cols-1 gap-4 lg:gap-0">
                    <div class="group/stat">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1 block">{{ __('member.dashboard.profile.payment_vs') }}</span>
                        <span class="text-xl font-black text-secondary group-hover/stat:text-primary transition-colors">{{ $user->payment_vs ?: '-' }}</span>
                    </div>
                    <div class="group/stat">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1 block">{{ __('member.dashboard.profile.teams') }}</span>
                        <div class="flex flex-wrap gap-1.5 justify-center lg:justify-start">
                            @if($myTeams->count() > 0)
                                @foreach($myTeams as $team)
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 border border-slate-200/50">{{ $team->name }}</span>
                                @endforeach
                            @else
                                <span class="text-xs font-bold text-slate-400 italic">{{ __('member.dashboard.profile.no_teams') }}</span>
                            @endif
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
                icon="calendar-exclamation"
                color="primary"
                :route="route('member.attendance.index')"
            />
            <x-member.kpi-card
                :title="__('dashboard.kpi.my_teams')"
                :value="$myTeams->count()"
                icon="user-group"
                color="secondary"
                :route="route('member.teams.index')"
            />
            <x-member.kpi-card
                :title="__('dashboard.kpi.my_payments')"
                :value="number_format($economySummary['total_to_pay'] ?? 0, 0, ',', ' ') . ' Kč'"
                icon="wallet"
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
                                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                        <i class="fa-light fa-chevron-right text-[10px]"></i>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Economy Summary -->
                <section class="space-y-4">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.dashboard.economy.title') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                    <div class="relative group rounded-[2.5rem]">
                        <!-- Background with shadow -->
                        <div class="absolute inset-0 bg-gradient-to-br from-white to-slate-50/50 rounded-[2.5rem] border border-slate-200/60 shadow-sm transition-all duration-500 group-hover:shadow-md group-hover:border-primary/10"></div>

                        <!-- Decorative background icon clipped -->
                        <div class="absolute inset-0 rounded-[2.5rem] overflow-hidden pointer-events-none">
                            <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700">
                                <i class="fa-light fa-whistle text-8xl text-secondary"></i>
                            </div>
                        </div>

                        <div class="relative p-6 z-10 space-y-6">
                            <!-- Icon and Text -->
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm shadow-primary/5 group-hover:scale-110 group-hover:rotate-3 transition-transform">
                                    <i class="fa-light fa-whistle text-xl"></i>
                                </div>
                                <div class="space-y-1.5 flex-1">
                                    <h4 class="text-sm font-black uppercase tracking-tight text-secondary leading-tight">{{ __('member.feedback.contact_coach_title') }}</h4>
                                    <p class="text-[11px] text-slate-500 font-medium leading-relaxed italic opacity-80">{{ __('member.feedback.hints.economy') }}</p>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-3">
                                <a href="{{ route('member.contact.coach.form') }}" class="btn btn-outline py-2.5 px-4 text-[10px] bg-white hover:border-primary/30 hover:text-primary transition-all flex items-center justify-center gap-2">
                                    <i class="fa-light fa-comment-dots text-xs"></i>
                                    {{ __('member.feedback.contact_coach_title') }}
                                </a>
                                <a href="{{ route('member.contact.admin.form') }}" class="btn py-2.5 px-4 text-[10px] bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all border-none shadow-none flex items-center justify-center gap-2">
                                    <i class="fa-light fa-user-gear text-xs"></i>
                                    {{ __('member.feedback.contact_admin_title') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </section>


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

        <!-- Bank Info & QR Payment -->
        <livewire:member.payment-widget />
    </div>
@endsection
