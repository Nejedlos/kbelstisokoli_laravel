@extends('emails.layouts.base')

@section('title', __('recruitment.email.title', ['team' => $teamName]))

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 22px; font-weight: bold; text-align: left;">
        {{ __('recruitment.email.title', ['team' => $teamName]) }}
    </h1>

    @include('emails.partials.key-value-table', [
        'items' => [
            __('recruitment.email.from') => $senderName . " - " . $senderEmail,
            __('recruitment.email.team') => $teamName,
            __('recruitment.email.age') => $extraData['age'] ?? __('recruitment.email.not_provided'),
            __('recruitment.email.height') => (isset($extraData['height']) && $extraData['height']) ? $extraData['height'] . ' cm' : __('recruitment.email.not_provided'),
            __('recruitment.email.position') => $extraData['position'] ?? __('recruitment.email.not_provided'),
            __('recruitment.email.level') => $extraData['level'] ?? __('recruitment.email.not_provided'),
        ]
    ])

    @include('emails.partials.divider')

    <div style="white-space: pre-wrap; margin-top: 20px;">{!! nl2br(e($messageBody)) !!}</div>

    @include('emails.partials.small-text', [
        'text' => __('recruitment.email.footer')
    ])
@endsection
