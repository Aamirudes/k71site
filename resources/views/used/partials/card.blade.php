@php
    $custom = $item->custom_fields ?? [];
    $hasPhotos = $item->hasUploadedImages();
    $firstImg = $hasPhotos ? $item->images->first()->url : null;
    $photoCount = $item->images->count();
    $effStatus = $item->effectiveUsedStatus();
    $isSold = $effStatus === 'sold';
    $isReserved = $effStatus === 'reserved';
    $isOwner = auth()->check() && $item->isOwnedBy(auth()->user());
    $isNegotiable = $item->isNegotiable();
    $conditionMeta = $item->conditionMeta();
    $condLabel = !empty($custom['condition']) && empty($item->condition_grade)
        ? $custom['condition']
        : $conditionMeta['label'];
    $cityLabel = $item->city_label;
    $ownerUser = $item->ownerUser();
    $sellerProfile = $ownerUser?->usedSellerProfile;
    $sellerDisplayName = $sellerProfile?->display_name
        ?? $item->vendor?->shop_name
        ?? $ownerUser?->name
        ?? 'Seller';
    $hasOrigRetail = $item->original_retail_price && $item->original_retail_price > $item->base_price;
    $discountPct = $hasOrigRetail
        ? (int) round((($item->original_retail_price - $item->base_price) / $item->original_retail_price) * 100)
        : 0;
    $fulfillment = $item->fulfillment_options ?? [];
@endphp

