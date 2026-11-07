@php
    $secId = $section['id'] ?? 'shorts';
    $title = $section['title'] ?? 'Studio Shorts & Video Reels';
    $subtitle = $section['subtitle'] ?? 'Watch real product unboxings, live craftsmanship, and styling sessions';
    $shortsToDisplay = (isset($vendorShorts) && $vendorShorts->isNotEmpty()) 
        ? $vendorShorts 
        : \App\Models\Short::where('status', 'published')->latest()->take(10)->get();
@endphp

@if(isset($shortsToDisplay) && $shortsToDisplay->isNotEmpty())
<section id="{{ $secId }}" 
         data-section-id="{{ $secId }}"
         class="py-8 sm:py-12 border-y border-black/5 dark:border-white/10 relative z-10"
         x-data="storefrontReelsManager(@js($shortsToDisplay->map(function($s) use ($vendor) {
             $prod = $s->products->first();
             return [
                 'id' => $s->id,
                 'title' => $s->title,
                 'description' => $s->description,
                 'video_url' => $s->video_url,
                 'thumb_url' => $s->thumb_url ?: ($s->thumbnail_path ? \App\Services\ImageService::url($s->thumbnail_path, 600) : ''),
                 'vendor_name' => $s->vendor?->shop_name ?? ($vendor->shop_name ?? 'Verified Store'),
                 'vendor_avatar' => $s->vendor?->logo ? asset('storage/' . $s->vendor->logo) : ($vendor->logo_url ?? asset('images/default-avatar.png')),
                 'product' => $prod ? [
                     'id' => $prod->id,
                     'title' => $prod->title,
                     'price' => number_format($prod->base_price / 100, 2),
                     'image' => $prod->primary_image_url,
                     'variant_id' => $prod->variants->first()?->id ?? null,
                 ] : null,
             ];
         })))">
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
                <span>▶ Tap to Watch In-Store</span>
            </span>
        </div>

        <!-- Horizontal Scrollable Shorts Rail: 1.25+ Peek on Mobile (Does NOT redirect) -->
        <div class="flex items-center gap-3.5 sm:gap-5 overflow-x-auto pb-3 pt-1 scroll-smooth snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach($shortsToDisplay as $index => $vShort)
                <button type="button"
                        @click="openModal({{ $index }})"
                        class="snap-start shrink-0 w-36 sm:w-44 aspect-[9/16] rounded-2xl relative overflow-hidden bg-black group border border-white/15 hover:border-white/40 shadow-xl hover:scale-103 transition-all duration-300 cursor-pointer text-left focus:outline-none">
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

                    <!-- Bottom Overlay (Title & Play badge, NO view count) -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/30 to-transparent flex flex-col justify-end p-3.5 pointer-events-none">
                        <span class="text-white text-xs font-bold line-clamp-2 leading-tight drop-shadow-md">
                            {{ $vShort->title }}
                        </span>
                        <div class="flex items-center gap-1.5 text-[10px] text-white/90 mt-1 font-mono font-semibold">
                            <span class="text-rose-400">▶</span>
                            <span>Watch Reel</span>
                        </div>
                    </div>

                    <!-- Glowing Play Button Overlay on Hover -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        <div class="w-12 h-12 rounded-full bg-white/25 backdrop-blur-md border border-white/50 flex items-center justify-center text-white text-xl shadow-lg">
                            ▶
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- IN-STORE REELS VIEWER MODAL (REAL INSTAGRAM/YOUTUBE SLIDING) -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <template x-teleport="body">
        <div x-show="isOpen" 
             x-cloak
             class="fixed inset-0 z-[9999] bg-black/95 backdrop-blur-2xl flex items-center justify-center overflow-hidden"
             @keydown.window.escape="closeModal()"
             @keydown.window.arrow-down.prevent="nextSlide()"
             @keydown.window.arrow-up.prevent="prevSlide()">

            <!-- Centered 9:16 Phone-Style Container -->
            <div class="relative w-full h-full sm:h-[94vh] sm:max-h-[880px] sm:max-w-[430px] sm:rounded-3xl bg-black overflow-hidden shadow-2xl border sm:border-white/15 flex flex-col">

                <!-- Top Floating Controls Header -->
                <div class="absolute top-0 inset-x-0 z-30 p-4 bg-gradient-to-b from-black/80 via-black/40 to-transparent flex items-center justify-between text-white pointer-events-auto">
                    <!-- Close Button (Returns to Storefront without redirect) -->
                    <button type="button" 
                            @click="closeModal()"
                            class="w-9 h-9 rounded-full bg-black/50 backdrop-blur-md border border-white/20 flex items-center justify-center text-white hover:bg-white hover:text-black transition cursor-pointer"
                            aria-label="Close reels viewer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Vendor Reel Badge -->
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md border border-white/20 text-[11px] font-mono font-bold text-white">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        <span x-text="activeReel ? activeReel.vendor_name : 'Reel'"></span>
                    </div>

                    <!-- Mute / Unmute Toggle -->
                    <button type="button" 
                            @click="toggleMute()"
                            class="w-9 h-9 rounded-full bg-black/50 backdrop-blur-md border border-white/20 flex items-center justify-center text-white hover:bg-black/80 transition cursor-pointer">
                        <span x-text="isMuted ? '🔇' : '🔊'" class="text-sm"></span>
                    </button>
                </div>

                <!-- Smooth Vertical Snap Track (Real Instagram/YouTube Sliding Animation) -->
                <div x-ref="sliderTrack"
                     class="w-full h-full overflow-y-scroll snap-y snap-mandatory scroll-smooth no-scrollbar select-none"
                     style="scroll-snap-type: y mandatory; -webkit-overflow-scrolling: touch;"
                     @scroll.passive="handleScroll()">
                    
                    <template x-for="(reel, idx) in reels" :key="reel.id">
                        <div class="w-full h-full snap-start shrink-0 relative bg-black flex items-center justify-center overflow-hidden"
                             :id="'store-reel-slide-' + idx">
                            
                            <!-- Video Player -->
                            <video :id="'store-reel-vid-' + idx"
                                   :src="reel.video_url"
                                   :poster="reel.thumb_url"
                                   class="w-full h-full object-cover cursor-pointer"
                                   playsinline
                                   webkit-playsinline
                                   loop
                                   :muted="isMuted"
                                   @click="togglePlay(idx)">
                            </video>

                            <!-- Pause / Play Touch Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none transition-opacity duration-200"
                                 :class="isPlaying(idx) ? 'opacity-0' : 'opacity-100 bg-black/20'">
                                <div class="w-16 h-16 rounded-full bg-black/60 backdrop-blur-md border border-white/30 text-white flex items-center justify-center text-2xl shadow-xl">
                                    ▶
                                </div>
                            </div>

                            <!-- Bottom Reel Overlay: Title & Tagged Product -->
                            <div class="absolute bottom-0 inset-x-0 p-4 pb-6 bg-gradient-to-t from-black via-black/60 to-transparent flex flex-col gap-3 pointer-events-none text-white">
                                
                                <!-- Vendor & Description -->
                                <div class="space-y-1 pointer-events-auto">
                                    <div class="flex items-center gap-2">
                                        <img :src="reel.vendor_avatar" class="w-7 h-7 rounded-full object-cover border border-white/40">
                                        <span class="text-xs font-bold text-white drop-shadow-sm" x-text="reel.vendor_name"></span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    </div>
                                    <h3 class="text-xs sm:text-sm font-bold text-white line-clamp-2 drop-shadow-md" x-text="reel.title"></h3>
                                </div>

                                <!-- Tagged Product Direct Buy Pill (1-Tap Add to Bag) -->
                                <template x-if="reel.product">
                                    <div class="p-2 sm:p-2.5 rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl flex items-center justify-between gap-3 pointer-events-auto">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <img :src="reel.product.image" class="w-10 h-10 rounded-xl object-cover border border-white/20 shrink-0 bg-stone-900">
                                            <div class="min-w-0">
                                                <span class="text-xs font-bold text-white truncate block" x-text="reel.product.title"></span>
                                                <span class="text-xs font-mono font-black text-emerald-400">₹<span x-text="reel.product.price"></span></span>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="window.quickAddToCart(reel.product.id, reel.product.variant_id, $el)"
                                                class="px-3.5 py-2 rounded-xl bg-white text-stone-950 hover:bg-stone-100 font-bold text-xs uppercase tracking-wider shrink-0 transition shadow-md active:scale-95 cursor-pointer">
                                            + Add
                                        </button>
                                    </div>
                                </template>

                            </div>

                        </div>
                    </template>

                </div>

                <!-- Desktop Next/Prev Arrow Controls -->
                <div class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 flex-col gap-2 z-40">
                    <button type="button" 
                            @click="prevSlide()"
                            class="w-10 h-10 rounded-full bg-black/60 hover:bg-white hover:text-black border border-white/20 backdrop-blur-md text-white flex items-center justify-center transition shadow-lg cursor-pointer"
                            aria-label="Previous reel">
                        ↑
                    </button>
                    <button type="button" 
                            @click="nextSlide()"
                            class="w-10 h-10 rounded-full bg-black/60 hover:bg-white hover:text-black border border-white/20 backdrop-blur-md text-white flex items-center justify-center transition shadow-lg cursor-pointer"
                            aria-label="Next reel">
                        ↓
                    </button>
                </div>

            </div>
        </div>
    </template>
