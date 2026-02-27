<div class="fi-section rounded-club overflow-hidden border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900 shadow-sm">
    <div class="p-6">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="w-full md:w-1/3 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center dark:bg-primary-900/20">
                        <i class="fa-light fa-life-ring text-primary-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-gray-900 dark:text-white">
                            {{ __('admin/dashboard.contact_admin.title') }}
                        </h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">Podpora systému</p>
                    </div>
                </div>

                <div class="flex flex-col items-center p-6 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 text-center">
                    @if(!empty($contact['photo']))
                        <img src="{{ asset('storage/' . $contact['photo']) }}" alt="admin" class="w-20 h-20 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-md mb-4">
                    @else
                        <div class="w-20 h-20 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-3xl border-4 border-white dark:border-gray-800 shadow-md mb-4">
                            <i class="fa-light fa-user-shield"></i>
                        </div>
                    @endif
                    <div class="text-base font-black text-gray-900 dark:text-white leading-none">{{ $contact['name'] }}</div>
                    <div class="text-xs text-primary-600 font-bold mt-1 uppercase tracking-widest">Hlavní administrátor</div>

                    <div class="w-full mt-6 space-y-2">
                        @if($contact['email'])
                        <a href="mailto:{{ $contact['email'] }}" class="flex items-center gap-3 px-3 py-2 rounded-xl bg-white dark:bg-white/5 border border-gray-100 dark:border-white/5 text-xs text-gray-600 dark:text-gray-300 hover:border-primary-200 transition-colors">
                            <i class="fa-light fa-envelope text-primary-600"></i>
                            <span class="truncate">{{ $contact['email'] }}</span>
                        </a>
                        @endif
                        @if($contact['phone'])
                        <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-white dark:bg-white/5 border border-gray-100 dark:border-white/5 text-xs text-gray-600 dark:text-gray-300">
                            <i class="fa-light fa-phone text-primary-600"></i>
                            <span>{{ $contact['phone'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex-1 w-full border-t md:border-t-0 md:border-l border-gray-100 dark:border-white/5 pt-8 md:pt-0 md:pl-8">
                <div class="mb-6">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">Rychlá zpráva</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin/dashboard.contact_admin.text') }}</p>
                </div>

                <form wire:submit.prevent="send" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Vaše jméno</label>
                        <input type="text" wire:model.defer="senderName" class="w-full border border-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all" placeholder="Jméno a příjmení">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Váš e‑mail</label>
                        <input type="email" wire:model.defer="senderEmail" class="w-full border border-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all" placeholder="email@domena.cz">
                    </div>
                    <div class="col-span-full">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Zpráva pro administrátora</label>
                        <textarea wire:model.defer="messageText" class="w-full border border-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-white rounded-xl px-4 py-2.5 text-sm min-h-[120px] focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all" placeholder="Popište prosím, s čím potřebujete pomoci..."></textarea>
                    </div>
                    <div class="col-span-full flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:shadow-md focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <i class="fa-light fa-paper-plane-top mr-2 text-sm"></i>
                            Odeslat zprávu
                        </button>
                        <a href="{{ $contactUrl }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-300 text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm">
                            <i class="fa-light fa-life-ring mr-2 text-sm"></i>
                            Centrum nápovědy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
