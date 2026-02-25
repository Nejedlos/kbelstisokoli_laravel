@props([
    'src' => null,
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'decoding' => 'async',
    'fetchpriority' => 'auto',
    'width' => null,
    'height' => null,
    'mobileSrc' => null,
])

@php
    $getImageVariants = function($path) {
        $defaultWebp = 'assets/img/home/basketball-court-detail.webp';
        $defaultJpg = 'assets/img/home/basketball-court-detail.jpg';

        if (!$path) {
            return ['webp' => $defaultWebp, 'img' => $defaultJpg];
        }

        $cleanPath = ltrim($path, '/');
        $pathInfo = pathinfo($cleanPath);
        $base = ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . $pathInfo['filename'];
        $ext = strtolower($pathInfo['extension'] ?? '');

        $webp = $base . '.webp';
        $jpg = $base . '.jpg';
        $jpeg = $base . '.jpeg';

        $finalWebp = null;
        $finalImg = null;

        if (file_exists(public_path($webp))) {
            $finalWebp = $webp;
        }

        if (file_exists(public_path($jpg))) {
            $finalImg = $jpg;
        } elseif (file_exists(public_path($jpeg))) {
            $finalImg = $jpeg;
        } elseif ($ext !== 'webp' && file_exists(public_path($cleanPath))) {
            $finalImg = $cleanPath;
        }

        if (!$finalWebp && !$finalImg) {
            return ['webp' => $defaultWebp, 'img' => $defaultJpg];
        }

        return ['webp' => $finalWebp, 'img' => $finalImg ?: $finalWebp];
    };

    $desktop = $getImageVariants($src);

    $mSrc = $mobileSrc;
    if (!$mSrc && $src) {
        $pi = pathinfo(ltrim($src, '/'));
        $baseMobile = ($pi['dirname'] !== '.' ? $pi['dirname'] . '/' : '') . $pi['filename'] . '-mobile';
        // Zkontrolujeme, zda existuje jakákoliv verze mobilního obrázku
        if (file_exists(public_path($baseMobile . '.webp')) ||
            file_exists(public_path($baseMobile . '.jpg')) ||
            file_exists(public_path($baseMobile . '.jpeg')) ||
            (isset($pi['extension']) && file_exists(public_path($baseMobile . '.' . $pi['extension'])))) {
            $mSrc = $baseMobile . '.' . ($pi['extension'] ?? 'jpg');
        }
    }

    $mobile = $mSrc ? $getImageVariants($mSrc) : null;
@endphp

<picture {{ $attributes->merge(['class' => '']) }}>
    @if($mobile)
        @if($mobile['webp'])
            <source media="(max-width: 639px)" srcset="{{ asset($mobile['webp']) }}" type="image/webp">
        @endif
        <source media="(max-width: 639px)" srcset="{{ asset($mobile['img']) }}">
    @endif

    @if($desktop['webp'])
        <source srcset="{{ asset($desktop['webp']) }}" type="image/webp">
    @endif
    <img
        src="{{ asset($desktop['img']) }}"
        alt="{{ $alt }}"
        @if($class) class="{{ $class }}" @endif
        @if($loading) loading="{{ $loading }}" @endif
        @if($decoding) decoding="{{ $decoding }}" @endif
        @if($fetchpriority !== 'auto') fetchpriority="{{ $fetchpriority }}" @endif
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
    >
</picture>
