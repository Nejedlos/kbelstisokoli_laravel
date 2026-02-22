@props(['breadcrumbs'])

@if($breadcrumbs && $breadcrumbs->isNotEmpty())
    <nav class="flex items-center gap-2 text-sm text-slate-500 overflow-x-auto whitespace-nowrap py-4 no-scrollbar" aria-label="Breadcrumb">
        @foreach($breadcrumbs as $breadcrumb)
            <div class="flex items-center gap-2">
                @if($breadcrumb->url && !$loop->last)
                    <a href="{{ $breadcrumb->url }}" class="hover:text-primary transition-colors">
                        {{ $breadcrumb->title }}
                    </a>
                @else
                    <span class="font-medium text-slate-900 line-clamp-1">
                        {{ $breadcrumb->title }}
                    </span>
                @endif

                @if(!$loop->last)
                    <i class="fa-light fa-chevron-right text-[10px] text-slate-300"></i>
                @endif
            </div>
        @endforeach
    </nav>

    {{-- JSON-LD pro SEO --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        @foreach($breadcrumbs as $index => $breadcrumb)
        {
          "@type": "ListItem",
          "position": {{ $index + 1 }},
          "name": "{{ $breadcrumb->title }}",
          @if($breadcrumb->url)
          "item": "{{ $breadcrumb->url }}"
          @endif
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
      ]
    }
    </script>
@endif
