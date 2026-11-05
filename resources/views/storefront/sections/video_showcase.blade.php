@php
    $title = $section['title'] ?? 'Studio Shorts & Reels';
    $subtitle = $section['subtitle'] ?? 'Watch real product unboxings and craftsmanship sessions';
@endphp

@if(isset($vendorShorts) && $vendorShorts->isNotEmpty())
<section id="{{ $section['id'] }}" 
         data-section-id="{{ $section['id'] }}"
         class="py-12 sm:py-16 border-b border-stone-200/60"
         style="background-color: var(--sf-surface, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-end justify-between mb-6 sm:mb-8">
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

            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-50 text-rose-600 border border-rose-200 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Live Video</span>
            </span>
        </div>

        <!-- Horizontal Scrollable Shorts Rail -->
        <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto pb-4 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($vendorShorts as $vShort)
                <a href="{{ route('shorts.show', $vShort->id) }}" 
                   class="flex-none w-32 sm:w-40 aspect-[9/16] rounded-2xl relative overflow-hidden bg-stone-950 group border border-stone-800 shadow-md hover:scale-103 transition duration-300">
                    @if($vShort->thumbnail_path)
                        <img src="{{ \App\Services\ImageService::url($vShort->thumbnail_path, 400) }}" 
                             alt="{{ $vShort->title }}" 
                             class="w-full h-full object-cover opacity-85 group-hover:opacity-100 transition">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/90 via-transparent to-transparent flex flex-col justify-end p-3">
                        <span class="text-white text-xs font-bold line-clamp-2 leading-tight">
                            {{ $vShort->title }}
                        </span>
                        <div class="flex items-center gap-1 text-[10px] text-stone-300 mt-1">
                            <span>▶</span>
                            <span>{{ number_format($vShort->views_count ?? 0) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

