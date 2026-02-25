@props(['email', 'class' => ''])

@php
    $encoded = \App\Support\EmailObfuscator::encode($email);
    $url = \App\Support\EmailObfuscator::getContactUrl($email);
@endphp

<a href="{{ $url }}"
   data-protected-email="{{ $encoded }}"
   {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot->isEmpty() ? '[email]' : $slot }}
</a>
