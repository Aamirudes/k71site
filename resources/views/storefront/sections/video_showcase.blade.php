@php
    $secId = $section['id'] ?? 'shorts';
    $title = $section['title'] ?? 'Studio Shorts & Video Reels';
    $subtitle = $section['subtitle'] ?? 'Watch real product unboxings, live craftsmanship, and styling sessions';
    $shortsToDisplay = (isset($vendorShorts) && $vendorShorts->isNotEmpty()) 
        ? $vendorShorts 
        : \App\Models\Short::where('status', 'published')->latest()->take(8)->get();
@endphp

@if(isset($shortsToDisplay) && $shortsToDisplay->isNotEmpty())
<section id="{{ $secId }}" 
         data-section-id="{{ $secId }}"
         class="py-8 sm:py-12 border-y border-black/5 dark:border-white/10 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-end justify-between mb-5 sm:mb-7">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span class="text-[10px] font-mono font-bold tracking-widest uppercase text-rose-500">Live Video Feed</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight font-heading">
                    {{ $title }}
                </h2>
                @if(!empty($subtitle))
                    <p class="text-xs sm:text-sm opacity-80 mt-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            <span class="text-xs font-bold px-3 py-1 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center gap-1.5 shadow-2xs">
                <span>▶ Swipe to Watch</span>
            </span>
        </div>

        <!-- Horizontal Scrollable Shorts Rail: 1.25+ Peek on Mobile -->
        <div class="flex items-center gap-3.5 sm:gap-5 overflow-x-auto pb-3 pt-1 scroll-smooth snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($shortsToDisplay as $vShort)
                <a href="{{ route('shorts.show', $vShort->id) }}" 
                   class="snap-start shrink-0 w-36 sm:w-44 aspect-[9/16] rounded-2xl relative overflow-hidden bg-black group border border-white/15 hover:border-white/40 shadow-xl hover:scale-103 transition-all duration-300">
                    @if($vShort->thumbnail_path)
                        <img src="{{ \App\Services\ImageService::url($vShort->thumbnail_path, 400) }}" 
                             alt="{{ $vShort->title }}" 
                             loading="lazy"
                             class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition duration-500 group-hover:scale-105">
                    @elseif($vShort->video_path)
                        <video src="{{ asset('storage/' . $vShort->video_path) }}" class="w-full h-full object-cover opacity-90" muted preload="metadata"></video>
                    @else
                        <div class="w-full h-full bg-zinc-900 flex flex-col items-center justify-center text-white/50 gap-2">
                            <span class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white text-lg">▶</span>
                            <span class="text-[10px] font-mono">Video Reel</span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/30 to-transparent flex flex-col justify-end p-3.5 pointer-events-none">
                        <span class="text-white text-xs font-bold line-clamp-2 leading-tight drop-shadow-md">
                            {{ $vShort->title }}
                        </span>
                        <div class="flex items-center gap-1.5 text-[11px] text-white/90 mt-1 font-mono font-semibold">
                            <span class="text-rose-400">▶</span>
                            <span>{{ number_format($vShort->views_count ?? 0) }} views</span>
                        </div>
                    </div>

                    <!-- Glowing Play Button Overlay on Hover -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        <div class="w-12 h-12 rounded-full bg-white/25 backdrop-blur-md border border-white/50 flex items-center justify-center text-white text-xl shadow-lg">
                            ▶
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif
