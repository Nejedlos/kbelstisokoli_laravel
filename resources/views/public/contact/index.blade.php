@extends('layouts.public')

@section('content')
    <x-page-header
        title="Kontakt"
        subtitle="Zde nás najdete. Neváhejte se na nás obrátit s jakýmkoliv dotazem."
        :breadcrumbs="['Kontakt' => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Contact Info -->
                <div class="lg:col-span-1 space-y-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tight text-secondary mb-6">Spojte se s námi</h2>
                        <p class="text-slate-600 mb-8 leading-relaxed">
                            Máte dotaz k náboru, tréninkům nebo zápasům? Jsme tu pro vás.
                        </p>
                    </div>

                    <div class="space-y-6">
                        @if($branding['contact']['address'] ?? null)
                            <div class="flex items-start">
                                <div class="w-12 h-12 rounded-club bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-xs text-slate-400 mb-1">Adresa</h4>
                                    <address class="not-italic font-bold text-secondary tracking-tight">
                                        {!! nl2br(e($branding['contact']['address'])) !!}
                                    </address>
                                </div>
                            </div>
                        @endif

                        @if($branding['contact']['email'] ?? null)
                            <div class="flex items-start">
                                <div class="w-12 h-12 rounded-club bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-xs text-slate-400 mb-1">Email</h4>
                                    <a href="mailto:{{ $branding['contact']['email'] }}" class="font-bold text-secondary tracking-tight hover:text-primary transition-colors">
                                        {{ $branding['contact']['email'] }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($branding['contact']['phone'] ?? null)
                            <div class="flex items-start">
                                <div class="w-12 h-12 rounded-club bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-xs text-slate-400 mb-1">Telefon</h4>
                                    <a href="tel:{{ str_replace(' ', '', $branding['contact']['phone']) }}" class="font-bold text-secondary tracking-tight hover:text-primary transition-colors">
                                        {{ $branding['contact']['phone'] }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Social Icons -->
                    <div class="pt-8 border-t border-slate-200">
                        <h4 class="font-black uppercase tracking-widest text-xs text-slate-400 mb-4">Sledujte nás</h4>
                        <div class="flex space-x-4">
                            @foreach(['facebook', 'instagram', 'youtube'] as $social)
                                @if($branding['socials'][$social] ?? null)
                                    <a href="{{ $branding['socials'][$social] }}" class="w-10 h-10 rounded-full bg-secondary text-white flex items-center justify-center hover:bg-primary transition-colors" target="_blank">
                                        <span class="sr-only">{{ ucfirst($social) }}</span>
                                        <div class="font-black text-xs">{{ strtoupper(substr($social, 0, 1)) }}</div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Map & Form Column -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- Interactive Map Placeholder -->
                    <div class="card h-[400px] bg-slate-100 relative overflow-hidden flex items-center justify-center border-2 border-slate-200">
                        <div class="text-center p-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <h3 class="text-xl font-black uppercase tracking-tight text-slate-400 mb-2">Interaktivní mapa</h3>
                            <p class="text-slate-400 text-sm">Zde se zobrazí mapa s polohou naší haly.</p>
                        </div>
                    </div>

                    <!-- Simple Contact Info / CTA -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-primary text-white p-8 rounded-club shadow-lg relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/10 rounded-full"></div>
                            <h3 class="text-xl font-black uppercase tracking-tight mb-2">Chcete hrát?</h3>
                            <p class="text-white/80 text-sm mb-6">Pořádáme nábor nových hráčů do všech kategorií. Přijďte si vyzkoušet trénink zdarma!</p>
                            <a href="#" class="inline-block font-black uppercase tracking-widest text-xs border-b-2 border-white pb-1 hover:text-secondary hover:border-secondary transition-colors">Více informací &rarr;</a>
                        </div>
                        <div class="bg-secondary text-white p-8 rounded-club shadow-lg relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/10 rounded-full"></div>
                            <h3 class="text-xl font-black uppercase tracking-tight mb-2">Napište nám</h3>
                            <p class="text-white/80 text-sm mb-6">Máte-li jakýkoliv jiný dotaz, napište nám na email nebo zavolejte.</p>
                            <a href="mailto:{{ $branding['contact']['email'] ?? '' }}" class="inline-block font-black uppercase tracking-widest text-xs border-b-2 border-white pb-1 hover:text-primary hover:border-primary transition-colors">Poslat email &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
