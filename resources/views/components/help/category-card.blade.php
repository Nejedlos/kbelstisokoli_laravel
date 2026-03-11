@props(['category'])

<a href="{{ \App\Filament\Pages\Help::getUrl(['cat' => $category->slug]) }}"
   class="group relative bg-white rounded-[2rem] border border-slate-100 p-10 hover:shadow-2xl hover:border-primary-100 hover:-translate-y-1 transition-all duration-500 overflow-hidden flex flex-col h-full">

    {{-- Card Accent Decor --}}
    <div @class([
        'absolute top-0 right-0 w-32 h-32 -mr-8 -mt-8 rounded-full blur-3xl opacity-20 transition-opacity group-hover:opacity-40',
        'bg-orange-500' => $category->color === 'orange',
        'bg-blue-500' => $category->color === 'blue',
        'bg-green-500' => $category->color === 'green',
        'bg-purple-500' => $category->color === 'purple',
        'bg-red-500' => $category->color === 'red',
        'bg-slate-500' => $category->color === 'slate',
    ])></div>

    <div @class([
        'w-20 h-20 rounded-2xl flex items-center justify-center text-3xl mb-8 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-sm',
        'bg-orange-50 text-orange-600' => $category->color === 'orange',
        'bg-blue-50 text-blue-600' => $category->color === 'blue',
        'bg-green-50 text-green-600' => $category->color === 'green',
        'bg-purple-50 text-purple-600' => $category->color === 'purple',
        'bg-red-50 text-red-600' => $category->color === 'red',
        'bg-slate-50 text-slate-600' => $category->color === 'slate',
    ])>
        <i class="fa-light {{ $category->icon }}"></i>
    </div>

    <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight group-hover:text-primary-600 transition-colors">{{ $category->getTranslation('name', app()->getLocale(), false) }}</h3>

    <p class="text-slate-500 leading-relaxed mb-8 font-medium">
        {{ $category->getTranslation('description', app()->getLocale(), false) }}
    </p>

    <div class="flex items-center justify-between mt-auto">
        <span class="text-xs font-black uppercase tracking-widest text-slate-400 group-hover:text-primary-600 transition-colors">
            {{ trans_choice('admin.navigation.pages.help_articles_count', $category->articles_count ?? 0) }}
        </span>
        <div @class([
            'w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500',
            'bg-slate-50 text-slate-300 group-hover:bg-primary-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-primary-600/30'
        ])>
            <i class="fa-light fa-arrow-right"></i>
        </div>
    </div>
</a>
