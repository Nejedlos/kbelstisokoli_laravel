@props(['announcements' => []])

@if(count($announcements) > 0)
    <div class="announcement-wrapper relative z-50">
        @foreach($announcements as $announcement)
            @php
                $bgColor = match($announcement->style_variant) {
                    'urgent' => 'bg-red-600',
                    'warning' => 'bg-amber-500',
                    'success' => 'bg-emerald-600',
                    default => 'bg-blue-600',
                };
            @endphp
            <div x-data="{ open: true }"
                 x-show="open"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-full"
                 class="{{ $bgColor }} text-white py-3 px-4 shadow-xl relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-black/5 pointer-events-none"></div>
                <div class="container mx-auto flex flex-col md:flex-row items-center justify-center gap-3 md:gap-8 text-center md:text-left relative z-10">
                    <div class="flex items-center gap-4">
                        @if($announcement->title)
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-sm rounded-lg text-[9px] font-black uppercase tracking-[0.2em] leading-none border border-white/10 shadow-sm">
                                {{ $announcement->title }}
                            </span>
                        @endif
                        <p class="text-[13px] font-bold tracking-tight leading-snug">
                            {{ $announcement->message }}
                        </p>
                    </div>

                    @if($announcement->cta_label && $announcement->cta_url)
                        <a href="{{ $announcement->cta_url }}"
                           class="inline-flex items-center px-5 py-1.5 bg-white text-secondary rounded-xl text-[10px] font-black uppercase tracking-widest hover:shadow-lg hover:scale-105 transition-all shrink-0 shadow-sm"
                        >
                            {{ $announcement->cta_label }}
                            <i class="fa-light fa-chevron-right ml-2 text-[8px]"></i>
                        </a>
                    @endif

                    <button @click="open = false"
                            class="absolute right-0 top-1/2 -translate-y-1/2 p-2 hover:bg-white/10 rounded-xl transition-all hidden md:flex items-center justify-center"
                            title="Zavřít"
                    >
                        <i class="fa-light fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
