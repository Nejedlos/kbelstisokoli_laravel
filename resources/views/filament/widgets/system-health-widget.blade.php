<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-black uppercase tracking-widest text-secondary">{{ __('admin/dashboard.system.title') }}</h3>
            <i class="fa-light fa-heart-pulse text-primary"></i>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full {{ $cronOk ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
            <div>
                <div class="text-sm font-bold text-slate-700">{{ __('admin/dashboard.system.cron.label') }}</div>
                <div class="text-xs text-slate-500">
                    {{ $cronOk ? __('admin/dashboard.system.cron.ok') : __('admin/dashboard.system.cron.problem') }}
                    <span class="ml-2 text-slate-400">{{ $lastRunHuman }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-black uppercase tracking-widest text-secondary">{{ __('admin/dashboard.system.title') }}</h3>
            <i class="fa-light fa-brain text-primary"></i>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full {{ $aiReady ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
            <div>
                <div class="text-sm font-bold text-slate-700">{{ __('admin/dashboard.system.ai.label') }}</div>
                <div class="text-xs text-slate-500">
                    {{ $aiReady ? __('admin/dashboard.system.ai.ready') : __('admin/dashboard.system.ai.needs_index') }}
                </div>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-black uppercase tracking-widest text-secondary">{{ __('admin/dashboard.club_health.title') }}</h3>
            <i class="fa-light fa-users text-primary"></i>
        </div>
        <div class="text-xs text-slate-500">
            <!-- Placeholder for future checks (queues, storage, etc.) -->
            <span class="inline-flex items-center gap-2"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span>OK</span>
        </div>
    </div>
</div>
