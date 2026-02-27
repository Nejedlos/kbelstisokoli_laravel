@teleport('body')
<div {{ $attributes->merge(['class' => 'ks-loader-overlay hidden']) }}
     @if(!$attributes->has('x-show') && !$attributes->has('wire:loading')) wire:loading.delay wire:loading.class.remove="hidden" @endif
     wire:key="ks-loader-{{ md5($attributes->get('wire:target', 'default')) }}">
    <div class="ks-loader-content">
        <div class="ks-ball-container">
            <div class="ks-basketball-icon" aria-hidden="true">
                <i class="fa-light fa-basketball"></i>
            </div>
        </div>
        <div class="ks-loader-text">
            @if($slot->isEmpty())
                {{ __('admin.navigation.resources.photo_pool.notifications.processing') }}
            @else
                {{ $slot }}
            @endif
            <span wire:stream="ks-loader-progress"></span>
        </div>
    </div>
</div>
@endteleport
