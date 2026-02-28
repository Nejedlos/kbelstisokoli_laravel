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
}" class="relative flex items-center">

    <!-- Trigger Button -->
    <button @click="searchOpen = !searchOpen; if(searchOpen) { search(); $nextTick(() => $refs.impersonateInput.focus()) }"
            type="button"
            class="flex items-center xl:gap-1.5 p-2 xl:px-2 xl:py-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all group {{ session()->has('impersonated_by') ? 'bg-red-50 dark:bg-red-900/10 text-red-600' : '' }}"
            title="{{ __('permissions.impersonate_users') }}">
        <div class="relative">
            <i class="fa-light fa-user-secret text-xl sm:text-base"></i>
            @if(session()->has('impersonated_by'))
                <span class="absolute -top-1 -right-1 flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
            @endif
        </div>
        <span class="hidden xl:inline text-[10px] font-bold uppercase tracking-wider">{{ __('permissions.impersonate_users') }}</span>
    </button>

    <!-- Dropdown Overlay -->
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         @keydown.escape.window="searchOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="absolute right-0 top-full mt-2 w-64 bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-800 p-3 z-[100] overflow-hidden text-left"
         style="display: none;">

        <div class="mb-3">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('permissions.impersonate_users') }}</h3>
            </div>
            <div class="relative">
                <input type="text"
                       x-ref="impersonateInput"
                       x-model="query"
                       @input.debounce.300ms="search()"
                       placeholder="{{ __('Search') }}..."
                       class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-lg pl-8 pr-3 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-red-500/40 outline-none transition-all">
                <div class="absolute inset-y-0 left-2.5 flex items-center pointer-events-none text-gray-400">
                    <i x-show="!loading" class="fa-light fa-magnifying-glass text-[10px]"></i>
                    <i x-show="loading" class="fa-light fa-spinner-third fa-spin text-[10px]" style="display: none;"></i>
                </div>
            </div>
        </div>

        <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-0.5">
            <template x-for="user in results" :key="user.id">
                <button @click="impersonate(user.id, user.text)"
                        class="w-full text-left px-2 py-1.5 rounded-lg hover:bg-red-50 hover:text-red-600 transition-all flex items-center gap-2 group">
                    <div class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-[8px] font-black group-hover:bg-red-100/50 transition-colors text-gray-400 group-hover:text-red-600">
                        <i class="fa-light fa-user"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-bold truncate" x-text="user.text"></p>
                    </div>
                    <div class="w-6 h-6 rounded-lg bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-300 dark:text-gray-600 group-hover:bg-red-600 group-hover:text-white transition-all shrink-0">
                        <i class="fa-light fa-chevron-right text-[8px]"></i>
                    </div>
                </button>
            </template>

            <div x-show="results.length === 0 && !loading"
                 class="px-2 py-4 text-center text-gray-400 italic text-[10px]">
                {{ __('No results found') }}
            </div>
        </div>

        @if(session()->has('impersonated_by'))
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('admin.impersonate.stop') }}"
                   class="w-full flex items-center justify-center gap-2 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-all shadow-md shadow-red-500/20">
                    <i class="fa-light fa-arrow-rotate-left"></i>
                    {{ __('permissions.stop_impersonation') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif
