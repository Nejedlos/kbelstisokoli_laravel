@props(['title', 'value', 'icon', 'color' => 'primary', 'route' => '#'])

<a href="{{ $route }}" class="relative overflow-hidden group">
    <div class="absolute inset-0 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-lg shadow-slate-200/20 group-hover:shadow-xl group-hover:shadow-primary/5 group-hover:border-primary/20 transition-all duration-500"></div>
    <div class="absolute top-0 right-0 w-32 h-32 bg-{{ $color === 'primary' ? 'rose' : ($color === 'secondary' ? 'slate' : 'blue') }}-500/5 rounded-full blur-2xl -mr-16 -mt-16 group-hover:bg-primary/10 transition-colors"></div>

    <div class="relative p-6 sm:p-7 flex items-center gap-4 sm:gap-6">
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-{{ $color === 'primary' ? 'rose' : ($color === 'secondary' ? 'slate' : 'blue') }}-50 text-{{ $color === 'primary' ? 'rose' : ($color === 'secondary' ? 'slate' : 'blue') }}-600 flex items-center justify-center flex-shrink-0 transition-all group-hover:scale-110 group-hover:rotate-3">
            @if(str_contains($icon, 'heroicon'))
                <x-dynamic-component :component="$icon" class="w-7 h-7 sm:w-8 sm:h-8" />
            @else
                <i class="fa-light fa-{{ $icon }} text-xl sm:text-2xl"></i>
            @endif
        </div>
        <div class="flex-1">
            <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1 sm:mb-1.5 group-hover:text-primary transition-colors">{{ $title }}</p>
            <p class="text-2xl sm:text-3xl font-black tracking-tight text-secondary leading-none">{{ $value }}</p>
        </div>
        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-primary group-hover:text-white transition-all shrink-0">
            <i class="fa-light fa-chevron-right text-[10px]"></i>
        </div>
    </div>
</a>
