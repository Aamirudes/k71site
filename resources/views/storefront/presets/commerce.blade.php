{{-- PRESET 03: COMMERCE (Apple-grade Luminous Frosted Discovery · Cobalt Liquid Glow · High Conversion) --}}
<div class="storefront-preset-commerce min-h-screen flex flex-col font-body relative overflow-x-hidden selection:bg-blue-600 selection:text-white bg-slate-50 text-slate-900"
     style="--sf-accent: #2563eb;">

    <!-- Liquid Background Ambient Glow Spheres (Cobalt & Royal Indigo) -->
    <div class="fixed top-0 left-1/3 w-[550px] h-[550px] rounded-full pointer-events-none opacity-20 filter blur-[120px]"
         style="background: radial-gradient(circle, #2563eb 0%, transparent 70%);"></div>
    <div class="fixed bottom-10 right-10 w-[600px] h-[600px] rounded-full pointer-events-none opacity-15 filter blur-[130px]"
         style="background: radial-gradient(circle, #4f46e5 0%, transparent 70%);"></div>
    <div class="fixed top-1/2 -left-20 w-[450px] h-[450px] rounded-full pointer-events-none opacity-10 filter blur-[110px]"
         style="background: radial-gradient(circle, #06b6d4 0%, transparent 70%);"></div>

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Floating Liquid Frosted Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 relative z-10 space-y-10 sm:space-y-14">
        
        {{-- High-Conversion Flagship Showcase Hero --}}
        @php
            $flagship = $featuredProducts->first() ?? $products->first();
            $flagshipImg = $flagship?->images->first()?->path ? asset('storage/' . $flagship->images->first()->path) : ($vendor->banner_url ?: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1600&q=80');
            $fPrice = $flagship ? $flagship->base_price : null;
            $fOldPrice = $flagship && $flagship->compare_at_price > $flagship->base_price ? $flagship->compare_at_price : null;
            $fDiscount = ($fPrice && $fOldPrice) ? round((($fOldPrice - $fPrice) / $fOldPrice) * 100) : null;
        @endphp

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="rounded-3xl p-6 sm:p-10 lg:p-12 relative overflow-hidden backdrop-blur-2xl bg-white/75 border border-white/60 shadow-xl shadow-blue-900/5">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Flagship Showcase Details (7 cols) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-500/10 text-blue-700 border border-blue-500/20">
                                ⚡ Flagship Featured
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                ✓ Verified Stock
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight font-heading text-slate-900 leading-[1.12]">
                            {{ $flagship ? $flagship->title : $vendor->shop_name }}
                        </h1>

                        <p class="text-sm text-slate-600 leading-relaxed max-w-xl">
                            {{ $flagship ? Str::limit($flagship->description, 180) : ($vendor->description ?: 'Shop directly with guaranteed authenticity, pan-India express shipping, and instant buyer assistance.') }}
                        </p>

                        <!-- Highlights Banner -->
                        <div class="flex flex-wrap items-center gap-6 text-xs text-slate-600 font-medium pt-1">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Pan-India Fast Dispatch</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>100% Genuine Guaranteed</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Easy Exchange Available</span>
                            </span>
                        </div>

                        <!-- Price and Instant Add Action -->
                        <div class="pt-4 flex flex-wrap items-center gap-5">
                            @if($fPrice)
                                <div class="flex items-baseline gap-2.5">
                                    <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                                        ₹{{ number_format($fPrice / 100, 2) }}
                                    </span>
                                    @if($fOldPrice)
                                        <span class="text-base text-slate-400 line-through">
                                            ₹{{ number_format($fOldPrice / 100, 2) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            Save {{ $fDiscount }}%
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                @if($flagship)
                                    <button type="button"
                                            onclick="window.quickAddToCart({{ $flagship->id }})"
                                            class="flex-1 sm:flex-initial px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-600/25 transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-98">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span>Add to Bag</span>
                                    </button>
                                    <a href="{{ route('product.show', $flagship->slug) }}"
                                       class="px-5 py-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition">
                                        Details →
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Right Flagship Image Pedestal (5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="relative aspect-square rounded-2xl overflow-hidden bg-white shadow-inner border border-slate-100 group">
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $flagship ? $flagship->title : $vendor->shop_name }}"
                                 class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-105">
                            
                            @if($flagship)
                                <div class="absolute bottom-3 left-3 px-3 py-1.5 rounded-xl bg-white/90 backdrop-blur-md text-[11px] font-bold text-slate-800 shadow-sm border border-white/60">
                                    ✓ Verified K71 Item
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Liquid Bubble Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- High-Density Product Discovery Catalog --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-slate-200 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 font-heading">
                        Explore Catalog
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Showing all {{ $products->total() }} available items directly from {{ $vendor->shop_name }}.
                    </p>
                </div>

                <!-- Category Quick Filter Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ !request('category') ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        All
                    </a>
                    @foreach($vendorCategories->take(6) as $cat)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}" 
                           class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ request('category') === $cat->slug ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @forelse($products as $product)
                    @php
                        $pImg = $product->images->first()?->path ? asset('storage/' . $product->images->first()->path) : 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80';
                        $hasDiscount = $product->compare_at_price > $product->base_price;
                        $disc = $hasDiscount ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                    @endphp
                    <div class="group rounded-2xl overflow-hidden backdrop-blur-xl bg-white/80 border border-white/60 hover:border-blue-400 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                        
                        <!-- Product Image -->
                        <div class="relative aspect-square overflow-hidden bg-slate-100">
                            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $pImg }}" 
                                     alt="{{ $product->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-106">
                            </a>

                            @if($hasDiscount)
                                <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-extrabold uppercase tracking-wider shadow-sm">
                                    Save {{ $disc }}%
                                </div>
                            @endif

                            <!-- Quick Add Floating Action Button -->
                            <button type="button"
                                    onclick="window.quickAddToCart({{ $product->id }})"
                                    class="absolute bottom-2.5 right-2.5 p-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-md transition-all duration-200 group-hover:scale-105 active:scale-95"
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
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        {{ $product->title }}
                                    </a>
                                </h3>
                            </div>

                            <div class="pt-2 flex items-baseline justify-between border-t border-slate-100">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm sm:text-base font-extrabold text-slate-900">
                                        ₹{{ number_format($product->base_price / 100, 2) }}
                                    </span>
                                    @if($hasDiscount)
                                        <span class="text-xs text-slate-400 line-through">
                                            ₹{{ number_format($product->compare_at_price / 100, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <button type="button"
                                        onclick="window.quickAddToCart({{ $product->id }})"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                    + Add
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-slate-400">
                        No products available in this category.
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

        {{-- Buyer Trust Guarantee Capsule --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 sm:p-10 rounded-3xl backdrop-blur-2xl bg-white/80 border border-white/60 shadow-lg shadow-blue-900/5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg shrink-0">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Express Delivery</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Fast pan-India dispatch straight from store inventory.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg shrink-0">
                            🛡️
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">100% Genuine</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Verified seller backed by K71 marketplace protection.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg shrink-0">
                            ↺
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Smooth Exchange</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Direct merchant support for size & item replacements.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center font-bold text-lg shrink-0">
                            💬
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">WhatsApp Help</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Chat directly with the store owner anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Customer Help & WhatsApp Contact Desk --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Boutique Glassmorphic Footer --}}
    @include('storefront.sections.footer')

</div>
