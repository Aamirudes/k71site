@php
    $palette = array_merge($manifest['palette'], $themeConfig['palette'] ?? []);
    $typo = array_merge($manifest['typography'], $themeConfig['typography'] ?? []);
    $motion = $manifest['motion'];
    $layout = $manifest['layout'];
    $motionIntensity = $themeConfig['motion']['intensity'] ?? 'normal';
    $pageArchitecture = $layout['page_architecture'] ?? 'editorial-runway';
    $choreography = $motion['choreography'] ?? 'quiet-editorial';
    $depth3d = $motion['depth_3d'] ?? 'none';

    $headingFont = $typo['heading_font'] ?? 'Playfair Display';
    $bodyFont = $typo['body_font'] ?? 'Inter';
    $googleFontsParam = urlencode($headingFont) . ':ital,wght@0,400;0,500;0,600;0,700;0,800;1,400';
    if ($bodyFont !== $headingFont) {
        $googleFontsParam .= '&family=' . urlencode($bodyFont) . ':wght@400;500;600;700';
    }
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $pageTitle = ($pageTitle ?? null) ? ($pageTitle . ' · ' . $storeName) : $storeName;
    $isDark = !empty($palette['is_dark']);

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

    $storeHomeUrl = $storeHomeUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=home')
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));

    $storeCollectionUrl = $storeCollectionUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=collection')
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));
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

    @vite(['resources/css/app.css', 'resources/js/k71-theme-motion.js'])
    <link rel="stylesheet" href="{{ asset('css/k71-theme-engine.css') }}?v=4.1.0">
    {{-- Per-theme scoped stylesheet: ONLY loads the active theme's CSS --}}
    <link rel="stylesheet" href="{{ asset('css/themes/' . $manifest['id'] . '.css') }}?v=4.1.0">
</head>
<body class="m-0 p-0 antialiased">
<div class="k71-sf-theme flex flex-col min-h-screen"
     data-k71-theme="{{ $manifest['id'] }}"
     data-k71-architecture="{{ $pageArchitecture }}"
     data-structural-variant="{{ $layout['structural_variant'] ?? 'A' }}"
     data-k71-category="{{ $manifest['category'] ?? 'general' }}"
     data-k71-header="{{ $layout['header_template'] ?? 'couture-centered' }}"
     data-k71-hero="{{ $layout['hero_template'] ?? 'asymmetric-campaign' }}"
     data-k71-card="{{ $layout['card_template'] ?? 'luxury-atelier' }}"
     data-k71-pdp="{{ $layout['pdp_template'] ?? 'luxury-lookbook' }}"
     data-label-style="{{ $typo['label_style'] ?? 'clean' }}"
     data-motion-profile="{{ $motion['profile'] ?? 'quiet-gallery' }}"
     data-motion-choreography="{{ $choreography }}"
     data-depth-3d="{{ $depth3d }}"
     data-motion-intensity="{{ $motionIntensity }}"
     data-motion-stagger="{{ $motion['stagger_ms'] ?? 45 }}"
     data-enable-parallax="{{ !empty($motion['enable_parallax']) ? 'true' : 'false' }}"
     style="
        --t-bg: {{ $palette['bg'] }};
        --t-surface: {{ $palette['surface'] }};
        --t-surface-alt: {{ $palette['surface_alt'] }};
        --t-text: {{ $palette['text'] }};
        --t-muted: {{ $palette['muted'] }};
        --t-accent: {{ $palette['accent'] }};
        --t-accent-contrast: {{ $palette['accent_contrast'] }};
        --t-border: {{ $palette['border'] }};
        --t-radius: {{ $layout['radius'] ?? '8px' }};
        --t-font-heading: '{{ $headingFont }}', Georgia, serif;
        --t-font-body: '{{ $bodyFont }}', system-ui, -apple-system, sans-serif;
        --t-heading-weight: {{ $typo['heading_weight'] ?? '600' }};
        --t-heading-tracking: {{ $typo['heading_tracking'] ?? '-0.02em' }};
        --t-motion-duration: {{ (int) ($motion['duration_ms'] ?? 480) }}ms;
        --t-motion-ease: {{ $motion['easing'] ?? 'cubic-bezier(0.22, 1, 0.36, 1)' }};
     ">
    <div id="k71-scroll-progress" aria-hidden="true"></div>

    @if($pageArchitecture === 'left-rail-gallery')
        {{-- Persistent Left Gallery Rail Architecture (Desktop Vertical Sidebar + Mobile Top Bar) --}}
        <div class="lg:hidden">
            @include('storefront.themes.partials.header')
        </div>
        <div class="flex flex-1 min-h-screen w-full">
            <aside class="hidden lg:flex lg:flex-col lg:w-72 xl:w-80 shrink-0 sticky top-0 h-screen overflow-y-auto px-7 py-9 justify-between"
                   style="border-right: 1px solid var(--t-border); background-color: var(--t-surface);">
                <div class="space-y-8">
                    <div class="space-y-2">
                        <a href="{{ $storeHomeUrl }}" class="inline-flex items-center gap-3">
                            @if(!empty($vendor->logo_url))
                                <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="h-9 w-9 object-contain rounded">
                            @endif
                            <span class="k71-heading text-2xl tracking-tight leading-none">{{ $storeName }}</span>
                        </a>
                        <p class="text-[11px] uppercase tracking-[0.2em]" style="color: var(--t-muted);">
                            {{ $manifest['name'] }} Gallery
                        </p>
                    </div>

                    <nav class="space-y-1.5" aria-label="Gallery navigation">
                        <p class="k71-eyebrow mb-2.5" style="color: var(--t-muted);">Exhibition Index</p>
                        <a href="{{ $storeHomeUrl }}" class="flex items-center justify-between py-2 text-sm font-medium hover:opacity-70 transition" style="border-bottom: 1px solid var(--t-border);">
                            <span>00 · Overview</span>
                            @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                        </a>
                        <a href="{{ $storeCollectionUrl }}" class="flex items-center justify-between py-2 text-sm font-medium hover:opacity-70 transition" style="border-bottom: 1px solid var(--t-border);">
                            <span>01 · Complete Catalogue</span>
                            @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                        </a>
                        @foreach(($vendorCategories ?? collect())->take(6) as $cIdx => $gCat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($gCat->slug) }}"
                               class="flex items-center justify-between py-2 text-xs hover:opacity-70 transition" style="color: var(--t-muted); border-bottom: 1px solid var(--t-border);">
                                <span class="truncate">{{ str_pad((string)($cIdx + 2), 2, '0', STR_PAD_LEFT) }} · {{ $gCat->name }}</span>
                                <span class="font-mono text-[10px]">{{ $gCat->products_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="space-y-4 pt-6" style="border-top: 1px solid var(--t-border);">
                    <div class="flex items-center gap-2">
                        <button type="button" data-k71-open="k71-search-modal"
                                class="flex-1 min-h-[42px] px-3 text-xs font-semibold inline-flex items-center justify-center gap-2 cursor-pointer"
                                style="border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                            @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-3.5 h-3.5'])
                            <span>Search</span>
                        </button>
                        <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer"
                           class="flex-1 min-h-[42px] px-3 text-xs font-semibold inline-flex items-center justify-center gap-2"
                           style="background-color: var(--t-accent); color: var(--t-accent-contrast); border-radius: var(--t-radius);">
                            @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-3.5 h-3.5'])
                            <span>Bag (<span data-k71-cart-count>{{ $cartCount }}</span>)</span>
                        </a>
                    </div>
                    @if($vendor->city)
                        <p class="text-[11px]" style="color: var(--t-muted);">{{ $vendor->city }}{{ $vendor->state ? ', ' . $vendor->state : '' }}</p>
                    @endif
                </div>
            </aside>

            <div class="flex-1 min-w-0 flex flex-col">
                <main id="main-content" class="flex-1 w-full">
                    @yield('theme_content')
                </main>
                @include('storefront.themes.partials.footer')
            </div>
        </div>
    @else
        @include('storefront.themes.partials.header')

        <main id="main-content" class="flex-1 w-full">
            @yield('theme_content')
        </main>

        @include('storefront.themes.partials.footer')
    @endif

    @include('storefront.themes.partials.overlays')
</div>
</body>
</html>
