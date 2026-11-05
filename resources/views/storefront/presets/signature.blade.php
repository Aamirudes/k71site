{{-- PRESET 05: SIGNATURE (Dark Holographic Streetwear · Electric Lime & Neon Magenta Liquid Bubbles) --}}
<div class="storefront-preset-signature min-h-screen flex flex-col font-body relative overflow-x-hidden selection:bg-lime-400 selection:text-black bg-zinc-950 text-zinc-100"
     style="--sf-accent: #22c55e;">

    <!-- Liquid Background Ambient Glow Spheres (Electric Lime & Vivid Magenta) -->
    <div class="fixed top-0 right-1/4 w-[550px] h-[550px] rounded-full pointer-events-none opacity-25 filter blur-[130px] animate-pulse"
         style="background: radial-gradient(circle, #22c55e 0%, transparent 70%); animation-duration: 7s;"></div>
    <div class="fixed bottom-10 left-10 w-[600px] h-[600px] rounded-full pointer-events-none opacity-20 filter blur-[140px]"
         style="background: radial-gradient(circle, #f43f5e 0%, transparent 70%);"></div>
    <div class="fixed top-1/2 left-1/3 w-[450px] h-[450px] rounded-full pointer-events-none opacity-15 filter blur-[120px]"
         style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);"></div>

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Floating Liquid Frosted Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 relative z-10 space-y-10 sm:space-y-14">
        
        {{-- Oversized High-Impact Flagship Drop Hero --}}
        @php
            $drop = $featuredProducts->first() ?? $products->first();
            $dropImg = $drop?->images->first()?->path ? asset('storage/' . $drop->images->first()->path) : ($vendor->banner_url ?: 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?auto=format&fit=crop&w=1600&q=80');
            $dPrice = $drop ? $drop->base_price : null;
            $dOldPrice = $drop && $drop->compare_at_price > $drop->base_price ? $drop->compare_at_price : null;
            $dDiscount = ($dPrice && $dOldPrice) ? round((($dOldPrice - $dPrice) / $dOldPrice) * 100) : null;
        @endphp

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="rounded-3xl p-6 sm:p-10 lg:p-12 relative overflow-hidden backdrop-blur-2xl bg-zinc-900/70 border border-zinc-800 shadow-2xl shadow-lime-950/20">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Drop Headline & Actions (7 cols) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-lime-400 text-black shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-black animate-ping"></span>
                            <span>EXCLUSIVE DROP // 2026</span>
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight font-heading text-white uppercase leading-[0.98]">
                            {{ $drop ? $drop->title : $vendor->shop_name }}
                        </h1>

                        <p class="text-xs sm:text-sm text-zinc-400 leading-relaxed font-sans max-w-xl">
                            {{ $drop ? Str::limit($drop->description, 180) : ($vendor->description ?: 'Exclusive drop release. Available in strictly limited production quantities. Verified authentic.') }}
                        </p>

                        <!-- Streetwear Specs / Badges -->
                        <div class="flex flex-wrap items-center gap-3 pt-1 text-xs font-mono font-bold">
                            <span class="px-3 py-1 rounded-lg bg-zinc-800/80 border border-zinc-700 text-lime-400">
                                ⚡ LIMITED RUN
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-zinc-800/80 border border-zinc-700 text-zinc-300">
                                ✓ DIRECT STUDIO
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-zinc-800/80 border border-zinc-700 text-rose-400">
                                🔥 HIGH DEMAND
                            </span>
                        </div>

                        <!-- Price & Instant Quick-Add Trigger -->
                        <div class="pt-3 flex flex-wrap items-center gap-5">
                            @if($dPrice)
                                <div class="flex items-baseline gap-3">
                                    <span class="text-3xl sm:text-5xl font-black text-lime-400 font-mono tracking-tight">
                                        ₹{{ number_format($dPrice / 100, 2) }}
                                    </span>
                                    @if($dOldPrice)
                                        <span class="text-base sm:text-lg text-zinc-500 line-through font-mono">
                                            ₹{{ number_format($dOldPrice / 100, 2) }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-md text-xs font-black bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">
                                            -{{ $dDiscount }}%
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                @if($drop)
                                    <button type="button"
                                            onclick="window.quickAddToCart({{ $drop->id }})"
                                            class="flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-lime-400 hover:bg-lime-300 text-black font-black text-sm tracking-wide uppercase transition-all duration-200 shadow-lg shadow-lime-400/25 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-98">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span>SECURE DROP / ADD</span>
                                    </button>
                                    <a href="{{ route('product.show', $drop->slug) }}"
                                       class="px-5 py-4 rounded-2xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-bold text-sm transition">
                                       VIEW SPEC →
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Right Drop Media (5 cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative aspect-square rounded-2xl overflow-hidden bg-black border-2 border-zinc-800 group shadow-2xl">
                            <img src="{{ $dropImg }}" 
                                 alt="{{ $drop ? $drop->title : $vendor->shop_name }}"
                                 class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-108">

                            <!-- Corner Glitch Accents -->
                            <div class="absolute top-3 left-3 px-2.5 py-1 rounded bg-black/80 backdrop-blur-md text-[10px] font-mono font-bold text-lime-400 border border-lime-400/40">
                                ARCHIVE // 2026
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded bg-black/80 backdrop-blur-md text-[10px] font-mono font-bold text-white border border-white/20">
                                VERIFIED
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Marquee Ticker Ribbon --}}
        <div class="py-3 bg-lime-400 text-black font-black uppercase text-xs sm:text-sm tracking-widest overflow-hidden whitespace-nowrap shadow-md">
            <div class="inline-block animate-marquee flex items-center gap-8">
                <span>✦ NEW RELEASES LIVE</span>
                <span>•</span>
                <span>PAN-INDIA FAST TRANSIT</span>
                <span>•</span>
                <span>100% VERIFIED AUTHENTIC</span>
                <span>•</span>
                <span>OFFICIAL {{ strtoupper($vendor->shop_name) }} STOREFRONT</span>
                <span>•</span>
                <span>✦ NEW RELEASES LIVE</span>
                <span>•</span>
                <span>PAN-INDIA FAST TRANSIT</span>
                <span>•</span>
                <span>100% VERIFIED AUTHENTIC</span>
                <span>•</span>
                <span>OFFICIAL {{ strtoupper($vendor->shop_name) }} STOREFRONT</span>
            </div>
        </div>

        {{-- Liquid Bubble Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Streetwear Catalog Grid --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-zinc-800 mb-8">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white font-heading uppercase">
                        DROP CATALOG // {{ $products->total() }} PIECES
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-400 mt-1">
                        Explore all available releases from {{ $vendor->shop_name }}.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono text-zinc-500">SORT BY:</span>
                    <span class="text-xs font-mono font-bold text-lime-400 px-3 py-1 rounded-lg bg-zinc-900 border border-zinc-800">
                        LATEST RELEASES
                    </span>
                </div>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @forelse($products as $product)
                    @php
                        $sImg = $product->images->first()?->path ? asset('storage/' . $product->images->first()->path) : 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?auto=format&fit=crop&w=600&q=80';
                        $hasDiscount = $product->compare_at_price > $product->base_price;
                        $disc = $hasDiscount ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                    @endphp
                    <div class="group rounded-2xl overflow-hidden backdrop-blur-xl bg-zinc-900/60 border border-zinc-800 hover:border-lime-400/60 shadow-lg transition-all duration-300 flex flex-col justify-between">
                        
                        <!-- Image Container -->
                        <div class="relative aspect-square overflow-hidden bg-black">
                            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $sImg }}" 
                                     alt="{{ $product->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-108">
                            </a>

                            @if($hasDiscount)
                                <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md bg-lime-400 text-black text-[10px] font-black uppercase tracking-wider shadow-sm">
                                    -{{ $disc }}%
                                </div>
                            @endif

                            <!-- Quick Add Floating Action Button -->
                            <button type="button"
                                    onclick="window.quickAddToCart({{ $product->id }})"
                                    class="absolute bottom-2.5 right-2.5 p-2.5 rounded-xl bg-zinc-900/90 hover:bg-lime-400 text-white hover:text-black backdrop-blur-md border border-zinc-700/80 transition-all duration-200 group-hover:scale-105 active:scale-95"
                                    title="Add to Bag">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Product Content -->
                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div>
                                @if($product->category)
                                    <span class="text-[10px] font-mono uppercase font-bold tracking-wider text-zinc-500 block mb-1">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <h3 class="text-xs sm:text-sm font-bold text-zinc-100 line-clamp-2 group-hover:text-lime-400 transition-colors">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        {{ $product->title }}
                                    </a>
                                </h3>
                            </div>

                            <div class="pt-2 flex items-baseline justify-between border-t border-zinc-800/80 font-mono">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm sm:text-base font-black text-white">
                                        ₹{{ number_format($product->base_price / 100, 2) }}
                                    </span>
                                    @if($hasDiscount)
                                        <span class="text-xs text-zinc-500 line-through">
                                            ₹{{ number_format($product->compare_at_price / 100, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <button type="button"
                                        onclick="window.quickAddToCart({{ $product->id }})"
                                        class="text-xs font-black text-lime-400 hover:text-lime-300 uppercase tracking-wider">
                                    + ADD
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-zinc-500 font-mono">
                        // NO DROPS FOUND IN THIS CATEGORY
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="pt-10 flex justify-center">
                    {{ $products->links() }}
                </div>
            @endif
        </section>

        {{-- Live Studio Shorts & Video Commerce --}}
        @if(isset($vendorShorts) && $vendorShorts->isNotEmpty())
            @include('storefront.sections.video_showcase')
        @endif

        {{-- Streetwear Guarantee Capsule --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 sm:p-10 rounded-3xl backdrop-blur-2xl bg-zinc-900/60 border border-zinc-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-lime-400/10 border border-lime-400/20 text-lime-400 flex items-center justify-center font-black text-base shrink-0">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Direct Drop</h4>
                            <p class="text-xs text-zinc-400 mt-1">Dispatched directly from the verified brand studio.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-lime-400/10 border border-lime-400/20 text-lime-400 flex items-center justify-center font-black text-base shrink-0">
                            ✓
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">100% Authentic</h4>
                            <p class="text-xs text-zinc-400 mt-1">Every release verified against counterfeit standards.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-lime-400/10 border border-lime-400/20 text-lime-400 flex items-center justify-center font-black text-base shrink-0">
                            📦
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Express Transit</h4>
                            <p class="text-xs text-zinc-400 mt-1">Speedy tracked delivery across all Indian cities.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-lime-400/10 border border-lime-400/20 text-lime-400 flex items-center justify-center font-black text-base shrink-0">
                            💬
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">VIP Concierge</h4>
                            <p class="text-xs text-zinc-400 mt-1">Direct WhatsApp access to the brand founder.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Direct Stylist / Inquiry Desk --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Boutique Glassmorphic Footer --}}
    @include('storefront.sections.footer')

</div>
