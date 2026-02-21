@extends('layouts.public')

@section('content')
<section class="section-padding">
    <div class="container text-center">
        <div class="mb-8">
            <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-primary text-white text-3xl font-bold">404</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Stránka nenalezena</h1>
        <p class="text-slate-600 mb-8">Omlouváme se, ale požadovaná stránka neexistuje nebo byla přesunuta.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('public.home') }}" class="btn btn-primary">Domů</a>
            <a href="{{ route('public.news.index') }}" class="btn btn-outline">Novinky</a>
            <a href="{{ route('public.matches.index') }}" class="btn btn-outline">Zápasy</a>
            <a href="{{ route('public.contact.index') }}" class="btn btn-outline">Kontakt</a>
        </div>
    </div>
</section>
@endsection
