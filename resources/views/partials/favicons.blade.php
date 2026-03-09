@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $faviconUrl = web_asset($branding['team_logo']['paths']['mini'] ?? '/favicon.ico', false);
    $includeMain = $includeMain ?? true;
@endphp
<!-- Favicon -->
@if($includeMain)
    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
@endif
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
