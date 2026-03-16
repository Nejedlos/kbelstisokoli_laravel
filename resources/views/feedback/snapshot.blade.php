<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback Snapshot</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Schovat prvky, které nechceme na screenshotu */
        #feedback-widget, .ks-feedback-ignore {
            display: none !important;
        }
        body {
            background-color: transparent !important;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div id="snapshot-root">
        {!! $dom !!}
    </div>
</body>
</html>
