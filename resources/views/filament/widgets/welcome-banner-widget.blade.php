<div class="rounded-club overflow-hidden border border-slate-100 bg-gradient-to-r from-primary/10 to-secondary/10 p-6 md:p-8">
    <div class="flex flex-col gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-secondary tracking-tight">
                {{ __('admin/dashboard.welcome.title', ['name' => $userName]) }}
            </h2>
            <p class="text-slate-600 mt-2">
                {{ __('admin/dashboard.welcome.text', ['active_players' => number_format($activePlayers, 0, ',', ' ')]) }}
            </p>
        </div>

        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3">
            @if(!empty($actions['new_match']))
                <a href="{{ $actions['new_match'] }}" class="group block w-full rounded-club border border-primary/30 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-primary/10 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.new_match') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-trophy text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.new_match') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.new_match_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['new_user']))
                <a href="{{ $actions['new_user'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.new_user') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-user-plus text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.new_user') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.new_user_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['new_post']))
                <a href="{{ $actions['new_post'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.new_post') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-pen-nib text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.new_post') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.new_post_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['new_training']))
                <a href="{{ $actions['new_training'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.new_training') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-dumbbell text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.new_training') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.new_training_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['new_event']))
                <a href="{{ $actions['new_event'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.new_event') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-calendar-star text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.new_event') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.new_event_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['media_upload']))
                <a href="{{ $actions['media_upload'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.media_upload') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-photo-film text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.media_upload') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.media_upload_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['audit_log']))
                <a href="{{ $actions['audit_log'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.audit_log') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-clipboard-list text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.audit_log') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.audit_log_hint') }}</p>
                </a>
            @endif

            @if(!empty($actions['finance']))
                <a href="{{ $actions['finance'] }}" class="group block w-full rounded-club border border-slate-200 bg-white/60 backdrop-blur-sm px-4 py-3 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/50" aria-label="{{ __('admin/dashboard.welcome.quick_actions.finance') }}">
                    <div class="flex items-center gap-2">
                        <i class="fa-light fa-piggy-bank text-primary"></i>
                        <span class="font-semibold text-secondary">{{ __('admin/dashboard.welcome.quick_actions.finance') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ __('admin/dashboard.welcome.quick_actions.finance_hint') }}</p>
                </a>
            @endif
        </div>
    </div>
</div>
