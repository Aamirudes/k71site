@php
    $activeBrand = $k71Brand ?? $currentBrand ?? (function_exists('k71_current_brand') ? k71_current_brand() : 'main');
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
<link rel="stylesheet" href="/assets/k71-ds/tokens.css?v={{ time() }}">
<link rel="stylesheet" href="/assets/k71-ds/base.css?v={{ time() }}">
<link rel="stylesheet" href="/assets/k71-ds/glass.css?v={{ time() }}">
<link rel="stylesheet" href="/assets/k71-ds/liquid.css?v={{ time() }}">
<link rel="stylesheet" href="/assets/k71-ds/components.css?v={{ time() }}">
<link rel="stylesheet" href="/assets/k71-ds/brand-overrides.css?v={{ time() }}">

<style>
/* Guaranteed High Contrast Buttons (Unlayered & Inlined for 100% Instant Parity) */
.text-white,
[class*="text-white"],
button.text-white,
a.text-white,
span.text-white,
button[class*="bg-slate-900"],
button[class*="bg-slate-950"],
button[class*="bg-black"],
button[class*="bg-zinc-900"],
button[class*="bg-zinc-950"],
button[class*="bg-indigo-600"],
button[class*="bg-indigo-700"],
button[class*="bg-orange-600"],
button[class*="bg-teal-700"],
button[class*="bg-emerald-600"],
a[class*="bg-slate-900"],
a[class*="bg-slate-950"],
a[class*="bg-black"],
a[class*="bg-zinc-900"],
a[class*="bg-zinc-950"],
a[class*="bg-indigo-600"],
a[class*="bg-indigo-700"],
a[class*="bg-orange-600"],
a[class*="bg-teal-700"],
a[class*="bg-emerald-600"],
.btn-primary,
button.btn-primary,
a.btn-primary {
    color: #ffffff !important;
}

button[class*="bg-slate-900"] :where(span, svg, p, div),
button[class*="bg-slate-950"] :where(span, svg, p, div),
button[class*="bg-black"] :where(span, svg, p, div),
button[class*="bg-zinc-900"] :where(span, svg, p, div),
button[class*="bg-zinc-950"] :where(span, svg, p, div),
button[class*="bg-indigo-600"] :where(span, svg, p, div),
a[class*="bg-slate-900"] :where(span, svg, p, div),
a[class*="bg-slate-950"] :where(span, svg, p, div),
a[class*="bg-black"] :where(span, svg, p, div),
a[class*="bg-zinc-900"] :where(span, svg, p, div),
a[class*="bg-zinc-950"] :where(span, svg, p, div),
a[class*="bg-indigo-600"] :where(span, svg, p, div),
.btn-primary :where(span, svg, p, div) {
    color: inherit !important;
}

header button[type="submit"],
form button[type="submit"] {
    color: #ffffff !important;
}
header button[type="submit"] *,
form button[type="submit"] * {
    color: inherit !important;
}

button[class*="bg-white"],
a[class*="bg-white"],
button[class*="bg-slate-100"],
a[class*="bg-slate-100"],
button[class*="bg-zinc-100"],
a[class*="bg-zinc-100"] {
    color: #0f172a !important;
}
button[class*="bg-white"] :where(span, svg, p, div),
a[class*="bg-white"] :where(span, svg, p, div),
button[class*="bg-slate-100"] :where(span, svg, p, div),
a[class*="bg-slate-100"] :where(span, svg, p, div) {
    color: inherit !important;
}
</style>

<!-- K71 Unified Design System Micro-Engines -->
<script defer src="/assets/k71-ds/liquid.js"></script>
<script defer src="/assets/k71-ds/motion.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.documentElement.classList.add('k71-js');
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('k71-revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('[data-k71-reveal]').forEach(function(el) {
                observer.observe(el);
            });
        } else {
            document.querySelectorAll('[data-k71-reveal]').forEach(function(el) {
                el.classList.add('k71-revealed');
            });
        }
    });
</script>
