@props(['branding', 'navigation'])

<footer class="bg-secondary text-slate-200 mt-16">
    <div class="container section-padding grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Brand / About -->
        <div>
            @if($branding['logo_path'])
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" alt="{{ brand_text($branding['club_name'] ?? 'Klub') }}" class="h-10 w-auto">
                    <span class="font-display font-bold uppercase tracking-wide">{{ brand_text($branding['club_name'] ?? config('app.name')) }}</span>
                </div>
            @endif
            @if(!empty($branding['slogan']))
                <p class="text-slate-400">{{ brand_text($branding['slogan']) }}</p>
            @endif
        </div>

        <!-- Navigation -->
        @if(!($branding['maintenance_mode'] ?? false))
        <div>
            <h3 class="text-white font-bold uppercase mb-4">Navigace</h3>
            <ul class="space-y-2">
                @forelse($navigation as $item)
                    <li>
                        <a href="{{ route($item['route']) }}" class="hover:text-primary transition">{{ $item['title'] }}</a>
                    </li>
                @empty
                    <li class="text-slate-400">Menu bude doplněno.</li>
                @endforelse
            </ul>
        </div>
        @endif

        <!-- Contact / Socials -->
        <div>
            <h3 class="text-white font-bold uppercase mb-4">Kontakt</h3>
            <ul class="space-y-3 text-slate-300">
                @if(data_get($branding, 'contact.address'))
                    <li class="flex items-start gap-3">
                        <i class="fa-light fa-location-dot mt-1 text-primary"></i>
                        <span>{{ $branding['contact']['address'] }}</span>
                    </li>
                @endif
                @if(data_get($branding, 'contact.email'))
                    <li class="flex items-center gap-3">
                        <i class="fa-light fa-envelope text-primary"></i>
                        <a href="mailto:{{ $branding['contact']['email'] }}" class="hover:text-primary transition-colors">{{ $branding['contact']['email'] }}</a>
                    </li>
                @endif
                @if(data_get($branding, 'contact.phone'))
                    <li class="flex items-center gap-3">
                        <i class="fa-light fa-phone text-primary"></i>
                        <a href="tel:{{ $branding['contact']['phone'] }}" class="hover:text-primary transition-colors">{{ $branding['contact']['phone'] }}</a>
                    </li>
                @endif
            </ul>

            <div class="mt-6 flex items-center gap-4">
                @if(data_get($branding, 'socials.facebook'))
                    <a href="{{ $branding['socials']['facebook'] }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300" target="_blank" rel="noopener">
                        <i class="fa-brands fa-facebook-f text-lg"></i>
                    </a>
                @endif
                @if(data_get($branding, 'socials.instagram'))
                    <a href="{{ $branding['socials']['instagram'] }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300" target="_blank" rel="noopener">
                        <i class="fa-brands fa-instagram text-lg"></i>
                    </a>
                @endif
                @if(data_get($branding, 'socials.youtube'))
                    <a href="{{ $branding['socials']['youtube'] }}" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300" target="_blank" rel="noopener">
                        <i class="fa-brands fa-youtube text-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-dark text-slate-400 text-sm">
        <div class="container py-4 flex items-center justify-between">
            <span>{{ brand_text($branding['footer_text'] ?? (__('Všechna práva vyhrazena'))) }}</span>
            @if(!($branding['maintenance_mode'] ?? false))
                <a href="{{ route('public.contact.index') }}" class="hover:text-primary">Kontakt</a>
            @endif
        </div>
    </div>
</footer>
