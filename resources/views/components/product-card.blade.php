@props(['product'])

@php
    // Extract unique colors from variants (assuming variant name format "Color / Size" or just "Color")
    $uniqueColors = collect();
    foreach($product->variants as $variant) {
        if ($variant->name && $variant->name !== 'Default') {
            $parts = explode('/', $variant->name);
            $colorName = trim($parts[0]);
            // Convert to valid CSS color (remove spaces)
            $cssColor = strtolower(str_replace(' ', '', $colorName));
            if(!$uniqueColors->contains('name', $colorName)) {
                $uniqueColors->push(['name' => $colorName, 'css' => $cssColor]);
            }
        }
    }
    
    // Calculate Discount Percentage
    $discount = 0;
    if($product->compare_at_price > $product->base_price) {
        $discount = round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100);
    }

    // Accurate stock determination across all variants
    $variantStockSum = $product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->total_stock;
    $isOutOfStock = $variantStockSum <= 0;
    $hasMultipleVariants = $product->variants->count() > 1 || ($product->variants->count() === 1 && $product->variants->first()?->sku !== 'Default');
    $firstInStockVariant = $product->variants->firstWhere('stock', '>', 0);
    $userWishlistIds = \App\Models\Wishlist::getUserWishlistIds();
    $isWishlisted = in_array($product->id, $userWishlistIds);
@endphp

