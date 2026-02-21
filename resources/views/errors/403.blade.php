@extends('layouts.public')

@section('content')
<section class="section-padding">
    <div class="container text-center">
        <div class="mb-8">
            <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-primary text-white text-3xl font-bold">403</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Přístup odepřen</h1>
        <p class="text-slate-600 mb-8">Pro tuto stránku nemáte potřebná oprávnění.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary">Přihlásit se</a>
            @endguest
            <a href="{{ route('public.home') }}" class="btn btn-outline">Domů</a>
        </div>
    </div>
</section>
@endsection
