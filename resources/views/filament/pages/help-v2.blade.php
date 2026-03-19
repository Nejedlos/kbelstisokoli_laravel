<x-filament-panels::page>
    @php $page = $page ?? ($this ?? null); @endphp
    <div class="space-y-12 py-8 max-w-7xl mx-auto">
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
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-4">
                        <i class="fa-light fa-magnifying-glass text-primary-600 bg-primary-50 p-3 rounded-2xl shadow-sm border border-primary-100"></i>
                        Výsledky vyhledávání pro <span class="text-primary-600">"{{ $searchQuery }}"</span>
                    </h3>
                    <button wire:click="$set('searchQuery', '')" class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all font-bold text-sm">
                        <i class="fa-light fa-xmark group-hover:rotate-90 transition-transform"></i>
                        Zrušit
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($page->getSearchResults() as $result)
                        <x-help.article-card
                            :article="$result"
                            :user-roles="$userRoles ?? []"
                        />
                    @empty
                        <div class="p-20 text-center bg-white rounded-[3rem] border border-slate-100 shadow-2xl col-span-full">
                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fa-light fa-face-frown text-4xl text-slate-300"></i>
                            </div>
                            <h4 class="text-3xl font-black text-slate-900 mb-4 tracking-tight">Žádné výsledky</h4>
                            <p class="text-slate-500 max-w-md mx-auto text-lg font-medium leading-relaxed">
                                Zkuste zadat jiné klíčové slovo nebo prozkoumejte kategorie nápovědy níže.
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
                <div class="space-y-8 py-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$articleData['breadcrumbs']" />

                    <div class="flex flex-col lg:flex-row gap-12">
                        <main class="flex-1 min-w-0">
                            <article class="bg-white rounded-3xl p-8 sm:p-16 shadow-2xl border border-slate-50 relative overflow-hidden">
                                {{-- Subtle Background Decor --}}
                                <div class="absolute top-0 right-0 w-96 h-96 -mr-48 -mt-48 bg-primary-50/50 rounded-full blur-3xl opacity-50"></div>

                                <div class="relative z-10">
                                    <h1 class="text-4xl md:text-5xl font-black mb-12 text-slate-900 tracking-tight leading-[1.1]">
                                        {{ $article->title_str ?? ($article->title ?? 'Untitled') }}
                                    </h1>

                                    <div class="prose prose-slate max-w-none prose-h2:text-3xl prose-h2:font-black prose-h2:tracking-tight prose-h2:mt-16 prose-h2:mb-8 prose-h3:text-2xl prose-h3:font-bold prose-h3:mt-12 prose-h3:mb-6 prose-p:text-slate-600 prose-p:leading-relaxed prose-p:text-lg prose-strong:text-slate-900 prose-li:text-slate-600 prose-img:rounded-3xl prose-img:shadow-xl">
                                        {!! $article->content_html ?? (method_exists($article, 'getParsedContent') ? $article->getParsedContent() : '') !!}
                                    </div>

                                    @if(count($article->faqs ?? []) > 0)
                                        <div class="mt-24 pt-12 border-t border-slate-100">
                                            <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-12">Často kladené dotazy</h3>
                                            <div class="space-y-4">
                                                @foreach($article->faqs as $faq)
                                                    <x-help.faq-item :faq="$faq" />
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
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
            @php
                $categoryData = $page?->getCategoryData();
                $userRoles = auth()->user() ? auth()->user()->getRoleNames()->toArray() : [];
            @endphp
            @if($categoryData)
                @php $category = $categoryData['category'] @endphp
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 py-8">
                    <x-help.breadcrumbs :breadcrumbs="$categoryData['breadcrumbs']" />

                    <div class="bg-white p-10 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="flex items-center gap-8 relative z-10">
                            <div @class([
                        'w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-0 shadow-sm border border-white/50 relative z-10',
                        'bg-orange-50 text-orange-600' => ($category->color ?? 'slate') === 'orange',
                        'bg-blue-50 text-blue-600' => ($category->color ?? 'slate') === 'blue',
                        'bg-green-50 text-green-600' => ($category->color ?? 'slate') === 'green',
                        'bg-purple-50 text-purple-600' => ($category->color ?? 'slate') === 'purple',
                        'bg-red-50 text-red-600' => ($category->color ?? 'slate') === 'red',
                        'bg-slate-50 text-slate-600' => ($category->color ?? 'slate') === 'slate' || empty($category->color),
                    ])>
                                @php
                                    $iconClass = isset($category->icon) ? preg_replace('/\\bfa-(light|regular|solid)\\b/', '', $category->icon) : 'fa-folder';
                                @endphp
                                <i class="fa-light {{ trim($iconClass) }} fa-fw"></i>
                            </div>
                            <div>
                                <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $category->name_str ?? 'Untitled' }}</h3>
                                <p class="text-slate-500 font-medium text-lg max-w-2xl">{{ $category->description_str ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row gap-12">
                        <main class="flex-1 space-y-8">
                            <div class="grid gap-6">
                                @foreach($category->articles as $article)
                                    <x-help.article-card
                                        :article="$article"
                                        :user-roles="$userRoles ?? []"
                                    />
                                @endforeach
                            </div>
                        </main>

                        <aside class="w-full lg:w-80 shrink-0">
                            <x-help.sidebar-nav :tree="$page->getTree()" :currentCategory="$category" />
                        </aside>
                    </div>
                </div>
            @endif
            {{-- CATEGORY_DETAIL_SECTION_END --}}
        @else
            {{-- LANDING_SECTION_START --}}
            @php
                $homeData = $page->getHomeData();
                $userRoles = auth()->user() ? auth()->user()->getRoleNames()->toArray() : [];
            @endphp
            <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500 py-8">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($homeData['featured_articles'] as $article)
                                <x-help.article-card
                                    :article="$article"
                                    :user-roles="$userRoles ?? []"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            {{-- LANDING_SECTION_END --}}
        @endif
    </div>
</x-filament-panels::page>
