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

    $vendorPhone = $product->vendor?->contact_phone ?: ($product->vendor?->user?->phone ?: null);
    $vendorSocial = is_array($product->vendor?->social_links) ? $product->vendor?->social_links : (is_string($product->vendor?->social_links) ? (json_decode($product->vendor?->social_links, true) ?: []) : []);
    $vendorWa = $vendorSocial['whatsapp'] ?? $vendorPhone;
    $vendorWaClean = $vendorWa ? preg_replace('/[^0-9]/', '', $vendorWa) : null;
    if ($vendorWaClean && strlen($vendorWaClean) === 10) { $vendorWaClean = '91' . $vendorWaClean; }
    $productMsg = rawurlencode("Namaste " . ($product->vendor?->shop_name ?: '') . "! I saw '{$product->title}' on K71: " . url('/product/' . $product->slug));
    $vendorWaUrl = $vendorWaClean ? "https://wa.me/{$vendorWaClean}?text={$productMsg}" : ($product->vendor ? route('storefront.show', $product->vendor->slug) : route('product.show', $product->slug));

    $vendorInsta = $vendorSocial['instagram'] ?? ($product->vendor?->user?->username ?: null);
    if ($vendorInsta) {
        $vendorInstaHandle = ltrim($vendorInsta, '@');
        $vendorInstaUrl = Str::startsWith($vendorInsta, 'http') ? $vendorInsta : 'https://instagram.com/' . $vendorInstaHandle;
    } else {
        $vendorInstaUrl = $product->vendor ? route('storefront.show', $product->vendor->slug) : route('product.show', $product->slug);
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

        <!-- Context-Aware Themed Action CTA Row with Instagram & WhatsApp -->
        <div class="mt-auto pt-2 border-t border-stone-100 dark:border-zinc-800/80 flex items-center justify-between gap-2">
            <div class="flex-1 min-w-0">
                @if($isOutOfStock)
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">
                        Sold Out
                    </span>
                @elseif($isService)
                    <a href="{{ $cardUrl }}" 
                       class="text-[11px] font-black text-purple-700 hover:text-purple-900 hover:underline inline-flex items-center gap-1 whitespace-nowrap" 
                       title="View Service Packages">
                        <span>View Packages</span>
                        <span class="text-[12px] leading-none">&rarr;</span>
                    </a>
                @elseif($product->isEnquiry())
                    <a href="{{ $cardUrl }}" 
                       class="text-[11px] font-black {{ $theme['accent_text'] }} hover:underline inline-flex items-center gap-1 whitespace-nowrap" 
                       title="Request Quotation / View Details">
                        <span>Request Quote</span>
                        <span class="text-[12px] leading-none">&rarr;</span>
                    </a>
                @elseif($product->isBooking())
                    <a href="{{ $cardUrl }}" 
                       class="text-[11px] font-black {{ $theme['accent_text'] }} hover:underline inline-flex items-center gap-1 whitespace-nowrap" 
                       title="Book Service">
                        <span>Book Service</span>
                        <span class="text-[12px] leading-none">&rarr;</span>
                    </a>
                @elseif(!$product->isPurchase())
                    <a href="{{ $cardUrl }}" 
                       class="text-[11px] font-black {{ $theme['accent_text'] }} hover:underline inline-flex items-center gap-1 whitespace-nowrap" 
                       title="View Listing">
                        <span>View Listing</span>
                        <span class="text-[12px] leading-none">&rarr;</span>
                    </a>
                @elseif($hasMultipleVariants)
                    <a href="{{ $cardUrl }}" 
                       class="px-3 py-1.5 rounded-full {{ $theme['btn_pill'] }} text-[11px] font-black transition-all inline-flex items-center gap-1 whitespace-nowrap" 
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
                        class="w-full py-1.5 px-3 rounded-xl {{ $theme['btn_bg'] }} text-xs font-black flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer active:scale-95"
                        :class="added ? '!bg-emerald-600 !text-white' : ''"
                        :disabled="adding || added"
                        aria-label="Add {{ addslashes($product->title) }} to Cart"
                        title="Add to Cart"
                    >
                        <span x-show="!adding && !added" class="flex items-center gap-1 whitespace-nowrap">
                            <span>ADD</span>
                            <span class="text-sm font-bold leading-none">+</span>
                        </span>
                        <span x-show="adding" class="flex items-center gap-1 text-white">
                            <svg class="animate-spin w-3 h-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>...</span>
                        </span>
                        <span x-show="added" class="flex items-center gap-1 text-white">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            <span>✓</span>
                        </span>
                    </button>
                @endif
            </div>

            <!-- Direct Instagram & WhatsApp Hotlines -->
            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ $vendorInstaUrl }}" target="_blank" rel="noopener noreferrer" 
                   @click.stop
                   class="w-6 h-6 rounded-full bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white flex items-center justify-center hover:scale-110 transition-transform shadow-2xs shrink-0 cursor-pointer" 
                   title="Direct Instagram" aria-label="Vendor Instagram">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                <a href="{{ $vendorWaUrl }}" target="_blank" rel="noopener noreferrer" 
                   @click.stop
                   class="w-6 h-6 rounded-full bg-[#25D366] text-white flex items-center justify-center hover:scale-110 transition-transform shadow-2xs shrink-0 cursor-pointer" 
                   title="Direct WhatsApp" aria-label="Vendor WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.599 2.679-.702c.972.53 1.815.812 2.781.813 3.179 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.768-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.647.05-1.077-.087-.277-.088-.636-.213-1.096-.412-1.964-.848-3.238-2.837-3.336-2.969-.098-.133-.799-1.063-.799-2.024 0-.961.503-1.432.682-1.626.178-.194.389-.243.518-.243.13 0 .26.002.373.007.12.006.279-.046.438.334.162.387.553 1.347.602 1.445.049.098.082.213.016.344-.066.13-.098.213-.195.326-.098.113-.205.253-.294.34-.098.098-.201.205-.087.401.114.195.508.837 1.09 1.356.75.669 1.382.876 1.577.973.195.098.309.082.423-.049.114-.13.488-.568.618-.763.13-.195.26-.163.439-.098.179.066 1.137.536 1.332.634.195.098.326.146.374.228.049.082.049.471-.095.876z"/></svg>
                </a>
            </div>
        </div>
        
    </div>
</div>
