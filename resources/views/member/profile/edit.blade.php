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
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                            @error('current_password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="new_password" class="text-xs font-black uppercase tracking-widest text-slate-400">Nové heslo</label>
                                <input type="password" name="new_password" id="new_password"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                                @error('new_password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label for="new_password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400">Potvrzení nového hesla</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                            </div>
                        </div>
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
