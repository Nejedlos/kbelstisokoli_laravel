@extends('layouts.public')

@section('content')
<section class="section-padding">
    <div class="container text-center">
        <div class="mb-8">
            <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-primary text-white text-3xl font-bold">410</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Obsah byl odstraněn</h1>
        <p class="text-slate-600 mb-8">Požadovaná stránka už není k dispozici. Vyberte si prosím jinou sekci.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ url('/') }}" class="btn btn-primary">Domů</a>
            <a href="{{ url('/novinky') }}" class="btn btn-outline">Novinky</a>
            <a href="{{ url('/zapasy') }}" class="btn btn-outline">Zápasy</a>
        </div>
    </div>
</section>
@endsection
