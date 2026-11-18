@php
    $palette = array_merge($manifest['palette'], $themeConfig['palette'] ?? []);
    $typo = array_merge($manifest['typography'], $themeConfig['typography'] ?? []);
    $glass = $manifest['glass'];
    $motion = $manifest['motion'];
    $layout = $manifest['layout'];
    $motionIntensity = $themeConfig['motion']['intensity'] ?? 'normal';
    $glassStrength = $themeConfig['motion']['glass_strength'] ?? ($glass['mode'] === 'none' ? 'off' : 'normal');

    $headingFont = $typo['heading_font'] ?? 'Playfair Display';
    $bodyFont = $typo['body_font'] ?? 'Inter';
    $googleFontsParam = urlencode($headingFont) . ':ital,wght@0,400;0,500;0,600;0,700;0,800;1,400';
    if ($bodyFont !== $headingFont) {
        $googleFontsParam .= '&family=' . urlencode($bodyFont) . ':wght@400;500;600;700';
    }
    $storeName = $vendor->shop_name ?? 'K71 Store';
    $pageTitle = ($pageTitle ?? null) ? ($pageTitle . ' · ' . $storeName) : $storeName;
    $isDark = !empty($palette['is_dark']);

    // Build real cart count safely
    $cartCount = 0;
    try {
        if (auth()->check()) {
            $userCart = \App\Models\Cart::where('user_id', auth()->id())->first();
            $cartCount = $userCart ? (int) $userCart->items()->sum('quantity') : 0;
        } else {
            $sessCart = \App\Models\Cart::where('session_id', session()->getId())->first();
            $cartCount = $sessCart ? (int) $sessCart->items()->sum('quantity') : 0;
        }
    } catch (\Throwable $e) {
        $cartCount = 0;
    }

    $storeHomeUrl = !empty($isCataloguePreview)
        ? route('vendor.themes.preview_frame', ['themeId' => $manifest['id'], 'page' => 'home'])
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : ''));

    $storeCollectionUrl = !empty($isCataloguePreview)
        ? route('vendor.themes.preview_frame', ['themeId' => $manifest['id'], 'page' => 'collection'])
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : '');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $isDark ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($isPreview) || !empty($isCataloguePreview))
        <meta name="robots" content="noindex, nofollow">
    @endif
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) ($metaDescription ?? $vendor->description ?? $storeName)), 160) }}">
    @if(!empty($canonicalUrl) && empty($isPreview) && empty($isCataloguePreview))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $googleFontsParam }}&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/k71-theme-engine.css') }}?v={{ $manifest['version'] ?? '1.0.0' }}">
</head>
<body class="m-0 p-0 antialiased">
<div class="k71-sf-theme flex flex-col min-h-screen"
     data-k71-theme="{{ $manifest['id'] }}"
     data-glass-mode="{{ $glass['mode'] ?? 'none' }}"
     data-glass-strength="{{ $glassStrength }}"
     data-label-style="{{ $typo['label_style'] ?? 'clean' }}"
     data-motion-profile="{{ $motion['profile'] ?? 'calm-fade' }}"
     data-motion-intensity="{{ $motionIntensity }}"
     data-motion-stagger="{{ $motion['stagger_ms'] ?? 45 }}"
     data-enable-parallax="{{ !empty($motion['enable_parallax']) ? 'true' : 'false' }}"
     data-enable-tilt="{{ !empty($motion['enable_tilt']) ? 'true' : 'false' }}"
     style="
        --t-bg: {{ $palette['bg'] }};
        --t-surface: {{ $palette['surface'] }};
        --t-surface-alt: {{ $palette['surface_alt'] }};
        --t-text: {{ $palette['text'] }};
        --t-muted: {{ $palette['muted'] }};
        --t-accent: {{ $palette['accent'] }};
        --t-accent-contrast: {{ $palette['accent_contrast'] }};
        --t-border: {{ $palette['border'] }};
        --t-radius: {{ $layout['radius'] ?? '12px' }};
        --t-font-heading: '{{ $headingFont }}', Georgia, serif;
        --t-font-body: '{{ $bodyFont }}', system-ui, -apple-system, sans-serif;
        --t-heading-weight: {{ $typo['heading_weight'] ?? '600' }};
        --t-heading-tracking: {{ $typo['heading_tracking'] ?? '-0.02em' }};
        --t-glass-bg: {{ $palette['glass_surface'] ?? $palette['surface'] }};
        --t-glass-border: {{ $palette['glass_border'] ?? $palette['border'] }};
        --t-glass-blur: {{ (int) ($glass['blur_px'] ?? 12) }}px;
        --t-motion-duration: {{ (int) ($motion['duration_ms'] ?? 480) }}ms;
        --t-motion-ease: {{ $motion['easing'] ?? 'cubic-bezier(0.22, 1, 0.36, 1)' }};
     ">

    @if(!empty($isCataloguePreview))
        <div class="w-full px-4 py-2 text-center text-xs font-semibold flex flex-wrap items-center justify-center gap-3 z-50"
             style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
            <span>Isolated Theme Preview: <strong>{{ $manifest['name'] }}</strong> (v{{ $manifest['version'] }})</span>
            @if(!empty($usingSampleProducts))
                <span class="opacity-85">· Displaying sample showcase items (your live store is untouched)</span>
            @else
                <span class="opacity-85">· Previewing with your store catalogue (live storefront untouched)</span>
            @endif
        </div>
    @elseif(!empty($isPreview))
        <div class="w-full px-4 py-2 text-center text-xs font-semibold z-50"
             style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
            Draft Preview Mode — Changes shown here are not public until explicitly published.
        </div>
    @endif

    @include('storefront.themes.partials.header')

    <main id="main-content" class="flex-1 w-full">
        @yield('theme_content')
    </main>

    @include('storefront.themes.partials.footer')
    @include('storefront.themes.partials.overlays')
</div>

<script src="{{ asset('js/k71-theme-motion.js') }}?v={{ $manifest['version'] ?? '1.0.0' }}" defer></script>
</body>
</html>
