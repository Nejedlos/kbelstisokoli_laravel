@extends('layouts.member')

@section('content')
<div class="container">
    <h1>Vítejte v členské sekci</h1>
    <p>Jste přihlášen jako: {{ auth()->user()->name }}</p>
    <p>Vaše role: {{ auth()->user()->getRoleNames()->implode(', ') }}</p>

    <div class="dashboard-grid">
        <div class="card">
            <h3>Moje docházka</h3>
            <p>Placeholder pro statistiku docházky.</p>
        </div>
        <div class="card">
            <h3>Moje týmy</h3>
            <p>Placeholder pro seznam týmů.</p>
        </div>
    </div>
</div>
@endsection
