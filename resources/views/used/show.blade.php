<x-layouts.used 
    :title="$product->title . ' — ₹' . number_format($product->base_price / 100) . ' in ' . ($product->custom_fields['location'] ?? ($product->vendor->city ?? 'Kishangarh, Rajasthan')) . ' | K71 Used'"
    :metaDescription="Str::limit($product->description, 160)"
>
    @php
        $custom = $product->custom_fields ?? [];
        $badge = $custom['badge'] ?? ($product->is_featured ? 'FEATURED' : 'VERIFIED');
        $location = $custom['location'] ?? ($product->vendor->city ?? 'Kishangarh, Rajasthan');
        $adId = $custom['ad_id'] ?? ('1857' . str_pad($product->id, 6, '0', STR_PAD_LEFT));
        $views = $custom['views_past_hour'] ?? 12;
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
                    text: 'Check out this pre-owned {{ addslashes($product->title) }} for ₹{{ number_format($product->base_price / 100) }} on K71 Used Marketplace.',
                    url: window.location.href,
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Listing link copied to clipboard!');
            }
        }
    }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-slate-900 font-sans selection:bg-blue-600 selection:text-white pb-24 md:pb-12">

        <!-- ===================================================================
             1. BREADCRUMBS & TOP ACTIONS (BLUE & WHITE)
             =================================================================== -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 mb-5 border-b border-blue-100 text-xs font-semibold text-slate-500">
            <nav class="flex items-center gap-2" aria-label="Breadcrumb">
                <a href="{{ route('used.index') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                    <span>←</span>
                    <span>Back to Classifieds</span>
                </a>
                <span class="text-blue-300">/</span>
                <a href="{{ route('used.index') }}" class="text-blue-700 hover:underline">
                    Used Marketplace
                </a>
                @if($product->category)
                    <span class="text-blue-300">/</span>
                    <a href="{{ route('used.index', ['cat' => $product->category->slug]) }}" class="hover:text-blue-600 transition">
                        {{ $product->category->name }}
                    </a>
                @endif
                <span class="text-blue-300">/</span>
                <span class="text-slate-800 font-bold truncate max-w-[200px] sm:max-w-xs">{{ $product->title }}</span>
            </nav>

            <div class="flex items-center gap-2">
                <!-- Wishlist -->
                <button type="button" 
                        @click="toggleWish()" 
                        class="p-2 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/50 text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" :class="isItemWishlisted ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                    <span x-text="isItemWishlisted ? 'Saved' : 'Save Ad'"></span>
                </button>

                <!-- Share -->
                <button type="button" 
                        @click="shareListing()" 
                        class="p-2 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/50 text-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span>Share</span>
                </button>
            </div>
        </div>

        <!-- ===================================================================
             2. MAIN PRODUCT DETAIL LAYOUT: 2-COLUMN (GALLERY + SELLER CARD)
             =================================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-12">
            
            <!-- Left Column: Gallery, Specs & Description (Cols 7 or 8) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Image Gallery Card -->
                <div class="bg-white rounded-3xl border border-blue-100 shadow-sm overflow-hidden p-3 sm:p-4">
                    
                    <!-- Main Image Display with Zoom/Aspect -->
                    <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] bg-slate-950 rounded-2xl overflow-hidden flex items-center justify-center">
                        <img :src="images[activeImageIndex]" 
                             alt="{{ $product->title }}" 
                             class="w-full h-full object-contain select-none">

                        <!-- Badge Top-Left -->
                        <div class="absolute top-3 left-3 flex items-center gap-2 z-10">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-black text-[10px] uppercase tracking-wider shadow-sm">
                                👑 {{ $badge }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-xs text-white font-black text-[10px] uppercase tracking-wider shadow-sm">
                                {{ $condition }}
                            </span>
                        </div>

                        <!-- Image Counter Bottom-Right -->
                        <div class="absolute bottom-3 right-3 px-3 py-1 rounded-lg bg-black/70 backdrop-blur-xs text-white text-xs font-mono font-bold z-10">
                            <span x-text="(activeImageIndex + 1) + '/' + images.length"></span>
                        </div>

                        <!-- Carousel Prev / Next Controls -->
                        <template x-if="images.length > 1">
                            <div>
                                <button type="button" 
                                        @click="activeImageIndex = (activeImageIndex > 0) ? activeImageIndex - 1 : images.length - 1"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center transition">
                                    ‹
                                </button>
                                <button type="button" 
                                        @click="activeImageIndex = (activeImageIndex < images.length - 1) ? activeImageIndex + 1 : 0"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center transition">
                                    ›
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Thumbnails Navigation Strip -->
                    <template x-if="images.length > 1">
                        <div class="flex items-center gap-2 mt-3 overflow-x-auto no-scrollbar pb-1">
                            <template x-for="(thumb, idx) in images" :key="idx">
                                <button type="button" 
                                        @click="activeImageIndex = idx"
                                        :class="activeImageIndex === idx ? 'border-blue-600 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-400 opacity-70 hover:opacity-100'"
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 bg-slate-100 shrink-0 transition cursor-pointer">
                                    <img :src="thumb" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Product Specifications Card -->
                <div class="bg-white rounded-3xl border border-blue-100 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <span>📋</span>
                            <span>Item Specifications &amp; Overview</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">AD ID: {{ $adId }}</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <!-- Condition -->
                        <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100">
                            <span class="text-[10px] font-black uppercase text-blue-600 block">Condition</span>
                            <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $condition }}</span>
                        </div>

                        <!-- Brand -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Brand / Make</span>
                            <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $product->brand ?? 'Standard' }}</span>
                        </div>

                        <!-- Location -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Inspection Area</span>
                            <span class="font-extrabold text-slate-900 text-sm mt-0.5 block truncate">{{ explode(',', $location)[0] }}</span>
                        </div>

                        <!-- Bill & Box Available -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Original Bill / Box</span>
                            <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $custom['bill_available'] ?? 'Available' }}</span>
                        </div>

                        <!-- Physical Age -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Approx. Age</span>
                            <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ isset($custom['age_months']) ? $custom['age_months'] . ' Months' : 'Under 1 Year' }}</span>
                        </div>

                        <!-- Negotiable -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-black uppercase text-slate-400 block">Price Flexibility</span>
                            <span class="font-extrabold text-emerald-700 text-sm mt-0.5 block">{{ $isNegotiable ? 'Negotiable on Table' : 'Fixed Price' }}</span>
                        </div>

                        <!-- Category-Specific Specs -->
                        @if(!empty($specs))
                            @foreach($specs as $specKey => $specVal)
                                @if(!in_array(strtolower($specKey), ['condition', 'price', 'brand']))
                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] font-black uppercase text-slate-400 block">{{ $specKey }}</span>
                                        <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $specVal }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Detailed Description Card -->
                <div class="bg-white rounded-3xl border border-blue-100 shadow-sm p-6 space-y-3">
                    <h3 class="text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>📝</span>
                        <span>Seller's Description</span>
                    </h3>
                    <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line font-normal">
                        {{ $product->description }}
                    </div>
                </div>

                <!-- Safety Guidelines for Buyer -->
                <div class="rounded-3xl bg-blue-50/80 border border-blue-200/90 p-5 sm:p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🛡️</span>
                        <h4 class="text-sm font-black text-blue-950">Safety Advice for Buyers</h4>
                    </div>
                    <ul class="space-y-1.5 text-xs text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">•</span>
                            <span>Meet seller in person at a well-lit, public location (e.g. Kishangarh Bus Stand, Shivaji Nagar).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">•</span>
                            <span>Inspect device serial numbers, battery health, and test working condition before releasing payment.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">•</span>
                            <span>Never send advance payments or scan QR codes claiming to give money.</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Right Column: Sticky Pricing, Seller Card & Direct Actions (Cols 4 or 5) -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- Main Pricing & Action Card (Sticky on Desktop) -->
                <div class="bg-white rounded-3xl border border-blue-200 shadow-lg p-5 sm:p-6 space-y-5 lg:sticky lg:top-24">
                    
                    <!-- Asking Price & Negotiable tag -->
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Asking Price</span>
                            @if($isNegotiable)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase">
                                    Negotiable
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase">
                                    Fixed
                                </span>
                            @endif
                        </div>
                        <div class="text-3xl font-black text-slate-950 mt-1">
                            ₹ {{ number_format($product->base_price / 100) }}
                        </div>
                        <h1 class="text-sm font-bold text-slate-800 mt-2 leading-snug">
                            {{ $product->title }}
                        </h1>
                        <div class="text-xs text-slate-500 mt-2 flex items-center justify-between">
                            <span>📍 {{ $location }}</span>
                            <span>{{ $product->created_at ? $product->created_at->diffForHumans() : 'Recently' }}</span>
                        </div>
                    </div>

                    <!-- Direct Action Buttons -->
                    <div class="space-y-2.5 pt-2 border-t border-slate-100">
                        <!-- 1. Make an Offer Button (Triggers Global Offer Modal) -->
                        <button type="button" 
                                @click="$dispatch('open-offer-modal', { id: {{ $product->id }}, title: '{{ addslashes($product->title) }}', price: {{ $product->base_price / 100 }}, image: '{{ $primaryImg }}' })"
                                class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 hover:brightness-105 text-white text-xs sm:text-sm font-black tracking-wide shadow-md shadow-blue-600/25 transition flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                            <span class="text-base">🤝</span>
                            <span>Make an Offer</span>
                        </button>

                        <!-- 2. Chat with Seller -->
                        <button type="button" 
                                @click="chatModalOpen = true"
                                class="w-full py-3 rounded-2xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-800 text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer">
                            <span class="text-base">💬</span>
                            <span>Chat with Seller</span>
                        </button>

                        <!-- 3. Show Seller Contact Phone -->
                        <div>
                            <template x-if="!phoneRevealed">
                                <button type="button" 
                                        @click="phoneRevealed = true"
                                        class="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer">
                                    <span>📞 Show Contact Phone</span>
                                </button>
                            </template>
                            <template x-if="phoneRevealed">
                                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs font-bold text-emerald-900">
                                    <div class="flex items-center gap-2">
                                        <span>📞</span>
                                        <a href="tel:{{ $sellerPhone }}" class="text-sm font-black text-emerald-800 hover:underline">{{ $sellerPhone }}</a>
                                    </div>
                                    <a href="tel:{{ $sellerPhone }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black">Call Now</a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Verified Seller Profile Badge -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white font-black text-base flex items-center justify-center shadow-xs">
                                {{ $sellerInitial }}
                            </div>
                            <div>
                                <div class="text-xs font-black text-slate-900 flex items-center gap-1">
                                    <span>{{ $sellerName }}</span>
                                    <span class="text-blue-600 text-[11px]" title="Verified Seller">✓</span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">Member since 2026 • Verified</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[9px] font-black uppercase">
                                Verified
                            </span>
                        </div>
                    </div>

                    <!-- Report Listing Trigger -->
                    <div class="pt-2 text-center">
                        <button type="button" 
                                @click="reportModalOpen = true"
                                class="text-[11px] font-bold text-slate-400 hover:text-rose-600 transition cursor-pointer">
                            🚩 Report suspicious listing
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <!-- ===================================================================
             3. SIMILAR PRODUCTS CAROUSEL / GRID
             =================================================================== -->
        @if($similarItems->isNotEmpty())
            <section class="mt-8 pt-8 border-t border-blue-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                        Similar Pre-Owned Listings You Might Like
                    </h3>
                    <a href="{{ route('used.index') }}" class="text-xs font-bold text-blue-700 hover:underline">
                        View All Ads &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($similarItems as $sim)
                        @php
                            $simImg = $sim->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=400&q=80';
                        @endphp
                        <a href="{{ route('used.show', $sim->slug) }}" class="group bg-white rounded-2xl border border-slate-200/90 hover:border-blue-500 hover:shadow-md transition p-2.5 flex flex-col justify-between">
                            <div class="aspect-square rounded-xl bg-slate-100 overflow-hidden mb-2">
                                <img src="{{ $simImg }}" alt="{{ $sim->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-900">
                                    ₹ {{ number_format($sim->base_price / 100) }}
                                </div>
                                <div class="text-xs font-bold text-slate-700 truncate mt-0.5">
                                    {{ $sim->title }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    📍 {{ explode(',', $sim->custom_fields['location'] ?? 'Kishangarh')[0] }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- ===================================================================
             4. CHAT WITH SELLER MODAL
             =================================================================== -->
        <div x-show="chatModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
             x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
                 @click.outside="chatModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">💬</span>
                        <h3 class="text-base font-black text-slate-900">Message Seller</h3>
                    </div>
                    <button @click="chatModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                        ✕
                    </button>
                </div>

                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center text-sm shrink-0">
                        {{ $sellerInitial }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 truncate">{{ $sellerName }}</div>
                        <div class="text-[11px] text-blue-700 font-bold truncate">Re: {{ $product->title }}</div>
                    </div>
                </div>

                <form action="{{ route('messages.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="receiver_id" value="{{ $product->vendor->user_id ?? 1 }}">

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Your Message</label>
                        <textarea name="body" x-model="chatText" rows="3" required
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition cursor-pointer">
                            Send Message →
                        </button>
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $sellerPhone) }}?text={{ urlencode('Hi ' . $sellerName . ', I am interested in your ' . $product->title . ' on K71 Used.') }}" 
                           target="_blank"
                           class="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black transition flex items-center gap-1">
                            <span>WhatsApp</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===================================================================
             5. REPORT LISTING MODAL
             =================================================================== -->
        <div x-show="reportModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
             x-transition>
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4"
                 @click.outside="reportModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🚩</span>
                        <h3 class="text-base font-black text-slate-900">Report Listing</h3>
                    </div>
                    <button @click="reportModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                        ✕
                    </button>
                </div>

                <template x-if="!reportSubmitted">
                    <div class="space-y-3">
                        <p class="text-xs text-slate-600">
                            Help us keep K71 Used safe. What is wrong with this listing?
                        </p>

                        <div class="space-y-2 text-xs font-bold text-slate-800">
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                <input type="radio" name="reason" value="Fraud or Fake Item" x-model="reportReason" class="text-blue-600">
                                <span>Fraudulent or Fake item</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                <input type="radio" name="reason" value="Already Sold" x-model="reportReason" class="text-blue-600">
                                <span>Item is already sold</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                <input type="radio" name="reason" value="Wrong Price or Info" x-model="reportReason" class="text-blue-600">
                                <span>Inaccurate price or photos</span>
                            </label>
                        </div>

                        <button type="button" 
                                @click="reportSubmitted = true; setTimeout(() => { reportModalOpen = false; reportSubmitted = false; }, 2000)"
                                class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black transition cursor-pointer">
                            Submit Report
                        </button>
                    </div>
                </template>

                <template x-if="reportSubmitted">
                    <div class="py-6 text-center space-y-2">
                        <div class="text-3xl">✅</div>
                        <h4 class="text-sm font-black text-slate-900">Report Submitted</h4>
                        <p class="text-xs text-slate-500">Thank you for keeping our community safe. Our moderation team will review this listing within 2 hours.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- ===================================================================
             6. MOBILE STICKY ACTION BAR (Fixed at bottom on phones)
             =================================================================== -->
        <div class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 flex items-center justify-between gap-3 shadow-2xl">
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Asking Price</div>
                <div class="text-base font-black text-slate-950 truncate">₹ {{ number_format($product->base_price / 100) }}</div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="$dispatch('open-offer-modal', { id: {{ $product->id }}, title: '{{ addslashes($product->title) }}', price: {{ $product->base_price / 100 }}, image: '{{ $primaryImg }}' })"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black shadow-md shadow-blue-600/30">
                    🤝 Make Offer
                </button>
                <a href="tel:{{ $sellerPhone }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold">
                    📞 Call
                </a>
            </div>
        </div>

    </div>
</x-layouts.used>
