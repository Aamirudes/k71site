@php
    $title = $section['title'] ?? 'Browse by Department';
    $subtitle = $section['subtitle'] ?? 'Select a category to filter collections';
    $activeCat = request('category');
@endphp

@if($vendorCategories->isNotEmpty())
<section id="{{ $section['id'] }}" 
         data-section-id="{{ $section['id'] }}"
         class="py-10 sm:py-14 border-b border-stone-200/50"
         style="background-color: var(--sf-bg, #fafaf9);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 sm:mb-8 gap-2">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-heading">
                    {{ $title }}
                </h2>
                @if(!empty($subtitle))
                    <p class="text-xs sm:text-sm text-stone-500 mt-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if($activeCat)
                <a href="{{ route('storefront.show', $vendor->slug) }}" 
                   class="text-xs font-semibold text-stone-500 hover:text-stone-900 flex items-center gap-1 transition">
                    <span>✕ Clear Filter</span>
                </a>
            @endif
        </div>

        <!-- Horizontal Scrollable Story Bubbles Strip -->
        <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto pb-4 pt-1 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            <!-- All Items Bubble -->
            <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
               class="flex flex-col items-center group shrink-0 text-center w-20 sm:w-24">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 border-2 transition-all duration-300 {{ empty($activeCat) ? 'border-stone-900 scale-105 shadow-md' : 'border-stone-200 group-hover:border-stone-400' }}">
                    <div class="w-full h-full rounded-full flex items-center justify-center bg-stone-900 text-white font-bold text-xs uppercase tracking-wider">
                        All
                    </div>
                </div>
                <span class="text-xs font-semibold text-stone-800 mt-2 truncate w-full group-hover:text-stone-950">
                    All Items
                </span>
                <span class="text-[10px] text-stone-400">
                    {{ $products->total() ?? 0 }}
                </span>
            </a>

            @foreach($vendorCategories as $cat)
                @php
                    $isCur = ($activeCat === $cat->slug);
                    $sampleImg = $cat->sample_image ? (str_starts_with($cat->sample_image, 'http') ? $cat->sample_image : asset('storage/' . $cat->sample_image)) : null;
                @endphp
                <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}#catalog" 
                   class="flex flex-col items-center group shrink-0 text-center w-20 sm:w-24">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 border-2 transition-all duration-300 {{ $isCur ? 'scale-105 shadow-md' : 'border-stone-200 group-hover:border-stone-400' }}"
                         style="{{ $isCur ? 'border-color: var(--sf-accent, #c59b27);' : '' }}">
                        <div class="w-full h-full rounded-full overflow-hidden bg-stone-100 flex items-center justify-center relative">
                            @if($sampleImg)
                                <img src="{{ $sampleImg }}" 
                                     alt="{{ $cat->name }}" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                     loading="lazy">
                            @else
                                <span class="text-xl">🏷️</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-stone-800 mt-2 truncate w-full group-hover:text-stone-950 {{ $isCur ? 'font-bold' : '' }}">
                        {{ $cat->name }}
                    </span>
                    <span class="text-[10px] text-stone-400">
                        {{ $cat->products_count }} items
                    </span>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

