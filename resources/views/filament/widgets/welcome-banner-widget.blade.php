<div class="fi-section rounded-club overflow-hidden border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900 shadow-sm">
    <div class="bg-gradient-to-br from-primary/10 via-transparent to-secondary/5 p-6 md:p-8 h-full">
        <div class="flex flex-col h-full justify-between gap-6">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight leading-none mb-3">
                    {{ __('admin/dashboard.welcome.title', ['name' => $userName]) }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed max-w-md">
                    {{ __('admin/dashboard.welcome.text', ['active_players' => number_format($activePlayers, 0, ',', ' ')]) }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @if(!empty($actions['new_match']))
                    <a href="{{ $actions['new_match'] }}" class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-white/50 p-3 dark:bg-white/5 dark:border-white/5 hover:bg-primary-50 hover:border-primary-200 dark:hover:bg-primary-900/10 dark:hover:border-primary-800 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center dark:bg-primary-900/20 group-hover:bg-primary-200 dark:group-hover:bg-primary-900/40 transition-colors">
                            <i class="fa-light fa-trophy text-primary-600"></i>
                        </div>
                        <div class="flex-1">
                            <span class="block text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('admin/dashboard.welcome.quick_actions.new_match') }}</span>
                            <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ __('admin/dashboard.welcome.quick_actions.new_match_hint') }}</span>
                        </div>
                    </a>
                @endif

                @if(!empty($actions['new_user']))
                    <a href="{{ $actions['new_user'] }}" class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-white/50 p-3 dark:bg-white/5 dark:border-white/5 hover:bg-gray-100 hover:border-gray-200 dark:hover:bg-white/10 dark:hover:border-white/10 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center dark:bg-white/5 group-hover:bg-gray-200 dark:group-hover:bg-white/10 transition-colors">
                            <i class="fa-light fa-user-plus text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <div class="flex-1">
                            <span class="block text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('admin/dashboard.welcome.quick_actions.new_user') }}</span>
                            <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ __('admin/dashboard.welcome.quick_actions.new_user_hint') }}</span>
                        </div>
                    </a>
                @endif

                @if(!empty($actions['new_training']))
                    <a href="{{ $actions['new_training'] }}" class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-white/50 p-3 dark:bg-white/5 dark:border-white/5 hover:bg-gray-100 hover:border-gray-200 dark:hover:bg-white/10 dark:hover:border-white/10 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center dark:bg-white/5 group-hover:bg-gray-200 dark:group-hover:bg-white/10 transition-colors">
                            <i class="fa-light fa-dumbbell text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <div class="flex-1">
                            <span class="block text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('admin/dashboard.welcome.quick_actions.new_training') }}</span>
                            <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ __('admin/dashboard.welcome.quick_actions.new_training_hint') }}</span>
                        </div>
                    </a>
                @endif

                @if(!empty($actions['new_event']))
                    <a href="{{ $actions['new_event'] }}" class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-white/50 p-3 dark:bg-white/5 dark:border-white/5 hover:bg-gray-100 hover:border-gray-200 dark:hover:bg-white/10 dark:hover:border-white/10 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center dark:bg-white/5 group-hover:bg-gray-200 dark:group-hover:bg-white/10 transition-colors">
                            <i class="fa-light fa-calendar-star text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <div class="flex-1">
                            <span class="block text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('admin/dashboard.welcome.quick_actions.new_event') }}</span>
                            <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ __('admin/dashboard.welcome.quick_actions.new_event_hint') }}</span>
                        </div>
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-4 border-t border-gray-100 dark:border-white/5 pt-4 mt-2">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-primary-500 border-2 border-white dark:border-gray-900 flex items-center justify-center text-[10px] font-bold text-white shadow-sm">KS</div>
                    <div class="w-8 h-8 rounded-full bg-secondary-500 border-2 border-white dark:border-gray-900 flex items-center justify-center text-[10px] font-bold text-white shadow-sm">1921</div>
                </div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    Administrace Kbelští Sokoli
                </div>
            </div>
        </div>
    </div>
</div>
