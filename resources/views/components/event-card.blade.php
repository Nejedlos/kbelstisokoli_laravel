@props(['event'])

<div class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
    <div class="flex flex-col md:flex-row">
        <!-- Date Badge -->
        <div class="bg-slate-50 md:w-32 flex flex-col items-center justify-center py-6 border-b md:border-b-0 md:border-r border-slate-100 group-hover:bg-primary/5 transition-colors">
            <span class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1">{{ $event->starts_at->translatedFormat('M') }}</span>
            <span class="text-3xl font-black text-secondary group-hover:text-primary transition-colors">{{ $event->starts_at->format('d') }}</span>
            <span class="text-[10px] font-bold text-slate-500 mt-1">{{ $event->starts_at->translatedFormat('l') }}</span>
        </div>

        <!-- Content -->
        <div class="flex-1 p-6">
            <div class="flex flex-wrap items-center gap-3 mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500">
                    {{ __('events.type_' . $event->event_type) }}
                </span>

                @foreach($event->teams as $team)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-primary/10 text-primary">
                        {{ $team->name }}
                    </span>
                @endforeach
            </div>

            <h3 class="text-xl font-black text-secondary group-hover:text-primary transition-colors mb-2">
                <a href="{{ route('public.events.show', $event->id) }}">
                    {{ $event->getTranslation('title', app()->getLocale()) }}
                </a>
            </h3>

            <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-slate-500 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-light fa-clock text-primary"></i>
                    <span>{{ $event->starts_at->format('H:i') }} @if($event->ends_at) — {{ $event->ends_at->format('H:i') }} @endif</span>
                </div>

                @if($event->location)
                <div class="flex items-center gap-2">
                    <i class="fa-light fa-location-dot text-primary"></i>
                    <span>{{ $event->location }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Action -->
        <div class="p-6 flex items-center justify-center md:border-l border-slate-100">
            <a href="{{ route('public.events.show', $event->id) }}" class="flex items-center justify-center w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                <i class="fa-light fa-arrow-right text-lg"></i>
            </a>
        </div>
    </div>
</div>