<div x-data="{ 
    adding: false, 
    added: false,
    isWishlisted: {{ $isWishlisted ? 'true' : 'false' }},
    init() {
        window.addEventListener('wishlist-state-changed', (e) => {
            if (e.detail && e.detail.productId === {{ $product->id }}) {
                this.isWishlisted = !!e.detail.isWishlisted;
            }
        });
    },
    toggleWishlist(e) {
        e.preventDefault();
        e.stopPropagation();
        this.isWishlisted = !this.isWishlisted;
        window.toggleProductWishlist({{ $product->id }}, this.isWishlisted, () => {
            this.isWishlisted = !this.isWishlisted;
        });
    }
}" class="group flex flex-col h-full relative">
    
    <!-- Image Area with Bulletproof 3:4 Aspect Ratio -->
    <div class="relative w-full aspect-[3/4] bg-gray-100 dark:bg-zinc-800 rounded-2xl overflow-hidden mb-3" style="aspect-ratio: 3 / 4; width: 100%;">
        <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative z-0" aria-label="{{ $product->title }}">
            @if($product->images->count() > 0)
                <img src="{{ \App\Services\ImageService::url($product->images->first()->path, 400) }}" 
                     alt="{{ $product->title }}" 
                     loading="lazy"
                     decoding="async"
                     width="300"
                     height="400"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"
                     class="mix-blend-multiply dark:mix-blend-normal transition-transform duration-700 group-hover:scale-105 {{ $isOutOfStock ? 'opacity-60 grayscale-[40%]' : '' }}"
                     onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                @if($product->images->count() > 1)
                    <img src="{{ \App\Services\ImageService::url($product->images[1]->path, 400) }}" 
                         alt="{{ $product->title }} Alternate" 
                         loading="lazy"
                         decoding="async"
                         width="300"
                         height="400"
                         style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"
                         class="mix-blend-multiply dark:mix-blend-normal opacity-0 transition-opacity duration-700 group-hover:opacity-100 {{ $isOutOfStock ? 'opacity-60 grayscale-[40%]' : '' }}"
                         onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                @endif
            @else
                <img src="{{ asset('images/product-placeholder.svg') }}" 
                     alt="{{ $product->title }}" 
                     loading="lazy"
                     decoding="async"
                     width="300"
                     height="400"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"
                     class="opacity-90 dark:opacity-80 {{ $isOutOfStock ? 'opacity-60 grayscale-[40%]' : '' }}">
            @endif
        </a>
        
        <!-- Stock / Sale Badge (Top Left) -->
        @if($isOutOfStock)
            <div class="absolute top-2 left-2 px-2.5 py-1 bg-stone-900/90 text-white font-sans text-[9px] font-black tracking-widest uppercase rounded z-10 shadow-sm backdrop-blur-xs">
                Sold Out
            </div>
        @elseif($discount > 0)
            <div class="absolute top-2 left-2 px-2 py-0.5 bg-[#FDF9DC] text-black font-sans text-[9px] font-black tracking-widest uppercase rounded z-10 shadow-sm">
                Sale
            </div>
        @endif
        
        <!-- Wishlist Button (Top Right) -->
        <button type="button" 
                @click="toggleWishlist($event)"
                :class="isWishlisted ? 'text-rose-500 bg-white/95 shadow-md scale-105' : 'text-stone-700 bg-white/90 hover:text-rose-500 hover:bg-white shadow-sm'"
                class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full backdrop-blur-md flex items-center justify-center transition-all duration-200 active:scale-90 z-20 cursor-pointer"
                aria-label="Toggle Wishlist"
                :aria-label="isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist'"
                :title="isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist'">
            <svg class="w-4 h-4 transition-transform duration-200" 
                 :class="isWishlisted ? 'scale-110' : ''"
                 :fill="isWishlisted ? 'currentColor' : 'none'" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </button>
    </div>
    
    <!-- Details Area (No background, sits cleanly on page) -->
    <div class="flex flex-col flex-1 px-1">
        
        <!-- Verified Seller Badge -->
        @if($product->vendor)
            <div class="flex items-center gap-1 mb-1">
                <span class="text-[9.5px] tracking-wider text-zinc-500 dark:text-zinc-400 flex items-center gap-1 font-medium truncate"
                   title="Sold by {{ $product->vendor->shop_name }}">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 text-[7px] font-bold" title="Verified Seller">✓</span>
                    <span class="truncate">Sold by {{ $product->vendor->shop_name }}</span>
                </span>
            </div>
        @endif

        <!-- Title -->
        <h3 class="text-xs text-zinc-900 dark:text-zinc-100 leading-[1.35] mb-1 font-medium line-clamp-2 min-h-[2.7em]">
            <a href="{{ route('product.show', $product->slug) }}" class="hover:underline decoration-1 underline-offset-2">{{ ucfirst($product->title) }}</a>
        </h3>
        
        <!-- Price Row -->
        <div class="flex items-baseline gap-1.5 flex-wrap mb-1">
            <span class="text-sm font-semibold text-zinc-900 dark:text-white">₹{{ number_format($product->base_price / 100, 0) }}</span>
            @if($discount > 0)
                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 line-through">₹{{ number_format($product->compare_at_price / 100, 0) }}</span>
                <span class="text-[9.5px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-1 py-0.5 rounded">{{ $discount }}% OFF</span>
            @endif
        </div>

        <!-- Context-Aware Action CTA Row -->
        <div class="mt-auto pt-1 flex items-center justify-between">
            @if($isOutOfStock)
                <span class="text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                    Sold Out
                </span>
            @elseif($product->isEnquiry())
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="text-[11px] font-bold text-amber-700 dark:text-amber-400 hover:text-amber-900 transition-colors inline-flex items-center gap-1" 
                   title="Request Quotation / View Details">
                    <span>Request Quote</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif($product->isBooking())
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="text-[11px] font-bold text-indigo-700 dark:text-indigo-400 hover:text-indigo-900 transition-colors inline-flex items-center gap-1" 
                   title="Book Service">
                    <span>Book Service</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif(!$product->isPurchase())
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="text-[11px] font-semibold text-zinc-900 dark:text-zinc-200 hover:text-black dark:hover:text-white transition-colors underline decoration-1 underline-offset-2 inline-flex items-center gap-1" 
                   title="View Listing">
                    <span>View Listing</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif($hasMultipleVariants)
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="text-[11px] font-semibold text-zinc-900 dark:text-zinc-200 hover:text-black dark:hover:text-white transition-colors underline decoration-1 underline-offset-2 inline-flex items-center gap-1" 
                   title="Select options">
                    <span>Select Options</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @else
                <!-- Single Variant Quick Add (+) -->
                <button type="button" 
                    @click.prevent="
                        adding = true;
                        fetch('/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                product_id: {{ $product->id }},
                                variant_id: {{ $firstInStockVariant?->id ?? 'null' }}, 
                                quantity: 1 
                            })
                        }).then(r => r.json()).then(data => {
                            adding = false;
                            if(data.success) {
                                added = true;
                                setTimeout(() => { added = false }, 2000);
                                if (typeof window.showToast === 'function') {
                                    window.showToast('Added to bag');
                                }
                                window.dispatchEvent(new CustomEvent('cart-updated'));
                                window.dispatchEvent(new CustomEvent('open-mini-cart'));
                            } else {
                                if (typeof window.showToast === 'function') {
                                    window.showToast(data.message || 'Error adding to cart', false);
                                }
                            }
                        }).catch(e => {
                            adding = false;
                            if (typeof window.showToast === 'function') {
                                window.showToast('Error adding to cart. Please try again.', false);
                            }
                        });
                    "
                    class="w-7 h-7 rounded-full bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-950 flex items-center justify-center hover:bg-black dark:hover:bg-white transition-all shrink-0 shadow-xs cursor-pointer active:scale-95"
                    :class="added ? '!bg-emerald-600 !text-white' : ''"
                    :disabled="adding || added"
                    aria-label="Add {{ addslashes($product->title) }} to Cart"
                    title="Add to Cart"
                >
                    <svg x-show="!adding && !added" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <svg x-show="adding" class="animate-spin w-3 h-3 text-white dark:text-zinc-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg x-show="added" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            @endif
        </div>
        
        <!-- Dynamic Color Swatches -->
        @if($uniqueColors->count() > 0)
            <div class="flex items-center gap-1.5 mt-auto">
                @foreach($uniqueColors->take(4) as $index => $color)
                    <div class="relative flex items-center justify-center w-[14px] h-[14px] rounded-full {{ $index === 0 ? 'ring-1 ring-offset-1 ring-gray-900' : 'ring-1 ring-gray-300' }}" title="{{ $color['name'] }}">
                        <span class="w-[10px] h-[10px] rounded-full border border-gray-200" style="background-color: {{ $color['css'] }};"></span>
                    </div>
                @endforeach
                @if($uniqueColors->count() > 4)
                    <span class="text-[9px] text-gray-400 font-medium ml-1">+{{ $uniqueColors->count() - 4 }}</span>
                @endif
            </div>
        @endif
        
    </div>
</div>
