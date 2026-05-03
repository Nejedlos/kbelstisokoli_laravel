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
    'sizes' => '100vw',
])

@php
    $appUrl = config('app.url');

    $getImageVariants = function($path) use ($appUrl) {
        $defaultWebp = 'assets/img/home/basketball-court-detail.webp';
        $defaultJpg = 'assets/img/home/basketball-court-detail.jpg';

        if (!$path) {
            return ['webp' => $defaultWebp, 'img' => $defaultJpg];
        }

        // Pokud jde o absolutní URL, zkusíme ji převést na relativní cestu k public_path
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (str_starts_with($path, $appUrl)) {
                $path = str_replace($appUrl, '', $path);
            } else {
                // Pokud směřuje jinam, vracíme ji přímo bez variant
                return ['webp' => null, 'img' => $path];
            }
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

        if (file_exists(public_path($webp)) || @file_exists(base_path('public/' . $webp))) {
            $finalWebp = $webp;
        }

        if (file_exists(public_path($jpg)) || @file_exists(base_path('public/' . $jpg))) {
            $finalImg = $jpg;
        } elseif (file_exists(public_path($jpeg)) || @file_exists(base_path('public/' . $jpeg))) {
            $finalImg = $jpeg;
        } elseif ($ext !== 'webp' && (file_exists(public_path($cleanPath)) || @file_exists(base_path('public/' . $cleanPath)))) {
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
        // Normalizujeme src pro výpočet mobileSrc
        $normalizedSrc = $src;
        if (str_starts_with($normalizedSrc, 'http://') || str_starts_with($normalizedSrc, 'https://')) {
            if (str_starts_with($normalizedSrc, $appUrl)) {
                $normalizedSrc = str_replace($appUrl, '', $normalizedSrc);
            }
        }

        if (!str_starts_with($normalizedSrc, 'http')) {
            $pi = pathinfo(ltrim($normalizedSrc, '/'));
            $baseMobile = ($pi['dirname'] !== '.' ? $pi['dirname'] . '/' : '') . ($pi['filename'] ?? '') . '-mobile';
            // Zkontrolujeme, zda existuje jakákoliv verze mobilního obrázku
            if (file_exists(public_path($baseMobile . '.webp')) || @file_exists(base_path('public/' . $baseMobile . '.webp')) ||
                file_exists(public_path($baseMobile . '.jpg')) || @file_exists(base_path('public/' . $baseMobile . '.jpg')) ||
                file_exists(public_path($baseMobile . '.jpeg')) || @file_exists(base_path('public/' . $baseMobile . '.jpeg')) ||
                (isset($pi['extension']) && (file_exists(public_path($baseMobile . '.' . $pi['extension'])) || @file_exists(base_path('public/' . $baseMobile . '.' . $pi['extension']))))) {
                $mSrc = $baseMobile . '.' . ($pi['extension'] ?? 'jpg');
            }
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
        <source srcset="{{ asset($desktop['webp']) }}" type="image/webp" @if($sizes) sizes="{{ $sizes }}" @endif>
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
        @if($sizes) sizes="{{ $sizes }}" @endif
    >
</picture>
