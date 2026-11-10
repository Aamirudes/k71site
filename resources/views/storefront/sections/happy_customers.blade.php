@php
    $secId = 'happy_customers';
    $hcConfig = $config['happy_customers'] ?? [];
    $isEnabled = isset($hcConfig['enabled']) ? filter_var($hcConfig['enabled'], FILTER_VALIDATE_BOOLEAN) : true;
    
    $title = $hcConfig['title'] ?? 'Happy Customers & Real Stories';
    $subtitle = $hcConfig['subtitle'] ?? 'Real customer unboxings, doorstep deliveries, and verified reviews';

    // Show ONLY merchant-added real customer stories (zero seeded/fake spotlight entries)
    $rawItems = $hcConfig['items'] ?? null;
    $items = is_array($rawItems)
        ? array_values(array_filter($rawItems, fn ($item) => is_array($item) && !empty($item['media_url'])))
        : [];
@endphp

@if($isEnabled && count($items) > 0)
<section id="{{ $secId }}" 
         class="py-10 sm:py-16 border-b border-black/5 dark:border-white/10 relative z-10"
         x-data="{
            scrollLeft() {
                $refs.hcTrack.scrollBy({ left: -$refs.hcTrack.offsetWidth * 0.75, behavior: 'smooth' });
            },
            scrollRight() {
                $refs.hcTrack.scrollBy({ left: $refs.hcTrack.offsetWidth * 0.75, behavior: 'smooth' });
            }
         }">
    <style>
        #happy_customers .hc-card {
            width: 38vw;
            min-width: 138px;
            max-width: 185px;
            aspect-ratio: 9 / 16;
            flex-shrink: 0;
        }
        @media (min-width: 640px) {
            #happy_customers .hc-card {
                width: 10.5rem;
                min-width: 10.5rem;
                max-width: none;
            }
        }
        @media (min-width: 768px) {
            #happy_customers .hc-card {
                width: 11.5rem;
                min-width: 11.5rem;
            }
        }
    </style>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Slider Arrow Controls -->
        <div class="flex items-end justify-between mb-6 sm:mb-8">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="text-[10px] font-mono font-bold tracking-widest uppercase text-amber-500">Verified Buyer Spotlight</span>
                </div>
                <h2 class="text-xl sm:text-3xl font-black tracking-tight font-heading">
                    {{ $title }}
                </h2>
                @if(!empty($subtitle))
                    <p class="text-xs sm:text-sm opacity-80 mt-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            <!-- Slider Nav Arrows -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        @click="scrollLeft()"
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-black/15 dark:border-white/20 bg-white dark:bg-zinc-800 text-stone-800 dark:text-white flex items-center justify-center hover:bg-stone-100 dark:hover:bg-zinc-700 transition shadow-xs cursor-pointer text-xs sm:text-sm font-bold active:scale-95"
                        aria-label="Previous story">
                    ←
                </button>
                <button type="button" 
                        @click="scrollRight()"
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-black/15 dark:border-white/20 bg-white dark:bg-zinc-800 text-stone-800 dark:text-white flex items-center justify-center hover:bg-stone-100 dark:hover:bg-zinc-700 transition shadow-xs cursor-pointer text-xs sm:text-sm font-bold active:scale-95"
                        aria-label="Next story">
                    →
                </button>
            </div>
        </div>

        <!-- Horizontal Slider: 2.4+ Cards Per View on Mobile, Multi-Card on Desktop -->
        <div x-ref="hcTrack"
             class="flex items-center gap-2.5 sm:gap-4 overflow-x-auto pb-4 pt-1 scroll-smooth snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($items as $idx => $item)
                @php
                    $isVid = ($item['type'] ?? 'image') === 'video';
                    $mediaUrl = $item['media_url'] ?? '';
                    if (!empty($mediaUrl) && !str_starts_with($mediaUrl, 'http') && !str_starts_with($mediaUrl, '/')) {
                        $mediaUrl = '/storage/' . $mediaUrl;
                    }
                    $thumbUrl = $item['thumb_url'] ?? $mediaUrl;
                    if (!empty($thumbUrl) && !str_starts_with($thumbUrl, 'http') && !str_starts_with($thumbUrl, '/')) {
                        $thumbUrl = '/storage/' . $thumbUrl;
                    }
                    if (empty($thumbUrl)) {
                        $thumbUrl = $mediaUrl;
                    }
                    $rating = (int)($item['rating'] ?? 5);
                @endphp
                {{-- Compact 9:16 Story Card --}}
                <div class="hc-card snap-start shrink-0 w-[38vw] sm:w-40 md:w-44 aspect-[9/16] rounded-2xl overflow-hidden relative bg-black group border border-black/10 dark:border-white/20 shadow-md transition-all duration-300 hover:scale-[1.02]">
                    
                    <!-- Background Media (Photo or Video Loop) -->
                    @if($isVid)
                        <video src="{{ $mediaUrl }}" 
                               poster="{{ $thumbUrl }}"
                               playsinline 
                               webkit-playsinline 
                               loop 
                               muted
                               preload="metadata"
                               class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer"
                               onclick="this.paused ? this.play() : this.pause()">
                        </video>
                        <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-full bg-black/70 backdrop-blur-md text-white text-[8px] font-mono font-bold flex items-center gap-1 border border-white/20 pointer-events-none z-10">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> REEL
                        </div>
                    @else
                        <img src="{{ $thumbUrl }}" 
                             alt="{{ $item['customer_name'] ?? 'Happy Customer' }}" 
                             loading="eager"
                             decoding="async"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-full bg-black/70 backdrop-blur-md text-white text-[8px] font-mono font-bold flex items-center gap-1 border border-white/20 z-10">
                            <span>📸 PHOTO</span>
                        </div>
                    @endif

                    <!-- Top-Left Verified Purchase Badge -->
                    <div class="absolute top-2 left-2 z-10">
                        <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/90 backdrop-blur-xs text-white font-mono font-black text-[7.5px] uppercase tracking-wider shadow-sm flex items-center gap-0.5">
                            <span>✓</span> BUYER
                        </span>
                    </div>

                    <!-- Bottom Scrim Overlay with Compact Review Text -->
                    <div class="absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-black/95 via-black/75 to-transparent pt-10 pb-2.5 px-2.5 sm:px-3 text-white flex flex-col justify-end pointer-events-none">
                        
                        <!-- Star Rating -->
                        <div class="flex items-center gap-0.5 text-amber-400 text-[9px] mb-1">
                            @for($s = 1; $s <= 5; $s++)
                                <span>{{ $s <= $rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>

                        <!-- Micro Customer Quote (2-line limit) -->
                        <p class="text-[9.5px] sm:text-[10.5px] font-medium leading-tight line-clamp-2 text-white/95 drop-shadow-sm mb-1.5">
                            "{{ $item['comment'] ?? 'Verified customer experience!' }}"
                        </p>

                        <!-- Customer Name & City -->
                        <div class="flex items-center justify-between gap-1 text-[9px] text-white/80 border-t border-white/15 pt-1">
                            <span class="font-bold min-w-0 flex-1 truncate text-white">
                                {{ $item['customer_name'] ?? 'Verified Customer' }}
                            </span>
                            <span class="shrink-0 text-[8px] text-zinc-300 font-mono">
                                {{ $item['location'] ?? 'IN' }}
                            </span>
                        </div>

                    </div>

                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

