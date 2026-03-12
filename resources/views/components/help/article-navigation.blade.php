@props(['prev' => null, 'next' => null])

@if($prev || $next)
    <div class="mt-20 pt-10 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            @if($prev)
                <a href="{{ \App\Support\HelpUrlHelper::getUrl(['file' => $prev->slug]) }}" class="group block p-6 bg-white rounded-3xl border border-slate-100 hover:border-primary-100 hover:shadow-xl transition-all h-full relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-slate-50 rounded-full -mr-12 -mt-12 opacity-50 group-hover:scale-110 transition-transform"></div>

                    <div class="relative z-10">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                            <i class="fa-light fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                            {{ __('admin.navigation.pages.help_prev_article') }}
                        </span>
                        <h4 class="text-lg font-black text-slate-900 leading-tight group-hover:text-primary-600 transition-colors">
                            {{ $prev->title_str }}
                        </h4>
                        @if($prev->metadata && isset($prev->metadata['short_intro']))
                            <p class="mt-2 text-sm text-slate-500 line-clamp-1 font-medium">
                                {{ $prev->metadata['short_intro'] }}
                            </p>
                        @endif
                    </div>
                </a>
            @endif
        </div>

        <div class="text-right">
            @if($next)
                <a href="{{ \App\Support\HelpUrlHelper::getUrl(['file' => $next->slug]) }}" class="group block p-6 bg-white rounded-3xl border border-slate-100 hover:border-primary-100 hover:shadow-xl transition-all h-full relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-24 h-24 bg-slate-50 rounded-full -ml-12 -mt-12 opacity-50 group-hover:scale-110 transition-transform"></div>

                    <div class="relative z-10">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center justify-end gap-2 text-right">
                            {{ __('admin.navigation.pages.help_next_article') }}
                            <i class="fa-light fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                        </span>
                        <h4 class="text-lg font-black text-slate-900 leading-tight group-hover:text-primary-600 transition-colors">
                            {{ $next->title_str }}
                        </h4>
                        @if($next->metadata && isset($next->metadata['short_intro']))
                            <p class="mt-2 text-sm text-slate-500 line-clamp-1 font-medium">
                                {{ $next->metadata['short_intro'] }}
                            </p>
                        @endif
                    </div>
                </a>
            @endif
        </div>
    </div>
@endif
