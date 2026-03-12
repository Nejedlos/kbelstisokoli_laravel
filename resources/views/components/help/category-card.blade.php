@props(['category'])

<a href="{{ \App\Support\HelpUrlHelper::getUrl(['cat' => $category->slug]) }}"
   class="group relative bg-white rounded-[2rem] border border-slate-200/60 p-10 hover:shadow-2xl hover:border-primary/20 hover:-translate-y-1 focus-visible:ring-4 focus-visible:ring-primary/50 focus-visible:outline-none transition-all duration-500 overflow-hidden flex flex-col h-full text-left">

    {{-- Card Accent Decor --}}
    <div @class([
        'absolute top-0 right-0 w-32 h-32 -mr-8 -mt-8 rounded-full blur-3xl opacity-20 transition-opacity group-hover:opacity-40',
        'bg-orange-500' => ($category->color ?? 'slate') === 'orange',
        'bg-sky-500' => in_array($category->color ?? 'slate', ['blue', 'sky']),
        'bg-emerald-500' => in_array($category->color ?? 'slate', ['emerald', 'green']),
        'bg-teal-500' => ($category->color ?? 'slate') === 'teal',
        'bg-amber-500' => in_array($category->color ?? 'slate', ['amber', 'yellow', 'orange-yellow']),
        'bg-purple-500' => ($category->color ?? 'slate') === 'purple',
        'bg-red-500' => ($category->color ?? 'slate') === 'red',
        'bg-slate-500' => ($category->color ?? 'slate') === 'slate' || empty($category->color),
    ])></div>

    <div @class([
        'relative z-10 w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-8 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-sm border border-white/50',
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

    <h3 class="text-3xl font-black text-secondary mb-3 tracking-tight group-hover:text-primary transition-colors">
        {{ $category->name_str ?? (method_exists($category, 'getTranslation') ? $category->getTranslation('name', app()->getLocale(), false) : 'Untitled') }}
    </h3>

    <p class="text-slate-600 leading-relaxed mb-8 text-base font-medium">
        {{ $category->description_str ?? (method_exists($category, 'getTranslation') ? $category->description : '') }}
    </p>

    <div class="flex items-center justify-between mt-auto pt-4">
        <span class="text-xs font-black uppercase tracking-widest text-slate-500 group-hover:text-primary-700 transition-colors">
            {{ trans_choice('admin.navigation.pages.help_articles_count', $category->articles_count ?? 0) }}
        </span>
        <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500 bg-slate-100 text-slate-400 group-hover:bg-primary-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-primary-600/30">
            <i class="fa-light fa-arrow-right"></i>
        </div>
    </div>
</a>
