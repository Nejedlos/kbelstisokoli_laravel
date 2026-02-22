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
            <x-empty-state title="Žádná data" subtitle="Karty budou doplněny později." />
        @else
            <div @class([
                'grid gap-8',
                'grid-cols-1 md:grid-cols-2 lg:grid-cols-' . $columns,
            ])>
                @foreach($cards as $card)
                    <div class="card card-hover group p-8 flex flex-col h-full bg-white border border-slate-100">
                        <div class="mb-6 flex items-center justify-between">
                            @if($card['icon'] ?? null)
                                <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-primary border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                                    {{-- Pokud je to cesta k souboru z FileUpload --}}
                                    @if(str_contains($card['icon'], '/'))
                                        <img src="{{ asset('storage/' . $card['icon']) }}" class="w-10 h-10 object-contain" alt="">
                                    @else
                                        {{-- Pokud je to název FontAwesome ikony --}}
                                        <i class="fa-light fa-{{ $card['icon'] }} text-3xl"></i>
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

                        @if($card['link'] ?? null)
                            <div class="mt-auto">
                                <a href="{{ $card['link'] }}" class="inline-flex items-center font-black uppercase tracking-widest text-[10px] text-slate-400 group-hover:text-primary transition-colors">
                                    <span>Více informací</span>
                                    <div class="ml-2 w-4 h-px bg-slate-200 group-hover:bg-primary transition-all group-hover:w-8"></div>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
