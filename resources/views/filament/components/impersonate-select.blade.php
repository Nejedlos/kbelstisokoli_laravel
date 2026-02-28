@if(auth()->user()?->can('impersonate_users'))
<div x-data="{
    searchOpen: false,
    query: '',
    results: [],
    loading: false,
    confirmModal: false,
    targetUser: { id: null, name: '' },
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
        this.targetUser = { id: userId, name: userName };
        this.confirmModal = true;
    },
    confirmImpersonate() {
        let url = '{{ route('admin.impersonate.start', ['userId' => 'USER_ID']) }}';
        window.location.href = url.replace('USER_ID', this.targetUser.id);
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

    <!-- Confirm Modal -->
    <template x-teleport="body">
        <div x-show="confirmModal"
             class="fixed inset-0 z-[100000] flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto"
             x-cloak>
            <!-- Backdrop -->
            <div x-show="confirmModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="confirmModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Content -->
            <div x-show="confirmModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                 class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl overflow-hidden border border-white/20">

                <!-- Basketball Decoration -->
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-red-600/5 rounded-full flex items-center justify-center rotate-12">
                    <i class="fa-light fa-basketball text-[8rem] text-red-600/10"></i>
                </div>

                <div class="relative p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600">
                            <i class="fa-light fa-user-magnifying-glass text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black uppercase tracking-tight text-gray-900 dark:text-white leading-none mb-1">
                                {{ __('permissions.impersonation_switch_title') }}
                            </h2>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-red-600 italic">
                                {{ __('permissions.impersonation_coach_decision') }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ __('permissions.impersonate_confirm') }}
                            <span class="font-black text-gray-900 dark:text-white bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded" x-text="targetUser.name"></span>?
                        </p>
                        <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-800">
                            <i class="fa-light fa-circle-exclamation text-amber-600"></i>
                            <p class="text-[11px] font-medium text-amber-800 dark:text-amber-400 leading-snug">
                                {{ __('permissions.impersonation_info_text') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button @click="confirmImpersonate()"
                                class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-red-500/25 flex items-center justify-center gap-2 group">
                            <span>{{ __('permissions.impersonation_enter_game') }}</span>
                            <i class="fa-light fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </button>
                        <button @click="confirmModal = false"
                                class="w-full py-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-bold text-xs uppercase tracking-widest transition-colors">
                            {{ __('permissions.impersonation_stay_bench') }}
                        </button>
                    </div>
                </div>

                <!-- Bottom progress decoration -->
                <div class="h-1.5 w-full bg-slate-100 dark:bg-gray-800 flex">
                    <div class="h-full w-1/3 bg-red-600"></div>
                    <div class="h-full w-1/3 bg-navy-600"></div>
                    <div class="h-full w-1/3 bg-blue-500"></div>
                </div>
            </div>
        </div>
    </template>
</div>
@endif
