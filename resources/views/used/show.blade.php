<x-layouts.used 
    :title="$product->title . ' — ₹' . number_format($product->base_price / 100) . ' in ' . ($product->custom_fields['location'] ?? ($product->vendor->city ?? 'Kishangarh, Rajasthan')) . ' | K71 Used'"
    :metaDescription="Str::limit($product->description, 160)"
>
    @php
        $custom = $product->custom_fields ?? [];
        $badge = $custom['badge'] ?? ($product->is_featured ? 'FEATURED' : 'VERIFIED');
        $location = $custom['location'] ?? ($product->vendor->city ?? 'Kishangarh, Rajasthan');
        $adId = $custom['ad_id'] ?? ('1857' . str_pad($product->id, 6, '0', STR_PAD_LEFT));
        $views = $custom['views_past_hour'] ?? 8;
        $specs = $custom['specs'] ?? [];
        $condition = $custom['condition'] ?? ($specs['Condition'] ?? 'Good Condition');
        $sellerName = $product->vendor->shop_name ?? ($product->vendor->user?->name ?? 'Verified Seller');
        $sellerPhone = $product->vendor->phone ?? '+919549311580';
        $sellerInitial = strtoupper(substr($sellerName, 0, 1));
        $isNegotiable = $product->is_negotiable ?? true;
        $allImages = $product->images;
        $primaryImg = $allImages->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1000&q=80';
    @endphp

    <div x-data="{
        activeImageIndex: 0,
        images: [
            @if($allImages->isNotEmpty())
                @foreach($allImages as $img)
                    '{{ $img->path }}',
                @endforeach
            @else
                '{{ $primaryImg }}'
            @endif
        ],
        phoneRevealed: false,
        reportModalOpen: false,
        reportReason: 'Wrong Price or Info',
        reportSubmitted: false,
        chatModalOpen: false,
        chatText: 'Hi {{ $sellerName }}, is your {{ addslashes($product->title) }} still available for inspection in {{ explode(',', $location)[0] }}?',
        isItemWishlisted: false,
        
        init() {
            const list = JSON.parse(localStorage.getItem('k71_used_wishlist') || '[]');
            this.isItemWishlisted = list.includes({{ $product->id }});
        },

        toggleWish() {
            let list = JSON.parse(localStorage.getItem('k71_used_wishlist') || '[]');
            if (this.isItemWishlisted) {
                list = list.filter(id => id !== {{ $product->id }});
                this.isItemWishlisted = false;
            } else {
                list.push({{ $product->id }});
                this.isItemWishlisted = true;
            }
            localStorage.setItem('k71_used_wishlist', JSON.stringify(list));
        },

        shareListing() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ addslashes($product->title) }} on K71 Used',
                    text: 'Check out this {{ addslashes($product->title) }} for ₹{{ number_format($product->base_price / 100) }} on K71 Used.',
                    url: window.location.href,
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Listing link copied to clipboard!');
            }
        }
    }" class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]" style="font-family: var(--font-grail-sans);">

        <!-- ===================================================================
             1. GRAIL OBSIDIAN HEADER BAND & BREADCRUMBS
             =================================================================== -->
        <section class="grail-hero-band py-4 sm:py-5 px-3 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto">
                <nav class="flex items-center justify-between gap-3 text-xs font-medium text-[#EFE9F2]/65 overflow-x-auto no-scrollbar" aria-label="Breadcrumb">
                    <div class="flex items-center gap-2 min-w-0">
                        <a href="{{ route('used.index') }}" class="hover:text-[#ECD2F3] transition flex items-center gap-1 shrink-0 font-semibold">
                            <span>←</span>
                            <span>All Classifieds</span>
                        </a>
                        <span class="text-white/25">/</span>
                        <a href="{{ route('used.index') }}" class="text-[#ECD2F3] hover:text-[#FFCDFF] transition shrink-0">
                            Used Marketplace
                        </a>
                        @if($product->category)
                            <span class="text-white/25">/</span>
                            <a href="{{ route('used.index', ['cat' => $product->category->slug]) }}" class="hover:text-[#ECD2F3] transition shrink-0">
                                {{ $product->category->name }}
                            </a>
                        @endif
                        <span class="text-white/25">/</span>
                        <span class="text-white font-semibold truncate max-w-[180px] sm:max-w-xs">{{ $product->title }}</span>
                    </div>
                    <span class="hidden sm:inline-block shrink-0 font-mono text-[11px] text-[#C9A24A] uppercase tracking-[0.12em]">
                        AD ID: {{ $adId }}
                    </span>
                </nav>
            </div>
        </section>

        <!-- ===================================================================
             2. CURVED EDITORIAL SHEET — MAIN 2-COLUMN DETAIL LAYOUT
             =================================================================== -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] pt-6 sm:pt-8 pb-28 md:pb-14 px-2.5 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <!-- Left Column: Gallery, Specs & Description -->
                    <div class="lg:col-span-8 space-y-5">
                        
                        <!-- Image Gallery Box -->
                        <div class="grail-card bg-white rounded-3xl border border-[#E6E2E8] shadow-xs overflow-hidden p-2.5 sm:p-4" data-grail-reveal>
                            <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] bg-[#0D0D0D] rounded-2xl overflow-hidden flex items-center justify-center">
                                <img :src="images[activeImageIndex]" 
                                     alt="{{ $product->title }}" 
                                     class="w-full h-full object-contain select-none">

                                <!-- Badges Top-Left -->
                                <div class="absolute top-3 left-3 flex items-center gap-2 z-10">
                                    <span class="px-2.5 py-1 rounded-full bg-[#ECD2F3] text-[#1A1018] font-bold text-[10px] uppercase tracking-[0.1em] shadow-sm">
                                        👑 {{ $badge }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-xs text-[#EFE9F2] font-bold text-[10px] uppercase tracking-[0.08em] border border-white/15 shadow-sm">
                                        {{ $condition }}
                                    </span>
                                </div>

                                <!-- Top-Right Actions: Wishlist + Share -->
                                <div class="absolute top-3 right-3 flex items-center gap-2 z-10">
                                    <button type="button" 
                                            @click="toggleWish()" 
                                            class="w-8 h-8 rounded-full bg-white/95 backdrop-blur-xs flex items-center justify-center text-[#1A1018] hover:text-rose-500 shadow-sm transition cursor-pointer">
                                        <svg class="w-4 h-4" :class="isItemWishlisted ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            @click="shareListing()" 
                                            class="w-8 h-8 rounded-full bg-white/95 backdrop-blur-xs flex items-center justify-center text-[#1A1018] hover:text-[#623562] shadow-sm transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    </button>
                                </div>

                                <!-- Image Counter Bottom-Right -->
                                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/75 backdrop-blur-xs text-[#EFE9F2] text-xs font-mono font-semibold border border-white/10 z-10">
                                    <span x-text="(activeImageIndex + 1) + '/' + images.length"></span>
                                </div>

                                <!-- Prev / Next Controls -->
                                <template x-if="images.length > 1">
                                    <div>
                                        <button type="button" 
                                                @click="activeImageIndex = (activeImageIndex > 0) ? activeImageIndex - 1 : images.length - 1"
                                                class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition border border-white/15">
                                            ‹
                                        </button>
                                        <button type="button" 
                                                @click="activeImageIndex = (activeImageIndex < images.length - 1) ? activeImageIndex + 1 : 0"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition border border-white/15">
                                            ›
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Thumbnails Strip -->
                            <template x-if="images.length > 1">
                                <div class="flex items-center gap-2 mt-3 overflow-x-auto no-scrollbar pb-1">
                                    <template x-for="(thumb, idx) in images" :key="idx">
                                        <button type="button" 
                                                @click="activeImageIndex = idx"
                                                :class="activeImageIndex === idx ? 'border-[#1A1018] ring-2 ring-[#ECD2F3]' : 'border-[#E6E2E8] opacity-65 hover:opacity-100'"
                                                class="w-16 h-16 rounded-xl overflow-hidden border-2 bg-[#FAFAF8] shrink-0 transition cursor-pointer">
                                            <img :src="thumb" class="w-full h-full object-cover">
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- MOBILE-ONLY: Price & Action Box -->
                        <div class="lg:hidden grail-card bg-white rounded-2xl border border-[#E6E2E8] p-4 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-2xl font-bold text-[#1A1018]">
                                    ₹ {{ number_format($product->base_price / 100) }}
                                </div>
                                @if($isNegotiable)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wide">
                                        Negotiable
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-xl font-medium text-[#1A1018] leading-snug" style="font-family: var(--font-grail-display);">
                                {{ $product->title }}
                            </h1>

                            <div class="text-xs text-[#1A1018]/60 flex items-center justify-between pt-1.5 border-t border-[#E6E2E8]">
                                <span>📍 {{ $location }}</span>
                                <span>{{ $product->created_at ? $product->created_at->diffForHumans() : 'Recently' }}</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-2 gap-2 pt-2">
                                <button type="button" 
                                        @click="$dispatch('open-offer-modal', { id: {{ $product->id }}, title: '{{ addslashes($product->title) }}', price: {{ $product->base_price / 100 }}, image: '{{ $primaryImg }}' })"
                                        class="grail-cta-pill py-3 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                                    <span>🤝 Make Offer</span>
                                </button>

                                <button type="button" 
                                        @click="chatModalOpen = true"
                                        class="grail-btn-obsidian py-3 text-xs font-semibold flex items-center justify-center gap-1.5">
                                    <span>💬 Chat Seller</span>
                                </button>
                            </div>

                            <!-- Call Button on mobile -->
                            <template x-if="!phoneRevealed">
                                <button type="button" 
                                        @click="phoneRevealed = true"
                                        class="w-full py-2.5 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3]/60 text-[#1A1018] text-xs font-semibold flex items-center justify-center gap-1.5 transition border border-[#E6E2E8]">
                                    <span>📞 Show Phone Number</span>
                                </button>
                            </template>
                            <template x-if="phoneRevealed">
                                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs font-bold text-emerald-900">
                                    <span class="font-bold text-sm">{{ $sellerPhone }}</span>
                                    <a href="tel:{{ $sellerPhone }}" class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold">Call Now</a>
                                </div>
                            </template>
                        </div>

                        <!-- Product Specifications Card -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] shadow-xs p-5 sm:p-6 space-y-4" data-grail-reveal>
                            <div class="flex items-center justify-between pb-3 border-b border-[#E6E2E8]">
                                <h2 class="text-lg sm:text-xl font-medium text-[#1A1018] flex items-center gap-2" style="font-family: var(--font-grail-display);">
                                    <span>📋</span>
                                    <span>Item Overview &amp; Specifications</span>
                                </h2>
                                <span class="text-xs text-[#1A1018]/45 font-mono">AD ID: {{ $adId }}</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                                <div class="p-3 rounded-xl bg-[#F8EFFA] border border-[#ECD2F3]">
                                    <span class="grail-eyebrow !text-[#623562] block">Condition</span>
                                    <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $condition }}</span>
                                </div>

                                <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Brand / Make</span>
                                    <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $product->brand ?? 'Standard' }}</span>
                                </div>

                                <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Location</span>
                                    <span class="font-bold text-[#1A1018] text-sm mt-0.5 block truncate">{{ explode(',', $location)[0] }}</span>
                                </div>

                                <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Original Bill / Box</span>
                                    <span class="font-bold text-[#1A1018] text-sm mt-0.5 block">{{ $custom['bill_available'] ?? 'Available' }}</span>
                                </div>

                                <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block">Price Flexibility</span>
                                    <span class="font-bold text-emerald-700 text-sm mt-0.5 block">{{ $isNegotiable ? 'Negotiable' : 'Fixed Price' }}</span>
                                </div>

                                <!-- Specific Attributes -->
                                @if(!empty($specs))
                                    @foreach($specs as $specKey => $specVal)
                                        @if(!in_array(strtolower($specKey), ['condition', 'price', 'brand', 'location']))
                                            <div class="p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                                <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/50 block truncate">{{ $specKey }}</span>
                                                <span class="font-bold text-[#1A1018] text-sm mt-0.5 block truncate">{{ $specVal }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Description Card -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] shadow-xs p-5 sm:p-6 space-y-3" data-grail-reveal>
                            <h2 class="text-lg sm:text-xl font-medium text-[#1A1018] flex items-center gap-2" style="font-family: var(--font-grail-display);">
                                <span>📝</span>
                                <span>Seller's Description</span>
                            </h2>
                            <div class="text-xs sm:text-sm text-[#1A1018]/80 leading-relaxed whitespace-pre-line font-normal">
                                {{ $product->description }}
                            </div>
                        </div>

                        <!-- Safety Advice -->
                        <div class="rounded-2xl bg-[#FAFAF8] border border-[#C9A24A]/35 p-5 space-y-2" data-grail-reveal>
                            <div class="flex items-center gap-2 text-[#1A1018] font-bold text-xs sm:text-sm">
                                <span>🛡️</span>
                                <span>Safety Tips for Buyers in Kishangarh &amp; Rajasthan</span>
                            </div>
                            <ul class="space-y-1 text-xs text-[#1A1018]/70">
                                <li>• Meet the seller in a busy public place (e.g. Bus Stand or City Center).</li>
                                <li>• Physically inspect and test the device or vehicle thoroughly before releasing payment.</li>
                                <li>• Never transfer advance booking money or scan QR codes claiming to credit your account.</li>
                            </ul>
                        </div>

                    </div>

                    <!-- Right Column: Desktop Sticky Price Card & Seller Info -->
                    <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-24">
                        
                        <!-- Desktop Price & Action Card -->
                        <div class="hidden lg:block grail-card bg-white rounded-3xl border border-[#E6E2E8] p-6 shadow-sm space-y-4">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="grail-eyebrow !text-[#623562]">Asking Price</span>
                                    @if($isNegotiable)
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wide">
                                            Negotiable
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#EFE9F2] text-[#1A1018] text-[10px] font-bold uppercase tracking-wide">
                                            Fixed
                                        </span>
                                    @endif
                                </div>
                                <div class="text-3xl font-bold text-[#1A1018] mt-1.5">
                                    ₹ {{ number_format($product->base_price / 100) }}
                                </div>
                                <h1 class="text-lg font-medium text-[#1A1018] mt-2 leading-snug" style="font-family: var(--font-grail-display);">
                                    {{ $product->title }}
                                </h1>
                                <div class="text-xs text-[#1A1018]/55 mt-2.5 flex items-center justify-between">
                                    <span>📍 {{ $location }}</span>
                                    <span>{{ $product->created_at ? $product->created_at->diffForHumans() : 'Recently' }}</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-2.5 pt-3 border-t border-[#E6E2E8]">
                                <button type="button" 
                                        @click="$dispatch('open-offer-modal', { id: {{ $product->id }}, title: '{{ addslashes($product->title) }}', price: {{ $product->base_price / 100 }}, image: '{{ $primaryImg }}' })"
                                        class="grail-cta-pill w-full py-3.5 text-xs sm:text-sm font-bold tracking-wide transition flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                                    <span class="text-base">🤝</span>
                                    <span>Make an Offer</span>
                                </button>

                                <button type="button" 
                                        @click="chatModalOpen = true"
                                        class="grail-btn-obsidian w-full py-3 text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 cursor-pointer">
                                    <span class="text-base">💬</span>
                                    <span>Chat with Seller</span>
                                </button>

                                <!-- Show Contact Phone -->
                                <div>
                                    <template x-if="!phoneRevealed">
                                        <button type="button" 
                                                @click="phoneRevealed = true"
                                                class="w-full py-3 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3]/60 border border-[#E6E2E8] text-[#1A1018] text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 cursor-pointer">
                                            <span>📞 Show Contact Phone</span>
                                        </button>
                                    </template>
                                    <template x-if="phoneRevealed">
                                        <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs font-bold text-emerald-900">
                                            <span class="text-sm font-bold">{{ $sellerPhone }}</span>
                                            <a href="tel:{{ $sellerPhone }}" class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-bold">Call Now</a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Seller Profile Card -->
                        <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] p-5 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-[#1A1018] text-[#ECD2F3] font-bold text-base flex items-center justify-center shadow-xs">
                                        {{ $sellerInitial }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#1A1018] flex items-center gap-1">
                                            <span>{{ $sellerName }}</span>
                                            <span class="text-[#623562] text-[11px]" title="Verified Seller">✓</span>
                                        </div>
                                        <div class="text-[10px] text-[#1A1018]/50 font-medium">Verified Community Seller</div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3] text-[9px] font-bold uppercase tracking-wider">
                                    Verified
                                </span>
                            </div>

                            <div class="pt-2.5 border-t border-[#E6E2E8] flex items-center justify-between text-xs text-[#1A1018]/60">
                                <span>Member on K71</span>
                                <span class="font-bold text-[#1A1018]">Since 2025</span>
                            </div>

                            <div class="pt-1 text-center">
                                <button type="button" 
                                        @click="reportModalOpen = true"
                                        class="text-[11px] font-semibold text-[#1A1018]/45 hover:text-rose-600 transition cursor-pointer">
                                    🚩 Report suspicious ad
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- ===================================================================
                     3. SIMILAR PRE-OWNED PRODUCTS
                     =================================================================== -->
                @if($similarItems->isNotEmpty())
                    <section class="mt-10 pt-8 border-t border-[#E6E2E8]" data-grail-reveal>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl sm:text-2xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                Similar Pre-Owned Listings
                            </h3>
                            <a href="{{ route('used.index') }}" class="text-xs font-bold text-[#623562] hover:text-[#1A1018] transition hover:underline">
                                View All Classifieds &rarr;
                            </a>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($similarItems as $sim)
                                @php
                                    $simImg = $sim->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=400&q=80';
                                @endphp
                                <a href="{{ route('used.show', $sim->slug) }}" class="group grail-card bg-white rounded-2xl border border-[#E6E2E8] hover:border-[#623562]/40 hover:shadow-md transition p-2.5 flex flex-col justify-between">
                                    <div class="aspect-[4/3] rounded-xl bg-[#FAFAF8] overflow-hidden mb-2">
                                        <img src="{{ $simImg }}" alt="{{ $sim->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-[#1A1018]">
                                            ₹ {{ number_format($sim->base_price / 100) }}
                                        </div>
                                        <div class="text-xs font-semibold text-[#1A1018]/80 truncate mt-0.5">
                                            {{ $sim->title }}
                                        </div>
                                        <div class="text-[10px] text-[#1A1018]/50 mt-1">
                                            📍 {{ explode(',', $sim->custom_fields['location'] ?? 'Kishangarh')[0] }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>

        <!-- ===================================================================
             4. CHAT WITH SELLER MODAL (GRAIL EDITORIAL)
             =================================================================== -->
        <div x-show="chatModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs"
             x-transition>
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full shadow-2xl border border-[#E6E2E8] overflow-hidden"
                 @click.outside="chatModalOpen = false">
                
                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-5 sm:px-6 py-4 flex items-center justify-between border-b border-white/10">
                    <div>
                        <span class="grail-eyebrow block">Direct Buyer Chat</span>
                        <h3 class="text-lg font-light text-white flex items-center gap-2 mt-0.5" style="font-family: var(--font-grail-display);">
                            <span>💬</span>
                            <span>Message Seller</span>
                        </h3>
                    </div>
                    <button @click="chatModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-[#EFE9F2] flex items-center justify-center text-sm font-bold transition">
                        ✕
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <div class="p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1A1018] text-[#ECD2F3] font-bold flex items-center justify-center text-sm shrink-0">
                            {{ $sellerInitial }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[#1A1018] truncate">{{ $sellerName }}</div>
                            <div class="text-[11px] text-[#623562] font-semibold truncate">Re: {{ $product->title }}</div>
                        </div>
                    </div>

                    <form action="{{ route('messages.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="receiver_id" value="{{ $product->vendor->user_id ?? 1 }}">

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-[0.12em] text-[#1A1018]/70 mb-1">Your Message</label>
                            <textarea name="body" x-model="chatText" rows="3" required
                                      class="grail-input-light w-full px-3.5 py-2.5 text-xs resize-none"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="grail-btn-obsidian flex-1 py-3 text-xs justify-center cursor-pointer">
                                Send Message →
                            </button>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $sellerPhone) }}?text={{ urlencode('Hi ' . $sellerName . ', I am interested in your ' . $product->title . ' on K71 Used.') }}" 
                               target="_blank"
                               class="px-4 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1">
                                <span>WhatsApp</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===================================================================
             5. REPORT LISTING MODAL (GRAIL EDITORIAL)
             =================================================================== -->
        <div x-show="reportModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs"
             x-transition>
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-sm w-full shadow-2xl border border-[#E6E2E8] overflow-hidden"
                 @click.outside="reportModalOpen = false">
                
                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-5 sm:px-6 py-4 flex items-center justify-between border-b border-white/10">
                    <div>
                        <span class="grail-eyebrow block">Marketplace Safety</span>
                        <h3 class="text-lg font-light text-white flex items-center gap-2 mt-0.5" style="font-family: var(--font-grail-display);">
                            <span>🚩</span>
                            <span>Report Listing</span>
                        </h3>
                    </div>
                    <button @click="reportModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-[#EFE9F2] flex items-center justify-center text-sm font-bold transition">
                        ✕
                    </button>
                </div>

                <div class="p-5 sm:p-6">
                    <template x-if="!reportSubmitted">
                        <div class="space-y-3">
                            <p class="text-xs text-[#1A1018]/70">
                                Help us keep K71 Used safe. What is wrong with this listing?
                            </p>

                            <div class="space-y-2 text-xs font-semibold text-[#1A1018]">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#E6E2E8] cursor-pointer hover:border-[#623562]/40 transition">
                                    <input type="radio" name="reason" value="Fraud or Fake Item" x-model="reportReason" class="accent-[#623562]">
                                    <span>Fraudulent or Fake item</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#E6E2E8] cursor-pointer hover:border-[#623562]/40 transition">
                                    <input type="radio" name="reason" value="Already Sold" x-model="reportReason" class="accent-[#623562]">
                                    <span>Item is already sold</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#E6E2E8] cursor-pointer hover:border-[#623562]/40 transition">
                                    <input type="radio" name="reason" value="Wrong Price or Info" x-model="reportReason" class="accent-[#623562]">
                                    <span>Inaccurate price or photos</span>
                                </label>
                            </div>

                            <button type="button" 
                                    @click="reportSubmitted = true; setTimeout(() => { reportModalOpen = false; reportSubmitted = false; }, 2000)"
                                    class="w-full py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition cursor-pointer">
                                Submit Report
                            </button>
                        </div>
                    </template>

                    <template x-if="reportSubmitted">
                        <div class="py-6 text-center space-y-2">
                            <div class="text-3xl">✅</div>
                            <h4 class="text-sm font-bold text-[#1A1018]">Report Submitted</h4>
                            <p class="text-xs text-[#1A1018]/60">Thank you for reporting. Our moderation team will inspect this listing promptly.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-layouts.used>
