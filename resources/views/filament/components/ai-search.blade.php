<div x-data="{ searchOpen: false }" class="hidden md:flex items-center gap-3 px-3 py-1.5 bg-gray-50/50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-800 max-w-[280px] transition-all hover:bg-white dark:hover:bg-gray-950 hover:border-primary/30 group cursor-pointer relative mr-4" @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())">
    <div class="flex items-center justify-center w-6 h-6 rounded bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
        <i class="fa-light fa-sparkles text-[10px]"></i>
    </div>

    <span class="text-[11px] text-gray-400 dark:text-gray-500 truncate font-medium group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">
        {{ __('search.ai_hint') }}
    </span>

    <div class="ml-auto text-[9px] font-black text-primary/40 group-hover:text-primary transition-colors">
        AI
    </div>

    <!-- AI Search Overlay -->
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="absolute left-0 top-full mt-3 w-full min-w-[380px] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 p-4 z-50 overflow-hidden text-left ring-1 ring-black/5 dark:ring-white/5"
         style="display: none;">

        <div class="mb-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--color-primary-rgb),0.5)]"></div>
                <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('search.ai_suggestion') }}</h3>
            </div>

            <form action="{{ route('member.search') }}" method="GET" class="relative" @submit.prevent="window.location.href = '{{ route('member.search') }}?q=' + encodeURIComponent($refs.searchInput.value)">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-light fa-sparkles text-sm animate-pulse"></i>
                </div>
                <input type="text"
                       name="q"
                       x-ref="searchInput"
                       placeholder="{{ __('search.ai_search_placeholder') }}"
                       class="w-full bg-gray-50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-xl pl-10 pr-12 py-3 text-sm text-gray-900 dark:text-white focus:border-primary focus:ring-0 outline-none transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center hover:scale-105 transition-transform shadow-md shadow-primary/20">
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </button>
            </form>
        </div>

        <div class="space-y-3">
            <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest flex items-center gap-2">
                <span class="w-4 h-px bg-gray-100 dark:bg-gray-800"></span>
                {{ __('search.ai_try_asking') }}
                <span class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                @php
                    $tips = ['branding', 'cleny', 'omluva', 'zapas'];
                @endphp
                @foreach($tips as $key)
                    <button @click.stop="$refs.searchInput.value = '{{ __('search.ai_tips.' . $key) }}'; $refs.searchInput.form.dispatchEvent(new Event('submit'))"
                            class="flex items-start gap-2 text-left p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/30 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-800 hover:border-primary/20 transition-all group/tip">
                        <div class="w-5 h-5 rounded bg-white dark:bg-gray-800 flex items-center justify-center shadow-sm text-[10px] text-primary group-hover/tip:bg-primary group-hover/tip:text-white transition-colors">
                            <i class="fa-light @if($key === 'branding') fa-palette @elseif($key === 'cleny') fa-user-gear @elseif($key === 'omluva') fa-calendar-xmark @else fa-basketball @endif"></i>
                        </div>
                        <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 leading-tight">
                            @if($key === 'branding') Logo a barvy @endif
                            @if($key === 'cleny') Reset hesla @endif
                            @if($key === 'omluva') Omluva z tréninku @endif
                            @if($key === 'zapas') Další zápas @endif
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                <p class="text-[10px] text-gray-400 dark:text-gray-500 italic leading-relaxed bg-gray-50 dark:bg-gray-800/50 p-2 rounded-lg border border-gray-100 dark:border-gray-800">
                    <i class="fa-light fa-circle-info text-primary mr-1 not-italic"></i>
                    {{ __('search.ai_admin_hint') }}
                </p>
            </div>
        </div>
    </div>
</div>
