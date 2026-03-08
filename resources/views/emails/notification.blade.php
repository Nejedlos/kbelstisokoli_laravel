@extends('emails.layouts.base')

@section('title', $subject ?? config('email_branding.brand_name'))

@section('content')
    @if (! empty($greeting))
        <p><strong>{{ $greeting }}</strong></p>
    @endif

    @if (isset($introLines) && is_array($introLines))
        @foreach ($introLines as $line)
            <p>{!! $line !!}</p>
        @endforeach
    @endif

    @if (isset($actionText) && ! empty($actionUrl))
        @include('emails.partials.button', [
            'url' => $actionUrl,
            'text' => $actionText,
            'color' => $actionColor ?? config('email_branding.colors.primary')
        ])
    @endif

    @if (isset($outroLines) && is_array($outroLines))
        @foreach ($outroLines as $line)
            <p>{!! $line !!}</p>
        @endforeach
    @endif

    @if (! empty($salutation))
        <p style="margin-top: 25px;">
            {!! nl2br(e($salutation)) !!}
        </p>
    @else
        <p style="margin-top: 25px;">
            Díky,<br>
            {{ config('email_branding.brand_name') }}
        </p>
    @endif
@endsection
