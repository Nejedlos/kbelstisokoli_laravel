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
            <nav class="mb-4 flex items-center justify-{{ $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'end' : 'start') }} space-x-2 text-sm opacity-80 uppercase tracking-widest font-bold">
                <a href="{{ route('public.home') }}" class="hover:text-primary transition-colors">Úvod</a>
                @foreach($breadcrumbs as $label => $link)
                    <span>/</span>
                    @if($link)
                        <a href="{{ $link }}" class="hover:text-primary transition-colors">{{ $label }}</a>
                    @else
                        <span class="text-white">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="text-4xl md:text-6xl font-black mb-4 uppercase tracking-tighter leading-none">{{ $title }}</h1>

        @if($subtitle)
            <p class="text-lg md:text-xl text-slate-300 max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</div>
