<div class="relative">
    {{-- Globální basketbalový loader pro okamžitou zpětnou vazbu při přihlašování --}}
    <div wire:loading.delay.shortest wire:target="authenticate" class="ks-loader-overlay">
         <div class="ks-loader-content">
            <div class="ks-ball-container">
                <i class="fa-light fa-basketball ks-basketball-icon"></i>
            </div>
            <div class="ks-loader-text">
                <span>{{ __('Ověřování taktiky...') }}</span>
            </div>
        </div>
    </div>

    {{ $this->content }}
</div>
