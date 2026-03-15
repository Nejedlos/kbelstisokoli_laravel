@extends('emails.layouts.base')

@section('title', 'Nový feedback od ' . $report->user->name)

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 22px; font-weight: bold; text-align: left;">
        Nový feedback od {{ $report->user->name }}
    </h1>

    @include('emails.partials.key-value-table', [
        'items' => [
            'Typ' => ucfirst($report->type),
            'Závažnost' => ucfirst($report->severity ?? 'N/A'),
            'Oblast' => ucfirst($report->source_area),
        ]
    ])

    <h2 style="font-size: 18px; color: {{ config('email_branding.colors.text') }}; margin-top: 25px;">{{ $report->title }}</h2>
    <p>{{ $report->description }}</p>

    @if($report->steps)
        <h3 style="font-size: 16px; color: {{ config('email_branding.colors.text') }}; margin-top: 20px;">Kroky k reprodukci</h3>
        <p>{{ $report->steps }}</p>
    @endif

    @if($report->screenshot_path && \Illuminate\Support\Facades\Storage::exists($report->screenshot_path))
        <h3 style="font-size: 16px; color: {{ config('email_branding.colors.text') }}; margin-top: 20px;">Screenshot</h3>
        <div style="margin-top: 10px;">
            <img src="{{ $message->embed(storage_path('app/private/' . $report->screenshot_path)) }}" alt="Screenshot" style="max-width: 100%; border-radius: 8px; border: 1px solid {{ config('email_branding.colors.border') }};">
        </div>
    @endif

    @include('emails.partials.divider')

    @include('emails.partials.panel', [
        'text' => "
            <strong>URL:</strong> {$report->url}<br>
            <strong>Uživatel:</strong> {$report->user->email} (ID: {$report->user_id})<br>
            <strong>App Version:</strong> {$report->app_version}<br>
            <strong>Browser:</strong> " . e($report->user_agent) . "
        "
    ])

    @include('emails.partials.button', [
        'url' => config('app.url') . '/admin/feedback-reports/' . $report->id,
        'text' => 'Zobrazit v administraci'
    ])

    <p>Díky,<br>{{ config('app.name') }}</p>
@endsection
