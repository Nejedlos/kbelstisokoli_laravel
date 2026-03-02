@props(['type' => 'success', 'message' => null])

@php
    $colors = match ($type) {
        'success' => [
            'bg' => 'bg-emerald-500/10',
            'border' => 'border-emerald-500/20',
            'icon' => 'fa-circle-check text-emerald-500',
            'text' => 'text-emerald-400',
        ],
        'error' => [
            'bg' => 'bg-red-500/10',
            'border' => 'border-red-500/20',
            'icon' => 'fa-circle-exclamation text-red-500',
            'text' => 'text-red-400',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10',
            'border' => 'border-amber-500/20',
            'icon' => 'fa-triangle-exclamation text-amber-500',
            'text' => 'text-amber-400',
        ],
        default => [
            'bg' => 'bg-blue-500/10',
            'border' => 'border-blue-500/20',
            'icon' => 'fa-circle-info text-blue-500',
            'text' => 'text-blue-400',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => "animate-fade-in {$colors['bg']} border {$colors['border']} flex gap-4 items-center mb-8 p-4 rounded-2xl {$colors['text']}"]) }}>
    <i class="{{ $colors['icon'] }} fa-light text-lg shrink-0"></i>
    <span class="font-bold text-sm leading-tight">
        {{ $message ?? $slot }}
    </span>
</div>
