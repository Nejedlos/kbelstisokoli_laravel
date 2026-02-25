@props(['branding'])

@php
    $footerNav = $footerMenu ?? [];
    $clubNav = $footerClubMenu ?? [];
@endphp

<footer class="bg-secondary pt-6 pb-6 text-slate-300 relative overflow-hidden">
    {{-- Accent line --}}
    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-primary via-primary-hover to-primary"></div>

    {{-- Decor --}}
    <div class="absolute top-0 right-0 w-1/3 h-full pointer-events-none opacity-[0.03]">
        <i class="fa-light fa-basketball text-[30rem] translate-x-1/2 -translate-y-1/4"></i>
    </div>

    <div class="container pt-12 md:pt-20 pb-6 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
            {{-- Column 1: Brand & Identity --}}
            <div class="space-y-6">
                @if($branding['logo_path'])
                    <a href="{{ route('public.home') }}" class="inline-flex items-center gap-4 group">
                        <div class="p-2 bg-white rounded-xl">
                            <img src="{{ asset('storage/' . $branding['logo_path']) }}" alt="{{ $branding['club_name'] }}" class="h-10 w-auto">
                        </div>
                        <span class="text-xl font-black uppercase tracking-tighter text-white group-hover:text-primary transition-colors">
                            {{ __('footer.brand_title') }}
                        </span>
                    </a>
                @else
                    <div class="text-2xl font-black uppercase tracking-tighter text-white">
                        {{ __('footer.brand_title') }}
                    </div>
                @endif

                <div class="space-y-4">
                    <p class="font-bold text-white leading-snug">
                        {{ __('footer.brand_text') }}
                    </p>
                    <p class="text-sm leading-relaxed text-white/60">
                        {{ __('footer.brand_subtext') }}
                    </p>
                </div>

                <div class="inline-flex items-center bg-white/5 border border-white/10 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest-responsive text-white/50 max-w-full overflow-hidden">
                    <span class="flex items-center justify-center h-2 w-2 mr-2.5 shrink-0 relative translate-y-[0.5px]">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                    </span>
                    <span class="leading-tight">{{ __('footer.brand_badge') }}</span>
                </div>
            </div>

            {{-- Column 2: Navigation --}}
            <div>
                <h3 class="text-white font-black uppercase tracking-widest-responsive text-sm mb-8 flex items-center leading-tight">
                    <span class="w-8 h-px bg-primary mr-3"></span>
                    {{ __('footer.nav_title') }}
                </h3>
                <ul class="space-y-4">
                    @forelse($footerNav as $item)
                        <li>
                            <a href="{{ $item->url }}" class="hover:text-primary transition-all flex items-center group {{ request()->url() === $item->url ? 'text-primary' : '' }}">
                                <i class="fa-light fa-chevron-right text-[10px] mr-0 opacity-0 group-hover:mr-3 group-hover:opacity-100 transition-all"></i>
                                <span class="font-medium">{{ $item->label }}</span>
                            </a>
                        </li>
                    @empty
                        {{-- Fallback na statickou navigaci z configu, pokud menu v DB není --}}
                        @foreach(config('navigation.public', []) as $item)
                            <li>
                                <a href="{{ route($item['route']) }}" class="hover:text-primary transition-all flex items-center group {{ request()->routeIs($item['route']) ? 'text-primary' : '' }}">
                                    <i class="fa-light fa-chevron-right text-[10px] mr-0 opacity-0 group-hover:mr-3 group-hover:opacity-100 transition-all"></i>
                                    <span class="font-medium">{{ __($item['title']) }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endforelse
                    <li>
                        <a href="{{ route('public.search') }}" class="hover:text-primary transition-all flex items-center group {{ request()->routeIs('public.search') ? 'text-primary' : '' }}">
                            <i class="fa-light fa-chevron-right text-[10px] mr-0 opacity-0 group-hover:mr-3 group-hover:opacity-100 transition-all"></i>
                            <span class="font-medium">{{ __('Search') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3: Teams & Club --}}
            <div>
                <h3 class="text-white font-black uppercase tracking-widest-responsive text-sm mb-8 flex items-center leading-tight">
                    <span class="w-8 h-px bg-primary mr-3"></span>
                    {{ __('footer.club_title') }}
                </h3>
                <ul class="space-y-4">
                    @forelse($clubNav as $item)
                        @php $isExternal = str_starts_with($item->url, 'http'); @endphp
                        <li class="{{ $isExternal ? 'pb-1' : '' }}">
                            <a href="{{ $item->url }}"
                               @if($isExternal) target="_blank" rel="noopener" @endif
                               class="group flex items-center justify-between transition-all duration-300 {{ $isExternal ? 'bg-white/5 border border-white/10 py-2 px-4 rounded-xl hover:bg-primary/10 hover:border-primary/20 hover:text-white' : 'hover:text-primary py-1' }}">
                                <span class="{{ $isExternal ? 'font-bold text-sm tracking-tight' : 'font-medium text-sm' }}">{{ $item->label }}</span>
                                @if($isExternal)
                                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
                                        <i class="fa-light fa-arrow-up-right text-[10px]"></i>
                                    </span>
                                @else
                                    <i class="fa-light fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500 italic">{{ __('footer.empty_menu') }}</li>
                    @endforelse
                </ul>
                <div class="mt-8 p-4 bg-white/5 rounded-xl border border-white/5">
                    <p class="text-xs leading-relaxed text-slate-400">
                        {{ __('footer.club_text') }}
                    </p>
                </div>
            </div>

            {{-- Column 4: Contact & Socials --}}
            <div>
                <h3 class="text-white font-black uppercase tracking-widest-responsive text-sm mb-8 flex items-center leading-tight">
                    <span class="w-8 h-px bg-primary mr-3"></span>
                    {{ __('footer.contact_title') }}
                </h3>

                <div class="space-y-6">
                    <ul class="space-y-4 text-sm">
                        @if($branding['venue']['name'] ?? null)
                            <li class="flex items-center gap-4 group">
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-primary shrink-0">
                                    <i class="fa-light fa-basketball-hoop"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white leading-tight">{{ $branding['venue']['name'] }}</div>
                                    @if($branding['match_day'] ?? null)
                                        <div class="text-[10px] text-slate-500 uppercase font-black tracking-widest mt-1">{{ $branding['match_day'] }}</div>
                                    @endif
                                </div>
                            </li>
                        @endif

                        @if(data_get($branding, 'contact.email'))
                            <li class="flex items-center gap-4 group">
                                <x-mailto :email="$branding['contact']['email']" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <i class="fa-light fa-envelope"></i>
                                </x-mailto>
                                <x-mailto :email="$branding['contact']['email']" class="hover:text-primary transition-colors font-bold break-all py-2" />
                            </li>
                        @endif

                        @if(data_get($branding, 'contact.phone'))
                            <li class="flex items-center gap-4 group">
                                <a href="tel:{{ str_replace(' ', '', $branding['contact']['phone']) }}" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all shrink-0">
                                    <i class="fa-light fa-phone"></i>
                                </a>
                                <a href="tel:{{ str_replace(' ', '', $branding['contact']['phone']) }}" class="hover:text-primary transition-colors font-bold py-2">{{ $branding['contact']['phone'] }}</a>
                            </li>
                        @endif

                        @if(!data_get($branding, 'contact.email') && !data_get($branding, 'contact.phone'))
                            <li class="text-slate-400 italic text-xs flex items-start gap-3">
                                <i class="fa-light fa-circle-info mt-0.5 text-primary"></i>
                                {{ __('footer.contact_placeholder_text') }}
                            </li>
                        @endif
                    </ul>

                    <div class="flex flex-wrap gap-4 pt-4 border-t border-white/5">
                        <a href="{{ route('public.contact.index') }}" class="btn btn-primary btn-sm px-6">
                            <span>{{ __('footer.contact_page_cta') }}</span>
                        </a>
                        <a href="{{ $branding['main_club_url'] }}"
                           target="_blank"
                           rel="noopener"
                           class="btn btn-outline-white btn-sm px-6 group"
                           data-track-click="external_link"
                           data-track-label="Footer: Main Club Website"
                           data-track-category="external">
                            <span>{{ __('footer.contact_club_cta') }}</span>
                            <i class="fa-light fa-arrow-up-right ml-2 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform opacity-70"></i>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="bg-black/40 border-t border-white/5 text-slate-500 text-xs md:text-sm">
        <div class="container py-10 md:py-8 flex flex-col md:flex-row items-center justify-between gap-10 md:gap-8">
            <div class="text-center md:text-left">
                <div class="font-bold text-slate-300 mb-2 uppercase tracking-tight text-balance leading-tight">
                    {{ brand_text($branding['footer_text'] ?? ('© ' . date('Y') . ' ' . __('footer.brand_title'))) }}
                </div>
                <div class="flex items-center justify-center md:justify-start gap-4">
                    <div class="w-10 h-px bg-primary/30 hidden xs:block"></div>
                    <span class="uppercase tracking-widest-responsive sm:tracking-[0.2em] text-[10px] font-black text-slate-500 leading-tight">
                        {{ __('footer.bottom_part_of') }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap justify-center md:justify-end items-center gap-x-8 gap-y-6 md:gap-x-6 md:gap-y-4">
                <a href="{{ route('public.contact.index') }}" class="hover:text-primary transition-all uppercase tracking-widest-responsive sm:tracking-[0.15em] text-[10px] font-black group flex items-center py-2">
                    <span class="w-1.5 h-1.5 bg-primary/40 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity hidden md:block"></span>
                    {{ __('Kontakt') }}
                </a>

                <span class="w-1 h-1 bg-slate-800 rounded-full hidden md:block"></span>

                <a href="{{ route('login') }}" class="hover:text-primary transition-all uppercase tracking-widest-responsive sm:tracking-[0.15em] text-[10px] font-black group flex items-center">
                    <span class="w-1 h-1 bg-primary/40 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                    {!! str_replace(' ', '&nbsp;', __('Členská sekce')) !!}
                </a>

                <span class="w-1 h-1 bg-slate-800 rounded-full hidden md:block"></span>

                <a href="{{ route('public.pages.show', 'gdpr') }}" class="hover:text-primary transition-all uppercase tracking-widest-responsive sm:tracking-[0.15em] text-[10px] font-black group flex items-center">
                    <span class="w-1 h-1 bg-primary/40 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                    {{ __('Ochrana soukromí') }}
                </a>

                <span class="w-1 h-1 bg-slate-800 rounded-full hidden md:block"></span>

                <a href="{{ $branding['main_club_url'] }}" target="_blank" rel="noopener" class="hover:text-primary transition-all uppercase tracking-widest-responsive sm:tracking-[0.15em] text-[10px] font-black flex items-center group bg-white/5 px-4 py-2 rounded-full border border-white/5 hover:bg-primary/10 hover:border-primary/20">
                    {{ __('Hlavní oddíl') }}
                    <i class="fa-light fa-arrow-up-right ml-2 text-[8px] transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5 text-primary"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
