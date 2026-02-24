<div class="card p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-black uppercase tracking-widest text-secondary">{{ __('admin/dashboard.recent_activity.title') }}</h3>
        <i class="fa-light fa-clock-rotate-left text-primary"></i>
    </div>

    @if($items->isEmpty())
        <p class="text-sm text-slate-400 italic">{{ __('admin/dashboard.recent_activity.empty') }}</p>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($items as $row)
                <div class="py-3 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-bold text-secondary">
                            {{ $row->action ?? ($row->event_key ?? 'event') }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $row->subject_label ?? ($row->subject_type ? str_replace('App\\Models\\', '', $row->subject_type) : '-') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                            {{ optional($row->occurred_at ?: $row->created_at)->diffForHumans() }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $row->actor?->name ?? __('admin/dashboard.recent_activity.actor_system') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
