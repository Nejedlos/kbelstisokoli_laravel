<div class="relative group flex items-center w-full sm:w-auto">
    <div class="relative w-full">
        <select wire:model.live="activeTeamId"
                class="w-full bg-white sm:bg-slate-100/80 border border-slate-200/60 rounded-xl pl-10 pr-10 py-3 sm:py-2 text-[12px] sm:text-[10px] font-black uppercase tracking-widest text-secondary focus:bg-white focus:border-primary/40 focus:ring-4 focus:ring-primary/5 outline-none transition-all appearance-none cursor-pointer hover:bg-slate-50 sm:hover:bg-slate-200/50 shadow-sm sm:shadow-none min-h-[48px] sm:min-h-[40px]">
            <option value="">{{ __('member.profile.section_settings.view_all') }}</option>
            @foreach($teams as $team)
                <option value="{{ $team->id }}">{{ $team->name }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 left-3.5 sm:left-3 flex items-center pointer-events-none text-primary/80 group-focus-within:text-primary transition-colors">
            <i class="fa-light fa-users-viewfinder text-base sm:text-sm"></i>
        </div>
        <div class="absolute inset-y-0 right-3.5 sm:right-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary/50 transition-colors">
            <i class="fa-light fa-chevron-down text-[12px] sm:text-[10px]"></i>
        </div>
    </div>
</div>
