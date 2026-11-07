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
         style="position: relative; width: 100%; height: 100vh; min-height: 560px; max-height: 1080px; overflow: hidden; background-color: #09090b; flex-shrink: 0;"
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
                 if (e.changedTouches && e.changedTouches[0]) {
                     this.touchStartX = e.changedTouches[0].screenX;
                 }
                 this.stopAutoplay();
             },
             handleTouchEnd(e) {
                 if (e.changedTouches && e.changedTouches[0]) {
                     this.touchEndX = e.changedTouches[0].screenX;
                     const diff = this.touchStartX - this.touchEndX;
                     if (Math.abs(diff) > 40) {
                         if (diff > 0) {
                             this.nextSlide();
                         } else {
                             this.prevSlide();
                         }
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
             style="{{ $idx === 0 ? 'display: block;' : 'display: none;' }} position: absolute; inset: 0; width: 100%; height: 100%;">

            <!-- High-Res Background Image with Fast Loading & Preload -->
            <img src="{{ $slideImg }}" 
                 alt="{{ $slide['title'] ?? $headline }}" 
                 class="w-full h-full object-cover object-center filter contrast-[1.03]"
                 style="width: 100%; height: 100%; object-fit: cover; object-position: center;"
                 {{ $idx === 0 ? 'loading=eager fetchpriority=high' : 'loading=lazy' }}>

            <!-- Top Header Protection Gradient (Ensures Transparent Header Is Razor Sharp) -->
            <div class="absolute inset-x-0 top-0 h-44 bg-gradient-to-b from-black/75 via-black/35 to-transparent pointer-events-none z-10"></div>

            <!-- Bottom Content Stage Vignette (Deep Cinematic Luxury Gradient) -->
            <div class="absolute inset-x-0 bottom-0 h-[65%] sm:h-[60%] bg-gradient-to-t from-black/95 via-black/50 to-transparent pointer-events-none z-10"></div>

            <!-- Content Stage: Lower-Third Placement Keeps Subject, Model & Photos Unobstructed -->
            <div class="absolute inset-x-0 bottom-0 z-20 pb-20 sm:pb-24 lg:pb-28 px-4 sm:px-8 lg:px-12 pointer-events-none">
                <div class="max-w-3xl mx-auto flex flex-col items-center text-center space-y-3 sm:space-y-4 pointer-events-auto">
                    
                    <!-- Subtitle / Tagline Eyebrow -->
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-[0.25em] text-white/85 drop-shadow">
                        {{ $slide['subtitle'] ?? $subtitle }}
                    </p>

                    <!-- Main Headline with Signature Fashion Display Typography -->
                    <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black font-heading text-white tracking-tight leading-[1.08] drop-shadow-[0_4px_24px_rgba(0,0,0,0.6)] max-w-2xl">
                        {{ $slide['title'] ?? $headline }}
                    </h1>

                    <!-- CTA Button (Liquid Glass / High-Contrast Boutique Pill) -->
                    <div class="pt-2 sm:pt-3">
                        <a href="{{ $slide['link'] ?? '#catalog' }}" 
                           class="group inline-flex items-center gap-3 px-8 sm:px-10 py-3.5 sm:py-4 rounded-full text-xs sm:text-sm font-extrabold uppercase tracking-widest text-black bg-white hover:bg-zinc-100 hover:scale-105 active:scale-95 transition-all duration-300 shadow-[0_12px_40px_rgba(0,0,0,0.5)] border border-white cursor-pointer">
                            <span>{{ $slide['button_text'] ?? 'Explore Boutique' }}</span>
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @endforeach

    <!-- Bottom Carousel Indicators (Sleek Minimalist Navigation Pills) -->
    <div class="absolute bottom-6 sm:bottom-8 left-4 sm:left-8 z-30 flex items-center gap-2 sm:gap-2.5">
        @foreach($slides as $idx => $s)
            <button type="button" 
                    @click="activeSlide = {{ $idx }}; stopAutoplay(); startAutoplay();" 
                    class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                    :class="activeSlide === {{ $idx }} ? 'w-8 sm:w-10 bg-white shadow-[0_0_12px_rgba(255,255,255,0.8)]' : 'w-2 bg-white/40 hover:bg-white/70'"
                    aria-label="Go to slide {{ $idx + 1 }}">
            </button>
        @endforeach
    </div>

    <!-- Right-Aligned Dual Arrow Capsule (Docked Right Side, Not Centered) -->
    <div class="absolute bottom-5 sm:bottom-7 right-4 sm:right-8 lg:right-10 z-30 flex items-center gap-1.5 bg-black/50 backdrop-blur-xl border border-white/20 p-1.5 rounded-full shadow-2xl">
        <button type="button" 
                @click="prevSlide(); stopAutoplay(); startAutoplay();"
                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/10 hover:bg-white text-white hover:text-black flex items-center justify-center transition-all duration-200 cursor-pointer hover:scale-105 active:scale-95"
                aria-label="Previous slide">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <span class="text-[10px] sm:text-xs font-mono font-bold text-white/90 px-1.5 select-none" 
              x-text="'0' + (activeSlide + 1) + ' / 0' + totalSlides"></span>

        <button type="button" 
                @click="nextSlide(); stopAutoplay(); startAutoplay();"
                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/10 hover:bg-white text-white hover:text-black flex items-center justify-center transition-all duration-200 cursor-pointer hover:scale-105 active:scale-95"
                aria-label="Next slide">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

</section>
