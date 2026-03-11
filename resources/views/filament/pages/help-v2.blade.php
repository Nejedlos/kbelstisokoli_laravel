<x-filament-panels::page>
    @php $page = $page ?? ($this ?? null); @endphp
    <div class="space-y-12 py-8">
        {{-- Search & Hero Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-8 sm:p-16 shadow-2xl">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-primary-500/20 rounded-full blur-[100px] animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-secondary-500/10 rounded-full blur-[100px]"></div>

            <div class="relative z-10 max-w-3xl mx-auto text-center space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-white/80 text-sm font-medium backdrop-blur-md mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                    </span>
                    {{ __('admin.navigation.pages.help') }} Kbelští sokoli
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
                        id="help-search-input"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="{{ __('admin.navigation.pages.help_search_placeholder') }}"
                        class="block w-full pl-14 pr-14 py-6 bg-white/10 border border-white/20 rounded-2xl shadow-2xl backdrop-blur-xl focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500/50 text-white placeholder-slate-500 transition-all text-lg"
                    >
                </div>
            </div>
        </div>

        @if($searchQuery)
            {{-- SEARCH_RESULTS_SECTION_START --}}
            <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                        <i class="fa-light fa-list-check text-primary-600 bg-primary-50 p-2 rounded-lg"></i>
                        Výsledky vyhledávání pro <span class="text-primary-600">"{{ $searchQuery }}"</span>
                    </h3>
                    <button wire:click="$set('searchQuery', '')" class="text-sm font-bold text-slate-500 hover:text-primary-600 transition-colors">
                        Zrušit
                    </button>
                </div>

                <div class="grid gap-4">
                    @forelse($page->getSearchResults() as $result)
                        <x-help.article-card :article="$result" :query="$searchQuery" />
                    @empty
                        <div class="p-12 text-center bg-white rounded-3xl border border-slate-100 shadow-xl">
                            <h4 class="text-3xl font-black text-slate-900 mb-4">Žádné výsledky</h4>
                            <p class="text-slate-500 max-w-md mx-auto text-lg font-medium">
                                Zkuste zadat jiné klíčové slovo.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
            {{-- SEARCH_RESULTS_SECTION_END --}}
        @elseif($currentFile)
            {{-- ARTICLE_DETAIL_SECTION_START --}}
            @php $articleData = $page?->getArticleData() @endphp
            @if($articleData)
                @php $article = $articleData['article'] @endphp
                <div class="space-y-8">
                    <x-help.breadcrumbs :breadcrumbs="$articleData['breadcrumbs']" />

                    <div class="flex flex-col lg:flex-row gap-12">
                        <main class="flex-1 min-w-0">
                            <article class="bg-white rounded-3xl p-8 sm:p-16 shadow-2xl">
                                <h1 class="text-4xl font-black mb-8">
                                    {{ $article->title_str ?? ($article->title ?? 'Untitled') }}
                                </h1>

                                <div class="prose prose-slate max-w-none">
                                    {!! $article->content_html ?? (method_exists($article, 'getParsedContent') ? $article->getParsedContent() : '') !!}
                                </div>

                                @if(count($article->faqs ?? []) > 0)
                                    <div class="mt-12 space-y-4">
                                        <h3 class="text-2xl font-bold mb-6">FAQ</h3>
                                        @foreach($article->faqs as $faq)
                                            <x-help.faq-item :faq="$faq" />
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        </main>

                        <aside class="w-full lg:w-80 space-y-12">
                            @if(count($article->quickActions ?? []) > 0)
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">Rychlé akce</h4>
                                    <div class="space-y-2">
                                        @foreach($article->quickActions as $action)
                                            <x-help.quick-action :action="$action" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($article->relatedArticles ?? []) > 0)
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">Související články</h4>
                                    <div class="space-y-2">
                                        @foreach($article->relatedArticles as $related)
                                            <a href="{{ \App\Filament\Pages\Help::getUrl(['file' => $related->slug]) }}"
                                               class="block p-4 bg-white rounded-2xl border border-slate-100 hover:border-primary-100 hover:shadow-md transition-all">
                                                <span class="text-sm font-bold text-slate-700 block">{{ $related->title_str ?? $related->title }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <x-help.sidebar-nav :tree="$page->getTree()" :currentCategory="$article->category" :currentArticle="$article" />
                        </aside>
                    </div>
                </div>
            @endif
            {{-- ARTICLE_DETAIL_SECTION_END --}}
        @elseif($currentCategory)
            {{-- CATEGORY_DETAIL_SECTION_START --}}
            @php $categoryData = $page?->getCategoryData() @endphp
            @if($categoryData)
                @php $category = $categoryData['category'] @endphp
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    {{-- KROK 1: POUZE SKELETON --}}
                    <div class="bg-white p-10 rounded-3xl border border-slate-100 shadow-sm">
                         <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $category->name_str ?? 'Untitled' }}</h3>
                         <p class="text-slate-500 font-medium text-lg">Category detail skeleton (bez komponent)</p>
                    </div>

                    {{--
                    <x-help.breadcrumbs :breadcrumbs="$categoryData['breadcrumbs']" />

                    <div class="flex flex-col lg:flex-row gap-12">
                        <main class="flex-1 space-y-8">
                            <div class="bg-white p-10 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                                <div class="flex items-center gap-8 relative z-10">
                                    <div @class([
                                        'w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-0 shadow-sm border border-white/50',
                                        'bg-orange-50 text-orange-600' => ($category->color ?? '') === 'orange',
                                        'bg-blue-50 text-blue-600' => ($category->color ?? '') === 'blue',
                                        'bg-green-50 text-green-600' => ($category->color ?? '') === 'green',
                                        'bg-purple-50 text-purple-600' => ($category->color ?? '') === 'purple',
                                        'bg-red-50 text-red-600' => ($category->color ?? '') === 'red',
                                        'bg-slate-50 text-slate-600' => ($category->color ?? '') === 'slate',
                                    ])>
                                        <i class="fa-light {{ $category->icon ?? 'fa-folder' }} fa-fw"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $category->name_str ?? 'Untitled' }}</h3>
                                        <p class="text-slate-500 font-medium text-lg max-w-2xl">{{ $category->description_str ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-6">
                                @foreach($category->articles as $article)
                                    <x-help.article-card
                                        :article="$article"
                                        :query="$searchQuery"
                                    />
                                @endforeach
                            </div>
                        </main>

                        <aside class="w-full lg:w-80 shrink-0">
                            <x-help.sidebar-nav :tree="$page->getTree()" :currentCategory="$category" />
                        </aside>
                    </div>
                    --}}
                </div>
            @endif
            {{-- CATEGORY_DETAIL_SECTION_END --}}
        @else
            {{-- LANDING_SECTION_START --}}
            @php
                $homeData = $page->getHomeData();
                $userRoles = auth()->user() ? auth()->user()->getRoleNames()->toArray() : [];
            @endphp
            <div class="space-y-12">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($homeData['categories'] as $category)
                        <x-help.category-card :category="$category" />
                    @endforeach
                </div>

                @if(count($homeData['featured_articles'] ?? []) > 0)
                    <div class="space-y-8 pt-12 border-t border-slate-100">
                        <div class="px-4">
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Doporučené články</h3>
                            <p class="text-slate-500 font-medium">To nejdůležitější pro vás na jednom místě.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($homeData['featured_articles'] as $article)
                                <a href="{{ \App\Filament\Pages\Help::getUrl(['file' => $article->slug]) }}"
                                   class="group p-6 rounded-2xl border transition-all text-left flex items-center justify-between bg-white border-slate-100 hover:border-primary-200 hover:shadow-xl hover:-translate-y-0.5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                                            <i class="fa-light fa-file-lines"></i>
                                        </div>
                                        <div>
                                            <span class="block text-lg font-bold text-slate-700 group-hover:text-slate-900 transition-colors mb-0.5">
                                                {{ $article->title_str ?? 'Untitled' }}
                                            </span>
                                        </div>
                                    </div>
                                    <i class="fa-light fa-arrow-right text-slate-300 group-hover:text-primary-600 transition-colors"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            {{-- LANDING_SECTION_END --}}
        @endif
    </div>
</x-filament-panels::page>
