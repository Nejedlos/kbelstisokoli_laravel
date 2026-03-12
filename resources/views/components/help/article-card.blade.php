@props(['article', 'query' => null, 'userRoles' => []])

@php
    $isMatchingRole = !empty($article->audience_roles) && !empty(array_intersect($userRoles, $article->audience_roles));
    $isFeatured = $article->is_featured;
@endphp

<a href="{{ \App\Support\HelpUrlHelper::getUrl(['file' => $article->slug, 'q' => $query]) }}"
   @class([
       'group p-6 rounded-2xl border transition-all text-left flex items-start justify-between relative overflow-hidden focus-visible:ring-4 focus-visible:ring-primary/20 focus-visible:outline-none',
       'bg-white border-slate-200/60 hover:border-primary/20 hover:shadow-xl hover:-translate-y-0.5' => !$isMatchingRole && !$isFeatured,
       'bg-primary/5 border-primary/10 hover:border-primary/30 hover:shadow-2xl hover:-translate-y-1' => $isMatchingRole,
       'bg-amber-50/30 border-amber-100 hover:border-amber-300 hover:shadow-2xl hover:-translate-y-1' => $isFeatured && !$isMatchingRole,
   ])>
    @if($isMatchingRole)
        <div class="absolute top-0 right-0">
            <div class="bg-primary text-white text-[9px] font-black uppercase tracking-tighter px-3 py-1 rounded-bl-xl shadow-sm animate-pulse">
                {{ __('admin.help.for_you') }}
            </div>
        </div>
    @elseif($isFeatured)
        <div class="absolute top-0 right-0">
            <div class="bg-amber-500 text-white text-[9px] font-black uppercase tracking-tighter px-3 py-1 rounded-bl-xl shadow-sm">
                {{ __('admin.help.featured') }}
            </div>
        </div>
    @endif

    <div class="flex items-start gap-6 flex-1 min-w-0">
        <div @class([
            'w-12 h-12 rounded-xl flex items-center justify-center transition-colors shrink-0 shadow-sm border mt-1',
            'bg-slate-50 border-slate-100 group-hover:bg-primary/10 group-hover:border-primary/20' => !$isMatchingRole,
            'bg-primary/20 border-primary/30 text-primary' => $isMatchingRole,
        ])>
            <i @class([
                'fa-light fa-file-lines transition-colors',
                'text-slate-400 group-hover:text-primary' => !$isMatchingRole,
                'text-primary' => $isMatchingRole,
            ])></i>
        </div>
        <div class="flex-1 min-w-0">
            <span class="block text-lg font-bold text-secondary group-hover:text-primary transition-colors mb-0.5 truncate sm:whitespace-normal">
                {{ $article->title_str ?? (is_array($article->title ?? null) ? ($article->title[app()->getLocale()] ?? $article->title['cs'] ?? 'Untitled') : ($article->title ?? 'Untitled')) }}
            </span>
            @php
                $excerptStr = $article->excerpt_str ?? ($article->excerpt ?? '');
            @endphp
            @if(isset($article->search_excerpt))
                <div class="text-sm text-slate-500 font-medium leading-relaxed mt-1 search-excerpt line-clamp-2">
                    {!! $article->search_excerpt !!}
                </div>
            @elseif($excerptStr)
                <p class="text-sm text-slate-500 line-clamp-1 font-medium">{{ $excerptStr }}</p>
            @endif

            @if(!empty($article->audience_roles))
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach($article->audience_roles as $role)
                        <x-help.audience-badge :role="$role" :is-matching-role="$isMatchingRole" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-4 shrink-0 self-center ml-4">
        <i class="fa-light fa-chevron-right text-slate-300 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
    </div>
</a>
