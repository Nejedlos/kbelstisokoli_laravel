<div x-data="{ searchOpen: false }" class="hidden md:flex items-center gap-3 px-4 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 max-w-sm lg:max-w-md w-full transition-all hover:bg-gray-200 dark:hover:bg-gray-700 group cursor-pointer relative mr-4" @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())">
    <i class="fa-light fa-magnifying-glass text-primary group-hover:scale-110 transition-transform"></i>
    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('search.ai_hint') }}</span>
    <div class="ml-auto flex items-center gap-1 bg-primary/10 text-primary px-1.5 py-0.5 rounded text-[10px] font-bold border border-primary/20">
        <i class="fa-light fa-sparkles text-[8px]"></i> AI
    </div>

    <!-- AI Search Overlay -->
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="absolute left-0 top-full mt-2 w-full min-w-[320px] bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-800 p-3 z-50 overflow-hidden text-left"
         style="display: none;">
        <form action="{{ route('member.search') }}" method="GET" class="relative" @submit.prevent="window.location.href = '{{ route('member.search') }}?q=' + encodeURIComponent($refs.searchInput.value)">
            <input type="text"
                   name="q"
                   x-ref="searchInput"
                   placeholder="{{ __('search.ai_search_placeholder') }}"
                   class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 text-sm text-gray-900 dark:text-white focus:border-primary focus:ring-0 outline-none pr-10">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                <i class="fa-light fa-arrow-right text-base"></i>
            </button>
        </form>
        <div class="mt-3 px-1">
            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest flex items-center gap-2 mb-2">
                <i class="fa-light fa-sparkles text-primary"></i> {{ __('search.ai_try_asking') }}
            </div>
            <div class="flex flex-wrap gap-1.5">
                @php
                    $tips = ['branding', 'cleny', 'omluva', 'zapas'];
                @endphp
                @foreach($tips as $key)
                    <button @click.stop="$refs.searchInput.value = '{{ __('search.ai_tips.' . $key) }}'; $refs.searchInput.form.dispatchEvent(new Event('submit'))"
                            class="text-[10px] bg-gray-100 dark:bg-gray-800 hover:bg-primary/10 hover:text-primary px-2 py-1 rounded-md transition-colors border border-gray-200 dark:border-gray-700 whitespace-nowrap text-gray-700 dark:text-gray-300 font-medium">
                        {{-- Zjednodušený label pro adminy --}}
                        @if($key === 'branding') Logo a barvy @endif
                        @if($key === 'cleny') Reset hesla uživateli @endif
                        @if($key === 'omluva') Omluva z tréninku @endif
                        @if($key === 'zapas') Další zápas @endif
                    </button>
                @endforeach
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px] text-gray-400 italic leading-tight">
                {{ __('search.ai_admin_hint') }}
            </div>
        </div>
    </div>
</div>
