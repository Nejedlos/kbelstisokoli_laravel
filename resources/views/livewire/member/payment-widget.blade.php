<div class="bg-secondary card group overflow-hidden p-6 relative sm:p-10 text-white">
    <div class="absolute duration-1000 group-hover:scale-110 opacity-[0.05] p-6 right-0 top-0 transition-transform">
        <i class="fa-light fa-bank sm:text-[180px] text-[120px]"></i>
    </div>
    <div class="gap-10 grid grid-cols-1 items-center md:grid-cols-2 relative z-10">
        <div class="space-y-6">
            <div>
                <h3 class="font-black leading-none mb-3 sm:text-3xl text-2xl text-primary tracking-tight uppercase">Bankovní spojení</h3>
                <p class="font-medium italic leading-relaxed opacity-80 sm:text-sm text-[13px] text-slate-300">Pro členské příspěvky a platby prosím používejte náš klubový účet:</p>
            </div>
            <div class="pt-2 space-y-3">
                <div class="border-b border-white/10 flex flex-col gap-1 justify-between pb-3 sm:flex-row sm:items-center">
                    <span class="font-black text-[10px] text-white/40 tracking-widest uppercase">Číslo účtu</span>
                    <span class="font-bold sm:text-lg text-base">{{ $bankAccount }}</span>
                </div>
                <div class="border-b border-white/10 flex flex-col gap-1 justify-between pb-3 sm:flex-row sm:items-center">
                    <span class="font-black text-[10px] text-white/40 tracking-widest uppercase">Banka</span>
                    <span class="font-bold text-sm">{{ $bankName }}</span>
                </div>
                <div class="border-b border-white/10 flex flex-col gap-1 justify-between pb-3 sm:flex-row sm:items-center">
                    <span class="font-black text-[10px] text-white/40 tracking-widest uppercase">Variabilní symbol</span>
                    <span class="font-bold text-sm">{{ $vs }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1">
                        <label class="font-black text-[10px] text-white/40 tracking-widest uppercase">Poznámka (MSG)</label>
                        <input type="text" wire:model.live="note" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs font-bold text-white focus:outline-none focus:border-primary/50 transition-colors" placeholder="{{ $memberName }}">
                    </div>
                    <div class="space-y-1">
                        <label class="font-black text-[10px] text-white/40 tracking-widest uppercase">Specifický symbol (SS)</label>
                        <input type="text" wire:model.live="ss" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs font-bold text-white focus:outline-none focus:border-primary/50 transition-colors" placeholder="Nepovinné">
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-4 items-center justify-center">
            <div class="bg-white flex group/qr h-40 items-center justify-center p-4 relative rounded-[2rem] shadow-2xl sm:h-48 sm:w-48 w-40 overflow-hidden">
                @if($qrCodeDataUri)
                    <img src="{{ $qrCodeDataUri }}" alt="QR Platba" class="w-full h-full object-contain">
                @else
                    <div class="flex flex-col items-center justify-center text-secondary text-[10px] text-center p-4">
                        <i class="fa-light fa-spinner fa-spin text-xl mb-2"></i>
                        <span class="font-bold uppercase tracking-widest">Generuji QR kód</span>
                    </div>
                @endif
                <div class="absolute backdrop-blur-[2px] bg-secondary/80 flex flex-col group-hover/qr:opacity-100 inset-0 items-center justify-center opacity-0 p-6 rounded-[2rem] text-center transition-all">
                    <i class="fa-light fa-magnifying-glass-plus mb-2 text-2xl"></i>
                    <span class="font-black leading-tight text-[10px] text-white tracking-widest uppercase">QR Platba</span>
                </div>
            </div>
            <span class="font-black italic text-[10px] text-white/40 tracking-widest uppercase">QR Platba</span>
        </div>
    </div>
</div>
