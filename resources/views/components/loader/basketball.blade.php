@php
    $hasWireLoading = false;
    foreach($attributes->getAttributes() as $key => $value) {
        if(str_starts_with($key, 'wire:loading')) {
            $hasWireLoading = true;
            break;
        }
    }
@endphp

@teleport('body')
<div {{ $attributes->merge(['class' => 'ks-loader-overlay hidden', 'style' => 'display: none']) }}
     @if(!$attributes->has('x-show') && !$hasWireLoading)
         wire:loading.delay
         wire:loading.class.remove="hidden"
     @endif
     @if($attributes->has('x-show'))
         x-show="{{ $attributes->get('x-show') }}"
         x-cloak
         :class="{ 'is-loading': {{ $attributes->get('x-show') }}, 'hidden': !({{ $attributes->get('x-show') }}) }"
     @endif
     wire:key="ks-loader-{{ md5($attributes->get('wire:target', 'default')) }}">
    <div class="ks-loader-content">
        <div class="ks-ball-container">
            <div class="ks-basketball-icon" aria-hidden="true">
                <i class="fa-light fa-basketball"></i>
            </div>
        </div>
        <div class="ks-loader-body">
            @if($slot->isEmpty())
                <div class="ks-loader-text">
                    {{ __('admin.navigation.resources.photo_pool.notifications.processing') }}
                </div>
            @else
                <div class="ks-loader-custom">
                    {{ $slot }}
                </div>
            @endif
            <div wire:stream="ks-loader-progress" class="ks-loader-progress"></div>
        </div>
    </div>
</div>
@endteleport