</section>

<script>
    function storefrontReelsManager(reelsList) {
        return {
            reels: reelsList || [],
            isOpen: false,
            currentIndex: 0,
            isMuted: false,
            playingStates: {},

            get activeReel() {
                return this.reels[this.currentIndex] || null;
            },

            openModal(idx) {
                this.currentIndex = idx;
                this.isOpen = true;
                document.body.style.overflow = 'hidden';

                this.$nextTick(() => {
                    const track = this.$refs.sliderTrack;
                    const targetSlide = document.getElementById('store-reel-slide-' + idx);
                    if (track && targetSlide) {
                        track.scrollTop = targetSlide.offsetTop;
                    }
                    this.playCurrentReel();
                });
            },

            closeModal() {
                this.isOpen = false;
                document.body.style.overflow = '';
                this.pauseAllReels();
            },

            toggleMute() {
                this.isMuted = !this.isMuted;
                this.reels.forEach((_, i) => {
                    const vid = document.getElementById('store-reel-vid-' + i);
                    if (vid) vid.muted = this.isMuted;
                });
            },

            togglePlay(idx) {
                const vid = document.getElementById('store-reel-vid-' + idx);
                if (!vid) return;
                if (vid.paused) {
                    vid.play();
                    this.playingStates[idx] = true;
                } else {
                    vid.pause();
                    this.playingStates[idx] = false;
                }
            },

            isPlaying(idx) {
                return this.playingStates[idx] !== false;
            },

            playCurrentReel() {
                this.reels.forEach((_, i) => {
                    const vid = document.getElementById('store-reel-vid-' + i);
                    if (vid) {
                        if (i === this.currentIndex) {
                            vid.muted = this.isMuted;
                            vid.play().then(() => {
                                this.playingStates[i] = true;
                            }).catch(() => {
                                vid.muted = true;
                                this.isMuted = true;
                                vid.play();
                                this.playingStates[i] = true;
                            });
                        } else {
                            vid.pause();
                            vid.currentTime = 0;
                            this.playingStates[i] = false;
                        }
                    }
                });
            },

            pauseAllReels() {
                this.reels.forEach((_, i) => {
                    const vid = document.getElementById('store-reel-vid-' + i);
                    if (vid) {
                        vid.pause();
                        this.playingStates[i] = false;
                    }
                });
            },

            nextSlide() {
                if (this.currentIndex < this.reels.length - 1) {
                    this.currentIndex++;
                } else {
                    this.currentIndex = 0;
                }
                this.scrollToCurrentIndex();
            },

            prevSlide() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                } else {
                    this.currentIndex = this.reels.length - 1;
                }
                this.scrollToCurrentIndex();
            },

            scrollToCurrentIndex() {
                const track = this.$refs.sliderTrack;
                const targetSlide = document.getElementById('store-reel-slide-' + this.currentIndex);
                if (track && targetSlide) {
                    track.scrollTo({ top: targetSlide.offsetTop, behavior: 'smooth' });
                }
                this.playCurrentReel();
            },

            handleScroll() {
                const track = this.$refs.sliderTrack;
                if (!track) return;
                const scrollPos = track.scrollTop;
                const slideHeight = track.offsetHeight || window.innerHeight;
                const newIndex = Math.round(scrollPos / slideHeight);

                if (newIndex !== this.currentIndex && newIndex >= 0 && newIndex < this.reels.length) {
                    this.currentIndex = newIndex;
                    this.playCurrentReel();
                }
            }
        };
    }
</script>
@endif
