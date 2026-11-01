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
/* Guaranteed High Contrast Buttons, Form Fields & Capsules */
.text-white,
button.text-white,
a.text-white,
span.text-white,
button[class~="bg-slate-900"],
button[class~="bg-slate-950"],
button[class~="bg-black"],
button[class~="bg-zinc-900"],
button[class~="bg-zinc-950"],
button[class~="bg-indigo-600"],
button[class~="bg-indigo-700"],
button[class~="bg-orange-600"],
button[class~="bg-teal-700"],
button[class~="bg-emerald-600"],
button[class~="bg-emerald-700"],
a[class~="bg-slate-900"],
a[class~="bg-slate-950"],
a[class~="bg-black"],
a[class~="bg-zinc-900"],
a[class~="bg-zinc-950"],
a[class~="bg-indigo-600"],
a[class~="bg-indigo-700"],
a[class~="bg-orange-600"],
a[class~="bg-teal-700"],
a[class~="bg-emerald-600"],
a[class~="bg-emerald-700"],
.btn-primary,
button.btn-primary,
a.btn-primary {
    color: #ffffff !important;
}

/* Force ALL children of dark buttons to be pure white */
button[class~="bg-slate-900"] *,
button[class~="bg-slate-950"] *,
button[class~="bg-black"] *,
button[class~="bg-zinc-900"] *,
button[class~="bg-zinc-950"] *,
button[class~="bg-indigo-600"] *,
button[class~="bg-indigo-700"] *,
button[class~="bg-orange-600"] *,
button[class~="bg-teal-700"] *,
button[class~="bg-emerald-600"] *,
button[class~="bg-emerald-700"] *,
a[class~="bg-slate-900"] *,
a[class~="bg-slate-950"] *,
a[class~="bg-black"] *,
a[class~="bg-zinc-900"] *,
a[class~="bg-zinc-950"] *,
a[class~="bg-indigo-600"] *,
a[class~="bg-indigo-700"] *,
a[class~="bg-orange-600"] *,
a[class~="bg-teal-700"] *,
a[class~="bg-emerald-600"] *,
a[class~="bg-emerald-700"] *,
.btn-primary * {
    color: #ffffff !important;
    fill: currentColor !important;
}

header button[type="submit"],
form button[type="submit"] {
    color: #ffffff !important;
}
header button[type="submit"] *,
form button[type="submit"] * {
    color: #ffffff !important;
    fill: currentColor !important;
}

/* Light buttons & pills: ALWAYS crisp black text */
button[class~="bg-white"],
a[class~="bg-white"],
button[class~="bg-slate-50"],
a[class~="bg-slate-50"],
button[class~="bg-slate-100"],
a[class~="bg-slate-100"],
button[class~="bg-zinc-100"],
a[class~="bg-zinc-100"],
button[class~="bg-stone-100"],
a[class~="bg-stone-100"],
button[class~="bg-amber-50"],
a[class~="bg-amber-50"],
button[class~="bg-amber-100"],
a[class~="bg-amber-100"],
button[class~="bg-emerald-50"],
a[class~="bg-emerald-50"],
button[class~="bg-emerald-100"],
a[class~="bg-emerald-100"],
button[class~="bg-cyan-50"],
a[class~="bg-cyan-50"],
button[class~="bg-cyan-100"],
a[class~="bg-cyan-100"],
button[class~="bg-sky-50"],
a[class~="bg-sky-50"],
button[class~="bg-sky-100"],
a[class~="bg-sky-100"],
button[class~="bg-violet-50"],
a[class~="bg-violet-50"],
button[class~="bg-violet-100"],
a[class~="bg-violet-100"] {
    color: #0f172a !important;
}
button[class~="bg-white"] *,
a[class~="bg-white"] *,
button[class~="bg-slate-50"] *,
a[class~="bg-slate-50"] *,
button[class~="bg-slate-100"] *,
a[class~="bg-slate-100"] *,
button[class~="bg-amber-50"] *,
a[class~="bg-amber-50"] *,
button[class~="bg-emerald-50"] *,
a[class~="bg-emerald-50"] * {
    color: #0f172a !important;
}

/* Hovering light buttons to dark background */
button[class*="hover:bg-slate-950"]:hover,
button[class*="hover:bg-slate-950"]:hover *,
a[class*="hover:bg-slate-950"]:hover,
a[class*="hover:bg-slate-950"]:hover *,
button[class*="hover:bg-slate-900"]:hover,
button[class*="hover:bg-slate-900"]:hover *,
a[class*="hover:bg-slate-900"]:hover,
a[class*="hover:bg-slate-900"]:hover *,
button[class*="hover:bg-zinc-800"]:hover,
button[class*="hover:bg-zinc-800"]:hover *,
a[class*="hover:bg-zinc-800"]:hover,
a[class*="hover:bg-zinc-800"]:hover *,
button[class*="hover:bg-zinc-900"]:hover,
button[class*="hover:bg-zinc-900"]:hover *,
a[class*="hover:bg-zinc-900"]:hover,
a[class*="hover:bg-zinc-900"]:hover *,
button[class*="hover:bg-black"]:hover,
button[class*="hover:bg-black"]:hover *,
a[class*="hover:bg-black"]:hover,
a[class*="hover:bg-black"]:hover *,
button[class*="hover:bg-amber-600"]:hover,
button[class*="hover:bg-amber-600"]:hover *,
a[class*="hover:bg-amber-600"]:hover,
a[class*="hover:bg-amber-600"]:hover *,
button[class*="hover:bg-amber-700"]:hover,
button[class*="hover:bg-amber-700"]:hover *,
a[class*="hover:bg-amber-700"]:hover,
a[class*="hover:bg-amber-700"]:hover *,
button[class*="hover:bg-emerald-600"]:hover,
button[class*="hover:bg-emerald-600"]:hover *,
a[class*="hover:bg-emerald-600"]:hover,
a[class*="hover:bg-emerald-600"]:hover *,
button[class*="hover:bg-emerald-700"]:hover,
button[class*="hover:bg-emerald-700"]:hover *,
a[class*="hover:bg-emerald-700"]:hover,
a[class*="hover:bg-emerald-700"]:hover * {
    color: #ffffff !important;
}

