@props(['breadcrumbs', 'variant' => 'dark'])

@php
    $isHome = request()->routeIs('public.home');
    if ($isHome || empty($breadcrumbs)) {
        return;
    }

    // Normalizace vstupu (pole vs kolekce)
    $items = collect();
    if (is_array($breadcrumbs)) {
        foreach($breadcrumbs as $label => $url) {
            $items->push((object)[
                'title' => is_string($label) ? $label : $url,
                'url' => is_string($label) ? $url : null
            ]);
        }
    } else {
        $items = $breadcrumbs;
    }
@endphp

<div @class([
    'breadcrumbs-container py-2',
    'text-slate-400' => $variant === 'dark',
    'text-white/60' => $variant === 'light',
])>
    <nav class="flex items-center gap-2 text-[10px] uppercase tracking-[0.2em] font-bold overflow-x-auto whitespace-nowrap no-scrollbar" aria-label="Breadcrumb">
        @foreach($items as $breadcrumb)
            <div class="flex items-center gap-2">
                @if($breadcrumb->url && !$loop->last)
                    <a href="{{ $breadcrumb->url }}" @class([
                        'transition-colors',
                        'hover:text-primary' => $variant === 'dark',
                        'hover:text-white' => $variant === 'light',
                    ])>
                        {{ $breadcrumb->title }}
                    </a>
                @else
                    <span @class([
                        'truncate max-w-[200px]',
                        'text-slate-600' => $variant === 'dark' && $loop->last,
                        'text-white' => $variant === 'light' && $loop->last,
                    ])>
                        {{ $breadcrumb->title }}
                    </span>
                @endif

                @if(!$loop->last)
                    <span @class([
                        'font-light opacity-40',
                        'text-slate-300' => $variant === 'dark',
                        'text-white' => $variant === 'light',
                    ])>/</span>
                @endif
            </div>
        @endforeach
    </nav>
</div>

{{-- JSON-LD pro SEO (ponecháváme původní logiku pro kolekci objektů) --}}
@if($items instanceof \Illuminate\Support\Collection)
    <script type="application/ld+json">
    {
      "{{ '@' }}context": "https://schema.org",
      "{{ '@' }}type": "BreadcrumbList",
      "itemListElement": [
        @foreach($items as $index => $breadcrumb)
        {
          "{{ '@' }}type": "ListItem",
          "position": {{ $index + 1 }},
          "name": "{{ $breadcrumb->title }}"@if($breadcrumb->url),
          "item": "{{ str_starts_with($breadcrumb->url, 'http') ? $breadcrumb->url : url($breadcrumb->url) }}"@endif
        }{{ $loop->last ? '' : ',' }}
        @endforeach
      ]
    }
    </script>
@endif
