@php
    $secId = $section['id'] ?? 'shorts';
    $title = $section['title'] ?? 'Studio Shorts & Video Reels';
    $subtitle = $section['subtitle'] ?? 'Watch real product unboxings, live craftsmanship, and styling sessions';
@endphp

@if(isset($vendorShorts) && $vendorShorts->isNotEmpty())
<section id="{{ $secId }}" 
         data-section-id="{{ $secId }}"
         class="py-10 sm:py-14 border-y border-white/10 backdrop-blur-xl bg-white/5 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-end justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight font-heading">
                    {{ $title }}
                </h2>
                @if(!empty($subtitle))
                    <p class="text-xs sm:text-sm opacity-70 mt-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Live Studio</span>
            </span>
        </div>

        <!-- Horizontal Scrollable Shorts Rail -->
        <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto pb-4 scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($vendorShorts as $vShort)
                <a href="{{ route('shorts.show', $vShort->id) }}" 
                   class="flex-none w-36 sm:w-44 aspect-[9/16] rounded-2xl relative overflow-hidden bg-black group border border-white/10 hover:border-white/40 shadow-xl hover:scale-103 transition-all duration-300">
                    @if($vShort->thumbnail_path)
                        <img src="{{ \App\Services\ImageService::url($vShort->thumbnail_path, 400) }}" 
                             alt="{{ $vShort->title }}" 
                             class="w-full h-full object-cover opacity-85 group-hover:opacity-100 transition duration-500">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/20 to-transparent flex flex-col justify-end p-3.5">
                        <span class="text-white text-xs font-bold line-clamp-2 leading-tight drop-shadow-sm">
                            {{ $vShort->title }}
                        </span>
                        <div class="flex items-center gap-1.5 text-[11px] text-white/80 mt-1 font-mono">
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

