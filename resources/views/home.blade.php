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

    $allCatalogProducts = $featuredProducts->merge($newArrivals)->unique('id');

    $carouselSlides = [
        [
            'badge' => $hp['slide1_badge'] ?? ($hp['hero_badge'] ?? 'Direct Factory Prices • Lowest Wholesale Rates'),
            'title' => $hp['slide1_title'] ?? 'Direct Products. Factory Direct Prices.',
            'subtitle' => $hp['slide1_subtitle'] ?? ($hp['hero_description'] ?? 'Shop trending fashion, smartphones, home living, cloud dining, and marble craft directly from verified Indian sellers with PhonePe escrow protection.'),
            'btn1_text' => $hp['slide1_btn1_text'] ?? ($hp['hero_cta_primary_text'] ?? 'Browse All Products'),
            'btn1_link' => $hp['slide1_btn1_link'] ?? ($hp['hero_cta_primary_link'] ?? '#latest-products'),
            'btn2_text' => $hp['slide1_btn2_text'] ?? '₹199 Budget Store',
            'btn2_link' => $hp['slide1_btn2_link'] ?? '/catalog?sort=price_low',
            'image' => !empty($hp['slide1_image']) ? asset('storage/' . $hp['slide1_image']) : (!empty($hp['slide1_image_url']) ? $hp['slide1_image_url'] : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1400&q=80'),
            'kicker' => 'Chapter 01 • Direct Mart',
        ],
        [
            'badge' => $hp['slide2_badge'] ?? 'Cloud Kitchen Dining • 30 Min Express',
            'title' => $hp['slide2_title'] ?? 'Authentic Flavors. Delivered Hot & Fresh.',
            'subtitle' => $hp['slide2_subtitle'] ?? 'Experience royal Nawabi thalis, aromatic dum biryanis, gourmet chef treats, and hygienic contactless doorstep delivery.',
            'btn1_text' => $hp['slide2_btn1_text'] ?? 'Order Food Online',
            'btn1_link' => $hp['slide2_btn1_link'] ?? '/food',
            'btn2_text' => $hp['slide2_btn2_text'] ?? 'View Restaurants',
            'btn2_link' => $hp['slide2_btn2_link'] ?? '/food',
            'image' => !empty($hp['slide2_image']) ? asset('storage/' . $hp['slide2_image']) : (!empty($hp['slide2_image_url']) ? $hp['slide2_image_url'] : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1400&q=80'),
            'kicker' => 'Chapter 02 • Feasto Dining',
        ],
        [
            'badge' => $hp['slide3_badge'] ?? 'Artisanal Craft & Certified Home Pro',
            'title' => $hp['slide3_title'] ?? 'Makrana Marble & Pro Verified Services.',
            'subtitle' => $hp['slide3_subtitle'] ?? 'Quarry-direct marble slabs, temple carvings, diamond polishing, and verified home professionals with milestone payment security.',
            'btn1_text' => $hp['slide3_btn1_text'] ?? 'Explore Services',
            'btn1_link' => $hp['slide3_btn1_link'] ?? '/services',
            'btn2_text' => $hp['slide3_btn2_text'] ?? 'Submit B2B Enquiry',
            'btn2_link' => $hp['slide3_btn2_link'] ?? '/enquiry',
            'image' => !empty($hp['slide3_image']) ? asset('storage/' . $hp['slide3_image']) : (!empty($hp['slide3_image_url']) ? $hp['slide3_image_url'] : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80'),
            'kicker' => 'Chapter 03 • Pro & Quarry',
        ]
    ];

    $displayLatest = (isset($latestProducts) && $latestProducts->isNotEmpty())
        ? $latestProducts
        : (isset($allCatalogProducts) ? $allCatalogProducts->take(20) : collect());

    $hasAnyVerticalSection = (
        $foodRestaurants->isNotEmpty()
        || $featuredFood->isNotEmpty()
        || $featuredServices->isNotEmpty()
        || $featuredUsed->isNotEmpty()
        || $featuredEnquiries->isNotEmpty()
    );

    $seq = $scrollSequence ?? \App\Services\ScrollSequenceService::resolveSequence($hp['scroll_seq_folder'] ?? null);
    $seqEnabled = array_key_exists('scroll_seq_enabled', $hp) ? (bool) $hp['scroll_seq_enabled'] : true;
    $seqFrames = $seq['frames'] ?? [];
    $defaultHeightVh = count($seqFrames) >= 150 ? 360 : 290;
    $seqHeightVh = max(180, min(450, (int) (($hp['scroll_seq_height_vh'] ?? 0) ?: $defaultHeightVh)));
    $seqSmoothing = max(0.04, min(0.35, (float) (($hp['scroll_seq_smoothing'] ?? 0) ?: 0.12)));
    $seqBeats = [
        [
            'kicker' => trim((string) ($hp['scroll_seq_beat1_kicker'] ?? '')) ?: 'Chapter I • Quarry & Craft',
            'title' => trim((string) ($hp['scroll_seq_beat1_title'] ?? '')) ?: 'Sculpted in Stone. Revealed in Motion.',
            'desc' => trim((string) ($hp['scroll_seq_beat1_desc'] ?? '')) ?: 'Every contour unfolds frame by frame as you scroll through K71 curated Indian craftsmanship and direct-factory finds.',
        ],
        [
            'kicker' => trim((string) ($hp['scroll_seq_beat2_kicker'] ?? '')) ?: 'Chapter II • Material Provenance',
            'title' => trim((string) ($hp['scroll_seq_beat2_title'] ?? '')) ?: 'Pure Veins & Authentic Workshop Polish.',
            'desc' => trim((string) ($hp['scroll_seq_beat2_desc'] ?? '')) ?: 'Inspect luminous surfaces, handloom weaves, and artisan detail directly from verified regional ateliers.',
        ],
        [
            'kicker' => trim((string) ($hp['scroll_seq_beat3_kicker'] ?? '')) ?: 'Chapter III • Direct Dispatch',
            'title' => trim((string) ($hp['scroll_seq_beat3_title'] ?? '')) ?: 'Own the Genuine Piece Direct.',
            'desc' => trim((string) ($hp['scroll_seq_beat3_desc'] ?? '')) ?: 'Backed by PhonePe escrow settlements, GST-vetted merchants, and insured Pan-India delivery.',
        ],
    ];
@endphp

<x-layouts.public
    :title="$seo['seo_meta_title'] ?? 'K71 • Direct Product Showcase & Deals Hub'"
    :metaDescription="$seo['seo_meta_description'] ?? 'Shop directly from verified Indian sellers: trending fashion, mobiles, electronics, artisan dining, certified services, and budget deals with PhonePe escrow protection.'"
    :schemaJson="$schemaData"
    :fullWidth="true"
    :grailTheme="true"
>
    <div class="grail-home-root relative w-full" data-grail-page>

        <!-- =========================================================
             0. TOP VERTICAL & CATEGORY RIBBON (OBSIDIAN SURFACE)
             ========================================================= -->
        <section class="w-full bg-[#060606] border-b border-white/10" data-pill-zone="dark">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center gap-3.5 sm:gap-5 overflow-x-auto no-scrollbar scroll-smooth py-1 touch-pan-x">

                    <!-- All Deals -->
                    <a href="{{ route('catalog.index') }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            🛍️
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[8px] font-bold uppercase tracking-wider">HOT</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">All Deals</span>
                    </a>

                    <!-- Mobiles -->
                    <a href="{{ route('catalog.index', ['search' => 'phone']) }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            📱
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-[#C9A24A] text-black text-[8px] font-bold uppercase tracking-wider">5G</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Mobiles</span>
                    </a>

                    <!-- Fashion -->
                    <a href="{{ route('catalog.index', ['search' => 'saree']) }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            👗
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-[#FFCDFF] text-[#1A1018] text-[8px] font-bold uppercase tracking-wider">SALE</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Fashion</span>
                    </a>

                    <!-- Electronics -->
                    <a href="{{ route('catalog.index', ['search' => 'laptop']) }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            💻
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Electronics</span>
                    </a>

                    <!-- Cloud Dining -->
                    <a href="{{ k71_vertical_url('food') }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            🍲
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-white/15 border border-white/20 text-[#EFE9F2] text-[8px] font-bold uppercase tracking-wider">FRESH</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Dining</span>
                    </a>

                    <!-- Services -->
                    <a href="{{ k71_vertical_url('services') }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            ⚡
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-white/15 border border-white/20 text-[#C9A24A] text-[8px] font-bold uppercase tracking-wider">PRO</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Services</span>
                    </a>

                    <!-- Pre-Owned -->
                    <a href="{{ k71_vertical_url('used') }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            ♻️
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-white/15 border border-white/20 text-[#EFE9F2] text-[8px] font-bold uppercase tracking-wider">USED</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Pre-Owned</span>
                    </a>

                    <!-- B2B Wholesale -->
                    <a href="{{ k71_vertical_url('enquiry') }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            📋
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-white/15 border border-white/20 text-[#EFE9F2] text-[8px] font-bold uppercase tracking-wider">B2B</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Wholesale</span>
                    </a>

                    <!-- Video Reels -->
                    <a href="{{ k71_vertical_url('shorts') }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 group-hover:bg-[#221826] flex items-center justify-center text-lg sm:text-xl transition-all duration-200 relative group-hover:scale-105">
                            ▶
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-[#FFCDFF] text-[#1A1018] text-[8px] font-bold uppercase tracking-wider">LIVE</span>
                        </div>
                        <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">Reels</span>
                    </a>

                    <!-- Real Database Categories -->
                    @if(!empty($categories))
                        @foreach($categories->take(6) as $cat)
                            <a href="{{ route('catalog.index', ['category' => $cat->slug]) }}" class="group flex flex-col items-center shrink-0 w-[62px] sm:w-[74px] text-center cursor-pointer">
                                <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-[#161616] border border-white/12 group-hover:border-[#ECD2F3]/70 flex items-center justify-center text-sm overflow-hidden transition-all duration-200 group-hover:scale-105">
                                    @if($cat->sample_image)
                                        <img src="{{ \App\Services\ImageService::url($cat->sample_image, 120) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover opacity-90 group-hover:opacity-100">
                                    @else
                                        <span class="text-[#ECD2F3] font-semibold text-xs uppercase">{{ substr($cat->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <span class="text-[11px] sm:text-xs font-medium text-[#EFE9F2]/80 group-hover:text-white mt-1.5 tracking-tight truncate w-full">{{ $cat->name }}</span>
                            </a>
                        @endforeach
                    @endif

                </div>
            </div>
        </section>

        <!-- =========================================================
             ACT 1: OBSIDIAN HERO STAGE (#000000 • GRAIL WORD-RISE & 3D TILT)
             ========================================================= -->
        <section class="relative bg-[#000000] text-[#EFE9F2] pt-5 sm:pt-8 pb-14 sm:pb-20 overflow-hidden" data-pill-zone="dark">
            <!-- Atmospheric Radial Orchid & Antique Gold Aura -->
            <div class="pointer-events-none absolute -top-24 left-1/4 w-[56vw] h-[420px] rounded-full bg-[radial-gradient(ellipse_at_center,rgba(233,184,255,0.16)_0%,rgba(201,162,74,0.06)_48%,transparent_72%)] blur-3xl"></div>
            <div class="pointer-events-none grail-scrim-b1 absolute inset-x-0 top-0 h-20 z-10"></div>
            <div class="pointer-events-none grail-scrim-b2 absolute inset-x-0 top-0 h-12 z-10"></div>

            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 relative z-20">
                <!-- Grail Editorial Colontitle Bar -->
                <div class="flex items-center justify-between text-[11px] sm:text-xs font-medium text-[#EFE9F2]/60 pb-4 sm:pb-6 border-b border-white/10 mb-5 sm:mb-8">
                    <span class="tracking-wide uppercase text-[#ECD2F3]">K71 • Curated Direct Marketplace</span>
                    <span class="tracking-wide text-right text-[#EFE9F2]/55 hidden sm:inline">PhonePe Escrow Protected • India</span>
                </div>

                <!-- 3-Slide Interactive Grail Carousel Stage -->
                <div class="relative rounded-3xl border border-white/12 bg-[#060606] overflow-hidden shadow-[0_30px_90px_-20px_rgba(233,184,255,0.18)]"
                     x-data="{
                        currentSlide: 0,
                        totalSlides: {{ count($carouselSlides) }},
                        timer: null,
                        startAutoplay() {
                            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                            this.stopAutoplay();
                            this.timer = setInterval(() => { this.nextSlide(); }, 6500);
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
                        @php
                            $words = preg_split('/\s+/', trim($slide['title'])) ?: [$slide['title']];
                        @endphp
                        <div x-show="currentSlide === {{ $idx }}"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             class="relative min-h-[430px] sm:min-h-[520px] px-5 pt-7 pb-14 sm:px-16 sm:py-11 lg:px-18 lg:py-12 flex flex-col justify-between"
                             style="{{ $idx > 0 ? 'display: none;' : '' }}">

                            <!-- Soft Backdrop Photography with Obsidian Gradient Scrim -->
                            <img src="{{ $slide['image'] }}"
                                 alt="{{ $slide['title'] }}"
                                 class="absolute inset-0 w-full h-full object-cover object-center opacity-35 pointer-events-none">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#000000] via-[#000000]/85 to-[#060606]/55 pointer-events-none"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-[#000000] via-transparent to-[#000000]/60 pointer-events-none"></div>

                            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center flex-1">
                                <!-- Left Editorial Typography Column -->
                                <div class="lg:col-span-7 space-y-5 sm:space-y-6">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-3 py-1 rounded-full bg-[#ECD2F3]/14 text-[#FFCDFF] text-[10px] sm:text-xs font-semibold tracking-wide border border-[#ECD2F3]/25 backdrop-blur-md">
                                            {{ $slide['badge'] }}
                                        </span>
                                        <span class="px-3 py-1 rounded-full bg-white/8 text-[#EFE9F2]/75 text-[10px] sm:text-xs font-medium border border-white/10 hidden sm:inline-block">
                                            {{ $slide['kicker'] }}
                                        </span>
                                    </div>

                                    @if($idx === 0)
                                        <h1 class="grail-display grail-heading-flex text-[clamp(2.25rem,5.2vw,4.5rem)] text-white tracking-tight">
                                            @foreach($words as $wIdx => $word)
                                                <span class="grail-mask">
                                                    <span class="grail-word-rise" style="animation-delay: {{ min($wIdx * 75, 520) }}ms;">{{ $word }}</span>
                                                </span>
                                            @endforeach
                                        </h1>
                                    @else
                                        <h2 class="grail-display grail-heading-flex text-[clamp(2.25rem,5.2vw,4.5rem)] text-white tracking-tight">
                                            @foreach($words as $wIdx => $word)
                                                <span class="grail-mask">
                                                    <span class="grail-word-rise" style="animation-delay: {{ min($wIdx * 75, 520) }}ms;">{{ $word }}</span>
                                                </span>
                                            @endforeach
                                        </h2>
                                    @endif

                                    <p class="text-sm sm:text-base text-[#EFE9F2]/75 max-w-xl leading-relaxed font-normal">
                                        {{ $slide['subtitle'] }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-3 pt-1">
                                        @if(!empty($slide['btn1_text']))
                                            <a href="{{ $slide['btn1_link'] }}"
                                               class="grail-cta-pill inline-flex items-center gap-2.5 px-6 sm:px-7 py-3 sm:py-3.5 text-xs sm:text-sm tracking-wide cursor-pointer">
                                                <span>{{ $slide['btn1_text'] }}</span>
                                                <span>&rarr;</span>
                                            </a>
                                        @endif
                                        @if(!empty($slide['btn2_text']))
                                            <a href="{{ $slide['btn2_link'] }}"
                                               class="inline-flex items-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 rounded-full bg-white/10 hover:bg-white/18 text-[#EFE9F2] font-semibold text-xs sm:text-sm border border-white/18 backdrop-blur-md transition-all cursor-pointer">
                                                <span>{{ $slide['btn2_text'] }}</span>
                                                <span>&#8599;</span>
                                            </a>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-5 pt-2 text-[11px] sm:text-xs text-[#EFE9F2]/65 font-medium">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full bg-[#C9A24A]/20 text-[#C9A24A] flex items-center justify-center text-[10px]">✓</span>
                                            Direct Verified Indian Sellers
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full bg-[#ECD2F3]/20 text-[#FFCDFF] flex items-center justify-center text-[10px]">✓</span>
                                            100% Secure Escrow Protection
                                        </span>
                                    </div>
                                </div>

                                <!-- Right 3D Perspective Grail Stage Showcase Card (Desktop Scroll-Scrubbed) -->
                                <div class="hidden lg:flex lg:col-span-5 justify-end">
                                    <div class="grail-stage-3d w-full max-w-[360px] rounded-[28px] bg-[#161616]/90 border border-white/16 p-3 shadow-[0_30px_70px_rgba(0,0,0,0.8)] backdrop-blur-xl" data-grail-stage>
                                        <div class="relative aspect-[4/5] rounded-[20px] overflow-hidden bg-black">
                                            <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/15 to-transparent"></div>
                                            <div class="absolute bottom-4 inset-x-4 flex items-end justify-between gap-2">
                                                <div>
                                                    <span class="text-[10px] uppercase tracking-[0.18em] text-[#C9A24A] block font-semibold">Live Marketplace</span>
                                                    <p class="text-sm font-medium text-white mt-0.5 line-clamp-1">{{ $slide['badge'] }}</p>
                                                </div>
                                                <span class="w-8 h-8 rounded-full bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-xs font-bold shrink-0">&#8599;</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach

                    <!-- Carousel Controls -->
                    <button type="button"
                            @click="prevSlide()"
                            aria-label="Previous Slide"
                            class="hidden sm:flex absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/65 hover:bg-black text-white border border-white/15 backdrop-blur-md items-center justify-center transition cursor-pointer z-20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button"
                            @click="nextSlide()"
                            aria-label="Next Slide"
                            class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/65 hover:bg-black text-white border border-white/15 backdrop-blur-md items-center justify-center transition cursor-pointer z-20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Slide Dots -->
                    <div class="flex items-center justify-center gap-2 pb-4 pt-1 bg-[#060606]/90 border-t border-white/5 relative z-20">
                        @foreach($carouselSlides as $idx => $slide)
                            <button type="button"
                                    @click="goToSlide({{ $idx }})"
                                    aria-label="Go to slide {{ $idx + 1 }}"
                                    :class="currentSlide === {{ $idx }} ? 'w-8 bg-[#ECD2F3]' : 'w-2 bg-white/25 hover:bg-white/50'"
                                    class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @if($seqEnabled)
        @php
            $seqConfigPayload = [
                'frames' => $seqFrames,
                'source' => $seq['source'] ?? 'fallback',
                'smoothing' => $seqSmoothing,
                'count' => count($seqFrames),
            ];
        @endphp
        <!-- =========================================================
             ACT 1B: STICKY PINNED VIDEO-FRAME SCROLL SEQUENCE (0 -> 1 SCRUB)
             ========================================================= -->
        <section id="k71-scroll-stage"
                 class="grail-seq-stage border-t border-white/10"
                 style="height: {{ $seqHeightVh }}vh;"
                 data-pill-zone="dark"
                 data-pill-hide="true"
                 data-seq-stage>
            <script type="application/json" id="k71-seq-config">{!! json_encode($seqConfigPayload, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) !!}</script>

            <div class="grail-seq-sticky">
                <!-- Full-Viewport HiDPI Canvas -->
                <canvas id="k71-seq-canvas"
                        class="grail-seq-canvas"
                        aria-label="K71 interactive product craft motion showcase"></canvas>

                <!-- Atmospheric Vignettes & High-Contrast Scrims -->
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/85 via-black/22 to-black/68"></div>
                <div class="pointer-events-none hidden lg:block absolute inset-0 bg-gradient-to-r from-black/78 via-black/28 to-transparent"></div>
                <div class="pointer-events-none absolute top-0 left-1/3 w-[48vw] h-[280px] rounded-full bg-[radial-gradient(ellipse_at_center,rgba(236,210,243,0.12)_0%,rgba(201,162,74,0.05)_50%,transparent_75%)] blur-3xl"></div>

                <!-- Overlay UI Grid -->
                <div class="relative z-10 h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-9 flex flex-col justify-between pointer-events-none">

                    <!-- Top Sequence Telemetry Strip -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2 sm:gap-2.5 px-3 sm:px-3.5 py-1.5 rounded-full bg-black/55 border border-white/14 backdrop-blur-xl text-[10px] sm:text-xs font-medium tracking-[0.14em] uppercase text-[#EFE9F2]/85">
                            <span class="w-2 h-2 rounded-full bg-[#ECD2F3] animate-pulse shrink-0"></span>
                            <span class="whitespace-nowrap" data-seq-badge>{{ !empty($seqFrames) ? 'Cinematic Scrub' : '3D Studio Preview' }}</span>
                            <span class="text-white/25">•</span>
                            <span class="font-mono text-[#C9A24A] tabular-nums whitespace-nowrap" data-seq-counter>
                                {{ !empty($seqFrames) ? sprintf('01 / %02d', count($seqFrames)) : '000°' }}
                            </span>
                        </div>

                        <a href="#budget-stores"
                           class="pointer-events-auto inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-black/55 hover:bg-black/85 border border-white/15 text-[11px] font-medium text-[#EFE9F2]/75 hover:text-white backdrop-blur-xl transition cursor-pointer">
                            <span>Skip</span>
                            <span aria-hidden="true">&#8595;</span>
                        </a>
                    </div>

                    <!-- Center / Lower-Left Synchronized Editorial Story Beats -->
                    <div class="relative flex-1 flex items-end pb-5 sm:pb-10">
                        <div class="relative w-full max-w-2xl min-h-[260px] sm:min-h-[280px]">
                            @foreach($seqBeats as $bIdx => $beat)
                                <div class="grail-seq-beat absolute inset-x-0 bottom-0 space-y-3 sm:space-y-4"
                                     data-seq-beat="{{ $bIdx }}"
                                     style="{{ $bIdx === 0 ? '--beat-o: 1; --beat-y: 0px; --beat-pe: auto;' : '--beat-o: 0; --beat-y: 26px; --beat-pe: none;' }}">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ECD2F3]/14 border border-[#ECD2F3]/28 text-[#FFCDFF] text-[10px] sm:text-xs font-semibold uppercase tracking-[0.18em] backdrop-blur-md">
                                        <span>{{ $beat['kicker'] }}</span>
                                    </div>

                                    <h2 class="grail-display grail-heading-flex text-[clamp(1.95rem,4.4vw,3.9rem)] text-white drop-shadow-[0_2px_20px_rgba(0,0,0,0.88)]">
                                        {{ $beat['title'] }}
                                    </h2>

                                    <p class="text-xs sm:text-base text-[#EFE9F2]/85 max-w-xl leading-relaxed font-normal drop-shadow-[0_1px_12px_rgba(0,0,0,0.9)]">
                                        {{ $beat['desc'] }}
                                    </p>

                                    @if($bIdx === 2)
                                        <div class="flex flex-wrap items-center gap-3 pt-1 pointer-events-auto">
                                            <a href="#latest-products"
                                               class="grail-cta-pill inline-flex items-center gap-2.5 px-6 py-3 text-xs sm:text-sm tracking-wide cursor-pointer">
                                                <span>Explore Curated Drops</span>
                                                <span aria-hidden="true">&rarr;</span>
                                            </a>
                                            <a href="{{ route('catalog.index') }}"
                                               class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white/10 hover:bg-white/18 text-[#EFE9F2] font-semibold text-xs sm:text-sm border border-white/18 backdrop-blur-md transition cursor-pointer">
                                                <span>Browse Catalog</span>
                                                <span aria-hidden="true">&#8599;</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bottom Interactive Chapter Selector & Linear Progress Track -->
                    <div class="pointer-events-auto space-y-3 pt-2 border-t border-white/10">
                        <div class="grid grid-cols-3 gap-2 sm:gap-4 text-left">
                            @foreach($seqBeats as $bIdx => $beat)
                                <button type="button"
                                        data-seq-jump="{{ $bIdx }}"
                                        class="group flex items-center justify-between gap-2 py-1 text-left transition cursor-pointer">
                                    <div class="min-w-0">
                                        <span class="block text-[9px] sm:text-[10px] font-mono uppercase tracking-[0.18em] text-[#C9A24A]">0{{ $bIdx + 1 }}</span>
                                        <span class="block text-[11px] sm:text-xs font-medium text-[#EFE9F2]/70 group-hover:text-white truncate transition-colors"
                                              data-seq-tab-label="{{ $bIdx }}">{{ $beat['kicker'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <div class="w-full h-[2px] rounded-full bg-white/15 overflow-hidden">
                            <div class="grail-seq-progress-fill w-full h-full bg-gradient-to-r from-[#C9A24A] via-[#ECD2F3] to-[#FFCDFF]"
                                 data-seq-bar
                                 style="--seq-p: 0;"></div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        @endif

        <!-- =========================================================
             ACT 2: OBSIDIAN BUDGET STATS (MASKED LINE RISE + FILL REVEAL)
             ========================================================= -->
        <section id="budget-stores" class="relative bg-[#060606] text-[#EFE9F2] py-14 sm:py-20 border-t border-white/10" data-grail-reveal data-pill-zone="dark">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-8 sm:space-y-12">

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="space-y-3">
                        <span class="text-[11px] uppercase tracking-[0.2em] text-[#C9A24A] font-semibold block">Curated Price Corners</span>
                        <h2 class="grail-display text-[clamp(2rem,4.5vw,3.75rem)] text-white">
                            <span class="grail-line-mask"><span class="grail-line-rise">Shop by Budget</span></span>
                            <span class="grail-line-mask"><span class="grail-line-rise" style="transition-delay: 95ms;">&amp; Factory Steals</span></span>
                        </h2>
                    </div>

                    <div class="flex flex-col sm:items-end gap-3">
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/60 max-w-sm leading-relaxed sm:text-right">
                            Every price tier is directly sourced from verified workshops, ateliers, and wholesale houses.
                        </p>
                        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#FFCDFF] hover:text-white transition">
                            <span>All Price Stores</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- 4-Column Hairline Stat Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 border border-white/12 divide-y sm:divide-y-0 sm:divide-x divide-white/12 rounded-2xl overflow-hidden bg-[#010101]">

                    <!-- 1. Under ₹199 -->
                    <a href="{{ route('catalog.index', ['max_price' => 199]) }}"
                       class="grail-stat-tile group p-5 sm:p-7 flex flex-col justify-between min-h-[168px] sm:min-h-[210px] hover:bg-white/[0.03]"
                       style="--target-fill: 58%;">
                        <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.16em]">
                            <span class="text-[#C9A24A]">Daily Value</span>
                            <span class="text-[#EFE9F2]/40 group-hover:text-[#FFCDFF] transition">&#8599;</span>
                        </div>
                        <div class="space-y-1.5 relative z-10">
                            <div class="grail-display text-3xl sm:text-4xl text-white tabular-nums">Under ₹199</div>
                            <h3 class="text-xs sm:text-sm font-medium text-[#EFE9F2]/70 group-hover:text-white transition">Under ₹199 Store</h3>
                            <p class="text-[11px] text-[#EFE9F2]/45 line-clamp-1">Everyday essentials &amp; accessories</p>
                        </div>
                    </a>

                    <!-- 2. Under ₹499 -->
                    <a href="{{ route('catalog.index', ['max_price' => 499]) }}"
                       class="grail-stat-tile group p-5 sm:p-7 flex flex-col justify-between min-h-[168px] sm:min-h-[210px] hover:bg-white/[0.03] border-l border-white/12 sm:border-l-0"
                       style="--target-fill: 72%;">
                        <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.16em]">
                            <span class="text-[#FFCDFF]">Popular Picks</span>
                            <span class="text-[#EFE9F2]/40 group-hover:text-[#FFCDFF] transition">&#8599;</span>
                        </div>
                        <div class="space-y-1.5 relative z-10">
                            <div class="grail-display text-3xl sm:text-4xl text-white tabular-nums">Under ₹499</div>
                            <h3 class="text-xs sm:text-sm font-medium text-[#EFE9F2]/70 group-hover:text-white transition">Under ₹499 Store</h3>
                            <p class="text-[11px] text-[#EFE9F2]/45 line-clamp-1">Trending fashion &amp; gadget picks</p>
                        </div>
                    </a>

                    <!-- 3. Under ₹999 -->
                    <a href="{{ route('catalog.index', ['max_price' => 999]) }}"
                       class="grail-stat-tile group p-5 sm:p-7 flex flex-col justify-between min-h-[168px] sm:min-h-[210px] hover:bg-white/[0.03]"
                       style="--target-fill: 84%;">
                        <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.16em]">
                            <span class="text-[#C9A24A]">Boutiques</span>
                            <span class="text-[#EFE9F2]/40 group-hover:text-[#FFCDFF] transition">&#8599;</span>
                        </div>
                        <div class="space-y-1.5 relative z-10">
                            <div class="grail-display text-3xl sm:text-4xl text-white tabular-nums">Under ₹999</div>
                            <h3 class="text-xs sm:text-sm font-medium text-[#EFE9F2]/70 group-hover:text-white transition">Under ₹999 Store</h3>
                            <p class="text-[11px] text-[#EFE9F2]/45 line-clamp-1">Marble handicrafts &amp; ethnic wear</p>
                        </div>
                    </a>

                    <!-- 4. Flat 50-80% OFF -->
                    <a href="{{ route('catalog.index', ['sort' => 'discount']) }}"
                       class="grail-stat-tile group p-5 sm:p-7 flex flex-col justify-between min-h-[168px] sm:min-h-[210px] hover:bg-white/[0.03] border-l border-white/12 sm:border-l-0"
                       style="--target-fill: 96%;">
                        <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.16em]">
                            <span class="text-[#FFCDFF]">Steal Deals</span>
                            <span class="text-[#EFE9F2]/40 group-hover:text-[#FFCDFF] transition">&#8599;</span>
                        </div>
                        <div class="space-y-1.5 relative z-10">
                            <div class="grail-display text-3xl sm:text-4xl text-[#FFCDFF] tabular-nums">50–80% Off</div>
                            <h3 class="text-xs sm:text-sm font-medium text-[#EFE9F2]/70 group-hover:text-white transition">Flat 50-80% OFF</h3>
                            <p class="text-[11px] text-[#EFE9F2]/45 line-clamp-1">Verified factory clearance stock</p>
                        </div>
                    </a>

                </div>
            </div>
        </section>

        <!-- =========================================================
             ACT 3: ORCHID INK-FLOOD TRANSITION (RADIAL BLOOM + SCRUB WORDS)
             ========================================================= -->
        <section class="relative bg-[#000000] py-4 sm:py-8 overflow-hidden" data-pill-zone="orchid">
            <div class="grail-ink-flood text-[#221826] rounded-[24px] mx-0 sm:mx-3 lg:mx-6 px-4 sm:px-8 lg:px-12 py-14 sm:py-20 relative shadow-[0_30px_90px_rgba(233,184,255,0.22)]"
                 data-grail-ink>

                <div class="max-w-7xl mx-auto space-y-10 sm:space-y-12">

                    <!-- Colontitle Header on Orchid -->
                    <div class="flex items-center justify-between text-[11px] font-medium text-[#623562]/85 tracking-wide uppercase">
                        <span>Act III • Verified Indian Ecosystem</span>
                        <span class="hidden sm:inline">Scroll-Triggered Motion</span>
                    </div>

                    <!-- Scroll-Scrubbed Word-by-Word Statement Heading -->
                    <div class="max-w-5xl" data-grail-scrub-words>
                        @php
                            $scrubWords = ['Six', 'verticals,', 'one', 'trusted', 'network', 'for', 'makers,', 'kitchens', '&', 'buyers.'];
                        @endphp
                        <h2 class="grail-display grail-heading-flex text-[clamp(2.25rem,5.2vw,4.5rem)] text-[#221826] leading-[0.92]">
                            @foreach($scrubWords as $sIdx => $sWord)
                                <span class="grail-scrub-word" style="--i: {{ $sIdx }};">{{ $sWord }}</span>
                            @endforeach
                        </h2>
                    </div>

                    <!-- Scroll-Drawn Hairline Rule -->
                    <div class="grail-hairline-draw w-full bg-[#623562]/30" data-grail-hairline></div>

                    <!-- Shorts Video Showcase OR 6-Vertical Ecosystem Spotlight -->
                    @if(isset($latestShorts) && $latestShorts->isNotEmpty())
                        <div class="space-y-5"
                             x-data="{
                                scrollLeft() { this.$refs.shortsTrack.scrollBy({ left: -340, behavior: 'smooth' }); },
                                scrollRight() { this.$refs.shortsTrack.scrollBy({ left: 340, behavior: 'smooth' }); }
                             }">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#221826] text-[#FFCDFF] text-[10px] font-bold uppercase tracking-wider">LIVE REELS</span>
                                        <h3 class="text-lg sm:text-2xl font-bold text-[#221826] tracking-tight">Latest Shorts in Motion</h3>
                                    </div>
                                    <p class="text-xs sm:text-sm text-[#623562] mt-1">Watch products firsthand with instant 1-tap checkout</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="scrollLeft()" aria-label="Previous Shorts"
                                            class="w-9 h-9 rounded-full bg-white/55 hover:bg-white text-[#221826] border border-[#623562]/20 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button type="button" @click="scrollRight()" aria-label="Next Shorts"
                                            class="w-9 h-9 rounded-full bg-white/55 hover:bg-white text-[#221826] border border-[#623562]/20 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                    <a href="{{ k71_vertical_url('shorts') }}" class="ml-1 px-4 py-2 rounded-full bg-[#221826] hover:bg-[#623562] text-[#FFE4FF] text-xs font-semibold transition">
                                        View All &rarr;
                                    </a>
                                </div>
                            </div>

                            <div x-ref="shortsTrack" class="flex items-center gap-3.5 sm:gap-4 overflow-x-auto no-scrollbar scroll-smooth py-2 touch-pan-x">
                                @foreach($latestShorts as $short)
                                    <a href="{{ route('shorts.show', $short->id) }}"
                                       class="shrink-0 w-[165px] sm:w-[196px] aspect-[9/16] rounded-[22px] overflow-hidden bg-[#1A1018] shadow-lg hover:-translate-y-1.5 transition-all duration-300 relative group block border border-[#623562]/25">
                                        @if($short->thumbnail_url)
                                            <img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-[#ECD2F3] text-3xl bg-[#221826]">▶</div>
                                        @endif

                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-black/25 p-3.5 flex flex-col justify-between text-white">
                                            <div class="flex items-center justify-between">
                                                <span class="px-2 py-0.5 rounded-full bg-[#ECD2F3]/20 backdrop-blur-md text-[#FFCDFF] text-[9px] font-bold uppercase tracking-wider border border-[#ECD2F3]/30">
                                                    Reel
                                                </span>
                                                <span class="w-7 h-7 rounded-full bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-[10px] font-bold group-hover:scale-110 transition-transform">▶</span>
                                            </div>
                                            <div class="space-y-1">
                                                <h4 class="text-xs font-semibold line-clamp-2 leading-snug text-white">{{ $short->title }}</h4>
                                                @if($short->vendor)
                                                    <p class="text-[10px] text-[#ECD2F3]/80 truncate">@ {{ $short->vendor->shop_name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4" data-grail-reveal>
                            @php
                                $verticalSpotlights = [
                                    ['name' => 'K71Mart Retail', 'tag' => 'Direct Brands', 'url' => route('catalog.index'), 'icon' => '🛍️'],
                                    ['name' => 'Feasto Dining', 'tag' => 'Cloud Kitchens', 'url' => k71_vertical_url('food'), 'icon' => '🍲'],
                                    ['name' => 'Pro Services', 'tag' => 'Certified Pros', 'url' => k71_vertical_url('services'), 'icon' => '⚡'],
                                    ['name' => 'Pre-Owned', 'tag' => 'Inspected Tech', 'url' => k71_vertical_url('used'), 'icon' => '♻️'],
                                    ['name' => 'B2B Quarry', 'tag' => 'Factory RFQs', 'url' => k71_vertical_url('enquiry'), 'icon' => '📋'],
                                    ['name' => 'Live Shorts', 'tag' => 'Shoppable Video', 'url' => k71_vertical_url('shorts'), 'icon' => '▶'],
                                ];
                            @endphp
                            @foreach($verticalSpotlights as $vIdx => $spot)
                                <a href="{{ $spot['url'] }}"
                                   class="grail-card-reveal group p-4 rounded-2xl bg-white/55 hover:bg-white/85 border border-[#623562]/20 transition-all flex flex-col justify-between min-h-[120px]"
                                   style="--delay: {{ $vIdx * 65 }}ms;">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xl">{{ $spot['icon'] }}</span>
                                        <span class="text-[10px] font-bold text-[#623562] group-hover:translate-x-0.5 transition-transform">&#8599;</span>
                                    </div>
                                    <div class="mt-4">
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-[#623562] block">{{ $spot['tag'] }}</span>
                                        <h3 class="text-sm font-bold text-[#221826] mt-0.5">{{ $spot['name'] }}</h3>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- =========================================================
             ACT 4: PEEL-DECK WARM STONE LIGHT SHEET (#F6F6F6 • LATEST 20)
             ========================================================= -->
        <section id="latest-products"
                 class="bg-[#000000] px-0 sm:px-3 lg:px-6 pt-6 pb-8"
                 data-pill-zone="light">
            <div class="grail-peel-sheet grail-light-sheet px-3 sm:px-8 lg:px-12 py-12 sm:py-16 shadow-[0_30px_90px_rgba(0,0,0,0.45)]"
                 data-grail-peel>
                <div class="max-w-7xl mx-auto space-y-8 sm:space-y-10">

                    <!-- Sheet Header -->
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-5 border-b border-black/10">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="px-3 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold text-[10px] uppercase tracking-[0.14em]">
                                    Fresh Arrivals
                                </span>
                                <span class="text-xs text-[#623562] font-semibold">Direct from Verified Sellers</span>
                            </div>
                            <h2 class="grail-display text-[clamp(2rem,4.6vw,3.8rem)] text-black">
                                Latest Products
                            </h2>
                        </div>

                        <a href="{{ route('catalog.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white border border-black/12 hover:border-[#623562] text-xs sm:text-sm font-semibold text-black transition">
                            <span>View Entire Catalog</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <!-- Product Grid (Up to 20 Items with Staggered Reveal) -->
                    @if($displayLatest->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-5" data-grail-reveal>
                            @foreach($displayLatest->take(20) as $prod)
                                <div class="grail-card-reveal h-full" style="--delay: {{ ($loop->index % 5) * 65 }}ms;">
                                    <x-product-card :product="$prod" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-10 sm:p-14 rounded-3xl bg-white border border-black/10 text-center space-y-3">
                            <p class="text-sm sm:text-base font-medium text-[#221826]">Products are being curated directly from verified Indian boutiques.</p>
                            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#1A1018] text-[#EFE9F2] text-xs font-semibold hover:bg-[#623562] transition">
                                <span>Explore Marketplace Catalog</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    @endif

                    <!-- Load More Button -->
                    <div class="text-center pt-4">
                        <a href="{{ route('catalog.index') }}"
                           class="inline-flex items-center gap-2.5 px-8 py-4 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#EFE9F2] font-semibold text-xs sm:text-sm tracking-wide shadow-sm active:scale-95 transition cursor-pointer">
                            <span>Load More Products</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <!-- =========================================================
             ACT 5: MULTI-VERTICAL SPLIT-REVEAL SHOWCASE (#FAFAF8 PARCHMENT)
             ========================================================= -->
        @if($hasAnyVerticalSection)
        <section class="bg-[#000000] px-0 sm:px-3 lg:px-6 pb-8" data-pill-zone="light">
            <div class="bg-[#FAFAF8] text-black rounded-[24px] px-3 sm:px-8 lg:px-12 py-12 sm:py-16">
                <div class="max-w-7xl mx-auto space-y-14 sm:space-y-16">

                    <!-- 5A. Cloud Dining & Artisan Kitchens -->
                    @if($foodRestaurants->isNotEmpty() || $featuredFood->isNotEmpty())
                    <div class="space-y-6">
                        <div class="grail-split-row pt-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center" data-grail-split>
                            <div class="grail-split-left md:col-span-5">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#837052] block mb-1">Feasto Dining</span>
                                <h2 class="grail-display text-3xl sm:text-4xl text-black">Artisan Kitchens</h2>
                            </div>
                            <div class="hidden md:flex md:col-span-2 justify-center">
                                <div class="grail-split-media w-14 h-14 rounded-2xl bg-[#F4F1EC] border border-black/10 flex items-center justify-center text-2xl">🍲</div>
                            </div>
                            <div class="grail-split-right md:col-span-5 md:text-right space-y-2">
                                <p class="text-xs sm:text-sm text-black/65">Hygiene-certified cloud kitchens with fresh 30–45 min doorstep dispatch.</p>
                                <a href="{{ k71_vertical_url('food') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-black hover:text-[#623562] transition">
                                    <span>All Menus</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5" data-grail-reveal>
                            @foreach(($foodRestaurants->isNotEmpty() ? $foodRestaurants : $featuredFood)->take(4) as $fIdx => $item)
                                @php
                                    $isVendor = $item instanceof \App\Models\Vendor;
                                    $rTitle = $isVendor ? $item->shop_name : $item->title;
                                    $rImg = $isVendor ? ($item->logo_url ?: $item->cover_url) : $item->primary_image_url;
                                    $rUrl = $isVendor ? route('food.restaurant', $item->slug) : ($item->vendor ? route('food.restaurant', $item->vendor->slug) : k71_vertical_url('food'));
                                @endphp
                                <a href="{{ $rUrl }}"
                                   class="grail-card-reveal group bg-white rounded-2xl border border-black/8 hover:border-[#623562]/40 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col justify-between"
                                   style="--delay: {{ $fIdx * 65 }}ms;">
                                    <div class="relative aspect-video overflow-hidden bg-[#F4F1EC]">
                                        @if($rImg)
                                            <img src="{{ format_image_url($rImg) }}" alt="{{ $rTitle }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-3xl">🍲</div>
                                        @endif
                                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[9px] font-bold uppercase tracking-wider">Fresh</span>
                                    </div>
                                    <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between">
                                        <div>
                                            <h3 class="font-semibold text-xs sm:text-sm text-black group-hover:text-[#623562] transition truncate">{{ $rTitle }}</h3>
                                            <p class="text-[11px] text-black/50 mt-0.5">Express delivery in 30-45 mins</p>
                                        </div>
                                        <div class="pt-2.5 mt-2.5 border-t border-black/8 flex items-center justify-between text-xs font-semibold text-black">
                                            <span>View Menu</span>
                                            <span>&rarr;</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- 5B. Verified Services & Specialists -->
                    @if($featuredServices->isNotEmpty())
                    <div class="space-y-6">
                        <div class="grail-split-row pt-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center" data-grail-split>
                            <div class="grail-split-left md:col-span-5">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#837052] block mb-1">Pro Network</span>
                                <h2 class="grail-display text-3xl sm:text-4xl text-black">Verified Services</h2>
                            </div>
                            <div class="hidden md:flex md:col-span-2 justify-center">
                                <div class="grail-split-media w-14 h-14 rounded-2xl bg-[#F4F1EC] border border-black/10 flex items-center justify-center text-2xl">⚡</div>
                            </div>
                            <div class="grail-split-right md:col-span-5 md:text-right space-y-2">
                                <p class="text-xs sm:text-sm text-black/65">Marble restoration, web engineering, photography &amp; vetted home specialists.</p>
                                <a href="{{ k71_vertical_url('services') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-black hover:text-[#623562] transition">
                                    <span>All Services</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5" data-grail-reveal>
                            @foreach($featuredServices->take(4) as $svIdx => $service)
                                @php
                                    $basicPkg = $service->gigPackages->firstWhere('tier', 'basic') ?? $service->gigPackages->first();
                                    $startingPrice = $basicPkg ? $basicPkg->price : ($service->base_price / 100);
                                @endphp
                                <a href="{{ route('services.show', $service->slug) }}"
                                   class="grail-card-reveal group bg-white rounded-2xl border border-black/8 hover:border-[#623562]/40 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col justify-between"
                                   style="--delay: {{ $svIdx * 65 }}ms;">
                                    <div class="relative aspect-video overflow-hidden bg-[#F4F1EC]">
                                        <img src="{{ $service->primary_image_url }}" alt="{{ $service->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[9px] font-bold uppercase tracking-wider">Vetted</span>
                                    </div>
                                    <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between">
                                        <div>
                                            <span class="text-[10px] font-semibold text-[#837052] block truncate">{{ $service->vendor?->shop_name ?? 'Specialist' }}</span>
                                            <h3 class="font-semibold text-xs sm:text-sm text-black group-hover:text-[#623562] transition truncate mt-0.5">{{ $service->title }}</h3>
                                        </div>
                                        <div class="pt-2.5 mt-2.5 border-t border-black/8 flex items-center justify-between">
                                            <span class="text-xs font-bold text-black tabular-nums">From ₹{{ number_format($startingPrice) }}</span>
                                            <span class="text-[11px] font-semibold text-[#623562]">Book &rarr;</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- 5C. Pre-Owned Inspected Deals -->
                    @if($featuredUsed->isNotEmpty())
                    <div class="space-y-6">
                        <div class="grail-split-row pt-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center" data-grail-split>
                            <div class="grail-split-left md:col-span-5">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#837052] block mb-1">Re-Commerce</span>
                                <h2 class="grail-display text-3xl sm:text-4xl text-black">Inspected Pre-Owned</h2>
                            </div>
                            <div class="hidden md:flex md:col-span-2 justify-center">
                                <div class="grail-split-media w-14 h-14 rounded-2xl bg-[#F4F1EC] border border-black/10 flex items-center justify-center text-2xl">♻️</div>
                            </div>
                            <div class="grail-split-right md:col-span-5 md:text-right space-y-2">
                                <p class="text-xs sm:text-sm text-black/65">Certified pre-owned smartphones, laptops, studio gear &amp; machinery.</p>
                                <a href="{{ k71_vertical_url('used') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-black hover:text-[#623562] transition">
                                    <span>All Used Deals</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-5" data-grail-reveal>
                            @foreach($featuredUsed->take(4) as $uIdx => $item)
                                <a href="{{ route('used.show', $item->slug) }}"
                                   class="grail-card-reveal group bg-white rounded-2xl border border-black/8 hover:border-[#623562]/40 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col justify-between"
                                   style="--delay: {{ $uIdx * 65 }}ms;">
                                    <div class="relative aspect-square overflow-hidden bg-[#F4F1EC]">
                                        <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[9px] font-bold uppercase tracking-wider">
                                            {{ $item->getCustomField('condition', 'Inspected') }}
                                        </span>
                                    </div>
                                    <div class="p-3.5">
                                        <h4 class="text-xs sm:text-sm font-semibold text-black truncate group-hover:text-[#623562] transition">{{ $item->title }}</h4>
                                        <div class="text-sm font-bold text-black mt-1 tabular-nums">{{ $item->formatted_base_price }}</div>
                                        <span class="text-[10px] text-black/50 block mt-0.5">📍 {{ $item->getCustomField('city', 'India') }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- 5D. Live B2B & Factory Enquiries -->
                    @if($featuredEnquiries->isNotEmpty())
                    <div class="space-y-6">
                        <div class="grail-split-row pt-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center" data-grail-split>
                            <div class="grail-split-left md:col-span-5">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#837052] block mb-1">Wholesale Desk</span>
                                <h2 class="grail-display text-3xl sm:text-4xl text-black">Live B2B Enquiries</h2>
                            </div>
                            <div class="hidden md:flex md:col-span-2 justify-center">
                                <div class="grail-split-media w-14 h-14 rounded-2xl bg-[#F4F1EC] border border-black/10 flex items-center justify-center text-2xl">📋</div>
                            </div>
                            <div class="grail-split-right md:col-span-5 md:text-right flex flex-col md:items-end gap-2">
                                <p class="text-xs sm:text-sm text-black/65">Post factory requirements or quote directly on wholesale buyer leads.</p>
                                <div class="flex items-center gap-2.5">
                                    <a href="{{ route('enquiry.create') }}" class="px-4 py-1.5 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#EFE9F2] text-xs font-semibold transition">+ Post RFQ</a>
                                    <a href="{{ k71_vertical_url('enquiry') }}" class="text-xs sm:text-sm font-semibold text-black hover:text-[#623562] transition">All RFQs &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5" data-grail-reveal>
                            @foreach($featuredEnquiries->take(4) as $eIdx => $enq)
                                <div class="grail-card-reveal p-4 sm:p-5 rounded-2xl bg-white border border-black/8 flex flex-col justify-between space-y-3"
                                     style="--delay: {{ $eIdx * 65 }}ms;">
                                    <div>
                                        <div class="flex items-center justify-between text-[10px] text-black/50 font-mono">
                                            <span>#{{ $enq->enquiry_number }}</span>
                                            <span class="px-2 py-0.5 rounded-full bg-[#F4F1EC] text-[#221826] font-bold uppercase">{{ $enq->status }}</span>
                                        </div>
                                        <h4 class="text-xs sm:text-sm font-semibold text-black mt-2 line-clamp-2">{{ $enq->title }}</h4>
                                        <div class="mt-2 text-[11px] text-black/60 flex items-center gap-2">
                                            <span>Qty: <strong class="text-black">{{ $enq->quantity }}</strong></span>
                                            <span>•</span>
                                            <span>📍 {{ $enq->city }}</span>
                                        </div>
                                    </div>
                                    <div class="pt-3 border-t border-black/8 flex items-center justify-between text-xs">
                                        <span class="font-bold text-black tabular-nums">{{ $enq->formatted_budget }}</span>
                                        <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" class="px-3.5 py-1.5 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#EFE9F2] font-semibold text-[11px] transition">
                                            Quote &rarr;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </section>
        @endif

        <!-- =========================================================
             ACT 6: OBSIDIAN TRUST PILLARS & LUMINOUS SELLER EMBLEM (#000000)
             ========================================================= -->
        <section class="relative bg-[#000000] text-[#EFE9F2] pt-12 sm:pt-16 pb-20 sm:pb-28 overflow-hidden" data-pill-zone="dark">
            <div class="pointer-events-none absolute bottom-0 left-1/2 -translate-x-1/2 w-[72vw] h-[380px] rounded-full bg-[radial-gradient(ellipse_at_center,rgba(233,184,255,0.20)_0%,rgba(201,162,74,0.08)_46%,transparent_72%)] blur-3xl"></div>

            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-14 sm:space-y-20 relative z-10">

                <!-- 4-Column Trust & Escrow Hairline Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 border border-white/12 divide-y sm:divide-y-0 sm:divide-x divide-white/12 rounded-2xl bg-[#060606] overflow-hidden" data-grail-reveal>
                    <div class="grail-card-reveal p-5 text-center sm:text-left" style="--delay: 0ms;">
                        <span class="text-[10px] uppercase tracking-[0.18em] text-[#C9A24A] font-semibold block mb-1.5">Verified</span>
                        <h4 class="text-sm font-semibold text-white">Verified Merchants</h4>
                        <p class="text-xs text-[#EFE9F2]/50 mt-0.5">GST &amp; Aadhaar Vetted</p>
                    </div>
                    <div class="grail-card-reveal p-5 text-center sm:text-left border-l border-white/12 sm:border-l-0" style="--delay: 65ms;">
                        <span class="text-[10px] uppercase tracking-[0.18em] text-[#FFCDFF] font-semibold block mb-1.5">Protected</span>
                        <h4 class="text-sm font-semibold text-white">PhonePe Escrow</h4>
                        <p class="text-xs text-[#EFE9F2]/50 mt-0.5">100% Safe Settlements</p>
                    </div>
                    <div class="grail-card-reveal p-5 text-center sm:text-left" style="--delay: 130ms;">
                        <span class="text-[10px] uppercase tracking-[0.18em] text-[#C9A24A] font-semibold block mb-1.5">Logistics</span>
                        <h4 class="text-sm font-semibold text-white">Fast Dispatch</h4>
                        <p class="text-xs text-[#EFE9F2]/50 mt-0.5">Express Pan-India Courier</p>
                    </div>
                    <div class="grail-card-reveal p-5 text-center sm:text-left border-l border-white/12 sm:border-l-0" style="--delay: 195ms;">
                        <span class="text-[10px] uppercase tracking-[0.18em] text-[#FFCDFF] font-semibold block mb-1.5">Connect</span>
                        <h4 class="text-sm font-semibold text-white">Direct Chat</h4>
                        <p class="text-xs text-[#EFE9F2]/50 mt-0.5">WhatsApp &amp; Live In-App DM</p>
                    </div>
                </div>

                <!-- Seller Onboarding Grail Emblem Stage -->
                <div class="max-w-4xl mx-auto text-center space-y-7 sm:space-y-9" data-grail-reveal>
                    <div class="inline-flex grail-emblem-tile w-16 h-16 sm:w-20 sm:h-20 rounded-3xl items-center justify-center mx-auto">
                        <span class="grail-display text-2xl sm:text-3xl font-normal text-[#221826] tracking-tight">K71</span>
                    </div>

                    <div class="space-y-4">
                        <span class="inline-block px-3.5 py-1 rounded-full bg-white/8 text-[#ECD2F3] text-[10px] font-semibold uppercase tracking-[0.18em] border border-white/12">
                            Indian Merchant Program
                        </span>
                        <h3 class="grail-display text-[clamp(2.1rem,5vw,4.25rem)] text-white">
                            <span class="grail-line-mask"><span class="grail-line-rise">Start Selling Direct</span></span>
                            <span class="grail-line-mask"><span class="grail-line-rise" style="transition-delay: 100ms;">on K71 Marketplace</span></span>
                        </h3>
                        <p class="text-xs sm:text-base text-[#EFE9F2]/65 max-w-xl mx-auto leading-relaxed">
                            Zero setup fee for retail boutiques, dining kitchens, verified specialists, pre-owned sellers, and marble factories with automated daily PhonePe settlements.
                        </p>
                    </div>

                    <div class="grail-hairline-draw grail-hairline-center w-full max-w-md mx-auto bg-white/25" data-grail-hairline></div>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-2">
                        <a href="{{ route('vendor.register') }}"
                           class="grail-cta-pill w-full sm:w-auto px-8 py-4 text-xs sm:text-sm tracking-wide text-center cursor-pointer">
                            Open Your Store &rarr;
                        </a>
                        <button type="button"
                                onclick="if(window.openAuthModal) { window.openAuthModal(null, 'login', 'seller'); } else { window.location.href = '{{ route('login') }}'; }"
                                class="w-full sm:w-auto px-7 py-4 rounded-full bg-white/10 hover:bg-white/16 text-[#EFE9F2] font-semibold text-xs sm:text-sm border border-white/18 text-center transition cursor-pointer">
                            Seller Login
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- =========================================================
             ADAPTIVE GRAIL FLOATING PILL (MORPHS THEME BY SCROLL ZONE)
             Positioned bottom-left to stay clear of floating-six-nav
             ========================================================= -->
        <a href="#latest-products"
           data-grail-pill
           data-pill-theme="dark"
           aria-label="Explore Curated Drops"
           class="grail-adaptive-pill fixed left-3.5 sm:left-6 bottom-4 sm:bottom-6 z-40 inline-flex items-center gap-3 pl-4 pr-2 py-2 cursor-pointer">
            <span class="text-xs font-medium whitespace-nowrap transition-colors" style="color: var(--pill-accent);">
                Explore Drops
            </span>
            <span class="grail-pill-mark w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                  style="background: var(--pill-btn-bg); color: var(--pill-btn-fg);">
                &#8595;
            </span>
        </a>

    </div>

    @push('scripts')
    <script>
        (function () {
            const page = document.querySelector('[data-grail-page]');
            if (!page) return;

            const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const revealEls = Array.from(page.querySelectorAll('[data-grail-reveal], .grail-card-reveal'));
            const scrubWordGroups = Array.from(page.querySelectorAll('[data-grail-scrub-words]'));
            const inkEls = Array.from(page.querySelectorAll('[data-grail-ink]'));
            const hairlineEls = Array.from(page.querySelectorAll('[data-grail-hairline]'));
            const splitEls = Array.from(page.querySelectorAll('[data-grail-split]'));
            const stageEls = Array.from(page.querySelectorAll('[data-grail-stage]'));
            const peelEls = Array.from(page.querySelectorAll('[data-grail-peel]'));
            const pillEl = page.querySelector('[data-grail-pill]');
            const pillZones = Array.from(page.querySelectorAll('[data-pill-zone]'));

            const clamp01 = (v) => (v < 0 ? 0 : v > 1 ? 1 : v);

            if (reducedMotion) {
                revealEls.forEach((el) => el.classList.add('is-in'));
                scrubWordGroups.forEach((el) => el.style.setProperty('--hl', '1'));
                inkEls.forEach((el) => el.style.setProperty('--ink', '1.25'));
                hairlineEls.forEach((el) => el.style.setProperty('--dgrow', '1'));
                splitEls.forEach((el) => el.style.setProperty('--rp', '1'));
                stageEls.forEach((el) => el.style.setProperty('--stage', '1'));
                peelEls.forEach((el) => el.style.setProperty('--s5off', '0'));
                return;
            }

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-in');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { rootMargin: '0px 0px -4% 0px', threshold: 0.02 });
                revealEls.forEach((el) => observer.observe(el));
            } else {
                revealEls.forEach((el) => el.classList.add('is-in'));
            }

            let ticking = false;
            const updateMotion = () => {
                ticking = false;
                const vh = window.innerHeight || 800;

                revealEls.forEach((el) => {
                    if (!el.classList.contains('is-in')) {
                        const r = el.getBoundingClientRect();
                        if (r.top < vh * 0.98) {
                            el.classList.add('is-in');
                        }
                    }
                });

                scrubWordGroups.forEach((el) => {
                    const r = el.getBoundingClientRect();
                    const p = clamp01((vh * 0.96 - r.top) / (vh * 0.45));
                    el.style.setProperty('--hl', p.toFixed(4));
                });

                inkEls.forEach((el) => {
                    const r = el.getBoundingClientRect();
                    const p = clamp01((vh * 1.02 - r.top) / (vh * 0.52));
                    const ink = 0.44 + p * 0.92;
                    el.style.setProperty('--ink', ink.toFixed(4));
                });

                hairlineEls.forEach((el) => {
                    const r = el.getBoundingClientRect();
                    const p = clamp01((vh * 0.96 - r.top) / (vh * 0.28));
                    el.style.setProperty('--dgrow', p.toFixed(4));
                });

                splitEls.forEach((el) => {
                    const r = el.getBoundingClientRect();
                    const p = clamp01((vh * 0.96 - r.top) / (vh * 0.34));
                    el.style.setProperty('--rp', p.toFixed(4));
                });

                stageEls.forEach((el) => {
                    const r = el.getBoundingClientRect();
                    if (window.scrollY < 40) {
                        el.style.setProperty('--stage', '1');
                        return;
                    }
                    const leave = clamp01(r.bottom / (vh * 0.72));
                    const enter = clamp01((vh - r.top) / (vh * 0.45));
                    const stageVal = 0.45 + 0.55 * Math.min(enter, leave);
                    el.style.setProperty('--stage', stageVal.toFixed(4));
                });

                peelEls.forEach((el) => {
                    const r = el.getBoundingClientRect();
                    const tail = clamp01((vh * 0.85 - r.bottom) / (vh * 0.85));
                    el.style.setProperty('--s5off', (tail * 0.45).toFixed(4));
                });

                if (pillEl && pillZones.length) {
                    const sampleY = vh * 0.82;
                    let activeZone = 'dark';
                    let hidePill = false;
                    for (let i = 0; i < pillZones.length; i++) {
                        const zr = pillZones[i].getBoundingClientRect();
                        if (zr.top <= sampleY && zr.bottom >= sampleY) {
                            activeZone = pillZones[i].getAttribute('data-pill-zone') || 'dark';
                            hidePill = pillZones[i].getAttribute('data-pill-hide') === 'true';
                            break;
                        }
                    }
                    if (pillEl.getAttribute('data-pill-theme') !== activeZone) {
                        pillEl.setAttribute('data-pill-theme', activeZone);
                    }
                    pillEl.style.opacity = hidePill ? '0' : '1';
                    pillEl.style.pointerEvents = hidePill ? 'none' : 'auto';
                    pillEl.style.transform = hidePill ? 'translateY(14px)' : '';
                }
            };

            const scheduleUpdate = () => {
                if (!ticking) {
                    ticking = true;
                    window.requestAnimationFrame(updateMotion);
                }
            };

            updateMotion();
            window.addEventListener('scroll', scheduleUpdate, { passive: true });
            window.addEventListener('resize', scheduleUpdate, { passive: true });
        })();

        /* =========================================================
           K71 PINNED CANVAS FRAME-SCRUB ENGINE
           - Supports custom admin/folder video frames + procedural 3D studio fallback
           - Progressive keyframe-first preloader (nearest-frame fallback, 0 flicker)
           - Delta-normalized exponential inertia + sub-frame optical dissolve
           ========================================================= */
        (function () {
            const stageEl = document.querySelector('[data-seq-stage]');
            const canvas = document.getElementById('k71-seq-canvas');
            const configEl = document.getElementById('k71-seq-config');
            if (!stageEl || !canvas || !configEl) return;

            const ctx = canvas.getContext('2d', { alpha: false, desynchronized: true }) || canvas.getContext('2d');
            if (!ctx) return;

            let cfg = { frames: [], source: 'fallback', smoothing: 0.12, count: 0 };
            try {
                cfg = Object.assign(cfg, JSON.parse(configEl.textContent || '{}'));
            } catch (e) {}

            const frameUrls = Array.isArray(cfg.frames) ? cfg.frames : [];
            const totalFrames = frameUrls.length;
            const hasRealFrames = totalFrames > 0;
            const smoothingBase = Math.min(0.35, Math.max(0.04, Number(cfg.smoothing) || 0.12));
            const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const stickyEl = stageEl.querySelector('.grail-seq-sticky');
            const counterEl = stageEl.querySelector('[data-seq-counter]');
            const barEl = stageEl.querySelector('[data-seq-bar]');
            const beatEls = Array.from(stageEl.querySelectorAll('[data-seq-beat]'));
            const tabLabels = Array.from(stageEl.querySelectorAll('[data-seq-tab-label]'));
            const jumpButtons = Array.from(stageEl.querySelectorAll('[data-seq-jump]'));

            const loadedFrames = new Array(totalFrames).fill(null);
            let loadedCount = 0;
            let cw = 1280;
            let ch = 720;
            let dpr = 1;

            let targetProgress = 0;
            let currentProgress = 0;
            let rafId = null;
            let lastTs = 0;
            let lastDrawKey = '';
            let prioritizeWindow = () => {};

            const clamp01 = (v) => (v < 0 ? 0 : v > 1 ? 1 : v);

            const resizeCanvas = () => {
                const box = stickyEl ? stickyEl.getBoundingClientRect() : stageEl.getBoundingClientRect();
                cw = Math.max(320, Math.round(box.width || window.innerWidth || 1280));
                ch = Math.max(360, Math.round(box.height || window.innerHeight || 720));
                dpr = Math.min(window.devicePixelRatio || 1, 2);
                const tw = Math.round(cw * dpr);
                const th = Math.round(ch * dpr);
                if (canvas.width !== tw || canvas.height !== th) {
                    canvas.width = tw;
                    canvas.height = th;
                }
                lastDrawKey = '';
                drawFrameAt(currentProgress);
            };

            const drawCoverImage = (img, alpha) => {
                if (!img || !img.naturalWidth || !img.naturalHeight) return;
                const iw = img.naturalWidth;
                const ih = img.naturalHeight;
                const scale = Math.max(cw / iw, ch / ih);
                const dw = iw * scale;
                const dh = ih * scale;
                const dx = (cw - dw) * 0.5;
                const dy = (ch - dh) * 0.5;
                if (alpha < 1) ctx.globalAlpha = alpha;
                ctx.drawImage(img, dx, dy, dw, dh);
                if (alpha < 1) ctx.globalAlpha = 1;
            };

            const findNearestLoaded = (idx) => {
                if (loadedFrames[idx]) return { idx, img: loadedFrames[idx] };
                for (let offset = 1; offset < totalFrames; offset++) {
                    const fwd = idx + offset;
                    if (fwd < totalFrames && loadedFrames[fwd]) {
                        return { idx: fwd, img: loadedFrames[fwd] };
                    }
                    const bwd = idx - offset;
                    if (bwd >= 0 && loadedFrames[bwd]) {
                        return { idx: bwd, img: loadedFrames[bwd] };
                    }
                }
                return null;
            };

            // Procedural 3D Architectural Monolith Studio Fallback (while no custom video frames exist)
            const studioDust = Array.from({ length: 34 }, (_, i) => ({
                x: ((i * 73) % 100) / 100,
                y: ((i * 41) % 100) / 100,
                z: 0.4 + ((i * 19) % 100) / 100,
                r: 0.9 + (i % 3) * 0.65,
                tint: i % 3 === 0 ? 'rgba(201,162,74,' : 'rgba(236,210,243,',
            }));

            const drawProceduralStudio = (p) => {
                ctx.fillStyle = '#020203';
                ctx.fillRect(0, 0, cw, ch);

                const isDesktop = cw >= 960;
                const focalX = isDesktop ? cw * 0.68 : cw * 0.54;
                const focalY = isDesktop ? ch * 0.49 : ch * 0.42;
                const radius = Math.min(cw, ch) * (isDesktop ? 0.30 : 0.28);

                // Moving studio keylight & orchid fill glow
                const keyX = focalX + Math.cos(p * Math.PI * 1.5 - 0.8) * radius * 0.95;
                const keyY = focalY - Math.sin(p * Math.PI) * radius * 0.65;
                const aura = ctx.createRadialGradient(keyX, keyY, radius * 0.08, focalX, focalY, radius * 2.35);
                aura.addColorStop(0, 'rgba(236, 210, 243, 0.24)');
                aura.addColorStop(0.38, 'rgba(201, 162, 74, 0.12)');
                aura.addColorStop(0.75, 'rgba(34, 24, 38, 0.18)');
                aura.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = aura;
                ctx.fillRect(0, 0, cw, ch);

                // Subtle architectural concentric perspective rings
                ctx.save();
                ctx.translate(focalX, focalY);
                ctx.rotate(p * 0.22);
                ctx.strokeStyle = 'rgba(239, 233, 242, 0.06)';
                ctx.lineWidth = 1;
                [1.18, 1.48, 1.82].forEach((m, idx) => {
                    ctx.beginPath();
                    ctx.ellipse(0, 0, radius * m, radius * m * (0.42 + Math.sin(p * Math.PI) * 0.14), 0.28 - idx * 0.08, 0, Math.PI * 2);
                    ctx.stroke();
                });
                ctx.restore();

                // Asymmetric Sculpted Diamond-Bevel Marble Slab / Atelier Monolith
                const yaw = -1.05 + p * 2.25;
                const pitch = 0.22 - Math.sin(p * Math.PI) * 0.14;
                const scalePulse = 0.96 + Math.sin(p * Math.PI) * 0.16;
                const fov = radius * 3.4;

                const cosY = Math.cos(yaw), sinY = Math.sin(yaw);
                const cosX = Math.cos(pitch), sinX = Math.sin(pitch);

                const project = (vx, vy, vz) => {
                    const x1 = vx * cosY - vz * sinY;
                    const z1 = vx * sinY + vz * cosY;
                    const y2 = vy * cosX - z1 * sinX;
                    const z2 = vy * sinX + z1 * cosX;
                    const persp = fov / (fov + z2 + radius * 1.15);
                    return {
                        x: focalX + x1 * persp * scalePulse,
                        y: focalY + y2 * persp * scalePulse,
                        z: z2,
                    };
                };

                const h = radius * 0.96;
                const wTop = radius * 0.56;
                const wMid = radius * 0.78;
                const wBot = radius * 0.48;
                const dMid = radius * 0.24;
                const dCap = radius * 0.16;

                const slabRing = (rx, rz, y, cutX = 0.74, cutZ = 0.74) => [
                    project(-rx * cutX, y, -rz),
                    project(rx * cutX, y, -rz),
                    project(rx, y, -rz * cutZ),
                    project(rx, y, rz * cutZ),
                    project(rx * cutX, y, rz),
                    project(-rx * cutX, y, rz),
                    project(-rx, y, rz * cutZ),
                    project(-rx, y, -rz * cutZ),
                ];

                const rTop = slabRing(wTop, dCap, -h * 0.78);
                const rMid = slabRing(wMid, dMid, -h * 0.04);
                const rBot = slabRing(wBot, dCap * 1.1, h * 0.76);
                const sides = 8;

                const faces = [];
                const pushBand = (upper, lower, shine) => {
                    for (let i = 0; i < sides; i++) {
                        const j = (i + 1) % sides;
                        const pts = [upper[i], upper[j], lower[j], lower[i]];
                        const cross = (pts[1].x - pts[0].x) * (pts[2].y - pts[0].y) - (pts[1].y - pts[0].y) * (pts[2].x - pts[0].x);
                        const avgZ = (pts[0].z + pts[1].z + pts[2].z + pts[3].z) * 0.25;
                        const dxCenter = (pts[0].x + pts[2].x) * 0.5 - keyX;
                        const lightDot = clamp01(0.68 - Math.abs(dxCenter) / (radius * 2.2));
                        faces.push({ pts, z: avgZ, front: cross > 0, isMain: (i === 0 || i === 4), light: lightDot * shine });
                    }
                };

                pushBand(rTop, rMid, 1.0);
                pushBand(rMid, rBot, 0.84);

                const topCenterZ = rTop.reduce((acc, v) => acc + v.z, 0) / sides;
                faces.push({ pts: rTop, z: topCenterZ, front: true, isCap: true, light: 0.94 });

                faces.sort((a, b) => b.z - a.z);

                faces.forEach((f) => {
                    if (!f.front && !f.isCap) return;
                    ctx.beginPath();
                    ctx.moveTo(f.pts[0].x, f.pts[0].y);
                    for (let k = 1; k < f.pts.length; k++) {
                        ctx.lineTo(f.pts[k].x, f.pts[k].y);
                    }
                    ctx.closePath();

                    if (f.isCap) {
                        const capGrad = ctx.createLinearGradient(focalX - wTop, focalY - h, focalX + wTop, focalY);
                        capGrad.addColorStop(0, 'rgba(236, 210, 243, 0.36)');
                        capGrad.addColorStop(0.5, 'rgba(34, 24, 38, 0.90)');
                        capGrad.addColorStop(1, 'rgba(201, 162, 74, 0.32)');
                        ctx.fillStyle = capGrad;
                    } else if (f.isMain) {
                        const gx = f.pts[0].x, gy = f.pts[0].y;
                        const gx2 = f.pts[2].x, gy2 = f.pts[2].y;
                        const faceGrad = ctx.createLinearGradient(gx, gy, gx2, gy2);
                        const l = f.light;
                        faceGrad.addColorStop(0, `rgba(${Math.round(28 + l * 64)}, ${Math.round(22 + l * 46)}, ${Math.round(36 + l * 74)}, 0.94)`);
                        faceGrad.addColorStop(0.48, `rgba(${Math.round(46 + l * 58)}, ${Math.round(36 + l * 42)}, ${Math.round(54 + l * 62)}, 0.94)`);
                        faceGrad.addColorStop(1, `rgba(${Math.round(22 + l * 38)}, ${Math.round(18 + l * 28)}, ${Math.round(26 + l * 40)}, 0.94)`);
                        ctx.fillStyle = faceGrad;
                    } else {
                        const l = f.light;
                        const rCol = Math.round(20 + l * 42);
                        const gCol = Math.round(16 + l * 28);
                        const bCol = Math.round(24 + l * 44);
                        ctx.fillStyle = `rgba(${rCol}, ${gCol}, ${bCol}, 0.92)`;
                    }
                    ctx.fill();

                    ctx.strokeStyle = f.light > 0.56
                        ? 'rgba(236, 210, 243, 0.42)'
                        : 'rgba(201, 162, 74, 0.24)';
                    ctx.lineWidth = 1.1;
                    ctx.stroke();

                    // Luminous marble vein accent on wide sculpted faces
                    if (f.isMain && Math.abs(f.pts[1].x - f.pts[0].x) > 36) {
                        ctx.beginPath();
                        ctx.moveTo(
                            f.pts[0].x * 0.8 + f.pts[3].x * 0.2,
                            f.pts[0].y * 0.72 + f.pts[3].y * 0.28
                        );
                        ctx.quadraticCurveTo(
                            (f.pts[0].x + f.pts[2].x) * 0.5,
                            (f.pts[1].y + f.pts[3].y) * 0.52,
                            f.pts[1].x * 0.25 + f.pts[2].x * 0.75,
                            f.pts[1].y * 0.35 + f.pts[2].y * 0.65
                        );
                        ctx.strokeStyle = 'rgba(201, 162, 74, 0.26)';
                        ctx.lineWidth = 1.2;
                        ctx.stroke();
                    }
                });

                // Orbiting subtle luminous studio motes
                studioDust.forEach((d, idx) => {
                    const drift = (p * (0.4 + d.z * 0.7) + d.x) % 1;
                    const px = cw * drift;
                    const py = ch * ((d.y + Math.sin(p * Math.PI * 2 + idx) * 0.04 + 1) % 1);
                    const alpha = (0.14 + 0.28 * Math.abs(Math.sin(p * Math.PI + idx))).toFixed(3);
                    ctx.fillStyle = d.tint + alpha + ')';
                    ctx.beginPath();
                    ctx.arc(px, py, d.r, 0, Math.PI * 2);
                    ctx.fill();
                });
            };

            const drawFrameAt = (p) => {
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                if (!hasRealFrames) {
                    const key = `proc_${Math.round(p * 1000)}_${cw}x${ch}`;
                    if (key === lastDrawKey) return;
                    lastDrawKey = key;
                    drawProceduralStudio(p);
                    return;
                }

                const exact = p * Math.max(0, totalFrames - 1);
                const lo = Math.floor(exact);
                const hi = Math.min(totalFrames - 1, lo + 1);
                const frac = exact - lo;
                const nearestIdx = Math.min(totalFrames - 1, Math.max(0, Math.round(exact)));
                const key = `real_${lo}_${hi}_${Math.round(frac * 20)}_${loadedCount}_${cw}x${ch}`;
                if (key === lastDrawKey) return;
                lastDrawKey = key;

                ctx.fillStyle = '#000000';
                ctx.fillRect(0, 0, cw, ch);

                const imgLo = loadedFrames[lo];
                const imgHi = loadedFrames[hi];

                if (!loadedFrames[nearestIdx]) {
                    prioritizeWindow(nearestIdx);
                }

                if (imgLo && imgHi && lo !== hi && frac > 0.03 && frac < 0.97) {
                    drawCoverImage(imgLo, 1);
                    drawCoverImage(imgHi, frac);
                } else {
                    const best = loadedFrames[nearestIdx]
                        ? { idx: nearestIdx, img: loadedFrames[nearestIdx] }
                        : findNearestLoaded(nearestIdx);
                    if (best) {
                        drawCoverImage(best.img, 1);
                    } else {
                        drawProceduralStudio(p);
                    }
                }
            };

            const updateOverlayUI = (p) => {
                if (barEl) {
                    barEl.style.setProperty('--seq-p', p.toFixed(4));
                }

                if (counterEl) {
                    if (hasRealFrames) {
                        const frameNum = Math.min(totalFrames, Math.max(1, Math.round(p * (totalFrames - 1)) + 1));
                        counterEl.textContent = `${String(frameNum).padStart(2, '0')} / ${String(totalFrames).padStart(2, '0')}`;
                    } else {
                        const deg = Math.round(p * 360);
                        counterEl.textContent = `${String(deg).padStart(3, '0')}°`;
                    }
                }

                const computeBeatOpacity = (idx, val) => {
                    if (idx === 0) {
                        if (val <= 0.24) return 1;
                        if (val >= 0.36) return 0;
                        return 1 - (val - 0.24) / 0.12;
                    }
                    if (idx === 1) {
                        if (val <= 0.28 || val >= 0.72) return 0;
                        if (val < 0.38) return (val - 0.28) / 0.10;
                        if (val > 0.62) return 1 - (val - 0.62) / 0.10;
                        return 1;
                    }
                    if (val <= 0.64) return 0;
                    if (val >= 0.74) return 1;
                    return (val - 0.64) / 0.10;
                };

                let strongestIdx = 0;
                let strongestVal = -1;
                beatEls.forEach((el, idx) => {
                    const op = clamp01(computeBeatOpacity(idx, p));
                    if (op > strongestVal) {
                        strongestVal = op;
                        strongestIdx = idx;
                    }
                    const yOffset = (1 - op) * (idx === 0 ? -16 : 22);
                    el.style.setProperty('--beat-o', op.toFixed(4));
                    el.style.setProperty('--beat-y', `${yOffset.toFixed(2)}px`);
                    el.style.setProperty('--beat-pe', op > 0.45 ? 'auto' : 'none');
                });

                tabLabels.forEach((lbl, idx) => {
                    lbl.style.color = idx === strongestIdx ? '#FFFFFF' : 'rgba(239, 233, 242, 0.55)';
                });
            };

            const computeTargetProgress = () => {
                const rect = stageEl.getBoundingClientRect();
                const stickyTop = window.innerWidth >= 640 ? 64 : 56;
                const stickyH = (stickyEl ? stickyEl.offsetHeight : (window.innerHeight - stickyTop)) || 600;
                const scrollSpan = Math.max(1, rect.height - stickyH);
                const elapsed = stickyTop - rect.top;
                return clamp01(elapsed / scrollSpan);
            };

            const tick = (ts) => {
                const dt = lastTs ? Math.min(40, Math.max(4, ts - lastTs)) : 16.67;
                lastTs = ts;

                targetProgress = computeTargetProgress();
                const alpha = reducedMotion ? 1 : (1 - Math.pow(1 - smoothingBase, dt / 16.67));
                const diff = targetProgress - currentProgress;

                if (Math.abs(diff) < 0.00035 || reducedMotion) {
                    currentProgress = targetProgress;
                    rafId = null;
                } else {
                    currentProgress += diff * alpha;
                    rafId = window.requestAnimationFrame(tick);
                }

                drawFrameAt(currentProgress);
                updateOverlayUI(currentProgress);
            };

            const kickLoop = () => {
                targetProgress = computeTargetProgress();
                if (!rafId) {
                    lastTs = 0;
                    rafId = window.requestAnimationFrame(tick);
                }
            };

            // Keyframe-first progressive image queue with on-demand scroll-window prioritization
            // IMPORTANT: Do not start multi-worker recursive streaming until window.load completes
            // (or user scrolls near the stage), so initial Hero & window.load finish immediately.
            let streamingBegun = false;
            if (hasRealFrames) {
                const queue = [];
                const urgentQueue = [];
                const queuedSet = new Set();
                const startedSet = new Set();

                const pushIdx = (i) => {
                    if (i >= 0 && i < totalFrames && !queuedSet.has(i)) {
                        queuedSet.add(i);
                        queue.push(i);
                    }
                };
                pushIdx(0);
                pushIdx(totalFrames - 1);
                for (let k = 1; k < 16; k++) pushIdx(Math.round((k * (totalFrames - 1)) / 16));
                for (let stride = 8; stride >= 1; stride = Math.floor(stride / 2)) {
                    for (let i = 0; i < totalFrames; i += stride) pushIdx(i);
                    if (stride === 1) break;
                }

                const fetchFrame = (idx, onDone) => {
                    const img = new Image();
                    img.decoding = 'async';
                    if ('fetchPriority' in img) {
                        img.fetchPriority = idx === 0 ? 'low' : 'auto';
                    }
                    img.onload = () => {
                        const markReady = () => {
                            loadedFrames[idx] = img;
                            loadedCount++;
                            lastDrawKey = '';
                            drawFrameAt(currentProgress);
                            if (onDone) onDone();
                        };
                        if (typeof img.decode === 'function') {
                            img.decode().then(markReady).catch(markReady);
                        } else {
                            markReady();
                        }
                    };
                    img.onerror = () => {
                        if (onDone) onDone();
                    };
                    img.src = frameUrls[idx];
                };

                let cursor = 0;
                const nextFrameIndex = () => {
                    while (urgentQueue.length > 0) {
                        const ui = urgentQueue.shift();
                        if (!startedSet.has(ui) && !loadedFrames[ui]) {
                            startedSet.add(ui);
                            return ui;
                        }
                    }
                    while (cursor < queue.length) {
                        const qi = queue[cursor++];
                        if (!startedSet.has(qi) && !loadedFrames[qi]) {
                            startedSet.add(qi);
                            return qi;
                        }
                    }
                    return -1;
                };

                const loadNext = () => {
                    const idx = nextFrameIndex();
                    if (idx === -1) return;
                    fetchFrame(idx, loadNext);
                };

                let workersStarted = 0;
                const totalConcurrency = Math.min(6, queue.length);
                const startWorkers = (targetWorkers) => {
                    while (workersStarted < targetWorkers) {
                        workersStarted++;
                        loadNext();
                    }
                };

                const onScrollBeforeLoad = () => {
                    if (window.scrollY > 80) {
                        beginStreaming();
                    }
                };

                const beginStreaming = () => {
                    if (streamingBegun) return;
                    streamingBegun = true;
                    window.removeEventListener('scroll', onScrollBeforeLoad);
                    startWorkers(totalConcurrency);
                };

                prioritizeWindow = (centerIdx) => {
                    const priority = [centerIdx];
                    for (let offset = 1; offset <= 4; offset++) {
                        priority.push(centerIdx + offset, centerIdx - offset);
                    }
                    urgentQueue.length = 0;
                    for (const candidate of priority) {
                        if (candidate >= 0 && candidate < totalFrames && !loadedFrames[candidate] && !startedSet.has(candidate)) {
                            urgentQueue.push(candidate);
                        }
                    }
                    if (!streamingBegun && window.scrollY > 80) {
                        beginStreaming();
                    }
                };

                // Single non-blocking first-frame warmup so canvas has real imagery right away
                if (!startedSet.has(0)) {
                    startedSet.add(0);
                    fetchFrame(0, null);
                }

                if (document.readyState === 'complete') {
                    setTimeout(beginStreaming, 0);
                } else {
                    window.addEventListener('load', () => {
                        setTimeout(beginStreaming, 20);
                    }, { once: true });
                    window.addEventListener('scroll', onScrollBeforeLoad, { passive: true });
                }
            }

            // Chapter quick-jump buttons
            jumpButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const beatIdx = Number(btn.getAttribute('data-seq-jump') || 0);
                    const targetStops = [0.08, 0.50, 0.90];
                    const pStop = targetStops[beatIdx] ?? 0;
                    const rect = stageEl.getBoundingClientRect();
                    const stageTopDoc = rect.top + window.scrollY;
                    const stickyTop = window.innerWidth >= 640 ? 64 : 56;
                    const stickyH = (stickyEl ? stickyEl.offsetHeight : (window.innerHeight - stickyTop)) || 600;
                    const scrollSpan = Math.max(1, rect.height - stickyH);
                    const destY = Math.max(0, stageTopDoc - stickyTop + scrollSpan * pStop);
                    window.scrollTo({ top: destY, behavior: reducedMotion ? 'auto' : 'smooth' });
                });
            });

            if ('ResizeObserver' in window && stickyEl) {
                new ResizeObserver(resizeCanvas).observe(stickyEl);
            } else {
                window.addEventListener('resize', resizeCanvas, { passive: true });
            }

            window.addEventListener('scroll', kickLoop, { passive: true });
            resizeCanvas();
            targetProgress = computeTargetProgress();
            currentProgress = targetProgress;
            drawFrameAt(currentProgress);
            updateOverlayUI(currentProgress);

            window.__k71ScrollSeq = {
                getState: () => ({
                    mode: hasRealFrames ? 'frames' : 'procedural',
                    totalFrames,
                    loadedCount,
                    progress: Number(currentProgress.toFixed(4)),
                    targetProgress: Number(targetProgress.toFixed(4)),
                    frameIndex: hasRealFrames ? Math.round(currentProgress * Math.max(0, totalFrames - 1)) : 0,
                }),
            };
        })();
    </script>
    @endpush
</x-layouts.public>
