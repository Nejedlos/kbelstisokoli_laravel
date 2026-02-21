@extends('layouts.member', [
    'title' => 'Můj profil',
    'subtitle' => 'Zde si můžete upravit své kontaktní údaje a nastavení účtu.'
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Main Edit Form -->
        <div class="lg:col-span-2 space-y-8">
            <form action="{{ route('member.profile.update') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Basic Info -->
                <section class="card p-6 md:p-8 space-y-6">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-4">Základní údaje</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="text-xs font-black uppercase tracking-widest text-slate-400">Jméno a příjmení</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                            @error('name') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400">E-mailová adresa</label>
                            <div class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-club font-bold text-slate-500 cursor-not-allowed">
                                {{ $user->email }}
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium italic">E-mail nelze v profilu měnit. Kontaktujte administrátora.</p>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-xs font-black uppercase tracking-widest text-slate-400">Telefonní číslo</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="+420 ..."
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                            @error('phone') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($profile)
                            <div class="space-y-2">
                                <label for="jersey_number" class="text-xs font-black uppercase tracking-widest text-slate-400">Číslo dresu</label>
                                <input type="text" name="jersey_number" id="jersey_number" value="{{ old('jersey_number', $profile->jersey_number) }}"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                                @error('jersey_number') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    @if($profile)
                        <div class="space-y-2">
                            <label for="public_bio" class="text-xs font-black uppercase tracking-widest text-slate-400">Veřejné Bio / O mně</label>
                            <textarea name="public_bio" id="public_bio" rows="4"
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-medium text-secondary">{{ old('public_bio', $profile->public_bio) }}</textarea>
                            <p class="text-[10px] text-slate-400 font-medium">Tento text se může zobrazit na webu u vašeho profilu.</p>
                            @error('public_bio') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </section>

                <!-- Password Change -->
                <section class="card p-6 md:p-8 space-y-6">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-4">Změna hesla</h3>
                    <p class="text-xs text-slate-500 font-medium">Pokud si nepřejete měnit heslo, ponechte tato pole prázdná.</p>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="current_password" class="text-xs font-black uppercase tracking-widest text-slate-400">Stávající heslo</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                            @error('current_password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="new_password" class="text-xs font-black uppercase tracking-widest text-slate-400">Nové heslo</label>
                                <input type="password" name="new_password" id="new_password"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                                @error('new_password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label for="new_password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400">Potvrzení nového hesla</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Two Factor Authentication -->
                <section class="card p-6 md:p-8 space-y-6 border-l-4 {{ $user->two_factor_secret ? 'border-l-success' : 'border-l-warning' }}">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-black uppercase tracking-tight text-secondary">Dvoufázové ověření (2FA)</h3>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $user->two_factor_secret ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700' }}">
                            {{ $user->two_factor_secret ? 'Aktivní' : 'Neaktivní' }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-600 font-medium leading-relaxed">
                            Dvoufázové ověření přidává další vrstvu zabezpečení k vašemu účtu. Při přihlášení budete muset zadat ověřovací kód z mobilní aplikace.
                            @if($user->can('access_admin'))
                                <span class="text-danger-600 font-bold block mt-2">Důležité: Jako administrátor musíte mít 2FA aktivní pro přístup do správy klubu.</span>
                            @endif
                        </p>

                        @if(! $user->two_factor_secret)
                            {{-- Enable 2FA --}}
                            <form method="POST" action="{{ route('two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary py-2 px-6 text-sm">
                                    Aktivovat 2FA
                                </button>
                            </form>
                        @else
                            {{-- 2FA Setup Flow (Confirming) --}}
                            @if($user->two_factor_confirmed_at)
                                {{-- Show Recovery Codes --}}
                                <div class="space-y-4 pt-4">
                                    <div class="flex flex-wrap gap-4">
                                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline py-2 px-4 text-xs">
                                                Regenerovat záchranné kódy
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('two-factor.disable') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-danger-50 text-danger-600 hover:bg-danger-100 py-2 px-4 text-xs uppercase tracking-widest font-black">
                                                Deaktivovat 2FA
                                            </button>
                                        </form>
                                    </div>

                                    @if(session('status') == 'two-factor-authentication-enabled' || session('status') == 'recovery-codes-generated')
                                        <div class="bg-slate-900 rounded-club p-6 mt-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-primary mb-4">Vaše záchranné kódy</p>
                                            <p class="text-[10px] text-slate-400 mb-4 font-medium italic">Uložte si tyto kódy na bezpečné místo. Pomohou vám se přihlásit, pokud ztratíte přístup k aplikaci.</p>
                                            <div class="grid grid-cols-2 gap-2 font-mono text-sm text-white">
                                                @foreach ($user->recoveryCodes() as $code)
                                                    <div class="bg-white/5 px-3 py-1 rounded">{{ $code }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Confirming 2FA --}}
                                <div class="bg-slate-50 border border-slate-200 rounded-club p-6 space-y-6">
                                    <div class="flex flex-col md:flex-row gap-8 items-center">
                                        <div class="p-4 bg-white rounded-club shadow-sm border border-slate-100">
                                            {!! $user->twoFactorQrCodeSvg() !!}
                                        </div>
                                        <div class="space-y-4 flex-1">
                                            <h4 class="font-black uppercase tracking-tight text-secondary text-sm">Dokončení nastavení</h4>
                                            <ol class="text-xs text-slate-600 space-y-2 list-decimal list-inside font-medium">
                                                <li>Nainstalujte si aplikaci (např. Google Authenticator).</li>
                                                <li>Naskenujte tento QR kód ve vaší aplikaci.</li>
                                                <li>Opište 6místný kód, který se vám v aplikaci zobrazil.</li>
                                            </ol>
                                            <div class="pt-2">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Ruční klíč:</p>
                                                <code class="text-xs font-bold text-secondary bg-slate-200 px-2 py-1 rounded break-all">{{ decrypt($user->two_factor_secret) }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex items-end gap-4 max-w-sm border-t border-slate-200 pt-6">
                                        @csrf
                                        <div class="flex-1 space-y-2">
                                            <label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ověřovací kód</label>
                                            <input id="code" type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code"
                                                   class="w-full px-4 py-2 bg-white border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none tracking-widest text-center">
                                        </div>
                                        <button type="submit" class="btn btn-primary py-2 px-6 text-sm uppercase tracking-widest">
                                            Potvrdit
                                        </button>
                                    </form>
                                    @error('code') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        @endif
                    </div>
                </section>

                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-primary px-12">
                        Uložit změny
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-8">
            <!-- Player Card Summary -->
            @if($profile)
                <div class="card bg-secondary text-white overflow-hidden">
                    <div class="p-8 text-center border-b border-white/10">
                        <div class="w-24 h-24 rounded-full bg-primary flex items-center justify-center text-4xl font-black mx-auto mb-4 border-4 border-white/10">
                            {{ $profile->jersey_number ?: '#' }}
                        </div>
                        <h4 class="text-2xl font-black uppercase tracking-tight">{{ $user->name }}</h4>
                        <span class="text-xs font-black uppercase tracking-[0.2em] text-primary">{{ $profile->position ?: 'Hráč' }}</span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between text-xs border-b border-white/10 pb-2">
                            <span class="font-black uppercase tracking-widest text-white/40 text-[9px]">Moje týmy</span>
                            <span class="font-bold">{{ $profile->teams->pluck('name')->implode(', ') ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="font-black uppercase tracking-widest text-white/40 text-[9px]">Status</span>
                            <span class="font-bold text-success-400">Aktivní člen</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help box -->
            <div class="bg-white rounded-club p-6 border border-slate-200 shadow-sm">
                <h4 class="text-sm font-black uppercase tracking-tight text-secondary mb-4">Potřebujete pomoc?</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">
                    Pokud potřebujete změnit citlivé údaje (jako je role v klubu nebo přiřazení k týmu), obraťte se prosím na své trenéry nebo vedení klubu.
                </p>
            </div>
        </div>
    </div>
@endsection
