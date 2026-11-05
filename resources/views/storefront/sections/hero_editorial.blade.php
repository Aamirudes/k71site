@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $title = $section['title'] ?? ($flagship ? $flagship->title : $vendor->shop_name);
    $subtitle = $section['subtitle'] ?? $config['brand']['tagline'] ?? 'Curated authentic creations directly from our studio';
    $badge = $section['badge'] ?? '⚡ Boutique Signature Drop';

    if ($flagship) {
        $priceRupees = number_format($flagship->base_price / 100, 2);
        $hasComparePrice = !empty($flagship->compare_at_price) && $flagship->compare_at_price > $flagship->base_price;
        $comparePriceRupees = $hasComparePrice ? number_format($flagship->compare_at_price / 100, 2) : null;
        $discountPct = $hasComparePrice ? round((($flagship->compare_at_price - $flagship->base_price) / $flagship->compare_at_price) * 100) : 0;
        $flagshipImg = $flagship->images->first()?->path ? \App\Services\ImageService::url($flagship->images->first()->path, 800) : asset('images/product-placeholder.svg');
        $defaultVariantId = $flagship->variants->first()?->id;
    }
@endphp

<!-- PRODUCT-FIRST LIQUID GLASS SHOWCASE HERO -->
<section id="hero" 
         class="relative overflow-hidden py-8 sm:py-14 lg:py-18">
    
    <!-- Ambient Floating Liquid Glow Spheres -->
    <div class="absolute top-10 left-1/4 w-96 h-96 rounded-full pointer-events-none opacity-25 filter blur-3xl"
         style="background: radial-gradient(circle, var(--sf-accent, #c59b27) 0%, transparent 70%);"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 rounded-full pointer-events-none opacity-20 filter blur-3xl"
         style="background: radial-gradient(circle, var(--sf-primary, #18181b) 0%, transparent 70%);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Large Liquid Glass Container Card -->
        <div class="rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-10 lg:p-12 border border-white/70 shadow-[0_20px_50px_rgba(0,0,0,0.06)] overflow-hidden relative"
             style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Left Column: Product Information & Purchase Action (7 cols) -->
                <div class="lg:col-span-7 flex flex-col items-start pr-0 lg:pr-6">
                    
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-4 border shadow-2xs"
                         style="background: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.12); border-color: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.25); color: var(--sf-accent, #c59b27);">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        <span>{{ $badge }}</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-stone-950 font-heading leading-[1.15] mb-4">
                        {{ $flagship ? $flagship->title : $vendor->shop_name }}
                    </h1>

                    <p class="text-sm sm:text-base text-stone-600 mb-6 max-w-xl leading-relaxed">
                        {{ $flagship && !empty($flagship->short_description) ? $flagship->short_description : $subtitle }}
                    </p>

                    @if($flagship)
                        <!-- Dynamic Pricing Pill & Sale Badge -->
                        <div class="p-3 sm:p-4 rounded-2xl bg-white/90 border border-stone-200/80 shadow-xs mb-6 w-full max-w-md flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-stone-400 block">Selling Price</span>
                                <div class="flex items-baseline gap-2 mt-0.5">
                                    <span class="text-xl sm:text-2xl font-black text-stone-950">
                                        ₹{{ $priceRupees }}
                                    </span>
                                    @if($hasComparePrice)
                                        <span class="text-xs text-stone-400 line-through">
                                            ₹{{ $comparePriceRupees }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white shadow-2xs">
                                            Save {{ $discountPct }}%
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] font-bold text-emerald-600 flex items-center gap-1 justify-end">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>In Stock</span>
                                </span>
                                <span class="text-[11px] text-stone-400 font-medium">Pan-India Express</span>
                            </div>
                        </div>

                        <!-- Action Buttons: Quick Add to Bag (Triggers Cart Popup) & Full Catalog -->
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 w-full">
                            <button type="button" 
                                    onclick="window.quickAddToCart({{ $flagship->id }}, {{ $defaultVariantId ?: 'null' }})"
                                    class="px-8 py-4 rounded-2xl text-xs sm:text-sm font-extrabold uppercase tracking-wider text-white shadow-xl hover:shadow-2xl transition-all duration-300 flex items-center gap-2 hover:scale-102 cursor-pointer"
                                    style="background: linear-gradient(135deg, var(--sf-primary, #18181b), #27272a); border: 1px solid rgba(255,255,255,0.2);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>Add to Bag · Instant Checkout</span>
                            </button>

                            <a href="#catalog" 
                               class="px-6 py-4 rounded-2xl text-xs sm:text-sm font-bold uppercase tracking-wider text-stone-800 bg-white/80 hover:bg-white border border-stone-300 transition-all shadow-xs">
                                Browse All ({{ $products->total() ?? 0 }})
                            </a>
                        </div>
                    @else
                        <a href="#catalog" 
                           class="px-8 py-4 rounded-2xl text-xs sm:text-sm font-extrabold uppercase tracking-wider text-white shadow-xl hover:scale-102 transition"
                           style="background-color: var(--sf-primary, #18181b);">
                            Explore Store Catalog →
                        </a>
                    @endif

                </div>

                <!-- Right Column: Flagship Product Glass Showcase Card (5 cols) -->
                <div class="lg:col-span-5 w-full">
                    <div class="relative w-full aspect-[3/4] rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl border border-white/80 bg-stone-100 group">
                        @if($flagship)
                            <a href="{{ route('product.show', $flagship->slug) }}" class="block w-full h-full relative">
                                <img src="{{ $flagshipImg }}" 
                                     alt="{{ $flagship->title }}" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/70 via-transparent to-transparent"></div>
                                
                                <div class="absolute bottom-5 inset-x-5 text-white flex items-end justify-between">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-300 block mb-1">
                                            Featured Spotlight
                                        </span>
                                        <h3 class="text-base sm:text-lg font-bold font-heading line-clamp-1">
                                            {{ $flagship->title }}
                                        </h3>
                                    </div>
                                    <span class="p-2.5 rounded-full bg-white/20 backdrop-blur-md text-white group-hover:bg-white group-hover:text-stone-950 transition">
                                        ↗
                                    </span>
                                </div>
                            </a>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-stone-100">
                                <span class="text-5xl mb-3">🛍️</span>
                                <span class="font-heading font-bold text-stone-900">{{ $vendor->shop_name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>
