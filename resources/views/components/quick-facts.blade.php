@props(['branding' => [], 'layout' => 'grid'])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6']) }}>
    {{-- Kde hrajeme (Hala) --}}
    @if($branding['venue']['name'] ?? null)
        <div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-all">
                <i class="fa-light fa-location-dot text-xl"></i>
            </div>
            <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-2">{{ __('contact.venue') }}</h4>
            <div class="font-bold text-secondary text-lg mb-1 leading-tight tracking-tight">
                {{ $branding['venue']['name'] }}
            </div>
            <div class="text-sm text-slate-500 mb-4">
                {{ $branding['venue']['street'] }}, {{ $branding['venue']['city'] }}
            </div>
            @if($branding['venue']['map_url'] ?? null)
                <a href="{{ $branding['venue']['map_url'] }}" target="_blank" rel="noopener" class="text-xs font-black uppercase text-primary hover:text-secondary flex items-center group/link">
                    {{ __('general.view_on_map') }}
                    <i class="fa-light fa-arrow-up-right ml-1.5 transition-transform group-hover/link:-translate-y-0.5 group-hover/link:translate-x-0.5"></i>
                </a>
            @endif
        </div>
    @endif

    {{-- Kdy hrajeme (Match Day) --}}
    @if($branding['match_day'] ?? null)
        <div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group text-balance">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-all">
                <i class="fa-light fa-calendar-days text-xl"></i>
            </div>
            <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-2">{{ __('contact.match_day') }}</h4>
            <div class="font-bold text-secondary text-lg mb-1 leading-tight tracking-tight">
                {{ $branding['match_day'] }}
            </div>
            <div class="text-sm text-slate-500">
                {{ __('general.match_day_desc') }}
            </div>
        </div>
    @endif

    {{-- Koho kontaktovat (Nábor) --}}
    @if($branding['public_contact']['person'] ?? null)
        <div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-all">
                <i class="fa-light fa-id-card text-xl"></i>
            </div>
            <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-2">{{ __('contact.team_leader') }}</h4>
            <div class="font-bold text-secondary text-lg mb-1 leading-tight tracking-tight">
                {{ $branding['public_contact']['person'] }}
            </div>
            @if($branding['public_contact']['role'] ?? null)
                <div class="text-xs font-bold uppercase text-slate-400 mb-4">{{ $branding['public_contact']['role'] }}</div>
            @endif
            <div class="flex flex-col gap-2">
                @if($branding['public_contact']['email'] ?? null)
                    <x-mailto :email="$branding['public_contact']['email']" class="text-xs font-black uppercase text-primary hover:text-secondary flex items-center">
                        <i class="fa-light fa-envelope mr-2"></i> {{ __('general.send_email') }}
                    </x-mailto>
                @endif
                @if($branding['public_contact']['phone'] ?? null)
                    <a href="tel:{{ str_replace(' ', '', $branding['public_contact']['phone']) }}" class="text-xs font-black uppercase text-primary hover:text-secondary flex items-center">
                        <i class="fa-light fa-phone mr-2"></i> {{ $branding['public_contact']['phone'] }}
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Hlavní oddíl / Mládež --}}
    <div class="p-6 bg-secondary text-white rounded-2xl shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 pointer-events-none group-hover:scale-110 transition-transform duration-500">
            <i class="fa-light fa-basketball text-6xl rotate-12"></i>
        </div>
        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-primary transition-all">
            <i class="fa-light fa-arrow-up-right-from-square text-xl"></i>
        </div>
        <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-2">{{ __('general.main_club') }}</h4>
        <div class="font-bold text-lg mb-1 leading-tight tracking-tight">
            {{ $branding['club_name'] ?? 'TJ Sokol Kbely' }}
        </div>
        <div class="text-xs text-slate-400 mb-4">{{ __('general.youth_recruitment') }}</div>
        <a href="{{ $branding['main_club_url'] ?? 'https://www.basketkbely.cz/' }}" target="_blank" rel="noopener" class="text-xs font-black uppercase text-primary hover:text-white flex items-center group/link">
            {{ __('general.visit_website') }}
            <i class="fa-light fa-arrow-up-right ml-1.5 transition-transform group-hover/link:-translate-y-0.5 group-hover/link:translate-x-0.5"></i>
        </a>
    </div>
</div>
