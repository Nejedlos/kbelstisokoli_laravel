<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $context['html_class'] ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback Snapshot</title>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data: *;">
    @vite(['resources/css/app.css'])
    <x-screenshot.styles />
    {!! $context['head'] ?? '' !!}
    <base href="{{ url('/') }}">
    <style>
        /* Schovat prvky, které nechceme na screenshotu */
        #feedback-widget, .ks-feedback-ignore, .ks-fab-trigger, .ks-fb-overlay {
            display: none !important;
        }
        body {
            margin: 0;
            padding: 0;
            overflow: visible !important;
        }
        #snapshot-root {
            width: 100%;
            height: auto;
        }
        /* Fix pro obrovské ikony, pokud by náhodou chybělo FA CSS nebo byly SVG */
        svg.fa-secondary, svg.fa-primary, svg.fa-light, svg.fa-regular, svg.fa-solid, svg.fa-thin, svg.fa-duotone,
        i.fa-secondary, i.fa-primary, i.fa-light, i.fa-regular, i.fa-solid, i.fa-thin, i.fa-duotone,
        .svg-inline--fa {
            display: inline-block;
            height: 1em !important;
            width: auto !important;
            vertical-align: -0.125em;
            overflow: visible;
        }
    </style>
</head>
<body class="{{ $context['body_class'] ?? '' }}" style="{{ $context['body_style'] ?? '' }}">
    <div id="snapshot-root">
        {!! preg_replace('/<script\b[^>]*>([\s\S]*?)<\/script>/i', '', $dom) !!}
    </div>
    <x-screenshot.scripts />
</body>
</html>
