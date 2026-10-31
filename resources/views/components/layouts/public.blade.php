@props([
    'title' => null, 
    'metaDescription' => null, 
    'metaKeywords' => null,
    'canonicalUrl' => null,
    'robots' => null,
    'ogImage' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogType' => 'website',
    'schemaJson' => null,
    'fullWidth' => false,
    'hideMainHeader' => false,
    'hideFooter' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ? (str_contains($title, config('k71.name', 'K71')) ? $title : $title . ' | ' . config('k71.name', 'K71')) : config('k71.name', 'K71') . ' - Multi-Vendor Marketplace & Video Commerce' }}</title>
    @if($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if($metaKeywords)
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    @if($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    <meta property="og:title" content="{{ $ogTitle ?? ($title ?? config('marketplace.name')) }}">
    @if($ogDescription ?? $metaDescription)
        <meta property="og:description" content="{{ $ogDescription ?? $metaDescription }}">
    @endif
    @if($ogImage)
        <meta property="og:image" content="{{ str_starts_with($ogImage, 'http') ? $ogImage : asset('storage/' . $ogImage) }}">
    @endif
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">

    @if(isset($schemaJson) && $schemaJson)
        <script type="application/ld+json">
            {!! is_array($schemaJson) ? json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $schemaJson !!}
        </script>
    @endif

    <!-- Favicon & PWA Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1F2A26">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts & Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Dancing+Script:wght@400;700&family=Outfit:wght@300;400;500;600;700&display=swap">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
    <!-- Enforce Pure Clean Light Mode on Marketplace -->
    <script>
        (function() {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        })();
    </script>
</head>
<body class="bg-[#f8fafc] text-slate-900 font-sans antialiased flex flex-col min-h-screen relative overflow-x-hidden selection:bg-indigo-600 selection:text-white">
    
    
    @php
        $detectedVertical = request()->attributes->get('vertical') ?? null;
        $isVerticalRoute = !empty($detectedVertical) 
            || request()->is('feasto*') 
            || request()->is('food*') 
            || request()->is('services*') 
            || request()->is('used*') 
            || request()->is('shorts*')
            || request()->routeIs('storefront.show')
            || app()->bound('current_vendor');
        $suppressMainHeader = $hideMainHeader || $isVerticalRoute;
    @endphp

    @if(!$suppressMainHeader)
        <!-- K71 Top Navigation Header -->
        <x-k71-nav />
    @endif

    <!-- K71 Glass Hamburger Menu Drawer -->
    <x-k71-hamburger />

    <!-- 6-in-1 Draggable Floating Radial Nav (Semi-Circle Arc + '*' Retract Button) -->
    <x-floating-six-nav />

    @if($fullWidth)
        <main class="flex-1 w-full pb-24 md:pb-16">
            {{ $slot }}
        </main>
    @else
        <main class="flex-1 w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 pb-24 md:pb-12">
            {{ $slot }}
        </main>
    @endif
    
    @if(!$hideFooter)
    <!-- LUXURY EDITORIAL ATELIER FOOTER -->
    @php
        $footerVendor = app()->bound('current_vendor') ? app('current_vendor') : null;
        $footerSocial = $footerVendor ? (is_array($footerVendor->social_links) ? $footerVendor->social_links : (is_string($footerVendor->social_links) ? (json_decode($footerVendor->social_links, true) ?: []) : [])) : [];
        $footerWa = $footerSocial['whatsapp'] ?? ($footerVendor ? $footerVendor->contact_phone : null);
        if ($footerWa) {
            $footerWaClean = preg_replace('/[^0-9]/', '', $footerWa);
            if (strlen($footerWaClean) === 10) { $footerWaClean = '91' . $footerWaClean; }
            $footerWaUrl = "https://wa.me/{$footerWaClean}";
        } else {
            $footerWaUrl = null;
        }

        $footerInsta = $footerSocial['instagram'] ?? null;
        if ($footerInsta) {
            $footerInstaHandle = ltrim($footerInsta, '@');
            $footerInstaUrl = Str::startsWith($footerInsta, 'http') ? $footerInsta : 'https://instagram.com/' . $footerInstaHandle;
        } else {
            $footerInstaUrl = null;
        }
    @endphp

    <footer class="bg-[#0C1310] text-stone-200 mt-16 border-t border-stone-800 rounded-t-[2.5rem] shadow-2xl relative overflow-hidden"
            x-data="{ 
                newsletterEmail: '', 
                newsletterSuccess: false,
                submitNewsletter() {
                    if (this.newsletterEmail && this.newsletterEmail.includes('@')) {
                        this.newsletterSuccess = true;
                        this.newsletterEmail = '';
                        setTimeout(() => this.newsletterSuccess = false, 5000);
                    }
                },
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }">

        <!-- Background Atmospheric Radial Texture -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-amber-200/40 to-transparent"></div>

        <!-- VENDOR STOREFRONT ATELIER BANNER (If browsing a boutique) -->
        @if($footerVendor)
            <div class="border-b border-stone-800/80 bg-stone-900/40 backdrop-blur-md py-6 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full border border-stone-700 bg-stone-800 flex items-center justify-center overflow-hidden shrink-0">
                            @if(!empty($footerVendor->logo_url))
                                <img src="{{ $footerVendor->logo_url }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                <span class="font-serif font-bold text-amber-200 text-base">{{ strtoupper(substr($footerVendor->shop_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-amber-300 block">Designer Boutique</span>
                            <h4 class="text-sm font-serif font-bold text-white">{{ $footerVendor->shop_name }}</h4>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-2">
                        @if($footerWaUrl)
                            <a href="{{ $footerWaUrl }}" target="_blank"
                               class="px-3.5 py-1.5 rounded-full bg-emerald-600/90 hover:bg-emerald-600 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                                <span>WhatsApp Stylist</span>
                            </a>
                        @endif

                        @if($footerInstaUrl)
                            <a href="{{ $footerInstaUrl }}" target="_blank"
                               class="px-3 py-1.5 rounded-full bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-bold transition-all border border-stone-700 flex items-center gap-1">
                                <span>📷 Instagram</span>
                            </a>
                        @endif

                        @php
                            $port = request()->getPort();
                            $portStr = ($port != 80 && $port != 443) ? ':' . $port : '';
                            $mktUrl = request()->getScheme() . '://' . config('marketplace.domain') . $portStr;
                        @endphp
                        <a href="{{ $mktUrl }}" 
                           class="px-3 py-1.5 rounded-full bg-stone-800/80 hover:bg-stone-700 text-stone-300 hover:text-white text-xs font-semibold transition-all border border-stone-700">
                            Explore Marketplace →
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- NEWSLETTER SECTION -->
        <div class="border-b border-zinc-800/80 py-10 px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="text-center lg:text-left max-w-xl">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-400 flex items-center justify-center lg:justify-start gap-1.5 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>K71 Newsletter</span>
                    </span>
                    <h3 class="text-xl sm:text-2xl font-sans font-bold text-white tracking-tight">Curated Releases &amp; Exclusive Drops</h3>
                    <p class="text-xs sm:text-sm text-zinc-400 mt-1 leading-relaxed">
                        Subscribe to receive timely updates on new arrivals, exclusive discounts, and seasonal launches.
                    </p>
                </div>

                <!-- Form -->
                <div class="w-full sm:w-auto flex-1 max-w-md">
                    <form @submit.prevent="submitNewsletter()" class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <input type="email" 
                                   x-model="newsletterEmail" 
                                   required
                                   placeholder="Enter your email address..." 
                                   class="w-full px-4 py-3 rounded-full bg-zinc-900/90 border border-zinc-700 text-white placeholder-zinc-500 text-xs focus:ring-1 focus:ring-white focus:border-white outline-none transition-all">
                        </div>
                        <button type="submit" 
                                class="px-6 py-3 rounded-full bg-white hover:bg-zinc-100 text-zinc-950 font-bold text-xs tracking-wider uppercase transition-all shadow-md shrink-0 active:scale-95">
                            Subscribe
                        </button>
                    </form>
                    <div x-show="newsletterSuccess" x-transition class="mt-2 text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>Subscribed! Welcome to K71. Check your inbox for updates.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN 4-COLUMN LUXURY NAVIGATION RAIL -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10 relative z-10">
            
            <!-- Column 1: Brand & Manifesto (Double column) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex flex-col">
                        <span class="font-sans text-2xl font-black text-white tracking-tight leading-none">
                            K71<span class="text-zinc-500 font-medium">.IN</span>
                        </span>
                        <span class="text-[9px] font-sans font-medium uppercase tracking-[0.22em] text-zinc-400 mt-1">
                            Curated Multi-Vendor Marketplace
                        </span>
                    </div>
                </div>

                <p class="text-xs text-zinc-400 leading-relaxed max-w-sm">
                    Connecting verified sellers, local cloud kitchens, services, and independent designers directly with customers across India.
                </p>

                <!-- Verified Merchant Legal Compliance Block -->
                <div class="mt-3 p-3.5 rounded-2xl bg-zinc-900/90 border border-zinc-800 text-xs space-y-2 max-w-sm">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-zinc-500 uppercase tracking-wider text-[10px]">Director / Management:</span>
                        <span class="font-medium text-zinc-200">Hashir</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-zinc-500 uppercase tracking-wider text-[10px]">MSME Registration:</span>
                        <span class="font-mono text-zinc-300 font-semibold">UDYAM-RJ-01-0176167</span>
                    </div>
                    <div class="border-t border-zinc-800/80 my-1"></div>
                    <div class="flex items-center justify-between text-[11px] pt-0.5">
                        <a href="tel:+919549311580" class="text-zinc-400 hover:text-white font-mono flex items-center gap-1.5 transition-colors">
                            <span>+91 95493 11580</span>
                        </a>
                        <a href="mailto:crab.hashir@gmail.com" class="text-zinc-400 hover:text-white font-mono flex items-center gap-1.5 transition-colors">
                            <span>crab.hashir@gmail.com</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Column 2: Shop & Categories -->
            <div>
                <h4 class="text-xs font-semibold tracking-wider uppercase text-zinc-200 mb-4 pb-1 border-b border-zinc-800/60 inline-block">
                    Shop
                </h4>
                <ul class="space-y-2 text-xs text-zinc-400">
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition-colors">All Products</a></li>
                    <li><a href="{{ route('catalog.index', ['gender' => 'women']) }}" class="hover:text-white transition-colors">Women's Collection</a></li>
                    <li><a href="{{ route('catalog.index', ['gender' => 'men']) }}" class="hover:text-white transition-colors">Men's Collection</a></li>
                    <li><a href="{{ route('catalog.index', ['sort' => 'newest']) }}" class="hover:text-white transition-colors">New Arrivals</a></li>
                    <li><a href="{{ route('shorts.index') }}" class="hover:text-white transition-colors">Video Shorts</a></li>
                </ul>
            </div>

            <!-- Column 3: Customer Support -->
            <div>
                <h4 class="text-xs font-semibold tracking-wider uppercase text-zinc-200 mb-4 pb-1 border-b border-zinc-800/60 inline-block">
                    Customer Care
                </h4>
                <ul class="space-y-2 text-xs text-zinc-400">
                    <li><a href="{{ route('pages.contact') }}" class="hover:text-white transition-colors">Contact Support</a></li>
                    <li><a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">Track Order</a></li>
                    <li><a href="{{ route('pages.shipping') }}" class="hover:text-white transition-colors">Shipping Policy</a></li>
                    <li><a href="{{ route('pages.refund') }}" class="hover:text-white transition-colors">Refund &amp; Return</a></li>
                    <li><a href="{{ route('pages.about') }}" class="hover:text-white transition-colors">About K71</a></li>
                </ul>
            </div>

            <!-- Column 4: Sell on K71 -->
            <div>
                <h4 class="text-xs font-semibold tracking-wider uppercase text-zinc-200 mb-4 pb-1 border-b border-zinc-800/60 inline-block">
                    Sell on K71
                </h4>
                <ul class="space-y-2 text-xs text-zinc-400">
                    <li>
                        <a href="{{ route('vendor.register') }}" class="text-zinc-200 hover:text-white font-medium transition-colors">
                            Open Seller Store
                        </a>
                    </li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Merchant Sign In</a></li>
                    <li><a href="{{ route('vendor.register') }}" class="hover:text-white transition-colors">Seller Policies</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="hover:text-white transition-colors">Terms of Service</a></li>
                </ul>
            </div>

        </div>

        <!-- TRUST PILLARS STRIP -->
        <div class="border-t border-zinc-800/80 bg-zinc-950/60 py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-center sm:text-left">
                <div class="flex items-center gap-3 justify-center sm:justify-start">
                    <div class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400">
                        <svg width="18" height="18" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold text-white">Verified Sellers</h5>
                        <p class="text-[10px] text-zinc-400">100% genuine products</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 justify-center sm:justify-start">
                    <div class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400">
                        <svg width="18" height="18" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V15.75m-19.5-.75h19.5"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold text-white">Express Delivery</h5>
                        <p class="text-[10px] text-zinc-400">Pan-India tracked courier</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 justify-center sm:justify-start">
                    <div class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400">
                        <svg width="18" height="18" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold text-white">Secure Payments</h5>
                        <p class="text-[10px] text-zinc-400">Encrypted UPI, Cards &amp; COD</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 justify-center sm:justify-start">
                    <div class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400">
                        <svg width="18" height="18" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold text-white">Easy Returns</h5>
                        <p class="text-[10px] text-zinc-400">Hassle-free replacement policy</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENT METHODS RAIL -->
        <div class="border-t border-zinc-800/80 py-4 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-[10px] uppercase font-medium tracking-wider text-zinc-500">Supported Payments:</span>
                <div class="flex flex-wrap items-center justify-center gap-1.5 text-[10px] font-medium text-zinc-400">
                    <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800">UPI</span>
                    <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800">PhonePe</span>
                    <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800">Google Pay</span>
                    <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800">Paytm</span>
                    <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800">Visa / Mastercard</span>
                    <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-800">Cash on Delivery</span>
                </div>
            </div>
        </div>

        <!-- BOTTOM LEGAL RAIL -->
        <div class="border-t border-zinc-800/80 py-5 px-4 sm:px-6 lg:px-8 bg-black/60">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-500">
                <div class="text-center sm:text-left">
                    <span>&copy; {{ date('Y') }} <strong>K71</strong>. Creative Agency &bull; ESTD 1992 (MSME: <span class="font-mono text-zinc-400">UDYAM-RJ-01-0176167</span>). All rights reserved.</span>
                </div>

                <div class="flex flex-wrap items-center justify-center sm:justify-end gap-3 text-xs">
                    <a href="{{ route('pages.privacy') }}" class="hover:text-zinc-300 transition-colors">Privacy Policy</a>
                    <span class="text-zinc-700">•</span>
                    <a href="{{ route('pages.terms') }}" class="hover:text-zinc-300 transition-colors">Terms</a>
                    <span class="text-zinc-700">•</span>
                    <a href="{{ route('pages.refund') }}" class="hover:text-zinc-300 transition-colors">Refunds</a>
                    <span class="text-zinc-700">•</span>
                    <a href="{{ route('pages.shipping') }}" class="hover:text-zinc-300 transition-colors">Shipping</a>
                    <span class="text-zinc-700">•</span>
                    <a href="{{ route('pages.contact') }}" class="hover:text-zinc-300 transition-colors">Contact</a>
                </div>
            </div>
        </div>

    </footer>
    @endif

    <!-- K71 Mobile Bottom Tab Navigation Removed per user request -->
    {{-- <x-k71-bottom-nav /> --}}

    <!-- K71 Mini-Cart Bottom Sheet / Drawer -->
    <x-k71-mini-cart />

    <!-- K71 Mini-Wishlist Drawer / Popup -->
    <x-k71-mini-wishlist />
    
    <!-- K71 Global Authentication Modal (Non-Negotiable) -->
    <x-k71-auth-modal />
    
    <!-- Global Floating Toast Container -->
    <div id="k71-toast-container" class="fixed bottom-20 sm:bottom-6 inset-x-0 flex justify-center items-center pointer-events-none z-50 px-4"></div>

    <script>
        function showToast(message, isSuccess = true) {
            const container = document.getElementById('k71-toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-zinc-900/95 text-white text-xs sm:text-sm font-medium px-4 py-2.5 rounded-full shadow-2xl border border-zinc-700/60 backdrop-blur-xl flex items-center gap-2 transform translate-y-3 opacity-0 transition-all duration-300';
            toast.innerHTML = `<span>${message}</span>`;
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-3', 'opacity-0');
            });
            setTimeout(() => {
                toast.classList.add('translate-y-3', 'opacity-0');
                setTimeout(() => toast.remove(), 350);
            }, 2400);
        }

        // Global Toast Helpers
        window.showToast = showToast;
        window.showK71Toast = showToast;

        function updateWishlistBadge(count) {
            const badge = document.getElementById('global-wishlist-badge');
            if (badge) {
                badge.textContent = count;
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badge.classList.add('scale-125');
                    setTimeout(() => badge.classList.remove('scale-125'), 200);
                } else {
                    badge.classList.add('hidden');
                }
            }
        }

        // Global in-flight lock map to prevent conflicting double-clicks
        window.wishlistInFlight = window.wishlistInFlight || {};

        window.toggleProductWishlist = function(productId, targetState, onErrorCallback) {
            if (window.wishlistInFlight[productId]) {
                return; // Prevent duplicate clicks while request is running
            }
            window.wishlistInFlight[productId] = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/wishlist/toggle', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    updateWishlistBadge(data.count);
                    showToast(data.message || (data.action === 'added' ? 'Added to Wishlist' : 'Removed from Wishlist'));
                    
                    // Broadcast event so all cards with this product stay in sync
                    window.dispatchEvent(new CustomEvent('wishlist-state-changed', {
                        detail: {
                            productId: productId,
                            isWishlisted: data.is_wishlisted,
                            count: data.count
                        }
                    }));
                } else {
                    if (typeof onErrorCallback === 'function') onErrorCallback();
                    showToast('Could not update wishlist', false);
                }
            })
            .catch(error => {
                console.error('Wishlist error:', error);
                if (typeof onErrorCallback === 'function') onErrorCallback();
                showToast('Unable to update wishlist. Please try again.', false);
            })
            .finally(() => {
                window.wishlistInFlight[productId] = false;
            });
        };

        // Legacy compatibility helper
        function addToWishlist(productId) {
            window.toggleProductWishlist(productId);
        }

        // CSRF Auto-Refresh & Session Heartbeat (Guarantees zero 419 Page Expired errors)
        (function() {
            function refreshCsrfToken() {
                return fetch('/api/heartbeat', {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.csrf_token) {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfMeta) csrfMeta.content = data.csrf_token;
                        document.querySelectorAll('input[name="_token"]').forEach(inp => {
                            inp.value = data.csrf_token;
                        });
                    }
                    return data?.csrf_token;
                })
                .catch(() => null);
            }
            setInterval(refreshCsrfToken, 60000);
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form && form.tagName === 'FORM') {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const tokenInput = form.querySelector('input[name="_token"]');
                    if (csrfMeta && tokenInput && csrfMeta.content) {
                        tokenInput.value = csrfMeta.content;
                    }
                }
            }, true);
        })();
    </script>

    @stack('scripts')
</body>
</html>
