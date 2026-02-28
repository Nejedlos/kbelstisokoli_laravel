@if(auth()->user()?->can('impersonate_users'))
<div x-data="{
    searchOpen: false,
    query: '',
    results: [],
    loading: false,
    search() {
        this.loading = true;
        fetch('{{ route('admin.impersonate.search') }}?q=' + encodeURIComponent(this.query))
            .then(response => response.json())
            .then(data => {
                this.results = data.results;
                this.loading = false;
            })
            .catch(() => {
                this.loading = false;
            });
    },
    impersonate(userId, userName) {
        if (confirm('{{ __('permissions.impersonate_confirm') }}' + userName + '?')) {
            let url = '{{ route('admin.impersonate.start', ['userId' => 'USER_ID']) }}';
            window.location.href = url.replace('USER_ID', userId);
        }
    }
}" class="relative">
    <!-- Trigger Button - Matching Filament Style -->
    <button @click="searchOpen = !searchOpen"
            type="button"
            class="fi-dropdown-list-item flex w-full items-center gap-2 p-2 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg group"
            title="{{ __('permissions.impersonate_users') }}">
        <div class="flex items-center justify-center w-5 h-5 text-gray-500 group-hover:text-primary transition-colors">
            <i class="fa-light fa-user-secret text-base"></i>
        </div>
        <span class="fi-dropdown-list-item-label flex-1 text-left text-gray-700 dark:text-gray-200 group-hover:text-primary transition-colors">
            {{ __('permissions.impersonate_users') }}
        </span>
        @if(session()->has('impersonated_by'))
            <span class="w-2 h-2 bg-amber-500 rounded-full border border-white dark:border-gray-900 animate-pulse mr-1"></span>
        @endif
        <i class="fa-light fa-chevron-down text-[10px] text-gray-400 group-hover:text-primary transition-transform" :class="{ 'rotate-180': searchOpen }"></i>
    </button>

    <!-- Dropdown Overlay -->
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         @keydown.escape.window="searchOpen = false"
         x-init="search(); $watch('searchOpen', value => { if (value) { $nextTick(() => { $refs.impersonateInput.focus() }) } })"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-800 p-2 z-[100] overflow-hidden ring-1 ring-black/5"
         style="display: none;">

        <div class="mb-3">
            <div class="flex items-center gap-2 mb-2 px-1">
                <i class="fa-light fa-users-viewfinder text-primary text-sm"></i>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-900 dark:text-gray-100">{{ __('permissions.impersonate_users') }}</h3>
            </div>
            <div class="relative">
                <input type="text"
                       x-ref="impersonateInput"
                       x-model="query"
                       @input.debounce.300ms="search()"
                       placeholder="{{ __('Search') }}..."
                       class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl pl-9 pr-4 py-2 text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <i x-show="!loading" class="fa-light fa-magnifying-glass text-xs"></i>
                    <i x-show="loading" class="fa-light fa-spinner-third fa-spin text-xs" style="display: none;"></i>
                </div>
            </div>
        </div>

        <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1">
            <template x-for="user in results" :key="user.id">
                <button @click="impersonate(user.id, user.text)"
                        class="w-full text-left px-3 py-2 rounded-xl hover:bg-primary/5 hover:text-primary transition-all flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-[10px] font-black group-hover:bg-primary/10 transition-colors">
                        <i class="fa-light fa-user"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold truncate" x-text="user.text"></p>
                    </div>
                    <i class="fa-light fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </button>
            </template>

            <div x-show="results.length === 0 && !loading"
                 class="px-3 py-6 text-center text-gray-400 italic text-xs">
                {{ __('No results found') }}
            </div>
        </div>

        @if(session()->has('impersonated_by'))
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('admin.impersonate.stop') }}"
                   class="w-full flex items-center justify-center gap-2 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-bold transition-all">
                    <i class="fa-light fa-arrow-rotate-left"></i>
                    {{ __('permissions.stop_impersonation') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif
