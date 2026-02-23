@extends('layouts.public')

@section('content')
    <x-page-header
        title="Historie klubu"
        subtitle="Sledujte cestu našeho basketbalového oddílu od jeho založení až po současnost."
        :breadcrumbs="['Historie' => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            <x-empty-state
                title="Historie se připravuje"
                subtitle="Aktuálně dáváme dohromady archivní materiály a vzpomínky pamětníků."
            />
        </div>
    </div>
@endsection
