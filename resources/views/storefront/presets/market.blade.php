{{-- PRESET 04: MARKET (Warm Organic Artisan Glass · Terracotta & Forest Sage Liquid Bubbles) --}}
<div class="storefront-preset-market min-h-screen flex flex-col font-body relative overflow-x-hidden selection:bg-amber-200 selection:text-amber-900 bg-[#fdfbf7] text-[#2d251e]"
     style="--sf-accent: #c85a32;">

    <!-- Liquid Background Ambient Glow Spheres (Terracotta & Forest Sage) -->
    <div class="fixed top-0 left-1/4 w-[550px] h-[550px] rounded-full pointer-events-none opacity-20 filter blur-[120px]"
         style="background: radial-gradient(circle, #c85a32 0%, transparent 70%);"></div>
    <div class="fixed bottom-10 right-10 w-[600px] h-[600px] rounded-full pointer-events-none opacity-15 filter blur-[130px]"
         style="background: radial-gradient(circle, #2d4a3e 0%, transparent 70%);"></div>
    <div class="fixed top-1/2 -left-20 w-[450px] h-[450px] rounded-full pointer-events-none opacity-15 filter blur-[110px]"
         style="background: radial-gradient(circle, #d97706 0%, transparent 70%);"></div>

    {{-- Top Warm Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Floating Liquid Frosted Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 relative z-10 space-y-10 sm:space-y-14">
        
        {{-- Artisan Flagship Product Showcase Hero --}}
        @php
            $artisanProduct = $featuredProducts->first() ?? $products->first();
            $artisanImg = $artisanProduct?->images->first()?->path ? asset('storage/' . $artisanProduct->images->first()->path) : ($vendor->banner_url ?: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1600&q=80');
            $artPrice = $artisanProduct ? $artisanProduct->base_price : null;
            $artOldPrice = $artisanProduct && $artisanProduct->compare_at_price > $artisanProduct->base_price ? $artisanProduct->compare_at_price : null;
            $artDiscount = ($artPrice && $artOldPrice) ? round((($artOldPrice - $artPrice) / $artOldPrice) * 100) : null;
        @endphp

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="rounded-3xl p-6 sm:p-10 lg:p-12 relative overflow-hidden backdrop-blur-2xl bg-white/75 border border-amber-900/10 shadow-xl shadow-amber-950/5">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Artisan Story & Product Info (7 cols) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#c85a32]/10 text-[#c85a32] border border-[#c85a32]/20">
                                🏺 Studio Handcrafted
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                ✓ Authenticated Batch
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight font-heading text-[#2d251e] leading-[1.12]">
                            {{ $artisanProduct ? $artisanProduct->title : $vendor->shop_name }}
                        </h1>

                        <p class="text-sm text-[#665a4e] leading-relaxed max-w-xl">
                            {{ $artisanProduct ? Str::limit($artisanProduct->description, 180) : ($vendor->description ?: 'Artisan craftsmanship made in small batches. Sourced with natural materials and shipped directly from our independent workshop.') }}
                        </p>

                        <!-- Craft Quality Badges -->
                        <div class="flex flex-wrap items-center gap-4 text-xs text-[#52463b] font-medium pt-1">
                            <span class="flex items-center gap-1.5">
                                <span>✦</span>
                                <span>Authentic Workshop Origins</span>
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1.5">
                                <span>🌿</span>
                                <span>Artisanal Quality</span>
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1.5">
                                <span>📦</span>
                                <span>Safe Secure Packing</span>
                            </span>
                        </div>

                        <!-- Price and Quick Add to Bag -->
                        <div class="pt-4 flex flex-wrap items-center gap-5">
                            @if($artPrice)
                                <div class="flex items-baseline gap-2.5">
                                    <span class="text-3xl sm:text-4xl font-extrabold text-[#2d251e] tracking-tight">
                                        ₹{{ number_format($artPrice / 100, 2) }}
                                    </span>
                                    @if($artOldPrice)
                                        <span class="text-base text-[#8c7e70] line-through">
                                            ₹{{ number_format($artOldPrice / 100, 2) }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#c85a32]/10 text-[#c85a32]">
                                            Save {{ $artDiscount }}%
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                @if($artisanProduct)
                                    <button type="button"
                                            onclick="window.quickAddToCart({{ $artisanProduct->id }})"
                                            class="flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-[#c85a32] hover:bg-[#b54f2a] text-white font-bold text-sm shadow-lg shadow-[#c85a32]/25 transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-98">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span>Add to Bag</span>
                                    </button>
                                    <a href="{{ route('product.show', $artisanProduct->slug) }}"
                                       class="px-5 py-4 rounded-2xl bg-amber-900/5 hover:bg-amber-900/10 text-[#4a3e33] font-semibold text-sm transition">
                                        Story &amp; Specs →
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Right Artisan Imagery (5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="relative aspect-[4/5] sm:aspect-square rounded-2xl overflow-hidden bg-white shadow-inner border border-amber-900/10 group">
                            <img src="{{ $artisanImg }}" 
                                 alt="{{ $artisanProduct ? $artisanProduct->title : $vendor->shop_name }}"
                                 class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-105">

                            @if($artisanProduct)
                                <div class="absolute bottom-3 left-3 px-3 py-1.5 rounded-xl bg-white/95 backdrop-blur-md text-[11px] font-bold text-[#3d3228] shadow-sm border border-amber-900/10">
                                    Crafted with Care
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Liquid Bubble Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product Discovery Catalog --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-amber-900/10 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#2d251e] font-heading">
                        Artisan Workshop Collection
                    </h2>
                    <p class="text-xs sm:text-sm text-[#736557] mt-1">
                        Explore all {{ $products->total() }} handcrafted items and creations.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-[#736557]">Direct from:</span>
                    <span class="text-xs font-bold text-[#c85a32] px-2.5 py-1 rounded-full bg-[#c85a32]/10 border border-[#c85a32]/20">
                        {{ $vendor->city ?: 'Independent Studio' }}
                    </span>
                </div>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @forelse($products as $product)
                    @php
                        $mImg = $product->images->first()?->path ? asset('storage/' . $product->images->first()->path) : 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80';
                        $hasDiscount = $product->compare_at_price > $product->base_price;
                        $disc = $hasDiscount ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                    @endphp
                    <div class="group rounded-2xl overflow-hidden backdrop-blur-xl bg-white/80 border border-amber-900/10 hover:border-[#c85a32]/40 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                        
                        <!-- Image Container -->
                        <div class="relative aspect-square overflow-hidden bg-amber-50">
                            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $mImg }}" 
                                     alt="{{ $product->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-106">
                            </a>

                            @if($hasDiscount)
                                <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full bg-[#c85a32] text-white text-[10px] font-extrabold uppercase tracking-wider shadow-sm">
                                    Save {{ $disc }}%
                                </div>
                            @endif

                            <!-- Quick Add Floating Action Button -->
                            <button type="button"
                                    onclick="window.quickAddToCart({{ $product->id }})"
                                    class="absolute bottom-2.5 right-2.5 p-2.5 rounded-xl bg-[#c85a32] hover:bg-[#b54f2a] text-white shadow-md transition-all duration-200 group-hover:scale-105 active:scale-95"
                                    title="Add to Bag">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Product Content -->
                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div>
                                @if($product->category)
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#918171] block mb-1">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <h3 class="text-xs sm:text-sm font-bold text-[#2d251e] line-clamp-2 group-hover:text-[#c85a32] transition-colors">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        {{ $product->title }}
                                    </a>
                                </h3>
                            </div>

                            <div class="pt-2 flex items-baseline justify-between border-t border-amber-900/5">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm sm:text-base font-extrabold text-[#2d251e]">
                                        ₹{{ number_format($product->base_price / 100, 2) }}
                                    </span>
                                    @if($hasDiscount)
                                        <span class="text-xs text-[#918171] line-through">
                                            ₹{{ number_format($product->compare_at_price / 100, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <button type="button"
                                        onclick="window.quickAddToCart({{ $product->id }})"
                                        class="text-xs font-bold text-[#c85a32] hover:text-[#b54f2a]">
                                    + Add
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-[#918171]">
                        No artisan pieces currently listed.
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

        {{-- Artisan Guarantee Capsule --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 sm:p-10 rounded-3xl backdrop-blur-2xl bg-white/80 border border-amber-900/10 shadow-lg shadow-amber-950/5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-[#c85a32] flex items-center justify-center font-bold text-lg shrink-0">
                            🏺
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-[#2d251e]">Workshop Direct</h4>
                            <p class="text-xs text-[#736557] mt-0.5">Direct from the maker without middlemen markups.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg shrink-0">
                            🌿
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-[#2d251e]">Authentic Craft</h4>
                            <p class="text-xs text-[#736557] mt-0.5">Every piece individually inspected before dispatch.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 text-[#2d251e] flex items-center justify-center font-bold text-lg shrink-0">
                            📦
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-[#2d251e]">Safe Packing</h4>
                            <p class="text-xs text-[#736557] mt-0.5">Multi-layer bubble protection for insured transit.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center font-bold text-lg shrink-0">
                            💬
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-[#2d251e]">Maker WhatsApp</h4>
                            <p class="text-xs text-[#736557] mt-0.5">Talk to the artisan directly for custom inquiries.</p>
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
