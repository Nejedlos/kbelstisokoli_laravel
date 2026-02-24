@extends('layouts.member', [
    'title' => __('member.profile.title'),
    'subtitle' => __('member.profile.subtitle')
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Main Edit Form -->
        <div class="lg:col-span-2 space-y-8">
            <form action="{{ route('member.profile.update') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Basic Info -->
                <section class="card p-6 md:p-8 space-y-6">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-4">{{ __('member.profile.basic_info') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                            @error('name') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.email') }}</label>
                            <div class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-club font-bold text-slate-500 cursor-not-allowed">
                                {{ $user->email }}
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium italic">{{ __('member.profile.email_help') }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.phone') }}</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="+420 ..."
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                            @error('phone') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($profile)
                            <div class="space-y-2">
                                <label for="jersey_number" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.jersey_number') }}</label>
                                <input type="text" name="jersey_number" id="jersey_number" value="{{ old('jersey_number', $profile->jersey_number) }}"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary">
                                @error('jersey_number') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    @if($profile)
                        <div class="space-y-2">
                            <label for="public_bio" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.bio') }}</label>
                            <textarea name="public_bio" id="public_bio" rows="4"
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-medium text-secondary">{{ old('public_bio', $profile->public_bio) }}</textarea>
                            <p class="text-[10px] text-slate-400 font-medium">{{ __('member.profile.bio_help') }}</p>
                            @error('public_bio') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </section>

                <!-- Password Change -->
                <section class="card p-6 md:p-8 space-y-6">
                    <h3 class="text-lg font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-4">{{ __('member.profile.password_change') }}</h3>
                    <p class="text-xs text-slate-500 font-medium">{{ __('member.profile.password_help') }}</p>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="current_password" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.current_password') }}</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                            @error('current_password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="new_password" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.new_password') }}</label>
                                <input type="password" name="new_password" id="new_password"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                                @error('new_password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label for="new_password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.confirm_new_password') }}</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Two Factor Authentication -->
                <section class="card p-6 md:p-8 space-y-6 border-l-4 {{ $user->two_factor_secret ? 'border-l-success' : 'border-l-warning' }}">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.profile.two_factor.title') }}</h3>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $user->two_factor_secret ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700' }}">
                            {{ $user->two_factor_secret ? __('member.profile.two_factor.active') : __('member.profile.two_factor.inactive') }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-600 font-medium leading-relaxed">
                            {{ __('member.profile.two_factor.help') }}
                            @if($user->can('access_admin'))
                                <span class="text-danger-600 font-bold block mt-2">{{ __('member.profile.two_factor.admin_warning') }}</span>
                            @endif
                        </p>

                        @if(! $user->two_factor_secret)
                            {{-- Enable 2FA --}}
                            <form method="POST" action="{{ route('two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary py-2 px-6 text-sm">
                                    {{ __('member.profile.two_factor.enable') }}
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
                                                {{ __('member.profile.two_factor.regenerate_codes') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('two-factor.disable') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-danger-50 text-danger-600 hover:bg-danger-100 py-2 px-4 text-xs uppercase tracking-widest font-black">
                                                {{ __('member.profile.two_factor.disable') }}
                                            </button>
                                        </form>
                                    </div>

                                    @if(session('status') == 'two-factor-authentication-enabled' || session('status') == 'recovery-codes-generated')
                                        <div class="bg-slate-900 rounded-club p-6 mt-4">
                                            <p class="text-xs font-black uppercase tracking-widest text-primary mb-4">{{ __('member.profile.two_factor.recovery_codes_title') }}</p>
                                            <p class="text-[10px] text-slate-400 mb-4 font-medium italic">{{ __('member.profile.two_factor.recovery_codes_help') }}</p>
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
                                            <h4 class="font-black uppercase tracking-tight text-secondary text-sm">{{ __('member.profile.two_factor.setup_title') }}</h4>
                                            <ol class="text-xs text-slate-600 space-y-2 list-decimal list-inside font-medium">
                                                <li>{{ __('member.profile.two_factor.setup_step_1') }}</li>
                                                <li>{{ __('member.profile.two_factor.setup_step_2') }}</li>
                                                <li>{{ __('member.profile.two_factor.setup_step_3') }}</li>
                                            </ol>
                                            <div class="pt-2">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('member.profile.two_factor.manual_key') }}:</p>
                                                <code class="text-xs font-bold text-secondary bg-slate-200 px-2 py-1 rounded break-all">{{ decrypt($user->two_factor_secret) }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex items-end gap-4 max-w-sm border-t border-slate-200 pt-6">
                                        @csrf
                                        <div class="flex-1 space-y-2">
                                            <label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('member.profile.two_factor.code_label') }}</label>
                                            <input id="code" type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code"
                                                   class="w-full px-4 py-2 bg-white border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none tracking-widest text-center">
                                        </div>
                                        <button type="submit" class="btn btn-primary py-2 px-6 text-sm uppercase tracking-widest">
                                            {{ __('member.profile.two_factor.confirm_button') }}
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
                        {{ __('member.profile.save_changes') }}
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
                        <span class="text-xs font-black uppercase tracking-[0.2em] text-primary">{{ $profile->position ?: __('member.profile.player_card.position_player') }}</span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between text-xs border-b border-white/10 pb-2">
                            <span class="font-black uppercase tracking-widest text-white/40 text-[9px]">{{ __('member.profile.player_card.my_teams') }}</span>
                            <span class="font-bold">{{ $profile->teams->pluck('name')->implode(', ') ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="font-black uppercase tracking-widest text-white/40 text-[9px]">{{ __('member.profile.player_card.status') }}</span>
                            <span class="font-bold text-success-400">{{ __('member.profile.player_card.active_member') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help box -->
            <div class="bg-white rounded-club p-6 border border-slate-200 shadow-sm">
                <h4 class="text-sm font-black uppercase tracking-tight text-secondary mb-4">{{ __('member.profile.help.title') }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">
                    {{ __('member.profile.help.text') }}
                </p>
            </div>
        </div>
    </div>
@endsection
