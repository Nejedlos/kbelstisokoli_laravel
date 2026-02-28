<div x-data="{ searchOpen: false }" class="relative flex items-center">
    <!-- Desktop Trigger (Input-like) -->
    <div class="hidden lg:block relative group min-w-[280px] mr-1">
        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 group-hover:text-primary transition-colors">
            <i class="fa-light fa-magnifying-glass text-[13px]"></i>
        </div>
        <input type="text"
               readonly
               @click="searchOpen = true"
               placeholder="{{ __('Search') }}..."
               class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg pl-9 pr-4 py-1.5 text-[12px] text-gray-600 dark:text-gray-300 placeholder:text-gray-500 cursor-pointer hover:bg-white dark:hover:bg-gray-700 hover:border-primary/30 transition-all shadow-sm">
    </div>

    <!-- Mobile Trigger (Icon) -->
    <button @click="searchOpen = !searchOpen"
            class="lg:hidden p-2 text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors focus:outline-none relative group"
            title="{{ __('Search') }}">
        <i class="fa-light fa-magnifying-glass text-xl group-hover:scale-110 transition-transform"></i>
    </button>

    <!-- Search Dropdown/Overlay -->
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         @keydown.escape.window="searchOpen = false"
         x-init="$watch('searchOpen', value => { if (value) { $nextTick(() => { $el.querySelector('input[type=search]')?.focus() }) } })"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="ks-search-overlay fixed inset-x-0 top-16 w-screen lg:absolute lg:inset-auto lg:right-0 lg:top-full lg:mt-3 lg:w-full lg:min-w-[450px] bg-white dark:bg-gray-900 rounded-none lg:rounded-2xl shadow-2xl border-t lg:border border-gray-100 dark:border-gray-800 p-2 z-50 overflow-hidden"
         style="display: none;">

        <div class="p-2 filament-standard-search-container">
            @livewire(\Filament\Livewire\GlobalSearch::class)
        </div>
    </div>
</div>

<style>
    /* Stylování vnořeného globálního vyhledávání, aby zapadlo do našeho dropdownu */
    .filament-standard-search-container .fi-global-search {
        display: block !important;
        width: 100% !important;
    }
    .filament-standard-search-container .fi-global-search-container {
        width: 100% !important;
    }
    .filament-standard-search-container .fi-global-search-field {
        margin-bottom: 0 !important;
    }
    /* Skrytí původního vyhledávání v topbaru (v layoutu), aby nezůstalo zdvojené */
    /* Využíváme faktu, že naše vyhledávání je uvnitř .filament-standard-search-container */
    .fi-topbar .fi-global-search:not(.filament-standard-search-container .fi-global-search) {
        display: none !important;
    }
</style>
