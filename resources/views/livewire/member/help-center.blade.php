<div>
    @php
        $isLanding = !$currentFile && !$currentCategory && !$searchQuery;
    @endphp

    <div class="mx-auto w-full space-y-8 py-8 max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Search & Hero Header --}}
        <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-slate-200/60 shadow-xl shadow-slate-200/40 p-6 sm:p-10">
            {{-- Pozadí s efektem --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-[100px] -mr-32 -mt-32 opacity-50"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-secondary/5 rounded-full blur-[80px] -ml-20 -mb-20 opacity-30"></div>

            <div class="relative z-10 mx-auto flex flex-col lg:flex-row items-center justify-between gap-10">
                <div class="flex-1 text-left flex items-center gap-8">
                    @if(!$isLanding)
                        <button wire:click="goHome" class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-primary-600 hover:bg-primary hover:text-white transition-all group shadow-sm">
                            <i class="fa-light fa-arrow-left text-xl transition-transform group-hover:-translate-x-1"></i>
                        </button>
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-primary flex items-center justify-center text-white shadow-xl shadow-primary/30">
                            <i class="fa-light fa-circle-question text-2xl"></i>
                        </div>
                    @endif
                    <div class="space-y-1">
                        <h2 class="text-3xl font-black text-secondary tracking-tight flex items-center gap-3 uppercase">
                            {{ __('admin.navigation.pages.help') }}
                        </h2>
                        <div class="flex items-center gap-3">
                            <p class="text-primary text-[10px] font-black uppercase tracking-[0.2em]">{{ __('admin.help.info_center') }}</p>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-200"></span>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">{{ __('member.nav.member_section') }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative group max-w-lg w-full">
                    <label for="help-search-input" class="sr-only">{{ __('admin.navigation.pages.help_search_placeholder') }}</label>
                    <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                        <i class="fa-light fa-magnifying-glass text-slate-400 text-xl group-focus-within:text-primary transition-colors"></i>
                    </div>
                    <input
                        type="search"
                        id="help-search-input"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="{{ __('admin.navigation.pages.help_search_placeholder') }}"
                        class="block w-full pl-16 pr-14 py-5 bg-slate-50 border border-slate-100 rounded-3xl focus:ring-4 focus:ring-primary/20 focus:border-primary/50 text-secondary placeholder-slate-400 transition-all text-lg font-bold shadow-inner"
                    >
                    @if($searchQuery)
                         <button wire:click="resetSearch" class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-300 hover:text-red-500 transition-colors">
                             <i class="fa-light fa-circle-xmark text-xl"></i>
                         </button>
                    @endif
                </div>
            </div>
        </div>

        @if($searchQuery)
            {{-- Výsledky vyhledávání --}}
            <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-4xl font-black text-secondary tracking-tight flex items-center gap-5">
                        <span class="bg-primary/10 text-primary p-4 rounded-3xl shadow-sm border border-primary/20 flex items-center justify-center w-16 h-16">
                             <i class="fa-light fa-magnifying-glass"></i>
                        </span>
                        <span>Výsledky pro <span class="text-primary-600 italic">"{{ $searchQuery }}"</span></span>
                    </h2>
                    <button wire:click="resetSearch" class="group flex items-center gap-3 px-6 py-3 rounded-2xl bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-600 transition-all font-black text-sm uppercase tracking-widest">
                        <i class="fa-light fa-xmark text-lg group-hover:rotate-90 transition-transform"></i>
                        Zrušit
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($searchResults as $result)
                        <x-help.article-card
                            :article="$result"
                            :user-roles="$userRoles"
                            :query="$searchQuery"
                        />
                    @empty
                        <div class="p-24 text-center bg-white rounded-[4rem] border border-slate-100 shadow-2xl col-span-full relative overflow-hidden">
                             <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50 -mt-32"></div>
                            <div class="relative z-10">
                                <div class="w-32 h-32 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-10 shadow-inner rotate-3">
                                    <i class="fa-light fa-face-frown text-6xl text-slate-300"></i>
                                </div>
                                <h4 class="text-4xl font-black text-secondary mb-6 tracking-tight">Nic jsme nenašli</h4>
                                <p class="text-slate-500 max-w-xl mx-auto text-xl font-medium leading-relaxed">
                                    Zkuste zadat jiné klíčové slovo nebo prozkoumejte kategorie nápovědy, které jsou vám k dispozici.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        @elseif($currentFile)
            {{-- Detail článku --}}
            @if($articleData)
                @php
                    $article = $articleData['article'];
                    $category = $article->category;
                @endphp
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$articleData['breadcrumbs']" />

                    <div class="bg-white p-8 sm:p-14 rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
                        {{-- Pozadí detailu --}}
                        <div @class([
                            'absolute top-0 right-0 w-[500px] h-[500px] rounded-full blur-[120px] opacity-10 -mr-64 -mt-64',
                            'bg-orange-500' => ($category->color ?? 'slate') === 'orange',
                            'bg-sky-500' => in_array($category->color ?? 'slate', ['blue', 'sky']),
                            'bg-emerald-500' => in_array($category->color ?? 'slate', ['emerald', 'green']),
                            'bg-teal-500' => ($category->color ?? 'slate') === 'teal',
                            'bg-amber-500' => in_array($category->color ?? 'slate', ['amber', 'yellow', 'orange-yellow']),
                            'bg-purple-500' => ($category->color ?? 'slate') === 'purple',
                            'bg-red-500' => ($category->color ?? 'slate') === 'red',
                            'bg-slate-500' => ($category->color ?? 'slate') === 'slate' || empty($category->color),
                        ])></div>

                        <div class="flex flex-col md:flex-row items-center md:items-start gap-12 relative z-10">
                            <div @class([
                                'w-32 h-32 rounded-[2.5rem] shrink-0 flex items-center justify-center text-5xl shadow-2xl border border-white/50 relative z-10 rotate-3',
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
                            <div class="text-center md:text-left pt-2">
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-6">
                                    <span class="px-5 py-2 rounded-full bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest border border-slate-200">
                                        {{ $category->name_str }}
                                    </span>
                                    @if($article->is_featured)
                                        <span class="px-5 py-2 rounded-full bg-amber-100 text-amber-700 text-xs font-black uppercase tracking-widest border border-amber-200 flex items-center gap-2">
                                            <i class="fa-solid fa-star text-[10px]"></i>
                                            Doporučeno
                                        </span>
                                    @endif
                                </div>
                                <h1 class="text-4xl sm:text-6xl font-black text-secondary leading-[1.1] mb-8 tracking-tight">
                                    {{ $article->title_str }}
                                </h1>
                                <p class="text-xl text-slate-500 font-medium leading-relaxed max-w-3xl">
                                    {{ $article->excerpt_str }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-16 prose prose-slate prose-lg max-w-none prose-headings:font-black prose-headings:tracking-tight prose-a:text-primary-600 prose-img:rounded-3xl prose-img:shadow-2xl border-t border-slate-50 pt-16">
                            {!! $article->content_html !!}
                        </div>

                        @if(isset($articleData['faqs']) && count($articleData['faqs']) > 0)
                            <div class="mt-20 pt-20 border-t border-slate-100">
                                <h3 class="text-3xl font-black text-secondary mb-10 tracking-tight flex items-center gap-4">
                                    <i class="fa-light fa-comments-question text-primary"></i>
                                    Často kladené dotazy
                                </h3>
                                <div class="space-y-4 max-w-4xl">
                                    @foreach($articleData['faqs'] as $faq)
                                        <x-help.faq-item :faq="$faq" />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <x-help.article-navigation
                            :prev="$articleData['prev_article'] ?? null"
                            :next="$articleData['next_article'] ?? null"
                        />
                    </div>
                </div>
            @endif
        @elseif($currentCategory)
            {{-- Detail kategorie --}}
            @if($categoryData)
                @php
                    $category = $categoryData['category'];
                    $articles = $categoryData['articles'] ?? collect();
                    $subcategories = $categoryData['subcategories'] ?? collect();
                @endphp
                <div class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <x-help.breadcrumbs :breadcrumbs="$categoryData['breadcrumbs']" />

                    <div class="flex flex-col lg:flex-row gap-12">
                        <div class="lg:w-1/3">
                            <x-help.sidebar-nav
                                :tree="$tree"
                                :current-category="$category"
                            />
                        </div>
                        <div class="lg:w-2/3 space-y-10">
                            {{-- Podkategorie --}}
                            @if($subcategories->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($subcategories as $sub)
                                        <x-help.category-card :category="$sub" />
                                    @endforeach
                                </div>
                            @endif

                            <div class="bg-white p-10 sm:p-14 rounded-[2.5rem] border border-slate-200/60 shadow-xl shadow-slate-200/40 relative overflow-hidden">
                                <div @class([
                                    'absolute top-0 right-0 w-80 h-80 rounded-full blur-[100px] opacity-10 -mr-32 -mt-32',
                                    'bg-orange-500' => ($category->color ?? 'slate') === 'orange',
                                    'bg-sky-500' => in_array($category->color ?? 'slate', ['blue', 'sky']),
                                    'bg-emerald-500' => in_array($category->color ?? 'slate', ['emerald', 'green']),
                                    'bg-teal-500' => ($category->color ?? 'slate') === 'teal',
                                    'bg-amber-500' => in_array($category->color ?? 'slate', ['amber', 'yellow', 'orange-yellow']),
                                    'bg-purple-500' => ($category->color ?? 'slate') === 'purple',
                                    'bg-red-500' => ($category->color ?? 'slate') === 'red',
                                    'bg-slate-500' => ($category->color ?? 'slate') === 'slate' || empty($category->color),
                                ])></div>

                                <div class="relative z-10">
                                    <div @class([
                                        'w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-8 shadow-sm border border-white/50 rotate-3',
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
                                    <h1 class="text-5xl font-black text-secondary mb-6 tracking-tight">{{ $category->name_str }}</h1>
                                    <p class="text-xl text-slate-500 font-medium leading-relaxed max-w-2xl">
                                        {{ $category->description_str }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                @foreach($articles as $article)
                                    <x-help.article-card
                                        :article="$article"
                                        :user-roles="$userRoles"
                                    />
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Landing Page - Úvod --}}
            <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
                {{-- Rychlá volba kontaktu --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center bg-white p-8 rounded-[2.5rem] border border-slate-200/60 shadow-xl shadow-slate-200/40 overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -mr-32 -mt-32"></div>
                    <div class="lg:col-span-2 relative z-10">
                        <h3 class="text-3xl font-black text-secondary tracking-tight mb-3">Nenašli jste, co jste hledali?</h3>
                        <p class="text-slate-500 text-lg font-medium">Náš tým je připraven vám pomoci. Kontaktujte svého trenéra nebo klubovou podporu přímo zde.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 relative z-10">
                        <a href="{{ route('member.contact.coach.form') }}" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-primary/30 hover:bg-primary/5 transition-all group text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <i class="fa-light fa-whistle text-lg"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-secondary">{{ __('member.feedback.contact_coach_title') }}</span>
                        </a>
                        <a href="{{ route('member.contact.admin.form') }}" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-primary/30 hover:bg-primary/5 transition-all group text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-primary group-hover:scale-110 transition-transform">
                                <i class="fa-light fa-user-gear text-lg"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-secondary">{{ __('member.feedback.contact_admin_title') }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    @foreach($homeData['categories'] as $category)
                        <x-help.category-card :category="$category" />
                    @endforeach
                </div>

                @if(!empty($homeData['featured_articles']))
                    <div class="pt-16">
                        <h3 class="text-3xl font-black text-secondary mb-10 tracking-tight flex items-center gap-4">
                            <span class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-sm border border-amber-100">
                                <i class="fa-solid fa-star text-sm"></i>
                            </span>
                            Doporučené články
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @foreach($homeData['featured_articles'] as $article)
                                <x-help.article-card
                                    :article="$article"
                                    :user-roles="$userRoles"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
