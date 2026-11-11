@php
    $custom = $product->custom_fields ?? [];
    $specs = is_array($custom['specs'] ?? null) ? $custom['specs'] : [];
    $allImages = $product->images;
    $photoUrls = $allImages->map->url->values()->all();
    $primaryImg = $photoUrls[0] ?? null;

    $effStatus = $product->effectiveUsedStatus();
    $isSold = $effStatus === 'sold';
    $isReserved = $effStatus === 'reserved';
    $isDraft = $effStatus === 'draft';
    $isPaused = $effStatus === 'paused';
    $isActive = $effStatus === 'active';
    $isNegotiable = $product->isNegotiable();

    $cityLabel = $product->city_label;
    $adId = $custom['ad_id'] ?? ('K71U-' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT));
    $sellerDisplayName = $sellerProfile?->display_name
        ?? $product->vendor?->shop_name
        ?? $ownerUser?->name
        ?? 'Seller';
    $sellerInitial = strtoupper(substr($sellerDisplayName, 0, 1));
    $memberSince = $ownerUser?->created_at ? $ownerUser->created_at->format('M Y') : 'Registered';
    $publicPhone = ($sellerProfile && $sellerProfile->show_phone_publicly)
        ? ($ownerUser?->phone ?? ($custom['contact_phone'] ?? ($product->vendor?->phone ?? null)))
        : null;

    $fulfillment = $product->fulfillment_options ?? [];
    $fulfillmentLabels = config('used_marketplace.fulfillment_modes', []);
    $packagingLabels = config('used_marketplace.packaging_options', []);
    $reportReasons = config('used_marketplace.report_reasons', []);

    $hasOrigRetail = $product->original_retail_price && $product->original_retail_price > $product->base_price;
    $discountPct = $hasOrigRetail
        ? (int) round((($product->original_retail_price - $product->base_price) / $product->original_retail_price) * 100)
        : 0;

    $schemaData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->title,
        'description' => \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 300),
        'sku' => $product->sku ?? $adId,
        'itemCondition' => $conditionMeta['schema_org'] ?? 'https://schema.org/UsedCondition',
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'INR',
            'price' => number_format($product->base_price / 100, 2, '.', ''),
            'availability' => $availabilityUrl,
            'url' => $canonicalUrl,
            'itemCondition' => $conditionMeta['schema_org'] ?? 'https://schema.org/UsedCondition',
        ],
    ];
    if ($product->brand) {
        $schemaData['brand'] = ['@type' => 'Brand', 'name' => $product->brand];
    }
    if (!empty($photoUrls)) {
        $schemaData['image'] = $photoUrls;
    }
@endphp

<x-layouts.used
    :title="$product->title . ' — ₹' . number_format($product->base_price / 100) . ' in ' . $cityLabel . ' | K71 Used'"
    :metaDescription="\Illuminate\Support\Str::limit($product->description, 160)"
    :canonicalUrl="$canonicalUrl"
    :robotsContent="$robotsContent"
    :schemaJson="$schemaData"
