@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center space-x-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl border border-slate-100 bg-white text-slate-300 cursor-not-allowed">
                <i class="fa-light fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-12 h-12 rounded-xl border border-slate-200 bg-white text-secondary hover:border-primary hover:text-primary transition-all shadow-sm hover:shadow-md" aria-label="{{ __('pagination.previous') }}">
                <i class="fa-light fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="inline-flex items-center justify-center w-12 h-12 text-slate-400">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary text-white font-black shadow-lg shadow-primary/20 z-10">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="inline-flex items-center justify-center w-12 h-12 rounded-xl border border-slate-200 bg-white text-secondary font-bold hover:border-primary hover:text-primary transition-all shadow-sm hover:shadow-md" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-12 h-12 rounded-xl border border-slate-200 bg-white text-secondary hover:border-primary hover:text-primary transition-all shadow-sm hover:shadow-md" aria-label="{{ __('pagination.next') }}">
                <i class="fa-light fa-chevron-right"></i>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl border border-slate-100 bg-white text-slate-300 cursor-not-allowed">
                <i class="fa-light fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
