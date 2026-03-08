@extends('emails.layouts.base')

@section('title', 'Application Error Report')

@section('content')
    <h1 style="margin-top: 0; color: #E11D48; font-size: 22px; font-weight: bold; text-align: left;">
        🚨 Application Error Report
    </h1>

    @include('emails.partials.key-value-table', [
        'items' => [
            'App' => $report['app']['name'] ?? config('app.name'),
            'Environment' => $report['app']['env'] ?? app()->environment(),
            'Time' => $report['timestamp'] ?? now()->toDateTimeString(),
            'URL' => $report['request']['url'] ?? '-',
            'Method' => $report['request']['method'] ?? '-',
            'IP' => $report['request']['ip'] ?? '-',
        ]
    ])

    <h2 style="font-size: 18px; color: {{ config('email_branding.colors.text') }}; margin-top: 25px;">Exception</h2>
    @include('emails.partials.panel', [
        'text' => "
            <strong>Class:</strong> <code>" . e($report['exception']['class'] ?? '') . "</code><br>
            <strong>Message:</strong> <code>" . e($report['exception']['message'] ?? '') . "</code><br>
            <strong>Code:</strong> <code>" . e($report['exception']['code'] ?? '') . "</code><br>
            <strong>File:</strong> <code>" . e($report['exception']['file'] ?? '') . ":" . ($report['exception']['line'] ?? '') . "</code>
        "
    ])

    <h3 style="font-size: 16px; color: {{ config('email_branding.colors.text') }}; margin-top: 20px;">Trace</h3>
    <div style="background-color: #F1F5F9; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; white-space: pre-wrap; word-break: break-all; overflow-x: auto;">
        {{ $report['exception']['trace'] ?? '' }}
    </div>

    @include('emails.partials.divider')

    <h2 style="font-size: 18px; color: {{ config('email_branding.colors.text') }}; margin-top: 25px;">Authenticated User</h2>
    <div style="background-color: #F1F5F9; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
        {{ json_encode($report['user'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
    </div>

    @include('emails.partials.divider')

    <h2 style="font-size: 18px; color: {{ config('email_branding.colors.text') }}; margin-top: 25px;">Request</h2>
    <div style="background-color: #F1F5F9; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
        {{ json_encode($report['request'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
    </div>

    @include('emails.partials.divider')

    <h2 style="font-size: 18px; color: {{ config('email_branding.colors.text') }}; margin-top: 25px;">Headers</h2>
    <div style="background-color: #F1F5F9; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
        {{ json_encode($report['headers'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
    </div>

    <p style="margin-top: 30px;">Díky,<br>{{ config('app.name') }}</p>
@endsection
