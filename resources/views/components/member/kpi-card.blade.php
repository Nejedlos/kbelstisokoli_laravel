@props(['title', 'value', 'icon', 'color' => 'primary', 'route' => '#'])

<a href="{{ $route }}" class="card card-hover p-6 flex items-center gap-6">
    <div class="w-14 h-14 rounded-club bg-{{ $color }}-50 text-{{ $color }}-600 flex items-center justify-center flex-shrink-0">
        <x-dynamic-component :component="$icon" class="w-8 h-8" />
    </div>
    <div>
        <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1">{{ $title }}</p>
        <p class="text-3xl font-black tracking-tight text-secondary leading-none">{{ $value }}</p>
    </div>
</a>
