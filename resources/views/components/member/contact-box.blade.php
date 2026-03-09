@props([
    'title' => null,
    'text' => null,
    'icon' => 'whistle',
    'coachRoute' => route('member.contact.coach.form'),
    'adminRoute' => route('member.contact.admin.form'),
    'type' => 'general' // 'general', 'economy', 'profile', 'sidebar'
])

@php
    $title = $title ?? match($type) {
        'economy' => __('member.feedback.contact_coach_title'),
        'profile' => __('member.profile.help.title'),
        'sidebar' => __('nav.need_help'),
        default => __('member.feedback.contact_coach_title')
    };

    $text = $text ?? match($type) {
        'economy' => __('member.feedback.hints.economy'),
        'profile' => __('member.feedback.hints.profile'),
        'sidebar' => __('nav.help_text'),
        default => __('member.feedback.hints.profile')
    };

    $icon = match($type) {
        'economy' => 'whistle',
        'profile' => 'user-gear',
        'sidebar' => 'headset',
        default => $icon
    };

    $isSidebar = $type === 'sidebar';
@endphp

<div {{ $attributes->merge(['class' => 'relative group ' . ($isSidebar ? 'rounded-2xl' : 'rounded-[2.5rem]')]) }}>
    <!-- Background with shadow -->
    <div class="absolute inset-0 bg-gradient-to-br from-white to-slate-50/50 {{ $isSidebar ? 'rounded-2xl' : 'rounded-[2.5rem]' }} border border-slate-200/60 shadow-sm transition-all duration-500 group-hover:shadow-md group-hover:border-primary/10"></div>

    <!-- Decorative background icon clipped -->
    <div class="absolute inset-0 {{ $isSidebar ? 'rounded-2xl' : 'rounded-[2.5rem]' }} overflow-hidden pointer-events-none">
        <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700">
            <i class="fa-light fa-{{ $icon }} {{ $isSidebar ? 'text-6xl' : 'text-8xl' }} text-secondary"></i>
        </div>
    </div>

    <div class="relative {{ $isSidebar ? 'p-5 space-y-4' : 'p-6 z-10 space-y-6' }}">
        <!-- Icon and Text -->
        <div class="flex items-start {{ $isSidebar ? 'gap-3' : 'gap-4' }}">
            <div class="{{ $isSidebar ? 'w-8 h-8 rounded-lg' : 'w-12 h-12 rounded-2xl' }} bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm shadow-primary/5 group-hover:scale-110 group-hover:rotate-3 transition-transform">
                <i class="fa-light fa-{{ $icon }} {{ $isSidebar ? 'text-sm' : 'text-xl' }}"></i>
            </div>
            <div class="{{ $isSidebar ? 'space-y-1' : 'space-y-1.5' }} flex-1">
                <h4 class="{{ $isSidebar ? 'text-[11px]' : 'text-sm' }} font-black uppercase tracking-tight text-secondary leading-tight">{{ $title }}</h4>
                <p class="{{ $isSidebar ? 'text-[10px]' : 'text-[11px]' }} text-slate-500 font-medium leading-relaxed italic opacity-80">{{ $text }}</p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="grid grid-cols-1 {{ $isSidebar ? '' : 'xs:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2' }} gap-3">
            <a href="{{ $coachRoute }}" class="btn btn-outline {{ $isSidebar ? 'py-2 px-3' : 'py-2.5 px-4' }} text-[10px] bg-white hover:border-primary/30 hover:text-primary transition-all flex items-center justify-center gap-2">
                <i class="fa-light fa-comment-dots text-xs"></i>
                {{ __('member.feedback.contact_coach_title') }}
            </a>
            <a href="{{ $adminRoute }}" class="btn {{ $isSidebar ? 'py-2 px-3' : 'py-2.5 px-4' }} text-[10px] bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all border-none shadow-none flex items-center justify-center gap-2">
                <i class="fa-light fa-user-gear text-xs"></i>
                {{ __('member.feedback.contact_admin_title') }}
            </a>
        </div>
    </div>
</div>
