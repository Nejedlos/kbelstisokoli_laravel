@extends('layouts.member', [
    'title' => __('member.feedback.contact_coach_title'),
    'subtitle' => __('member.feedback.contact_coach_subtitle')
])

@section('content')
    <livewire:member.coach-contact-form />
@endsection
