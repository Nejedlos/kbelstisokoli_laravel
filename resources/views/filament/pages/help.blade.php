<x-filament-panels::page>
    @php
        $page = $page ?? ($this ?? null);
        $isLanding = !$currentFile && !$currentCategory && !$searchQuery;
    @endphp

    <div class="mx-auto w-full space-y-8 py-8 max-w-7xl">
        {{-- Search & Hero Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 shadow-xl p-5 sm:p-8 border border-white/5">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/10 rounded-full blur-[80px] -mr-20 -mt-20"></div>

            <div class="relative z-10 mx-auto flex flex-col md:flex-row items-center justify-between gap-6 md:gap-8">
                <div class="flex-1 text-left flex items-center gap-4 sm:gap-6">
                    @if(!$isLanding)
                        <a href="{{ \App\Filament\Pages\Help::getUrl() }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-primary-400 hover:bg-primary-600 hover:text-white transition-all group">
                            <i class="fa-light fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                        </a>
                    @endif
                    <div class="space-y-1">
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                            {{ __('admin.navigation.pages.help') }}
                        </h2>
                        <p class="text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">{{ __('admin.navigation.pages.help_info_center') }}</p>
                    </div>
                </div>

                <div class="relative group max-w-md w-full">
                    <label for="help-search-input" class="sr-only">{{ __('admin.navigation.pages.help_search_placeholder') }}</label>
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fa-light fa-magnifying-glass text-slate-400 text-base sm:text-lg group-focus-within:text-primary-400 transition-colors"></i>
                    </div>
                    <input
                        type="search"
                        id="help-search-input"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="{{ __('admin.navigation.pages.help_search_placeholder') }}"
                        class="block w-full pl-12 pr-10 py-3 sm:py-4 bg-white/5 border border-white/10 rounded-2xl shadow-xl backdrop-blur-xl focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500/50 text-white placeholder-slate-500 transition-all text-sm sm:text-base font-medium"
                    >
                    @if($searchQuery)
                         <button wire:click="$set('searchQuery', '')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-white transition-colors">
                             <i class="fa-light fa-xmark"></i>
                         </button>
                    @endif
                </div>
            </div>
        </div>

        @if($searchQuery)
            {{-- SEARCH_RESULTS_SECTION_START --}}
            <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex items-center justify-between px-4">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3 sm:gap-4">
                        <i class="fa-light fa-magnifying-glass text-primary-600 bg-primary-50 p-2 sm:p-3 rounded-2xl shadow-sm border border-primary-100"></i>
                        {{ __('admin.navigation.pages.help_search_results_for') }} <span class="text-primary-600">"{{ $searchQuery }}"</span>
                    </h2>
                    <button wire:click="$set('searchQuery', '')" class="group flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all font-bold text-xs sm:text-sm">
                        <i class="fa-light fa-xmark group-hover:rotate-90 transition-transform"></i>
                        {{ __('admin.navigation.pages.help_search_cancel') }}
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
                            <h4 class="text-3xl font-black text-slate-900 mb-4 tracking-tight">{{ __('admin.navigation.pages.help_no_results') }}</h4>
                            <p class="text-slate-500 max-w-md mx-auto text-lg font-medium leading-relaxed">
                                {{ __('admin.navigation.pages.help_no_results_desc') }}
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
                @php
                    $article = $articleData['article'];
                    $category = $article->category;
                @endphp
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$articleData['breadcrumbs']" />

                    <div class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 sm:gap-8 relative z-10">
                            <div @class([
                                'w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-2xl sm:rounded-3xl flex items-center justify-center text-3xl sm:text-4xl mb-0 shadow-sm border border-white/50 relative z-10',
                                'bg-orange-50 text-orange-600' => ($category->color ?? 'slate') === 'orange',
                                'bg-sky-50 text-sky-600' => in_array($category->color ?? 'slate', ['blue', 'sky']),
                                'bg-emerald-50 text-emerald-600' => in_array($category->color ?? 'slate', ['emerald', 'green']),
                                'bg-teal-50 text-teal-600' => ($category->color ?? 'slate') === 'teal',
                                'bg-amber-50 text-amber-600' => in_array($category->color ?? 'slate', ['amber', 'yellow', 'orange-yellow']),
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
                                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $article->title_str ?? $article->title }}</h1>
                                <p class="text-slate-600 font-medium text-base sm:text-lg max-w-2xl">{{ $category->name_str ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row gap-12">
                        <main class="flex-1 min-w-0">
                            <article class="bg-white rounded-3xl p-6 sm:p-12 md:p-16 shadow-2xl border border-slate-50 relative overflow-hidden">
                                {{-- Subtle Background Decor --}}
                                <div class="absolute top-0 right-0 w-96 h-96 -mr-48 -mt-48 bg-primary-50/50 rounded-full blur-3xl opacity-50"></div>

                                <div class="relative z-10">
                                    <div class="prose prose-slate max-w-none prose-h2:text-2xl sm:prose-h2:text-3xl prose-h2:font-black prose-h2:tracking-tight prose-h2:mt-12 sm:prose-h2:mt-16 prose-h2:mb-6 sm:prose-h2:mb-8 prose-h3:text-xl sm:prose-h3:text-2xl prose-h3:font-bold prose-h3:mt-10 sm:prose-h3:mt-12 prose-h3:mb-4 sm:prose-h3:mb-6 prose-p:text-slate-600 prose-p:leading-relaxed prose-p:text-base sm:prose-p:text-lg prose-strong:text-slate-900 prose-li:text-slate-600 prose-img:rounded-3xl prose-img:shadow-xl">
                                        {!! $article->content_html ?? (method_exists($article, 'getParsedContent') ? $article->getParsedContent() : '') !!}
                                    </div>

                                    @if(count($article->faqs ?? []) > 0)
                                        <div class="mt-24 pt-12 border-t border-slate-100">
                                            <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-12">{{ __('admin.navigation.resources.help_faq.plural_label') }}</h3>
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
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">{{ __('admin.navigation.resources.help_quick_action.plural_label') }}</h4>
                                    <div class="space-y-2">
                                        @foreach($article->quickActions as $action)
                                            <x-help.quick-action :action="$action" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($article->relatedArticles ?? []) > 0)
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 px-4">{{ __('admin.navigation.pages.help_related_articles') }}</h4>
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
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$categoryData['breadcrumbs']" />

                    <div class="bg-white p-10 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="flex items-center gap-8 relative z-10">
                            <div @class([
                                'w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-0 shadow-sm border border-white/50 relative z-10',
                                'bg-orange-50 text-orange-600' => ($category->color ?? 'slate') === 'orange',
                                'bg-sky-50 text-sky-600' => in_array($category->color ?? 'slate', ['blue', 'sky']),
                                'bg-emerald-50 text-emerald-600' => in_array($category->color ?? 'slate', ['emerald', 'green']),
                                'bg-teal-50 text-teal-600' => ($category->color ?? 'slate') === 'teal',
                                'bg-amber-50 text-amber-600' => in_array($category->color ?? 'slate', ['amber', 'yellow', 'orange-yellow']),
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
                                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $category->name_str ?? 'Untitled' }}</h1>
                                <p class="text-slate-600 font-medium text-lg max-w-2xl">{{ $category->description_str ?? '' }}</p>
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
            <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="bg-white p-10 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-8 relative z-10">
                        <div class="w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-0 shadow-sm border border-white/50 relative z-10 bg-primary-50 text-primary-600">
                            <i class="fa-light fa-rocket-launch fa-fw"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">S čím vám můžeme pomoci?</h1>
                            <p class="text-slate-600 font-medium text-lg max-w-2xl">{{ __('admin.navigation.pages.help_description') }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($homeData['categories'] as $category)
                        <x-help.category-card :category="$category" />
                    @endforeach
                </div>

                @if(count($homeData['featured_articles'] ?? []) > 0)
                    <div class="space-y-8 pt-12 border-t border-slate-100">
                        <div class="px-4">
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Doporučené články</h2>
                            <p class="text-slate-600 font-medium">To nejdůležitější pro vás na jednom místě.</p>
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
