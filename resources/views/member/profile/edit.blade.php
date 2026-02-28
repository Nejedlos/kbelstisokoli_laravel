@extends('layouts.member', [
    'title' => __('member.profile.title'),
    'subtitle' => __('member.profile.subtitle')
])

@section('content')
    <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
        <livewire:member.avatar-modal />

        <!-- Sidebar Info (Shown first on mobile) -->
        <div class="w-full lg:w-1/3 order-1 lg:order-2 space-y-8">
            <!-- Avatar Management -->
            <section class="card p-6 sm:p-8 space-y-6 overflow-hidden relative">
                <div class="absolute top-0 right-0 p-4 opacity-5">
                    <i class="fa-light fa-user-circle text-6xl text-secondary"></i>
                </div>

                <h3 class="text-xl font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-5 relative z-10">{{ __('member.profile.avatar.title') }}</h3>

                <div class="flex flex-col xs:flex-row items-center gap-6 sm:gap-10 relative z-10">
                        <div class="relative group shrink-0"
                             x-data="{
                                 avatarUrl: '{{ $user->getAvatarUrl() }}',
                                 init() {
                                     window.addEventListener('avatarUpdated', (event) => {
                                         if (event.detail.userId == {{ $user->id }}) {
                                             this.avatarUrl = event.detail.url || '{{ $user->getAvatarUrl() }}';
                                         }
                                     });
                                 }
                             }"
                             @click="$dispatch('openAvatarModal', { userId: {{ $user->id }} })">
                            <!-- Outer Ring -->
                            <div class="absolute -inset-2.5 bg-gradient-to-tr from-primary to-accent rounded-full opacity-20 group-hover:opacity-40 blur-xl transition-opacity duration-700"></div>

                            <!-- Image Container -->
                            <div class="relative w-32 h-32 sm:w-36 sm:h-36 rounded-[2.5rem] overflow-hidden ring-4 ring-white shadow-2xl cursor-pointer bg-white group-hover:scale-[1.02] transition-all duration-500">
                                <img id="avatarPreview"
                                     :src="avatarUrl"
                                     alt="Avatar"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-secondary/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center text-white">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-1 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                    <i class="fa-light fa-camera-retro text-lg"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100">{{ __('member.profile.avatar.change') }}</span>
                            </div>
                        </div>

                        <!-- Badge -->
                        <div class="absolute -bottom-1.5 -right-1.5 w-10 h-10 bg-accent text-white rounded-2xl border-4 border-white flex items-center justify-center shadow-lg transform group-hover:rotate-12 transition-all group-hover:bg-primary">
                            <i class="fa-light fa-pen-nib text-xs"></i>
                        </div>

                        <!-- Delete Button -->
                        <button type="button"
                                id="avatar-delete-btn"
                                onclick="event.stopPropagation(); Livewire.dispatch('deleteAvatar', { userId: {{ $user->id }} })"
                                class="absolute -top-1.5 -right-1.5 w-10 h-10 bg-rose-500 text-white rounded-2xl border-4 border-white flex items-center justify-center shadow-lg hover:bg-rose-600 hover:scale-110 transition-all z-20"
                                x-show="avatarUrl && !avatarUrl.includes('default-avatar')">
                            <i class="fa-light fa-trash-can text-xs"></i>
                        </button>
                    </div>

                    <div class="flex-1 text-center xs:text-left space-y-4">
                        <div class="space-y-1.5">
                            <p class="text-[13px] text-slate-600 font-black leading-relaxed">
                                {{ __('member.profile.avatar.hint') }}
                            </p>
                            <p class="text-[11px] text-slate-400 font-bold italic opacity-80">
                                {{ __('member.profile.avatar.formats') }}
                            </p>
                        </div>

                        <button type="button"
                                @click="$dispatch('openAvatarModal', { userId: {{ $user->id }} })"
                                class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-primary hover:text-white text-secondary text-[10px] font-black uppercase tracking-widest transition-all shadow-sm hover:shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 w-full xs:w-auto justify-center">
                            <i class="fa-light fa-sparkles"></i>
                            {{ __('member.profile.avatar.gallery_open') }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- Player Card Summary (Enhanced Sexy Style) -->
            @if($profile)
                <div class="relative overflow-hidden group rounded-[2.5rem] bg-white border border-slate-200/60 shadow-xl shadow-slate-200/40 transition-all duration-700 group-hover:shadow-primary/5 group-hover:border-primary/20">
                    <!-- Premium Background Decor -->
                    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: var(--pattern-basketball);"></div>

                    <!-- Glow Effects -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-[80px] -mr-32 -mt-32 transition-opacity opacity-50 group-hover:opacity-100"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-secondary/5 rounded-full blur-[60px] -ml-24 -mb-24"></div>

                    <!-- Header Section -->
                    <div class="relative p-8 sm:p-10 text-center border-b border-slate-100/60">
                        <!-- Jersey Number Badge -->
                        <div class="relative inline-block mb-6 sm:mb-8">
                            <div class="absolute -inset-4 bg-primary/10 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                            <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-[2rem] bg-gradient-to-tr from-primary to-accent flex items-center justify-center text-4xl sm:text-5xl font-black italic text-white border-4 border-white shadow-2xl transform group-hover:rotate-6 transition-all duration-500">
                                {{ $profile->jersey_number ?: '#' }}
                            </div>
                        </div>

                        <h4 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-secondary mb-2 leading-none">{{ $user->name }}</h4>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-full border border-slate-100">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary italic">{{ $profile->position ?: __('member.profile.player_card.position_player') }}</span>
                        </div>
                    </div>

                    <!-- Stats / Details -->
                    <div class="relative p-6 sm:p-8 space-y-6 bg-slate-50/30 backdrop-blur-sm">
                        <div class="flex justify-between items-center text-xs border-b border-slate-100 pb-5">
                            <div class="space-y-2 w-full">
                                <span class="font-black uppercase tracking-[0.2em] text-slate-400 text-[9px] block">{{ __('member.profile.player_card.my_teams') }}</span>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($profile->teams as $team)
                                        <span class="font-bold text-secondary bg-white px-2.5 py-1 rounded-lg text-[10px] border border-slate-200 shadow-sm">{{ $team->name }}</span>
                                    @empty
                                        <span class="font-bold text-slate-400 italic text-[10px]">-</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="font-black uppercase tracking-[0.2em] text-slate-400 text-[9px]">{{ __('member.profile.player_card.status') }}</span>
                            <div class="px-3 sm:px-4 py-1.5 rounded-xl bg-emerald-500/5 border border-emerald-500/10 shadow-sm">
                                <span class="font-black text-emerald-600 uppercase tracking-widest text-[9px] sm:text-[10px] flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    {{ __('member.profile.player_card.active_member') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help box -->
            <div class="bg-white/50 backdrop-blur-sm rounded-[2.5rem] p-6 sm:p-8 border border-slate-200/60 shadow-sm group">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-light fa-circle-question text-lg"></i>
                    </div>
                    <h4 class="text-xs font-black uppercase tracking-widest text-secondary">{{ __('member.profile.help.title') }}</h4>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed font-bold italic opacity-80">
                    {{ __('member.profile.help.text') }}
                </p>
            </div>
        </div>

        <!-- Main Edit Form -->
        <div class="w-full lg:w-2/3 order-2 lg:order-1 space-y-8">
            <form action="{{ route('member.profile.update') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Basic Info -->
                <section class="card p-6 sm:p-8 space-y-6">
                    <h3 class="text-xl font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-5 leading-none">{{ __('member.profile.basic_info') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
                        <div class="space-y-2">
                            <label for="name" class="form-label">{{ __('member.profile.name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-input min-h-[44px]">
                            @error('name') <p class="text-[10px] text-danger-600 font-bold mt-1.5 uppercase tracking-wider italic">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="form-label">{{ __('member.profile.email') }}</label>
                            <div class="px-5 py-3 bg-slate-100 border border-slate-200 rounded-2xl font-bold text-slate-500 cursor-not-allowed opacity-70 min-h-[44px] flex items-center">
                                {{ $user->email }}
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium italic mt-1.5 opacity-80 leading-tight">{{ __('member.profile.email_help') }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="form-label">{{ __('member.profile.phone') }}</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="+420 ..." class="form-input min-h-[44px]">
                            @error('phone') <p class="text-[10px] text-danger-600 font-bold mt-1.5 uppercase tracking-wider italic">{{ $message }}</p> @enderror
                        </div>

                        @if($profile)
                            <div class="space-y-2">
                                <label for="jersey_number" class="form-label">{{ __('member.profile.jersey_number') }}</label>
                                <input type="text" name="jersey_number" id="jersey_number" value="{{ old('jersey_number', $profile->jersey_number) }}" class="form-input min-h-[44px]">
                                @error('jersey_number') <p class="text-[10px] text-danger-600 font-bold mt-1.5 uppercase tracking-wider italic">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    @if($profile)
                        <div class="space-y-2">
                            <label for="public_bio" class="form-label">{{ __('member.profile.bio') }}</label>
                            <textarea name="public_bio" id="public_bio" rows="4" class="form-input min-h-[120px]">{{ old('public_bio', $profile->public_bio) }}</textarea>
                            <p class="text-[10px] text-slate-400 font-medium italic mt-1.5 opacity-80 leading-tight">{{ __('member.profile.bio_help') }}</p>
                            @error('public_bio') <p class="text-[10px] text-danger-600 font-bold mt-1.5 uppercase tracking-wider italic">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </section>

                <!-- Password Change -->
                <section class="card p-6 sm:p-8 space-y-6">
                    <h3 class="text-xl font-black uppercase tracking-tight text-secondary border-b border-slate-100 pb-5 leading-none">{{ __('member.profile.password_change') }}</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed italic opacity-80">{{ __('member.profile.password_help') }}</p>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="current_password" class="form-label">{{ __('member.profile.current_password') }}</label>
                            <input type="password" name="current_password" id="current_password" class="form-input min-h-[44px]">
                            @error('current_password') <p class="text-[10px] text-danger-600 font-bold mt-1.5 uppercase tracking-wider italic">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
                            <div class="space-y-2">
                                <label for="new_password" class="form-label">{{ __('member.profile.new_password') }}</label>
                                <input type="password" name="new_password" id="new_password" class="form-input min-h-[44px]">
                                @error('new_password') <p class="text-[10px] text-danger-600 font-bold mt-1.5 uppercase tracking-wider italic">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label for="new_password_confirmation" class="form-label">{{ __('member.profile.confirm_new_password') }}</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-input min-h-[44px]">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Two Factor Authentication -->
                <section class="card p-6 sm:p-8 space-y-6 border-l-4 {{ $user->two_factor_secret ? 'border-l-emerald-500' : 'border-l-warning' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-5 gap-4">
                        <h3 class="text-xl font-black uppercase tracking-tight text-secondary leading-none">{{ __('member.profile.two_factor.title') }}</h3>
                        <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm self-start sm:self-center {{ $user->two_factor_secret ? 'bg-emerald-50 text-emerald-700' : 'bg-warning-50 text-warning-700' }}">
                            <div class="inline-block w-1.5 h-1.5 rounded-full mr-2 {{ $user->two_factor_secret ? 'bg-emerald-500 animate-pulse' : 'bg-warning-500' }}"></div>
                            {{ $user->two_factor_secret ? __('member.profile.two_factor.active') : __('member.profile.two_factor.inactive') }}
                        </span>
                    </div>

                    <div class="space-y-6">
                        <p class="text-sm text-slate-600 font-medium leading-relaxed italic opacity-80">
                            {{ __('member.profile.two_factor.help') }}
                            @if($user->can('access_admin'))
                                <span class="text-primary font-black block mt-3 uppercase tracking-wider text-[11px] non-italic">{{ __('member.profile.two_factor.admin_warning') }}</span>
                            @endif
                        </p>

                        @if(! $user->two_factor_secret)
                            {{-- Enable 2FA --}}
                            <form method="POST" action="{{ route('two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary px-8 py-3 w-full sm:w-auto">
                                    <i class="fa-light fa-shield-check mr-2"></i> {{ __('member.profile.two_factor.enable') }}
                                </button>
                            </form>
                        @else
                            {{-- 2FA Setup Flow (Confirming) --}}
                            @if($user->two_factor_confirmed_at)
                                {{-- Show Recovery Codes --}}
                                <div class="space-y-6 pt-2">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="w-full sm:w-auto">
                                            @csrf
                                            <button type="submit" class="btn btn-outline py-2.5 px-5 w-full justify-center">
                                                <i class="fa-light fa-arrows-rotate mr-2 text-primary"></i> {{ __('member.profile.two_factor.regenerate_codes') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('two-factor.disable') }}" class="w-full sm:w-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-rose-50 text-rose-600 hover:bg-rose-100 py-2.5 px-5 border-transparent w-full justify-center font-bold">
                                                <i class="fa-light fa-trash-can mr-2"></i> {{ __('member.profile.two_factor.disable') }}
                                            </button>
                                        </form>
                                    </div>

                                    @if(session('status') == 'two-factor-authentication-enabled' || session('status') == 'recovery-codes-generated')
                                        <div class="bg-slate-900 rounded-[2rem] p-6 sm:p-8 mt-6 relative overflow-hidden group shadow-2xl">
                                            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:rotate-12 transition-transform duration-700">
                                                <i class="fa-light fa-shield-keyhole text-7xl text-white"></i>
                                            </div>
                                            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-primary mb-5 relative z-10">{{ __('member.profile.two_factor.recovery_codes_title') }}</p>
                                            <p class="text-[11px] text-slate-400 mb-6 font-medium italic leading-relaxed relative z-10">{{ __('member.profile.two_factor.recovery_codes_help') }}</p>
                                            <div class="grid grid-cols-1 xs:grid-cols-2 gap-3 font-mono text-sm text-white relative z-10">
                                                @foreach ($user->recoveryCodes() as $code)
                                                    <div class="bg-white/5 hover:bg-white/10 px-4 py-2.5 rounded-xl border border-white/5 transition-colors text-center tracking-widest text-xs sm:text-sm">{{ $code }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Confirming 2FA --}}
                                <div class="bg-slate-50 border border-slate-200/60 rounded-[2rem] p-6 sm:p-8 space-y-8 shadow-inner">
                                    <div class="flex flex-col md:flex-row gap-8 sm:gap-10 items-center">
                                        <div class="p-6 bg-white rounded-[2rem] shadow-xl border border-slate-100 flex-shrink-0">
                                            {!! $user->twoFactorQrCodeSvg() !!}
                                        </div>
                                        <div class="space-y-5 flex-1 text-center md:text-left">
                                            <h4 class="font-black uppercase tracking-tight text-secondary text-lg leading-tight">{{ __('member.profile.two_factor.setup_title') }}</h4>
                                            <ol class="text-xs text-slate-500 space-y-3 list-decimal list-inside font-bold italic opacity-80 leading-relaxed text-left">
                                                <li>{{ __('member.profile.two_factor.setup_step_1') }}</li>
                                                <li>{{ __('member.profile.two_factor.setup_step_2') }}</li>
                                                <li>{{ __('member.profile.two_factor.setup_step_3') }}</li>
                                            </ol>
                                            <div class="pt-4 border-t border-slate-200/60 text-left">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('member.profile.two_factor.manual_key') }}:</p>
                                                <code class="text-xs font-black text-primary bg-primary/5 border border-primary/10 px-3 py-1.5 rounded-lg break-all tracking-widest">{{ decrypt($user->two_factor_secret) }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex flex-col sm:flex-row items-end gap-5 max-w-md mx-auto md:mx-0 border-t border-slate-200 pt-8">
                                        @csrf
                                        <div class="w-full sm:flex-1 space-y-2">
                                            <label for="code" class="form-label text-center sm:text-left">{{ __('member.profile.two_factor.code_label') }}</label>
                                            <input id="code" type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code"
                                                   class="form-input text-center tracking-[0.3em] sm:tracking-[0.5em] text-lg sm:text-xl py-4" placeholder="000 000">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-full sm:w-auto h-[60px] px-8">
                                            {{ __('member.profile.two_factor.confirm_button') }}
                                        </button>
                                    </form>
                                    @error('code') <p class="text-[10px] text-danger-600 font-bold mt-2 uppercase tracking-wider text-center md:text-left">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        @endif
                    </div>
                </section>

                <div class="flex items-center justify-center sm:justify-end">
                    <button type="submit" class="btn btn-primary px-12 py-4 w-full sm:w-auto shadow-xl shadow-primary/20">
                        {{ __('member.profile.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
