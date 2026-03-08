@extends('layouts.member', [
    'title' => $title ?? __('nav.matches_statistics'),
])

@section('content')
    <div class="space-y-10">
        @livewire('member.my-statistics', ['view' => 'matches', 'teamId' => $activeTeamId])
    </div>
@endsection
