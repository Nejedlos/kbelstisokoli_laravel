@teleport('body')
<div {{ $attributes->merge(['class' => 'ks-loader-overlay']) }}
     @if(!$attributes->has('x-show') && !$attributes->has('wire:loading'))
         wire:loading.delay
         wire:loading.class="is-loading"
     @endif
     @if($attributes->has('x-show'))
         :class="{ 'is-loading': {{ $attributes->get('x-show') }} }"
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
