<x-layouts.enquiry 
    title="K71 Enquiry — India's Leading B2B Sourcing & Factory Quotation Marketplace"
    metaDescription="Discover verified suppliers, post bulk buying requirements, compare factory quotations, and do business directly with verified manufacturers on K71 Enquiry."
>
    <div x-data="k71EnquiryHub()" class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- 1. GRAIL OBSIDIAN B2B HERO & SMART SEARCH DOCK -->
        <section class="grail-hero-band relative pt-8 sm:pt-12 pb-12 sm:pb-16 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <!-- Subtle Orchid/Gold Aura -->
            <div class="absolute top-0 inset-x-0 h-96 pointer-events-none overflow-hidden" aria-hidden="true">
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[720px] h-[300px] bg-gradient-to-b from-[#ECD2F3]/12 via-[#C9A24A]/6 to-transparent blur-3xl rounded-full"></div>
            </div>

            <div class="max-w-7xl mx-auto space-y-6 relative z-10">

                <!-- Breadcrumb & Sourcing Hub Tag -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-[#EFE9F2] font-semibold backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="grail-eyebrow !text-[#C9A24A]">B2B Sourcing Hub</span>
                        <span class="text-white/25">•</span>
                        <span>{{ number_format($stats['total_rfqs'] ?? 42) }}+ Active Buyer Requirements</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.register') }}" class="text-[#ECD2F3] hover:text-[#FFCDFF] font-semibold text-xs flex items-center gap-1 transition">
                            <span>Become a Verified Supplier</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Minimal Editorial Headline & Mission -->
                <div class="max-w-3xl space-y-2.5">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-light text-white tracking-tight leading-tight" style="font-family: var(--font-grail-display);">
                        Find suppliers. Compare offers. Do business.
                    </h1>
                    <p class="text-sm sm:text-base text-[#EFE9F2]/75 font-normal leading-relaxed">
                        The next-generation B2B enquiry marketplace. Connect directly with verified manufacturers, request customized bulk quotations, and trade with complete transparency.
                    </p>
                </div>

                <!-- 2. PREMIUM DARK GLASS SEARCH PANEL -->
                <div class="grail-dark-card rounded-3xl p-3 sm:p-4 border border-white/15 backdrop-blur-2xl shadow-2xl">
                    <form action="{{ route('enquiry.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center">
                        
                        <!-- Search Keyword Input (5 cols) -->
                        <div class="md:col-span-5 relative">
                            <div class="absolute left-3.5 top-3.5 text-[#ECD2F3]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="Enter product, machine, or service (e.g. Makrana Marble, Gangsaw, CNC)..."
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white/10 border border-white/15 text-xs sm:text-sm font-medium text-white focus:bg-white/15 focus:border-[#ECD2F3] focus:outline-none transition-all placeholder:text-white/45">
                        </div>

                        <!-- Category Selector (3 cols) -->
                        <div class="md:col-span-3 relative">
                            <select name="cat" class="w-full px-4 py-3 rounded-2xl bg-white/10 border border-white/15 text-xs sm:text-sm font-semibold text-white focus:border-[#ECD2F3] outline-none transition-all cursor-pointer">
                                <option value="" class="bg-[#0D0D0D] text-white">All B2B Categories</option>
                                @foreach($enquiryCategories as $cat)
                                    <option value="{{ $cat->slug }}" class="bg-[#0D0D0D] text-white" {{ $selectedCategory === $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location Selector (2 cols) -->
                        <div class="md:col-span-2 relative">
                            <select name="city" class="w-full px-3 py-3 rounded-2xl bg-white/10 border border-white/15 text-xs sm:text-sm font-semibold text-white focus:border-[#ECD2F3] outline-none transition-all cursor-pointer">
                                <option value="" class="bg-[#0D0D0D] text-white">All Locations</option>
                                @foreach($availableCities as $c)
                                    <option value="{{ $c }}" class="bg-[#0D0D0D] text-white" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                                <option value="Jaipur" class="bg-[#0D0D0D] text-white">Jaipur</option>
                                <option value="Makrana" class="bg-[#0D0D0D] text-white">Makrana</option>
                                <option value="Kishangarh" class="bg-[#0D0D0D] text-white">Kishangarh</option>
                                <option value="Mumbai" class="bg-[#0D0D0D] text-white">Mumbai</option>
                                <option value="Delhi" class="bg-[#0D0D0D] text-white">Delhi NCR</option>
                                <option value="Ahmedabad" class="bg-[#0D0D0D] text-white">Ahmedabad</option>
                            </select>
                        </div>

                        <!-- Action Buttons (2 cols) -->
                        <div class="md:col-span-2 flex items-center gap-2">
                            <button type="submit" 
                                    class="grail-cta-pill w-full py-3 text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer max-h-11 active:scale-95">
                                <span>Search</span>
                            </button>
                        </div>

                    </form>

                    <!-- Popular Sourcing Chips + Quick RFQ CTA Strip -->
                    <div class="mt-3 pt-3 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <span class="text-[#C9A24A] font-bold uppercase text-[10px] tracking-[0.14em] shrink-0">Trending:</span>
                            @foreach(['Makrana Marble', 'Diamond Blades', 'Granite Slabs', 'CNC Machines', 'Packaging Material', 'Quartz Stone'] as $chip)
                                <a href="{{ route('enquiry.index', ['search' => $chip]) }}" 
                                   class="px-3 py-1 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 border border-white/15 hover:border-[#ECD2F3]/40 text-[#EFE9F2]/85 hover:text-[#FFCDFF] text-xs font-medium transition shrink-0">
                                    {{ $chip }}
                                </a>
                            @endforeach
                        </div>

                        <button type="button" 
                                @click="openRfqModal()" 
                                class="px-4 py-2 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] font-bold text-xs shadow-sm active:scale-95 transition flex items-center gap-1.5 cursor-pointer shrink-0">
                            <span>✨ Post Your Requirement</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3. CURVED EDITORIAL BODY SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-28 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">

                <!-- A. CATEGORY DISCOVERY SLIDER & STRIP -->
                <section id="categories" class="space-y-4" data-grail-reveal x-data="{
                    scrollLeft() { $refs.catSlider.scrollBy({ left: -280, behavior: 'smooth' }); },
                    scrollRight() { $refs.catSlider.scrollBy({ left: 280, behavior: 'smooth' }); }
                }">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-[0.12em] bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3]">
                                    Sourcing Categories
                                </span>
                                <span class="text-xs text-[#1A1018]/60 font-mono">Wholesale &amp; Real Estate</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight mt-1" style="font-family: var(--font-grail-display);">Key Industry &amp; Real Estate Sectors</h2>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if($selectedCategory)
                                <a href="{{ route('enquiry.index') }}" class="text-xs font-bold text-[#623562] hover:underline mr-2">Clear Filter ✕</a>
                            @endif
                            <button type="button" @click="scrollLeft()" class="p-2 rounded-full bg-white border border-[#E6E2E8] text-[#1A1018] hover:bg-[#EFE9F2] transition shadow-2xs active:scale-95" aria-label="Previous categories">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="scrollRight()" class="p-2 rounded-full bg-white border border-[#E6E2E8] text-[#1A1018] hover:bg-[#EFE9F2] transition shadow-2xs active:scale-95" aria-label="Next categories">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Smooth Horizontal Slider -->
                    <div x-ref="catSlider" class="flex items-stretch gap-3.5 sm:gap-4 overflow-x-auto no-scrollbar scroll-smooth pb-2 pt-1">
                        @php
                            $sampleCategoryIcons = [
                                'real-estate' => '🏡',
                                'property' => '🏢',
                                'natural-stone' => '🏛️',
                                'machinery' => '⚙️',
                                'construction' => '🏗️',
                                'minerals' => '💎',
                                'chemicals' => '🧪',
                                'packaging' => '📦',
                            ];
                        @endphp

                        @forelse($enquiryCategories as $cat)
                            <a href="{{ route('enquiry.index', ['cat' => $cat->slug]) }}" 
                               class="w-48 sm:w-56 shrink-0 p-4 rounded-3xl grail-card bg-white border transition-all duration-300 group flex flex-col justify-between shadow-2xs hover:shadow-xl {{ $selectedCategory === $cat->slug ? 'border-[#623562] ring-2 ring-[#ECD2F3]' : 'border-[#E6E2E8] hover:border-[#623562]/40' }}">
                                <div class="w-12 h-12 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-[#623562] flex items-center justify-center text-2xl font-bold mb-3 group-hover:scale-110 transition-transform">
                                    @if($cat->image)
                                        <img src="{{ format_image_url($cat->image) }}" alt="{{ $cat->name }}" class="w-7 h-7 object-contain">
                                    @else
                                        <span>{{ $sampleCategoryIcons[$cat->slug] ?? (str_contains(strtolower($cat->name), 'real estate') ? '🏡' : '🏭') }}</span>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-xs sm:text-sm font-bold text-[#1A1018] group-hover:text-[#623562] transition-colors leading-snug">
                                        {{ $cat->name }}
                                    </h3>
                                    <p class="text-[11px] text-[#1A1018]/65 font-semibold mt-1 flex items-center justify-between">
                                        <span>{{ $cat->enquiries_count }} RFQs</span>
                                        <span class="text-[#623562] font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full py-8 text-center text-[#1A1018]/45 text-xs">
                                No dedicated categories configured yet. All general wholesale requirements are supported.
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- B. RECOMMENDED B2B WHOLESALE PRODUCTS -->
                <section class="space-y-4" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Recommended Industrial &amp; Wholesale Listings</h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-normal">Direct factory-priced products ready for bulk dispatch and custom quotation.</p>
                        </div>
                        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-[#623562] hover:text-[#1A1018] transition flex items-center gap-1">
                            <span>Browse Catalog</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @forelse($b2bProducts as $prod)
                            <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] hover:border-[#623562]/40 shadow-xs transition-all duration-200 flex flex-col justify-between group overflow-hidden">
                                <div>
                                    <!-- Image Preview -->
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#FAFAF8]">
                                        <img src="{{ $prod->primary_image_url }}" 
                                             alt="{{ $prod->title }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        
                                        <div class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-black/80 text-[#EFE9F2] text-[9.5px] font-bold uppercase tracking-wider border border-white/10">
                                            {{ $prod->category?->name ?? 'Wholesale' }}
                                        </div>

                                        @if($prod->vendor)
                                            <div class="absolute bottom-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-white/95 border border-[#E6E2E8] text-[#1A1018] text-[10px] font-semibold shadow-xs flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span class="truncate max-w-[130px]">{{ $prod->vendor->shop_name }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Product Details -->
                                    <div class="p-3.5 space-y-1.5">
                                        <h3 class="text-xs sm:text-sm font-semibold text-[#1A1018] group-hover:text-[#623562] transition-colors line-clamp-2 leading-snug min-h-[2.4rem]">
                                            <a href="{{ route('product.show', $prod->slug) }}">
                                                {{ $prod->title }}
                                            </a>
                                        </h3>

                                        <div class="flex items-baseline gap-2">
                                            <span class="text-base font-bold text-[#1A1018]">
                                                ₹{{ number_format($prod->base_price / 100, 2) }}
                                            </span>
                                            <span class="text-[10px] text-[#1A1018]/50 font-semibold uppercase">
                                                {{ $prod->custom_fields['unit'] ?? ($prod->isMarble() ? 'Per Sq Ft' : 'Per Unit') }}
                                            </span>
                                        </div>

                                        <!-- Specs Snippet / MOQ -->
                                        <div class="text-[11px] text-[#1A1018]/65 line-clamp-1 leading-normal flex items-center gap-2">
                                            <span class="font-medium bg-[#EFE9F2] text-[#1A1018] px-2 py-0.5 rounded-full text-[10px]">MOQ: {{ $prod->custom_fields['moq'] ?? '50 Units' }}</span>
                                            <span class="font-medium">Direct Dispatch</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Action Cluster -->
                                <div class="p-3.5 pt-3 border-t border-[#E6E2E8] flex flex-col gap-1.5">
                                    <button type="button" 
                                            @click="openRfqModal('{{ addslashes($prod->title) }}', {{ $prod->vendor_id ?? 'null' }}, {{ $prod->id }})"
                                            class="grail-cta-pill w-full py-2.5 text-xs font-bold text-center transition cursor-pointer active:scale-[0.98]">
                                        Get Best Price
                                    </button>
                                    <a href="{{ route('product.show', $prod->slug) }}" 
                                       class="w-full py-1.5 rounded-full bg-[#FAFAF8] hover:bg-[#EFE9F2] border border-[#E6E2E8] text-[#1A1018]/80 text-[11px] font-semibold text-center transition">
                                        View Specs &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-[#1A1018]/45 text-xs">
                                No wholesale products matching this filter. Post your requirement below to get direct quotes from suppliers.
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- C. TRUSTED SUPPLIER DISCOVERY -->
                @if($verifiedSuppliers->isNotEmpty())
                <section id="suppliers" class="space-y-4" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Verified Suppliers &amp; Manufacturers</h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-normal">Pre-audited verified businesses with direct contact and quoting capabilities.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        @foreach($verifiedSuppliers as $sup)
                            <div class="grail-card p-4 rounded-2xl bg-white border border-[#E6E2E8] hover:border-[#623562]/40 shadow-xs transition-all flex flex-col justify-between group">
                                <div>
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-12 h-12 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8] overflow-hidden shrink-0">
                                            <img src="{{ $sup->logo ? format_image_url($sup->logo) : asset('images/default-avatar.png') }}" 
                                                 alt="{{ $sup->shop_name }}" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                                <h3 class="text-sm font-bold text-[#1A1018] truncate">{{ $sup->shop_name }}</h3>
                                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[9.5px] font-bold uppercase shrink-0">
                                                    ✓ Verified • GST
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-[#1A1018]/65 flex items-center gap-1">
                                                <span>📍</span>
                                                <span class="truncate">{{ $sup->city ?? 'Jaipur' }}, {{ $sup->state ?? 'India' }}</span>
                                            </p>
                                            <div class="flex items-center gap-2 mt-1.5 text-[10.5px]">
                                                <span class="bg-[#EFE9F2] text-[#1A1018] px-2 py-0.5 rounded-full font-medium">MOQ: 50+ Units</span>
                                                <span class="text-[#1A1018]/60 font-medium">Bulk Rates: Factory Direct</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 mt-3 border-t border-[#E6E2E8] flex flex-col gap-1.5">
                                    <button type="button" 
                                            @click="openRfqModal('', {{ $sup->id }})"
                                            class="grail-cta-pill w-full py-2.5 text-xs font-bold text-center transition cursor-pointer active:scale-[0.98]">
                                        Get Best Price
                                    </button>
                                    <a href="{{ $sup->storefront_url }}" 
                                       class="w-full py-1.5 rounded-full bg-[#FAFAF8] hover:bg-[#EFE9F2] border border-[#E6E2E8] text-[#1A1018]/80 text-[11px] font-semibold text-center transition">
                                        View Storefront &amp; Catalog &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- D. LIVE BUYER REQUIREMENTS & RFQ BOARD -->
                <section class="space-y-4" data-grail-reveal>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5" style="font-family: var(--font-grail-display);">
                                <span>Open Buyer Requirements &amp; Leads</span>
                                <span class="text-xs font-sans px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">Live Board</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-normal">Direct RFQs posted by verified commercial buyers waiting for supplier quotations.</p>
                        </div>

                        <button type="button" 
                                @click="openRfqModal()" 
                                class="grail-btn-obsidian px-4 py-2.5 text-xs transition self-start sm:self-auto cursor-pointer active:scale-95">
                            + Post Requirement Free
                        </button>
                    </div>

                    <!-- Enquiries List / Table -->
                    <div class="grail-card bg-white rounded-3xl border border-[#E6E2E8] shadow-xs divide-y divide-[#E6E2E8] overflow-hidden">
                        @forelse($enquiries as $enq)
                            <div class="p-5 sm:p-6 hover:bg-[#FAFAF8] transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-2 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[10px] font-mono font-bold text-[#1A1018] bg-[#EFE9F2] px-2 py-0.5 rounded-md">
                                            {{ $enq->enquiry_number }}
                                        </span>
                                        <span class="text-xs font-bold text-[#623562] bg-[#F8EFFA] border border-[#ECD2F3] px-2.5 py-0.5 rounded-full">
                                            {{ $enq->category?->name ?? 'General Sourcing' }}
                                        </span>
                                        @if($enq->urgency === 'immediate')
                                            <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                                ⚡ Immediate Need
                                            </span>
                                        @endif
                                        <span class="text-xs text-[#1A1018]/55">
                                            Posted {{ $enq->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <h3 class="text-base sm:text-lg font-bold text-[#1A1018] hover:text-[#623562] transition leading-snug">
                                        <a href="{{ route('enquiry.show', $enq->enquiry_number) }}">
                                            {{ $enq->title }}
                                        </a>
                                    </h3>

                                    <p class="text-xs text-[#1A1018]/65 line-clamp-2 max-w-3xl leading-relaxed">
                                        {{ $enq->description }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#1A1018]/60 font-medium pt-1">
                                        <span>📦 Quantity: <strong class="text-[#1A1018]">{{ $enq->quantity }}</strong></span>
                                        <span>📍 Delivery: <strong class="text-[#1A1018]">{{ $enq->city }}</strong></span>
                                        @if($enq->target_budget)
                                            <span>💰 Target: <strong class="text-emerald-700">₹{{ number_format($enq->target_budget / 100, 2) }}</strong></span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right CTA -->
                                <div class="flex flex-wrap sm:flex-col items-start sm:items-end justify-between sm:justify-center gap-2 shrink-0 pt-2 sm:pt-0">
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 block whitespace-nowrap">
                                            💬 {{ $enq->quotes->count() }} Quotes Submitted
                                        </span>
                                    </div>
                                    <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" 
                                       class="grail-btn-obsidian px-4 sm:px-5 py-2 sm:py-2.5 text-xs transition whitespace-nowrap">
                                        Submit Quotation &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center space-y-3">
                                <div class="text-3xl">📋</div>
                                <h4 class="text-base font-bold text-[#1A1018]">No matching requirements found</h4>
                                <p class="text-xs text-[#1A1018]/55 max-w-md mx-auto">Be the first to submit a requirement or adjust your keyword and location search.</p>
                                <button type="button" @click="openRfqModal()" class="grail-btn-obsidian px-5 py-2.5 text-xs">
                                    Post New Requirement
                                </button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($enquiries->hasPages())
                        <div class="pt-4">
                            {{ $enquiries->links() }}
                        </div>
                    @endif
                </section>

                <!-- E. HOW IT WORKS (3 SIMPLE STEPS) -->
                <section class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-8 sm:p-12 shadow-xs" data-grail-reveal>
                    <div class="text-center max-w-xl mx-auto mb-10 space-y-2">
                        <span class="grail-eyebrow !text-[#C9A24A]">Seamless Sourcing Journey</span>
                        <h2 class="text-2xl sm:text-4xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">How K71 B2B Works</h2>
                        <p class="text-xs sm:text-sm text-[#1A1018]/60">Connecting bulk buyers and verified manufacturers in 3 transparent steps.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 relative">
                        <!-- Step 1 -->
                        <div class="space-y-3 text-center sm:text-left p-6 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8]">
                            <div class="w-12 h-12 rounded-2xl bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center font-bold text-lg shadow-xs mx-auto sm:mx-0">
                                1
                            </div>
                            <h3 class="text-base font-bold text-[#1A1018]">Discover or Post Requirement</h3>
                            <p class="text-xs text-[#1A1018]/65 leading-relaxed">
                                Search wholesale catalog products or submit your customized RFQ with volume, delivery destination, and specs.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="space-y-3 text-center sm:text-left p-6 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8]">
                            <div class="w-12 h-12 rounded-2xl bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center font-bold text-lg shadow-xs mx-auto sm:mx-0">
                                2
                            </div>
                            <h3 class="text-base font-bold text-[#1A1018]">Compare Factory Quotations</h3>
                            <p class="text-xs text-[#1A1018]/65 leading-relaxed">
                                Verified manufacturers submit competitive structured quotations detailing unit prices, MOQ, and lead times.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="space-y-3 text-center sm:text-left p-6 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8]">
                            <div class="w-12 h-12 rounded-2xl bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center font-bold text-lg shadow-xs mx-auto sm:mx-0">
                                3
                            </div>
                            <h3 class="text-base font-bold text-[#1A1018]">Connect &amp; Close Deals</h3>
                            <p class="text-xs text-[#1A1018]/65 leading-relaxed">
                                Chat directly via direct messaging or phone with zero commission middlemen or inflated price tags.
                            </p>
                        </div>
                    </div>
                </section>

            </div>
        </div>

        <!-- 4. INTERACTIVE POST REQUIREMENT (RFQ) EDITORIAL MODAL -->
        <div x-show="rfqModalOpen" 
             x-cloak
             @keydown.escape.window="rfqModalOpen = false"
             class="fixed inset-0 z-[105] flex items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-md">
            
            <div @click.outside="rfqModalOpen = false"
                 class="w-full max-w-xl bg-[#FAFAF8] text-[#1A1018] rounded-[28px] border border-[#E6E2E8] shadow-2xl overflow-hidden relative max-h-[92vh] overflow-y-auto no-scrollbar">

                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-6 py-5 flex items-center justify-between border-b border-white/10">
                    <div>
                        <span class="grail-eyebrow block">RFQ SOURCING WIZARD</span>
                        <h3 class="text-xl font-light text-white tracking-tight mt-0.5" style="font-family: var(--font-grail-display);">Post Your Requirement</h3>
                    </div>
                    <button type="button" @click="rfqModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-[#EFE9F2] flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <div class="p-6 sm:p-8">
                    <!-- Form Error Banner -->
                    <div x-show="rfqError" x-cloak class="mb-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                        <span x-text="rfqError"></span>
                    </div>

                    <form @submit.prevent="submitRfqForm()" class="space-y-4">
                        <input type="hidden" name="vendor_id" x-model="rfqVendorId">
                        <input type="hidden" name="product_id" x-model="rfqProductId">

                        <!-- Product / Requirement Title -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Product or Requirement Needed *</label>
                            <input type="text" 
                                   x-model="rfqTitle" 
                                   placeholder="e.g. 5,000 Sq.Ft Makrana Pure White Marble Slabs 18mm"
                                   required
                                   class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm">
                        </div>

                        <!-- Category & Urgency -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Category</label>
                                <select x-model="rfqCategoryId" class="grail-input-light w-full px-3 py-2.5 text-xs">
                                    <option value="">Select Category</option>
                                    @foreach($enquiryCategories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Requirement Timeline</label>
                                <select x-model="rfqUrgency" class="grail-input-light w-full px-3 py-2.5 text-xs">
                                    <option value="immediate">Immediate (Ready stock needed)</option>
                                    <option value="within_1_week">Within 1-2 Weeks</option>
                                    <option value="flexible" selected>Flexible / Planning Phase</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quantity & Target Budget -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Quantity &amp; Unit *</label>
                                <input type="text" 
                                       x-model="rfqQuantity" 
                                       placeholder="e.g. 5,000 Sq.Ft or 25 Metric Tonnes"
                                       required
                                       class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Target Budget (Optional ₹)</label>
                                <input type="number" 
                                       x-model="rfqBudget" 
                                       placeholder="e.g. 250000"
                                       class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm">
                            </div>
                        </div>

                        <!-- Delivery City & Pincode -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Delivery Destination / City *</label>
                                <input type="text" 
                                       x-model="rfqCity" 
                                       placeholder="e.g. Jaipur, Delhi, Mumbai Port"
                                       required
                                       class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Destination Pincode</label>
                                <input type="text" 
                                       x-model="rfqPincode" 
                                       placeholder="e.g. 302001"
                                       class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm">
                            </div>
                        </div>

                        <!-- Specifications / Notes -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-[#1A1018]/80 uppercase tracking-[0.12em]">Technical Specifications &amp; Requirements *</label>
                            <textarea rows="3" 
                                      x-model="rfqDescription" 
                                      placeholder="Describe required grades, finish, dimensions, packing, or certification details..."
                                      required
                                      class="grail-input-light w-full px-4 py-2.5 text-xs"></textarea>
                        </div>

                        <!-- Buyer Contact Info -->
                        <div class="p-4 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] space-y-3">
                            <div class="text-[11px] font-bold text-[#623562] uppercase tracking-[0.12em]">Your Buyer Contact Information</div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <input type="text" 
                                       x-model="rfqBuyerName" 
                                       placeholder="Your Full Name *"
                                       required
                                       class="grail-input-light w-full px-3 py-2 text-xs bg-white">
                                
                                <input type="tel" 
                                       x-model="rfqBuyerPhone" 
                                       placeholder="Mobile / WhatsApp *"
                                       required
                                       class="grail-input-light w-full px-3 py-2 text-xs bg-white">
                                
                                <input type="email" 
                                       x-model="rfqBuyerEmail" 
                                       placeholder="Work Email"
                                       class="grail-input-light w-full px-3 py-2 text-xs bg-white">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-2 flex items-center justify-end gap-2.5">
                            <button type="button" 
                                    @click="rfqModalOpen = false" 
                                    class="px-5 py-2.5 rounded-full text-xs font-bold text-[#1A1018]/70 hover:bg-[#EFE9F2] transition">
                                Cancel
                            </button>

                            <button type="submit" 
                                    :disabled="rfqSubmitting" 
                                    class="grail-btn-obsidian px-5 py-2.5 font-bold text-xs uppercase tracking-wider transition flex items-center gap-2 cursor-pointer disabled:opacity-50 max-h-11 active:scale-95">
                                <span x-show="!rfqSubmitting">Submit Requirement &amp; Get Quotes &rarr;</span>
                                <span x-show="rfqSubmitting">Posting Requirement...</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Sticky Mobile & Tablet Enquiry Bar -->
        <div class="fixed bottom-14 md:bottom-4 inset-x-0 z-40 px-3 sm:px-6 pointer-events-none">
            <div class="max-w-4xl mx-auto bg-[#0D0D0D]/95 backdrop-blur-xl text-[#EFE9F2] rounded-2xl p-3 sm:p-3.5 shadow-2xl border border-white/15 flex items-center justify-between gap-3 pointer-events-auto">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-white truncate">Need customized factory pricing or bulk quotes?</p>
                    <p class="text-[11px] text-[#EFE9F2]/65 truncate">Connect with verified suppliers across India with direct RFQs</p>
                </div>
                <button type="button" 
                        @click="openRfqModal()" 
                        class="grail-cta-pill shrink-0 px-4 py-2 text-xs font-bold transition shadow-xs cursor-pointer active:scale-95">
                    + Post RFQ
                </button>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function k71EnquiryHub() {
        return {
            rfqModalOpen: false,
            rfqSubmitting: false,
            rfqError: '',
            rfqTitle: '',
            rfqCategoryId: '',
            rfqUrgency: 'flexible',
            rfqQuantity: '',
            rfqBudget: '',
            rfqCity: 'Jaipur',
            rfqPincode: '',
            rfqDescription: '',
            rfqBuyerName: '{{ auth()->user()?->name ?? '' }}',
            rfqBuyerPhone: '{{ auth()->user()?->phone ?? '' }}',
            rfqBuyerEmail: '{{ auth()->user()?->email ?? '' }}',
            rfqVendorId: null,
            rfqProductId: null,

            openRfqModal(title = '', vendorId = null, productId = null) {
                this.rfqTitle = title;
                this.rfqVendorId = vendorId;
                this.rfqProductId = productId;
                this.rfqError = '';
                this.rfqModalOpen = true;
            },

            async submitRfqForm() {
                this.rfqSubmitting = true;
                this.rfqError = '';

                // If user is not logged in and needs authentication
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const res = await fetch('/enquiry', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            title: this.rfqTitle,
                            category_id: this.rfqCategoryId || null,
                            urgency: this.rfqUrgency,
                            quantity: this.rfqQuantity,
                            target_budget: this.rfqBudget ? parseFloat(this.rfqBudget) : null,
                            city: this.rfqCity,
                            pincode: this.rfqPincode || null,
                            description: this.rfqDescription,
                            buyer_name: this.rfqBuyerName,
                            buyer_phone: this.rfqBuyerPhone,
                            buyer_email: this.rfqBuyerEmail,
                            vendor_id: this.rfqVendorId,
                            product_id: this.rfqProductId
                        })
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.rfqModalOpen = false;
                        if (window.showToast) {
                            window.showToast('Requirement posted! RFQ: ' + data.enquiry_number + ' 🎉');
                        }
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 800);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        this.rfqError = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Could not submit requirement.');
                    }
                } catch(e) {
                    console.error(e);
                    this.rfqError = 'Network error while submitting requirement. Please try again.';
                } finally {
                    this.rfqSubmitting = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-layouts.enquiry>
