@extends('layouts.member', [
    'title' => $title ?? __('nav.matches_statistics'),
])

@section('content')
    <div class="space-y-10">
        @livewire('public.team-season-stats')
    </div>
@endsection
