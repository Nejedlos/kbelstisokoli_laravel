@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $faviconUrl = web_asset($branding['team_logo']['paths']['mini'] ?? '/favicon.ico', false);
    $includeMain = $includeMain ?? true;
@endphp
<!-- Favicon -->
@if($includeMain)
    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
@endif
<link rel="icon" type="image/png" sizes="32x32" href="{{ web_asset('favicon-32x32.png', false) }}?v=1">
<link rel="icon" type="image/png" sizes="16x16" href="{{ web_asset('favicon-16x16.png', false) }}?v=1">
<link rel="apple-touch-icon" sizes="180x180" href="{{ web_asset('apple-touch-icon.png', false) }}?v=1">
<link rel="manifest" href="{{ web_asset('site.webmanifest', false) }}?v=1">
