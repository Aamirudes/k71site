<!DOCTYPE html>
<html lang="en" class="dark" data-brand="{{ $currentBrand ?? 'shorts' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoShort ? $seoShort->title . ' — K71 Shorts' : 'K71 Shorts — Shoppable Video Commerce & Trending Reels' }}</title>
    <meta name="description" content="{{ $seoShort ? ($seoShort->description ?: 'Watch ' . $seoShort->title . ' and shop tagged products on K71 Shorts.') : 'Explore trending fashion, food, lifestyle, and handicrafts in immersive shoppable short videos on K71.' }}">

    <!-- OpenGraph & Twitter Cards -->
    <meta property="og:title" content="{{ $seoShort ? $seoShort->title : 'K71 Shorts — Flipkart-Style Video Commerce' }}">
    <meta property="og:description" content="{{ $seoShort ? ($seoShort->description ?: 'Watch & shop directly on K71 Shorts') : 'Shop authentic products directly from trending vertical videos on K71.' }}">
    <meta property="og:image" content="{{ $seoShort ? ($seoShort->thumb_url ?: asset('images/logo.png')) : asset('images/logo.png') }}">
    <meta property="og:type" content="video.other">
    <meta name="twitter:card" content="summary_large_image">

    @if($seoShort)
    <!-- VideoObject Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "VideoObject",
        "name": "{{ addslashes($seoShort->title) }}",
        "description": "{{ addslashes($seoShort->description ?: $seoShort->title) }}",
        "thumbnailUrl": ["{{ $seoShort->thumb_url ?: asset('images/product-placeholder.svg') }}"],
        "uploadDate": "{{ $seoShort->created_at?->toIso8601String() ?? now()->toIso8601String() }}",
        "contentUrl": "{{ $seoShort->video_url }}",
        "embedUrl": "{{ route('shorts.show', $seoShort->id) }}"
    }
    </script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- K71 Core Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --fk-blue: #18181b;
            --fk-blue-dark: #09090b;
            --fk-yellow: #e4e4e7;
            --fk-orange: #ffffff;
            --fk-green: #22c55e;
            --fk-bg: #09090b;
            --fk-border: #27272a;
            --fk-text: #f4f4f5;
            --fk-muted: #71717a;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--fk-bg);
            color: var(--fk-text);
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        /* Hide scrollbars for slick horizontal rails */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Smooth scroll snap for reels */
        .reel-scroll-snap {
            scroll-snap-type: y mandatory;
            -webkit-overflow-scrolling: touch;
        }
        .reel-snap-item {
            scroll-snap-align: start;
            scroll-snap-stop: always;
        }

        /* Pulsing dot for live commerce */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.3); }
        }
        .live-dot {
            animation: pulse-dot 1.8s infinite ease-in-out;
        }

        /* Subtle heart pop animation */
        @keyframes heart-pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.35); }
            100% { transform: scale(1); }
        }
        .animate-heart-pop {
            animation: heart-pop 0.35s ease-out;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased bg-[#09090b] text-white selection:bg-white selection:text-black"
      x-data="flipkartShortsViewer({
          initialShorts: {{ Js::from($clientShorts) }},
          openId: {{ (int) $openId }},
          openCat: '{{ addslashes($openCat) }}',
          csrfToken: '{{ csrf_token() }}',
          cartAddUrl: '{{ route('cart.add') }}',
          cartCountUrl: '{{ route('cart.count') }}',
          ajaxUrl: '{{ route('shorts.ajax') }}'
      })"
      x-init="initApp()"
      @keydown.window="handleKeyboard($event)">

    <!-- Thin Sleek Top Strip: Back to K71 Universal Marketplace -->
    <div class="bg-black text-white text-xs border-b border-white/10 px-4 sm:px-6 py-2 flex items-center justify-between z-50 relative">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-zinc-300 hover:text-white transition font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to K71 Universal Marketplace</span>
            </a>
            <span class="text-zinc-600">|</span>
            <span class="text-rose-400 font-bold text-[11px] hidden sm:inline flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                K71 Shoppable Video Commerce
            </span>
        </div>
        <div class="flex items-center gap-4 text-[11px] text-zinc-400">
            <span class="hidden md:inline font-mono">IST UTC+5:30 (Rajasthan)</span>
            <a href="{{ route('vendor.shorts.index') }}" class="text-amber-300 hover:text-amber-200 font-bold transition flex items-center gap-1">
                <span>Upload Short Reel →</span>
            </a>
        </div>
    </div>

    <!-- 6-in-1 Draggable Floating Radial Nav (Semi-Circle Arc + '*' Retract Button) -->
    <x-floating-six-nav />

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MINIMAL WHITE & BLACK TOP NAVBAR -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <header class="sticky top-0 z-40 bg-black text-white border-b border-zinc-800 shadow-md">
        <!-- Top bar with brand & search -->
        <div class="max-w-7xl mx-auto px-4 py-2.5 flex items-center justify-between gap-3 sm:gap-6">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('shorts.index') }}" class="flex items-center gap-1.5 group">
                    <span class="text-xl sm:text-2xl font-black tracking-tight text-white group-hover:text-zinc-300 transition">K71</span>
                    <span class="inline-flex items-center gap-1 bg-zinc-800 text-white border border-zinc-700 text-[10px] sm:text-xs font-black uppercase px-2 py-0.5 rounded-full shadow-sm">
                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        SHORTS
                    </span>
                </a>
                <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] font-semibold text-zinc-300 bg-zinc-900 border border-zinc-800 px-2.5 py-0.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 live-dot"></span>
                    Live Video Shopping
                </span>
            </div>

            <!-- Instant Search Bar -->
            <div class="flex-1 max-w-xl relative">
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.250ms="filterShorts()"
                           placeholder="Search products, styles, reels or sellers..."
                           class="w-full bg-zinc-900 text-white placeholder-zinc-500 text-xs sm:text-sm pl-9 pr-8 py-2 rounded-lg border border-zinc-800 focus:border-white focus:outline-none transition">
                    <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button x-show="searchQuery.length > 0"
                            @click="searchQuery = ''; filterShorts()"
                            class="absolute right-2.5 top-2.5 text-zinc-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <!-- Switch to other K71 Verticals -->
                <div class="hidden lg:flex items-center gap-2 text-xs font-semibold">
                    <a href="{{ url('/') }}" class="text-zinc-400 hover:text-white px-2 py-1 rounded transition">Store</a>
                    <a href="{{ k71_vertical_url('food') }}" class="text-zinc-400 hover:text-white px-2 py-1 rounded transition">Food</a>
                    <a href="{{ k71_vertical_url('used') }}" class="text-zinc-400 hover:text-white px-2 py-1 rounded transition">Used</a>
                </div>

                <!-- Creator Studio Upload -->
                @auth
                <a href="{{ route('vendor.shorts.index') }}"
                   class="inline-flex items-center gap-1.5 bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-bold px-3 py-1.5 rounded-md border border-zinc-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline">Upload Reel</span>
                </a>
                @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1 text-xs font-bold text-white bg-zinc-800 hover:bg-zinc-700 px-3 py-1.5 rounded-md border border-zinc-700 transition">
                    <span class="hidden sm:inline">Seller</span> Studio
                </a>
                @endauth

                <!-- Cart Button with Live Counter -->
                <a href="{{ route('cart.index') }}"
                   aria-label="View Shopping Cart"
                   class="relative inline-flex items-center gap-1.5 bg-white hover:bg-zinc-200 text-black font-extrabold text-xs sm:text-sm px-3.5 py-1.5 rounded-md shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="hidden sm:inline">Cart</span>
                    <span x-show="cartCount > 0"
                          x-text="cartCount"
                          class="bg-black text-white text-[11px] font-black w-4 h-4 rounded-full flex items-center justify-center -ml-0.5"></span>
                </a>
            </div>
        </div>

        <!-- Horizontal Category Navigation Strip -->
        <div class="bg-zinc-950 border-b border-zinc-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2 text-xs font-semibold">
                    <button @click="setActiveCat('')"
                            :class="activeCat === '' ? 'bg-white text-black font-black shadow-sm' : 'bg-zinc-900 text-zinc-300 hover:bg-zinc-800 border border-zinc-800'"
                            class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap flex items-center gap-1">
                        <span>🔥</span> All Reels
                    </button>
                    @foreach($categories as $c)
                    <button @click="setActiveCat('{{ $c['slug'] }}')"
                            :class="activeCat === '{{ $c['slug'] }}' ? 'bg-white text-black font-black shadow-sm' : 'bg-zinc-900 text-zinc-300 hover:bg-zinc-800 border border-zinc-800'"
                            class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap">
                        {{ $c['name'] }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MAIN DISCOVERY HOMEPAGE -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-3 sm:px-6 py-4 space-y-6">

        <!-- 1. HERO SPOTLIGHT SHOPPABLE BANNER (MINIMAL B&W) -->
        @if($heroShort)
        <section class="bg-zinc-900 rounded-2xl shadow-sm border border-zinc-800 overflow-hidden text-white">
            <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <!-- Left: 9:16 Video Player Preview Card -->
                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative w-full max-w-[280px] sm:max-w-[310px] aspect-[9/16] rounded-2xl overflow-hidden shadow-lg bg-black cursor-pointer group"
                         @click="openPlayerById({{ $heroShort->id }})">
                        <img src="{{ $heroShort->thumb_url }}"
                             alt="{{ $heroShort->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        
                        <!-- Top Badges -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5">
                            <span class="bg-white text-black text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 live-dot"></span> SPOTLIGHT
                            </span>
                        </div>
                        <div class="absolute top-3 right-3 bg-black/60 backdrop-blur text-white text-[11px] font-medium px-2 py-0.5 rounded-md">
                            {{ $heroShort->duration ? gmdate('i:s', $heroShort->duration) : '0:30' }}
                        </div>

                        <!-- Center Play Button Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-14 h-14 rounded-full bg-white/95 text-black flex items-center justify-center shadow-xl group-hover:scale-110 transition duration-300">
                                <svg class="w-7 h-7 fill-current ml-1" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </div>
                        </div>

                        <!-- Bottom Card Overlay -->
                        <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/90 via-black/50 to-transparent text-white">
                            <div class="flex items-center gap-2 mb-1">
                                <img src="{{ $heroShort->vendor?->logo ? asset('storage/' . $heroShort->vendor->logo) : asset('images/default-avatar.png') }}"
                                     class="w-6 h-6 rounded-full object-cover border border-white/40">
                                <span class="text-xs font-semibold truncate">{{ $heroShort->vendor?->shop_name ?? 'Verified Store' }}</span>
                            </div>
                            <p class="text-xs font-bold line-clamp-1 text-zinc-100">{{ $heroShort->title }}</p>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-zinc-300">
                                <span>{{ \App\Models\Short::formatNumber($heroShort->views) }} views</span>
                                <span class="text-zinc-300 font-bold">🛍️ {{ $heroShort->products->count() }} Tagged Products</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Featured Product Showcase in This Video -->
                <div class="lg:col-span-7 flex flex-col justify-center">
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                        <span>✦ WATCH &amp; BUY DIRECTLY</span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-black text-white leading-snug">
                        {{ $heroShort->title }}
                    </h1>
                    <p class="text-xs sm:text-sm text-zinc-400 mt-2 line-clamp-2">
                        {{ $heroShort->description ?: 'Discover authentic quality products showcased in high-definition video. Instant delivery, verified ratings, and easy checkout.' }}
                    </p>

                    <!-- Tagged Products Mini Carousel / Cards -->
                    @if($heroShort->products->isNotEmpty())
                    @php $firstProd = $heroShort->products->first(); @endphp
                    <div class="mt-4 p-3 sm:p-4 bg-zinc-950 border border-zinc-800 rounded-xl flex flex-col sm:flex-row items-center gap-4">
                        <img src="{{ $firstProd->primary_image_url }}"
                             alt="{{ $firstProd->title }}"
                             class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-lg border border-zinc-800 shrink-0">
                        
                        <div class="flex-1 w-full text-center sm:text-left">
                            <div class="flex items-center justify-center sm:justify-start gap-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-zinc-800 text-white border border-zinc-700">
                                    ★ {{ number_format($firstProd->avg_rating ?? 4.8, 1) }}
                                </span>
                                <span class="text-xs text-zinc-400">({{ $firstProd->review_count ?? 24 }} ratings)</span>
                                <span class="text-xs text-emerald-400 font-bold">In Stock</span>
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-white mt-1 line-clamp-1">
                                {{ $firstProd->title }}
                            </h2>
                            <div class="mt-1 flex items-baseline justify-center sm:justify-start gap-2">
                                <span class="text-base sm:text-xl font-black text-white">₹{{ number_format($firstProd->base_price / 100, 0) }}</span>
                                @if($firstProd->compare_at_price && ($firstProd->compare_at_price > $firstProd->base_price))
                                <span class="text-xs text-zinc-400 line-through">₹{{ number_format($firstProd->compare_at_price / 100, 0) }}</span>
                                <span class="text-xs font-bold text-emerald-400">
                                    {{ round((1 - ($firstProd->base_price / $firstProd->compare_at_price)) * 100) }}% OFF
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col w-full sm:w-auto gap-2 shrink-0">
                            <button @click="addToCartDirect({{ $firstProd->id }})"
                                    class="w-full sm:w-auto bg-white hover:bg-zinc-200 text-black font-extrabold text-xs sm:text-sm px-5 py-2.5 rounded-lg shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Add to Cart
                            </button>
                            <button @click="openPlayerById({{ $heroShort->id }})"
                                    class="w-full sm:w-auto bg-zinc-800 hover:bg-zinc-700 text-white font-bold text-xs sm:text-sm px-5 py-2.5 rounded-lg border border-zinc-700 shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Watch Video
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        <!-- 2. TRENDING SHOPPABLE REELS RAIL -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🔥</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-white">Trending Shoppable Reels</h2>
                        <p class="text-xs text-zinc-400">Swipe to watch customer favorites &amp; creator showcases</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-zinc-300">Scroll ➔</span>
            </div>

            <div class="flex gap-3 sm:gap-4 overflow-x-auto no-scrollbar pb-2">
                @foreach($trendingShorts as $s)
                <div class="flex-none w-[160px] sm:w-[190px] aspect-[9/16] rounded-xl overflow-hidden bg-black shadow-sm border border-slate-200 relative group cursor-pointer"
                     @click="openPlayerById({{ $s->id }})">
                    <img src="{{ $s->thumb_url }}"
                         alt="{{ $s->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    
                    <!-- Views Badge -->
                    <div class="absolute top-2 left-2 bg-black/60 backdrop-blur text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                        <svg class="w-3 h-3 fill-current text-amber-400" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                        {{ \App\Models\Short::formatNumber($s->views) }}
                    </div>

                    <!-- Duration Badge -->
                    <div class="absolute top-2 right-2 bg-black/60 text-white text-[10px] font-medium px-1.5 py-0.5 rounded">
                        {{ $s->duration ? gmdate('i:s', $s->duration) : '0:28' }}
                    </div>

                    <!-- Bottom Info -->
                    <div class="absolute bottom-0 inset-x-0 p-2.5 bg-gradient-to-t from-black/90 via-black/60 to-transparent text-white">
                        <p class="text-xs font-bold line-clamp-1">{{ $s->title }}</p>
                        @if($s->products->isNotEmpty())
                        <div class="mt-1 inline-flex items-center gap-1 bg-amber-500/90 text-slate-900 text-[10px] font-extrabold px-1.5 py-0.5 rounded">
                            🛍️ ₹{{ number_format($s->products->first()->base_price / 100, 0) }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- 3. "SHOP WHAT YOU WATCH" LIVE PRODUCT RAIL -->
        @if($shoppableProducts->isNotEmpty())
        <section class="bg-zinc-900 rounded-2xl shadow-sm border border-zinc-800 p-4 sm:p-5 space-y-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                        <span>🛍️</span> Shop What You Watch
                    </h2>
                    <p class="text-xs text-zinc-400">Genuine catalog products featured in popular reels</p>
                </div>
                <a href="{{ url('/') }}" class="text-xs font-bold text-zinc-300 hover:text-white underline">View All Products ➔</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($shoppableProducts as $p)
                @php
                    $price = $p->base_price / 100;
                    $compare = $p->compare_at_price ? ($p->compare_at_price / 100) : null;
                    $isOnSale = $compare && ($compare > $price);
                    $disc = $isOnSale ? round((1 - ($price / $compare)) * 100) : 0;
                @endphp
                <div class="bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 flex flex-col justify-between hover:border-zinc-600 transition group">
                    <a href="{{ route('product.show', $p->slug) }}" class="block">
                        <div class="relative aspect-square rounded-lg overflow-hidden bg-zinc-900">
                            <img src="{{ $p->primary_image_url }}"
                                 alt="{{ $p->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @if($disc > 0)
                            <span class="absolute top-1 left-1 bg-white text-black text-[10px] font-black px-1.5 py-0.5 rounded shadow">
                                {{ $disc }}% OFF
                            </span>
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-white mt-2 line-clamp-2 leading-tight group-hover:text-zinc-300 transition">
                            {{ $p->title }}
                        </h3>
                    </a>

                    <div class="mt-2 pt-2 border-t border-zinc-800">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-sm font-black text-white">₹{{ number_format($price, 0) }}</span>
                            @if($compare)
                            <span class="text-[10px] text-zinc-400 line-through">₹{{ number_format($compare, 0) }}</span>
                            @endif
                        </div>
                        <button @click="addToCartDirect({{ $p->id }})"
                                class="w-full mt-2 bg-white hover:bg-zinc-200 text-black font-extrabold text-[11px] py-1.5 rounded shadow-sm transition flex items-center justify-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add to Cart
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- 4. VERIFIED CREATOR & BOUTIQUE SHOWCASE -->
        @if($creators->isNotEmpty())
        <section class="bg-zinc-900 rounded-2xl shadow-sm border border-zinc-800 p-4 sm:p-5 space-y-3 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                        <span>✨</span> Verified Creators &amp; Boutiques
                    </h2>
                    <p class="text-xs text-zinc-400">Shop curated collections from trusted vendors &amp; master artisans</p>
                </div>
            </div>

            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
                @foreach($creators as $c)
                <div class="flex-none w-[170px] bg-zinc-950 border border-zinc-800 rounded-xl p-3 text-center flex flex-col items-center justify-between">
                    <img src="{{ $c->logo ? asset('storage/' . $c->logo) : asset('images/default-avatar.png') }}"
                         alt="{{ $c->shop_name }}"
                         class="w-14 h-14 rounded-full object-cover border border-zinc-700 shadow-sm">
                    <h3 class="text-xs font-bold text-white mt-2 line-clamp-1">{{ $c->shop_name }}</h3>
                    <span class="text-[10px] font-semibold text-zinc-400 mt-0.5">{{ $c->shorts_count }} Shoppable Reels</span>
                    <a href="{{ route('storefront.show', $c->slug) }}"
                       class="mt-2.5 w-full bg-zinc-800 hover:bg-zinc-700 text-white border border-zinc-700 font-bold text-[11px] py-1 rounded transition">
                        Visit Store
                    </a>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- 5. ALL SHOPPABLE VIDEOS GRID -->
        <section class="space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white">Explore All Shoppable Videos</h2>
                    <p class="text-xs text-zinc-400" x-text="`Showing ${filteredShorts.length} verified videos`"></p>
                </div>
            </div>

            <!-- Responsive Video Grid: 2 cols on mobile, 4 cols on desktop -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
                <template x-for="(short, index) in filteredShorts" :key="short.id">
                    <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-800 overflow-hidden flex flex-col group cursor-pointer hover:border-zinc-600 transition"
                         @click="openPlayerByIndex(index)">
                        
                        <!-- 9:16 Video Thumbnail Container -->
                        <div class="relative aspect-[9/16] bg-black overflow-hidden">
                            <img :src="short.thumb_url"
                                 :alt="short.title"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            
                            <!-- Duration & Views Badges -->
                            <div class="absolute top-2 left-2 bg-black/70 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 fill-current text-white" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                <span x-text="short.views"></span>
                            </div>

                            <!-- Play Icon Hover -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <div class="w-12 h-12 rounded-full bg-white/95 text-black flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 fill-current ml-0.5" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </div>
                            </div>

                            <!-- Floating Tagged Products Badge -->
                            <template x-if="short.products_detail && short.products_detail.length > 0">
                                <div class="absolute bottom-2 left-2 right-2 bg-black/90 backdrop-blur-sm rounded-lg p-1.5 border border-zinc-700 shadow-md flex items-center gap-2">
                                    <img :src="short.products_detail[0].image"
                                         class="w-7 h-7 rounded object-cover border border-zinc-700 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-bold text-zinc-100 truncate" x-text="short.products_detail[0].title"></div>
                                        <div class="text-[11px] font-black text-white" x-text="short.products_detail[0].price"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-black bg-white px-2 py-0.5 rounded shrink-0">Shop</span>
                                </div>
                            </template>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-2.5 flex-1 flex flex-col justify-between">
                            <h3 class="text-xs font-bold text-white line-clamp-2 leading-tight group-hover:text-zinc-300 transition"
                                x-text="short.title"></h3>
                            <div class="mt-2 flex items-center justify-between text-[11px] text-zinc-400 pt-1.5 border-t border-zinc-800">
                                <span class="truncate font-medium" x-text="short.vendor_name || 'Verified Seller'"></span>
                                <span class="flex items-center gap-0.5 text-white font-semibold">
                                    ❤️ <span x-text="short.likes"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </main>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- IMMERSIVE 9:16 VERTICAL VIDEO PLAYER MODAL -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="playerOpen"
         x-cloak
         class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex items-center justify-center overflow-hidden"
         @click.self="closePlayer()">

        <!-- Desktop Outer Frame: 9:16 Centered Phone Mockup -->
        <div class="relative w-full h-full sm:h-[94vh] sm:max-h-[880px] sm:max-w-[440px] sm:rounded-2xl bg-black overflow-hidden flex flex-col shadow-2xl border sm:border-slate-800"
             @touchstart="touchStart($event)"
             @touchend="touchEnd($event)">

            <!-- Top Overlay Controls -->
            <div class="absolute top-0 inset-x-0 z-30 p-4 bg-gradient-to-b from-black/80 via-black/40 to-transparent flex items-center justify-between text-white">
                <!-- Back / Close Button -->
                <button @click="closePlayer()"
                        class="w-9 h-9 rounded-full bg-black/40 backdrop-blur flex items-center justify-center hover:bg-black/70 transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- K71 Shorts Video Shopping Badge -->
                <div class="flex items-center gap-1.5 bg-black/80 text-white text-[11px] font-black px-2.5 py-1 rounded-full border border-white/20 shadow">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 live-dot"></span>
                    K71 SHORTS
                </div>

                <!-- Mute / Unmute Toggle -->
                <button @click="toggleMute()"
                        class="w-9 h-9 rounded-full bg-black/40 backdrop-blur flex items-center justify-center hover:bg-black/70 transition">
                    <span x-show="!isMuted" class="text-base">🔊</span>
                    <span x-show="isMuted" class="text-base">🔇</span>
                </button>
            </div>

            <!-- Main Native HTML5 Video Element -->
            <div class="relative flex-1 bg-black flex items-center justify-center overflow-hidden cursor-pointer"
                 @click="togglePlay()">
                <video x-ref="videoElement"
                       class="w-full h-full object-cover"
                       playsinline
                       webkit-playsinline
                       loop
                       :muted="isMuted"
                       @timeupdate="updateProgress()"
                       @play="isPlaying = true"
                       @pause="isPlaying = false"
                       @ended="nextVideo()">
                </video>

                <!-- Animated Play / Pause Indicator -->
                <div x-show="!isPlaying"
                     class="absolute inset-0 flex items-center justify-center pointer-events-none bg-black/20">
                    <div class="w-16 h-16 rounded-full bg-black/60 backdrop-blur text-white flex items-center justify-center shadow-2xl">
                        <svg class="w-8 h-8 fill-current ml-1" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                </div>
            </div>

            <!-- Right-Side Interactive Floating Action Rail -->
            <div class="absolute right-3 bottom-24 z-30 flex flex-col items-center gap-4 text-white">
                <!-- Creator Avatar & Follow -->
                <div class="relative flex flex-col items-center">
                    <img :src="currentShort?.vendor_avatar || '{{ asset('images/default-avatar.png') }}'"
                         class="w-11 h-11 rounded-full object-cover border-2 border-white shadow-lg">
                    <button @click.stop="toggleFollow()"
                            class="absolute -bottom-1.5 w-5 h-5 rounded-full bg-white text-black flex items-center justify-center text-xs font-bold shadow">
                        <span x-text="isFollowed ? '✓' : '+'"></span>
                    </button>
                </div>

                <!-- Like Button -->
                <button @click.stop="toggleLike()"
                        class="flex flex-col items-center gap-1 group">
                    <div class="w-11 h-11 rounded-full bg-black/40 backdrop-blur flex items-center justify-center group-hover:scale-110 transition"
                         :class="{ 'animate-heart-pop': isLiked }">
                        <svg class="w-6 h-6 transition"
                             :class="isLiked ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-white'"
                             viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold drop-shadow" x-text="currentShort?.likes || '0'"></span>
                </button>

                <!-- Tagged Products Button (Triggers Bottom Sheet) -->
                <button @click.stop="drawerOpen = true"
                        class="flex flex-col items-center gap-1 group">
                    <div class="w-11 h-11 rounded-full bg-white text-black flex items-center justify-center font-bold shadow-lg group-hover:scale-110 transition relative">
                        <span class="text-lg">🛍️</span>
                        <span class="absolute -top-1 -right-1 bg-black text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white"
                              x-text="currentShort?.products_detail?.length || 0"></span>
                    </div>
                    <span class="text-[11px] font-bold drop-shadow">Shop</span>
                </button>

                <!-- Share Button -->
                <button @click.stop="shareVideo()"
                        class="flex flex-col items-center gap-1 group">
                    <div class="w-11 h-11 rounded-full bg-black/40 backdrop-blur flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    </div>
                    <span class="text-[11px] font-bold drop-shadow" x-text="currentShort?.shares || '0'"></span>
                </button>

                <!-- Next / Prev desktop arrows -->
                <div class="hidden sm:flex flex-col gap-2 pt-2">
                    <button @click.stop="prevVideo()"
                            class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur flex items-center justify-center transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <button @click.stop="nextVideo()"
                            class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur flex items-center justify-center transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Bottom Left Info & Floating Mini Product Card -->
            <div class="absolute bottom-4 left-3 right-16 z-30 text-white pointer-events-none">
                <!-- Creator Tag -->
                <div class="flex items-center gap-2 mb-1 pointer-events-auto">
                    <span class="text-xs sm:text-sm font-black drop-shadow" x-text="currentShort?.vendor_name"></span>
                    <span class="text-white text-xs">✓</span>
                </div>

                <!-- Video Title -->
                <p class="text-xs sm:text-sm font-medium line-clamp-2 drop-shadow mb-2 text-zinc-100"
                   x-text="currentShort?.title"></p>

                <!-- Floating Minimal Shoppable Mini-Card -->
                <template x-if="currentShort?.products_detail && currentShort.products_detail.length > 0">
                    <div class="pointer-events-auto bg-black/90 backdrop-blur-md text-white rounded-xl p-2.5 shadow-2xl border border-zinc-700 flex items-center gap-3 cursor-pointer hover:bg-black transition"
                         @click="drawerOpen = true">
                        <img :src="currentShort.products_detail[0].image"
                             class="w-12 h-12 rounded-lg object-cover border border-zinc-700 shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-white truncate" x-text="currentShort.products_detail[0].title"></h4>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span class="text-sm font-black text-white" x-text="currentShort.products_detail[0].price"></span>
                                <span x-show="currentShort.products_detail[0].original_price"
                                      class="text-[10px] text-zinc-500 line-through"
                                      x-text="currentShort.products_detail[0].original_price"></span>
                                <span x-show="currentShort.products_detail[0].discount_pct > 0"
                                      class="text-[10px] font-black text-white bg-zinc-800 px-1 rounded border border-zinc-700"
                                      x-text="`${currentShort.products_detail[0].discount_pct}% OFF`"></span>
                            </div>
                        </div>
                        <button class="bg-white text-black font-black text-xs px-3 py-1.5 rounded-lg shrink-0 shadow">
                            SHOP ➔
                        </button>
                    </div>
                </template>
            </div>

            <!-- Video Playback Progress Bar (Scrubber) -->
            <div class="absolute bottom-0 inset-x-0 h-1 bg-white/20 z-40">
                <div class="h-full bg-white transition-all duration-100"
                     :style="`width: ${progressPct}%`"></div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- SHOPPABLE BOTTOM SHEET / DRAWER -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div x-show="drawerOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute inset-x-0 bottom-0 max-h-[75%] bg-zinc-900 rounded-t-2xl z-50 shadow-2xl flex flex-col text-white border-t border-zinc-800 overflow-hidden">
                
                <!-- Drawer Header -->
                <div class="p-3.5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🛍️</span>
                        <h3 class="text-sm font-black text-white">Products in this Video</h3>
                        <span class="bg-white text-black text-[10px] font-black px-1.5 py-0.2 rounded-full"
                              x-text="currentShort?.products_detail?.length || 0"></span>
                    </div>
                    <button @click="drawerOpen = false"
                            class="w-7 h-7 rounded-full bg-zinc-800 hover:bg-zinc-700 text-zinc-300 flex items-center justify-center transition">
                        ✕
                    </button>
                </div>

                <!-- Product List in Drawer -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4 no-scrollbar">
                    <template x-for="item in currentShort?.products_detail || []" :key="item.id">
                        <div class="p-3 bg-zinc-950 border border-zinc-800 rounded-xl shadow-sm flex flex-col gap-3">
                            <div class="flex gap-3">
                                <img :src="item.image"
                                     :alt="item.title"
                                     class="w-20 h-20 rounded-lg object-cover border border-zinc-800 shrink-0">
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="bg-zinc-800 text-white text-[10px] font-black px-1.5 py-0.5 rounded border border-zinc-700"
                                              x-text="`★ ${item.rating}`"></span>
                                        <span class="text-[10px] text-zinc-400" x-text="`(${item.reviews_count} reviews)`"></span>
                                    </div>
                                    <h4 class="text-xs font-bold text-white line-clamp-2 leading-tight" x-text="item.title"></h4>
                                    
                                    <div class="flex items-baseline gap-2 mt-1">
                                        <span class="text-base font-black text-white" x-text="item.price"></span>
                                        <span x-show="item.original_price"
                                              class="text-xs text-zinc-400 line-through"
                                              x-text="item.original_price"></span>
                                        <span x-show="item.discount_pct > 0"
                                              class="text-xs font-bold text-emerald-400"
                                              x-text="`${item.discount_pct}% OFF`"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Drawer CTAs: Add to Cart (AJAX) & Buy Now -->
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-800">
                                <button @click="addToCart(item.id)"
                                        class="w-full bg-white hover:bg-zinc-200 text-black font-extrabold text-xs py-2 rounded-lg shadow-sm transition flex items-center justify-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    Add to Cart
                                </button>
                                <button @click="buyNow(item.id)"
                                        class="w-full bg-zinc-800 hover:bg-zinc-700 text-white font-extrabold text-xs py-2 rounded-lg border border-zinc-700 shadow-sm transition flex items-center justify-center gap-1 cursor-pointer">
                                    ⚡ Buy Now
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- FLOATING TOAST NOTIFICATION -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="toast.show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed bottom-6 right-6 z-50 bg-black text-white text-xs sm:text-sm font-bold px-4 py-3 rounded-xl shadow-2xl border border-zinc-800 flex items-center gap-3">
        <span x-text="toast.icon || '🛒'"></span>
        <span x-text="toast.message"></span>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- FOOTER -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <footer class="mt-12 bg-black border-t border-zinc-800 py-8 text-center text-xs text-zinc-400">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-black text-white">K71 SHORTS</span>
                <span>• India's Premier Shoppable Video Experience</span>
            </div>
            <div class="flex items-center gap-4 font-semibold text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-white">Main Store</a>
                <a href="{{ k71_vertical_url('food') }}" class="hover:text-white">Food</a>
                <a href="{{ k71_vertical_url('used') }}" class="hover:text-white">Used</a>
                <a href="{{ route('pages.terms') }}" class="hover:text-white">Terms</a>
                <a href="{{ route('pages.privacy') }}" class="hover:text-white">Privacy</a>
            </div>
        </div>
    </footer>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- FLIPKART SHORTS REACTIVE ENGINE (ALPINES / JS) -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <script>
        window.ZSU = @json($zsuConfig);

        function flipkartShortsViewer(config) {
            return {
                allShorts: config.initialShorts || [],
                filteredShorts: config.initialShorts || [],
                activeCat: config.openCat || '',
                searchQuery: '',
                playerOpen: false,
                currentIndex: 0,
                isPlaying: false,
                isMuted: true,
                progressPct: 0,
                drawerOpen: false,
                cartCount: 0,
                isLiked: false,
                isFollowed: false,
                touchStartY: 0,
                toast: { show: false, message: '', icon: '' },

                get currentShort() {
                    return this.filteredShorts[this.currentIndex] || null;
                },

                initApp() {
                    this.fetchCartCount();

                    // If openId deep-linked, open immediately
                    if (config.openId > 0) {
                        this.openPlayerById(config.openId);
                    }
                },

                filterShorts() {
                    let list = this.allShorts;
                    if (this.activeCat) {
                        list = list.filter(s => {
                            if (!s.categories) return false;
                            const slug = this.activeCat.toLowerCase();
                            return s.categories.some(c => c.toLowerCase().includes(slug) || slug.includes(c.toLowerCase()));
                        });
                    }
                    if (this.searchQuery.trim().length > 0) {
                        const q = this.searchQuery.toLowerCase();
                        list = list.filter(s => {
                            return s.title.toLowerCase().includes(q) ||
                                   (s.vendor_name && s.vendor_name.toLowerCase().includes(q)) ||
                                   (s.products_detail && s.products_detail.some(p => p.title.toLowerCase().includes(q)));
                        });
                    }
                    this.filteredShorts = list;
                },

                setActiveCat(catSlug) {
                    this.activeCat = catSlug;
                    this.filterShorts();
                },

                openPlayerByIndex(index) {
                    if (index < 0 || index >= this.filteredShorts.length) return;
                    this.currentIndex = index;
                    this.playerOpen = true;
                    this.drawerOpen = false;
                    this.isLiked = false;
                    this.isFollowed = false;
                    this.$nextTick(() => {
                        this.loadCurrentVideo();
                    });
                },

                openPlayerById(id) {
                    const idx = this.filteredShorts.findIndex(s => s.id === id);
                    if (idx !== -1) {
                        this.openPlayerByIndex(idx);
                    } else {
                        // If not in filtered list, check allShorts
                        const fullIdx = this.allShorts.findIndex(s => s.id === id);
                        if (fullIdx !== -1) {
                            this.filteredShorts = this.allShorts;
                            this.openPlayerByIndex(fullIdx);
                        }
                    }
                },

                loadCurrentVideo() {
                    const video = this.$refs.videoElement;
                    if (!video || !this.currentShort) return;

                    video.src = this.currentShort.video_url;
                    video.load();
                    video.muted = this.isMuted;
                    video.play().then(() => {
                        this.isPlaying = true;
                    }).catch(() => {
                        // Autoplay policy fallback: ensure muted and retry
                        video.muted = true;
                        this.isMuted = true;
                        video.play().then(() => {
                            this.isPlaying = true;
                        }).catch(e => console.warn('Autoplay blocked:', e));
                    });

                    // Record view event via AJAX
                    this.recordEvent('zsu_record_view', { short_id: this.currentShort.id });
                },

                togglePlay() {
                    const video = this.$refs.videoElement;
                    if (!video) return;
                    if (video.paused) {
                        video.play();
                        this.isPlaying = true;
                    } else {
                        video.pause();
                        this.isPlaying = false;
                    }
                },

                toggleMute() {
                    const video = this.$refs.videoElement;
                    if (!video) return;
                    this.isMuted = !this.isMuted;
                    video.muted = this.isMuted;
                },

                updateProgress() {
                    const video = this.$refs.videoElement;
                    if (!video || !video.duration) return;
                    this.progressPct = (video.currentTime / video.duration) * 100;
                },

                nextVideo() {
                    if (this.currentIndex < this.filteredShorts.length - 1) {
                        this.currentIndex++;
                    } else {
                        this.currentIndex = 0; // Loop back to start
                    }
                    this.drawerOpen = false;
                    this.isLiked = false;
                    this.$nextTick(() => this.loadCurrentVideo());
                },

                prevVideo() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                    } else {
                        this.currentIndex = this.filteredShorts.length - 1;
                    }
                    this.drawerOpen = false;
                    this.isLiked = false;
                    this.$nextTick(() => this.loadCurrentVideo());
                },

                closePlayer() {
                    const video = this.$refs.videoElement;
                    if (video) video.pause();
                    this.playerOpen = false;
                    this.drawerOpen = false;
                },

                touchStart(e) {
                    this.touchStartY = e.touches[0].clientY;
                },

                touchEnd(e) {
                    const touchEndY = e.changedTouches[0].clientY;
                    const diff = this.touchStartY - touchEndY;
                    if (Math.abs(diff) > 50) {
                        if (diff > 0) {
                            this.nextVideo(); // Swipe Up -> Next
                        } else {
                            this.prevVideo(); // Swipe Down -> Prev
                        }
                    }
                },

                handleKeyboard(e) {
                    if (!this.playerOpen) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.nextVideo();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.prevVideo();
                    } else if (e.key === ' ') {
                        e.preventDefault();
                        this.togglePlay();
                    } else if (e.key === 'Escape') {
                        this.closePlayer();
                    } else if (e.key === 'm' || e.key === 'M') {
                        this.toggleMute();
                    }
                },

                toggleLike() {
                    this.isLiked = !this.isLiked;
                    if (this.currentShort) {
                        this.recordEvent('zsu_record_like', { short_id: this.currentShort.id });
                        this.showToast(this.isLiked ? 'Liked this Reel! ❤️' : 'Removed Like', '❤️');
                    }
                },

                toggleFollow() {
                    this.isFollowed = !this.isFollowed;
                    if (this.currentShort && this.currentShort.vendor_id) {
                        this.recordEvent('zsu_follow_vendor', { vendor_id: this.currentShort.vendor_id });
                        this.showToast(this.isFollowed ? 'Following Store! ✓' : 'Unfollowed Store', '✨');
                    }
                },

                shareVideo() {
                    const shareUrl = window.location.origin + '/shorts/v/' + (this.currentShort?.id || '');
                    if (navigator.share) {
                        navigator.share({
                            title: this.currentShort?.title || 'K71 Shorts',
                            url: shareUrl
                        }).catch(() => {});
                    } else {
                        navigator.clipboard.writeText(shareUrl).then(() => {
                            this.showToast('Reel Link Copied to Clipboard!', '🔗');
                        });
                    }
                    if (this.currentShort) {
                        this.recordEvent('zsu_record_share', { short_id: this.currentShort.id, platform: 'web' });
                    }
                },

                addToCart(productId) {
                    this.addToCartDirect(productId);
                },

                addToCartDirect(productId) {
                    fetch(config.cartAddUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success || data.cart_count !== undefined) {
                            this.fetchCartCount();
                            this.showToast('Added to K71 Cart! 🛒', '🛒');
                        } else {
                            this.showToast(data.message || 'Product added to cart', '🛒');
                        }
                    })
                    .catch(() => {
                        this.fetchCartCount();
                        this.showToast('Added to Cart! 🛒', '🛒');
                    });
                },

                buyNow(productId) {
                    fetch(config.cartAddUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: 1
                        })
                    })
                    .then(() => {
                        window.location.href = '{{ route('checkout.index') }}';
                    })
                    .catch(() => {
                        window.location.href = '{{ route('checkout.index') }}';
                    });
                },

                fetchCartCount() {
                    fetch(config.cartCountUrl, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            this.cartCount = data.count;
                        }
                    })
                    .catch(() => {});
                },

                recordEvent(action, payload) {
                    const body = new FormData();
                    body.append('action', action);
                    body.append('_token', config.csrfToken);
                    for (const k in payload) {
                        body.append(k, payload[k]);
                    }
                    fetch(config.ajaxUrl, {
                        method: 'POST',
                        body: body
                    }).catch(() => {});
                },

                showToast(msg, icon) {
                    this.toast = { show: true, message: msg, icon: icon || '🛍️' };
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                }
            };
        }
    </script>
    <!-- Mobile Bottom Tab Navigation (Section F) -->
    <x-k71-bottom-nav active="shorts" />

    <!-- Shared K71 Authentication Popup Modal -->
    <x-k71-auth-modal />
</body>
</html>