/* Form fields input and textarea */
input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="image"]),
textarea,
select {
    color: #0f172a !important;
    background-color: #ffffff;
}

input::placeholder,
textarea::placeholder {
    color: #64748b !important;
    opacity: 1 !important;
}

/* 4. LIQUID CAPSULE / PILL BADGES: STRICT HIGH CONTRAST */
.liquid-bubble-pill,
span[class*="rounded-full"],
div[class*="rounded-full"] {
    font-weight: 800 !important;
}

/* Light capsules & pills -> STRICT PURE BLACK TEXT */
span[class*="rounded-full"][class*="bg-white"],
span[class*="rounded-full"][class*="bg-slate-50"],
span[class*="rounded-full"][class*="bg-slate-100"],
span[class*="rounded-full"][class*="bg-zinc-100"],
span[class*="rounded-full"][class*="bg-stone-100"],
span[class*="rounded-full"][class*="bg-amber-50"],
span[class*="rounded-full"][class*="bg-amber-100"],
span[class*="rounded-full"][class*="bg-emerald-50"],
span[class*="rounded-full"][class*="bg-emerald-100"],
span[class*="rounded-full"][class*="bg-cyan-50"],
span[class*="rounded-full"][class*="bg-cyan-100"],
span[class*="rounded-full"][class*="bg-sky-50"],
span[class*="rounded-full"][class*="bg-sky-100"],
span[class*="rounded-full"][class*="bg-violet-50"],
span[class*="rounded-full"][class*="bg-violet-100"],
span[class*="rounded-full"][class*="bg-purple-50"],
span[class*="rounded-full"][class*="bg-purple-100"],
span[class*="rounded-full"][class*="bg-white"] *,
span[class*="rounded-full"][class*="bg-slate-50"] *,
span[class*="rounded-full"][class*="bg-slate-100"] *,
span[class*="rounded-full"][class*="bg-zinc-100"] *,
span[class*="rounded-full"][class*="bg-stone-100"] *,
span[class*="rounded-full"][class*="bg-amber-50"] *,
span[class*="rounded-full"][class*="bg-amber-100"] *,
span[class*="rounded-full"][class*="bg-emerald-50"] *,
span[class*="rounded-full"][class*="bg-emerald-100"] *,
span[class*="rounded-full"][class*="bg-cyan-50"] *,
span[class*="rounded-full"][class*="bg-cyan-100"] *,
span[class*="rounded-full"][class*="bg-sky-50"] *,
span[class*="rounded-full"][class*="bg-sky-100"] *,
span[class*="rounded-full"][class*="bg-violet-50"] *,
span[class*="rounded-full"][class*="bg-violet-100"] *,
span[class*="rounded-full"][class*="bg-purple-50"] *,
span[class*="rounded-full"][class*="bg-purple-100"] * {
    color: #09090b !important;
}

/* Dark capsules & pills -> STRICT PURE WHITE TEXT */
span[class*="rounded-full"][class*="bg-black"],
span[class*="rounded-full"][class*="bg-slate-900"],
span[class*="rounded-full"][class*="bg-slate-950"],
span[class*="rounded-full"][class*="bg-zinc-900"],
span[class*="rounded-full"][class*="bg-zinc-950"],
span[class*="rounded-full"][class*="bg-stone-900"],
span[class*="rounded-full"][class*="bg-rose-"],
span[class*="rounded-full"][class*="bg-red-"],
span[class*="rounded-full"][class*="bg-blue-"],
span[class*="rounded-full"][class*="bg-emerald-600"],
span[class*="rounded-full"][class*="bg-amber-600"],
span[class*="rounded-full"][class*="bg-black"] *,
span[class*="rounded-full"][class*="bg-slate-900"] *,
span[class*="rounded-full"][class*="bg-slate-950"] *,
span[class*="rounded-full"][class*="bg-zinc-900"] *,
span[class*="rounded-full"][class*="bg-zinc-950"] *,
span[class*="rounded-full"][class*="bg-stone-900"] *,
span[class*="rounded-full"][class*="bg-rose-"] *,
span[class*="rounded-full"][class*="bg-red-"] *,
span[class*="rounded-full"][class*="bg-blue-"] *,
span[class*="rounded-full"][class*="bg-emerald-600"] *,
span[class*="rounded-full"][class*="bg-amber-600"] * {
    color: #ffffff !important;
}

.dark .liquid-bubble-pill,
.dark [class*="rounded-full"][class*="backdrop-blur"] {
    color: #ffffff !important;
}

/* 6-in-1 Floating Nav Guaranteed Visibility across all viewports */
.k71-fab {
    display: block !important;
    position: fixed !important;
    z-index: 9999 !important;
    visibility: visible !important;
    opacity: 1 !important;
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
