<x-filament-panels::page>
    <div class="space-y-12">
        {{-- Search & Hero Header --}}
        <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900 p-8 sm:p-16 shadow-2xl">
            {{-- Decorative Background --}}
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-primary-500/20 rounded-full blur-[100px] animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-secondary-500/10 rounded-full blur-[100px]"></div>

            <div class="relative z-10 max-w-3xl mx-auto text-center space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-white/80 text-sm font-medium backdrop-blur-md mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                    </span>
                    Centrum nápovědy Kbelští sokoli
                </div>

                <h2 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-tight">
                    {{ __('admin.navigation.pages.help_subtitle') }}
                </h2>

                <p class="text-slate-400 text-lg sm:text-xl max-w-2xl mx-auto font-medium">
                    {{ __('admin.navigation.pages.help_description') }}
                </p>

                <div class="mt-10 relative max-w-2xl mx-auto group">
                    <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                        <i class="fa-light fa-magnifying-glass text-slate-400 text-xl group-focus-within:text-primary-400 transition-colors"></i>
                    </div>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="{{ __('admin.navigation.pages.help_search_placeholder') }}"
                        class="block w-full pl-14 pr-6 py-6 bg-white/10 border border-white/20 rounded-[2rem] shadow-2xl backdrop-blur-xl focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500/50 text-white placeholder-slate-500 transition-all text-lg"
                    >
                </div>
            </div>
        </div>

        @if($searchQuery)
            {{-- Search Results --}}
            <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                        <i class="fa-light fa-list-check text-primary-600 bg-primary-50 p-2 rounded-lg"></i>
                        {{ __('admin.navigation.pages.help_search_results_for') }} <span class="text-primary-600">"{{ $searchQuery }}"</span>
                    </h3>
                    <button wire:click="$set('searchQuery', '')" class="text-sm font-bold text-slate-500 hover:text-primary-600 transition-colors">
                        {{ __('admin.navigation.pages.help_search_cancel') }}
                    </button>
                </div>

                <div class="grid gap-4">
                    @forelse($this->getSearchResults() as $result)
                        <button
                            wire:click="$set('currentFile', '{{ $result['path'] }}'); $set('searchQuery', '')"
                            class="text-left p-8 bg-white rounded-3xl border border-slate-100 hover:border-primary-200 hover:shadow-2xl transition-all group relative overflow-hidden"
                        >
                            <div class="flex items-start gap-6 relative z-10">
                                <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center shrink-0 group-hover:bg-primary-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                    <i class="fa-light fa-file-lines text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-slate-900 group-hover:text-primary-600 transition-colors mb-2">{{ $result['title'] }}</h4>
                                    <p class="text-slate-500 leading-relaxed italic">{!! $result['excerpt'] !!}</p>
                                </div>
                            </div>
                            <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-2 transition-all">
                                <i class="fa-light fa-chevron-right text-primary-600 text-2xl"></i>
                            </div>
                        </button>
                    @empty
                        <div class="p-20 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fa-light fa-face-frown-slight text-4xl text-slate-300"></i>
                            </div>
                            <h4 class="text-2xl font-bold text-slate-900 mb-2">{{ __('admin.navigation.pages.help_no_results') }}</h4>
                            <p class="text-slate-500 max-w-sm mx-auto text-lg">{{ __('admin.navigation.pages.help_no_results_desc') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @elseif(!$currentFile && !$currentCategory)
            {{-- Categories Landing --}}
            <div class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ __('admin.navigation.pages.help_browse_categories') }}</h3>
                        <p class="text-slate-500 font-medium">{{ __('admin.navigation.pages.help_browse_categories_desc') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($this->getTree() as $category)
                        <button
                            wire:click="$set('currentCategory', '{{ $category['path'] }}')"
                            class="group relative text-left bg-white rounded-[2.5rem] border border-slate-100 p-10 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] hover:-translate-y-2 transition-all duration-500 overflow-hidden"
                        >
                            {{-- Card Accent Decor --}}
                            <div @class([
                                'absolute top-0 right-0 w-32 h-32 -mr-8 -mt-8 rounded-full blur-3xl opacity-20 transition-opacity group-hover:opacity-40',
                                'bg-orange-500' => $category['color'] === 'orange',
                                'bg-blue-500' => $category['color'] === 'blue',
                                'bg-green-500' => $category['color'] === 'green',
                                'bg-purple-500' => $category['color'] === 'purple',
                                'bg-red-500' => $category['color'] === 'red',
                                'bg-slate-500' => $category['color'] === 'slate',
                            ])></div>

                            <div @class([
                                'w-20 h-20 rounded-[1.5rem] flex items-center justify-center text-3xl mb-8 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-sm',
                                'bg-orange-50 text-orange-600' => $category['color'] === 'orange',
                                'bg-blue-50 text-blue-600' => $category['color'] === 'blue',
                                'bg-green-50 text-green-600' => $category['color'] === 'green',
                                'bg-purple-50 text-purple-600' => $category['color'] === 'purple',
                                'bg-red-50 text-red-600' => $category['color'] === 'red',
                                'bg-slate-50 text-slate-600' => $category['color'] === 'slate',
                            ])>
                                <i class="fa-light {{ $category['icon'] }}"></i>
                            </div>

                            <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight group-hover:text-primary-600 transition-colors">{{ $category['name'] }}</h3>

                            <p class="text-slate-500 leading-relaxed mb-8 font-medium">
                                {{ $category['description'] ?? 'Podrobný návod a postupy pro tuto sekci nápovědy.' }}
                            </p>

                            <div class="flex items-center justify-between mt-auto">
                                <span class="text-xs font-black uppercase tracking-widest text-slate-400 group-hover:text-primary-600 transition-colors">
                                    {{ trans_choice('admin.navigation.pages.help_articles_count', count($category['children'])) }}
                                </span>
                                <div @class([
                                    'w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500',
                                    'bg-slate-50 text-slate-300 group-hover:bg-primary-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-primary-600/30'
                                ])>
                                    <i class="fa-light fa-arrow-right"></i>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @elseif($currentCategory && !$currentFile)
            {{-- Category Content List --}}
            <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                @php $catInfo = $this->getCategoryInfo() @endphp
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-6">
                        <div @class([
                            'w-16 h-16 rounded-2xl flex items-center justify-center text-2xl shadow-sm',
                            'bg-orange-50 text-orange-600' => $catInfo['color'] === 'orange',
                            'bg-blue-50 text-blue-600' => $catInfo['color'] === 'blue',
                            'bg-green-50 text-green-600' => $catInfo['color'] === 'green',
                            'bg-purple-50 text-purple-600' => $catInfo['color'] === 'purple',
                            'bg-red-50 text-red-600' => $catInfo['color'] === 'red',
                            'bg-slate-50 text-slate-600' => $catInfo['color'] === 'slate',
                        ])>
                            <i class="fa-light {{ $catInfo['icon'] }}"></i>
                        </div>
                        <div>
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ $catInfo['name'] }}</h3>
                            <p class="text-slate-500 font-medium">{{ trans_choice('admin.navigation.pages.help_articles_in_category', count($catInfo['children'])) }}</p>
                        </div>
                    </div>
                    <button
                        wire:click="$set('currentCategory', null)"
                        class="px-6 py-3 rounded-xl bg-slate-50 text-slate-600 font-bold hover:bg-slate-100 transition-all flex items-center gap-2"
                    >
                        <i class="fa-light fa-arrow-left"></i>
                        {{ __('admin.navigation.pages.help_back_to_overview') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($catInfo['children'] as $file)
                        <button
                            wire:click="$set('currentFile', '{{ $file['path'] }}')"
                            class="group p-8 bg-white rounded-3xl border border-slate-100 hover:border-primary-200 hover:shadow-xl transition-all text-left flex items-center justify-between"
                        >
                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-primary-50 transition-colors">
                                    <i class="fa-light fa-file-lines text-slate-400 group-hover:text-primary-600"></i>
                                </div>
                                <span class="text-xl font-bold text-slate-700 group-hover:text-slate-900 transition-colors">{{ $file['name'] }}</span>
                            </div>
                            <i class="fa-light fa-chevron-right text-slate-300 group-hover:text-primary-600 group-hover:translate-x-1 transition-all"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Article Detail --}}
            <div class="flex flex-col lg:flex-row gap-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
                {{-- Sidebar --}}
                <aside class="w-full lg:w-80 shrink-0">
                    <div class="sticky top-24 space-y-8">
                        <button
                            wire:click="$set('currentFile', null)"
                            class="group flex items-center gap-3 text-sm font-black text-slate-400 hover:text-primary-600 transition-all uppercase tracking-widest px-4"
                        >
                            <i class="fa-light fa-arrow-left-long group-hover:-translate-x-2 transition-transform"></i>
                            Zpět na sekci
                        </button>

                        <nav class="bg-white rounded-[2rem] border border-slate-100 p-4 shadow-sm space-y-8 overflow-hidden">
                            @foreach($this->getTree() as $category)
                                <div>
                                    <button
                                        wire:click="$set('currentCategory', '{{ $category['path'] }}'); $set('currentFile', null)"
                                        @class([
                                            'w-full text-left px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-colors flex items-center justify-between group/cat',
                                            'text-primary-600 bg-primary-50' => $currentCategory === $category['path'],
                                            'text-slate-400 hover:text-slate-600' => $currentCategory !== $category['path'],
                                        ])
                                    >
                                        {{ $category['name'] }}
                                        <i @class([
                                            'fa-light fa-chevron-right text-[8px] transition-transform',
                                            'rotate-90' => $currentCategory === $category['path'],
                                        ])></i>
                                    </button>

                                    @if($currentCategory === $category['path'])
                                        <div class="mt-2 space-y-1 px-2 animate-in fade-in slide-in-from-top-2 duration-300">
                                            @foreach($category['children'] as $file)
                                                <button
                                                    wire:click="$set('currentFile', '{{ $file['path'] }}')"
                                                    @class([
                                                        'w-full text-left px-4 py-3 rounded-xl text-sm transition-all relative overflow-hidden group/item',
                                                        'bg-slate-900 text-white font-bold shadow-lg shadow-slate-900/20' => $currentFile === $file['path'],
                                                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => $currentFile !== $file['path'],
                                                    ])
                                                >
                                                    <span class="relative z-10">{{ $file['name'] }}</span>
                                                    @if($currentFile === $file['path'])
                                                        <div class="absolute inset-y-0 left-0 w-1 bg-primary-500"></div>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </nav>

                        <div class="bg-primary-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl shadow-primary-600/20">
                            <i class="fa-light fa-circle-question absolute -right-4 -bottom-4 text-8xl opacity-10 rotate-12"></i>
                            <h4 class="text-xl font-black mb-2 relative z-10">Stále tápete?</h4>
                            <p class="text-primary-100 text-sm mb-6 relative z-10 font-medium">Napište nám přímo a my vám s radostí pomůžeme.</p>
                            <button
                                wire:click="submitContactForm"
                                class="w-full py-3 bg-white text-primary-600 rounded-xl font-black text-sm hover:bg-primary-50 transition-colors relative z-10"
                            >
                                Kontaktovat podporu
                            </button>
                        </div>
                    </div>
                </aside>

                {{-- Content --}}
                <main class="flex-1 min-w-0">
                    @php $fileData = $this->getFile() @endphp
                    @if($fileData)
                        <article class="bg-white rounded-[3rem] border border-slate-100 p-8 sm:p-16 shadow-2xl relative overflow-hidden">
                            {{-- Decorative gradient --}}
                            <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-50"></div>

                            <header class="relative mb-12">
                                <div class="flex items-center gap-4 text-primary-600 font-black uppercase tracking-widest text-[10px] mb-4">
                                    <i class="fa-light fa-book-open-reader"></i>
                                    <span>Návod k použití</span>
                                </div>
                                <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                                    {{ $fileData['title'] }}
                                </h1>
                                <div class="mt-6 flex items-center gap-6 text-slate-400 text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-light fa-calendar"></i>
                                        Aktualizováno {{ now()->format('d. m. Y') }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-light fa-clock"></i>
                                        Čtení na 3 min
                                    </div>
                                </div>
                            </header>

                            <div class="prose prose-slate max-w-none
                                prose-headings:font-black prose-headings:tracking-tight prose-headings:text-slate-900
                                prose-p:text-slate-600 prose-p:leading-relaxed prose-p:text-lg
                                prose-a:text-primary-600 prose-a:no-underline hover:prose-a:underline prose-a:font-bold
                                prose-img:rounded-[2.5rem] prose-img:shadow-2xl
                                prose-strong:text-slate-900 prose-strong:font-black
                                prose-ul:list-none prose-ul:pl-0
                                prose-li:relative prose-li:pl-8 prose-li:mb-4
                                before:prose-li:content-['\f00c'] before:prose-li:font-['Font_Awesome_7_Pro'] before:prose-li:absolute before:prose-li:left-0 before:prose-li:text-primary-500 before:prose-li:font-light
                                prose-code:text-primary-600 prose-code:bg-primary-50 prose-code:px-2 prose-code:py-0.5 prose-code:rounded-md prose-code:before:content-none prose-code:after:content-none
                            ">
                                {!! $fileData['content'] !!}
                            </div>

                            <div class="mt-20 pt-10 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-bold text-slate-400">Bylo to užitečné?</span>
                                    <div class="flex gap-2">
                                        <button class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-green-50 hover:text-green-600 transition-colors flex items-center justify-center border border-slate-100">
                                            <i class="fa-light fa-thumbs-up"></i>
                                        </button>
                                        <button class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center justify-center border border-slate-100">
                                            <i class="fa-light fa-thumbs-down"></i>
                                        </button>
                                    </div>
                                </div>
                                <button
                                    wire:click="submitContactForm"
                                    class="text-primary-600 hover:text-primary-700 font-black text-sm uppercase tracking-widest flex items-center gap-2 group"
                                >
                                    Nenašli jste odpověď? Kontaktujte nás
                                    <i class="fa-light fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                                </button>
                            </div>
                        </article>
                    @endif
                </main>
            </div>
        @endif
    </div>
</x-filament-panels::page>
