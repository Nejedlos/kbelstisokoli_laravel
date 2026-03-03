@extends('layouts.member', [
    'title' => $title ?? __('nav.my_statistics'),
])

@section('content')
    <div class="space-y-10">
        @livewire('member.my-statistics', ['view' => 'personal'])
    </div>
@endsection
