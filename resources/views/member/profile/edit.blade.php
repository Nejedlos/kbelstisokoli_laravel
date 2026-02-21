@extends('layouts.member')

@section('content')
<div class="container">
    <h1>Můj profil</h1>
    <p>Uživatelský profil (placeholder).</p>
    <form method="post" action="{{ route('member.profile.update') }}">
        @csrf
        <button type="submit">Uložit (placeholder)</button>
    </form>
</div>
@endsection
