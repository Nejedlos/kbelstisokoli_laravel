@props(['data'])

@php
    $component = $data['component'] ?? null;
    $parameters = $data['parameters'] ?? [];
@endphp

@if($component)
    <div @if($data['custom_id'] ?? null) id="{{ $data['custom_id'] }}" @endif class="py-12 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative group scroll-mt-24">
        <!-- Dekorativní prvky na pozadí -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/5 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-primary/5 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>

        <div class="max-w-3xl mx-auto px-6 relative">
            @livewire($component, $parameters)
        </div>
    </div>
@endif
