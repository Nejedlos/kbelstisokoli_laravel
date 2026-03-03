@extends('layouts.member', [
    'title' => $title ?? __('nav.players_statistics'),
])

@section('content')
    <div class="space-y-10">
        @livewire('member.my-statistics', ['view' => 'team'])
    </div>
@endsection
