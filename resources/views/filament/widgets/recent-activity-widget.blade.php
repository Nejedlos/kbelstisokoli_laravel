<div class="fi-section rounded-club overflow-hidden border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900 shadow-sm">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <i class="fa-light fa-clock-rotate-left text-primary-600"></i>
                {{ __('admin/dashboard.recent_activity.title') }}
            </h3>
        </div>

        @if($items->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-3 dark:bg-white/5">
                    <i class="fa-light fa-inbox text-gray-300"></i>
                </div>
                <p class="text-sm text-gray-400 italic">{{ __('admin/dashboard.recent_activity.empty') }}</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($items as $row)
                    <div class="py-4 flex items-start justify-between gap-4 group">
                        <div class="flex gap-4">
                            <div class="mt-1 w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center dark:bg-white/5 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/10 transition-colors">
                                <i class="fa-light fa-circle-dot text-[10px] text-gray-400 group-hover:text-primary-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $row->action ?? ($row->event_key ?? 'event') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $row->subject_label ?? ($row->subject_type ? str_replace('App\\Models\\', '', $row->subject_type) : '-') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[10px] font-black uppercase tracking-widest text-primary-600 dark:text-primary-400">
                                {{ optional($row->occurred_at ?: $row->created_at)->diffForHumans() }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $row->actor?->name ?? __('admin/dashboard.recent_activity.actor_system') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
