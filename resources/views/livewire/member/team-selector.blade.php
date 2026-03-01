<div class="relative group flex items-center gap-3">
    @if($activeTeam)
        <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 bg-primary/10 border border-primary/20 rounded-lg text-[10px] font-black uppercase tracking-wider text-primary animate-fade-in">
            <i class="fa-light fa-eye"></i>
            <span>{{ $activeTeam->name }}</span>
        </div>
    @else
        <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 bg-secondary/10 border border-secondary/20 rounded-lg text-[10px] font-black uppercase tracking-wider text-secondary animate-fade-in">
            <i class="fa-light fa-layer-group"></i>
            <span>{{ __('member.profile.section_settings.view_all') }}</span>
        </div>
    @endif

    <div class="relative">
        <select wire:model.live="activeTeamId"
                class="bg-slate-100/80 border border-slate-200/60 rounded-xl pl-9 pr-8 py-2 text-[11px] font-black uppercase tracking-widest text-secondary focus:bg-white focus:border-primary/30 focus:ring-4 focus:ring-primary/5 outline-none transition-all appearance-none cursor-pointer hover:bg-slate-200/50 min-h-[40px] w-full sm:w-auto">
            <option value="">{{ __('member.profile.section_settings.view_all') }}</option>
            @foreach($teams as $team)
                <option value="{{ $team->id }}">{{ $team->name }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-primary">
            <i class="fa-light fa-users-viewfinder text-sm"></i>
        </div>
        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
            <i class="fa-light fa-chevron-down text-[10px]"></i>
        </div>
    </div>
</div>
