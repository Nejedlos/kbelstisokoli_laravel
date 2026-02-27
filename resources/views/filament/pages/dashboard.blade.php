<x-filament-panels::page>
    <div class="space-y-8 pb-12">
        {{-- Hero / Welcome Section --}}
        <div class="relative overflow-hidden rounded-[2rem] bg-gray-900 shadow-2xl dark:bg-black">
            {{-- Background Elements --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-primary-600/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-secondary-600/10 blur-3xl"></div>

            <div class="relative flex flex-col lg:flex-row items-center gap-8 p-8 md:p-12">
                <div class="flex-1 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-primary-400 backdrop-blur-md">
                        <i class="fa-light fa-basketball animate-bounce"></i>
                        {{ __('Kbelští Sokoli Dashboard') }}
                    </div>

                    <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                        @if($isNejedly)
                            Ahoj Michale, <br><span class="text-primary-500">vzhůru do koše!</span>
                        @else
                            {{ __('admin/dashboard.welcome.title', ['name' => $userName]) }}
                        @endif
                    </h1>

                    <p class="max-w-xl text-lg text-gray-400 leading-relaxed mx-auto lg:mx-0">
                        {{ __('admin/dashboard.welcome.text', ['active_players' => number_format($stats['players'], 0, ',', ' ')]) }}
                    </p>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 pt-4">
                        <a href="{{ $actions['new_match'] }}" class="fi-btn fi-btn-color-primary fi-size-md relative inline-grid grid-flow-col items-center justify-center font-black uppercase tracking-widest outline-none transition duration-75 focus-visible:ring-2 rounded-xl py-2.5 px-6 bg-primary-600 text-white hover:bg-primary-500 shadow-xl shadow-primary-900/20 text-[10px]">
                            <span class="flex items-center gap-2">
                                <i class="fa-light fa-trophy-star fa-fw text-sm"></i>
                                {{ __('admin/dashboard.welcome.quick_actions.new_match') }}
                            </span>
                        </a>
                        <a href="{{ $actions['new_user'] }}" class="fi-btn fi-btn-color-gray fi-size-md relative inline-grid grid-flow-col items-center justify-center font-black uppercase tracking-widest outline-none transition duration-75 focus-visible:ring-2 rounded-xl py-2.5 px-6 bg-white/10 text-white hover:bg-white/20 backdrop-blur-md border border-white/10 text-[10px]">
                            <span class="flex items-center gap-2">
                                <i class="fa-light fa-user-plus fa-fw text-sm"></i>
                                {{ __('admin/dashboard.welcome.quick_actions.new_user') }}
                            </span>
                        </a>
                        <a href="{{ $actions['new_training'] }}" class="fi-btn fi-btn-color-gray fi-size-md relative inline-grid grid-flow-col items-center justify-center font-black uppercase tracking-widest outline-none transition duration-75 focus-visible:ring-2 rounded-xl py-2.5 px-6 bg-white/10 text-white hover:bg-white/20 backdrop-blur-md border border-white/10 text-[10px]">
                            <span class="flex items-center gap-2">
                                <i class="fa-light fa-clock fa-fw text-sm"></i>
                                {{ __('admin/dashboard.welcome.quick_actions.new_training') }}
                            </span>
                        </a>
                    </div>
                </div>

                <div class="relative w-full max-w-[320px] aspect-square hidden md:block">
                     <div class="absolute inset-0 bg-gradient-to-tr from-primary-600 to-secondary-500 rounded-3xl rotate-6 opacity-20 blur-xl"></div>
                     <div class="relative h-full w-full rounded-3xl border border-white/10 bg-white/5 backdrop-blur-2xl p-6 flex flex-col justify-center items-center text-center space-y-4">
                        <div class="relative flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full bg-primary-500/30 blur-xl animate-[pulse-aura_3s_ease-in-out_infinite]"></div>
                            <div class="relative w-20 h-20 rounded-full bg-white/10 flex items-center justify-center text-3xl shadow-inner border border-white/10">
                                <i class="fa-light fa-basketball text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-3xl font-black text-white leading-none">{{ $stats['matches']['upcoming'] }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-1">{{ __('admin/dashboard.kpi.matches_upcoming') }}</div>
                        </div>
                        <div class="w-full h-px bg-white/10"></div>
                        <div>
                            <div class="text-3xl font-black text-white leading-none">{{ $stats['trainings']['upcoming'] }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-1">{{ __('admin/dashboard.kpi.trainings_upcoming') }}</div>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid (Bento) --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

            {{-- KPI Stats - Left Col --}}
            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Finance Summary Card --}}
                <div class="sm:col-span-2 relative overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm p-8 flex flex-col md:flex-row items-center gap-8">
                     <div class="flex-1 space-y-4 w-full">
                        <div class="flex items-center gap-2 text-primary-600 font-black uppercase tracking-widest text-xs">
                            <i class="fa-light fa-wallet"></i>
                            {{ __('admin/dashboard.finance.total_receivables') }}
                        </div>
                        <div class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">
                            {{ number_format($finance['total_receivables'], 0, ',', ' ') }} <span class="text-xl font-bold text-gray-400">Kč</span>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('admin/dashboard.finance.overdue') }}</span>
                                <span class="text-lg font-black text-danger-600">{{ number_format($finance['overdue'], 0, ',', ' ') }} Kč</span>
                            </div>
                            <div class="w-px bg-gray-100 dark:bg-white/5"></div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('admin/dashboard.finance.payments_month') }}</span>
                                <span class="text-lg font-black text-success-600">+{{ number_format($finance['payments_month'], 0, ',', ' ') }} Kč</span>
                            </div>
                        </div>
                     </div>
                     <div class="w-full md:w-auto">
                        <a href="{{ $actions['finance'] }}" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-sm transition-transform hover:scale-105 active:scale-95 shadow-lg">
                            {{ __('admin/dashboard.welcome.quick_actions.finance_cta') }}
                            <i class="fa-light fa-arrow-right"></i>
                        </a>
                     </div>
                </div>

                {{-- Teams Card --}}
                <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-lg bg-warning-100 dark:bg-warning-900/20 flex items-center justify-center text-warning-600">
                            <i class="fa-light fa-users-viewfinder text-xl"></i>
                        </div>
                        <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['teams'] }}</span>
                    </div>
                    <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ __('admin/dashboard.kpi.teams_total') }}</h4>
                    <p class="text-xs text-gray-500 mt-1">Aktuálně registrované týmy</p>
                </div>

                {{-- Players Card --}}
                <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-lg bg-info-100 dark:bg-info-900/20 flex items-center justify-center text-info-600">
                            <i class="fa-light fa-user-group text-xl"></i>
                        </div>
                        <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['players'] }}</span>
                    </div>
                    <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ __('admin/dashboard.kpi.players_total') }}</h4>
                    <p class="text-xs text-gray-500 mt-1">Hráčské profily s historií</p>
                </div>

                {{-- Leads Card --}}
                <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/20 flex items-center justify-center text-success-600">
                            <i class="fa-light fa-address-card text-xl"></i>
                        </div>
                        <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['leads_pending'] }}</span>
                    </div>
                    <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ __('admin/dashboard.kpi.leads_total') }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ __('admin/dashboard.kpi.leads_pending_desc', ['count' => $stats['leads_pending']]) }}</p>
                </div>

                {{-- Posts Card --}}
                <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-lg bg-secondary-100 dark:bg-secondary-900/20 flex items-center justify-center text-secondary-600">
                            <i class="fa-light fa-newspaper text-xl"></i>
                        </div>
                        <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['posts_active'] }}</span>
                    </div>
                    <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ __('admin/dashboard.kpi.posts_total') }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ __('admin/dashboard.kpi.posts_active_desc', ['count' => $stats['posts_active']]) }}</p>
                </div>

                {{-- System Health Card --}}
                <div class="sm:col-span-2 rounded-3xl bg-gray-50 dark:bg-white/5 border border-dashed border-gray-200 dark:border-white/10 p-8">
                     <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl {{ $health['cron_ok'] ? 'bg-success-500' : 'bg-danger-500' }} flex items-center justify-center text-white shadow-lg {{ $health['cron_ok'] ? 'shadow-success-500/20' : 'shadow-danger-500/20' }}">
                                <i class="fa-light fa-heart-pulse text-2xl animate-pulse"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ __('admin/dashboard.system.title') }}</h4>
                                <p class="text-sm text-gray-500">{{ __('admin/dashboard.system.cron.last_run', ['time' => $health['last_cron']]) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-8">
                            <div class="text-center">
                                <div class="text-2xl font-black {{ $health['mismatches'] > 0 ? 'text-danger-600' : 'text-success-600' }} leading-none">{{ $health['mismatches'] }}</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('admin/dashboard.health.mismatches') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-black {{ $health['missing_configs'] > 0 ? 'text-warning-600' : 'text-success-600' }} leading-none">{{ $health['missing_configs'] }}</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('admin/dashboard.health.missing_configs') }}</div>
                            </div>
                        </div>

                        @if($health['show_renewal'])
                            <div class="flex-shrink-0 flex flex-col items-center lg:items-end gap-3">
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest animate-pulse text-center lg:text-right">
                                    {{ __('admin/dashboard.health.renewal_warning') }}
                                </span>
                                <a href="{{ $health['renewal_url'] }}" class="flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black uppercase tracking-widest text-xs shadow-lg shadow-amber-500/20 transition-all transform hover:-translate-y-1">
                                    <i class="fa-light fa-calendar-star text-sm"></i>
                                    {{ __('admin/dashboard.health.renewal_cta', ['season' => $health['expected_season']]) }}
                                </a>
                            </div>
                        @endif
                     </div>
                </div>
            </div>

            {{-- Activity & Feedback - Right Col --}}
            <div class="md:col-span-4 space-y-6">

                {{-- Recent Activity Card --}}
                <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 dark:border-white/5 bg-gray-50/50 dark:bg-white/5 flex items-center justify-between">
                        <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs flex items-center gap-2">
                            <i class="fa-light fa-bolt-auto text-primary-600"></i>
                            {{ __('admin/dashboard.recent_activity.title') }}
                        </h4>
                        <a href="{{ route('filament.admin.resources.audit-logs.index') }}" class="text-[10px] font-bold text-primary-600 uppercase hover:underline">Vše</a>
                    </div>
                    <div class="p-6 space-y-6">
                        @forelse($recentActivity as $log)
                            <div class="flex gap-4 group">
                                <div @class([
                                    'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center transition-colors',
                                    'bg-success-50 text-success-600 dark:bg-success-900/20' => $log->color === 'success',
                                    'bg-warning-50 text-warning-600 dark:bg-warning-900/20' => $log->color === 'warning',
                                    'bg-danger-50 text-danger-600 dark:bg-danger-900/20' => $log->color === 'danger',
                                    'bg-info-50 text-info-600 dark:bg-info-900/20' => $log->color === 'info',
                                    'bg-primary-50 text-primary-600 dark:bg-primary-900/20' => $log->color === 'primary',
                                    'bg-gray-100 text-gray-400 dark:bg-white/5' => $log->color === 'gray',
                                ])>
                                    <i class="fa-light {{ $log->icon }} text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-bold text-gray-900 dark:text-white leading-snug group-hover:text-primary-600 transition-colors">
                                        {{ $log->action }}: {{ $log->subject }}
                                    </div>
                                    @if($log->details)
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 italic mt-0.5">{{ $log->details }}</div>
                                    @endif
                                    <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-2">
                                        <span class="truncate font-medium text-gray-600 dark:text-gray-400">{{ $log->actor }}</span>
                                        <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                                        <span class="whitespace-nowrap">{{ $log->time->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                             <div class="text-center py-4 space-y-2">
                                <i class="fa-light fa-ghost text-3xl text-gray-200 dark:text-gray-800"></i>
                                <p class="text-xs text-gray-400">{{ __('admin/dashboard.recent_activity.empty') }}</p>
                             </div>
                        @endforelse
                    </div>
                </div>

                {{-- Upcoming Agenda Card --}}
                <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 dark:border-white/5 bg-gray-50/50 dark:bg-white/5 flex items-center justify-between">
                        <h4 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs flex items-center gap-2">
                            <i class="fa-light fa-calendar-lines text-primary-600"></i>
                            {{ __('admin/dashboard.agenda.title') ?? 'Klubová agenda' }}
                        </h4>
                    </div>
                    <div class="p-6 space-y-6">
                        @php $hasAgenda = $upcomingMatches->count() > 0 || $upcomingTrainings->count() > 0; @endphp

                        @foreach($upcomingMatches as $match)
                             <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/20 flex flex-col items-center justify-center text-red-600 leading-none">
                                    <span class="text-[10px] font-black uppercase tracking-tighter">{{ $match->scheduled_at->translatedFormat('M') }}</span>
                                    <span class="text-lg font-black">{{ $match->scheduled_at->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $match->team?->name }} vs {{ $match->opponent?->name ?: '???' }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ $match->scheduled_at->format('H:i') }} &bull; {{ $match->location ?: 'Domácí hřiště' }}</div>
                                </div>
                             </div>
                        @endforeach

                        @foreach($upcomingTrainings as $training)
                             <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-white/10 flex flex-col items-center justify-center text-gray-600 dark:text-gray-400 leading-none">
                                    <span class="text-[10px] font-black uppercase tracking-tighter">{{ $training->starts_at->translatedFormat('M') }}</span>
                                    <span class="text-lg font-black">{{ $training->starts_at->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white truncate">Trénink: {{ $training->teams->pluck('name')->join(', ') }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ $training->starts_at->format('H:i') }} &bull; {{ $training->location ?: 'Hala' }}</div>
                                </div>
                             </div>
                        @endforeach

                        @foreach($upcomingEvents as $event)
                             <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/20 flex flex-col items-center justify-center text-emerald-600 leading-none">
                                    <span class="text-[10px] font-black uppercase tracking-tighter">{{ $event->starts_at->translatedFormat('M') }}</span>
                                    <span class="text-lg font-black">{{ $event->starts_at->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $event->getTranslation('title', app()->getLocale()) }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ $event->starts_at->format('H:i') }} &bull; {{ $event->location ?: 'Klubovna' }}</div>
                                </div>
                             </div>
                        @endforeach

                        @if(!$hasAgenda && $upcomingEvents->count() == 0)
                             <div class="text-center py-4 space-y-2">
                                <i class="fa-light fa-calendar-xmark text-3xl text-gray-200 dark:text-gray-800"></i>
                                <p class="text-xs text-gray-400">Žádný naplánovaný program.</p>
                             </div>
                        @endif
                    </div>
                </div>

                {{-- Contact Admin Card --}}
                <div class="rounded-3xl bg-gradient-to-br from-primary-600 to-primary-700 p-8 text-white shadow-xl shadow-primary-900/20">
                     <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl">
                            <i class="fa-light fa-headset"></i>
                        </div>
                        <div>
                            <h4 class="font-black uppercase tracking-tight text-sm leading-tight">{{ __('admin/dashboard.contact_admin.title') }}</h4>
                            <p class="text-[10px] text-white/70 uppercase tracking-widest mt-0.5">Admin: Michal Nejedlý</p>
                        </div>
                     </div>

                     <form wire:submit="submitContactForm" class="space-y-4">
                        <div>
                            <select wire:model="contact_subject" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-sm text-white focus:ring-2 focus:ring-white/30 outline-none">
                                <option value="" class="text-gray-900">{{ __('admin/dashboard.contact_admin.fields.subject') }}</option>
                                <option value="technical" class="text-gray-900">{{ __('admin/dashboard.contact_admin.subjects.technical') }}</option>
                                <option value="access" class="text-gray-900">{{ __('admin/dashboard.contact_admin.subjects.access') }}</option>
                                <option value="finance" class="text-gray-900">{{ __('admin/dashboard.contact_admin.subjects.finance') }}</option>
                                <option value="other" class="text-gray-900">{{ __('admin/dashboard.contact_admin.subjects.other') }}</option>
                            </select>
                            @error('contact_subject') <span class="text-[10px] text-red-200 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <textarea wire:model="contact_message" rows="3" placeholder="{{ __('admin/dashboard.contact_admin.fields.placeholder_message') }}" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-sm text-white placeholder:text-white/50 focus:ring-2 focus:ring-white/30 outline-none resize-none"></textarea>
                            @error('contact_message') <span class="text-[10px] text-red-200 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-white text-primary-700 font-black uppercase tracking-widest text-xs hover:bg-gray-100 transition-colors shadow-lg disabled:opacity-50">
                            <i class="fa-light fa-paper-plane" wire:loading.remove wire:target="submitContactForm"></i>
                            <i class="fa-light fa-spinner-third animate-spin" wire:loading wire:target="submitContactForm"></i>
                            {{ __('admin/dashboard.contact_admin.cta') }}
                        </button>
                     </form>

                     <div class="mt-6 pt-6 border-t border-white/10 flex justify-center">
                        <a href="mailto:nejedlymi@gmail.com" class="text-[10px] font-bold uppercase tracking-widest text-white/70 hover:text-white flex items-center gap-2">
                            <i class="fa-light fa-envelope"></i>
                            {{ __('admin/dashboard.contact_admin.mailto') }}
                        </a>
                     </div>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
