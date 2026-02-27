@extends('layouts.public')

@section('content')
<section class="section-padding bg-slate-50 min-h-[60vh] flex items-center">
    <div class="container max-w-3xl">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl mb-4">Chci hrát za C & E</h1>
            <p class="text-slate-500 max-w-xl mx-auto italic">
                Vyplňte krátkou žádost a my se vám brzy ozveme. Těšíme se na vás na palubovce!
            </p>
        </div>

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
                Zpět na informace o náboru
            </a>
        </div>
    </div>
</section>
@endsection
