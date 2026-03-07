@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('recruitment.join.title')"
        :subtitle="__('recruitment.join.subtitle')"
        image="assets/img/hero/hero-trainings.webp"
    />

    <section class="section-padding bg-slate-50 min-h-[40vh] flex items-center">
        <div class="container max-w-3xl">
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative group p-8 md:p-12">
            <!-- Dekorativní prvky na pozadí -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/5 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-primary/5 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>

            <div class="relative">
                @livewire('recruitment-form', ['team' => $team])
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('public.recruitment.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-colors flex items-center justify-center gap-2">
                <i class="fa-light fa-arrow-left"></i>
                {{ __('recruitment.join.back_link') }}
            </a>
        </div>
    </div>
</section>
@endsection
