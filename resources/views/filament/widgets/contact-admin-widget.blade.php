<div class="rounded-club border border-slate-200 bg-white p-6">
    <div class="flex flex-col items-center md:items-start md:flex-row gap-4 text-center md:text-left">
        @if(!empty($contact['photo']))
            <img src="{{ asset('storage/' . $contact['photo']) }}" alt="admin" class="w-14 h-14 rounded-full object-cover border border-slate-200">
        @else
            <div class="w-14 h-14 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl border border-slate-200 shrink-0">
                <i class="fa-light fa-user-shield"></i>
            </div>
        @endif
        <div class="flex-1 w-full">
            <h3 class="text-sm font-black uppercase tracking-tight text-secondary flex items-center justify-center md:justify-start gap-2">
                <i class="fa-light fa-life-ring"></i>
                {{ __('admin/dashboard.contact_admin.title') }}
            </h3>
            <p class="text-xs text-slate-600 mt-1">
                {{ __('admin/dashboard.contact_admin.text') }}
            </p>

            <div class="mt-3 space-y-2 text-xs">
                <div class="px-3 py-2 rounded-club bg-slate-50 border border-slate-200 flex items-center justify-center md:justify-start gap-2">
                    <i class="fa-light fa-user fa-fw"></i>
                    <span class="font-bold text-secondary">{{ $contact['name'] }}</span>
                </div>
                <div class="px-3 py-2 rounded-club bg-slate-50 border border-slate-200 flex items-center justify-center md:justify-start gap-2">
                    <i class="fa-light fa-envelope fa-fw"></i>
                    <span>{{ $contact['email'] ?? __('member.feedback.contact_card.not_available') }}</span>
                </div>
                <div class="px-3 py-2 rounded-club bg-slate-50 border border-slate-200 flex items-center justify-center md:justify-start gap-2">
                    <i class="fa-light fa-phone fa-fw"></i>
                    <span>{{ $contact['phone'] ?? __('member.feedback.contact_card.not_available') }}</span>
                </div>
            </div>

            <form wire:submit.prevent="send" class="mt-4 space-y-2 text-left">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Vaše jméno</label>
                    <input type="text" wire:model.defer="senderName" class="w-full border border-slate-200 rounded-club px-3 py-2 text-sm" placeholder="Jméno a příjmení">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Váš e‑mail</label>
                    <input type="email" wire:model.defer="senderEmail" class="w-full border border-slate-200 rounded-club px-3 py-2 text-sm" placeholder="email@domena.cz">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Telefon (volitelné)</label>
                    <input type="tel" wire:model.defer="senderPhone" class="w-full border border-slate-200 rounded-club px-3 py-2 text-sm" placeholder="+420 123 456 789">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Zpráva pro administrátora</label>
                    <textarea wire:model.defer="messageText" class="w-full border border-slate-200 rounded-club px-3 py-2 text-sm min-h-32" placeholder="Popište prosím, s čím potřebujete pomoci..."></textarea>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        <i class="fa-light fa-paper-plane-top mr-1.5"></i>
                        Odeslat zprávu
                    </button>
                    <a href="{{ $contactUrl }}" class="btn btn-outline w-full sm:w-auto">
                        <i class="fa-light fa-life-ring mr-1.5"></i>
                        Otevřít stránku Kontakt admina
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
