{{-- PRESET 09: ELYSIUM (Royal Heritage Velvet · 24K Gilded Gilt Luxury · Opulent Midnight Pavilion) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path 
        ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) 
        : ($storeBanner ?: 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-elysium min-h-screen flex flex-col font-body selection:bg-[#d4af37] selection:text-black bg-[#060913] text-[#fcfbf7]"
     style="--sf-accent: #d4af37; --sf-primary: #060913;"
     x-data="{ drawerOpen: false }">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Gilded Crown Marquee Bar --}}
    <div class="w-full bg-[#0a0f20] border-b border-[#d4af37]/30 text-[10px] font-mono tracking-[0.25em] uppercase py-2 px-4 overflow-hidden select-none">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <span class="flex items-center gap-2 text-[#f5d77f]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#d4af37] shadow-[0_0_8px_#d4af37]"></span>
                <span>ROYAL ATELIER PROVENANCE: {{ strtoupper($vendor->city ?: 'HERITAGE GUILD') }}</span>
            </span>
            <span class="hidden md:inline-block text-[#d4af37]/80">24K CERTIFIED HEIRLOOM STANDARDS · FULL INSURED TRANSIT</span>
            <span class="text-[#f5d77f] font-bold">K71 ESCROW PROTECTED</span>
        </div>
    </div>

    {{-- Gilded Royal Pavilion Floating Header --}}
    <div class="sticky top-0 z-40 px-3 sm:px-6 py-2.5 transition-all">
        <header class="max-w-7xl mx-auto rounded-3xl bg-[#060913]/95 backdrop-blur-2xl border border-[#d4af37]/40 shadow-[0_15px_40px_rgba(0,0,0,0.8),0_0_20px_rgba(212,175,55,0.15)] px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4 text-white">
            
            <!-- Left: Hamburger Drawer Trigger + Royal Crest Brand -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 shrink-0">
                <button type="button" 
                        @click="drawerOpen = true"
                        class="p-2 -ml-2 text-[#d4af37] hover:text-white transition cursor-pointer flex items-center justify-center shrink-0"
                        aria-label="Open boutique menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full border border-[#d4af37] bg-[#0d1326] p-1 overflow-hidden shadow-[0_0_12px_rgba(212,175,55,0.3)]">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full border-2 border-[#d4af37] bg-gradient-to-tr from-[#997928] via-[#d4af37] to-[#f5d77f] text-black flex items-center justify-center font-heading font-black text-sm sm:text-base shadow-[0_0_15px_rgba(212,175,55,0.4)]">
                            👑
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-heading font-bold text-base sm:text-xl tracking-wide text-white group-hover:text-[#f5d77f] transition">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono tracking-widest text-[#d4af37] uppercase">
                            ROYAL CHARTER // 2026
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Royal Department Navigation -->
            <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-[#0d1326]/90 border border-[#d4af37]/30 text-xs font-mono">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-4 py-1.5 rounded-full transition {{ !$currentCategory ? 'bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] text-black font-bold shadow-[0_0_15px_rgba(212,175,55,0.4)]' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                    TREASURY ({{ $products->total() ?? $products->count() }})
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                       class="px-4 py-1.5 rounded-full transition {{ $currentCategory === $c->slug ? 'bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] text-black font-bold shadow-[0_0_15px_rgba(212,175,55,0.4)]' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                        {{ strtoupper($c->name) }}
                    </a>
                @endforeach
                <a href="#trust" class="px-4 py-1.5 rounded-full text-slate-300 hover:text-white transition font-bold">
                    CHARTER
                </a>
            </nav>

            <!-- Right: Gilded Bag Button (With Bag Icon, No Plain Text) -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="p-2 sm:px-3 sm:py-2 rounded-full bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] hover:brightness-110 text-black transition-all duration-200 shadow-[0_0_20px_rgba(212,175,55,0.4)] flex items-center gap-2 cursor-pointer hover:scale-105 active:scale-95"
                        aria-label="Shopping Cart">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 rounded-full bg-black text-[#f5d77f] text-[10px] font-black font-mono">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </header>
    </div>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- OPULENT GILDED PAVILION HERO STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4">
            <div class="rounded-[2.5rem] p-6 sm:p-12 lg:p-16 border border-[#d4af37]/40 bg-gradient-to-b from-[#0d1326] via-[#090e1d] to-[#04060c] shadow-[0_25px_80px_rgba(0,0,0,0.9),0_0_40px_rgba(212,175,55,0.12)] relative overflow-hidden">
                
                <!-- Gilded Filigree Geometry -->
                <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full border border-[#d4af37]/20 pointer-events-none"></div>
                <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-80 h-80 rounded-full bg-[#d4af37]/10 blur-3xl pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                    
                    <!-- Left Column: Opulent Statement (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-mono tracking-widest uppercase bg-[#060913] text-[#f5d77f] border border-[#d4af37]/40 font-bold shadow-xs">
                            <span>✦ ROYAL GUILD APPOINTMENT</span>
                            <span>•</span>
                            <span>{{ strtoupper($vendor->city ?: 'ATELIER') }}</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-heading font-black tracking-tight text-white leading-[1.08]">
                            <span class="block text-[#f5d77f] font-normal italic text-2xl sm:text-3xl mb-1">Heirloom Distinction</span>
                            {{ $storeName }}
                        </h1>

                        <p class="text-xs sm:text-base text-slate-300 leading-relaxed max-w-xl font-normal">
                            {{ $vendor->description ?: 'Regal artisanal craftsmanship forged with precious materials, museum integrity, and flawless finishing. Direct workshop provenance accompanied by K71 escrow protection and insured doorstep delivery.' }}
                        </p>

                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <a href="#catalog" class="px-8 py-3.5 rounded-full bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] hover:brightness-110 text-black font-mono text-xs font-black uppercase tracking-wider transition-all duration-200 text-center shadow-[0_0_25px_rgba(212,175,55,0.4)] hover:scale-105 active:scale-95">
                                Explore Treasury ({{ $products->total() ?? $products->count() }} Pieces) ↓
                            </a>
                            <span class="text-[11px] font-mono text-[#d4af37] text-center sm:text-left">
                                Verified Royal Seal · 100% Genuine
                            </span>
                        </div>

                    </div>

                    <!-- Right Column: Velvet Portrait Showcase (5 Cols) -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-[340px] aspect-[3/4] rounded-2xl overflow-hidden border-2 border-[#d4af37]/60 shadow-[0_15px_40px_rgba(0,0,0,0.8),0_0_25px_rgba(212,175,55,0.2)] bg-[#060913] group">
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $flagship->title ?? $storeName }}" 
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">
                            
                            <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-[#060913] via-[#060913]/80 to-transparent text-white border-t border-[#d4af37]/20">
                                <span class="text-[9px] font-mono uppercase tracking-widest text-[#f5d77f] block">Crown Masterpiece</span>
                                <h4 class="font-heading font-bold text-sm truncate text-white">{{ $flagship->title ?? $storeName }}</h4>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- ROYAL TREASURY PRODUCT ARCHIVE --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            
            <!-- Category Navigation -->
            <div class="p-4 sm:p-5 rounded-3xl bg-[#0d1326] border border-[#d4af37]/30 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-heading font-black tracking-tight text-white">
                        Royal Treasury Archive
                    </h2>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">
                        Each item packaged directly under master guild seal
                    </p>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-4 py-1.5 rounded-full text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-gradient-to-r from-[#b38728] to-[#d4af37] text-black font-bold shadow-xs' : 'bg-[#060913] border border-[#d4af37]/30 text-slate-300 hover:text-white' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-4 py-1.5 rounded-full text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-gradient-to-r from-[#b38728] to-[#d4af37] text-black font-bold shadow-xs' : 'bg-[#060913] border border-[#d4af37]/30 text-slate-300 hover:text-white' }}">
                            {{ strtoupper($c->name) }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center rounded-3xl border border-[#d4af37]/30 bg-[#0d1326] p-8 space-y-3">
                    <span class="text-2xl font-heading text-[#d4af37] block">👑 Treasury Empty 👑</span>
                    <p class="text-xs text-slate-400">No items match this category chamber.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block mt-3 px-6 py-2 rounded-full border border-[#d4af37] text-xs font-mono uppercase tracking-widest text-[#f5d77f] hover:bg-[#d4af37] hover:text-black transition">
                        Reset Chamber
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        @php
                            $priceRupees = number_format($product->base_price / 100, 2);
                            $hasComparePrice = !empty($product->compare_at_price) && $product->compare_at_price > $product->base_price;
                            $comparePriceRupees = $hasComparePrice ? number_format($product->compare_at_price / 100, 2) : null;
                            $discountPct = $hasComparePrice ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                            
                            $imgFirst = $product->images->first()?->path ? \App\Services\ImageService::url($product->images->first()->path, 600) : asset('images/product-placeholder.svg');
                            $isOutOfStock = $product->total_stock <= 0 && $product->variants->sum('stock') <= 0;
                        @endphp
                        
                        <div class="group rounded-2xl border border-[#d4af37]/30 bg-[#0d1326] hover:border-[#d4af37] hover:shadow-[0_0_25px_rgba(212,175,55,0.25)] transition-all duration-300 flex flex-col justify-between overflow-hidden">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-[3/4] bg-[#060913] overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>

                                @if($discountPct > 0)
                                    <div class="absolute top-2.5 left-2.5">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#d4af37] text-black font-mono text-[9px] font-black uppercase tracking-wider shadow-xs">
                                            -{{ $discountPct }}%
                                        </span>
                                    </div>
                                @endif

                                @if($product->is_featured)
                                    <div class="absolute top-2.5 right-2.5">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#060913] border border-[#d4af37] text-[#f5d77f] font-mono text-[9px] font-bold uppercase tracking-wider shadow-xs">
                                            HEIRLOOM
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[9px] font-mono text-[#d4af37] uppercase tracking-widest block truncate mb-1">
                                        {{ $product->category?->name ?? 'ROYAL PIECE' }}
                                    </span>
                                    <h3 class="text-xs sm:text-sm font-heading font-bold text-white line-clamp-2 leading-snug group-hover:text-[#f5d77f] transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="pt-2 border-t border-[#d4af37]/20 flex items-baseline justify-between gap-1">
                                    <div class="flex items-baseline gap-1.5 flex-wrap">
                                        <span class="text-sm sm:text-base font-bold font-mono text-[#f5d77f]">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($comparePriceRupees)
                                            <span class="text-xs font-mono text-slate-500 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Add to Bag Action -->
                                <div class="pt-1">
                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $product->variants->first()?->id ?? 'null' }}, this)"
                                                class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#b38728] via-[#d4af37] to-[#f5d77f] hover:brightness-110 text-black font-mono text-[11px] font-black uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-xs">
                                            + RESERVE PIECE
                                        </button>
                                    @else
                                        <div class="w-full py-2 rounded-xl text-center text-[10px] font-mono text-slate-500 uppercase bg-black/60 border border-zinc-800">
                                            Archived
                                        </div>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                @if(method_exists($products, 'links') && $products->hasPages())
                    <div class="mt-10 flex justify-center font-mono">
                        {{ $products->fragment('catalog')->links() }}
                    </div>
                @endif
            @endif

        </section>

        {{-- Studio Shorts & Video Reels Showcase --}}
        @include('storefront.sections.video_showcase')

        {{-- Genuine Customer Reviews (Immediately Above Happy Customers) --}}
        @include('storefront.sections.reviews_section')

        {{-- Happy Customers Photos & Videos Slider (Shown Just Below Reviews) --}}
        @include('storefront.sections.happy_customers')

        {{-- ROYAL CHARTER PROTOCOL --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="rounded-3xl border border-[#d4af37]/30 bg-[#0d1326] p-6 sm:p-10 shadow-2xl">
                <div class="pb-5 mb-6 border-b border-[#d4af37]/20 flex items-center justify-between font-mono text-xs">
                    <span class="font-bold uppercase tracking-[0.2em] text-[#f5d77f] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#d4af37] shadow-[0_0_8px_#d4af37]"></span>
                        ROYAL CHARTER // HEIRLOOM SPECIFICATIONS
                    </span>
                    <span class="text-[#d4af37]">100% CROWN AUTHENTICATED</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-mono text-xs">
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-[#060913] border border-[#d4af37]/20">
                        <span class="text-[10px] text-[#f5d77f] uppercase block font-bold">01 // PROVENANCE</span>
                        <h4 class="font-bold text-white uppercase">Guild Origin</h4>
                        <p class="text-[11px] text-slate-300">Package origin authenticated at {{ $vendor->city ?: 'the master guild' }}.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-[#060913] border border-[#d4af37]/20">
                        <span class="text-[10px] text-[#f5d77f] uppercase block font-bold">02 // PROTECTION</span>
                        <h4 class="font-bold text-white uppercase">Escrow Vault</h4>
                        <p class="text-[11px] text-slate-300">Funds secure in escrow until confirmed doorstep inspection.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-[#060913] border border-[#d4af37]/20">
                        <span class="text-[10px] text-[#f5d77f] uppercase block font-bold">03 // TRANSIT</span>
                        <h4 class="font-bold text-white uppercase">Insured Courier</h4>
                        <p class="text-[11px] text-slate-300">Full transit insurance across all Indian pin codes.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-[#060913] border border-[#d4af37]/20">
                        <span class="text-[10px] text-[#f5d77f] uppercase block font-bold">04 // EXCHANGE</span>
                        <h4 class="font-bold text-white uppercase">Guild Care</h4>
                        <p class="text-[11px] text-slate-300">{{ $vendor->settings?->return_window_days ?: 7 }}-day royal concierge exchange window.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Dark Opulent Footer --}}
    @include('storefront.sections.footer')

    {{-- Slide-From-Left Menu Drawer --}}
    @include('storefront.sections.theme_drawer')

</div>

