@php
    $secId = 'happy_customers';
    $hcConfig = $config['happy_customers'] ?? [];
    $isEnabled = isset($hcConfig['enabled']) ? (bool)$hcConfig['enabled'] : true;
    
    $title = $hcConfig['title'] ?? 'Happy Customers & Real Stories';
    $subtitle = $hcConfig['subtitle'] ?? 'Real customer unboxings, doorstep deliveries, and verified reviews';
    
    $storeName = $config['brand']['store_name'] ?? $vendor->shop_name;
    $defaultItems = [
        [
            'type' => 'image',
            'media_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80',
            'customer_name' => 'Ananya Sharma',
            'location' => 'Mumbai, MH',
            'rating' => 5,
            'comment' => "Received my order in pristine packaging! The craftsmanship exceeds expectations. Will definitely order again from {$storeName}!",
            'product_name' => 'Verified Studio Purchase',
            'verified' => true,
        ],
        [
            'type' => 'video',
            'media_url' => 'https://assets.mixkit.co/videos/preview/mixkit-girl-in-a-traditional-dress-smiling-41229-large.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=80',
            'customer_name' => 'Meera & Rajesh Patel',
            'location' => 'Ahmedabad, GJ',
            'rating' => 5,
            'comment' => "Live unboxing was unbelievable! Authentic quality, insured transit arrived within 48 hours. Five stars all the way!",
            'product_name' => 'Express Courier Dispatch',
            'verified' => true,
        ],
        [
            'type' => 'image',
            'media_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800&q=80',
            'customer_name' => 'Vikram Malhotra',
            'location' => 'New Delhi, DL',
            'rating' => 5,
            'comment' => "Direct workshop provenance makes all the difference. Escrow protection gives absolute peace of mind.",
            'product_name' => 'Authentic Batch Order',
            'verified' => true,
        ],
        [
            'type' => 'video',
            'media_url' => 'https://assets.mixkit.co/videos/preview/mixkit-woman-showing-a-new-dress-in-a-mirror-41474-large.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80',
            'customer_name' => 'Sneha Kulkarni',
            'location' => 'Pune, MH',
            'rating' => 5,
            'comment' => "Customer support on WhatsApp helped with custom sizing right away. Perfect fit and luxury feel!",
            'product_name' => 'Verified Customer Delivery',
            'verified' => true,
        ],
        [
            'type' => 'image',
            'media_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=800&q=80',
            'customer_name' => 'Arjun Reddy',
            'location' => 'Hyderabad, TS',
            'rating' => 5,
            'comment' => "Top notch experience. Tracking updates at every milestone. 100% genuine products directly from maker.",
            'product_name' => 'Curated Workshop Piece',
            'verified' => true,
        ],
    ];

    $items = (!empty($hcConfig['items']) && is_array($hcConfig['items'])) ? $hcConfig['items'] : $defaultItems;
@endphp

@if($isEnabled && !empty($items))
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

        <!-- Horizontal Slider: Exactly 2.5 Compact Shorts-Style Cards Per View on Mobile (w-[36vw]), Multi-Card on Desktop -->
        <div x-ref="hcTrack"
             class="flex items-center gap-2.5 sm:gap-4 overflow-x-auto pb-4 pt-1 scroll-smooth snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($items as $idx => $item)
                @php
                    $isVid = ($item['type'] ?? 'image') === 'video';
                    $mediaUrl = $item['media_url'] ?? '';
                    $thumbUrl = $item['thumb_url'] ?? $mediaUrl;
                    $rating = (int)($item['rating'] ?? 5);
                @endphp
                {{-- Compact 9:16 Story Card (Identical form factor to Shorts Cards, 2.5/view on mobile) --}}
                <div class="snap-start shrink-0 w-[36vw] sm:w-40 md:w-44 aspect-[9/16] rounded-2xl overflow-hidden relative bg-black group border border-black/10 dark:border-white/20 shadow-md transition-all duration-300 hover:scale-[1.02]">
                    
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
                             loading="lazy"
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
                            "{{ $item['comment'] ?? 'Wonderful experience and genuine product!' }}"
                        </p>

                        <!-- Customer Name & City -->
                        <div class="flex items-center justify-between text-[9px] text-white/80 border-t border-white/15 pt-1">
                            <span class="font-bold truncate max-w-[80%] text-white">
                                {{ $item['customer_name'] ?? 'Verified Customer' }}
                            </span>
                            <span class="text-[8px] text-zinc-300 font-mono">
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

