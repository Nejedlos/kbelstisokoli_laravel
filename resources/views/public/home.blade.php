@extends('layouts.public')

@section('content')
<div class="container">
    <h1>Vítejte na webu Kbelští sokoli</h1>
    <p>Toto je veřejná úvodní stránka (placeholder). Obsah bude doplněn později.</p>
    <p>
        <a href="{{ route('login') }}">Přihlásit se</a> |
        <a href="/admin">Administrace</a> |
        <a href="/clenska-sekce/dashboard">Členská sekce</a>
    </p>
</div>
@endsection
