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

    @include('emails.partials.divider')

    <div style="margin-top: 30px; padding: 20px; background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; color: #1e293b; font-size: 16px; font-weight: bold;">{{ __('recruitment.email.instructions_title') }}</h3>
        <p style="color: #475569; font-size: 14px; line-height: 1.5; margin-bottom: 20px;">
            {{ __('recruitment.email.instructions_text') }}
        </p>

        @php
            // Konstrukce odkazu do Filamentu
            $adminUrl = config('app.url') . '/admin/leads';
            if (isset($leadId)) {
                // Pokud chceme rovnou na detail, v ManageLeads (Filament v5) to bývá modál,
                // takže odkaz na přehled stačí, nebo zkusíme ?record=ID
                $adminUrl .= '?record=' . $leadId;
            }
        @endphp

        <a href="{{ $adminUrl }}" style="display: inline-block; padding: 10px 20px; background-color: {{ config('email_branding.colors.primary') }}; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">
            {{ __('recruitment.email.admin_link') }}
        </a>
    </div>

    @include('emails.partials.small-text', [
        'text' => __('recruitment.email.footer')
    ])
@endsection
