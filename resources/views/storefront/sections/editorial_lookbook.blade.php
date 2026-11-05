@php
    $title = $section['title'] ?? 'The Seasonal Lookbook';
    $subtitle = $section['subtitle'] ?? 'An editorial exploration of texture and craft';
    $quote = $section['quote'] ?? 'True luxury lives in the thread count, the patient hand-weave, and the timeless elegance of unhurried design.';
    
    // Sample product photos from vendor's catalog to construct lookbook
    $lookbookImages = $products->flatMap(function($p) {
        return $p->images;
    })->take(3)->pluck('path')->map(fn($p) => \App\Services\ImageService::url($p, 800))->all();

    $fallbackBanners = array_filter([$config['brand']['banner_url'] ?? $vendor->banner_url]);
    $displayImages = !empty($lookbookImages) ? $lookbookImages : $fallbackBanners;
@endphp

<section id="lookbook" 
         data-section-id="{{ $section['id'] }}"
         class="py-16 sm:py-24 border-y border-stone-200/60 overflow-hidden"
         style="background-color: var(--sf-surface, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-3xl mb-12 sm:mb-16">
            <span class="text-xs uppercase font-bold tracking-widest block mb-2"
                  style="color: var(--sf-accent, #c59b27);">
                Editorial Campaign
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-stone-900 font-heading leading-tight">
                {{ $title }}
            </h2>
            @if(!empty($subtitle))
                <p class="text-stone-500 text-sm sm:text-base mt-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <!-- Asymmetric Editorial Photo Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">
            
            <!-- Left Large Hero Image (7 cols) -->
            <div class="lg:col-span-7">
                <div class="relative aspect-[4/5] sm:aspect-[16/11] rounded-2xl overflow-hidden shadow-lg bg-stone-100 border border-stone-200/80 group">
                    @if(!empty($displayImages[0]))
                        <img src="{{ $displayImages[0] }}" 
                             alt="{{ $title }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-103"
                             loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-stone-200 text-stone-400">
                            <span class="text-4xl">📸</span>
                        </div>
                    @endif
                    <div class="absolute bottom-4 left-4 px-3 py-1.5 rounded-lg bg-stone-950/70 text-white text-[11px] font-medium backdrop-blur-xs">
                        Lookbook Series 01
                    </div>
                </div>
            </div>

            <!-- Right Column with Quote & Secondary Photo (5 cols) -->
            <div class="lg:col-span-5 flex flex-col justify-between gap-6 sm:gap-8">
                <!-- Pull Quote Box -->
                <div class="p-6 sm:p-8 rounded-2xl bg-stone-50 border border-stone-200/80 shadow-2xs">
                    <span class="text-3xl font-serif text-stone-300 block mb-2">“</span>
                    <blockquote class="text-base sm:text-lg italic font-heading text-stone-800 leading-relaxed">
                        {{ $quote }}
                    </blockquote>
                    <div class="mt-4 pt-4 border-t border-stone-200/60 flex items-center justify-between text-xs text-stone-500 font-semibold">
                        <span>{{ $config['brand']['store_name'] ?? $vendor->shop_name }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-stone-400">Atelier Archives</span>
                    </div>
                </div>

                <!-- Secondary Detail Image -->
                @if(!empty($displayImages[1]))
                    <div class="relative aspect-[16/9] rounded-2xl overflow-hidden shadow-md bg-stone-100 border border-stone-200/80 group">
                        <img src="{{ $displayImages[1] }}" 
                             alt="Detail" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-103"
                             loading="lazy">
                        <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded bg-stone-950/70 text-white text-[10px] font-semibold backdrop-blur-xs">
                            Detail Texture
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>
</section>

