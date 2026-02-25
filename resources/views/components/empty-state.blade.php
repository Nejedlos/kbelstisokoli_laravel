@props(['title' => null, 'subtitle' => null, 'icon' => null, 'cta' => null])

<div class="container text-center py-16 bg-white/60 rounded-club border border-slate-100">
    @if($icon)
        <div class="mx-auto mb-4">
            <img src="{{ $icon }}" alt="" class="h-10 w-10 mx-auto opacity-60" />
        </div>
    @endif
    <h3 class="text-xl font-bold mb-2">{{ $title ?? __('general.empty_state.title') }}</h3>
    @if($subtitle || __('general.empty_state.subtitle'))
        <p class="text-slate-500 mb-4">{{ $subtitle ?? __('general.empty_state.subtitle') }}</p>
    @endif
    @if($cta)
        <a href="{{ $cta['url'] ?? '#' }}" class="btn btn-primary">{{ $cta['label'] ?? __('general.empty_state.add') }}</a>
    @endif
</div>
