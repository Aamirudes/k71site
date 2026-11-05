{{-- PRESET 02: OBJECT (Minimal Cyber-Glass · Obsidian / Slate · Neon Cyan Liquid Glow) --}}
<div class="storefront-preset-object min-h-screen flex flex-col font-body relative overflow-x-hidden selection:bg-cyan-500 selection:text-slate-950 bg-slate-950 text-slate-100"
     style="--sf-accent: #06b6d4;">

    <!-- Liquid Background Ambient Glow Spheres (Cyan & Electric Indigo) -->
    <div class="fixed top-10 left-1/4 w-[500px] h-[500px] rounded-full pointer-events-none opacity-25 filter blur-[120px] animate-pulse"
         style="background: radial-gradient(circle, #06b6d4 0%, transparent 70%); animation-duration: 8s;"></div>
    <div class="fixed bottom-20 right-10 w-[600px] h-[600px] rounded-full pointer-events-none opacity-20 filter blur-[140px]"
         style="background: radial-gradient(circle, #3b82f6 0%, transparent 70%);"></div>
    <div class="fixed top-1/2 -left-20 w-[400px] h-[400px] rounded-full pointer-events-none opacity-15 filter blur-[100px]"
         style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);"></div>

    {{-- Top Sleek Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Floating Liquid Frosted Cyber Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 relative z-10 space-y-12 sm:space-y-16">
        
        {{-- High-Tech Cyber Flagship Hero Stage --}}
        @php
            $heroProduct = $featuredProducts->first() ?? $products->first();
            $heroImage = $heroProduct?->images->first()?->path ? asset('storage/' . $heroProduct->images->first()->path) : ($vendor->banner_url ?: 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=1600&q=80');
            $heroPrice = $heroProduct ? $heroProduct->base_price : null;
            $heroOldPrice = $heroProduct && $heroProduct->compare_at_price > $heroProduct->base_price ? $heroProduct->compare_at_price : null;
            $discountPct = ($heroPrice && $heroOldPrice) ? round((($heroOldPrice - $heroPrice) / $heroOldPrice) * 100) : null;
            $heroPriceRupees = $heroPrice ? number_format($heroPrice / 100, 2) : null;
            $heroOldPriceRupees = $heroOldPrice ? number_format($heroOldPrice / 100, 2) : null;
        @endphp

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="rounded-3xl p-6 sm:p-10 lg:p-12 relative overflow-hidden backdrop-blur-2xl bg-slate-900/60 border border-slate-800/80 shadow-2xl shadow-cyan-950/40">
                <!-- Inner Neon Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
                     style="background-image: linear-gradient(to right, #06b6d4 1px, transparent 1px), linear-gradient(to bottom, #06b6d4 1px, transparent 1px); background-size: 32px 32px;"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center relative z-10">
                    
                    <!-- Left Spec & Purchase Column (6 cols) -->
                    <div class="lg:col-span-6 space-y-5">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-mono font-bold tracking-wider uppercase bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                            <span>PRECISION SPEC // 01</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight font-heading text-white leading-[1.08]">
                            {{ $heroProduct ? $heroProduct->title : $vendor->shop_name }}
                        </h1>

                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed font-sans max-w-xl">
                            {{ $heroProduct ? Str::limit($heroProduct->description, 160) : ($vendor->description ?: 'Engineered with meticulous material precision. Explore the limited production run.') }}
                        </p>

                        <!-- Tech Spec Chips -->
                        <div class="grid grid-cols-3 gap-2.5 pt-1">
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                                <span class="block text-[10px] text-slate-500 font-mono uppercase tracking-wider">Availability</span>
                                <span class="text-xs font-bold text-cyan-400 font-mono">IN STOCK</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                                <span class="block text-[10px] text-slate-500 font-mono uppercase tracking-wider">Dispatch</span>
                                <span class="text-xs font-bold text-slate-200 font-mono">24-48 HRS</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                                <span class="block text-[10px] text-slate-500 font-mono uppercase tracking-wider">Quality</span>
                                <span class="text-xs font-bold text-emerald-400 font-mono">VERIFIED</span>
                            </div>
                        </div>

                        <!-- Price & Direct Cart Trigger -->
                        <div class="flex flex-wrap items-center gap-4 pt-3">
                            @if($heroPrice)
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl sm:text-4xl font-extrabold font-mono text-cyan-400">
                                        ₹{{ $heroPriceRupees }}
                                    </span>
                                    @if($heroOldPrice)
                                        <span class="text-sm line-through text-slate-500 font-mono">
                                            ₹{{ $heroOldPriceRupees }}
                                        </span>
                                        <span class="text-xs font-bold font-mono px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                            -{{ $discountPct }}%
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                @if($heroProduct)
                                    <button type="button"
                                            onclick="window.quickAddToCart({{ $heroProduct->id }})"
                                            class="flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-cyan-400 hover:bg-cyan-300 text-slate-950 font-bold text-sm tracking-wide transition-all duration-200 shadow-lg shadow-cyan-400/20 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-98">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span>QUICK ADD TO BAG</span>
                                    </button>
                                    <a href="{{ route('product.show', $heroProduct->slug) }}"
                                       class="p-4 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700/80 transition flex items-center justify-center"
                                       title="View Full Technical Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Right Showcase Image (6 cols) -->
                    <div class="lg:col-span-6 relative">
                        <div class="relative aspect-square sm:aspect-[4/3] lg:aspect-square rounded-2xl overflow-hidden bg-slate-950 border border-slate-800/80 group">
                            <!-- Neon Corner Accents -->
                            <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-cyan-400/80 z-20 pointer-events-none"></div>
                            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-cyan-400/80 z-20 pointer-events-none"></div>

                            <img src="{{ $heroImage }}" 
                                 alt="{{ $heroProduct ? $heroProduct->title : $vendor->shop_name }}"
                                 class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-105">

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent pointer-events-none"></div>

                            @if($heroProduct)
                                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-xs font-mono text-slate-300">
                                    <span class="px-2.5 py-1 rounded-md bg-slate-900/80 backdrop-blur-md border border-slate-700">
                                        ID: #{{ str_pad($heroProduct->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-md bg-slate-900/80 backdrop-blur-md border border-slate-700 text-cyan-400">
                                        SERIES 2026
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Liquid Bubble Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product Catalog Grid (Product-First with Quick Cyber Add) --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-slate-800/80 mb-8">
                <div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-mono text-cyan-400 uppercase tracking-widest mb-1">
                        <span>// RELEASES &amp; HARDWARE</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-heading">
                        All Production Drops
                    </h2>
                </div>

                <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
                    <span>INDEXED:</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-cyan-400 font-bold">
                        {{ $products->total() }} UNITS
                    </span>
                </div>
            </div>

            <!-- 4-Column Cyber Glass Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @forelse($products as $product)
                    @php
                        $img = $product->images->first()?->path ? asset('storage/' . $product->images->first()->path) : 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80';
                        $hasDiscount = $product->compare_at_price > $product->base_price;
                        $disc = $hasDiscount ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                    @endphp
                    <div class="group rounded-2xl overflow-hidden backdrop-blur-xl bg-slate-900/50 border border-slate-800/80 hover:border-cyan-500/50 transition-all duration-300 flex flex-col justify-between hover:shadow-xl hover:shadow-cyan-950/30">
                        
                        <!-- Image Stage -->
                        <div class="relative aspect-square overflow-hidden bg-slate-950">
                            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $img }}" 
                                     alt="{{ $product->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-108">
                            </a>

                            @if($hasDiscount)
                                <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md bg-cyan-400 text-slate-950 text-[10px] font-mono font-black uppercase tracking-wider shadow-sm">
                                    -{{ $disc }}%
                                </div>
                            @endif

                            <!-- Quick Add Floating Action -->
                            <button type="button"
                                    onclick="window.quickAddToCart({{ $product->id }})"
                                    class="absolute bottom-2.5 right-2.5 p-2.5 rounded-xl bg-slate-900/90 hover:bg-cyan-400 text-slate-200 hover:text-slate-950 backdrop-blur-md border border-slate-700/80 transition-all duration-200 shadow-md group-hover:scale-105 active:scale-95"
                                    title="Add to Bag">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Product Data -->
                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div>
                                @if($product->category)
                                    <span class="text-[10px] font-mono uppercase tracking-wider text-slate-500 block mb-1">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <h3 class="text-xs sm:text-sm font-bold text-slate-200 font-sans line-clamp-2 group-hover:text-cyan-400 transition-colors">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        {{ $product->title }}
                                    </a>
                                </h3>
                            </div>

                            <div class="pt-2 flex items-baseline justify-between border-t border-slate-800/60 font-mono">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm sm:text-base font-extrabold text-white">
                                        ₹{{ number_format($product->base_price / 100, 2) }}
                                    </span>
                                    @if($hasDiscount)
                                        <span class="text-[10px] text-slate-500 line-through">
                                            ₹{{ number_format($product->compare_at_price / 100, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <button type="button"
                                        onclick="window.quickAddToCart({{ $product->id }})"
                                        class="text-[11px] text-cyan-400 hover:text-cyan-300 font-bold uppercase tracking-wider">
                                    + ADD
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-slate-500 font-mono">
                        // NO ITEMS FOUND MATCHING CURRENT SPEC
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

        {{-- Cyber Trust Guarantee Capsule --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 sm:p-10 rounded-3xl backdrop-blur-2xl bg-slate-900/50 border border-slate-800/80">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center font-mono font-bold shrink-0">
                            01
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Direct Workshop</h4>
                            <p class="text-xs text-slate-400 mt-1">Directly dispatched from the vendor's verified studio.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center font-mono font-bold shrink-0">
                            02
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Secure Transit</h4>
                            <p class="text-xs text-slate-400 mt-1">Trackable real-time courier transit across all pin codes.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center font-mono font-bold shrink-0">
                            03
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Quality Seal</h4>
                            <p class="text-xs text-slate-400 mt-1">Inspected and verified before hermetic packaging.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center font-mono font-bold shrink-0">
                            04
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Direct WhatsApp</h4>
                            <p class="text-xs text-slate-400 mt-1">Real-time merchant support for custom specifications.</p>
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