>
    <div x-data="{
        activeImageIndex: 0,
        images: @js($photoUrls),
        phoneRevealed: false,
        reportModalOpen: false,
        chatModalOpen: false,
        savesCount: {{ (int) ($product->saves_count ?? 0) }},
        shareListing() {
            if (navigator.share) {
                navigator.share({
                    title: @js($product->title . ' on K71 Used'),
                    text: @js($product->title . ' — ₹' . number_format($product->base_price / 100)),
                    url: window.location.href,
                }).catch(() => {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href);
                alert('Listing link copied to clipboard.');
            }
        }
    }"
    @used-wishlist-updated.window="if ($event.detail.productId === {{ $product->id }} && $event.detail.savesCount !== null && $event.detail.savesCount !== undefined) { savesCount = $event.detail.savesCount; }"
    class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- ===================================================================
             1. BREADCRUMBS BAR
             =================================================================== -->
        <section class="grail-hero-band py-4 px-3 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto">
                <nav class="flex items-center justify-between gap-3 text-xs font-medium text-[#EFE9F2]/65 overflow-x-auto no-scrollbar" aria-label="Breadcrumb">
                    <div class="flex items-center gap-2 min-w-0">
                        <a href="{{ route('used.index') }}" class="hover:text-[#ECD2F3] transition flex items-center gap-1 shrink-0 font-semibold">
                            <span>←</span>
                            <span>All Classifieds</span>
                        </a>
                        @if($product->category?->parent)
                            <span class="text-white/25">/</span>
                            <a href="{{ route('used.index', ['cat' => $product->category->parent->slug]) }}" class="hover:text-[#ECD2F3] transition shrink-0">
                                {{ $product->category->parent->name }}
                            </a>
                        @endif
                        @if($product->category)
                            <span class="text-white/25">/</span>
                            <a href="{{ route('used.index', ['cat' => $product->category->slug]) }}" class="text-[#ECD2F3] hover:text-[#FFCDFF] transition shrink-0">
                                {{ $product->category->name }}
                            </a>
                        @endif
                        <span class="text-white/25">/</span>
                        <span class="text-white font-semibold truncate max-w-[200px] sm:max-w-sm">{{ $product->title }}</span>
                    </div>
                    <span class="hidden sm:inline-block shrink-0 font-mono text-[11px] text-[#C9A24A] uppercase tracking-[0.12em]">
                        AD ID: {{ $adId }}
                    </span>
                </nav>
            </div>
        </section>

        <!-- ===================================================================
             2. CURVED EDITORIAL SHEET
             =================================================================== -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] pt-6 sm:pt-8 pb-20 px-3 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto space-y-6">

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Status Banners -->
                @if($isDraft)
                    <div class="p-4 rounded-2xl bg-[#1A1018] text-[#EFE9F2] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-xs">
                            <span class="px-2 py-0.5 rounded-full bg-amber-400 text-[#1A1018] font-black uppercase text-[10px] mr-2">Draft Preview</span>
                            Only you can see this listing. Review all details and publish when ready.
                        </div>
                        @if($isOwner)
                            <form action="{{ route('used.status', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="publish">
                                <button type="submit" class="px-4 py-2 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-xs font-bold cursor-pointer">
                                    Publish Listing Live →
                                </button>
                            </form>
                        @endif
                    </div>
                @elseif($isPaused)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-semibold">
                        <span>⏸ This listing is currently paused and hidden from public search results.</span>
                        @if($isOwner)
                            <form action="{{ route('used.status', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="publish">
                                <button type="submit" class="px-4 py-2 rounded-full bg-[#1A1018] text-[#ECD2F3] text-xs font-bold cursor-pointer">
                                    Resume Listing
                                </button>
                            </form>
                        @endif
                    </div>
                @elseif($isReserved)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 text-xs font-semibold flex items-center justify-between gap-2">
                        <span>🔒 This item is currently RESERVED pending buyer handover. New offers are paused.</span>
                        @if($isOwner)
                            <a href="{{ route('used.dashboard', ['tab' => 'transactions']) }}" class="underline font-bold">Manage Handover →</a>
                        @endif
                    </div>
                @elseif($isSold)
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">
                        🏷️ This pre-owned item has been marked SOLD by the seller. Enquiries and offers are closed.
                    </div>
                @endif

                <!-- Owner Quick Action Bar -->
                @if($isOwner)
                    <div class="p-3.5 rounded-2xl bg-white border border-[#ECD2F3] shadow-xs flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="font-bold text-[#623562]">Owner Controls:</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('used.edit', $product->id) }}" class="px-3.5 py-1.5 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold">
                                ✎ Edit Listing &amp; Photos
                            </a>
                            @if(!$isSold)
                                <form action="{{ route('used.status', $product->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="bump">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#FAFAF8] border border-black/15 font-bold hover:bg-[#F8EFFA] cursor-pointer">↑ Refresh Position</button>
                                </form>
                                @if($isActive)
                                    <form action="{{ route('used.status', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="pause">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#FAFAF8] border border-black/15 font-bold hover:bg-amber-50 cursor-pointer">⏸ Pause</button>
                                    </form>
                                @endif
                            @endif
                            <form action="{{ route('used.toggle-sold', $product->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-full {{ $isSold ? 'bg-emerald-600 text-white' : 'bg-rose-50 text-rose-700 border border-rose-200' }} font-bold cursor-pointer">
                                    {{ $isSold ? 'Re-activate Listing' : '✓ Mark Sold' }}
                                </button>
                            </form>
                            <a href="{{ route('used.dashboard') }}" class="px-3.5 py-1.5 rounded-full bg-[#F8EFFA] text-[#1A1018] font-bold">Dashboard →</a>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                    <!-- LEFT COLUMN (Gallery, Condition/Specs, Description, My Offers, Handover Safety) -->
                    <div class="lg:col-span-8 space-y-5">

                        <!-- Multi-Photo Gallery (ZERO stock image fallbacks) -->
                        <div class="grail-card bg-white rounded-3xl border border-[#E6E2E8] shadow-xs overflow-hidden p-2.5 sm:p-4">
                            <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] bg-[#0D0D0D] rounded-2xl overflow-hidden flex items-center justify-center">
                                @if($allImages->isNotEmpty())
                                    <img :src="images[activeImageIndex]"
                                         alt="{{ $product->title }}"
                                         class="w-full h-full object-contain select-none {{ $isSold ? 'opacity-75' : '' }}">
                                @else
                                    <div class="flex flex-col items-center justify-center text-center p-6 text-[#EFE9F2]/60">
                                        <span class="text-4xl mb-2 opacity-70">📷</span>
                                        <p class="text-sm font-semibold text-white/80">No photos uploaded by the seller</p>
                                        <p class="text-xs text-white/45 mt-1">You can request real photos in the enquiry chat before meeting.</p>
                                    </div>
                                @endif

                                <!-- Badges Top-Left -->
                                <div class="absolute top-3 left-3 flex flex-wrap items-center gap-1.5 z-10">
                                    @if($isSold)
                                        <span class="px-2.5 py-1 rounded-full bg-rose-600 text-white font-bold text-[10px] uppercase tracking-wider">SOLD</span>
                                    @elseif($isReserved)
                                        <span class="px-2.5 py-1 rounded-full bg-amber-400 text-[#1A1018] font-bold text-[10px] uppercase tracking-wider">RESERVED</span>
                                    @elseif($product->is_featured)
                                        <span class="px-2.5 py-1 rounded-full bg-[#ECD2F3] text-[#1A1018] font-bold text-[10px] uppercase tracking-wider">FEATURED</span>
                                    @endif

                                    <span class="px-2.5 py-1 rounded-full bg-black/80 text-[#EFE9F2] font-bold text-[10px] uppercase tracking-wider border border-white/15">
                                        {{ $product->condition_label }}
                                    </span>

                                    @if($product->is_refurbished)
                                        <span class="px-2.5 py-1 rounded-full bg-sky-700 text-white font-bold text-[10px] uppercase tracking-wider">
                                            Refurbished
                                        </span>
                                    @endif
                                </div>

                                <!-- Top-Right Save & Share -->
                                <div class="absolute top-3 right-3 flex items-center gap-2 z-10">
                                    <button type="button"
                                            @click="toggleWishlist({{ $product->id }})"
                                            class="w-9 h-9 rounded-full bg-white/95 backdrop-blur-xs flex items-center justify-center text-[#1A1018] hover:text-rose-500 shadow-sm transition cursor-pointer"
                                            aria-label="Save listing">
                                        <svg class="w-4 h-4" :class="isWishlisted({{ $product->id }}) ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            @click="shareListing()"
                                            class="w-9 h-9 rounded-full bg-white/95 backdrop-blur-xs flex items-center justify-center text-[#1A1018] hover:text-[#623562] shadow-sm transition cursor-pointer"
                                            aria-label="Share listing">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    </button>
                                </div>

                                @if($allImages->isNotEmpty())
                                    <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/75 text-[#EFE9F2] text-xs font-mono font-semibold border border-white/10 z-10">
                                        <span x-text="(activeImageIndex + 1) + '/' + images.length"></span>
                                    </div>
                                @endif

                                <template x-if="images.length > 1">
                                    <div>
                                        <button type="button"
                                                @click="activeImageIndex = (activeImageIndex > 0) ? activeImageIndex - 1 : images.length - 1"
                                                class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition border border-white/15 cursor-pointer">‹</button>
                                        <button type="button"
                                                @click="activeImageIndex = (activeImageIndex < images.length - 1) ? activeImageIndex + 1 : 0"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition border border-white/15 cursor-pointer">›</button>
                                    </div>
                                </template>
                            </div>

                            <!-- Thumbnail Strip -->
                            <template x-if="images.length > 1">
                                <div class="flex items-center gap-2 mt-3 overflow-x-auto no-scrollbar pb-1">
                                    <template x-for="(thumb, idx) in images" :key="idx">
                                        <button type="button"
                                                @click="activeImageIndex = idx"
                                                :class="activeImageIndex === idx ? 'border-[#1A1018] ring-2 ring-[#ECD2F3]' : 'border-[#E6E2E8] opacity-65 hover:opacity-100'"
                                                class="w-16 h-16 rounded-xl overflow-hidden border-2 bg-[#FAFAF8] shrink-0 transition cursor-pointer">
                                            <img :src="thumb" class="w-full h-full object-cover" alt="">
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- MOBILE-ONLY: Price & Primary Actions Box -->
                        <div class="lg:hidden grail-card bg-white rounded-2xl border border-[#E6E2E8] p-4 shadow-xs space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-2xl font-bold text-[#1A1018]">₹ {{ number_format($product->base_price / 100) }}</div>
                                    @if($hasOrigRetail && $discountPct > 0)
                                        <div class="text-xs text-[#1A1018]/50">
                                            <span class="line-through">₹{{ number_format($product->original_retail_price / 100) }}</span>
                                            <span class="ml-1 font-semibold text-[#623562]">({{ $discountPct }}% off original MRP)</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $isNegotiable ? 'bg-emerald-100 text-emerald-800' : 'bg-[#EFE9F2] text-[#1A1018]' }}">
                                    {{ $isNegotiable ? 'Negotiable' : 'Fixed Price' }}
                                </span>
                            </div>

                            <h1 class="text-xl font-medium text-[#1A1018] leading-snug" style="font-family: var(--font-grail-display);">
                                {{ $product->title }}
                            </h1>

                            <div class="text-xs text-[#1A1018]/60 flex items-center justify-between pt-1.5 border-t border-[#E6E2E8]">
                                <span>📍 {{ $cityLabel }}</span>
                                <span>{{ ($product->published_at ?? $product->created_at)?->diffForHumans() ?? 'Recently' }}</span>
                            </div>

                            @if(!$isOwner && $isActive)
                                <div class="grid {{ $isNegotiable ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 pt-2">
                                    @if($isNegotiable)
                                        <button type="button"
                                                @click="$dispatch('open-offer-modal', {
                                                    id: {{ $product->id }},
                                                    title: @js($product->title),
                                                    price: {{ $product->base_price / 100 }},
                                                    minOffer: {{ $product->min_offer_amount ? ($product->min_offer_amount / 100) : 'null' }},
                                                    image: @js($primaryImg)
                                                })"
                                                class="grail-cta-pill py-3 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                                            <span>🤝 Make Offer</span>
                                        </button>
                                    @endif
                                    <button type="button"
                                            @click="chatModalOpen = true"
                                            class="grail-btn-obsidian py-3 text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>💬 Contact Seller</span>
                                    </button>
                                </div>
                            @endif

                            @if($publicPhone)
                                <template x-if="!phoneRevealed">
                                    <button type="button" @click="phoneRevealed = true"
                                            class="w-full py-2.5 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3]/60 text-[#1A1018] text-xs font-semibold border border-[#E6E2E8] cursor-pointer">
                                        📞 Show Seller Phone
                                    </button>
                                </template>
                                <template x-if="phoneRevealed">
                                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs font-bold text-emerald-900">
                                        <span>{{ $publicPhone }}</span>
                                        <a href="tel:{{ $publicPhone }}" class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold">Call</a>
                                    </div>
                                </template>
                            @endif
                        </div>

                        <!-- Inline Buyer Offer Negotiation History (Only for authenticated buyer with offers on this listing) -->
                        @auth
                            @if($myOffers->isNotEmpty())
                                <div class="grail-card bg-white rounded-2xl border border-[#ECD2F3] p-5 space-y-3">
                                    <h2 class="text-base font-bold text-[#1A1018]">Your Offers on This Item</h2>
                                    @foreach($myOffers as $offer)
                                        <div class="p-3.5 rounded-xl bg-[#F8EFFA] border border-[#ECD2F3] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                            <div>
                                                <div class="font-bold text-[#1A1018]">
                                                    Your Offer: {{ $offer->formattedOfferAmount() }}
                                                    <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] uppercase {{ $offer->isAccepted() ? 'bg-emerald-100 text-emerald-800' : ($offer->isCountered() ? 'bg-amber-100 text-amber-800' : 'bg-black/10 text-[#1A1018]') }}">
                                                        {{ strtoupper($offer->status) }}
                                                    </span>
                                                </div>
                                                @if($offer->isCountered() && $offer->counter_amount)
                                                    <div class="text-[#623562] font-bold mt-1">
                                                        ⚡ Seller Counteroffer: ₹{{ number_format($offer->counter_amount / 100, 2) }}
                                                    </div>
                                                @endif
                                                @if($offer->vendor_notes)
                                                    <div class="text-[#1A1018]/70 mt-1">Seller note: "{{ $offer->vendor_notes }}"</div>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if($offer->isCountered())
                                                    <form action="{{ route('used.offers.respond_counter', $offer) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="accept_counter">
                                                        <button type="submit" class="px-3.5 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">Accept Counter</button>
                                                    </form>
                                                    <form action="{{ route('used.offers.respond_counter', $offer) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="reject_counter">
                                                        <button type="submit" class="px-3.5 py-1.5 rounded-full bg-white border border-black/15 font-bold cursor-pointer">Decline</button>
                                                    </form>
                                                @endif
                                                @if(in_array($offer->status, ['pending', 'countered'], true))
                                                    <form action="{{ route('used.offers.withdraw', $offer) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="px-3.5 py-1.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold cursor-pointer">Withdraw</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endauth

                        <!-- Condition, Disclosure & Specifications Card -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] shadow-xs p-5 sm:p-6 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-[#E6E2E8]">
                                <h2 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                    Condition, Disclosure &amp; Specs
                                </h2>
                                <span class="text-xs text-[#1A1018]/45 font-mono">AD ID: {{ $adId }}</span>
                            </div>

                            <!-- Honest Condition Banner -->
                            <div class="p-3.5 rounded-xl bg-[#F8EFFA] border border-[#ECD2F3] space-y-1">
                                <div class="text-xs font-bold text-[#623562] uppercase tracking-wider">
                                    Condition: {{ $conditionMeta['label'] ?? $product->condition_label }}
                                    @if($product->is_refurbished) • Professionally Refurbished @endif
                                </div>
                                @if(!empty($conditionMeta['description']))
                                    <p class="text-xs text-[#1A1018]/75">{{ $conditionMeta['description'] }}</p>
                                @endif
                            </div>

                            <!-- Defect Disclosure Box -->
                            @if(!empty($product->defect_notes))
                                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs space-y-1">
                                    <div class="font-bold text-amber-900">⚠️ Seller Disclosed Defects / Wear</div>
                                    <p class="text-amber-950/85 whitespace-pre-line">{{ $product->defect_notes }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                                @if($product->brand)
                                    <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Brand</span>
                                        <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $product->brand }}</span>
                                    </div>
                                @endif

                                @if($product->model_name)
                                    <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Model</span>
                                        <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $product->model_name }}</span>
                                    </div>
                                @endif

                                @if($product->purchase_year || $product->age_months !== null)
                                    <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Age / Purchased</span>
                                        <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">
                                            {{ $product->age_months !== null ? $product->age_months . ' months old' : '' }}
                                            {{ $product->purchase_year ? '(' . $product->purchase_year . ')' : '' }}
                                        </span>
                                    </div>
                                @endif

                                <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Area</span>
                                    <span class="font-bold text-[#1A1018] text-sm mt-0.5 block truncate">{{ $cityLabel }}</span>
                                </div>

                                @if($product->original_packaging)
                                    <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Packaging &amp; Bill</span>
                                        <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $packagingLabels[$product->original_packaging] ?? $product->original_packaging }}</span>
                                    </div>
                                @endif

                                @if($product->included_accessories)
                                    <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Included Accessories</span>
                                        <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $product->included_accessories }}</span>
                                    </div>
                                @endif

                                @if(($product->quantity_available ?? 1) > 1)
                                    <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Quantity Available</span>
                                        <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $product->quantity_available }} units</span>
                                    </div>
                                @endif

                                @foreach($specs as $specKey => $specVal)
                                    @if(!in_array(strtolower((string) $specKey), ['condition', 'price', 'brand', 'location']))
                                        <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                            <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block truncate">{{ ucfirst(str_replace('_', ' ', (string) $specKey)) }}</span>
                                            <span class="font-bold text-[#1A1018] text-sm mt-0.5 block truncate">{{ is_array($specVal) ? implode(', ', $specVal) : $specVal }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            @if($product->reason_for_selling)
                                <div class="text-xs text-[#1A1018]/75 pt-2 border-t border-[#E6E2E8]">
                                    <span class="font-bold text-[#1A1018]">Reason for selling:</span> {{ $product->reason_for_selling }}
                                </div>
                            @endif
                        </div>

                        <!-- Description Card -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] shadow-xs p-5 sm:p-6 space-y-3">
                            <h2 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                Seller's Description
                            </h2>
                            <div class="text-xs sm:text-sm text-[#1A1018]/80 leading-relaxed whitespace-pre-line font-normal">
                                {{ $product->description }}
                            </div>
                        </div>

                        <!-- Handover & Fulfillment Options + Privacy Notice -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] shadow-xs p-5 sm:p-6 space-y-3">
                            <h2 class="text-lg font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                Pickup, Meetup &amp; Handover Options
                            </h2>
                            <div class="flex flex-wrap gap-2 text-xs">
                                @if(!empty($fulfillment['local_pickup']))
                                    <span class="px-3 py-1.5 rounded-full bg-[#F8EFFA] border border-[#ECD2F3] text-[#1A1018] font-semibold">🏠 {{ $fulfillmentLabels['local_pickup']['label'] ?? 'Local Pickup' }}</span>
                                @endif
                                @if(!empty($fulfillment['public_meetup']))
                                    <span class="px-3 py-1.5 rounded-full bg-[#F8EFFA] border border-[#ECD2F3] text-[#1A1018] font-semibold">🤝 {{ $fulfillmentLabels['public_meetup']['label'] ?? 'Public Meetup Spot' }}</span>
                                @endif
                                @if(!empty($fulfillment['seller_shipping']))
                                    <span class="px-3 py-1.5 rounded-full bg-sky-50 border border-sky-200 text-sky-900 font-semibold">
                                        📦 {{ $fulfillmentLabels['seller_shipping']['label'] ?? 'Seller-Arranged Shipping' }}
                                        @if(!empty($fulfillment['shipping_charge']))
                                            (+₹{{ number_format($fulfillment['shipping_charge'] / 100) }})
                                        @endif
                                    </span>
                                @endif
                            </div>
                            @if(!empty($fulfillment['pickup_notes']))
                                <p class="text-xs text-[#1A1018]/75">{{ $fulfillment['pickup_notes'] }}</p>
                            @endif
                            <p class="text-[11px] text-[#1A1018]/55">
                                🔒 Privacy &amp; Direct Handover Notice: Public listings only show general city/locality ({{ $cityLabel }}). Exact pickup spots are shared privately in enquiry threads or upon offer acceptance. K71 Used facilitates direct peer handover without escrow or courier coverage.
                            </p>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Desktop Price Card & Seller Identity Card -->
                    <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-24">

                        <!-- Desktop Price & Action Card -->
                        <div class="hidden lg:block grail-card bg-white rounded-3xl border border-[#E6E2E8] p-6 shadow-sm space-y-4">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="grail-eyebrow !text-[#623562]">Asking Price</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $isNegotiable ? 'bg-emerald-100 text-emerald-800' : 'bg-[#EFE9F2] text-[#1A1018]' }}">
                                        {{ $isNegotiable ? 'Negotiable' : 'Fixed' }}
                                    </span>
                                </div>
                                <div class="text-3xl font-bold text-[#1A1018] mt-1.5">
                                    ₹ {{ number_format($product->base_price / 100) }}
                                </div>
                                @if($hasOrigRetail && $discountPct > 0)
                                    <div class="text-xs text-[#1A1018]/55 mt-0.5">
                                        Original retail: <span class="line-through">₹{{ number_format($product->original_retail_price / 100) }}</span>
                                        <span class="ml-1 font-semibold text-[#623562]">({{ $discountPct }}% lower)</span>
                                    </div>
                                @endif
                                @if($isNegotiable && $product->min_offer_amount)
                                    <div class="text-[11px] text-[#623562] font-semibold mt-1">
                                        Seller minimum offer: ₹{{ number_format($product->min_offer_amount / 100) }}
                                    </div>
                                @endif

                                <h1 class="text-lg font-medium text-[#1A1018] mt-2 leading-snug" style="font-family: var(--font-grail-display);">
                                    {{ $product->title }}
                                </h1>
                                <div class="text-xs text-[#1A1018]/55 mt-2.5 flex items-center justify-between">
                                    <span>📍 {{ $cityLabel }}</span>
                                    <span>{{ ($product->published_at ?? $product->created_at)?->diffForHumans() ?? 'Recently' }}</span>
                                </div>
                                <div class="text-[11px] text-[#1A1018]/50 mt-1.5 flex items-center gap-3">
                                    <span>👁 {{ (int) ($product->views_count ?? 0) }} views</span>
                                    <span>🔖 <span x-text="savesCount"></span> saves</span>
                                </div>
                            </div>

                            @if(!$isOwner && $isActive)
                                <div class="space-y-2.5 pt-3 border-t border-[#E6E2E8]">
                                    @if($isNegotiable)
                                        <button type="button"
                                                @click="$dispatch('open-offer-modal', {
                                                    id: {{ $product->id }},
                                                    title: @js($product->title),
                                                    price: {{ $product->base_price / 100 }},
                                                    minOffer: {{ $product->min_offer_amount ? ($product->min_offer_amount / 100) : 'null' }},
                                                    image: @js($primaryImg)
                                                })"
                                                class="grail-cta-pill w-full py-3.5 text-xs sm:text-sm font-bold tracking-wide transition flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                                            <span>🤝 Make Offer</span>
                                        </button>
                                    @endif

                                    @if($existingThread)
                                        <a href="{{ route('used.threads.show', $existingThread) }}"
                                           class="grail-btn-obsidian w-full py-3 text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2">
                                            <span>💬 Open Conversation Thread →</span>
                                        </a>
                                    @else
                                        <button type="button"
                                                @click="chatModalOpen = true"
                                                class="grail-btn-obsidian w-full py-3 text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 cursor-pointer">
                                            <span>💬 Contact Seller</span>
                                        </button>
                                    @endif

                                    @if($publicPhone)
                                        <div>
                                            <template x-if="!phoneRevealed">
                                                <button type="button" @click="phoneRevealed = true"
                                                        class="w-full py-3 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3]/60 border border-[#E6E2E8] text-[#1A1018] text-xs font-semibold cursor-pointer">
                                                    📞 Show Seller Phone
                                                </button>
                                            </template>
                                            <template x-if="phoneRevealed">
                                                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs font-bold text-emerald-900">
                                                    <span>{{ $publicPhone }}</span>
                                                    <a href="tel:{{ $publicPhone }}" class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold">Call Now</a>
                                                </div>
                                            </template>
                                        </div>
                                    @endif
                                </div>
                            @elseif($isOwner)
                                <div class="pt-3 border-t border-[#E6E2E8] text-xs text-[#1A1018]/65">
                                    You are viewing your own listing. Use the Owner Controls bar above or manage offers from your Used Dashboard.
                                </div>
                            @else
                                <div class="pt-3 border-t border-[#E6E2E8] text-xs font-semibold text-amber-800">
                                    Offers and new enquiries are currently closed ({{ strtoupper($effStatus) }}).
                                </div>
                            @endif
                        </div>

                        <!-- Public Seller Identity Card (Truthful metrics only) -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] p-5 shadow-xs space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-2xl bg-[#1A1018] text-[#ECD2F3] font-bold text-base flex items-center justify-center shadow-xs shrink-0 overflow-hidden">
                                        @if($ownerUser?->avatar)
                                            <img src="{{ format_user_avatar($ownerUser->avatar, $sellerDisplayName) }}" alt="{{ $sellerDisplayName }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $sellerInitial }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        @if($sellerProfile?->handle)
                                            <a href="{{ route('used.seller.profile', $sellerProfile->handle) }}" class="text-sm font-bold text-[#1A1018] hover:text-[#623562] truncate block">
                                                {{ $sellerDisplayName }} →
                                            </a>
                                        @else
                                            <div class="text-sm font-bold text-[#1A1018] truncate">{{ $sellerDisplayName }}</div>
                                        @endif
                                        <div class="text-[11px] text-[#1A1018]/55 truncate">{{ $sellerProfile?->location_label ?? $cityLabel }}</div>
                                    </div>
                                </div>
                                @if($sellerProfile && $sellerProfile->reviews_count > 0)
                                    <span class="px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold shrink-0">
                                        ★ {{ number_format($sellerProfile->avg_rating, 1) }} ({{ $sellerProfile->reviews_count }})
                                    </span>
                                @endif
                            </div>

                            @if($sellerProfile?->bio)
                                <p class="text-xs text-[#1A1018]/70 leading-relaxed">{{ $sellerProfile->bio }}</p>
                            @endif

                            <div class="pt-2.5 border-t border-[#E6E2E8] grid grid-cols-2 gap-2 text-xs text-[#1A1018]/70">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-[#1A1018]/45 block">Member Since</span>
                                    <span class="font-bold text-[#1A1018]">{{ $memberSince }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-[#1A1018]/45 block">Completed Deals</span>
                                    <span class="font-bold text-[#1A1018]">{{ (int) ($sellerProfile?->sold_count ?? 0) }}</span>
                                </div>
                            </div>

                            @if($sellerProfile?->handle)
                                <a href="{{ route('used.seller.profile', $sellerProfile->handle) }}"
                                   class="block w-full py-2.5 text-center rounded-full bg-[#F8EFFA] hover:bg-[#ECD2F3] text-xs font-bold text-[#1A1018] transition">
                                    View Seller Profile &amp; All Ads
                                </a>
                            @endif

                            @if(!$isOwner)
                                <div class="pt-1 text-center">
                                    <button type="button"
                                            @click="reportModalOpen = true"
                                            class="text-[11px] font-semibold text-[#1A1018]/50 hover:text-rose-600 transition cursor-pointer">
                                        🚩 Report this listing
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Seller Reviews Section -->
                @if($sellerReviews->isNotEmpty())
                    <section class="pt-6 border-t border-[#E6E2E8]">
                        <h3 class="text-lg sm:text-xl font-medium text-[#1A1018] mb-3" style="font-family: var(--font-grail-display);">
                            Verified Handover Reviews for {{ $sellerDisplayName }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($sellerReviews as $rev)
                                <div class="grail-card bg-white p-4 rounded-2xl border border-[#E6E2E8] space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-amber-600">{{ str_repeat('★', $rev->rating) }} ({{ $rev->rating }}/5)</span>
                                        <span class="text-[10px] text-[#1A1018]/50">{{ $rev->created_at?->diffForHumans() }}</span>
                                    </div>
                                    @if($rev->title)
                                        <div class="font-bold text-[#1A1018]">{{ $rev->title }}</div>
                                    @endif
                                    <p class="text-[#1A1018]/75">{{ $rev->body }}</p>
                                    <div class="text-[10px] text-[#1A1018]/50 pt-1 border-t border-[#E6E2E8]">
                                        By {{ $rev->user?->name ?? 'Buyer' }} @if($rev->product) • {{ \Illuminate\Support\Str::limit($rev->product->title, 30) }} @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Other Active Listings From This Seller -->
                @if($sellerOtherAds->isNotEmpty())
                    <section class="pt-6 border-t border-[#E6E2E8]">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                More Items From {{ $sellerDisplayName }}
                            </h3>
                            @if($sellerProfile?->handle)
                                <a href="{{ route('used.seller.profile', $sellerProfile->handle) }}" class="text-xs font-bold text-[#623562] hover:underline">View Seller Store →</a>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($sellerOtherAds as $sAd)
                                @include('used.partials.card', ['item' => $sAd])
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Similar Listings -->
                @if($similarItems->isNotEmpty())
                    <section class="pt-6 border-t border-[#E6E2E8]">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                Similar Pre-Owned Listings
                            </h3>
                            <a href="{{ route('used.index') }}" class="text-xs font-bold text-[#623562] hover:underline">Browse All →</a>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($similarItems as $sim)
                                @include('used.partials.card', ['item' => $sim])
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>
        </div>

        <!-- CONTACT SELLER ENQUIRY MODAL -->
        <div x-show="chatModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full shadow-2xl border border-[#E6E2E8] overflow-hidden" @click.outside="chatModalOpen = false">
                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-6 py-4 flex items-center justify-between border-b border-white/10">
                    <div>
                        <span class="grail-eyebrow block">Listing Enquiry</span>
                        <h3 class="text-lg font-light text-white" style="font-family: var(--font-grail-display);">💬 Contact Seller</h3>
                    </div>
                    <button @click="chatModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 text-[#EFE9F2] flex items-center justify-center text-sm font-bold cursor-pointer">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    @auth
                        <form action="{{ route('used.enquire', $product) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs">
                                <div class="font-bold text-[#1A1018]">{{ $sellerDisplayName }}</div>
                                <div class="text-[#623562] font-semibold truncate">Item: {{ $product->title }}</div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Your Enquiry</label>
                                <textarea name="body" rows="3" required
                                          class="grail-input-light w-full px-3.5 py-2.5 text-xs resize-none"
                                          placeholder="Ask about condition, serial/bill check, accessories, or arrange inspection...">{{ "Hi {$sellerDisplayName}, is your {$product->title} still available for inspection?" }}</textarea>
                            </div>
                            <button type="submit" class="grail-btn-obsidian w-full py-3 text-xs justify-center cursor-pointer">
                                Send Enquiry Message →
                            </button>
                        </form>
                    @else
                        <div class="text-center space-y-3 py-4">
                            <p class="text-xs text-[#1A1018]/75">Please sign in to send an enquiry message and keep your conversation in K71 Used.</p>
                            <a href="{{ route('login') }}" class="inline-block px-6 py-2.5 grail-btn-obsidian text-xs font-bold">Sign In to Contact Seller →</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <!-- REAL REPORT LISTING MODAL -->
        <div x-show="reportModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full shadow-2xl border border-[#E6E2E8] overflow-hidden" @click.outside="reportModalOpen = false">
                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-6 py-4 flex items-center justify-between border-b border-white/10">
                    <div>
                        <span class="grail-eyebrow block">Trust &amp; Safety</span>
                        <h3 class="text-lg font-light text-white" style="font-family: var(--font-grail-display);">🚩 Report Suspicious Listing</h3>
                    </div>
                    <button @click="reportModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 text-[#EFE9F2] flex items-center justify-center text-sm font-bold cursor-pointer">✕</button>
                </div>

                <div class="p-6">
                    @auth
                        <form action="{{ route('reports.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <input type="hidden" name="reportable_type" value="product">
                            <input type="hidden" name="reportable_id" value="{{ $product->id }}">

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1.5">Reason *</label>
                                <div class="space-y-1.5">
                                    @foreach($reportReasons as $rKey => $rLabel)
                                        <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#E6E2E8] cursor-pointer hover:border-[#623562]/40">
                                            <input type="radio" name="reason" value="{{ $rKey }}" {{ $loop->first ? 'checked' : '' }} class="accent-[#623562]">
                                            <span class="font-semibold">{{ $rLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Additional Details (Optional)</label>
                                <textarea name="details" rows="2" class="grail-input-light w-full px-3 py-2 text-xs resize-none" placeholder="Describe what looks suspicious or inaccurate..."></textarea>
                            </div>

                            <button type="submit" class="w-full py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold cursor-pointer">
                                Submit Report for Review
                            </button>
                        </form>
                    @else
                        <div class="text-center space-y-3 py-4">
                            <p class="text-xs text-[#1A1018]/75">Sign in so our moderation team can log and act on your report.</p>
                            <a href="{{ route('login') }}" class="inline-block px-6 py-2.5 grail-btn-obsidian text-xs font-bold">Sign In to Report →</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

    </div>
</x-layouts.used>
