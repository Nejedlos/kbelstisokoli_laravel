@props(['title' => null, 'subtitle' => null, 'align' => 'left', 'cta' => null])

<div class="container mb-10 {{ $align === 'center' ? 'text-center' : 'text-left' }}">
    @if($subtitle)
        <div class="text-sm uppercase tracking-widest text-primary font-bold mb-2">{{ $subtitle }}</div>
    @endif
    @if($title)
        <h2 class="section-title">{{ $title }}</h2>
    @endif
    @if($cta)
        <div class="mt-4">
            <a href="{{ $cta['url'] ?? '#' }}" class="btn btn-outline">{{ $cta['label'] ?? 'Více' }}</a>
        </div>
    @endif
</div>
