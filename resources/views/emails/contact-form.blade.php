@extends('emails.layouts.base')

@section('title', $subjectText ?? 'Zpráva z kontaktního formuláře')

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 22px; font-weight: bold; text-align: left;">
        Zpráva z kontaktního formuláře
    </h1>

    @include('emails.partials.key-value-table', [
        'items' => [
            'Odesílatel' => $senderName,
            'E-mail' => $senderEmail,
        ]
    ])

    @include('emails.partials.divider')

    <div style="white-space: pre-wrap; margin-top: 20px;">{!! nl2br(e($messageBody)) !!}</div>

    @include('emails.partials.small-text', [
        'text' => 'Tato zpráva byla odeslána automaticky z kontaktního formuláře na webu ' . config('email_branding.brand_name') . '.'
    ])
@endsection
