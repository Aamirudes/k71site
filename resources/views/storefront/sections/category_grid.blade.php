@php
    $title = $section['title'] ?? 'Explore by Category';
    $subtitle = $section['subtitle'] ?? 'Browse curated disciplines';
@endphp

@if($vendorCategories->isNotEmpty())
<section id="{{ $section['id'] }}" 
         data-section-id="{{ $section['id'] }}"
         class="py-12 sm:py-16 border-b border-stone-200/60"
         style="background-color: var(--sf-surface, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 sm:mb-10 text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 font-heading">
                {{ $title }}
            </h2>
            @if(!empty($subtitle))
                <p class="text-xs sm:text-sm text-stone-500 mt-2">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($vendorCategories as $cat)
                @php
                    $sampleImg = $cat->sample_image ? (str_starts_with($cat->sample_image, 'http') ? $cat->sample_image : asset('storage/' . $cat->sample_image)) : null;
                @endphp
                <a href="{{ $vendor->storefrontUrl(['category' => $cat->slug], 'catalog') }}" 
                   class="group relative rounded-2xl overflow-hidden aspect-[4/3] bg-stone-100 border border-stone-200/80 shadow-2xs hover:shadow-lg transition-all duration-300">
                    @if($sampleImg)
                        <img src="{{ $sampleImg }}" 
                             alt="{{ $cat->name }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-stone-950/30 to-transparent"></div>
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-stone-100 to-stone-200 flex items-center justify-center">
                            <span class="text-3xl">🔲</span>
                        </div>
                    @endif

                    <div class="absolute bottom-0 inset-x-0 p-4 text-white">
                        <h3 class="text-sm sm:text-base font-bold font-heading drop-shadow-xs">
                            {{ $cat->name }}
                        </h3>
                        <span class="text-[11px] text-stone-200 opacity-90 drop-shadow-xs">
                            {{ $cat->products_count }} {{ Str::plural('item', $cat->products_count) }} →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

