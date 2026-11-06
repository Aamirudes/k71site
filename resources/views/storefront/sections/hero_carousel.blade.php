@php
    $heroCfg = $config['hero'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    
    $flagship = $featuredProducts->first() ?? $products->first();
    $bannerUrl = $brandCfg['banner_url'] ?? $vendor->banner_url;
    if (empty($bannerUrl) && $flagship) {
        $firstImg = $flagship->images->first();
        $bannerUrl = $firstImg ? \App\Services\ImageService::url($firstImg->path, 1600) : null;
    }
    
    $headline = $heroCfg['title'] ?? $brandCfg['hero_headline'] ?? $vendor->shop_name;
    $subtitle = $heroCfg['subtitle'] ?? $brandCfg['tagline'] ?? $vendor->tagline ?? 'Curated authentic collections handcrafted with distinction';
    $buttonText = $heroCfg['button_text'] ?? 'Explore Boutique';
    $buttonLink = $heroCfg['link'] ?? '#catalog';

    // 3 Curated Luxury Slides by default (customizable per vendor)
    $defaultSlides = [
        [
            'image' => $bannerUrl ?: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=2000&q=80',
            'title' => $headline,
            'subtitle' => $subtitle,
            'button_text' => $buttonText,
            'link' => $buttonLink,
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=2000&q=80',
            'title' => 'Artisan Editions 2026',
            'subtitle' => 'Handcrafted creations packaged directly from our workshop with doorstep insured delivery across India.',
            'button_text' => 'Discover New Drops',
            'link' => '#catalog',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=2000&q=80',
            'title' => 'Signature Heritage',
            'subtitle' => 'Verified authentic craftsmanship backed by K71 Buyer Protection and direct merchant support.',
            'button_text' => 'Shop Collection',
            'link' => '#catalog',
        ],
    ];

    // Guarantee that all 3 slides exist with high-res images and content for EVERY vendor
    $finalSlides = [];
    for ($i = 0; $i < 3; $i++) {
        $custom = $heroCfg['slides'][$i] ?? [];
        $def = $defaultSlides[$i];
        $finalSlides[] = [
            'image' => !empty($custom['image']) ? $custom['image'] : $def['image'],
            'title' => !empty($custom['title']) ? $custom['title'] : $def['title'],
            'subtitle' => !empty($custom['subtitle']) ? $custom['subtitle'] : $def['subtitle'],
            'button_text' => !empty($custom['button_text']) ? $custom['button_text'] : $def['button_text'],
            'link' => !empty($custom['link']) ? $custom['link'] : $def['link'],
        ];
    }
    $slides = $finalSlides;
@endphp

<!-- 100VH LUXURY AESTHETIC HERO IMAGE CAROUSEL WITH FAST SWIPE-TO-SLIDE -->
<section id="hero" 
         class="relative w-full h-screen min-h-[640px] max-h-[1100px] overflow-hidden bg-black select-none"
         x-data="{
             activeSlide: 0,
             totalSlides: {{ count($slides) }},
             touchStartX: 0,
             touchEndX: 0,
             timer: null,
             init() {
                 this.startAutoplay();
             },
             startAutoplay() {
                 this.stopAutoplay();
                 if (this.totalSlides > 1) {
                     this.timer = setInterval(() => {
                         this.nextSlide();
                     }, 6000);
                 }
             },
             stopAutoplay() {
                 if (this.timer) {
                     clearInterval(this.timer);
                     this.timer = null;
                 }
             },
             nextSlide() {
                 this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
             },
             prevSlide() {
                 this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
             },
             handleTouchStart(e) {
                 this.touchStartX = e.changedTouches[0].screenX;
                 this.stopAutoplay();
             },
             handleTouchEnd(e) {
                 this.touchEndX = e.changedTouches[0].screenX;
                 const diff = this.touchStartX - this.touchEndX;
                 if (Math.abs(diff) > 40) {
                     if (diff > 0) {
                         this.nextSlide();
                     } else {
                         this.prevSlide();
                     }
                 }
                 this.startAutoplay();
             }
         }"
         @mouseenter="stopAutoplay()"
         @mouseleave="startAutoplay()"
         @touchstart="handleTouchStart($event)"
         @touchend="handleTouchEnd($event)">

    <!-- Carousel Slides Track -->
    @foreach($slides as $idx => $slide)
        @php
            $slideImg = !empty($slide['image']) ? $slide['image'] : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=2000&q=80';
        @endphp
        <div x-show="activeSlide === {{ $idx }}"
             x-transition:enter="transition-all ease-[cubic-bezier(0.16,1,0.3,1)] duration-800"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition-all ease-[cubic-bezier(0.16,1,0.3,1)] duration-600"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute inset-0 w-full h-full"
             style="{{ $idx === 0 ? 'display: block;' : 'display: none;' }}">

            <!-- High-Res Background Image with Fast Loading & Preload -->
            <img src="{{ $slideImg }}" 
                 alt="{{ $slide['title'] ?? $headline }}" 
                 class="w-full h-full object-cover object-center filter brightness-[0.70] contrast-[1.05]"
                 {{ $idx === 0 ? 'loading=eager fetchpriority=high' : 'loading=lazy' }}>

            <!-- Deep Liquid Dark Vignette Gradient for Ultra Legibility -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/60 pointer-events-none"></div>

            <!-- Content Stage / Central Typography -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4 sm:px-8 max-w-4xl mx-auto z-20 space-y-5 sm:space-y-6 pt-16 sm:pt-20">
                
                <!-- Verified Merchant Pill -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-bold uppercase tracking-widest text-white/95 bg-white/10 backdrop-blur-xl border border-white/20 shadow-lg">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Verified Flagship Atelier · Direct Dispatch</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black font-heading text-white tracking-tight leading-[1.1] drop-shadow-md max-w-3xl">
                    {{ $slide['title'] ?? $headline }}
                </h1>

                <!-- Subtitle / Tagline -->
                <p class="text-sm sm:text-base md:text-lg text-white/85 max-w-xl font-normal leading-relaxed drop-shadow-sm line-clamp-3">
                    {{ $slide['subtitle'] ?? $subtitle }}
                </p>

                <!-- CTA Button -->
                <div class="pt-2 sm:pt-4">
                    <a href="{{ $slide['link'] ?? '#catalog' }}" 
                       class="inline-flex items-center gap-2.5 px-7 sm:px-9 py-3.5 sm:py-4 rounded-full text-xs sm:text-sm font-black uppercase tracking-wider text-black bg-white hover:bg-zinc-100 hover:scale-105 active:scale-95 transition-all duration-300 shadow-[0_10px_35px_rgba(255,255,255,0.25)] border border-white/80 cursor-pointer">
                        <span>{{ $slide['button_text'] ?? 'Explore Boutique' }}</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    @endforeach

    <!-- Bottom Carousel Indicators (3 Fast Navigation Pills) -->
    <div class="absolute bottom-10 sm:bottom-12 inset-x-0 z-30 flex items-center justify-center gap-2 sm:gap-3">
        @foreach($slides as $idx => $s)
            <button type="button" 
                    @click="activeSlide = {{ $idx }}; stopAutoplay(); startAutoplay();" 
                    class="h-1.5 sm:h-2 rounded-full transition-all duration-300 cursor-pointer"
                    :class="activeSlide === {{ $idx }} ? 'w-8 sm:w-12 bg-white shadow-lg' : 'w-2 sm:w-3 bg-white/35 hover:bg-white/60'"
                    aria-label="Go to slide {{ $idx + 1 }}">
            </button>
        @endforeach
    </div>

    <!-- Previous / Next Sleek Arrow Buttons -->
    <button type="button" 
            @click="prevSlide(); stopAutoplay(); startAutoplay();"
            class="hidden sm:flex absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-black/35 hover:bg-black/60 text-white backdrop-blur-md border border-white/20 items-center justify-center transition-all hover:scale-110 cursor-pointer"
            aria-label="Previous slide">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button type="button" 
            @click="nextSlide(); stopAutoplay(); startAutoplay();"
            class="hidden sm:flex absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-black/35 hover:bg-black/60 text-white backdrop-blur-md border border-white/20 items-center justify-center transition-all hover:scale-110 cursor-pointer"
            aria-label="Next slide">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <!-- Smooth Scroll Down Prompt -->
    <a href="#catalog" 
       class="absolute bottom-2 inset-x-0 z-20 flex flex-col items-center justify-center text-white/60 hover:text-white transition-colors group">
        <span class="text-[9px] uppercase tracking-widest font-bold">Scroll to Explore</span>
        <svg class="w-4 h-4 animate-bounce mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </a>

</section>
