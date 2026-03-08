@extends('emails.layouts.base')

@section('title', $type === 'coach' ? __('mail.feedback.to_coach_title') : __('mail.feedback.to_admin_title'))

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 20px; font-weight: bold; text-align: left;">
        {{ $type === 'coach' ? __('mail.feedback.to_coach_title') : __('mail.feedback.to_admin_title') }}
    </h1>

    @if($team)
        <p style="margin-bottom: 8px; color: {{ config('email_branding.colors.muted') }};">
            {{ __('mail.feedback.team') }}: <strong>{{ $team->name }}</strong>
        </p>
    @endif

    <div style="white-space: pre-line; margin: 20px 0;">{{ $bodyMessage }}</div>

    @include('emails.partials.divider')

    <p style="font-size: 13px; color: {{ config('email_branding.colors.muted') }};">
        {{ __('mail.feedback.from_user') }}: <strong>{{ $user->name }}</strong> ({{ $user->email }})
    </p>

    <p style="margin-top: 30px;">Díky,<br>{{ config('email_branding.brand_name') }}</p>
@endsection
