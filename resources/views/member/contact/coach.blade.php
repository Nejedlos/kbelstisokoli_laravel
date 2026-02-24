@extends('layouts.member', [
    'title' => __('member.feedback.contact_coach_title'),
    'subtitle' => __('member.feedback.contact_coach_subtitle')
])

@section('content')
    <div class="max-w-4xl mx-auto">
        @if (session('status'))
            <div class="mb-6 p-4 rounded-club bg-success-50 text-success-700 border border-success-200 text-sm animate-fade-in">
                <i class="fa-light fa-circle-check mr-1.5"></i> {{ session('status') }}
            </div>
        @endif

        <div class="card sport-card-accent p-6 md:p-10">
            <form action="{{ route('member.contact.coach.send') }}" method="POST" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        @if($teams->count() === 0)
                            <div class="p-4 rounded-club bg-warning-50 text-warning-800 border border-warning-200 text-sm">
                                <i class="fa-light fa-triangle-exclamation mr-1.5"></i> {{ __('member.feedback.no_team_warning') }}
                            </div>
                        @elseif($teams->count() === 1)
                            <input type="hidden" name="team_id" value="{{ $teams->first()->id }}">
                            <div class="space-y-2">
                                <div class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.team') }}</div>
                                <div class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-club font-bold text-secondary flex items-center gap-3">
                                    <i class="fa-light fa-users text-primary shrink-0"></i>
                                    {{ $teams->first()->name }}
                                </div>
                            </div>
                        @else
                            <div class="space-y-2">
                                <label for="team_id" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.team') }}</label>
                                <div class="relative">
                                    <select id="team_id" name="team_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary appearance-none">
                                        <option value="">-- {{ __('member.feedback.choose_team') }} --</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                        <i class="fa-light fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                @error('team_id')
                                    <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label for="subject" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.subject') }}</label>
                            <input id="subject" type="text" name="subject" value="{{ old('subject') }}"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary"
                                   placeholder="Např. Omluva z tréninku, dotaz na platbu..."
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
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-3">
                            @if($teams->count() === 1)
                                @if(!empty($coachContacts) && count($coachContacts) > 0)
                                    <div class="card p-4 border border-slate-200 space-y-3">
                                        <div class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.contact_card.coaches_title') }}</div>
                                        @foreach($coachContacts as $c)
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                                    @if(!empty($c['photo']))
                                                        <img src="{{ $c['photo'] }}" alt="coach" class="w-full h-full object-contain bg-white">
                                                    @else
                                                        <i class="fa-light fa-whistle text-xl text-slate-400"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-bold text-secondary">{{ $c['name'] }}</div>
                                                    <div class="text-xs text-slate-600 flex flex-wrap gap-4 mt-0.5">
                                                        <span>
                                                            <i class="fa-light fa-envelope text-primary mr-1"></i>
                                                            @if(!empty($c['email']))
                                                                <a href="mailto:{{ $c['email'] }}" class="font-bold hover:underline">{{ $c['email'] }}</a>
                                                            @else
                                                                <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                                            @endif
                                                        </span>
                                                        <span>
                                                            <i class="fa-light fa-phone text-primary mr-1"></i>
                                                            @if(!empty($c['phone']))
                                                                <a href="tel:{{ $c['phone'] }}" class="font-bold hover:underline">{{ $c['phone'] }}</a>
                                                            @else
                                                                <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="card p-4 border border-slate-200">
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                                @if(!empty($adminFallback['photo']))
                                                    <img src="{{ asset('storage/' . $adminFallback['photo']) }}" alt="admin" class="w-full h-full object-contain bg-white">
                                                @else
                                                    <i class="fa-light fa-user-gear text-2xl text-slate-400"></i>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.contact_card.admin_title') }}</div>
                                                <div class="font-bold text-secondary">{{ $adminFallback['name'] ?? __('member.feedback.contact_card.admin_name_default') }}</div>
                                                <div class="mt-1 text-sm text-slate-600 space-y-0.5">
                                                    <div>
                                                        <i class="fa-light fa-envelope text-primary mr-1.5"></i>
                                                        @if(!empty($adminFallback['email']))
                                                            <a href="mailto:{{ $adminFallback['email'] }}" class="font-bold hover:underline">{{ $adminFallback['email'] }}</a>
                                                        @else
                                                            <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <i class="fa-light fa-phone text-primary mr-1.5"></i>
                                                        @if(!empty($adminFallback['phone']))
                                                            <a href="tel:{{ $adminFallback['phone'] }}" class="font-bold hover:underline">{{ $adminFallback['phone'] }}</a>
                                                        @else
                                                            <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.contact_card.fallback_admin') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="p-4 rounded-club bg-slate-50 border border-slate-100 text-xs text-slate-600">
                                    <i class="fa-light fa-circle-info text-primary mr-1.5"></i>
                                    {{ __('member.feedback.contact_card.choose_team_hint') }}
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="message" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.message') }}</label>
                            <textarea id="message" name="message" rows="10"
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-medium text-secondary"
                                      placeholder="Zde napište svou zprávu pro trenéra..."
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
                            <i class="fa-light fa-paper-plane mr-2"></i> {{ __('member.feedback.send_to_coach') }}
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
