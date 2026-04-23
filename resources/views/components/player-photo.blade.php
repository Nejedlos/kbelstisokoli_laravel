@props([
    'user',
    'photoUrl' => null,
    'class' => 'w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105',
    'size' => 'lg'
])

@php
    $photoUrl = $photoUrl ?? $user->getPlayerPhotoUrl();
    $iconSize = match($size) {
        'sm' => 'fa-2x',
        'md' => 'fa-3x',
        'lg' => 'fa-5x',
        default => 'fa-3x'
    };
    $padding = match($size) {
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
        default => 'p-6'
    };
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full h-full bg-slate-100 flex items-start justify-center overflow-hidden']) }}>
    @if($photoUrl)
        <img src="{{ $photoUrl }}"
             alt="{{ $user->display_name }}"
             class="{{ $class }}"
             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
    @endif

    <div class="player-photo-placeholder {{ $photoUrl ? 'hidden' : '' }} w-full h-full flex flex-col items-center justify-center text-slate-300 {{ $padding }} bg-slate-100">
        <i class="fa-light fa-user {{ $iconSize }} mb-4 opacity-20"></i>
        <span class="text-[10px] uppercase font-bold tracking-widest opacity-40 text-center leading-tight">
            {{ __('teams.detail.photo_pending') }}
        </span>
    </div>
</div>
