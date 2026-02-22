@extends('layouts.public')

@section('content')
    @if($page)
        <x-page-blocks :blocks="$page->content ?? []" animate="true" />
    @else
        <div class="container py-20 text-center">
            <h1 class="text-4xl font-bold mb-4">Vítejte na webu Kbelští sokoli</h1>
            <p class="text-xl text-gray-600 mb-8">Obsah úvodní stránky zatím nebyl vytvořen v administraci.</p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login') }}" class="btn btn-primary">Přihlásit se</a>
                <a href="/admin" class="btn btn-secondary">Administrace</a>
            </div>
        </div>
    @endif
@endsection
