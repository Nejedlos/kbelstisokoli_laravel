<div x-data="{ isUpdating: false }"
     @qr-updating-start.window="isUpdating = true"
     @qr-updating-stop.window="isUpdating = false"
     class="bg-secondary card group overflow-hidden p-6 relative sm:p-10 text-white">
    <style>
        @keyframes qr-scale-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .animate-qr-scale-pulse {
            animation: qr-scale-pulse 0.3s ease-in-out infinite;
        }
        @keyframes overlay-slow-pulse {
            0%, 100% { transform: scale(1); opacity: 0.85; }
            50% { transform: scale(1.05); opacity: 1; }
        }
        .animate-overlay-slow-pulse {
            animation: overlay-slow-pulse 0.3s ease-in-out 2 forwards;
        }
    </style>
    <div class="absolute duration-1000 group-hover:scale-110 opacity-[0.05] p-6 right-0 top-0 transition-transform">
        <i class="fa-light fa-bank sm:text-[180px] text-[120px]"></i>
    </div>
    <div class="gap-10 grid grid-cols-1 items-center md:grid-cols-2 relative z-10">
        <div class="space-y-6">
            <div>
                <h3 class="font-black leading-none mb-3 sm:text-3xl text-2xl text-primary tracking-tight uppercase">{{ __('member.economy.bank_info.title') }}</h3>
                <p class="font-medium italic leading-relaxed opacity-80 sm:text-sm text-[13px] text-slate-300">{{ __('member.economy.bank_info.text') }}</p>
            </div>
            <div class="pt-2 space-y-3">
                <div class="border-b border-white/10 flex flex-col gap-1 justify-between pb-3 sm:flex-row sm:items-center">
                    <span class="font-black text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.economy.bank_info.account_number') }}</span>
                    <span class="font-bold sm:text-lg text-base">{{ $bankAccount }}</span>
                </div>
                <div class="border-b border-white/10 flex flex-col gap-1 justify-between pb-3 sm:flex-row sm:items-center">
                    <span class="font-black text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.economy.bank_info.bank') }}</span>
                    <span class="font-bold text-sm">{{ $bankName }}</span>
                </div>
                <div class="border-b border-white/10 flex flex-col gap-1 justify-between pb-3 sm:flex-row sm:items-center">
                    <span class="font-black text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.dashboard.profile.payment_vs') }}</span>
                    <span class="font-bold text-sm">{{ $vs }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="space-y-1">
                        <label class="font-black text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.economy.bank_info.amount') }}</label>
                        <input type="text" wire:model.live="amount" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs font-bold text-white focus:outline-none focus:border-primary/50 transition-colors" placeholder="{{ __('member.economy.bank_info.amount_placeholder') }}">
                    </div>
                    <div class="space-y-1">
                        <label class="font-black text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.economy.bank_info.note') }}</label>
                        <input type="text" wire:model.live="note" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs font-bold text-white focus:outline-none focus:border-primary/50 transition-colors" placeholder="{{ $memberName }}">
                    </div>
                    <div class="space-y-1">
                        <label class="font-black text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.economy.bank_info.ss') }}</label>
                        <input type="text" wire:model.live="ss" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs font-bold text-white focus:outline-none focus:border-primary/50 transition-colors" placeholder="{{ __('member.economy.bank_info.ss_placeholder') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-4 items-center justify-center">
            <div class="bg-white flex group/qr h-40 items-center justify-center p-4 relative rounded-[2rem] shadow-2xl sm:h-48 sm:w-48 w-40 overflow-hidden">
                @if($qrCodeDataUri)
                    <div x-show="isUpdating" x-cloak class="absolute inset-0 flex items-center justify-center z-20 transition-opacity duration-300">
                        <i class="fa-light fa-arrows-rotate fa-spin text-4xl text-primary"></i>
                    </div>
                    <img src="{{ $qrCodeDataUri }}" alt="QR Platba"
                         class="w-full h-full object-contain transition-all duration-300"
                         id="qr-code-img"
                         wire:key="{{ md5($qrCodeDataUri) }}"
                         :class="isUpdating ? 'animate-qr-scale-pulse opacity-40 blur-[2px]' : ''">
                @else
                    <div class="flex flex-col items-center justify-center text-secondary text-[10px] text-center p-4">
                        <i class="fa-light fa-spinner fa-spin text-xl mb-2"></i>
                        <span class="font-bold uppercase tracking-widest">Generuji QR kód</span>
                    </div>
                @endif
                <div class="absolute backdrop-blur-[2px] bg-secondary/80 flex flex-col group-hover/qr:opacity-100 inset-0 items-center justify-center opacity-0 p-6 rounded-[2rem] text-center transition-all"
                     :class="isUpdating ? 'opacity-100 animate-overlay-slow-pulse' : ''">
                    <i class="fa-light fa-magnifying-glass-plus mb-2 text-2xl"></i>
                    <span class="font-black leading-tight text-[10px] text-white tracking-widest uppercase">{{ __('member.economy.bank_info.qr_payment') }}</span>
                </div>
            </div>

            @if($qrCodeDataUri)
                <button
                    onclick="downloadQRCode()"
                    class="bg-white/10 hover:bg-white/20 transition-colors px-4 py-2 rounded-full flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-white"
                >
                    <i class="fa-light fa-download"></i>
                    {{ __('member.economy.bank_info.download_qr') }}
                </button>
            @endif

            <span class="font-black italic text-[10px] text-white/40 tracking-widest uppercase">{{ __('member.economy.bank_info.qr_payment') }}</span>
        </div>
    </div>

    <script>
        function downloadQRCode() {
            const img = document.getElementById('qr-code-img');
            if (!img) return;

            const link = document.createElement('a');
            link.href = img.src;
            link.download = 'ks-qr-platba.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // JS Debug pro uživatele
        document.addEventListener('livewire:init', () => {
            console.log('PaymentWidget: Livewire initialized');

            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                if (component.name !== 'member.payment-widget') return;

                console.log('PaymentWidget: Sending update...', commit);
                window.dispatchEvent(new CustomEvent('qr-updating-start'));

                succeed(({ snapshot, effect }) => {
                    console.log('PaymentWidget: Update successful');
                    setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('qr-updating-stop'));
                    }, 600); // Necháme puls běžet 0.6 sekundy (2 cykly po 0.3s)
                });

                fail(() => {
                    console.error('PaymentWidget: Update failed!');
                    window.dispatchEvent(new CustomEvent('qr-updating-stop'));
                });
            });
        });
    </script>
</div>
