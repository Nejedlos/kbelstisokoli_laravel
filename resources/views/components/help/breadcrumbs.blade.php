@props(['breadcrumbs' => []])

@if(!empty($breadcrumbs))
    <nav class="flex items-center mb-8 text-sm sm:text-base" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 list-none p-0 m-0">
            <li class="inline-flex items-center">
                <a href="{{ \App\Filament\Pages\Help::getUrl() }}" class="inline-flex items-center font-medium text-slate-600 hover:text-primary-600 focus-visible:text-primary-600 focus-visible:outline-none transition-colors">
                    <i class="fa-light fa-house mr-2"></i>
                    {{ __('admin.navigation.pages.help') }}
                </a>
            </li>
            @foreach($breadcrumbs as $breadcrumb)
                <li>
                    <div class="flex items-center">
                        <i class="fa-light fa-chevron-right text-slate-400 mx-2 text-[10px] sm:text-[11px]"></i>
                        @if($loop->last)
                            <span class="font-bold text-slate-900 truncate max-w-[200px] sm:max-w-md">{{ $breadcrumb['label'] }}</span>
                        @else
                            <a href="{{ $breadcrumb['url'] }}" class="font-medium text-slate-600 hover:text-primary-600 focus-visible:text-primary-600 focus-visible:outline-none transition-colors">{{ $breadcrumb['label'] }}</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
