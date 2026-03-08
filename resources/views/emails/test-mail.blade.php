@extends('emails.layouts.base')

@section('title', 'Zkušební e-mail')

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 22px; font-weight: bold; text-align: left;">
        Zkušební e-mail
    </h1>

    <p>Toto je automaticky generovaný e-mail z administrace projektu <strong>{{ config('app.name') }}</strong> pro ověření funkčnosti SMTP spojení.</p>

    <div style="margin: 20px 0;">
        <p><strong>Zpráva:</strong></p>
        <p>{{ $messageContent }}</p>
    </div>

    @include('emails.partials.panel', [
        'text' => "
            <strong>Technické detaily:</strong><br>
            - Čas odeslání: " . now()->toDateTimeString() . "<br>
            - Prostředí: " . app()->environment() . "<br>
            - Mailer: " . config('mail.default') . "
        "
    ])

    <p>Díky,<br>{{ config('app.name') }}</p>
@endsection
