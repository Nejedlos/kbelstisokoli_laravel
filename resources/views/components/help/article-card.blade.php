@props(['article', 'query' => null, 'userRoles' => []])

@php
    $isMatchingRole = !empty($article->audience_roles) && !empty(array_intersect($userRoles, $article->audience_roles));
    $isFeatured = $article->is_featured;
@endphp

<a href="{{ \App\Filament\Pages\Help::getUrl(['file' => $article->slug, 'q' => $query]) }}"
   @class([
       'group p-6 rounded-2xl border transition-all text-left flex items-center justify-between relative overflow-hidden',
       'bg-white border-slate-100 hover:border-primary-200 hover:shadow-xl hover:-translate-y-0.5' => !$isMatchingRole && !$isFeatured,
       'bg-primary-50/30 border-primary-100 hover:border-primary-300 hover:shadow-2xl hover:-translate-y-1' => $isMatchingRole,
       'bg-amber-50/30 border-amber-100 hover:border-amber-300 hover:shadow-2xl hover:-translate-y-1' => $isFeatured && !$isMatchingRole,
   ])>
    @if($isMatchingRole)
        <div class="absolute top-0 right-0">
            <div class="bg-primary-500 text-white text-[8px] font-black uppercase tracking-tighter px-3 py-1 rounded-bl-xl shadow-sm animate-pulse">
                {{ __('admin.help.for_you') }}
            </div>
        </div>
    @elseif($isFeatured)
        <div class="absolute top-0 right-0">
            <div class="bg-amber-500 text-white text-[8px] font-black uppercase tracking-tighter px-3 py-1 rounded-bl-xl shadow-sm">
                {{ __('admin.help.featured') }}
            </div>
        </div>
    @endif

    <div class="flex items-center gap-6">
        <div @class([
            'w-12 h-12 rounded-xl flex items-center justify-center transition-colors shrink-0 shadow-sm border',
            'bg-white border-slate-100 group-hover:bg-primary-50 group-hover:border-primary-100' => !$isMatchingRole,
            'bg-primary-100 border-primary-200 text-primary-600' => $isMatchingRole,
        ])>
            <i @class([
                'fa-light fa-file-lines transition-colors',
                'text-slate-400 group-hover:text-primary-600' => !$isMatchingRole,
                'text-primary-700' => $isMatchingRole,
            ])></i>
        </div>
        <div>
            <span class="block text-lg font-bold text-slate-700 group-hover:text-slate-900 transition-colors mb-0.5">
                {{ $article->title_str ?? (is_array($article->title ?? null) ? ($article->title[app()->getLocale()] ?? $article->title['cs'] ?? 'Untitled') : ($article->title ?? 'Untitled')) }}
            </span>
            @php
                $excerptStr = $article->excerpt_str ?? ($article->excerpt ?? '');
            @endphp
            @if(isset($article->search_excerpt))
                <div class="text-sm text-slate-500 font-medium leading-relaxed mt-1 search-excerpt">
                    {!! $article->search_excerpt !!}
                </div>
            @elseif($excerptStr)
                <p class="text-sm text-slate-500 line-clamp-1 font-medium">{{ $excerptStr }}</p>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-4 shrink-0">
        @if(!empty($article->audience_roles))
            <div class="hidden sm:flex gap-1">
                @foreach(array_slice($article->audience_roles, 0, 2) as $role)
                    <x-help.audience-badge :role="$role" :is-matching-role="$isMatchingRole" />
                @endforeach
            </div>
        @endif
        <i class="fa-light fa-chevron-right text-slate-300 group-hover:text-primary-600 group-hover:translate-x-1 transition-all"></i>
    </div>
</a>
