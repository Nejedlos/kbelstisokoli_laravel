@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'fa-basketball',
    'primaryCta' => null, // ['url' => '...', 'label' => '...']
    'secondaryCta' => null, // ['url' => '...', 'label' => '...']
])

<div {{ $attributes->merge(['class' => 'text-center py-16 px-6 bg-white/60 rounded-club border border-slate-100']) }}>
    @if($icon)
        <div class="mx-auto mb-6 text-slate-300">
            <i class="fa-light {{ $icon }} text-5xl md:text-6xl"></i>
        </div>
    @endif

    <h3 class="text-2xl md:text-3xl font-display font-black uppercase tracking-tighter mb-4 text-secondary">
        {{ $title ?? __('general.empty_state.title') }}
    </h3>

    @if($subtitle || __('general.empty_state.subtitle'))
        <p class="text-slate-500 max-w-xl mx-auto mb-10 leading-relaxed text-balance">
            {{ $subtitle ?? __('general.empty_state.subtitle') }}
        </p>
    @endif

    @if($primaryCta || $secondaryCta)
        <div class="flex flex-wrap items-center justify-center gap-4 mt-8">
            @if($primaryCta)
                <a href="{{ $primaryCta['url'] ?? '#' }}" class="btn btn-primary px-8">
                    {{ $primaryCta['label'] ?? __('general.empty_state.add') }}
                </a>
            @endif

            @if($secondaryCta)
                <a href="{{ $secondaryCta['url'] ?? '#' }}" class="btn btn-outline px-8">
                    {{ $secondaryCta['label'] ?? '' }}
                </a>
            @endif
        </div>
    @endif
</div>
