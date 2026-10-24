<x-layouts.public 
    :title="$seoTitle" 
    :metaDescription="$seoDescription" 
    :ogImage="$ogImage" 
    :canonicalUrl="$canonicalUrl"
    ogType="product"
    :schemaJson="$schemaJson"
>
    @php
        $discount = 0;
        if($product->compare_at_price > $product->base_price) {
            $discount = round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100);
        }
        $savings = max(0, ($product->compare_at_price - $product->base_price) / 100);
        
        $sellerPhone = preg_replace('/[^0-9]/', '', $product->vendor->contact_phone ?? '');
        if (strlen($sellerPhone) === 10) { $sellerPhone = '91' . $sellerPhone; }
        $waProductMsg = rawurlencode("Hello {$product->vendor->shop_name}! I am interested in {$product->title} (₹" . number_format($product->base_price / 100, 2) . ") on K71 Marketplace. Could you please share more details or availability?");

        $isWishlisted = in_array($product->id, \App\Models\Wishlist::getUserWishlistIds());
        $hasMultipleVariants = $product->variants->count() > 0 && !($product->variants->count() == 1 && $product->variants->first()?->sku === 'Default');
        $hasSizeGuide = !($product->size_chart && isset($product->size_chart['type']) && $product->size_chart['type'] === 'none');
        $sc = $product->size_chart;
        $hasCustomChart = $sc && !empty($sc['columns']) && !empty($sc['rows']) && ($sc['type'] ?? '') !== 'none';
        
        $imageUrls = $product->images->map(fn($img) => $img->url)->values();
        if ($imageUrls->isEmpty()) {
            $imageUrls = collect([asset('images/product-placeholder.svg')]);
        }

        // Dynamic Vendor Operations & Logistics Settings
        $vendorSettings = $product->vendor->settings;
        $processingDays = $vendorSettings ? (int) ($vendorSettings->processing_days ?: 2) : 2;
        $returnDays = $product->return_window_days !== null ? (int) $product->return_window_days : ($vendorSettings ? (int) ($vendorSettings->return_window_days ?: 7) : 7);
        $isReturnable = $product->is_returnable ?? ($returnDays > 0);
        $isCodAvailable = $vendorSettings ? (bool) $vendorSettings->cod_enabled : false;

        $serviceablePincodes = null;
        if ($vendorSettings && $vendorSettings->serviceable_pincodes) {
            $serviceablePincodes = is_array($vendorSettings->serviceable_pincodes) 
                ? $vendorSettings->serviceable_pincodes 
                : json_decode($vendorSettings->serviceable_pincodes, true);
        }

        $totalAvailableStock = $product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->total_stock;
        $isEntirelyOutOfStock = $totalAvailableStock <= 0;
        $firstInStockVariant = $product->variants->firstWhere('stock', '>', 0);
        $productPreset = $product->vendor->product_preset ?? 'gallery';
    @endphp

    <!-- Universal Blueprint Variants JSON Repository -->
    <script type="application/json" id="product-variants-data">
        {!! json_encode($product->variants->map(function($v) use ($product) { 
            return [
                'id' => $v->id, 
                'sku' => $v->sku ?: 'Default',
                'name' => $v->sku ?: 'Default',
                'color' => $v->attributes_hash['color'] ?? ($v->color ?? ''),
                'color_hex' => $v->attributes_hash['color_hex'] ?? ($v->color_hex ?? '#111827'),
                'size' => $v->attributes_hash['size'] ?? ($v->size ?? $v->sku),
                'stock' => (int) ($v->stock_quantity ?: $v->stock),
                'price' => $v->price ? number_format((float)$v->price, 2, '.', '') : ($v->price_override ? number_format($v->price_override / 100, 2, '.', '') : number_format($product->base_price / 100, 2, '.', '')),
                'image_url' => $v->image_url,
                'attributes_hash' => $v->attributes_hash ?: []
            ]; 
        })) !!}
    </script>

    <div x-data="{ 
        quantity: 1, 
        selectedVariant: {{ $firstInStockVariant?->id ?? ($product->variants->first()?->id ?? 'null') }}, 
        adding: false, 
        message: '',
        messageType: 'success',
        showSizeGuide: false,
        sizeUnit: 'in',
        lightboxOpen: false,
        activeImgIndex: 0,
        images: {{ $imageUrls->toJson() }},
        wishlisted: {{ $isWishlisted ? 'true' : 'false' }},
        shareCopied: false,
        isScrolledPastCTA: false,
        touchStartX: 0,
        touchEndX: 0,
        isEntirelyOutOfStock: {{ $isEntirelyOutOfStock ? 'true' : 'false' }},
        customPriceOverride: null,
        selectedFoodModifiers: [],
        variants: {{ $product->variants->map(function($v) use ($product) { 
            return [
                'id' => $v->id, 
                'sku' => $v->sku ?: 'Default',
                'name' => $v->sku ?: 'Default',
                'color' => $v->attributes_hash['color'] ?? ($v->color ?? ''),
                'color_hex' => $v->attributes_hash['color_hex'] ?? ($v->color_hex ?? '#111827'),
                'size' => $v->attributes_hash['size'] ?? ($v->size ?? $v->sku),
                'stock' => (int) ($v->stock_quantity ?: $v->stock),
                'price' => $v->price ? number_format((float)$v->price, 2, '.', '') : ($v->price_override ? number_format($v->price_override / 100, 2, '.', '') : number_format($product->base_price / 100, 2, '.', '')),
                'image_url' => $v->image_url,
                'attributes_hash' => $v->attributes_hash ?: []
            ]; 
        })->toJson() }},
        get currentPrice() { 
            if(this.customPriceOverride !== null) return this.customPriceOverride;
            if(!this.selectedVariant) return '{{ number_format($product->base_price / 100, 2) }}'; 
            const v = this.variants.find(x => x.id == this.selectedVariant); 
            return v ? v.price : '{{ number_format($product->base_price / 100, 2) }}'; 
        },
        get selectedStock() {
            if(!this.selectedVariant) {
                return this.variants.reduce((sum, v) => sum + (v.stock || 0), 0);
            }
            const v = this.variants.find(x => x.id == this.selectedVariant);
            return v ? v.stock : 0;
        },
        get isCurrentVariantOutOfStock() {
            if(this.isEntirelyOutOfStock) return true;
            if(!this.selectedVariant) return false;
            const v = this.variants.find(x => x.id == this.selectedVariant);
            return !v || v.stock <= 0;
        },
        selectImage(idx) {
            this.activeImgIndex = idx;
        },
        nextImage() {
            if (this.images.length > 0) {
                this.activeImgIndex = (this.activeImgIndex + 1) % this.images.length;
            }
        },
        prevImage() {
            if (this.images.length > 0) {
                this.activeImgIndex = (this.activeImgIndex - 1 + this.images.length) % this.images.length;
            }
        },
        handleTouchStart(e) {
            this.touchStartX = e.changedTouches[0].screenX;
        },
        handleTouchEnd(e) {
            this.touchEndX = e.changedTouches[0].screenX;
            if (this.touchStartX - this.touchEndX > 45) {
                this.nextImage();
            } else if (this.touchEndX - this.touchStartX > 45) {
                this.prevImage();
            }
        },
        toggleWishlist() {
            this.wishlisted = !this.wishlisted;
            if (typeof window.toggleProductWishlist === 'function') {
                window.toggleProductWishlist({{ $product->id }}, this.wishlisted, () => {
                    this.wishlisted = !this.wishlisted;
                });
            }
        },
        shareProduct() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ addslashes($product->title) }}',
                    text: 'Explore {{ addslashes($product->title) }} by {{ addslashes($product->vendor->shop_name) }}',
                    url: window.location.href
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href);
                this.shareCopied = true;
                setTimeout(() => this.shareCopied = false, 2500);
            }
        },
        submitAddToCart() {
            if (this.isEntirelyOutOfStock || this.selectedStock <= 0) {
                this.message = 'This product is currently completely sold out.';
                this.messageType = 'error';
                return;
            }
            if(!this.selectedVariant) {
                const inStockVariant = this.variants.find(x => x.stock > 0) || this.variants[0];
                if (inStockVariant) {
                    this.selectedVariant = inStockVariant.id;
                } else {
                    this.message = 'This item is currently out of stock.';
                    this.messageType = 'error';
                    return;
                }
            }
            const activeVar = this.variants.find(x => x.id == this.selectedVariant);
            if (!activeVar || activeVar.stock <= 0) {
                this.message = 'The selected option is currently sold out.';
                this.messageType = 'error';
                return;
            }
            if (this.quantity > activeVar.stock) {
                this.message = `Only ${activeVar.stock} pieces available in stock for size ${activeVar.sku}.`;
                this.messageType = 'error';
                return;
            }
            this.adding = true;
            this.message = '';
            const payload = {
                product_id: {{ $product->id }},
                variant_id: this.selectedVariant,
                quantity: this.quantity,
                selected_modifiers: this.selectedFoodModifiers.map(m => m.id)
            };
            fetch('/cart/add', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                this.adding = false;
                if(data.success) {
                    this.message = '✓ ' + (data.message || 'Added to cart successfully!');
                    this.messageType = 'success';
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    window.dispatchEvent(new CustomEvent('open-mini-cart'));
                } else {
                    this.message = data.message || 'Could not add to cart.';
                    this.messageType = 'error';
                }
            }).catch(e => {
                this.adding = false;
                this.message = 'Error connecting to cart. Please try again.';
                this.messageType = 'error';
            });
        },
        init() {
            window.addEventListener('variant-selected', (e) => {
                if (e.detail) {
                    this.selectedVariant = e.detail.id;
                    if (e.detail.price) {
                        this.customPriceOverride = e.detail.price;
                    }
                }
            });

            window.addEventListener('food-selection-updated', (e) => {
                if (e.detail) {
                    if (e.detail.portion) {
                        this.selectedVariant = e.detail.portion.id;
                    }
                    this.selectedFoodModifiers = e.detail.modifiers || [];
                    this.customPriceOverride = e.detail.totalPrice;
                }
            });

            window.addEventListener('swap-product-image', (e) => {
                if (e.detail && e.detail.url) {
                    const idx = this.images.indexOf(e.detail.url);
                    if (idx > -1) {
                        this.activeImgIndex = idx;
                    } else {
                        this.images.unshift(e.detail.url);
                        this.activeImgIndex = 0;
                    }
                }
            });

            window.addEventListener('open-size-guide', () => {
                this.showSizeGuide = true;
            });

            if ('IntersectionObserver' in window && this.$refs.mainBuyBtn) {
                const observer = new IntersectionObserver(([entry]) => {
                    this.isScrolledPastCTA = !entry.isIntersecting;
                }, { threshold: 0 });
                observer.observe(this.$refs.mainBuyBtn);
            }
        }
    }" class="relative">

        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 sm:mb-6 overflow-x-auto no-scrollbar py-1">
            <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors whitespace-nowrap">Home</a>
            <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            @if($product->category)
                <a href="{{ route('category.show', $product->category->slug) }}" class="hover:text-brand-text transition-colors whitespace-nowrap">{{ $product->category->name }}</a>
                <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            @endif
            <span class="text-brand-text font-medium truncate max-w-[200px] sm:max-w-xs">{{ $product->title }}</span>
        </nav>

        <!-- Main Product Card: Clean, Edge-Optimized Luxury Canvas -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200/80 shadow-xs overflow-hidden mb-16">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                
                <!-- LEFT COLUMN: Product Gallery (7 cols on Desktop) -->
                <div class="lg:col-span-7 bg-[#FAF8F5]/60 p-3 sm:p-6 lg:p-8 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-stone-200/70">
                    <div>
                        <!-- Mobile & Desktop Gallery Container -->
                        <div class="flex flex-col-reverse lg:flex-row gap-3 sm:gap-4">
                            
                            <!-- Thumbnail Rail (Vertical on Desktop, Horizontal on Mobile) -->
                            @if($product->images->count() > 1)
                                <div class="flex lg:flex-col gap-2.5 overflow-x-auto lg:overflow-y-auto max-h-[580px] no-scrollbar py-1 px-0.5 shrink-0">
                                    @foreach($product->images as $index => $image)
                                        <button type="button" 
                                                @click="selectImage({{ $index }})"
                                                :class="activeImgIndex === {{ $index }} ? 'ring-2 ring-brand-text ring-offset-2 border-transparent shadow-sm' : 'border-stone-200/80 opacity-70 hover:opacity-100'"
                                                class="relative w-14 h-18 sm:w-16 sm:h-22 rounded-xl overflow-hidden bg-white border transition-all duration-200 shrink-0 group focus:outline-none">
                                            <img src="{{ asset('storage/' . $image->path) }}" 
                                                 alt="{{ $product->title }} angle {{ $index + 1 }}" 
                                                 class="w-full h-full object-cover object-top transition-transform duration-300 group-hover:scale-105"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Main Spotlight Stage -->
                            <div class="relative w-full aspect-[3/4] sm:aspect-[4/5] rounded-2xl overflow-hidden bg-[#FAF7F2] flex-1 border border-stone-200/60 shadow-inner group"
                                 @touchstart="handleTouchStart($event)"
                                 @touchend="handleTouchEnd($event)">
                                
                                @if($product->images->count() > 0)
                                    <!-- Dynamic Main Image -->
                                    <template x-for="(imgSrc, idx) in images" :key="idx">
                                        <img x-show="activeImgIndex === idx"
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 scale-98"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             :src="imgSrc" 
                                             alt="{{ $product->title }}" 
                                             class="absolute inset-0 w-full h-full object-cover object-top cursor-zoom-in"
                                             @click="lightboxOpen = true"
                                             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                    </template>
                                @else
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-stone-400 bg-stone-50">
                                        <svg class="w-12 h-12 stroke-[1.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-xs mt-2 uppercase tracking-wider font-semibold">Image Preview Coming Soon</span>
                                    </div>
                                @endif

                                <!-- Floating Badges (Top Left) -->
                                <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10 pointer-events-none">
                                    @if($discount > 0)
                                        <span class="px-2.5 py-1 bg-brand-rose/90 backdrop-blur-md text-brand-text font-bold text-[10px] tracking-wider uppercase rounded-full shadow-2xs">
                                            {{ $discount }}% OFF
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-1 bg-white/90 backdrop-blur-md text-brand-text font-semibold text-[10px] tracking-wider uppercase rounded-full shadow-2xs">
                                        Handcrafted
                                    </span>
                                </div>

                                <!-- Floating Action Icons (Top Right) -->
                                <div class="absolute top-3 right-3 flex flex-col gap-2 z-10">
                                    <!-- Wishlist Heart -->
                                    <button type="button" 
                                            @click="toggleWishlist()"
                                            class="w-9 h-9 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center transition-all duration-200 shadow-sm hover:scale-105 active:scale-95"
                                            :class="wishlisted ? 'text-rose-500 bg-rose-50/90' : 'text-stone-700 hover:text-rose-500'">
                                        <svg class="w-4.5 h-4.5 transition-transform" :fill="wishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </button>

                                    <!-- Share Icon -->
                                    <button type="button" 
                                            @click="shareProduct()"
                                            class="w-9 h-9 rounded-full bg-white/90 backdrop-blur-md text-stone-700 hover:text-brand-text flex items-center justify-center transition-all duration-200 shadow-sm hover:scale-105 active:scale-95"
                                            title="Share this product">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                                    </button>
                                </div>

                                <!-- Floating Zoom Pill (Bottom Right) -->
                                <button type="button" 
                                        @click="lightboxOpen = true"
                                        class="absolute bottom-3 right-3 px-3 py-1.5 rounded-full bg-white/90 backdrop-blur-md text-stone-700 hover:text-black text-[11px] font-medium flex items-center gap-1.5 shadow-sm transition-all hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path></svg>
                                    <span>Zoom</span>
                                </button>

                                <!-- Mobile Gallery Swipe Pill (Bottom Center) -->
                                @if($product->images->count() > 1)
                                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-black/60 backdrop-blur-sm text-white text-[11px] font-semibold tracking-wider flex items-center gap-1 shadow-sm sm:hidden">
                                        <span x-text="activeImgIndex + 1">1</span>
                                        <span class="opacity-60">/</span>
                                        <span x-text="images.length">{{ $product->images->count() }}</span>
                                    </div>
                                @endif

                                <!-- Slide Navigation Arrows (Desktop & Tablet Hover) -->
                                @if($product->images->count() > 1)
                                    <button type="button" @click.stop="prevImage()" 
                                            class="hidden sm:flex absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-stone-700 items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                    </button>
                                    <button type="button" @click.stop="nextImage()" 
                                            class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-stone-700 items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Toast notification for link share -->
                        <div x-show="shareCopied" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mt-2 text-center text-xs text-emerald-800 bg-emerald-50 py-1.5 px-3 rounded-xl border border-emerald-200 font-medium">
                            ✓ Product link copied to clipboard!
                        </div>
                    </div>

                    <!-- Gallery Bottom Guarantee Micro-Strip -->
                    <div class="hidden sm:grid grid-cols-3 gap-3 pt-6 mt-6 border-t border-stone-200/60 text-stone-600 text-[11px]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-sage shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-medium">100% Genuine Handcraft</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-sage shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-medium">Dispatches in 24-48 hrs</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-sage shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span class="font-medium">7 Days Returnable</span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Product Dossier & Buying Controls (5 cols on Desktop) -->
                <div class="lg:col-span-5 p-4 sm:p-6 lg:p-8 flex flex-col justify-start">
                    
                    <!-- Vendor Profile / Boutique Verification Badge -->
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                        <a href="{{ $product->vendor->storefront_url }}" class="flex items-center gap-2.5 group">
                            <div class="w-8 h-8 rounded-full bg-brand-surface border border-brand-sage/40 flex items-center justify-center text-xs font-serif font-bold text-brand-text group-hover:scale-105 transition-transform">
                                {{ substr($product->vendor->shop_name, 0, 1) }}
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest block leading-none">Boutique</span>
                                <span class="text-xs font-bold text-brand-text group-hover:text-brand-sage transition-colors leading-tight">
                                    {{ $product->vendor->shop_name }}
                                </span>
                            </div>
                        </a>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200/70">
                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            <span>Verified</span>
                        </span>
                    </div>

                    <!-- Title & Silhouette -->
                    <div class="mt-3">
                        <div class="flex items-center gap-2 text-[11px] font-semibold text-brand-sage uppercase tracking-wider mb-1 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">
                                {{ ucfirst($product->vertical ?? 'marketplace') }}
                            </span>
                            @if($product->vertical === 'used' || $product->getCustomField('condition'))
                                <span class="px-2.5 py-0.5 rounded-full bg-cyan-100 text-cyan-800 font-bold border border-cyan-300">
                                    ★ {{ $product->getCustomField('condition', 'Pre-Owned') }}
                                </span>
                            @endif
                            @if(!$product->isPurchase())
                                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200">
                                    {{ ucfirst(str_replace('_', ' ', $product->listing_type)) }} Only
                                </span>
                            @endif
                            <span>{{ $product->category->name ?? 'Category' }}</span>
                            @if($product->gender)
                                <span>•</span>
                                <span>{{ ucfirst($product->gender) }}</span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-serif font-semibold text-brand-text tracking-tight leading-snug">
                            {{ $product->title }}
                        </h1>
                    </div>

                    <!-- Pricing & Savings Hub -->
                    <div class="mt-4 p-3.5 rounded-2xl bg-[#FAF8F5] border border-stone-200/60">
                        <div class="flex items-baseline gap-2.5 flex-wrap">
                            <span class="text-3xl font-serif font-bold text-brand-text">
                                ₹<span x-text="currentPrice">{{ number_format($product->base_price / 100, 2) }}</span>
                            </span>
                            @if($product->compare_at_price > $product->base_price)
                                <span class="text-base text-gray-400 line-through font-normal">
                                    ₹{{ number_format($product->compare_at_price / 100, 2) }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-brand-rose/20 text-brand-text font-bold text-xs">
                                    Save ₹{{ number_format($savings, 0) }} ({{ $discount }}% OFF)
                                </span>
                            @endif
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-gray-500">
                            <span>Inclusive of all taxes (GST {{ $product->gst_rate }}% included)</span>
                            @if($product->hsn_code)
                                <span class="font-mono text-[10px] text-gray-400">HSN: {{ $product->hsn_code }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Urgency or Sold Out Alert -->
                    @if($isEntirelyOutOfStock)
                        <div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm flex items-start gap-3">
                            <span class="text-xl shrink-0 mt-0.5">🚫</span>
                            <div>
                                <p class="font-bold text-rose-950">Currently Sold Out</p>
                                <p class="text-xs text-rose-700 mt-0.5">This item is currently out of stock. Contact the seller below to inquire about restock availability.</p>
                            </div>
                        </div>
                    @elseif($totalAvailableStock > 0 && $totalAvailableStock <= 5)
                        <div class="mt-3 flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50/80 border border-amber-200/70 text-amber-900 text-xs font-semibold">
                            <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                            <span>Limited Edition! Only {{ $totalAvailableStock }} pieces left in stock.</span>
                        </div>
                    @endif

                    <!-- Purchasing Form -->
                    <form @submit.prevent="submitAddToCart()" class="mt-5">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        @php $verticalType = $product->vertical_type; @endphp

                        <!-- Universal Dynamic Vertical Buying Interface -->
                        <div class="mb-5">
                            @if($verticalType === 'fashion_matrix')
                                @include('catalog.partials.variant_fashion')
                            @elseif($verticalType === 'food_portion')
                                @include('catalog.partials.variant_food')
                            @elseif($verticalType === 'digital_gig')
                                @include('catalog.partials.variant_gig')
                            @elseif($verticalType === 'b2b_machinery')
                                @include('catalog.partials.variant_b2b')
                            @elseif($verticalType === 'service_booking')
                                @include('catalog.partials.variant_service')
                            @elseif($verticalType === 'classified_asset')
                                @include('catalog.partials.variant_used')
                            @else
                                @include('catalog.partials.variant_fashion')
                            @endif
                        </div>

                        <!-- Vertical Context Action CTAs -->
                        @if($product->isPurchase())
                            @if($verticalType === 'food_portion')
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center border border-stone-300 rounded-full bg-white px-2 py-1 shadow-2xs">
                                        <button type="button" @click="if(quantity > 1) quantity--" 
                                                class="w-7 h-8 flex items-center justify-center text-stone-600 hover:text-black font-bold text-sm disabled:opacity-30" 
                                                :disabled="quantity <= 1">-</button>
                                        <span x-text="quantity" class="w-8 text-center text-xs font-bold text-stone-900">1</span>
                                        <button type="button" @click="quantity++" 
                                                class="w-7 h-8 flex items-center justify-center text-stone-600 hover:text-black font-bold text-sm">+</button>
                                    </div>
                                    <button type="submit" 
                                            x-ref="mainBuyBtn"
                                            :disabled="adding || !selectedVariant" 
                                            class="flex-1 py-3.5 px-6 rounded-full bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 active:scale-[0.99] cursor-pointer">
                                        <span x-text="adding ? 'Adding to Order...' : `🍲 Add to Feast (₹${(parseFloat(currentPrice) * quantity).toFixed(2)})`"></span>
                                    </button>
                                </div>
                            @elseif($verticalType === 'classified_asset')
                                <div class="flex items-center gap-3">
                                    <button type="submit" 
                                            x-ref="mainBuyBtn"
                                            :disabled="adding || isEntirelyOutOfStock" 
                                            class="flex-1 py-3.5 px-6 rounded-full bg-stone-900 hover:bg-black text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 active:scale-[0.99] cursor-pointer">
                                        <span>🔒 Buy Verified Asset Now (₹{{ number_format($product->base_price / 100, 2) }})</span>
                                    </button>
                                </div>
                            @elseif($verticalType === 'b2b_machinery')
                                <div class="flex items-center gap-3 pt-2">
                                    <button type="submit" 
                                            x-ref="mainBuyBtn"
                                            :disabled="adding || isEntirelyOutOfStock" 
                                            class="flex-1 py-3 px-5 rounded-full bg-white hover:bg-stone-50 border border-stone-300 text-stone-800 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer">
                                        <span>🛒 Buy Single Sample Machine</span>
                                    </button>
                                </div>
                            @elseif($verticalType !== 'digital_gig' && $verticalType !== 'service_booking')
                                <!-- Quantity Stepper & Main Add To Cart Row -->
                                <div class="flex items-center gap-3">
                                    <!-- Stepper -->
                                    <div class="flex items-center border border-stone-300 rounded-full bg-white px-2 py-1 shadow-2xs {{ $isEntirelyOutOfStock ? 'opacity-40 pointer-events-none' : '' }}">
                                        <button type="button" @click="if(quantity > 1) quantity--" 
                                                class="w-7 h-8 flex items-center justify-center text-stone-600 hover:text-black font-bold text-sm disabled:opacity-30" 
                                                :disabled="quantity <= 1">-</button>
                                        <span x-text="quantity" class="w-8 text-center text-xs font-bold text-brand-text">1</span>
                                        <button type="button" @click="if(quantity < selectedStock) quantity++" 
                                                class="w-7 h-8 flex items-center justify-center text-stone-600 hover:text-black font-bold text-sm disabled:opacity-30"
                                                :disabled="quantity >= selectedStock">+</button>
                                    </div>

                                    <!-- Primary Add to Cart CTA -->
                                    <button type="submit" 
                                            x-ref="mainBuyBtn"
                                            :disabled="adding || isEntirelyOutOfStock || (selectedVariant && isCurrentVariantOutOfStock) || selectedStock === 0" 
                                            class="flex-1 py-3.5 px-6 rounded-full bg-brand-text hover:bg-black text-white text-xs sm:text-sm font-bold tracking-wide transition-all duration-200 shadow-md flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99]"
                                            :class="isEntirelyOutOfStock || (selectedVariant && isCurrentVariantOutOfStock) || selectedStock === 0 ? '!bg-stone-300 !text-stone-600' : ''">
                                        <svg x-show="!adding && !isEntirelyOutOfStock && (!selectedVariant || !isCurrentVariantOutOfStock) && selectedStock > 0" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        <svg x-show="adding" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="adding ? 'Adding to Bag...' : (isEntirelyOutOfStock || (selectedVariant && isCurrentVariantOutOfStock) || selectedStock === 0 ? 'Sold Out' : 'Add to Bag')"></span>
                                    </button>
                                </div>
                            @endif
                        @elseif($product->isEnquiry())
                            <div class="space-y-2">
                                <a href="https://wa.me/{{ $sellerPhone ?? '' }}?text={{ rawurlencode('Hello! I would like to enquire about bulk/wholesale order for: ' . $product->title . ' on K71') }}" target="_blank"
                                   class="w-full py-3.5 px-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2">
                                    <span>📋</span>
                                    <span>Request Custom Quotation / Bulk Enquiry</span>
                                </a>
                                <p class="text-[11px] text-center text-stone-500">This is an enquiry-only listing for wholesale & custom orders.</p>
                            </div>
                        @elseif($product->isBooking())
                            <div class="space-y-3" x-data="{ showBookingModal: false, selectedSlot: '10:00 AM - 12:00 PM' }">
                                <button type="button" @click="showBookingModal = true"
                                        class="w-full py-3.5 px-6 rounded-full bg-zinc-900 hover:bg-black text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Book Service Slot / Appointment</span>
                                </button>

                                <a href="https://wa.me/{{ $sellerPhone ?? '' }}?text={{ rawurlencode('Hello! I would like to book a slot for service: ' . $product->title . ' on K71') }}" target="_blank"
                                   class="w-full py-2.5 px-5 rounded-full bg-zinc-100 hover:bg-zinc-200 text-zinc-900 border border-zinc-200 text-xs font-semibold flex items-center justify-center gap-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Chat with Professional on WhatsApp</span>
                                </a>

                                <p class="text-[11px] text-center text-stone-500">Doorstep service coordinated directly with verified provider.</p>

                                <!-- Booking Modal -->
                                <div x-show="showBookingModal" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                                     style="display: none;"
                                     @keydown.escape.window="showBookingModal = false">
                                    
                                    <div @click.away="showBookingModal = false"
                                         class="w-full max-w-lg bg-slate-900 border border-white/15 rounded-3xl p-6 shadow-2xl text-left text-slate-100 relative max-h-[90vh] overflow-y-auto">
                                        
                                        <button type="button" @click="showBookingModal = false" 
                                                class="absolute top-4 right-4 text-slate-400 hover:text-white p-2 rounded-full hover:bg-white/10 transition">
                                            ✕
                                        </button>

                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="text-2xl p-2.5 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">📅</div>
                                            <div>
                                                <h3 class="text-lg font-black text-white">Book Service Appointment</h3>
                                                <p class="text-xs text-slate-400">{{ $product->title }}</p>
                                            </div>
                                        </div>

                                        <form action="{{ route('services.book', $product->id) }}" method="POST" class="space-y-4">
                                            @csrf

                                            <!-- Date and Slot -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-300 mb-1">Appointment Date *</label>
                                                    <input type="date" name="booking_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                                           class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-300 mb-1">Time Slot *</label>
                                                    <select name="booking_slot" required x-model="selectedSlot"
                                                            class="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                                        <option value="09:00 AM - 11:00 AM">09:00 AM - 11:00 AM (Morning)</option>
                                                        <option value="11:00 AM - 01:00 PM">11:00 AM - 01:00 PM (Noon)</option>
                                                        <option value="02:00 PM - 04:00 PM">02:00 PM - 04:00 PM (Afternoon)</option>
                                                        <option value="04:00 PM - 06:00 PM">04:00 PM - 06:00 PM (Evening)</option>
                                                        <option value="06:00 PM - 08:00 PM">06:00 PM - 08:00 PM (Night)</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Customer Contact Details -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-300 mb-1">Full Name *</label>
                                                    <input type="text" name="customer_name" required value="{{ auth()->user()?->name ?? '' }}" placeholder="Your name"
                                                           class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-300 mb-1">Phone Number *</label>
                                                    <input type="tel" name="customer_phone" required value="{{ auth()->user()?->phone ?? '' }}" placeholder="10-digit mobile"
                                                           class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-300 mb-1">Email (Optional)</label>
                                                <input type="email" name="customer_email" value="{{ auth()->user()?->email ?? '' }}" placeholder="For appointment updates"
                                                       class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-300 mb-1">Doorstep Service Address *</label>
                                                <textarea name="service_address" rows="2" required placeholder="House/Flat No., Landmark, Area, City, Pincode"
                                                          class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-300 mb-1">Special Instructions / Issue Details (Optional)</label>
                                                <textarea name="notes" rows="2" placeholder="Describe specific issue or preference..."
                                                          class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/15 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                                            </div>

                                            <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                                                <div>
                                                    <div class="text-[10px] text-slate-400 uppercase">Total Service Charge</div>
                                                    <div class="text-base font-black text-indigo-400">₹{{ number_format($product->base_price / 100, 2) }}</div>
                                                </div>

                                                <button type="submit" 
                                                        class="px-6 py-2.5 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg active:scale-95">
                                                    Confirm Booking
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @elseif($product->isAuction())
                            <div class="space-y-4" x-data="{ showMessageModal: false }">
                                <!-- Live Auction Card -->
                                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-950">
                                    <div class="flex items-center justify-between pb-3 border-b border-amber-500/20">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-2.5 w-2.5 rounded-full {{ $product->isAuctionLive() ? 'bg-emerald-500 animate-ping' : 'bg-stone-400' }}"></span>
                                            <span class="text-xs font-bold uppercase tracking-wider {{ $product->isAuctionLive() ? 'text-emerald-700' : 'text-stone-600' }}">
                                                {{ $product->isAuctionLive() ? 'Live Auction' : ($product->isAuctionEnded() ? 'Auction Ended' : 'Upcoming Auction') }}
                                            </span>
                                        </div>
                                        @if($product->auction_ends_at)
                                            <div class="text-[11px] font-semibold text-amber-900">
                                                {{ $product->isAuctionEnded() ? 'Ended' : 'Ends in: ' . $product->auction_ends_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-3">
                                        <div class="bg-white/80 p-3 rounded-xl border border-amber-200">
                                            <span class="text-[10px] uppercase font-bold text-stone-500 block">Current High Bid</span>
                                            <span class="text-lg font-black text-amber-950 font-mono">{{ $product->formattedCurrentBid() }}</span>
                                            <span class="text-[10px] text-stone-400 block">{{ $product->bids_count }} bid(s) placed</span>
                                        </div>
                                        <div class="bg-white/80 p-3 rounded-xl border border-amber-200">
                                            <span class="text-[10px] uppercase font-bold text-stone-500 block">Min Next Bid</span>
                                            <span class="text-lg font-black text-emerald-700 font-mono">{{ $product->formattedMinNextBid() }}</span>
                                            <span class="text-[10px] text-stone-400 block">+₹{{ number_format(($product->bid_increment ?? 10000)/100) }} increment</span>
                                        </div>
                                    </div>

                                    @if($product->isAuctionLive())
                                        <div class="mt-4">
                                            <form action="{{ route('auctions.bid', $product) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                <div class="relative flex-1">
                                                    <span class="absolute left-3 top-2.5 text-stone-400 font-bold text-sm">₹</span>
                                                    <input type="number" step="1" min="{{ $product->minNextBid() / 100 }}" name="bid_amount" required
                                                           value="{{ $product->minNextBid() / 100 }}"
                                                           placeholder="Enter bid amount"
                                                           class="w-full pl-7 pr-3 py-2.5 rounded-full bg-white border border-amber-300 text-stone-900 font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                                </div>
                                                <button type="submit" 
                                                        class="px-6 py-2.5 rounded-full bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md active:scale-95 shrink-0 cursor-pointer">
                                                    Place Bid
                                                </button>
                                            </form>
                                            <p class="text-[10px] text-amber-800/80 mt-1.5 text-center">Anti-sniping protection: bids placed in final 5 mins auto-extend by 5 mins.</p>
                                        </div>
                                    @elseif($product->isAuctionEnded())
                                        <div class="mt-3 p-3 rounded-xl bg-stone-100 text-center text-xs font-semibold text-stone-700">
                                            Bidding is closed for this item.
                                            @if($product->winningBidder)
                                                Winning bidder: <span class="font-bold">{{ $product->winningBidder->name }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Recent Bids Accordion / List -->
                                    @if($product->bids && $product->bids->count() > 0)
                                        <div class="mt-3 pt-3 border-t border-amber-500/20">
                                            <div class="text-[11px] font-bold text-amber-900 mb-1.5 flex items-center justify-between">
                                                <span>Bid History</span>
                                                <span class="text-[10px] text-stone-500">Latest 5 bids</span>
                                            </div>
                                            <div class="space-y-1">
                                                @foreach($product->bids->take(5) as $b)
                                                    <div class="flex items-center justify-between text-[11px] py-1 px-2 rounded-lg bg-white/60">
                                                        <span class="font-medium text-stone-700">{{ substr($b->user->first_name ?? 'Bidder', 0, 1) }}***{{ substr($b->user->first_name ?? 'Bidder', -1) }}</span>
                                                        <span class="font-mono font-bold {{ $b->status === 'winning' ? 'text-emerald-700' : 'text-stone-500' }}">{{ $b->formattedBidAmount() }}</span>
                                                        <span class="text-[9px] text-stone-400">{{ $b->created_at->diffForHumans() }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Direct Message Vendor CTA -->
                                <button type="button" @click="showMessageModal = true"
                                        class="w-full py-2.5 px-4 rounded-xl bg-white border border-stone-200 text-stone-700 text-xs font-semibold hover:bg-stone-50 flex items-center justify-center gap-2 transition cursor-pointer">
                                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Send Direct Message to Seller</span>
                                </button>

                                <!-- Direct Message Modal -->
                                <div x-show="showMessageModal" 
                                     x-transition.opacity
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                                     style="display: none;">
                                    <div @click.away="showMessageModal = false" class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-brand-text relative">
                                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                                            <h3 class="text-base font-bold text-gray-900">Message {{ $product->vendor->shop_name }}</h3>
                                            <button type="button" @click="showMessageModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">×</button>
                                        </div>
                                        <form action="{{ route('messages.store') }}" method="POST" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="vendor_id" value="{{ $product->vendor_id }}">
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Subject</label>
                                                <input type="text" name="subject" value="Inquiry: {{ $product->title }}" class="w-full rounded-xl border border-gray-300 py-2 px-3 text-xs text-gray-900 focus:ring-2 focus:ring-amber-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Message *</label>
                                                <textarea name="body" rows="4" required placeholder="Type your message to the seller..." class="w-full rounded-xl border border-gray-300 py-2 px-3 text-xs text-gray-900 focus:ring-2 focus:ring-amber-500"></textarea>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 pt-2">
                                                <button type="button" @click="showMessageModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100">Cancel</button>
                                                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold">Send Message</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($product->isSold())
                                <div class="p-4 rounded-2xl bg-stone-100 border border-stone-200 text-center">
                                    <span class="text-sm font-bold text-stone-600">This pre-owned listing has been marked as SOLD.</span>
                                </div>
                            @else
                                <div class="space-y-3" x-data="{ showOfferModal: false }">
                                    <!-- Offer Button & WhatsApp -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <button type="button" @click="showOfferModal = true"
                                                class="w-full py-3.5 px-5 rounded-full bg-zinc-900 hover:bg-black text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                            </svg>
                                            <span>Make an Offer</span>
                                        </button>

                                        <a href="https://wa.me/{{ $sellerPhone ?? '' }}?text={{ rawurlencode('Hello! I am contacting you regarding your used listing: ' . $product->title . ' on K71. Is it still available? What is your best offer?') }}" target="_blank"
                                           class="w-full py-3.5 px-5 rounded-full bg-zinc-800 hover:bg-zinc-900 text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            <span>Chat on WhatsApp</span>
                                        </a>
                                    </div>

                                    @if(!empty($sellerPhone))
                                        <a href="tel:{{ $sellerPhone }}" 
                                           class="w-full py-2.5 px-4 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold flex items-center justify-center gap-2 transition">
                                            <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <span>Call Seller ({{ $sellerPhone }})</span>
                                        </a>
                                    @endif

                                    <p class="text-[11px] text-center text-stone-500">Direct peer-to-peer negotiation per Decision D9. No cart or middlemen fees.</p>

                                    <!-- Make an Offer Modal -->
                                    <div x-show="showOfferModal" 
                                         x-transition.opacity
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                                         style="display: none;">
                                        <div @click.away="showOfferModal = false" class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-brand-text relative">
                                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xl">🤝</span>
                                                    <h3 class="text-base font-bold text-gray-900">Make an Offer</h3>
                                                </div>
                                                <button type="button" @click="showOfferModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">×</button>
                                            </div>

                                            <form action="{{ route('offers.store', $product) }}" method="POST" class="space-y-4">
                                                @csrf
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Asking Price</label>
                                                    <div class="text-lg font-bold text-gray-900">₹{{ number_format($product->base_price / 100, 2) }}</div>
                                                </div>

                                                <div>
                                                    <label for="offer_amount" class="block text-xs font-bold text-gray-700 uppercase mb-1">Your Offer Price (₹)</label>
                                                    <input type="number" step="1" min="1" name="offer_amount" id="offer_amount" required 
                                                           value="{{ round($product->base_price / 100 * 0.9) }}"
                                                           placeholder="Enter your offer amount in ₹" 
                                                           class="w-full rounded-xl border border-gray-300 py-2.5 px-3 text-sm font-mono font-bold text-gray-900 focus:ring-2 focus:ring-cyan-500">
                                                </div>

                                                <div>
                                                    <label for="buyer_name" class="block text-xs font-bold text-gray-700 uppercase mb-1">Your Name</label>
                                                    <input type="text" name="buyer_name" id="buyer_name" required value="{{ auth()->user()?->name ?? '' }}"
                                                           placeholder="Your Full Name" 
                                                           class="w-full rounded-xl border border-gray-300 py-2 px-3 text-sm focus:ring-2 focus:ring-cyan-500">
                                                </div>

                                                <div>
                                                    <label for="buyer_phone" class="block text-xs font-bold text-gray-700 uppercase mb-1">Phone Number (+91)</label>
                                                    <input type="tel" name="buyer_phone" id="buyer_phone" required value="{{ auth()->user()?->phone ?? '' }}"
                                                           placeholder="10-digit mobile number" 
                                                           class="w-full rounded-xl border border-gray-300 py-2 px-3 text-sm focus:ring-2 focus:ring-cyan-500">
                                                </div>

                                                <div>
                                                    <label for="message" class="block text-xs font-bold text-gray-700 uppercase mb-1">Note to Seller (Optional)</label>
                                                    <textarea name="message" id="message" rows="2" 
                                                              placeholder="e.g. Can pick up today evening, cash ready." 
                                                              class="w-full rounded-xl border border-gray-300 py-2 px-3 text-xs focus:ring-2 focus:ring-cyan-500"></textarea>
                                                </div>

                                                <div class="pt-2 flex items-center justify-end gap-2">
                                                    <button type="button" @click="showOfferModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md transition active:scale-95">Send Offer to Seller</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Feedback Message Alert -->
                        <div x-show="message" 
                             x-transition 
                             class="mt-3 p-3 rounded-xl text-xs font-semibold flex items-center gap-2"
                             :class="messageType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            <span x-text="message"></span>
                        </div>

                        <!-- WhatsApp Designer Direct Inquiry Button -->
                        @if(!empty($sellerPhone))
                            <div class="mt-3">
                                <a href="https://wa.me/{{ $sellerPhone }}?text={{ $waProductMsg }}" target="_blank"
                                   class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-full border border-emerald-300/80 bg-emerald-50/70 hover:bg-emerald-100/90 text-emerald-900 text-xs font-bold transition-all shadow-2xs group">
                                    <svg class="w-4 h-4 fill-emerald-600 group-hover:scale-110 transition-transform shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>Chat with Seller on WhatsApp</span>
                                </a>
                            </div>
                        @endif
                    </form>

                    <!-- Pincode Delivery Estimator Widget (Connected to Vendor Settings) -->
                    <div x-data="{
                        pincode: '',
                        checked: false,
                        checking: false,
                        status: '', // 'available', 'not_serviceable', 'invalid'
                        estimateDate: '',
                        processingDays: {{ $processingDays }},
                        isCodAvailable: {{ $isCodAvailable ? 'true' : 'false' }},
                        isReturnable: {{ $isReturnable ? 'true' : 'false' }},
                        returnDays: {{ $returnDays }},
                        serviceablePincodes: {{ json_encode($serviceablePincodes) }},
                        checkDelivery() {
                            if (!/^[1-9][0-9]{5}$/.test(this.pincode)) {
                                this.status = 'invalid';
                                this.checked = true;
                                return;
                            }
                            this.checking = true;
                            setTimeout(() => {
                                if (this.serviceablePincodes && this.serviceablePincodes.length > 0) {
                                    if (!this.serviceablePincodes.includes(this.pincode)) {
                                        this.status = 'not_serviceable';
                                        this.checking = false;
                                        this.checked = true;
                                        return;
                                    }
                                }
                                const transitDays = 3;
                                const totalDays = this.processingDays + transitDays;
                                const d = new Date();
                                d.setDate(d.getDate() + totalDays);
                                const opts = { weekday: 'short', month: 'short', day: 'numeric' };
                                this.estimateDate = d.toLocaleDateString('en-US', opts);
                                this.status = 'available';
                                this.checking = false;
                                this.checked = true;
                            }, 300);
                        }
                    }" class="mt-6 p-3.5 rounded-2xl bg-stone-50/80 border border-stone-200/70">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-brand-text uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-brand-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Delivery & COD Checker
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   maxlength="6" 
                                   x-model="pincode" 
                                   placeholder="Enter 6-digit Pincode" 
                                   @keydown.enter.prevent="checkDelivery()"
                                   class="flex-1 bg-white border border-stone-300 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-brand-sage focus:border-brand-sage outline-none font-medium">
                            <button type="button" 
                                    @click="checkDelivery()" 
                                    :disabled="pincode.length !== 6 || checking" 
                                    class="px-4 py-2 rounded-xl bg-brand-text text-white text-xs font-semibold hover:bg-black transition-colors disabled:opacity-40">
                                <span x-show="!checking">Check</span>
                                <span x-show="checking">...</span>
                            </button>
                        </div>

                        <!-- Dynamic Feedback based on Vendor's Dashboard Settings -->
                        <div x-show="checked" x-transition class="mt-2.5 pt-2.5 border-t border-stone-200/60 space-y-1 text-xs text-stone-600">
                            <template x-if="status === 'invalid'">
                                <div class="flex items-center gap-1.5 text-rose-700 font-semibold text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Please enter a valid 6-digit Indian PIN code.</span>
                                </div>
                            </template>

                            <template x-if="status === 'not_serviceable'">
                                <div class="flex items-center gap-1.5 text-amber-800 font-semibold text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span>Delivery not currently serviceable to pincode <strong x-text="pincode"></strong> by this boutique.</span>
                                </div>
                            </template>

                            <template x-if="status === 'available'">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-emerald-800 font-semibold text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Express Delivery by <strong x-text="estimateDate"></strong> (Dispatches in {{ $processingDays }} business days)</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <template x-if="isCodAvailable">
                                            <span class="flex items-center gap-1.5 text-emerald-800">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span>Cash on Delivery (COD) Available</span>
                                            </span>
                                        </template>
                                        <template x-if="!isCodAvailable">
                                            <span class="flex items-center gap-1.5 text-stone-600">
                                                <svg class="w-3.5 h-3.5 text-stone-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>Prepaid Orders Only (UPI & Cards) • COD disabled by boutique</span>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-stone-600">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>{{ $isReturnable && $returnDays > 0 ? $returnDays . ' Days Easy Doorstep Return Guarantee' : 'Final Sale — Non-Returnable Item' }}</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Structured Product Accordions -->
                    <div class="mt-6 border-t border-stone-200/80 divide-y divide-stone-200/70" x-data="{ activeTab: 'fabric' }">
                        
                        <!-- Accordion 1: Fabric & Craft Story -->
                        <div class="py-3.5">
                            <button type="button" 
                                    @click="activeTab = (activeTab === 'fabric' ? '' : 'fabric')"
                                    class="w-full flex items-center justify-between text-left group">
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-text flex items-center gap-2">
                                    <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    <span>Material &amp; Specifications</span>
                                </span>
                                <svg class="w-4 h-4 text-stone-400 transform transition-transform duration-200" :class="activeTab === 'fabric' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="activeTab === 'fabric'" x-collapse>
                                <div class="pt-3 text-xs leading-relaxed text-stone-600 space-y-3">
                                    <div class="prose prose-sm text-stone-600">
                                        {!! nl2br(e($product->description)) !!}
                                    </div>
                                    
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-2 border-t border-stone-100 text-[11px]">
                                        @if(!empty($product->material))
                                            <div class="bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                <span class="text-stone-400 block uppercase text-[9px] font-bold">Material</span>
                                                <span class="font-semibold text-brand-text">{{ $product->material }}</span>
                                            </div>
                                        @endif
                                        @if(!empty($product->care_instructions))
                                            <div class="bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                <span class="text-stone-400 block uppercase text-[9px] font-bold">Care Instructions</span>
                                                <span class="font-semibold text-brand-text">{{ $product->care_instructions }}</span>
                                            </div>
                                        @endif
                                        @if($product->weight_grams)
                                            <div class="bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                <span class="text-stone-400 block uppercase text-[9px] font-bold">Package Weight</span>
                                                <span class="font-semibold text-brand-text">{{ $product->weight_grams }}g (approx. / pc)</span>
                                            </div>
                                        @endif
                                        <div class="bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                <span class="text-stone-400 block uppercase text-[9px] font-bold">Country of Origin</span>
                                                <span class="font-semibold text-brand-text">{{ $product->country_of_origin ?? 'India' }}</span>
                                        </div>
                                        <div class="bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                <span class="text-stone-400 block uppercase text-[9px] font-bold">SKU Code</span>
                                                <span class="font-semibold text-brand-text font-mono">{{ $product->sku ?? 'K71-ITEM' }}</span>
                                        </div>
                                        @if($product->custom_fields && is_array($product->custom_fields))
                                            @foreach($product->custom_fields as $cfKey => $cfVal)
                                                @if(!empty($cfVal))
                                                    <div class="bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                        <span class="text-stone-400 block uppercase text-[9px] font-bold">{{ ucwords(str_replace('_', ' ', $cfKey)) }}</span>
                                                        <span class="font-semibold text-brand-text">{{ is_array($cfVal) ? implode(', ', $cfVal) : $cfVal }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 2: Size & Measurements Guide -->
                        @if($verticalType === 'fashion_matrix' || $hasCustomChart)
                        <div class="py-3.5">
                            <button type="button" 
                                     @click="activeTab = (activeTab === 'size' ? '' : 'size')"
                                     class="w-full flex items-center justify-between text-left group">
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-text flex items-center gap-2">
                                    <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2zM7 7v4M11 7v2M15 7v4M19 7v2"/>
                                    </svg>
                                    <span>Fit &amp; Measurements</span>
                                </span>
                                <svg class="w-4 h-4 text-stone-400 transform transition-transform duration-200" :class="activeTab === 'size' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="activeTab === 'size'" x-collapse>
                                <div class="pt-3 text-xs text-stone-600 space-y-2.5">
                                    <p class="leading-relaxed">
                                        Tailored to provide a comfortable fit and silhouette. View our detailed size chart for exact measurements.
                                    </p>
                                    <button type="button" @click="showSizeGuide = true" class="w-full py-2 px-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-brand-text font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
                                        <span>Open Size Guide</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Accordion 3: Shipping, COD & Easy Returns -->
                        <div class="py-3.5">
                            <button type="button" 
                                     @click="activeTab = (activeTab === 'shipping' ? '' : 'shipping')"
                                     class="w-full flex items-center justify-between text-left group">
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-text flex items-center gap-2">
                                    <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-8 0a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                    <span>Shipping, COD &amp; Returns Policy</span>
                                </span>
                                <svg class="w-4 h-4 text-stone-400 transform transition-transform duration-200" :class="activeTab === 'shipping' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="activeTab === 'shipping'" x-collapse>
                                <div class="pt-3 text-xs leading-relaxed text-stone-600 space-y-2.5">
                                    <div class="flex items-start gap-2">
                                        <span class="font-bold text-brand-text shrink-0">Dispatch:</span>
                                        <span>Ships within <strong>{{ $processingDays }} business day(s)</strong> directly from {{ $product->vendor->pickup_address['city'] ?? $product->vendor->shop_name }} store.</span>
                                    </div>
                                    @if($product->weight_grams)
                                        <div class="flex items-start gap-2">
                                            <span class="font-bold text-brand-text shrink-0">Weight:</span>
                                            <span>Approx. <strong>{{ $product->weight_grams >= 1000 ? number_format($product->weight_grams / 1000, 2) . ' kg' : $product->weight_grams . ' grams' }}</strong> per piece.</span>
                                        </div>
                                    @endif
                                    <div class="flex items-start gap-2">
                                        <span class="font-bold text-brand-text shrink-0">Returns:</span>
                                        @if($isReturnable && $returnDays > 0)
                                            <span><strong>{{ $returnDays }} Days Easy Return &amp; Exchange</strong> from delivery date.</span>
                                        @else
                                            <span><strong>Final Sale:</strong> Non-returnable item.</span>
                                        @endif
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="font-bold text-brand-text shrink-0">Payment:</span>
                                        @if($isCodAvailable)
                                            <span><strong>Cash on Delivery (COD) Available</strong> at checkout, along with UPI, Cards, and Net Banking.</span>
                                        @else
                                            <span><strong>Prepaid Orders Only:</strong> UPI, QR Code &amp; Cards accepted.</span>
                                        @endif
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="font-bold text-brand-text shrink-0">Authenticity:</span>
                                        <span>100% Genuine product with verified merchant guarantee.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 4: About Boutique -->
                        <div class="py-3.5">
                            <button type="button" 
                                     @click="activeTab = (activeTab === 'boutique' ? '' : 'boutique')"
                                     class="w-full flex items-center justify-between text-left group">
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-text flex items-center gap-2">
                                    <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>About {{ $product->vendor->shop_name }}</span>
                                </span>
                                <svg class="w-4 h-4 text-stone-400 transform transition-transform duration-200" :class="activeTab === 'boutique' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="activeTab === 'boutique'" x-collapse>
                                <div class="pt-3 text-xs leading-relaxed text-stone-600 space-y-2.5">
                                    <p>{{ $product->vendor->description ?? "Welcome to {$product->vendor->shop_name}, verified merchant on K71 Marketplace." }}</p>
                                    <a href="{{ route('catalog.index', ['store' => $product->vendor->slug]) }}" class="inline-flex items-center gap-1.5 font-bold text-indigo-600 hover:underline">
                                        <span>View all products from {{ $product->vendor->shop_name }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Sticky Mobile Bottom Bar (Slide in when scrolled past primary CTA) -->
        <div x-show="isScrolledPastCTA"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform translate-y-full opacity-0"
             x-transition:enter-end="transform translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="transform translate-y-0 opacity-100"
             x-transition:leave-end="transform translate-y-full opacity-0"
             class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-stone-200/90 px-4 py-2.5 shadow-[0_-8px_25px_rgba(0,0,0,0.12)]">
            <div class="flex items-center justify-between gap-3 max-w-lg mx-auto">
                <div class="flex items-center gap-2.5 min-w-0">
                    @if($product->images->count() > 0)
                        <img src="{{ $product->images->first()->url }}" 
                             alt="{{ $product->title }}" 
                             class="w-10 h-10 rounded-lg object-cover bg-stone-100 shrink-0 border border-stone-200">
                    @endif
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-brand-text truncate">{{ $product->title }}</p>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-brand-text">₹<span x-text="currentPrice">{{ number_format($product->base_price / 100, 2) }}</span></span>
                            @if($product->compare_at_price > $product->base_price)
                                <span class="text-[10px] text-gray-400 line-through">₹{{ number_format($product->compare_at_price / 100, 2) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <button type="button" 
                        @click="submitAddToCart()" 
                        :disabled="adding || selectedStock === 0"
                        class="px-5 py-2.5 rounded-full bg-brand-text text-white text-xs font-bold tracking-wide hover:bg-black transition-all shrink-0 flex items-center gap-1.5 shadow-md active:scale-95 disabled:opacity-75">
                    <svg x-show="!adding" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span x-text="adding ? 'Adding...' : (selectedStock === 0 ? 'Sold Out' : 'Add to Bag')"></span>
                </button>
            </div>
        </div>

        <!-- Fullscreen High-Resolution Lightbox Modal -->
        <div x-show="lightboxOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4 sm:p-6"
             @keydown.escape.window="lightboxOpen = false">
            <div class="relative max-w-4xl max-h-[90vh] w-full flex flex-col items-center justify-center" @click.away="lightboxOpen = false">
                <button type="button" 
                        @click="lightboxOpen = false" 
                        class="absolute -top-10 right-0 text-white/80 hover:text-white text-2xl font-bold p-2 focus:outline-none">
                    ✕
                </button>
                <img :src="images[activeImgIndex]" alt="{{ $product->title }} Full View" class="max-h-[80vh] max-w-full rounded-xl object-contain shadow-2xl">
                
                @if($product->images->count() > 1)
                    <div class="flex items-center gap-4 mt-4">
                        <button type="button" @click="prevImage()" class="p-2 rounded-full bg-white/20 hover:bg-white/40 text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <span class="text-white text-xs font-bold tracking-wider" x-text="(activeImgIndex + 1) + ' / ' + images.length"></span>
                        <button type="button" @click="nextImage()" class="p-2 rounded-full bg-white/20 hover:bg-white/40 text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Garment Size Guide Modal -->
        <div x-show="showSizeGuide" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             @keydown.escape.window="showSizeGuide = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-stone-200" @click.away="showSizeGuide = false">
                
                @if($hasCustomChart)
                    <!-- Custom Vendor Size Guide -->
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                        <div>
                            <h3 class="text-xl font-serif font-bold text-brand-text">{{ $sc['title'] ?? 'Garment Fit & Size Guide' }}</h3>
                            <p class="text-xs text-stone-500 mt-0.5">Designer tailored measurements for this piece</p>
                        </div>
                        <button type="button" @click="showSizeGuide = false" class="text-stone-400 hover:text-stone-700 text-lg font-bold">
                            ✕
                        </button>
                    </div>

                    <!-- Dynamic Size Table -->
                    <div class="overflow-x-auto rounded-2xl border border-stone-100 mb-4">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-stone-50 border-b border-stone-100 font-bold text-stone-700">
                                <tr>
                                    @foreach($sc['columns'] as $col)
                                        <th class="p-3 whitespace-nowrap">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 font-medium text-stone-600">
                                @foreach($sc['rows'] as $row)
                                    <tr class="hover:bg-stone-50/50">
                                        @foreach($sc['columns'] as $idx => $col)
                                            <td class="p-3 whitespace-nowrap {{ $idx === 0 ? 'font-bold text-brand-text' : '' }}">
                                                {{ $row[$col] ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($sc['tip']))
                        <p class="text-[11px] text-stone-600 leading-relaxed mb-4 bg-stone-50 p-3 rounded-xl border border-stone-100">
                            💡 <strong>Fit Tip:</strong> {{ $sc['tip'] }}
                        </p>
                    @endif

                @else
                    <!-- Standard Fallback Sizing Table (with Unit in/cm converter) -->
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                        <div>
                            <h3 class="text-xl font-serif font-bold text-brand-text">Garment Fit & Size Guide</h3>
                            <p class="text-xs text-stone-500 mt-0.5">Standard Indian ethnic & apparel measurements</p>
                        </div>
                        <button type="button" @click="showSizeGuide = false" class="text-stone-400 hover:text-stone-700 text-lg font-bold">
                            ✕
                        </button>
                    </div>

                    <!-- Unit Toggle -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Unit:</span>
                        <div class="flex items-center gap-1 bg-stone-100 p-1 rounded-xl text-xs font-bold">
                            <button type="button" @click="sizeUnit = 'in'" :class="sizeUnit === 'in' ? 'bg-white shadow-2xs text-brand-text' : 'text-stone-500'" class="px-3 py-1 rounded-lg transition-all">Inches (in)</button>
                            <button type="button" @click="sizeUnit = 'cm'" :class="sizeUnit === 'cm' ? 'bg-white shadow-2xs text-brand-text' : 'text-stone-500'" class="px-3 py-1 rounded-lg transition-all">Centimeters (cm)</button>
                        </div>
                    </div>

                    <!-- Size Table -->
                    <div class="overflow-x-auto rounded-2xl border border-stone-100 mb-4">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-stone-50 border-b border-stone-100 font-bold text-stone-700">
                                <tr>
                                    <th class="p-3">Size</th>
                                    <th class="p-3">Bust / Chest</th>
                                    <th class="p-3">Waist</th>
                                    <th class="p-3">Hip</th>
                                    <th class="p-3">Length</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 font-medium text-stone-600">
                                <tr>
                                    <td class="p-3 font-bold text-brand-text">S (Small)</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '36 in' : '91 cm'">36 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '32 in' : '81 cm'">32 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '38 in' : '96 cm'">38 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '40 in' : '101 cm'">40 in</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-brand-text">M (Medium)</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '38 in' : '96 cm'">38 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '34 in' : '86 cm'">34 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '40 in' : '101 cm'">40 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '41 in' : '104 cm'">41 in</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-brand-text">L (Large)</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '40 in' : '101 cm'">40 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '36 in' : '91 cm'">36 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '42 in' : '106 cm'">42 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '42 in' : '106 cm'">42 in</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-brand-text">XL</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '42 in' : '106 cm'">42 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '38 in' : '96 cm'">38 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '44 in' : '111 cm'">44 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '42 in' : '106 cm'">42 in</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-brand-text">XXL</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '44 in' : '111 cm'">44 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '40 in' : '101 cm'">40 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '46 in' : '116 cm'">46 in</td>
                                    <td class="p-3" x-text="sizeUnit === 'in' ? '43 in' : '109 cm'">43 in</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[11px] text-stone-500 leading-relaxed mb-4 bg-stone-50 p-3 rounded-xl border border-stone-100">
                        💡 <strong>Saree & Dupatta Fit Note:</strong> Sarees come in standard 5.5 meters length with an additional 0.8 meter unstitched blouse piece. For kurtis, shirts, and stitched suits, select by bust/chest circumference.
                    </p>
                @endif

                <button type="button" @click="showSizeGuide = false" 
                        class="w-full py-2.5 rounded-full bg-brand-text text-white font-bold text-xs hover:bg-black transition-colors">
                    Close Guide
                </button>
            </div>
        </div>

        <!-- Immersive Preset: Floating Quick-Checkout Dock -->
        @if($productPreset === 'immersive')
            <div x-show="isScrolledPastCTA" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:w-96 z-40 bg-white/95 backdrop-blur-xl border border-stone-200/90 p-3.5 rounded-2xl shadow-2xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-900 truncate">{{ $product->title }}</p>
                    <p class="text-xs font-bold text-emerald-800">₹<span x-text="currentPrice">{{ number_format($product->base_price / 100, 2) }}</span></p>
                </div>
                <button type="button" @click="addToCart()" :disabled="adding || isCurrentVariantOutOfStock" class="px-5 py-2.5 rounded-xl bg-brand-sage text-white text-xs font-bold hover:bg-brand-sage/90 transition-all shadow-md active:scale-95 shrink-0 flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                    <span x-show="!adding">Quick Add</span>
                    <span x-show="adding">Adding...</span>
                </button>
            </div>
        @endif

    </div>

    <!-- Reviews, Ratings & Vendor Response Section -->
    <section class="mt-12 bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs" x-data="{ showReportModal: false, showReviewForm: false }">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-6 border-b border-stone-100 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <h2 class="text-xl sm:text-2xl font-sans font-bold text-brand-text">Customer Reviews &amp; Ratings</h2>
                </div>
                <p class="text-xs text-stone-500 mt-1">Verified customer feedback and store responses.</p>
            </div>
            <div class="flex items-center gap-2">
                @auth
                    <button type="button" @click="showReviewForm = !showReviewForm" class="px-4 py-2 rounded-full bg-brand-text hover:bg-black text-white text-xs font-bold transition">
                        Write a Review
                    </button>
                @endauth
                <button type="button" @click="showReportModal = true" class="px-3 py-2 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                    <span>Report Item</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Write Review Drawer / Form -->
        <div x-show="showReviewForm" class="mt-6 p-5 rounded-2xl bg-[#FAF8F5] border border-stone-200/60" style="display: none;">
            <h3 class="text-sm font-bold text-brand-text mb-3">Share Your Experience with this Item</h3>
            <form action="{{ route('reviews.store', $product) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Rating *</label>
                    <select name="rating" required class="w-full sm:w-48 px-3 py-2 rounded-xl border border-stone-300 text-xs font-bold text-stone-900 bg-white">
                        <option value="5">5 Stars (Exceptional)</option>
                        <option value="4">4 Stars (Very Good)</option>
                        <option value="3">3 Stars (Average)</option>
                        <option value="2">2 Stars (Below Expectations)</option>
                        <option value="1">1 Star (Disappointing)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Headline (Optional)</label>
                    <input type="text" name="title" placeholder="Summary of your thoughts..." class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs text-stone-900 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Detailed Review *</label>
                    <textarea name="body" rows="3" required placeholder="Describe fabric quality, sizing accuracy, delivery experience..." class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs text-stone-900 bg-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showReviewForm = false" class="px-4 py-2 rounded-xl text-xs text-stone-500 hover:bg-stone-200/60">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-brand-text hover:bg-black text-white text-xs font-bold">Submit Review</button>
                </div>
            </form>
        </div>

        <!-- Reviews List -->
        <div class="mt-6 space-y-4">
            @if($product->reviews && $product->reviews->count() > 0)
                @foreach($product->reviews as $rev)
                    <div class="p-4 sm:p-5 rounded-2xl bg-stone-50/60 border border-stone-200/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-brand-text">{{ $rev->user->name ?? 'Verified Customer' }}</span>
                                    @if($rev->is_verified_purchase)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200">
                                            ✓ Verified Buyer
                                        </span>
                                    @endif
                                    <span class="text-amber-500 text-xs font-bold">{{ str_repeat('★', $rev->rating) }}{{ str_repeat('☆', 5 - $rev->rating) }}</span>
                                </div>
                                @if($rev->title)
                                    <h4 class="text-xs font-bold text-stone-900 mt-1">{{ $rev->title }}</h4>
                                @endif
                                <p class="text-xs text-stone-600 mt-1 leading-relaxed">{{ $rev->body }}</p>
                            </div>
                            <span class="text-[10px] text-stone-400 shrink-0 font-mono">{{ $rev->created_at->format('d M Y') }}</span>
                        </div>

                        <!-- Vendor Response Box (if replied) -->
                        @if($rev->hasVendorReply())
                            <div class="mt-3.5 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs">
                                <div class="flex items-center gap-1.5 font-bold text-amber-950 mb-1">
                                    <svg class="w-3.5 h-3.5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Response from {{ $product->vendor->shop_name }}:</span>
                                    <span class="text-[10px] text-amber-800/60 font-mono font-normal">({{ $rev->vendor_replied_at?->diffForHumans() }})</span>
                                </div>
                                <p class="text-amber-900 text-xs leading-relaxed">{{ $rev->vendor_reply }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="p-8 text-center bg-stone-50/40 rounded-2xl border border-stone-100">
                    <p class="text-xs text-stone-500">No reviews yet for this design. Be the first to share your thoughts!</p>
                </div>
            @endif
        </div>

        <!-- Report Modal -->
        <div x-show="showReportModal" @click.away="showReportModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" style="display: none;">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full text-left shadow-2xl border border-stone-200">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
                    <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                        <span>🚩</span>
                        <span>Report Listing</span>
                    </h3>
                    <button type="button" @click="showReportModal = false" class="text-stone-400 hover:text-stone-600 font-bold text-xl">×</button>
                </div>
                <form action="{{ route('reports.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="reportable_type" value="product">
                    <input type="hidden" name="reportable_id" value="{{ $product->id }}">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Reason for Report *</label>
                        <select name="reason" required class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs text-stone-900 focus:ring-2 focus:ring-rose-500">
                            <option value="fake_item">Counterfeit or Fake Product</option>
                            <option value="inappropriate">Inappropriate or Misleading Content</option>
                            <option value="spam">Spam / Duplicate Listing</option>
                            <option value="copyright">Intellectual Property Infringement</option>
                            <option value="other">Other Violation</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Details (Optional)</label>
                        <textarea name="details" rows="3" placeholder="Provide additional details or context for moderation..." class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs text-stone-900 focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
                        <button type="button" @click="showReportModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-stone-600 hover:bg-stone-100">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- More from this Boutique Showcase -->
    @if($vendorProducts->count() > 0)
    <section class="mt-12 mb-16">
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-stone-200/80">
            <div>
                <span class="text-[10px] uppercase font-bold tracking-widest text-brand-sage block leading-none">Curated Collection</span>
                <h2 class="text-xl sm:text-2xl font-serif font-bold text-brand-text mt-1">More from {{ $product->vendor->shop_name }}</h2>
            </div>
            <a href="{{ $product->vendor->storefront_url }}" class="text-xs font-bold text-brand-text hover:text-brand-sage flex items-center gap-1 transition-colors">
                <span>View All Designs</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($vendorProducts as $vp)
                <x-product-card :product="$vp" />
            @endforeach
        </div>
    </section>
    @endif
</x-layouts.public>
