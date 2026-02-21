@extends('layouts.public')

@section('content')
<div class="container">
    <h2>Přihlášení</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" required autofocus />
        </div>
        <div>
            <label>Heslo</label>
            <input type="password" name="password" required />
        </div>
        <div>
            <button type="submit">Přihlásit se</button>
        </div>
        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
@endsection
