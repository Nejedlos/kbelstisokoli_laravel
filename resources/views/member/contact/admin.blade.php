@extends('layouts.member', [
    'title' => __('member.feedback.contact_admin_title'),
    'subtitle' => __('member.feedback.contact_admin_subtitle')
])

@section('content')
    <livewire:member.admin-contact-form />
@endsection
