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
                 'likes' => $s->likes_count ?: ($s->likes ?? 14),
                 'shares' => $s->shares_count ?: 6,
                 'duration' => $s->duration ? gmdate('i:s', $s->duration) : '0:30',
                 'product' => $prod ? [
                     'id' => $prod->id,
                     'title' => $prod->title,
                     'price' => number_format($prod->base_price / 100, 2),
                     'image' => $prod->primary_image_url,
                     'variant_id' => $prod->variants->first()?->id ?? null,
                 ] : null,
                 'products_detail' => $s->products->map(fn($p) => [
                     'id' => $p->id,
                     'title' => $p->title,
                     'price' => '₹' . number_format($p->base_price / 100, 2),
                     'image' => $p->primary_image_url,
                     'variant_id' => $p->variants->first()?->id ?? null,
                 ])->values()->all(),
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
    <!-- EXACT MATCH IMMERSIVE 9:16 VERTICAL REELS VIEWER MODAL (MATCHES SHORTS PAGE) -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <template x-teleport="body">
        <div x-show="isOpen" 
             x-cloak
             class="fixed inset-0 z-[99999] bg-black/95 backdrop-blur-md flex items-center justify-center overflow-hidden"
             @keydown.window.escape="closeModal()"
             @keydown.window.arrow-down.prevent="nextVideo()"
             @keydown.window.arrow-up.prevent="prevVideo()"
             @keydown.window.space.prevent="togglePlay()">

            <!-- Centered 9:16 Phone Mockup Container (Exact Shorts Page Style) -->
            <div class="relative w-full h-full sm:h-[94vh] sm:max-h-[880px] sm:max-w-[440px] sm:rounded-2xl bg-black overflow-hidden flex flex-col shadow-2xl border sm:border-zinc-800"
                 @touchstart="touchStart($event)"
                 @touchend="touchEnd($event)">

                <!-- Top Overlay Controls -->
                <div class="absolute top-0 inset-x-0 z-30 p-4 bg-gradient-to-b from-black/80 via-black/40 to-transparent flex items-center justify-between text-white pointer-events-auto">
                    <!-- Back / Close Button -->
                    <button type="button"
                            @click="closeModal()"
                            class="w-9 h-9 rounded-full bg-black/40 backdrop-blur flex items-center justify-center hover:bg-black/70 transition cursor-pointer"
                            aria-label="Close reels viewer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <!-- K71 Shorts Video Shopping Badge -->
                    <div class="flex items-center gap-1.5 bg-black/80 text-white text-[11px] font-black px-2.5 py-1 rounded-full border border-white/20 shadow">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span x-text="activeReel?.vendor_name ? `${activeReel.vendor_name} · REELS` : 'K71 SHORTS'"></span>
                    </div>

                    <!-- Mute / Unmute Toggle -->
                    <button type="button"
                            @click="toggleMute()"
                            class="w-9 h-9 rounded-full bg-black/40 backdrop-blur flex items-center justify-center hover:bg-black/70 transition cursor-pointer">
                        <span x-show="!isMuted" class="text-base">🔊</span>
                        <span x-show="isMuted" class="text-base">🔇</span>
                    </button>
                </div>

                <!-- Main Native HTML5 Video Element with Real Instagram/YouTube Slide Animation -->
                <div class="relative flex-1 bg-black flex items-center justify-center overflow-hidden cursor-pointer"
                     @click="togglePlay()">
                    <div :class="{ 'animate-reel-slide-up': slideDirection === 'up' && isSliding, 'animate-reel-slide-down': slideDirection === 'down' && isSliding }"
                         class="w-full h-full flex items-center justify-center">
                        <video x-ref="storeVideoEl"
                               class="w-full h-full object-cover"
                               playsinline
                               webkit-playsinline
                               loop
                               :muted="isMuted"
                               @timeupdate="updateProgress()"
                               @play="isPlaying = true"
                               @pause="isPlaying = false"
                               @ended="nextVideo()">
                        </video>
                    </div>

                    <!-- Animated Play / Pause Indicator -->
                    <div x-show="!isPlaying"
                         class="absolute inset-0 flex items-center justify-center pointer-events-none bg-black/20">
                        <div class="w-16 h-16 rounded-full bg-black/60 backdrop-blur text-white flex items-center justify-center shadow-2xl">
                            <svg class="w-8 h-8 fill-current ml-1" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Right-Side Interactive Floating Action Rail (Exact Match) -->
                <div class="absolute right-3 bottom-24 z-30 flex flex-col items-center gap-4 text-white">
                    <!-- Creator Avatar & Follow -->
                    <div class="relative flex flex-col items-center">
                        <img :src="activeReel?.vendor_avatar || '{{ asset('images/default-avatar.png') }}'"
                             class="w-11 h-11 rounded-full object-cover border-2 border-white shadow-lg">
                        <button type="button"
                                @click.stop="toggleFollow()"
                                class="absolute -bottom-1.5 w-5 h-5 rounded-full bg-white text-black flex items-center justify-center text-xs font-bold shadow cursor-pointer">
                            <span x-text="isFollowed ? '✓' : '+'"></span>
                        </button>
                    </div>

                    <!-- Like Button -->
                    <button type="button"
                            @click.stop="toggleLike()"
                            class="flex flex-col items-center gap-1 group cursor-pointer">
                        <div class="w-11 h-11 rounded-full bg-black/40 backdrop-blur flex items-center justify-center group-hover:scale-110 transition"
                             :class="{ 'animate-heart-pop': isLiked }">
                            <svg class="w-6 h-6 transition"
                                 :class="isLiked ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-white'"
                                 viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold drop-shadow" x-text="(activeReel?.likes || 12) + (isLiked ? 1 : 0)"></span>
                    </button>

                    <!-- Tagged Products Button (Triggers Bottom Sheet) -->
                    <button type="button"
                            @click.stop="drawerOpen = true"
                            class="flex flex-col items-center gap-1 group cursor-pointer">
                        <div class="w-11 h-11 rounded-full bg-white text-black flex items-center justify-center font-bold shadow-lg group-hover:scale-110 transition relative">
                            <span class="text-lg">🛍️</span>
                            <span class="absolute -top-1 -right-1 bg-black text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white"
                                  x-text="activeReel?.products_detail?.length || (activeReel?.product ? 1 : 0)"></span>
                        </div>
                        <span class="text-[11px] font-bold drop-shadow">Shop</span>
                    </button>

                    <!-- Share Button -->
                    <button type="button"
                            @click.stop="shareVideo()"
                            class="flex flex-col items-center gap-1 group cursor-pointer">
                        <div class="w-11 h-11 rounded-full bg-black/40 backdrop-blur flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        </div>
                        <span class="text-[11px] font-bold drop-shadow" x-text="activeReel?.shares || 4"></span>
                    </button>

                    <!-- Desktop Up / Down Arrows -->
                    <div class="hidden sm:flex flex-col gap-2 pt-2">
                        <button type="button"
                                @click.stop="prevVideo()"
                                class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur flex items-center justify-center transition cursor-pointer"
                                aria-label="Previous reel">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button"
                                @click.stop="nextVideo()"
                                class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur flex items-center justify-center transition cursor-pointer"
                                aria-label="Next reel">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Bottom Left Info & Floating Mini Product Card -->
                <div class="absolute bottom-4 left-3 right-16 z-30 text-white pointer-events-none">
                    <!-- Creator Tag -->
                    <div class="flex items-center gap-2 mb-1 pointer-events-auto">
                        <span class="text-xs sm:text-sm font-black drop-shadow" x-text="activeReel?.vendor_name"></span>
                        <span class="text-white text-xs">✓</span>
                    </div>

                    <!-- Video Title -->
                    <p class="text-xs sm:text-sm font-medium line-clamp-2 drop-shadow mb-2 text-zinc-100"
                       x-text="activeReel?.title"></p>

                    <!-- Floating Minimal Shoppable Mini-Card (Exact Match) -->
                    <template x-if="activeReel?.products_detail && activeReel.products_detail.length > 0">
                        <div class="pointer-events-auto bg-black/90 backdrop-blur-md text-white rounded-xl p-2.5 shadow-2xl border border-zinc-700 flex items-center gap-3 cursor-pointer hover:bg-black transition"
                             @click="drawerOpen = true">
                            <img :src="activeReel.products_detail[0].image"
                                 class="w-12 h-12 rounded-lg object-cover border border-zinc-700 shrink-0">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-white truncate" x-text="activeReel.products_detail[0].title"></h4>
                                <div class="flex items-baseline gap-2 mt-0.5">
                                    <span class="text-sm font-black text-white" x-text="activeReel.products_detail[0].price"></span>
                                </div>
                            </div>
                            <button type="button" class="bg-white text-black font-black text-xs px-3 py-1.5 rounded-lg shrink-0 shadow">
                                SHOP ➔
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Video Playback Progress Bar (Scrubber) -->
                <div class="absolute bottom-0 inset-x-0 h-1 bg-white/20 z-40">
                    <div class="h-full bg-white transition-all duration-100"
                         :style="`width: ${progressPct}%`"></div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════════ -->
                <!-- TAGGED PRODUCTS BOTTOM SHEET DRAWER (EXACT SHORTS PAGE MATCH) -->
                <!-- ═══════════════════════════════════════════════════════════════ -->
                <div x-show="drawerOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full"
                     class="absolute inset-x-0 bottom-0 z-50 bg-zinc-950 text-white rounded-t-3xl max-h-[75vh] flex flex-col shadow-2xl border-t border-zinc-800 pointer-events-auto">
                    
                    <!-- Drawer Header -->
                    <div class="p-4 border-b border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🛍️</span>
                            <span class="text-xs font-black uppercase tracking-wider text-white">Tagged Products in this Reel</span>
                        </div>
                        <button type="button"
                                @click="drawerOpen = false"
                                class="w-8 h-8 rounded-full bg-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center transition cursor-pointer">
                            ✕
                        </button>
                    </div>

                    <!-- Products List in Drawer -->
                    <div class="p-4 overflow-y-auto space-y-3 flex-1 no-scrollbar">
                        <template x-for="item in (activeReel?.products_detail || [])" :key="item.id">
                            <div class="p-3 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center gap-3">
                                <img :src="item.image" class="w-14 h-14 rounded-xl object-cover border border-zinc-700 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-white line-clamp-1" x-text="item.title"></h5>
                                    <div class="text-sm font-black text-white mt-0.5" x-text="item.price"></div>
                                </div>
                                <button type="button"
                                        @click="window.quickAddToCart(item.id, item.variant_id, $el); drawerOpen = false"
                                        class="px-3.5 py-2 rounded-xl bg-white hover:bg-zinc-200 text-black font-black text-xs uppercase tracking-wider shrink-0 transition active:scale-95 cursor-pointer">
                                    + Add to Bag
                                </button>
                            </div>
                        </template>
                    </div>

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
            isPlaying: false,
            isMuted: false,
            progressPct: 0,
            drawerOpen: false,
            isLiked: false,
            isFollowed: false,
            isSliding: false,
            slideDirection: 'up',
            touchStartY: 0,

            get activeReel() {
                return this.reels[this.currentIndex] || null;
            },

            openModal(idx) {
                this.currentIndex = idx;
                this.isOpen = true;
                this.drawerOpen = false;
                this.isLiked = false;
                this.isFollowed = false;
                document.body.style.overflow = 'hidden';
                document.body.classList.add('shorts-modal-active');
                window.dispatchEvent(new CustomEvent('shorts-modal-state', { detail: { open: true } }));

                this.$nextTick(() => {
                    this.loadVideo();
                });
            },

            closeModal() {
                this.isOpen = false;
                this.drawerOpen = false;
                document.body.style.overflow = '';
                document.body.classList.remove('shorts-modal-active');
                window.dispatchEvent(new CustomEvent('shorts-modal-state', { detail: { open: false } }));
                const video = this.$refs.storeVideoEl;
                if (video) video.pause();
            },

            loadVideo() {
                const video = this.$refs.storeVideoEl;
                if (!video || !this.activeReel) return;
                video.src = this.activeReel.video_url;
                video.load();
                video.muted = this.isMuted;
                video.play().then(() => {
                    this.isPlaying = true;
                }).catch(() => {
                    video.muted = true;
                    this.isMuted = true;
                    video.play().then(() => {
                        this.isPlaying = true;
                    }).catch(e => console.warn('Autoplay prevented:', e));
                });
            },

            togglePlay() {
                const video = this.$refs.storeVideoEl;
                if (!video) return;
                if (video.paused) {
                    video.play();
                    this.isPlaying = true;
                } else {
                    video.pause();
                    this.isPlaying = false;
                }
            },

            toggleMute() {
                const video = this.$refs.storeVideoEl;
                if (!video) return;
                this.isMuted = !this.isMuted;
                video.muted = this.isMuted;
            },

            updateProgress() {
                const video = this.$refs.storeVideoEl;
                if (!video || !video.duration) return;
                this.progressPct = (video.currentTime / video.duration) * 100;
            },

            nextVideo() {
                this.slideDirection = 'up';
                this.isSliding = true;
                if (this.currentIndex < this.reels.length - 1) {
                    this.currentIndex++;
                } else {
                    this.currentIndex = 0;
                }
                this.drawerOpen = false;
                this.isLiked = false;
                this.$nextTick(() => {
                    this.loadVideo();
                    setTimeout(() => { this.isSliding = false; }, 340);
                });
            },

            prevVideo() {
                this.slideDirection = 'down';
                this.isSliding = true;
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                } else {
                    this.currentIndex = this.reels.length - 1;
                }
                this.drawerOpen = false;
                this.isLiked = false;
                this.$nextTick(() => {
                    this.loadVideo();
                    setTimeout(() => { this.isSliding = false; }, 340);
                });
            },

            touchStart(e) {
                this.touchStartY = e.touches[0].clientY;
            },

            touchEnd(e) {
                const touchEndY = e.changedTouches[0].clientY;
                const diff = this.touchStartY - touchEndY;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) {
                        this.nextVideo();
                    } else {
                        this.prevVideo();
                    }
                }
            },

            toggleLike() {
                this.isLiked = !this.isLiked;
            },

            toggleFollow() {
                this.isFollowed = !this.isFollowed;
            },

            shareVideo() {
                if (navigator.share) {
                    navigator.share({
                        title: this.activeReel?.title || 'K71 Reel',
                        url: window.location.href
                    }).catch(() => {});
                } else {
                    navigator.clipboard.writeText(window.location.href);
                    alert('Reel link copied to clipboard!');
                }
            }
        };
    }
</script>
@endif