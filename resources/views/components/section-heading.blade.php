@props(['title' => null, 'subtitle' => null, 'align' => 'left', 'cta' => null])

<div class="mb-10 {{ $align === 'center' ? 'text-center' : 'text-left' }}">
    @if($subtitle)
        <div class="text-[min(3.5vw,0.875rem)] sm:text-sm uppercase tracking-widest-responsive text-primary font-bold mb-3 badge-nowrap max-w-full overflow-hidden leading-none">{{ $subtitle }}</div>
    @endif
    @if($title)
        <h2 class="section-title text-balance {{ $align === 'center' ? 'section-title-center' : '' }}">{{ $title }}</h2>
    @endif
    @if($cta)
        <div class="mt-4">
            <a href="{{ $cta['url'] ?? '#' }}" class="btn btn-outline">{{ $cta['label'] ?? 'Více' }}</a>
        </div>
    @endif
</div>
