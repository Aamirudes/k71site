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

        <!-- Horizontal Slider: Exactly 2.5 Slides Per View on Mobile (w-[38vw]), Multi-Card on Desktop -->
        <div x-ref="hcTrack"
             class="flex items-stretch gap-2.5 sm:gap-4 overflow-x-auto pb-4 pt-1 scroll-smooth snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($items as $idx => $item)
                @php
                    $isVid = ($item['type'] ?? 'image') === 'video';
                    $mediaUrl = $item['media_url'] ?? '';
                    $thumbUrl = $item['thumb_url'] ?? $mediaUrl;
                    $rating = (int)($item['rating'] ?? 5);
                @endphp
                <div class="snap-start shrink-0 w-[38vw] sm:w-[220px] md:w-[240px] rounded-2xl overflow-hidden border border-black/10 dark:border-white/15 bg-white dark:bg-zinc-900 shadow-xs flex flex-col justify-between group hover:shadow-lg transition-all duration-300">
                    
                    <!-- Media Showcase (Photo or Video Reel) -->
                    <div class="relative aspect-square bg-black overflow-hidden">
                        @if($isVid)
                            <video src="{{ $mediaUrl }}" 
                                   poster="{{ $thumbUrl }}"
                                   playsinline 
                                   webkit-playsinline 
                                   loop 
                                   muted
                                   preload="metadata"
                                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer"
                                   onclick="this.paused ? this.play() : this.pause()">
                            </video>
                            <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-full bg-black/75 backdrop-blur-md text-white text-[8px] font-mono font-bold flex items-center gap-1 border border-white/20 pointer-events-none">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> REEL
                            </div>
                        @else
                            <img src="{{ $thumbUrl }}" 
                                 alt="{{ $item['customer_name'] ?? 'Happy Customer' }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-full bg-black/75 backdrop-blur-md text-white text-[8px] font-mono font-bold flex items-center gap-1 border border-white/20">
                                <span>📸 VERIFIED</span>
                            </div>
                        @endif

                        <!-- Top-Left Verified Purchase Badge -->
                        <div class="absolute top-1.5 left-1.5">
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-500 text-white font-mono font-black text-[8px] uppercase tracking-wider shadow-sm flex items-center gap-0.5">
                                <span>✓</span> BUYER
                            </span>
                        </div>
                    </div>

                    <!-- Customer Story Content (Cute & Compact) -->
                    <div class="p-2.5 sm:p-3 flex-1 flex flex-col justify-between space-y-2">
                        <div class="space-y-1">
                            <!-- Star Rating -->
                            <div class="flex items-center gap-0.5 text-amber-400 text-[10px]">
                                @for($s = 1; $s <= 5; $s++)
                                    <span>{{ $s <= $rating ? '★' : '☆' }}</span>
                                @endfor
                                <span class="text-[9px] font-bold text-stone-500 dark:text-zinc-400 ml-0.5">5.0</span>
                            </div>

                            <!-- Testimonial Quote -->
                            <p class="text-[11px] sm:text-xs font-medium leading-snug line-clamp-2 text-stone-700 dark:text-zinc-200">
                                "{{ $item['comment'] ?? 'Wonderful experience and genuine product!' }}"
                            </p>
                        </div>

                        <!-- Customer Info Footer -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[10px]">
                            <div class="min-w-0">
                                <h4 class="font-bold text-stone-900 dark:text-white truncate text-[11px]">
                                    {{ $item['customer_name'] ?? 'Verified Customer' }}
                                </h4>
                                <span class="text-[9px] text-stone-400 dark:text-zinc-500 block truncate">
                                    {{ $item['location'] ?? 'India' }}
                                </span>
                            </div>
                            <span class="w-4 h-4 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[9px] flex items-center justify-center shrink-0">
                                ✓
                            </span>
                        </div>

                    </div>

                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

