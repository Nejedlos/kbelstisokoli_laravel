@extends('emails.layouts.base')

@section('title', $type === 'coach' ? __('mail.feedback.confirm_coach_title') : __('mail.feedback.confirm_admin_title'))

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 20px; font-weight: bold; text-align: left;">
        {{ $type === 'coach' ? __('mail.feedback.confirm_coach_title') : __('mail.feedback.confirm_admin_title') }}
    </h1>

    @if($team)
        <p style="margin-bottom: 8px; color: {{ config('email_branding.colors.muted') }};">
            {{ __('mail.feedback.team') }}: <strong>{{ $team->name }}</strong>
        </p>
    @endif

    <p style="white-space: pre-line;">{{ __('mail.feedback.confirm_body') }}</p>

    @include('emails.partials.panel', [
        'text' => '
            <div style="font-size: 12px; color: ' . config('email_branding.colors.muted') . ';">' . __('mail.feedback.your_message') . ':</div>
            <div style="white-space: pre-line; margin-top: 5px;">' . e($bodyMessage) . '</div>
        '
    ])

    <p>Díky,<br>{{ config('email_branding.brand_name') }}</p>
@endsection
