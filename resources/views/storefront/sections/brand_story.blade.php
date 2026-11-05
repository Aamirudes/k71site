@php
    $title = $section['title'] ?? 'The Atelier Story';
    $quote = $section['quote'] ?? 'Authentic craftsmanship directly from our looms and workbenches.';
    $text = $section['text'] ?? $config['brand']['description'] ?? $vendor->description ?? '';
    $imageUrl = $section['image_url'] ?? $config['brand']['banner_url'] ?? $vendor->banner_url;
    $establishedYear = $config['brand']['established_year'] ?? $vendor->created_at?->format('Y') ?? date('Y');
    $city = $config['brand']['city'] ?? $vendor->city ?? 'India';
@endphp

<section id="story" 
         data-section-id="{{ $section['id'] }}"
         class="py-16 sm:py-24 border-b border-stone-200/60"
         style="background-color: var(--sf-bg, #fafaf9);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-center">
            
            <!-- Left Narrative Column (7 cols) -->
            <div class="md:col-span-7">
                <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-stone-400 mb-3">
                    <span>Est. {{ $establishedYear }}</span>
                    <span>•</span>
                    <span>{{ $city }}</span>
                </div>

                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-stone-900 font-heading mb-6 leading-snug">
                    {{ $title }}
                </h2>

                @if(!empty($quote))
                    <div class="pl-4 border-l-2 border-stone-900 mb-6 italic text-stone-700 font-heading text-base sm:text-lg leading-relaxed">
                        “{{ $quote }}”
                    </div>
                @endif

                @if(!empty($text))
                    <div class="text-sm sm:text-base text-stone-600 space-y-4 leading-relaxed">
                        {!! nl2br(e($text)) !!}
                    </div>
                @endif

                <div class="mt-8 flex items-center gap-4 text-xs font-semibold text-stone-500">
                    <span class="w-8 h-px bg-stone-300"></span>
                    <span>Verified Merchant on K71 Marketplace</span>
                </div>
            </div>

            <!-- Right Visual Studio Portrait (5 cols) -->
            <div class="md:col-span-5">
                <div class="relative aspect-square sm:aspect-[4/5] rounded-2xl overflow-hidden shadow-lg border border-stone-200/80 bg-stone-100">
                    @if(!empty($imageUrl))
                        <img src="{{ $imageUrl }}" 
                             alt="{{ $title }}" 
                             class="w-full h-full object-cover"
                             loading="lazy">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-br from-stone-100 to-stone-200">
                            <span class="text-4xl mb-2">📜</span>
                            <span class="font-heading font-bold text-base text-stone-800">{{ $config['brand']['store_name'] ?? $vendor->shop_name }}</span>
                            <span class="text-xs text-stone-500 mt-1">Direct Workshop</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</section>

