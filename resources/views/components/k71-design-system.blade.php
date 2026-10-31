@php
    $activeBrand = $currentBrand ?? (function_exists('k71_current_brand') ? k71_current_brand() : 'main');
@endphp

<!-- Pre-assert Brand on HTML root immediately -->
<script>
    if (!document.documentElement.getAttribute('data-brand')) {
        document.documentElement.setAttribute('data-brand', '{{ $activeBrand }}');
    }
</script>

<!-- Google Fonts Preloads per Brand Scale (Section E) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- K71 Unified Design System Styles -->
<link rel="stylesheet" href="/assets/k71-ds/tokens.css">
<link rel="stylesheet" href="/assets/k71-ds/base.css">
<link rel="stylesheet" href="/assets/k71-ds/glass.css">
<link rel="stylesheet" href="/assets/k71-ds/liquid.css">
<link rel="stylesheet" href="/assets/k71-ds/components.css">

<!-- K71 Unified Design System Micro-Engines -->
<script defer src="/assets/k71-ds/liquid.js"></script>
<script defer src="/assets/k71-ds/motion.js"></script>
