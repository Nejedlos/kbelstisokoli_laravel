@props(['url', 'name' => '', 'address' => ''])

<div x-data="{ mapOpen: false }" class="inline-block">
    <button
        @click="mapOpen = true"
        type="button"
        class="text-xs font-black uppercase text-primary hover:text-secondary flex items-center group/link cursor-pointer"
    >
        {{ __('general.view_on_map') }}
        <i class="fa-light fa-arrow-up-right ml-1.5 transition-transform group-hover/link:-translate-y-0.5 group-hover/link:translate-x-0.5"></i>
    </button>

    <template x-teleport="body">
        <div
            x-show="mapOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
            x-cloak
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="mapOpen = false"
            ></div>

            <!-- Modal Content -->
            <div
                x-show="mapOpen"
                x-transition:enter="transition ease-out duration-500 transform"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-300 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                class="relative w-full max-w-[740px] bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]"
            >
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3 text-left">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <i class="fa-light fa-location-dot text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black uppercase tracking-tight text-secondary leading-none mb-1 truncate">{{ $name }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ $address }}</p>
                        </div>
                    </div>
                    <button @click="mapOpen = false" class="w-10 h-10 rounded-xl hover:bg-slate-200 transition-colors text-slate-400 hover:text-slate-600 flex items-center justify-center shrink-0">
                        <i class="fa-light fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Map Iframe -->
                <div class="relative flex-1 bg-slate-100 min-h-[300px] sm:min-h-[500px]">
                    <iframe
                        src="{{ $url }}"
                        class="absolute inset-0 w-full h-full border-0"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>

                <!-- Footer / Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-4 items-center justify-between">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ __('general.map_modal_footer_note') }}
                    </div>
                    <a
                        href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}"
                        target="_blank"
                        rel="noopener"
                        class="px-5 py-2.5 bg-secondary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary transition-colors flex items-center gap-2"
                    >
                        <i class="fa-light fa-map-location-dot"></i>
                        {{ __('general.open_google_maps') }}
                    </a>
                </div>
            </div>
        </div>
    </template>
</div>
