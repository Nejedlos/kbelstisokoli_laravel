@props(['breadcrumbs' => []])

@if(!empty($breadcrumbs))
    <nav class="flex items-center mb-8 text-xs sm:text-sm font-bold uppercase tracking-wider" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 list-none p-0 m-0">
            <li class="inline-flex items-center">
                <a href="{{ \App\Support\HelpUrlHelper::getUrl() }}" class="inline-flex items-center text-slate-400 hover:text-primary transition-colors">
                    <i class="fa-light fa-house mr-2"></i>
                    {{ __('admin.navigation.pages.help') }}
                </a>
            </li>
            @foreach($breadcrumbs as $breadcrumb)
                <li>
                    <div class="flex items-center">
                        <i class="fa-light fa-chevron-right text-slate-300 mx-2 text-[8px] sm:text-[9px]"></i>
                        @if($loop->last)
                            <span class="text-secondary truncate max-w-[150px] sm:max-w-xs">{{ $breadcrumb['label'] }}</span>
                        @else
                            <a href="{{ $breadcrumb['url'] }}" class="text-slate-400 hover:text-primary transition-colors">{{ $breadcrumb['label'] }}</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