<div class="grail-card hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between overflow-hidden group {{ $isSold ? 'opacity-80' : '' }}">
    <!-- Image Container -->
    <div class="relative w-full aspect-[4/3] bg-[#0D0D0D] overflow-hidden">
        <a href="{{ route('used.show', $item->slug) }}" class="block w-full h-full">
            @if($hasPhotos)
                <img src="{{ $firstImg }}"
                     alt="{{ $item->title }}"
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $isSold ? 'grayscale-[40%]' : '' }}">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center bg-[#141115] text-[#EFE9F2]/50 p-4 text-center">
                    <span class="text-2xl mb-1 opacity-75">📷</span>
                    <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#EFE9F2]/45">No photos uploaded</span>
                </div>
            @endif
        </a>

        <!-- Top-Left Status / Feature Badges -->
        <div class="absolute top-2 left-2 flex flex-wrap items-center gap-1 z-10 max-w-[75%]">
            @if($isSold)
                <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-black text-[9px] uppercase tracking-wider shadow-xs">
                    SOLD
                </span>
            @elseif($isReserved)
                <span class="px-2.5 py-0.5 rounded-full bg-amber-500 text-[#1A1018] font-black text-[9px] uppercase tracking-wider shadow-xs">
                    RESERVED
                </span>
            @elseif($item->is_featured)
                <span class="px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] font-black text-[9px] uppercase tracking-wider shadow-xs border border-[#ECD2F3]/30">
                    FEATURED
                </span>
            @endif

            @if($item->is_refurbished)
                <span class="px-2 py-0.5 rounded-full bg-sky-900/90 text-sky-100 font-bold text-[9px] uppercase tracking-wider">
                    Refurbished
                </span>
            @endif
        </div>

        <!-- Condition Pill Bottom-Left -->
        <span class="absolute bottom-2 left-2 px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider z-10 text-white"
              style="{{ ($conditionMeta['key'] ?? '') === 'like_new' || stripos($condLabel, 'like new') !== false ? 'background-color: #15803D !important;' : 'background-color: rgba(26, 16, 24, 0.88) !important;' }}">
            {{ $condLabel }}
        </span>

        @if($photoCount > 1)
            <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded-full bg-black/75 text-white text-[10px] font-semibold z-10">
                📷 {{ $photoCount }}
            </span>
        @endif

        <!-- Save / Wishlist Button Top-Right -->
        <button type="button"
                @click="toggleWishlist({{ $item->id }})"
                class="absolute top-2 right-2 z-20 w-8 h-8 rounded-full bg-white/95 backdrop-blur-md flex items-center justify-center text-[#1A1018]/75 hover:text-rose-500 shadow-sm transition cursor-pointer"
                aria-label="Save listing">
            <svg class="w-4 h-4" :class="isWishlisted({{ $item->id }}) ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
        </button>
    </div>

    <!-- Card Body -->
    <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between bg-white">
        <div>
            <!-- Asking Price + Optional Seller-Entered Original Price -->
            <div class="flex items-start justify-between gap-1.5 flex-wrap">
                <div>
                    <div class="text-[16px] font-bold text-[#1A1018] tracking-tight leading-tight">
                        ₹ {{ number_format($item->base_price / 100) }}
                    </div>
                    @if($hasOrigRetail && $discountPct > 0)
                        <div class="text-[10px] text-[#1A1018]/45 flex items-center gap-1 mt-0.5">
                            <span class="line-through">₹{{ number_format($item->original_retail_price / 100) }}</span>
                            <span class="font-semibold text-[#623562]">({{ $discountPct }}% off MRP)</span>
                        </div>
                    @endif
                </div>
                @if(!$isSold && !$isReserved)
                    @if($isNegotiable)
                        <span class="text-[9px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 shrink-0">
                            Negotiable
                        </span>
                    @else
                        <span class="text-[9px] font-bold text-[#1A1018]/65 bg-[#FAFAF8] px-2 py-0.5 rounded-full border border-black/10 shrink-0">
                            Fixed Price
                        </span>
                    @endif
                @endif
            </div>

            <!-- Title -->
            <a href="{{ route('used.show', $item->slug) }}" class="block mt-1.5">
                <h4 class="text-[13px] sm:text-[14px] leading-snug font-semibold text-[#1A1018] group-hover:text-[#623562] transition line-clamp-2 min-h-[2.3rem]">
                    {{ $item->title }}
                </h4>
            </a>

            <!-- Seller & Fulfillment Meta -->
            <div class="mt-2 flex items-center justify-between gap-1 text-[10px] text-[#1A1018]/65">
                @if($sellerProfile?->handle)
                    <a href="{{ route('used.seller.profile', $sellerProfile->handle) }}" class="truncate hover:text-[#623562] font-semibold transition">
                        👤 {{ $sellerDisplayName }}
                        @if(($sellerProfile->reviews_count ?? 0) > 0)
                            <span class="text-amber-600 ml-0.5">★ {{ number_format($sellerProfile->avg_rating, 1) }}</span>
                        @endif
                    </a>
                @else
                    <span class="truncate font-semibold">👤 {{ $sellerDisplayName }}</span>
                @endif
                @if(!empty($fulfillment['seller_shipping']))
                    <span class="px-1.5 py-0.5 rounded bg-[#F8EFFA] text-[#623562] font-semibold shrink-0">Shipping</span>
                @endif
            </div>

            <!-- City & Real Relative Time -->
            <div class="mt-1.5 text-[11px] text-[#1A1018]/55 flex items-center justify-between gap-1">
                <span class="truncate inline-flex items-center gap-1 bg-[#FAFAF8] border border-black/6 text-[#1A1018]/75 px-2 py-0.5 rounded-full text-[10px] font-medium">
                    <svg class="w-3 h-3 text-[#623562] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate">{{ $cityLabel }}</span>
                </span>
                <span class="shrink-0 text-[#1A1018]/55 font-medium">{{ ($item->refreshed_at ?? $item->created_at)?->diffForHumans(null, true) ?? '' }}</span>
            </div>
        </div>

        <!-- Card Actions -->
        <div class="mt-3 pt-2.5 border-t border-black/8 grid {{ !$isOwner && !$isSold && !$isReserved && $isNegotiable ? 'grid-cols-2' : 'grid-cols-1' }} gap-1.5">
            @if(!$isOwner && !$isSold && !$isReserved && $isNegotiable)
                <button type="button"
                        @click="$dispatch('open-offer-modal', {
                            id: {{ $item->id }},
                            title: @js($item->title),
                            price: {{ $item->base_price / 100 }},
                            minOffer: {{ $item->min_offer_amount ? ($item->min_offer_amount / 100) : 'null' }},
                            image: @js($firstImg)
                        })"
                        class="py-2 px-2 grail-cta-pill text-xs font-bold text-center cursor-pointer">
                    🤝 Offer
                </button>
            @endif

            <a href="{{ route('used.show', $item->slug) }}"
               class="py-2 px-2 grail-btn-ghost-light text-xs font-semibold text-center">
                {{ $isOwner ? 'Manage / View' : ($isSold ? 'View Sold Ad' : 'View') }}
            </a>
        </div>
    </div>
</div>

