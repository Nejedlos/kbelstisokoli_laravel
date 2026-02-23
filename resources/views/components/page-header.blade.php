@props([
    'title',
    'subtitle' => null,
    'image' => null,
    'alignment' => 'center',
    'breadcrumbs' => []
])

<div class="page-header bg-secondary text-white py-16 md:py-24 relative overflow-hidden">
    @if($image)
        <div class="absolute inset-0 z-0 opacity-30">
            <img src="{{ $image }}" alt="" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="container relative z-10 text-{{ $alignment }}">
        @if(!empty($breadcrumbs))
            <div class="mb-4 flex justify-{{ $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'end' : 'start') }}">
                <x-breadcrumbs :breadcrumbs="array_merge(['Úvod' => route('public.home')], $breadcrumbs)" variant="light" />
            </div>
        @endif

        <h1 class="text-4xl md:text-6xl font-black mb-4 uppercase tracking-tighter leading-none">{{ $title }}</h1>

        @if($subtitle)
            <p class="text-lg md:text-xl text-slate-300 max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</div>
