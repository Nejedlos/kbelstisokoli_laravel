@props(['email', 'class' => ''])

@php
    $encoded = base64_encode($email);
@endphp

<a href="javascript:void(0)"
   data-protected-email="{{ $encoded }}"
   {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot->isEmpty() ? '[email]' : $slot }}
</a>
