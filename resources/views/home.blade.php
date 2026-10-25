@php
    $appUrl = url('/');
    $appName = config('k71.name', 'K71');
    $hp = $homepageSettings ?? [];
    $seo = $seoSettings ?? [];

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $appUrl . '/#website',
                'url' => $appUrl,
                'name' => 'K71 Marketplace',
                'description' => 'India\'s Premier Direct Product Showcase & Curated Multi-Vertical Marketplace',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('catalog.index') . '?search={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ]
            ],
            [
                '@type' => 'Organization',
                '@id' => $appUrl . '/#organization',
                'name' => 'K71',
                'url' => $appUrl,
                'logo' => asset('images/k71-logo.png'),
            ]
        ]
    ];

    // Combine products for a rich catalog showcase
    $allCatalogProducts = $featuredProducts->merge($newArrivals)->unique('id');
@endphp

<x-layouts.public 
    :title="$seo['seo_meta_title'] ?? 'K71 • Direct Product Showcase & Deals Hub'"
    :metaDescription="$seo['seo_meta_description'] ?? 'Shop directly from verified Indian sellers: trending fashion, mobiles, electronics, artisan dining, certified services, and budget deals with PhonePe escrow protection.'"
    :schemaJson="$schemaData"
    :fullWidth="true"
>
    <!-- Background Clean Modern Canvas -->
    <div class="relative w-full overflow-hidden bg-[#F8F9FA] dark:bg-zinc-950 text-slate-900 dark:text-zinc-100">

        <!-- ==========================================
             0. FLIPKART & MEESHO TOP CATEGORY SLIDER (STICKY & RESPONSIVE)
             ========================================== -->
        <section class="w-full bg-white dark:bg-zinc-900 border-b border-slate-200/80 dark:border-zinc-800 shadow-xs backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2.5 sm:py-3">
                <div class="flex items-center gap-3.5 sm:gap-6 overflow-x-auto no-scrollbar scroll-smooth py-1 px-1 touch-pan-x" 
                     style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                    
                    <!-- 1. All Catalog (K71Mart) -->
                    <a href="{{ route('catalog.index') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            🛍️
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-slate-900 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">HOT</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">All Deals</span>
                    </a>

                    <!-- 2. Mobiles & Tech -->
                    <a href="{{ route('catalog.index', ['search' => 'phone']) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            📱
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-slate-800 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">5G</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Mobiles</span>
                    </a>

                    <!-- 3. Fashion & Apparel -->
                    <a href="{{ route('catalog.index', ['search' => 'saree']) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            👗
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-rose-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">SALE</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Fashion</span>
                    </a>

                    <!-- 4. Electronics -->
                    <a href="{{ route('catalog.index', ['search' => 'laptop']) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            💻
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Electronics</span>
                    </a>

                    <!-- 5. Cloud Dining & Food -->
                    <a href="{{ k71_vertical_url('food') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            🍲
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-slate-800 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">FRESH</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Dining</span>
                    </a>

                    <!-- 6. Professional Services -->
                    <a href="{{ k71_vertical_url('services') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            ⚡
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-slate-800 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">PRO</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Services</span>
                    </a>

                    <!-- 7. Pre-Owned Marketplace -->
                    <a href="{{ k71_vertical_url('used') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            ♻️
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-slate-800 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">USED</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Pre-Owned</span>
                    </a>

                    <!-- 8. B2B Wholesale RFQs -->
                    <a href="{{ k71_vertical_url('enquiry') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            📋
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-slate-800 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">B2B</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Wholesale</span>
                    </a>

                    <!-- 9. Shoppable Video Reels -->
                    <a href="{{ k71_vertical_url('shorts') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                            ▶
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-rose-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">LIVE</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Reels</span>
                    </a>

                    <!-- Real Database Categories -->
                    @if(!empty($categories))
                        @foreach($categories->take(6) as $cat)
                            <a href="{{ route('catalog.index', ['category' => $cat->slug]) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[70px] text-center cursor-pointer">
                                <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700/80 shadow-xs flex items-center justify-center text-lg overflow-hidden group-hover:scale-105 group-hover:border-slate-900 group-hover:shadow-sm transition-all duration-200 relative">
                                    @if($cat->sample_image)
                                        <img src="{{ \App\Services\ImageService::url($cat->sample_image, 120) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-slate-700 dark:text-zinc-300 font-bold text-xs uppercase">{{ substr($cat->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <span class="text-[11px] sm:text-xs font-semibold text-slate-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">{{ $cat->name }}</span>
                            </a>
                        @endforeach
                    @endif

                </div>
            </div>
        </section>

        <div class="relative z-10 space-y-10 sm:space-y-14 pb-20 sm:pb-28">

            <!-- ==========================================
                 1. HIGH-IMPACT PROMOTIONAL PRODUCT HERO (AMAZON & MEESHO STYLE)
                 ========================================== -->
            <section class="pt-4 sm:pt-6 max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-stretch">
                    
                    @php
                        $carouselSlides = [
                            [
                                'badge' => $hp['slide1_badge'] ?? ($hp['hero_badge'] ?? '🔥 Direct Factory Prices • Lowest Wholesale Rates'),
                                'title' => $hp['slide1_title'] ?? 'Direct Products. Factory Direct Prices.',
                                'subtitle' => $hp['slide1_subtitle'] ?? ($hp['hero_description'] ?? 'Shop trending fashion, smartphones, home living, cloud dining, and marble craft directly from verified Indian sellers with PhonePe escrow protection.'),
                                'btn1_text' => $hp['slide1_btn1_text'] ?? ($hp['hero_cta_primary_text'] ?? 'Browse All Products'),
                                'btn1_link' => $hp['slide1_btn1_link'] ?? ($hp['hero_cta_primary_link'] ?? '#product-showcase'),
                                'btn2_text' => $hp['slide1_btn2_text'] ?? '₹199 Budget Store',
                                'btn2_link' => $hp['slide1_btn2_link'] ?? '/catalog?sort=price_low',
                                'image' => !empty($hp['slide1_image']) ? asset('storage/' . $hp['slide1_image']) : (!empty($hp['slide1_image_url']) ? $hp['slide1_image_url'] : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1400&q=80'),
                            ],
                            [
                                'badge' => $hp['slide2_badge'] ?? '⚡ Cloud Kitchen Dining • 30 Min Express',
                                'title' => $hp['slide2_title'] ?? 'Authentic Flavors. Delivered Hot & Fresh.',
                                'subtitle' => $hp['slide2_subtitle'] ?? 'Experience royal Nawabi thalis, aromatic dum biryanis, gourmet chef treats, and hygienic contactless doorstep delivery.',
                                'btn1_text' => $hp['slide2_btn1_text'] ?? 'Order Food Online',
                                'btn1_link' => $hp['slide2_btn1_link'] ?? '/food',
                                'btn2_text' => $hp['slide2_btn2_text'] ?? 'View Restaurants',
                                'btn2_link' => $hp['slide2_btn2_link'] ?? '/food',
                                'image' => !empty($hp['slide2_image']) ? asset('storage/' . $hp['slide2_image']) : (!empty($hp['slide2_image_url']) ? $hp['slide2_image_url'] : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1400&q=80'),
                            ],
                            [
                                'badge' => $hp['slide3_badge'] ?? '💎 Artisanal Craft & Certified Home Pro',
                                'title' => $hp['slide3_title'] ?? 'Makrana Marble & Pro Verified Services.',
                                'subtitle' => $hp['slide3_subtitle'] ?? 'Quarry-direct marble slabs, temple carvings, diamond polishing, and verified home professionals with milestone payment security.',
                                'btn1_text' => $hp['slide3_btn1_text'] ?? 'Explore Services',
                                'btn1_link' => $hp['slide3_btn1_link'] ?? '/services',
                                'btn2_text' => $hp['slide3_btn2_text'] ?? 'Submit B2B Enquiry',
                                'btn2_link' => $hp['slide3_btn2_link'] ?? '/enquiry',
                                'image' => !empty($hp['slide3_image']) ? asset('storage/' . $hp['slide3_image']) : (!empty($hp['slide3_image_url']) ? $hp['slide3_image_url'] : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80'),
                            ]
                        ];
                    @endphp

                    <!-- Main High-Converting Hero 3-Slide Carousel (8 cols) -->
                    <div class="lg:col-span-8 rounded-2xl sm:rounded-3xl text-white shadow-md relative overflow-hidden border border-slate-900/10 min-h-[380px] sm:min-h-[440px] flex flex-col justify-between group"
                         x-data="{
                            currentSlide: 0,
                            totalSlides: 3,
                            timer: null,
                            startAutoplay() {
                                this.timer = setInterval(() => { this.nextSlide(); }, 6000);
                            },
                            stopAutoplay() {
                                if (this.timer) clearInterval(this.timer);
                            },
                            nextSlide() {
                                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                            },
                            prevSlide() {
                                this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                            },
                            goToSlide(idx) {
                                this.currentSlide = idx;
                            }
                         }"
                         x-init="startAutoplay()"
                         @mouseenter="stopAutoplay()"
                         @mouseleave="startAutoplay()">
                        
                        @foreach($carouselSlides as $idx => $slide)
                            <div x-show="currentSlide === {{ $idx }}"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-300 absolute inset-0"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute inset-0 w-full h-full p-6 sm:p-10 flex flex-col justify-between z-10"
                                 style="{{ $idx > 0 ? 'display: none;' : '' }}">
                                
                                <!-- Background Image with Clean Neutral Scrim -->
                                <img src="{{ $slide['image'] }}" 
                                     alt="{{ $slide['title'] }}" 
                                     class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none -z-20">
                                <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/25 sm:to-transparent -z-10 pointer-events-none"></div>

                                <!-- Top Tagline & Trust Chip -->
                                <div class="relative z-10 flex flex-wrap items-center gap-2">
                                    <span class="px-3 py-1 rounded-full bg-white/20 text-white text-[10px] sm:text-xs font-bold tracking-wide shadow-sm backdrop-blur-md border border-white/20">
                                        {{ $slide['badge'] }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full bg-black/40 text-zinc-200 text-[10px] sm:text-xs font-medium backdrop-blur-md border border-white/10 hidden sm:inline-block">
                                        ⚡ Verified Sellers &amp; Escrow Protection
                                    </span>
                                </div>

                                <!-- Hero Main Headline -->
                                <div class="relative z-10 my-4 sm:my-6 space-y-2 sm:space-y-3">
                                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-[1.12] text-white drop-shadow-sm">
                                        {{ $slide['title'] }}
                                    </h1>
                                    <p class="text-xs sm:text-sm lg:text-base text-zinc-200 max-w-xl font-normal leading-relaxed drop-shadow-sm">
                                        {{ $slide['subtitle'] }}
                                    </p>
                                </div>

                                <!-- Action Buttons & Trust Badges -->
                                <div class="relative z-10 space-y-4">
                                    <div class="flex flex-wrap items-center gap-3">
                                        @if(!empty($slide['btn1_text']))
                                            <a href="{{ $slide['btn1_link'] }}" 
                                               class="px-5 sm:px-7 py-3 sm:py-3.5 rounded-xl bg-white hover:bg-slate-100 text-slate-950 font-black text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                                                <span>{{ $slide['btn1_text'] }}</span>
                                                <span>&rarr;</span>
                                            </a>
                                        @endif
                                        @if(!empty($slide['btn2_text']))
                                            <a href="{{ $slide['btn2_link'] }}" 
                                               class="px-5 sm:px-7 py-3 sm:py-3.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs sm:text-sm tracking-wide border border-white/25 backdrop-blur-md active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                                                <span>{{ $slide['btn2_text'] }}</span>
                                                <span>↗</span>
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Trust Mini Badges -->
                                    <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-[10px] sm:text-xs text-zinc-300 font-medium pt-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-black">✓</span>
                                            <span>Direct Verified Indian Sellers</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-black">✓</span>
                                            <span>100% Secure Escrow Protection</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach

                        <!-- Carousel Controls: Left & Right Chevrons -->
                        <button type="button" 
                                @click="prevSlide()" 
                                aria-label="Previous Slide"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-black/70 text-white backdrop-blur-md border border-white/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 z-20 cursor-pointer shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" 
                                @click="nextSlide()" 
                                aria-label="Next Slide"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-black/70 text-white backdrop-blur-md border border-white/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 z-20 cursor-pointer shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <!-- Carousel Indicators / Dots (Bottom Center) -->
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-20">
                            @foreach($carouselSlides as $idx => $slide)
                                <button type="button" 
                                        @click="goToSlide({{ $idx }})" 
                                        aria-label="Go to slide {{ $idx + 1 }}"
                                        :class="currentSlide === {{ $idx }} ? 'w-7 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                                        class="h-2 rounded-full transition-all duration-300 cursor-pointer"></button>
                            @endforeach
                        </div>

                    </div>

                    <!-- Right Column: 2 Quick-Pick Product Tiles (4 cols) -->
                    <div class="lg:col-span-4 grid grid-cols-2 lg:grid-cols-1 gap-3.5 sm:gap-4">
                        @php 
                            $tileProd1 = $featuredProducts->first() ?? $newArrivals->first();
                            $tileProd2 = $featuredProducts->skip(1)->first() ?? $newArrivals->skip(1)->first();
                        @endphp

                        <!-- Tile 1 -->
                        @if($tileProd1)
                        <a href="{{ route('product.show', $tileProd1->slug) }}" 
                           class="group p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-200 font-bold text-[9px] uppercase tracking-wider">
                                    ⭐ Trending Pick
                                </span>
                                @if($tileProd1->compare_at_price > $tileProd1->base_price)
                                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-black text-[9px]">
                                        {{ round((($tileProd1->compare_at_price - $tileProd1->base_price) / $tileProd1->compare_at_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800 mb-2 relative">
                                @if($tileProd1->primary_image_url)
                                    <img src="{{ $tileProd1->primary_image_url }}" alt="{{ $tileProd1->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🛍️</div>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $tileProd1->title }}</h4>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-sm font-black text-slate-950 dark:text-white">{{ $tileProd1->formatted_base_price }}</span>
                                    @if($tileProd1->compare_at_price > $tileProd1->base_price)
                                        <span class="text-[10px] text-slate-400 line-through">{{ $tileProd1->formatted_compare_at_price }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endif

                        <!-- Tile 2 -->
                        @if($tileProd2)
                        <a href="{{ route('product.show', $tileProd2->slug) }}" 
                           class="group p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-200 font-bold text-[9px] uppercase tracking-wider">
                                    ✨ Best Value
                                </span>
                                @if($tileProd2->compare_at_price > $tileProd2->base_price)
                                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-black text-[9px]">
                                        {{ round((($tileProd2->compare_at_price - $tileProd2->base_price) / $tileProd2->compare_at_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800 mb-2 relative">
                                @if($tileProd2->primary_image_url)
                                    <img src="{{ $tileProd2->primary_image_url }}" alt="{{ $tileProd2->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">👗</div>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $tileProd2->title }}</h4>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-sm font-black text-slate-950 dark:text-white">{{ $tileProd2->formatted_base_price }}</span>
                                    @if($tileProd2->compare_at_price > $tileProd2->base_price)
                                        <span class="text-[10px] text-slate-400 line-through">{{ $tileProd2->formatted_compare_at_price }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endif

                    </div>

                </div>
            </section>

            <!-- ==========================================
                 2. MEESHO & AMAZON SIGNATURE: SHOP BY BUDGET HUB
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🏷️</span>
                            <h2 class="text-base sm:text-xl font-black text-slate-950 dark:text-white tracking-tight">Shop by Budget &amp; Deals</h2>
                        </div>
                        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-slate-700 dark:text-zinc-300 hover:text-black dark:hover:text-white flex items-center gap-1">
                            <span>All Price Stores</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <!-- 4 Budget Corners Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        
                        <!-- 1. Under 199 Store -->
                        <a href="{{ route('catalog.index', ['max_price' => 199]) }}" 
                           class="group p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50/80 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700/80 hover:border-slate-900 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition-all duration-200">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-700 text-slate-900 dark:text-white border border-slate-200/80 dark:border-zinc-600 flex items-center justify-center text-sm mb-2 font-black shadow-xs">
                                ₹
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Daily Value</span>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-950 dark:text-white mt-0.5 group-hover:text-black">Under ₹199 Store</h3>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1 line-clamp-1">Everyday essentials &amp; accessories</p>
                        </a>

                        <!-- 2. Under 499 Store -->
                        <a href="{{ route('catalog.index', ['max_price' => 499]) }}" 
                           class="group p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50/80 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700/80 hover:border-slate-900 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition-all duration-200">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-700 text-slate-900 dark:text-white border border-slate-200/80 dark:border-zinc-600 flex items-center justify-center text-sm mb-2 font-black shadow-xs">
                                👗
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Popular</span>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-950 dark:text-white mt-0.5 group-hover:text-black">Under ₹499 Store</h3>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1 line-clamp-1">Trending fashion &amp; gadget picks</p>
                        </a>

                        <!-- 3. Under 999 Store -->
                        <a href="{{ route('catalog.index', ['max_price' => 999]) }}" 
                           class="group p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50/80 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700/80 hover:border-slate-900 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition-all duration-200">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-700 text-slate-900 dark:text-white border border-slate-200/80 dark:border-zinc-600 flex items-center justify-center text-sm mb-2 font-black shadow-xs">
                                💎
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Boutiques</span>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-950 dark:text-white mt-0.5 group-hover:text-black">Under ₹999 Store</h3>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1 line-clamp-1">Marble handicrafts &amp; ethnic wear</p>
                        </a>

                        <!-- 4. Flat 50-80% Off Clearance -->
                        <a href="{{ route('catalog.index', ['sort' => 'discount']) }}" 
                           class="group p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50/80 dark:bg-zinc-800/80 border border-slate-200/80 dark:border-zinc-700/80 hover:border-slate-900 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition-all duration-200">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-700 text-slate-900 dark:text-white border border-slate-200/80 dark:border-zinc-600 flex items-center justify-center text-sm mb-2 font-black shadow-xs">
                                🔥
                            </div>
                            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Steal Deals</span>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-950 dark:text-white mt-0.5 group-hover:text-black">Flat 50-80% OFF</h3>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1 line-clamp-1">Verified factory clearance stock</p>
                        </a>

                    </div>
                </div>
            </section>

            <!-- ==========================================
                 3. UNIVERSAL DYNAMIC SEARCH DOCK (FAST & COMPACT)
                 ========================================== -->
            <section class="max-w-4xl mx-auto px-3 sm:px-6">
                <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs relative"
                     x-data="{
                        query: '',
                        results: [],
                        loading: false,
                        isOpen: false,
                        fetchSuggestions() {
                            if(this.query.trim().length < 2) {
                                this.results = [];
                                this.isOpen = false;
                                return;
                            }
                            this.loading = true;
                            fetch('/api/search/suggest?q=' + encodeURIComponent(this.query))
                                .then(r => r.json())
                                .then(data => {
                                    this.results = data.results || [];
                                    this.isOpen = true;
                                    this.loading = false;
                                })
                                .catch(() => { this.loading = false; });
                        }
                     }"
                     @click.away="isOpen = false">
                    
                    <!-- Search Input Form -->
                    <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                        <div class="relative flex-1 w-full flex items-center">
                            <div class="pl-3.5 text-slate-400">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" x-model="query"
                                   @input.debounce.250ms="fetchSuggestions()"
                                   @focus="if(results.length > 0) isOpen = true"
                                   autocomplete="off"
                                   placeholder="Search 10,000+ products, fashion, mobiles, cloud dining, services..."
                                   class="w-full px-3.5 py-3 bg-transparent text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none">
                            
                            <div x-show="loading" class="pr-3 text-slate-400" style="display: none;">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full sm:w-auto px-7 py-3 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs sm:text-sm tracking-wide shadow-xs active:scale-95 transition shrink-0 cursor-pointer">
                            Search Products
                        </button>
                    </form>

                    <!-- Live Dynamic Suggestions Dropdown -->
                    <div x-show="isOpen && (results.length > 0 || loading)" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xl overflow-hidden z-50 divide-y divide-slate-100 dark:divide-zinc-800"
                         style="display: none;">
                        
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3.5 p-3 hover:bg-slate-50 dark:hover:bg-zinc-800 transition group">
                                <img :src="item.thumb" :alt="item.title" class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-zinc-700 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-black" x-text="item.title"></p>
                                    <div class="flex items-center gap-2.5 text-[11px] text-slate-500 mt-0.5">
                                        <span class="font-mono font-bold text-slate-900 dark:text-zinc-100" x-text="item.price"></span>
                                        <span class="uppercase px-1.5 py-0.2 rounded bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 font-bold text-[9px]" x-text="item.vertical"></span>
                                        <span class="truncate text-slate-400" x-text="item.vendor"></span>
                                    </div>
                                </div>
                                <span class="text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white group-hover:translate-x-1 transition-all text-xs font-bold pr-2">&rarr;</span>
                            </a>
                        </template>

                        <div x-show="results.length > 0" class="p-2.5 bg-slate-50/90 dark:bg-zinc-800/90 text-center">
                            <a :href="'{{ route('catalog.index') }}?search=' + encodeURIComponent(query)" class="text-xs font-bold text-slate-800 dark:text-zinc-200 hover:text-black">
                                See all matching items for "<span x-text="query"></span>" &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Trending Search Keywords -->
                    <div class="pt-2.5 px-2 pb-0.5 border-t border-slate-100 dark:border-zinc-800 mt-2 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">Trending:</span>
                        <a href="{{ route('catalog.index', ['search' => 'silk']) }}" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px] font-medium hover:bg-slate-200 transition">Silk Sarees</a>
                        <a href="{{ route('catalog.index', ['search' => 'phone']) }}" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px] font-medium hover:bg-slate-200 transition">5G Mobiles</a>
                        <a href="{{ k71_vertical_url('food') }}" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px] font-medium hover:bg-slate-200 transition">Biryani &amp; Sweets</a>
                        <a href="{{ k71_vertical_url('services') }}" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px] font-medium hover:bg-slate-200 transition">Marble Polishing</a>
                        <a href="{{ k71_vertical_url('used') }}" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px] font-medium hover:bg-slate-200 transition">Used iPhones</a>
                    </div>
                </div>
            </section>


            <!-- ==========================================
                 5. MAIN DIRECT PRODUCT SHOWCASE (MEESHO / AMAZON ULTRA-RESPONSIVE GRID)
                 ========================================== -->
            <section id="product-showcase" class="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8 space-y-6"
                     x-data="{
                        activeTab: 'all',
                        filterProducts(itemCategory, itemPrice, isDeal) {
                            if (this.activeTab === 'all') return true;
                            if (this.activeTab === 'deals') return isDeal;
                            if (this.activeTab === 'under499') return itemPrice <= 49900;
                            if (this.activeTab === 'fashion') return (itemCategory || '').toLowerCase().includes('fashion') || (itemCategory || '').toLowerCase().includes('clothing') || (itemCategory || '').toLowerCase().includes('saree') || (itemCategory || '').toLowerCase().includes('apparel');
                            if (this.activeTab === 'tech') return (itemCategory || '').toLowerCase().includes('tech') || (itemCategory || '').toLowerCase().includes('mobile') || (itemCategory || '').toLowerCase().includes('electronic') || (itemCategory || '').toLowerCase().includes('laptop');
                            return true;
                        }
                     }">
                
                <!-- Header with Quick-Filter Pills -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pb-3 border-b border-slate-200/80 dark:border-zinc-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-black text-[9px] uppercase tracking-wider">
                                DIRECT SHOWCASE
                            </span>
                            <span class="text-xs text-slate-500 font-bold">1-Tap Add to Bag</span>
                        </div>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-950 dark:text-white tracking-tight mt-1">
                            Explore Trending Products
                        </h2>
                    </div>

                    <!-- Category Filter Tabs (Instant Alpine filtering) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 text-xs font-bold" style="scrollbar-width: none;">
                        <button type="button" @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            All Items
                        </button>
                        <button type="button" @click="activeTab = 'deals'"
                                :class="activeTab === 'deals' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            🔥 Top Deals
                        </button>
                        <button type="button" @click="activeTab = 'under499'"
                                :class="activeTab === 'under499' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            ⚡ Under ₹499
                        </button>
                        <button type="button" @click="activeTab = 'fashion'"
                                :class="activeTab === 'fashion' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            👗 Fashion
                        </button>
                        <button type="button" @click="activeTab = 'tech'"
                                :class="activeTab === 'tech' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            📱 Mobiles &amp; Tech
                        </button>
                    </div>
                </div>

                <!-- ULTRA-RESPONSIVE PRODUCT GRID: 2-COL MOBILE, 3-4 COL TABLET, 4-5 COL DESKTOP -->
                @if($allCatalogProducts->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-2.5 sm:gap-4 lg:gap-5">
                        @foreach($allCatalogProducts as $prod)
                            @php
                                $catSlug = $prod->category?->slug ?? '';
                                $catName = $prod->category?->name ?? '';
                                $hasDiscount = ($prod->compare_at_price > $prod->base_price);
                            @endphp
                            <div x-show="filterProducts('{{ addslashes($catName . ' ' . $catSlug) }}', {{ $prod->base_price }}, {{ $hasDiscount ? 'true' : 'false' }})"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100">
                                <x-product-card :product="$prod" />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-10 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 text-center">
                        <p class="text-xs sm:text-sm text-slate-500">Products are loading directly from verified boutiques.</p>
                    </div>
                @endif

                <!-- View All Full Catalog Button -->
                <div class="text-center pt-4">
                    <a href="{{ route('catalog.index') }}" 
                       class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs sm:text-sm tracking-wide shadow-xs active:scale-95 transition">
                        <span>Load More Products</span>
                        <span>&rarr;</span>
                    </a>
                </div>

            </section>

            <!-- ==========================================
                 6. CLOUD DINING & RESTAURANTS (CLEAN VISUAL TILES)
                 ========================================== -->
            @if($foodRestaurants->isNotEmpty() || $featuredFood->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🍲</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-slate-950 dark:text-white tracking-tight leading-tight">Artisan Kitchens &amp; Dining</h2>
                            <p class="text-[11px] text-slate-500">Hygiene-certified cloud kitchens with fresh doorstep dispatch</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('food') }}" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-zinc-100 hover:underline flex items-center gap-1">
                        <span>All Menus</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach(($foodRestaurants->isNotEmpty() ? $foodRestaurants : $featuredFood)->take(4) as $item)
                        @php
                            $isVendor = $item instanceof \App\Models\Vendor;
                            $rTitle = $isVendor ? $item->shop_name : $item->title;
                            $rImg = $isVendor ? ($item->logo_url ?: $item->cover_url) : $item->primary_image_url;
                            $rUrl = $isVendor ? route('food.restaurant', $item->slug) : ($item->vendor ? route('food.restaurant', $item->vendor->slug) : k71_vertical_url('food'));
                        @endphp
                        <a href="{{ $rUrl }}" class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-video overflow-hidden bg-slate-100 dark:bg-zinc-800">
                                @if($rImg)
                                    <img src="{{ format_image_url($rImg) }}" alt="{{ $rTitle }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🍲</div>
                                @endif
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-900 text-white text-[9px] font-black uppercase tracking-wider">
                                    Fresh
                                </span>
                            </div>
                            <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-xs sm:text-sm text-slate-950 dark:text-white group-hover:text-black transition truncate">{{ $rTitle }}</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Express delivery in 30-45 mins</p>
                                </div>
                                <div class="pt-2 mt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold text-slate-900 dark:text-zinc-100">
                                    <span>View Menu</span>
                                    <span>&rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 7. VERIFIED SERVICES & SPECIALISTS (VISUAL GIG CARDS)
                 ========================================== -->
            @if($featuredServices->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⚡</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-slate-950 dark:text-white tracking-tight leading-tight">Verified Services &amp; Specialists</h2>
                            <p class="text-[11px] text-slate-500">Marble restoration, web engineering, photo shoots &amp; home repairs</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('services') }}" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-zinc-100 hover:underline flex items-center gap-1">
                        <span>All Services</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach($featuredServices->take(4) as $service)
                        @php
                            $basicPkg = $service->gigPackages->firstWhere('tier', 'basic') ?? $service->gigPackages->first();
                            $startingPrice = $basicPkg ? $basicPkg->price : ($service->base_price / 100);
                        @endphp
                        <a href="{{ route('services.show', $service->slug) }}" class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-video overflow-hidden bg-slate-100 dark:bg-zinc-800">
                                <img src="{{ $service->primary_image_url }}" alt="{{ $service->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-900 text-white text-[9px] font-black uppercase tracking-wider">
                                    Vetted
                                </span>
                            </div>
                            <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 block truncate">{{ $service->vendor?->shop_name ?? 'Specialist' }}</span>
                                    <h3 class="font-bold text-xs sm:text-sm text-slate-950 dark:text-white group-hover:text-black transition truncate mt-0.5">{{ $service->title }}</h3>
                                </div>
                                <div class="pt-2 mt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                                    <span class="text-xs font-black text-slate-950 dark:text-white">From ₹{{ number_format($startingPrice) }}</span>
                                    <span class="text-[11px] font-bold text-slate-900 dark:text-zinc-100">Book &rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 8. VERIFIED PRE-OWNED DEALS (RE-COMMERCE)
                 ========================================== -->
            @if($featuredUsed->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">♻️</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-slate-950 dark:text-white tracking-tight leading-tight">Pre-Owned Inspected Deals</h2>
                            <p class="text-[11px] text-slate-500">Certified second-hand smartphones, laptops, machinery &amp; vehicles</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('used') }}" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-zinc-100 hover:underline flex items-center gap-1">
                        <span>All Used Deals</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($featuredUsed->take(4) as $item)
                        <a href="{{ route('used.show', $item->slug) }}" class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-square overflow-hidden bg-slate-100 dark:bg-zinc-800">
                                <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-900 text-white text-[9px] font-black uppercase tracking-wider">
                                    {{ $item->getCustomField('condition', 'Inspected') }}
                                </span>
                            </div>
                            <div class="p-3">
                                <h4 class="text-xs font-bold text-slate-950 dark:text-white truncate group-hover:text-black transition">{{ $item->title }}</h4>
                                <div class="text-sm font-black text-slate-950 dark:text-white mt-1">{{ $item->formatted_base_price }}</div>
                                <span class="text-[10px] text-slate-400 block mt-0.5">📍 {{ $item->getCustomField('city', 'India') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 9. SHOPPABLE STORIES & REELS (9:16 VIDEO ROW)
                 ========================================== -->
            @if($latestShorts->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">▶</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-slate-950 dark:text-white tracking-tight leading-tight">Shoppable Video Stories</h2>
                            <p class="text-[11px] text-slate-500">Watch products in motion with 1-tap checkout</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('shorts') }}" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-zinc-100 hover:underline flex items-center gap-1">
                        <span>Watch Reels Feed</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                    @foreach($latestShorts->take(6) as $short)
                        <a href="{{ route('shorts.show', $short->id) }}" class="group relative aspect-[9/16] rounded-2xl overflow-hidden bg-slate-950 shadow-xs hover:shadow-md transition-all duration-200 block">
                            @if($short->thumbnail_url)
                                <img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white text-3xl">▶</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-transparent to-black/20 p-3 flex flex-col justify-between text-white">
                                <span class="self-start px-2 py-0.5 rounded-full bg-rose-600 text-[8px] font-black uppercase tracking-wider">
                                    LIVE
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold line-clamp-2 leading-snug drop-shadow-sm">{{ $short->title }}</h4>
                                    <span class="text-[10px] text-zinc-300 mt-1 block">Watch &amp; Buy &rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 10. B2B FACTORY REQUIREMENTS & RFQS
                 ========================================== -->
            @if($featuredEnquiries->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📋</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-slate-950 dark:text-white tracking-tight leading-tight">Live B2B &amp; Factory Enquiries</h2>
                            <p class="text-[11px] text-slate-500">Post requirement or quote on verified wholesale buyers</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('enquiry.create') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-xs">
                            + Post RFQ
                        </a>
                        <a href="{{ k71_vertical_url('enquiry') }}" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-zinc-100 hover:underline">
                            All RFQs &rarr;
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($featuredEnquiries->take(4) as $enq)
                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                                    <span>#{{ $enq->enquiry_number }}</span>
                                    <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 font-bold uppercase">{{ $enq->status }}</span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-950 dark:text-white mt-1.5 line-clamp-2">{{ $enq->title }}</h4>
                                <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-2">
                                    <span>Qty: <strong class="text-slate-800 dark:text-zinc-200">{{ $enq->quantity }}</strong></span>
                                    <span>•</span>
                                    <span>📍 {{ $enq->city }}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-950 dark:text-white">{{ $enq->formatted_budget }}</span>
                                <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-900 dark:text-white font-bold text-[11px] transition">
                                    Quote &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 11. SELLER ONBOARDING CALLOUT
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="p-6 sm:p-10 rounded-2xl sm:rounded-3xl bg-slate-950 text-white shadow-md relative overflow-hidden border border-slate-900 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-2 text-center md:text-left">
                        <span class="px-3 py-1 rounded-full bg-white/10 text-zinc-300 text-[10px] font-bold uppercase tracking-widest border border-white/10">
                            Indian Merchant Program
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            Start Selling Direct on K71 Marketplace
                        </h3>
                        <p class="text-xs sm:text-sm text-zinc-400 max-w-lg leading-relaxed">
                            Zero setup fee for retail boutiques, dining kitchens, verified specialists, pre-owned sellers, and marble factories with automated daily PhonePe settlements.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0 w-full md:w-auto">
                        <a href="{{ route('vendor.register') }}" 
                           class="w-full md:w-auto px-7 py-3.5 rounded-xl bg-white hover:bg-zinc-100 text-slate-950 font-black text-xs sm:text-sm tracking-wide shadow-xs text-center transition active:scale-95">
                            Open Your Store &rarr;
                        </a>
                        <button type="button" 
                                onclick="if(window.openAuthModal) { window.openAuthModal(null, 'login', 'seller'); } else { window.location.href = '{{ route('login') }}'; }"
                                class="w-full md:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs sm:text-sm border border-white/15 text-center transition">
                            Seller Login
                        </button>
                    </div>
                </div>
            </section>

            <!-- ==========================================
                 12. TRUST & BUYER PROTECTION PILLARS
                 ========================================== -->
            <section class="max-w-6xl mx-auto px-3 sm:px-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs text-center">
                        <span class="text-2xl block mb-1">🛡️</span>
                        <h4 class="text-xs font-bold text-slate-950 dark:text-white">Verified Merchants</h4>
                        <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">GST &amp; Aadhaar Vetted</p>
                    </div>
                    <div class="p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs text-center">
                        <span class="text-2xl block mb-1">⚡</span>
                        <h4 class="text-xs font-bold text-slate-950 dark:text-white">PhonePe Escrow</h4>
                        <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">100% Safe Settlements</p>
                    </div>
                    <div class="p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs text-center">
                        <span class="text-2xl block mb-1">📦</span>
                        <h4 class="text-xs font-bold text-slate-950 dark:text-white">Fast Dispatch</h4>
                        <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">Express Pan-India Logistics</p>
                    </div>
                    <div class="p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-xs text-center">
                        <span class="text-2xl block mb-1">💬</span>
                        <h4 class="text-xs font-bold text-slate-950 dark:text-white">Direct Chat</h4>
                        <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">WhatsApp &amp; Live In-App DM</p>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-layouts.public>
