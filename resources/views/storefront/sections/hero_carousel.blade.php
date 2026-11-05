@php
    $heroCfg = $config['hero'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    
    $flagship = $featuredProducts->first() ?? $products->first();
    $bannerUrl = $brandCfg['banner_url'] ?? $vendor->banner_url;
    if (empty($bannerUrl) && $flagship) {
        $firstImg = $flagship->images->first();
        $bannerUrl = $firstImg ? \App\Services\ImageService::url($firstImg->path, 1200) : null;
    }
    
    $headline = $heroCfg['title'] ?? $brandCfg['hero_headline'] ?? $vendor->shop_name;
    $subtitle = $heroCfg['subtitle'] ?? $brandCfg['tagline'] ?? $vendor->tagline ?? 'Direct Workshop Dispatch · Verified Merchant';
    $buttonText = $heroCfg['button_text'] ?? 'Explore Catalog';
    $buttonLink = $heroCfg['link'] ?? '#catalog';

    // Optional multi-slides support if configured
    $slides = $heroCfg['slides'] ?? [];
    if (empty($slides) && !empty($bannerUrl)) {
        $slides = [
            [
                'image' => $bannerUrl,
                'title' => $headline,
                'subtitle' => $subtitle,
                'link' => $buttonLink,
                'button_text' => $buttonText,
            ]
        ];
    }
@endphp

<!-- CUSTOMIZABLE HERO CAROUSEL / BANNER -->
<section id="hero" class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-6 sm:pb-8">
    <div x-data="{
            activeSlide: 0,
            slidesCount: {{ count($slides) ?: 1 }},
            timer: null,
            init() {
                if (this.slidesCount > 1) {
                    this.timer = setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                    }, 5000);
                }
            }
         }" 
         class="relative rounded-3xl overflow-hidden shadow-[0_10px_35px_rgba(0,0,0,0.06)] border border-stone-200/80 dark:border-white/10 aspect-[16/9] sm:aspect-[21/9] lg:aspect-[24/9] min-h-[320px] sm:min-h-[380px] bg-stone-900 flex items-center">

        <!-- Slide Background Images -->
        @if(!empty($slides))
            @foreach($slides as $idx => $slide)
                <div x-show="activeSlide === {{ $idx }}"
                     x-transition:enter="transition ease-out duration-700"
                     x-transition:enter-start="opacity-0 scale-102"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-500"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-98"
                     class="absolute inset-0 z-0">
                    <img src="{{ $slide['image'] }}" 
                         alt="{{ $slide['title'] ?? $headline }}" 
                         class="w-full h-full object-cover object-center filter brightness-[0.72]">
                </div>
            @endforeach
        @elseif($flagship && $flagship->images->first())
            <div class="absolute inset-0 z-0">
                <img src="{{ \App\Services\ImageService::url($flagship->images->first()->path, 1200) }}" 
                     alt="{{ $headline }}" 
                     class="w-full h-full object-cover object-center filter brightness-[0.70]">
            </div>
        @else
            <!-- Neutral Architectural Surface -->
            <div class="absolute inset-0 z-0 bg-gradient-to-r from-stone-900 via-stone-850 to-stone-900"></div>
        @endif

        <!-- Subtle Dark Vignette Overlay for Text Legibility -->
        <div class="absolute inset-0 z-10 bg-gradient-to-t sm:bg-gradient-to-r from-black/85 via-black/50 to-transparent"></div>

        <!-- Content Stage -->
        <div class="relative z-20 max-w-2xl px-6 sm:px-12 lg:px-16 py-8 text-white space-y-4">
            
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/15 backdrop-blur-md border border-white/20 text-white">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Verified K71 Storefront</span>
            </div>

            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tight font-heading leading-[1.15] drop-shadow-sm text-white">
                {{ $headline }}
            </h1>

            <p class="text-xs sm:text-sm text-stone-200 line-clamp-2 max-w-lg leading-relaxed font-sans">
                {{ $subtitle }}
            </p>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ $buttonLink }}" 
                   class="px-6 sm:px-8 py-3 sm:py-3.5 rounded-2xl text-xs sm:text-sm font-bold uppercase tracking-wider transition-all duration-200 shadow-lg hover:scale-102 flex items-center gap-2"
                   style="background-color: var(--sf-accent, #c59b27); color: #000000;">
                    <span>{{ $buttonText }}</span>
                    <span>&darr;</span>
                </a>

                @if($flagship)
                    <a href="{{ route('product.show', $flagship->slug) }}" 
                       class="px-5 py-3 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs sm:text-sm font-semibold border border-white/25 transition">
                        Featured Drop &rarr;
                    </a>
                @endif
            </div>

        </div>

        <!-- Slide Indicators (If Multi-Slide) -->
        @if(count($slides) > 1)
            <div class="absolute bottom-4 right-6 z-20 flex items-center gap-2">
                @foreach($slides as $idx => $slide)
                    <button type="button" 
                            @click="activeSlide = {{ $idx }}" 
                            class="h-1.5 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $idx }} ? 'w-8 bg-white' : 'w-2 bg-white/40'"></button>
                @endforeach
            </div>
        @endif

    </div>
</section>
