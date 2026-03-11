@props(['type' => 'tip', 'title' => null])

@php
    $config = match($type) {
        'warning' => [
            'icon' => 'fa-light fa-triangle-exclamation',
            'bg' => 'bg-orange-50',
            'text' => 'text-orange-900',
            'border' => 'border-orange-100',
            'iconColor' => 'text-orange-500',
            'title' => $title ?? 'Varování',
        ],
        'error', 'danger' => [
            'icon' => 'fa-light fa-circle-xmark',
            'bg' => 'bg-red-50',
            'text' => 'text-red-900',
            'border' => 'border-red-100',
            'iconColor' => 'text-red-500',
            'title' => $title ?? 'Pozor',
        ],
        'success' => [
            'icon' => 'fa-light fa-circle-check',
            'bg' => 'bg-green-50',
            'text' => 'text-green-900',
            'border' => 'border-green-100',
            'iconColor' => 'text-green-500',
            'title' => $title ?? 'Hotovo',
        ],
        default => [
            'icon' => 'fa-light fa-lightbulb',
            'bg' => 'bg-primary-50',
            'text' => 'text-primary-900',
            'border' => 'border-primary-100',
            'iconColor' => 'text-primary-500',
            'title' => $title ?? 'Tip',
        ],
    };
@endphp

<div @class([
    'p-6 rounded-3xl border flex gap-6 my-8',
    $config['bg'],
    $config['border'],
    $config['text']
])>
    <div @class([
        'w-12 h-12 rounded-2xl bg-white/50 flex items-center justify-center shrink-0 border border-white/20 shadow-sm',
        $config['iconColor']
    ])>
        <i class="{{ $config['icon'] }} text-xl"></i>
    </div>
    <div class="flex-1">
        <h5 class="text-sm font-black uppercase tracking-widest mb-2 opacity-80">{{ $config['title'] }}</h5>
        <div class="prose-sm prose-slate max-w-none opacity-90 leading-relaxed font-medium">
            {{ $slot }}
        </div>
    </div>
</div>
