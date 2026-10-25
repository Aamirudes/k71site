@props(['product'])

@php
    $theme = $product->vertical_theme;

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
    $isService = ($product->vertical === 'services' || in_array($product->listing_type, ['gig', 'service', 'booking']));
    $isFood = ($product->vertical === 'food');
    $tracksPhysicalStock = ($product->vertical === 'ecommerce' || empty($product->vertical) || $product->listing_type === 'product');

    $variantStockSum = $product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->total_stock;
    $isOutOfStock = $tracksPhysicalStock && ($variantStockSum <= 0);
    $hasMultipleVariants = $product->variants->count() > 1 || ($product->variants->count() === 1 && $product->variants->first()?->sku !== 'Default' && $product->variants->first()?->name !== 'Default');
    $firstInStockVariant = $product->first_in_stock_variant;
    $userWishlistIds = \App\Models\Wishlist::getUserWishlistIds();
    $isWishlisted = in_array($product->id, $userWishlistIds);

    $cardUrl = route('product.show', $product->slug);
    if ($isService) {
        $cardUrl = route('services.show', $product->slug);
    } elseif ($product->vertical === 'used' || $product->listing_type === 'classified') {
        $cardUrl = route('used.show', $product->slug);
    } elseif ($isFood && $product->vendor) {
        $cardUrl = route('food.restaurant', $product->vendor->slug);
    }
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
}" class="group flex flex-col h-full relative bg-white dark:bg-zinc-900 rounded-3xl p-3 border {{ $theme['border_color'] }} {{ $theme['card_border_hover'] }} shadow-xs hover:shadow-xl transition-all duration-300">
    
    <!-- Image Area with Bulletproof 3:4 Aspect Ratio -->
    <div class="relative w-full aspect-[3/4] bg-stone-100 dark:bg-zinc-800 rounded-2xl overflow-hidden mb-3" style="aspect-ratio: 3 / 4; width: 100%;">
        <a href="{{ $cardUrl }}" class="block w-full h-full relative z-0" aria-label="{{ $product->title }}">
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
        
        <!-- Top Left: Liquid Bubble Frosted Glass Theme Badge & Stock Pill -->
        <div class="absolute top-2.5 left-2.5 z-10 flex flex-col items-start gap-1.5">
            <!-- Liquid Bubble Glass Pill Badge -->
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase {{ $theme['badge_bg'] }} transition-all group-hover:scale-102">
                <span class="w-1.5 h-1.5 rounded-full {{ $theme['dot_color'] ?? 'bg-slate-900 dark:bg-white' }} shadow-xs"></span>
                <span>{{ $theme['label'] }}</span>
            </span>

            @if($isOutOfStock)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold tracking-wider uppercase bg-black/80 text-white backdrop-blur-md border border-white/20 shadow-sm">
                    Sold Out
                </span>
            @elseif($discount > 0)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase bg-rose-500/90 text-white backdrop-blur-md border border-white/30 shadow-[0_2px_8px_rgba(244,63,94,0.3)]">
                    {{ $discount }}% OFF
                </span>
            @endif
        </div>
        
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
    
    <!-- Details Area -->
    <div class="flex flex-col flex-1 px-1">
        
        <!-- Category & Verified Seller Row -->
        <div class="flex items-center justify-between gap-1 mb-1">
            <span class="text-[9.5px] font-black uppercase tracking-wider {{ $theme['accent_text'] }} truncate">
                {{ $product->category?->name ?? $theme['label'] }}
            </span>
            @if($product->vendor)
                <span class="text-[9px] text-emerald-600 font-bold flex items-center gap-0.5 shrink-0" title="Verified Seller: {{ $product->vendor->shop_name }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="truncate max-w-[80px]">{{ $product->vendor->shop_name }}</span>
                </span>
            @endif
        </div>

        <!-- Title -->
        <h3 class="text-xs text-zinc-900 dark:text-zinc-100 leading-[1.35] mb-1.5 font-bold line-clamp-2 min-h-[2.7em]">
            <a href="{{ $cardUrl }}" class="hover:underline decoration-1 underline-offset-2">{{ ucfirst($product->title) }}</a>
        </h3>
        
        <!-- Price Row -->
        <div class="flex items-baseline gap-1.5 flex-wrap mb-2">
            <span class="text-sm font-black text-zinc-900 dark:text-white">
                @if($isService)
                    <span class="text-[10px] text-zinc-400 font-semibold block w-full">Starting at</span>
                @endif
                ₹{{ number_format($product->base_price / 100, 0) }}
            </span>
            @if($discount > 0 && !$isService)
                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 line-through">₹{{ number_format($product->compare_at_price / 100, 0) }}</span>
            @endif
        </div>

        <!-- Dynamic Color Swatches -->
        @if($uniqueColors->count() > 0 && !$isService)
            <div class="flex items-center gap-1.5 mb-2">
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

        <!-- Context-Aware Themed Action CTA Row -->
        <div class="mt-auto pt-2 border-t border-stone-100 dark:border-zinc-800/80 flex items-center justify-between">
            @if($isOutOfStock)
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">
                    Sold Out
                </span>
            @elseif($isService)
                <a href="{{ $cardUrl }}" 
                   class="text-[11px] font-black text-purple-700 hover:text-purple-900 hover:underline inline-flex items-center gap-1" 
                   title="View Service Packages">
                    <span>View Packages</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif($product->isEnquiry())
                <a href="{{ $cardUrl }}" 
                   class="text-[11px] font-black {{ $theme['accent_text'] }} hover:underline inline-flex items-center gap-1" 
                   title="Request Quotation / View Details">
                    <span>Request Quote</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif($product->isBooking())
                <a href="{{ $cardUrl }}" 
                   class="text-[11px] font-black {{ $theme['accent_text'] }} hover:underline inline-flex items-center gap-1" 
                   title="Book Service">
                    <span>Book Service</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif(!$product->isPurchase())
                <a href="{{ $cardUrl }}" 
                   class="text-[11px] font-black {{ $theme['accent_text'] }} hover:underline inline-flex items-center gap-1" 
                   title="View Listing">
                    <span>View Listing</span>
                    <span class="text-[12px] leading-none">&rarr;</span>
                </a>
            @elseif($hasMultipleVariants)
                <a href="{{ $cardUrl }}" 
                   class="px-3 py-1.5 rounded-full {{ $theme['btn_pill'] }} text-[11px] font-black transition-all inline-flex items-center gap-1" 
                   title="Select options">
                    <span>Options ›</span>
                </a>
            @else
                <!-- Themed Universal Quick Add Button -->
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
                                    window.showToast('{{ addslashes($product->title) }} added to bag! 🛍️');
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
                    class="w-full py-2 px-3 rounded-xl {{ $theme['btn_bg'] }} text-xs font-black flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer active:scale-95"
                    :class="added ? '!bg-emerald-600 !text-white' : ''"
                    :disabled="adding || added"
                    aria-label="Add {{ addslashes($product->title) }} to Cart"
                    title="Add to Cart"
                >
                    <span x-show="!adding && !added" class="flex items-center gap-1">
                        <span>ADD</span>
                        <span class="text-sm font-bold leading-none">+</span>
                    </span>
                    <span x-show="adding" class="flex items-center gap-1 text-white">
                        <svg class="animate-spin w-3 h-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>ADDING...</span>
                    </span>
                    <span x-show="added" class="flex items-center gap-1 text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>ADDED</span>
                    </span>
                </button>
            @endif
        </div>
        
    </div>
</div>
