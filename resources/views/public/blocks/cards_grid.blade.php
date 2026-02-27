@php
    $cards = $data['cards'] ?? [];
    $columns = $data['columns'] ?? 3;
@endphp

<section class="block-cards-grid section-padding bg-bg relative overflow-hidden">
    {{-- Subtle background decor --}}
    <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-slate-100 to-transparent opacity-50"></div>

    <div class="container relative z-10">
        @if($data['title'] ?? null)
            <x-section-heading :title="$data['title']" :subtitle="($data['subtitle'] ?? null)" align="center" />
        @endif

        @if(empty($cards))
            <x-empty-state :title="__('general.blocks.no_data')" :subtitle="__('general.blocks.cards_placeholder')" />
        @else
            <div @class([
                'grid gap-8',
                'grid-cols-1 md:grid-cols-2 lg:grid-cols-' . $columns,
                'max-w-4xl mx-auto' => $columns == 2,
            ])>
                @foreach($cards as $card)
                    <div class="card card-hover group flex flex-col h-full bg-white border border-slate-100 overflow-hidden">
                        @if($card['image_url'] ?? null)
                            <div class="relative h-64 overflow-hidden">
                                <x-picture
                                    :src="$card['image_url']"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    :alt="$card['title'] ?? ''"
                                    loading="lazy"
                                    decoding="async"
                                    width="1600"
                                    height="900"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-secondary/60 to-transparent opacity-40"></div>
                                @if($card['badge'] ?? null)
                                    <div class="absolute top-4 left-4 bg-primary text-white text-[min(3.2vw,10px)] sm:text-[10px] font-black uppercase tracking-widest-responsive px-2.5 sm:px-3 py-1 rounded badge-nowrap max-w-[calc(100%-2rem)] overflow-hidden">
                                        {{ $card['badge'] }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="p-6 md:p-8 flex flex-col h-full flex-grow">
                            <div class="mb-6 flex items-center justify-between">
                                @if($card['icon'] ?? null)
                                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-primary border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                                        {{-- Pokud je to cesta k souboru z FileUpload --}}
                                        @if(str_contains($card['icon'], '/'))
                                            <img src="{{ web_asset($card['icon']) }}" class="w-8 h-8 md:w-10 md:h-10 object-contain" alt="" loading="lazy" decoding="async" width="40" height="40">
                                        @else
                                            {{-- Pokud je to název FontAwesome ikony --}}
                                            <i class="fa-light fa-{{ $card['icon'] }} text-2xl md:text-3xl"></i>
                                        @endif
                                    </div>
                                @endif
                                <div class="text-primary opacity-0 group-hover:opacity-100 transition-opacity translate-x-4 group-hover:translate-x-0 transition-transform duration-300">
                                    <i class="fa-light fa-arrow-right-long text-xl"></i>
                                </div>
                            </div>

                            <h3 class="text-2xl font-black uppercase tracking-tight text-secondary mb-4 group-hover:text-primary transition-colors">
                                {{ $card['title'] ?? '' }}
                            </h3>

                            <p class="text-slate-500 leading-relaxed mb-8 flex-grow">
                                {{ $card['description'] ?? '' }}
                            </p>

                            @if(($card['link'] ?? null) || ($card['secondary_link'] ?? null))
                                @php
                                    $isExternal = isset($card['link']) && str_contains($card['link'], 'basketkbely.cz');
                                @endphp
                                <div class="mt-auto flex flex-wrap gap-4 items-center">
                                    @if($card['link'] ?? null)
                                        @if($isExternal)
                                            <a href="{{ $card['link'] }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm w-full sm:w-auto px-6 py-2.5">
                                                <span>{{ $card['link_label'] ?? 'Více informací' }}</span>
                                                <i class="fa-light fa-arrow-up-right ml-2 text-[10px]"></i>
                                            </a>
                                        @else
                                            <a href="{{ $card['link'] }}" class="inline-flex items-center font-black uppercase tracking-widest-responsive text-xs sm:text-[10px] text-slate-400 group-hover:text-primary transition-colors py-1 underline decoration-slate-300 underline-offset-4 group-hover:decoration-primary" data-track-click="cards_grid_primary" data-track-label="{{ $card['title'] ?? '' }}">
                                                <span>{{ $card['link_label'] ?? 'Více informací' }}</span>
                                                <div class="ml-2 w-4 h-px bg-slate-200 group-hover:bg-primary transition-all group-hover:w-8 hidden xs:block"></div>
                                            </a>
                                        @endif
                                    @endif

                                    @if($card['secondary_link'] ?? null)
                                        <a href="{{ $card['secondary_link'] }}" class="text-xs sm:text-[10px] font-bold uppercase tracking-widest-responsive text-slate-400 hover:text-secondary transition-colors underline decoration-slate-200 underline-offset-4 py-1 ml-auto sm:ml-0">
                                            {{ $card['secondary_link_label'] ?? 'Program' }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
