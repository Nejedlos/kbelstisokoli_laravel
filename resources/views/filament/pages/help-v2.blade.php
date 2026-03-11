<x-filament-panels::page>
    <div class="space-y-12">
        {{-- Search & Hero Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-8 sm:p-16 shadow-2xl">
            {{-- Decorative Background --}}
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
                    @if($searchQuery)
                        <button
                            wire:click="$set('searchQuery', '')"
                            class="absolute inset-y-0 right-0 pr-6 flex items-center text-slate-400 hover:text-white transition-colors"
                        >
                            <i class="fa-light fa-circle-xmark text-xl"></i>
                        </button>
                    @else
                        <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none">
                            <kbd class="hidden sm:inline-flex items-center px-2 py-1 bg-white/10 border border-white/20 rounded-lg text-[10px] font-black text-slate-400">
                                /
                            </kbd>
                        </div>
                    @endif
                </div>

                <script>
                    document.addEventListener('keydown', function(e) {
                        if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                            e.preventDefault();
                            document.getElementById('help-search-input')?.focus();
                        }
                    });

                    // Highlight searches in article detail
                    document.addEventListener('livewire:load', function () {
                        highlightSearchTerms();
                    });

                    document.addEventListener('livewire:navigated', function () {
                        highlightSearchTerms();
                    });

                    function highlightSearchTerms() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const query = urlParams.get('q');

                        if (!query || query.length < 3) return;

                        const elements = document.querySelectorAll('.highlightable');
                        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');

                        elements.forEach(el => {
                            // Only process text nodes to avoid breaking HTML tags
                            const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null, false);
                            let node;
                            const nodesToReplace = [];

                            while (node = walker.nextNode()) {
                                if (node.nodeValue.match(regex) && node.parentElement.tagName !== 'MARK') {
                                    nodesToReplace.push(node);
                                }
                            }

                            nodesToReplace.forEach(node => {
                                const span = document.createElement('span');
                                span.innerHTML = node.nodeValue.replace(regex, '<mark class="bg-primary-100 text-primary-900 px-1 rounded-sm">$1</mark>');
                                node.parentNode.replaceChild(span, node);
                            });
                        });
                    }
                </script>
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
                        <x-help.article-card :article="$result" :query="$searchQuery" />
                    @empty
                        <div class="p-12 sm:p-20 text-center bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden relative">
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-slate-50 rounded-full opacity-50"></div>

                            <div class="relative z-10">
                                <div class="w-24 h-24 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-8 text-primary-600 shadow-inner">
                                    <i class="fa-light fa-magnifying-glass-minus text-4xl"></i>
                                </div>
                                <h4 class="text-3xl font-black text-slate-900 mb-4">{{ __('admin.navigation.pages.help_no_results') }}</h4>
                                <p class="text-slate-500 max-w-md mx-auto text-lg mb-12 font-medium">
                                    {{ __('admin.navigation.pages.help_no_results_desc') }}
                                </p>

                                <div class="bg-slate-50 rounded-2xl p-8 max-w-lg mx-auto text-left border border-slate-100">
                                    <h5 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2">
                                        <i class="fa-light fa-lightbulb text-primary-500"></i>
                                        {{ __('admin.navigation.pages.help_search_no_results_tips') }}
                                    </h5>
                                    <ul class="space-y-4">
                                        <li class="flex items-start gap-3 text-slate-600 font-medium">
                                            <i class="fa-light fa-check-circle text-primary-500 mt-1"></i>
                                            {{ __('admin.navigation.pages.help_search_tip_general') }}
                                        </li>
                                        <li class="flex items-start gap-3 text-slate-600 font-medium">
                                            <i class="fa-light fa-check-circle text-primary-500 mt-1"></i>
                                            {{ __('admin.navigation.pages.help_search_tip_typos') }}
                                        </li>
                                        <li class="flex items-start gap-3 text-slate-600 font-medium">
                                            <i class="fa-light fa-check-circle text-primary-500 mt-1"></i>
                                            {{ __('admin.navigation.pages.help_search_tip_faq') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        @elseif($currentFile)
            {{-- Article Detail --}}
            @php $articleData = $this->getArticleData() @endphp
            @if($articleData)
                @php $article = $articleData['article'] @endphp
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$articleData['breadcrumbs']" />

                    <div class="flex flex-col lg:flex-row gap-12">
                        {{-- Main Content --}}
                        <main class="flex-1 min-w-0">
                            <article class="bg-white rounded-3xl border border-slate-100 p-8 sm:p-16 shadow-2xl relative overflow-hidden">
                                {{-- Decorative gradient --}}
                                <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-50"></div>

                                <header class="relative mb-12">
                                    <div class="flex flex-wrap items-center gap-3 mb-6">
                                        @if($article->audience_roles)
                                            @foreach($article->audience_roles as $role)
                                                <x-help.audience-badge :role="$role" />
                                            @endforeach
                                        @endif
                                    </div>

                                    <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight mb-6 highlightable">
                                        {{ $article->getTranslation('title', app()->getLocale(), false) }}
                                    </h1>

                                    @php $articleMetadata = $article->getTranslation('metadata', app()->getLocale(), false); @endphp
                                    @if(isset($articleMetadata['purpose']))
                                        <div class="text-xl font-medium text-slate-500 border-l-4 border-primary-500 pl-6 my-8 highlightable">
                                            {{ $articleMetadata['purpose'] }}
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-6 text-slate-400 text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-light fa-calendar"></i>
                                            {{ __('admin.navigation.pages.help_updated_at') }} {{ $article->updated_at->translatedFormat('d. m. Y') }}
                                        </div>
                                    </div>
                                </header>

                                <div class="prose prose-slate max-w-none highlightable
                                    prose-headings:font-black prose-headings:tracking-tight prose-headings:text-slate-900
                                    prose-p:text-slate-600 prose-p:leading-relaxed prose-p:text-lg
                                    prose-a:text-primary-600 prose-a:no-underline hover:prose-a:underline prose-a:font-bold
                                    prose-img:rounded-3xl prose-img:shadow-2xl
                                    prose-strong:text-slate-900 prose-strong:font-black
                                    prose-ul:list-none prose-ul:pl-0
                                    prose-li:relative prose-li:pl-8 prose-li:mb-4
                                    before:prose-li:content-['\f00c'] before:prose-li:font-['Font_Awesome_7_Pro'] before:prose-li:absolute before:prose-li:left-0 before:prose-li:text-primary-500 before:prose-li:font-light
                                    prose-code:text-primary-600 prose-code:bg-primary-50 prose-code:px-2 prose-code:py-0.5 prose-code:rounded-md prose-code:before:content-none prose-code:after:content-none
                                    prose-blockquote:border-l-4 prose-blockquote:border-primary-500 prose-blockquote:bg-primary-50/50 prose-blockquote:py-4 prose-blockquote:px-8 prose-blockquote:rounded-r-2xl prose-blockquote:font-medium prose-blockquote:text-slate-700
                                ">
                                    {!! $article->getParsedContent() !!}
                                </div>

                                <x-help.article-navigation :prev="$articleData['prev']" :next="$articleData['next']" />

                                @if($article->faqs->count() > 0)
                                    <section class="mt-20 space-y-8 highlightable">
                                        <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('admin.navigation.pages.help_faqs') }}</h3>
                                        <div class="space-y-4">
                                            @foreach($article->faqs as $faq)
                                                <x-help.faq-item :faq="$faq" />
                                            @endforeach
                                        </div>
                                    </section>
                                @endif

                                <div class="mt-20 pt-10 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm font-bold text-slate-400">{{ __('admin.navigation.pages.help_was_helpful') }}</span>
                                        <div class="flex gap-2">
                                            <button class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-green-50 hover:text-green-600 transition-colors flex items-center justify-center border border-slate-100 shadow-sm">
                                                <i class="fa-light fa-thumbs-up"></i>
                                            </button>
                                            <button class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center justify-center border border-slate-100 shadow-sm">
                                                <i class="fa-light fa-thumbs-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        wire:click="submitContactForm"
                                        class="text-primary-600 hover:text-primary-700 font-black text-sm uppercase tracking-widest flex items-center gap-2 group"
                                    >
                                        {{ __('admin.navigation.pages.help_no_answer') }}
                                        <i class="fa-light fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                                    </button>
                                </div>
                            </article>
                        </main>

                        {{-- Right Sidebar --}}
                        <aside class="w-full lg:w-80 shrink-0 space-y-12">
                            @if($article->quickActions->count() > 0)
                                <div class="space-y-6">
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 px-4">{{ __('admin.navigation.pages.help_quick_actions') }}</h4>
                                    <div class="space-y-3">
                                        @foreach($article->quickActions as $action)
                                            <x-help.quick-action :action="$action" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($article->relatedArticles->count() > 0)
                                <div class="space-y-6">
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 px-4">{{ __('admin.navigation.pages.help_related_articles') }}</h4>
                                    <div class="space-y-3">
                                        @foreach($article->relatedArticles as $related)
                                            <a href="{{ \App\Filament\Pages\Help::getUrl(['file' => $related->slug]) }}"
                                               class="block p-4 bg-white rounded-2xl border border-slate-100 hover:border-primary-100 hover:shadow-md transition-all">
                                                <span class="text-sm font-bold text-slate-700 block">{{ $related->getTranslation('title', app()->getLocale(), false) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <x-help.sidebar-nav :tree="$this->getTree()" :currentCategory="$article->category" :currentArticle="$article" />

                            <div class="bg-primary-600 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl shadow-primary-600/20">
                                <i class="fa-light fa-circle-question absolute -right-4 -bottom-4 text-8xl opacity-10 rotate-12"></i>
                                <h4 class="text-xl font-black mb-2 relative z-10">{{ __('admin.navigation.pages.help_need_more') }}</h4>
                                <p class="text-primary-100 text-sm mb-6 relative z-10 font-medium">{{ __('admin.navigation.pages.help_support_desc') }}</p>
                                <button
                                    wire:click="submitContactForm"
                                    class="w-full py-3 bg-white text-primary-600 rounded-xl font-black text-sm hover:bg-primary-50 transition-colors relative z-10"
                                >
                                    {{ __('admin.navigation.pages.help_contact_support') }}
                                </button>
                            </div>
                        </aside>
                    </div>
                </div>
            @endif
        @elseif($currentCategory)
            {{-- Category Content List --}}
            @php $categoryData = $this->getCategoryData() @endphp
            @if($categoryData)
                @php $category = $categoryData['category'] @endphp
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$categoryData['breadcrumbs']" />

                    <div class="flex flex-col lg:flex-row gap-12">
                        <main class="flex-1 space-y-8">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-white p-10 rounded-3xl border border-slate-100 shadow-sm overflow-hidden relative">
                                <div @class([
                                    'absolute top-0 right-0 w-32 h-32 -mr-8 -mt-8 rounded-full blur-3xl opacity-10',
                                    'bg-orange-500' => $category->color === 'orange',
                                    'bg-blue-500' => $category->color === 'blue',
                                    'bg-green-500' => $category->color === 'green',
                                    'bg-purple-500' => $category->color === 'purple',
                                    'bg-red-500' => $category->color === 'red',
                                    'bg-slate-500' => $category->color === 'slate',
                                ])></div>

                                <div class="flex items-center gap-8 relative z-10">
                                    <div @class([
                                        'w-20 h-20 rounded-[1.5rem] flex items-center justify-center text-3xl shadow-sm',
                                        'bg-orange-50 text-orange-600' => $category->color === 'orange',
                                        'bg-blue-50 text-blue-600' => $category->color === 'blue',
                                        'bg-green-50 text-green-600' => $category->color === 'green',
                                        'bg-purple-50 text-purple-600' => $category->color === 'purple',
                                        'bg-red-50 text-red-600' => $category->color === 'red',
                                        'bg-slate-50 text-slate-600' => $category->color === 'slate',
                                    ])>
                                        <i class="fa-light {{ $category->icon }}"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $category->getTranslation('name', app()->getLocale(), false) }}</h3>
                                        <p class="text-slate-500 font-medium text-lg max-w-2xl">{{ $category->getTranslation('description', app()->getLocale(), false) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-6">
                                @foreach($category->articles as $article)
                                    <x-help.article-card
                                        :article="$article"
                                        :query="$search"
                                    />
                                @endforeach
                            </div>
                        </main>

                        <aside class="w-full lg:w-80 shrink-0">
                            <x-help.sidebar-nav :tree="$this->getTree()" :currentCategory="$category" />
                        </aside>
                    </div>
                </div>
            @endif
        @else
            {{-- Categories Landing --}}
            @php $homeData = $this->getHomeData() @endphp
            <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="flex items-end justify-between px-4">
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ __('admin.navigation.pages.help_browse_categories') }}</h3>
                        <p class="text-slate-500 font-medium">{{ __('admin.navigation.pages.help_browse_categories_desc') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($homeData['categories'] as $category)
                        <x-help.category-card :category="$category" />
                    @endforeach
                </div>

                @if($homeData['featured_articles']->count() > 0)
                    <div class="space-y-8 pt-12 border-t border-slate-100">
                        <div class="px-4">
                            <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ __('admin.navigation.pages.help_featured_articles') }}</h3>
                            <p class="text-slate-500 font-medium">{{ __('admin.navigation.pages.help_featured_articles_desc') }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($homeData['featured_articles'] as $article)
                                <x-help.article-card
                                    :article="$article"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
