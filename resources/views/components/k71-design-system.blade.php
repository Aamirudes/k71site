@php
    $host = request()->getHost();
    $path = request()->path();
    $routeName = request()->route() ? request()->route()->getName() : '';

    $detectedBrand = 'main';
    if (str_starts_with($host, 'food.') || str_starts_with($path, 'food') || ($routeName && str_starts_with($routeName, 'food.'))) {
        $detectedBrand = 'food';
    } elseif (str_starts_with($host, 'services.') || str_starts_with($path, 'services') || ($routeName && str_starts_with($routeName, 'services.'))) {
        $detectedBrand = 'services';
    } elseif (str_starts_with($host, 'used.') || str_starts_with($path, 'used') || ($routeName && str_starts_with($routeName, 'used.'))) {
        $detectedBrand = 'used';
    } elseif (str_starts_with($host, 'enquiry.') || str_starts_with($host, 'b2b.') || str_starts_with($path, 'enquiry') || str_starts_with($path, 'b2b') || ($routeName && str_starts_with($routeName, 'enquiry.'))) {
        $detectedBrand = 'b2b';
    } elseif (str_starts_with($host, 'feasto.') || str_starts_with($path, 'feasto') || ($routeName && str_starts_with($routeName, 'feasto.'))) {
        $detectedBrand = 'feasto';
    } elseif (str_starts_with($host, 'shorts.') || str_starts_with($path, 'shorts') || ($routeName && str_starts_with($routeName, 'shorts.'))) {
        $detectedBrand = 'shorts';
    }
@endphp

<!-- Set Brand on HTML root immediately -->
<script>
    document.documentElement.setAttribute('data-brand', '{{ $detectedBrand }}');
</script>

<!-- Google Fonts / Fontshare Preloads including Devanagari fallback -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..800;1,9..40,400..800&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700;800;900&family=Sora:wght@400;600;700&family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

<!-- K71 Unified Design System Styles -->
<link rel="stylesheet" href="/assets/k71-ds/tokens.css">
<link rel="stylesheet" href="/assets/k71-ds/base.css">
<link rel="stylesheet" href="/assets/k71-ds/glass.css">
<link rel="stylesheet" href="/assets/k71-ds/liquid.css">
<link rel="stylesheet" href="/assets/k71-ds/components.css">

<!-- K71 Unified Design System Micro-Engines -->
<script defer src="/assets/k71-ds/liquid.js"></script>
<script defer src="/assets/k71-ds/motion.js"></script>

<!-- Universal Optical SVG Filters for Gooey Liquid & Refraction (Zero Layout Impact) -->
<svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden; pointer-events: none;" version="1.1" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter id="k71-gooey-filter">
            <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
            <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo" />
            <feComposite in="SourceGraphic" in2="goo" operator="atop" />
        </filter>
    </defs>
</svg>
