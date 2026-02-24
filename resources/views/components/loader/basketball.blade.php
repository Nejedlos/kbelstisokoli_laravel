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
        @if(!$slot->isEmpty())
            <div class="ks-loader-text">{{ $slot }}</div>
        @endif
    </div>
</div>
@endteleport
