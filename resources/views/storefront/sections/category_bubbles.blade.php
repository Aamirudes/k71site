@php
    $title = $section['title'] ?? 'Explore Collections';
    $activeCat = request('category');
@endphp

@if($vendorCategories->isNotEmpty())
<section id="categories" class="py-6 sm:py-10 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-stone-900 font-heading flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: var(--sf-accent, #c59b27);"></span>
                <span>{{ $title }}</span>
            </h2>

            @if($activeCat)
                <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                   class="text-xs font-bold px-3 py-1 rounded-full bg-stone-900 text-white hover:bg-stone-800 transition flex items-center gap-1">
                    <span>✕ Clear</span>
                </a>
            @endif
        </div>

        <!-- Horizontal Scrollable Liquid Bubble Strip -->
        <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto pb-4 pt-1 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            <!-- All Items Liquid Bubble -->
            <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
               class="flex flex-col items-center group shrink-0 text-center w-20 sm:w-24">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-1 transition-all duration-300 {{ empty($activeCat) ? 'scale-105 shadow-xl ring-2' : 'hover:scale-105' }}"
                     style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 2px solid {{ empty($activeCat) ? 'var(--sf-accent, #c59b27)' : 'rgba(255,255,255,0.7)' }};">
                    <div class="w-full h-full rounded-full flex flex-col items-center justify-center font-black text-xs uppercase tracking-wider text-white shadow-sm"
                         style="background: linear-gradient(135deg, var(--sf-primary, #18181b), #27272a);">
                        <span>All</span>
                        <span class="text-[9px] opacity-75">{{ $products->total() ?? 0 }}</span>
                    </div>
                </div>
                <span class="text-xs font-bold text-stone-900 mt-2 truncate w-full">
                    All Drops
                </span>
            </a>

            @foreach($vendorCategories as $cat)
                @php
                    $isCur = ($activeCat === $cat->slug);
                    $sampleImg = $cat->sample_image ? (str_starts_with($cat->sample_image, 'http') ? $cat->sample_image : asset('storage/' . $cat->sample_image)) : null;
                @endphp
                <a href="{{ $vendor->storefrontUrl(['category' => $cat->slug], 'catalog') }}" 
                   class="flex flex-col items-center group shrink-0 text-center w-20 sm:w-24">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-1 transition-all duration-300 {{ $isCur ? 'scale-105 shadow-xl ring-2' : 'hover:scale-105' }}"
                         style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 2px solid {{ $isCur ? 'var(--sf-accent, #c59b27)' : 'rgba(255,255,255,0.7)' }};">
                        <div class="w-full h-full rounded-full overflow-hidden bg-stone-100 flex items-center justify-center relative shadow-inner">
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
                    <span class="text-xs font-semibold text-stone-800 mt-2 truncate w-full group-hover:text-stone-950 {{ $isCur ? 'font-black' : '' }}">
                        {{ $cat->name }}
                    </span>
                    <span class="text-[10px] text-stone-400 font-medium">
                        {{ $cat->products_count }} items
                    </span>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif
