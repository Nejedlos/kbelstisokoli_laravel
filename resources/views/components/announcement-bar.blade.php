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
                 class="{{ $bgColor }} text-white py-2 px-4 shadow-md relative overflow-hidden"
            >
                <div class="container mx-auto flex flex-col md:flex-row items-center justify-center gap-2 md:gap-6 text-center md:text-left">
                    <div class="flex items-center gap-3">
                        @if($announcement->title)
                            <span class="inline-block px-2 py-0.5 bg-white/20 rounded text-[10px] font-black uppercase tracking-widest leading-none">
                                {{ $announcement->title }}
                            </span>
                        @endif
                        <p class="text-sm font-bold tracking-tight leading-snug">
                            {{ $announcement->message }}
                        </p>
                    </div>

                    @if($announcement->cta_label && $announcement->cta_url)
                        <a href="{{ $announcement->cta_url }}"
                           class="inline-flex items-center px-3 py-1 bg-white text-dark rounded-full text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-colors shrink-0"
                        >
                            {{ $announcement->cta_label }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif

                    <button @click="open = false"
                            class="absolute right-4 top-1/2 -translate-y-1/2 p-1 hover:bg-white/10 rounded-full transition-colors hidden md:block"
                            title="Zavřít"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
