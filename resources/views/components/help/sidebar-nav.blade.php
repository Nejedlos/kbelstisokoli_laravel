@props(['tree', 'currentCategory' => null, 'currentArticle' => null])

<nav class="bg-white rounded-3xl border border-slate-100 p-4 shadow-sm space-y-2 overflow-hidden sticky top-24">
    <div class="px-4 py-2 mb-4 border-b border-slate-50">
        <h4 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Kategorie</h4>
    </div>

    @foreach($tree as $category)
        <div class="space-y-1">
            <a
                href="{{ \App\Filament\Pages\Help::getUrl(['cat' => $category->slug]) }}"
                @class([
                    'w-full text-left px-4 py-3 rounded-xl transition-all flex items-center justify-between group/cat',
                    'bg-primary-50 text-primary-600 font-bold' => $currentCategory && $currentCategory->id === $category->id,
                    'text-slate-500 hover:bg-slate-50 hover:text-slate-900' => !$currentCategory || $currentCategory->id !== $category->id,
                ])
            >
                <div class="flex items-center gap-3">
                    <i class="fa-light {{ $category->icon ?? 'fa-folder' }} text-sm"></i>
                    <span class="text-sm tracking-tight">{{ $category->name_str ?? (method_exists($category, 'getTranslation') ? $category->getTranslation('name', app()->getLocale(), false) : 'Untitled') }}</span>
                </div>
                @if(($category->children_count ?? 0) > 0 || ($category->articles_count ?? 0) > 0 || (isset($category->articles) && count($category->articles) > 0))
                    <i @class([
                        'fa-light fa-chevron-right text-[10px] transition-transform',
                        'rotate-90' => $currentCategory && $currentCategory->id === $category->id,
                    ])></i>
                @endif
            </a>

            @php
                $isSameCat = $currentCategory && $currentCategory->id === $category->id;
                $isParentOfCurrent = $currentCategory && $currentCategory->parent_id === $category->id;
                $showChildren = $isSameCat || $isParentOfCurrent;

                // Načteme články pro tuto kategorii, pokud je aktivní (Lazy loading nebo z eager loadu)
                $articles = $showChildren ? $category->articles : collect();
            @endphp

            @if($showChildren)
                <div class="ml-4 pl-4 border-l border-slate-100 space-y-1 py-1 animate-in fade-in slide-in-from-left-2 duration-300">
                    @foreach($articles as $article)
                        <a
                            href="{{ \App\Filament\Pages\Help::getUrl(['file' => $article->slug]) }}"
                            @class([
                                'block w-full text-left px-4 py-2 rounded-lg text-sm transition-all',
                                'bg-primary-600 text-white font-bold shadow-lg shadow-primary-600/20 scale-[1.02]' => $currentArticle && $currentArticle->id === $article->id,
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => !$currentArticle || $currentArticle->id !== $article->id,
                            ])
                        >
                            {{ $article->title_str ?? (method_exists($article, 'getTranslation') ? $article->getTranslation('title', app()->getLocale(), false) : 'Untitled') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</nav>
