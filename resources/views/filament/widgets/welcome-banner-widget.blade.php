<div class="rounded-club overflow-hidden border border-slate-100 bg-gradient-to-r from-primary/10 to-secondary/10 p-6 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-secondary tracking-tight">
                {{ __('admin/dashboard.welcome.title', ['name' => $userName]) }}
            </h2>
            <p class="text-slate-600 mt-2">
                {{ __('admin/dashboard.welcome.text', ['active_players' => number_format($activePlayers, 0, ',', ' ')]) }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ $actions['match'] }}" class="btn btn-primary">
                <i class="fa-light fa-trophy mr-2"></i> {{ __('admin/dashboard.welcome.quick_actions.new_match') }}
            </a>
            <a href="{{ $actions['user'] }}" class="btn btn-outline">
                <i class="fa-light fa-user-plus mr-2"></i> {{ __('admin/dashboard.welcome.quick_actions.new_user') }}
            </a>
            <a href="{{ $actions['post'] }}" class="btn btn-outline">
                <i class="fa-light fa-pen-nib mr-2"></i> {{ __('admin/dashboard.welcome.quick_actions.new_post') }}
            </a>
        </div>
    </div>
</div>
