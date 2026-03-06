@inject('partnerService', 'App\Services\PartnerService')
@inject('brandingService', 'App\Services\BrandingService')

@php
    $partners = $partnerService->getHomepagePartners();
    $branding = $brandingService->getSettings();
    $style = $branding['partners']['section_style'] ?? 'logo_with_label';
@endphp

@if($partners->isNotEmpty())
    <div class="bg-white border-b border-ui-border relative z-20 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-center py-4 md:py-6 gap-6 md:gap-12">
                {{-- Label --}}
                <div class="shrink-0 text-center md:text-left">
                    <span class="block text-[10px] md:text-[11px] font-display font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-1">
                        {{ __('partners.main_partner_label') }}
                    </span>
                    <h3 class="text-sm md:text-base font-bold text-secondary uppercase tracking-tight leading-none">
                        {{ $partners->first()->name }}
                    </h3>
                </div>

                {{-- Partners List --}}
                <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12">
                    @foreach($partners as $partner)
                        <a href="{{ $partner->website_url ?? '#' }}"
                           @if($partner->opened_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                           class="group/partner transition-all duration-300 hover:scale-105"
                           title="{{ $partner->name }}">

                            <picture>
                                @if($partner->logo_path_webp)
                                    <source srcset="{{ asset($partner->logo_path_webp) }}" type="image/webp">
                                @endif
                                <img src="{{ asset($partner->logo_path_png ?? $partner->logo_path_webp) }}"
                                     alt="{{ $partner->name }}"
                                     class="object-contain transition-all duration-500 grayscale group-hover/partner:grayscale-0 opacity-70 group-hover/partner:opacity-100"
                                     style="max-width: {{ $branding['partners']['logo_width_desktop'] }}px; max-height: {{ $branding['partners']['logo_max_height'] }}px; width: auto; height: auto;">
                            </picture>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
