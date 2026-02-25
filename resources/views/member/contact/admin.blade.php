@extends('layouts.member', [
    'title' => __('member.feedback.contact_admin_title'),
    'subtitle' => __('member.feedback.contact_admin_subtitle')
])

@section('content')
    <div class="max-w-4xl mx-auto">
        @if (session('status'))
            <div class="mb-6 p-4 rounded-club bg-success-50 text-success-700 border border-success-200 text-sm animate-fade-in">
                <i class="fa-light fa-circle-check mr-1.5"></i> {{ session('status') }}
            </div>
        @endif

        <div class="card sport-card-accent p-6 md:p-10">
            <form action="{{ route('member.contact.admin.send') }}" method="POST" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="subject" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.subject') }}</label>
                            <input id="subject" type="text" name="subject" value="{{ old('subject') }}"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary"
                                   placeholder="Např. Nefunkční tlačítko, problém s přihlášením..."
                                   required>
                            @error('subject')
                                <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-member.dropzone name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" :max-size-mb="10" />
                            @error('attachment')
                                <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="p-6 rounded-club bg-slate-50 border border-slate-100 space-y-3">
                            <div class="flex items-center gap-2 text-primary">
                                <i class="fa-light fa-circle-info"></i>
                                <span class="text-xs font-black uppercase tracking-widest">Kdy kontaktovat admina?</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                                Administrátora kontaktujte v případě technických potíží, chyb v systému nebo pokud potřebujete změnit nastavení svého účtu, které sami nemůžete upravit.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="card p-4 border border-slate-200">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                    @if(!empty($adminContact['photo']))
                                        <img src="{{ asset('storage/' . $adminContact['photo']) }}" alt="admin" class="w-full h-full object-contain bg-white">
                                    @else
                                        <i class="fa-light fa-user-gear text-2xl text-slate-400"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.contact_card.admin_title') }}</div>
                                    <div class="font-bold text-secondary">{{ $adminContact['name'] ?? __('member.feedback.contact_card.admin_name_default') }}</div>
                                    <div class="mt-1 text-sm text-slate-600 space-y-0.5">
                                        <div>
                                            <i class="fa-light fa-envelope text-primary mr-1.5"></i>
                                            @if(!empty($adminContact['email']))
                                                <x-mailto :email="$adminContact['email']" class="font-bold hover:underline" />
                                            @else
                                                <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <i class="fa-light fa-phone text-primary mr-1.5"></i>
                                            @if(!empty($adminContact['phone']))
                                                <a href="tel:{{ $adminContact['phone'] }}" class="font-bold hover:underline">{{ $adminContact['phone'] }}</a>
                                            @else
                                                <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="message" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.message') }}</label>
                            <textarea id="message" name="message" rows="10"
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-medium text-secondary"
                                      placeholder="Zde podrobně popište svůj požadavek nebo problém..."
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex flex-col lg:flex-row items-center justify-between gap-6">
                    <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto sm:min-w-[200px]" :class="{ 'is-loading': loading }">
                            <i class="fa-light fa-paper-plane mr-2"></i> {{ __('member.feedback.send_to_admin') }}
                        </button>
                        <a href="{{ route('member.dashboard') }}" class="btn btn-outline py-3 px-6 text-sm w-full sm:w-auto">
                            <i class="fa-light fa-arrow-left mr-2"></i> {{ __('general.back') }}
                        </a>
                    </div>

                    <div class="flex items-center gap-2 text-slate-400">
                        <i class="fa-light fa-shield-check text-success-500"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest italic">Zpráva bude bezpečně doručena</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
