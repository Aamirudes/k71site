<div x-data="k71MiniWishlist()" 
     x-cloak
     @open-mini-wishlist.window="openWishlist()"
     @wishlist-state-changed.window="fetchWishlist()"
     @keydown.escape.window="closeWishlist()"
     class="relative z-[100]">

    <!-- Backdrop -->
    <div x-show="isOpen" 
         style="display: none;"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeWishlist()"
         class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

    <!-- Drawer / Bottom Sheet Container -->
    <div x-show="isOpen"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full"
         x-transition:enter-end="translate-y-0 md:translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 md:translate-x-0"
         x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full"
         class="fixed bottom-0 md:bottom-0 md:top-0 md:right-0 inset-x-0 md:inset-x-auto w-full md:w-[420px] max-h-[85vh] md:max-h-screen bg-white/95 backdrop-blur-2xl rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none shadow-2xl flex flex-col overflow-hidden border-t md:border-t-0 md:border-l border-zinc-200/60">

        <!-- Sheet Header -->
        <div class="px-5 py-4 flex items-center justify-between border-b border-zinc-100 bg-white/80 backdrop-blur-md">
            <div class="flex items-center gap-2">
                <span class="text-rose-500 text-base">❤️</span>
                <h3 class="text-base font-semibold text-zinc-900 tracking-tight">Saved Wishlist</h3>
                <span x-show="wishlistData.count > 0" 
                      x-text="'(' + wishlistData.count + ')'" 
                      class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200/60"></span>
            </div>
            <button type="button"
                    @click="closeWishlist()" 
                    id="k71-close-wishlist-btn"
                    aria-label="Close wishlist"
                    class="w-8 h-8 rounded-full hover:bg-zinc-100 text-zinc-500 hover:text-zinc-900 flex items-center justify-center transition-colors cursor-pointer">
                <svg width="20" height="20" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Wishlist Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-5 divide-y divide-zinc-100 no-scrollbar">
            <!-- Loading state -->
            <template x-if="isLoading">
                <div class="py-12 flex flex-col items-center justify-center text-zinc-400 gap-2">
                    <svg class="animate-spin h-6 w-6 text-zinc-900" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs">Loading saved items...</span>
                </div>
            </template>

            <!-- Empty state -->
            <template x-if="!isLoading && (!wishlistData.items || wishlistData.items.length === 0)">
                <div class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 text-2xl border border-rose-100">
                        🤍
                    </div>
                    <p class="text-sm font-semibold text-zinc-900">Your wishlist is empty</p>
                    <p class="text-xs text-zinc-500 mt-1 max-w-xs mx-auto">Tap the heart icon on any product to save it here for later</p>
                    <a href="{{ route('catalog.index') }}" 
                       @click="closeWishlist()"
                       class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-zinc-900 text-white text-xs font-semibold hover:bg-black transition-all shadow-md">
                        Explore Catalog &rarr;
                    </a>
                </div>
            </template>

            <!-- Item rows -->
            <template x-if="!isLoading && wishlistData.items && wishlistData.items.length > 0">
                <div class="space-y-4">
                    <template x-for="item in wishlistData.items" :key="item.id">
                        <div class="pt-3 first:pt-0 flex gap-3.5 items-center justify-between">
                            <!-- Left: Thumbnail & Info -->
                            <div class="flex gap-3 items-center min-w-0">
                                <a :href="item.url" class="w-16 h-18 bg-zinc-100 rounded-xl overflow-hidden flex-shrink-0 border border-zinc-200/60 block">
                                    <template x-if="item.image_url">
                                        <img :src="item.image_url" :alt="item.title" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image_url">
                                        <div class="w-full h-full flex items-center justify-center text-zinc-300">
                                            <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                                        </div>
                                    </template>
                                </a>

                                <div class="min-w-0">
                                    <span class="text-[10px] text-zinc-500 uppercase font-bold tracking-wider truncate block" x-text="item.vendor_name"></span>
                                    <a :href="item.url" class="text-xs font-bold text-zinc-900 hover:text-indigo-600 line-clamp-1 block transition-colors" x-text="item.title"></a>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        <span class="text-xs font-black text-zinc-950" x-text="item.price_formatted"></span>
                                        <span x-show="item.in_stock" class="text-[9px] font-bold text-emerald-600">In Stock</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Action Buttons -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" 
                                        @click="moveToCart(item)"
                                        title="Move to Shopping Bag"
                                        class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-black text-white text-[11px] font-bold transition flex items-center gap-1 shadow-sm active:scale-95 cursor-pointer">
                                    <span>Bag</span>
                                    <span>&rarr;</span>
                                </button>
                                <button type="button" 
                                        @click="removeItem(item.id)"
                                        title="Remove from Wishlist"
                                        class="w-7 h-7 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-zinc-100 bg-white/80 backdrop-blur-md flex items-center justify-between">
            <span class="text-xs text-zinc-500 font-medium" x-text="wishlistData.count + ' Saved ' + (wishlistData.count === 1 ? 'Item' : 'Items')"></span>
            <button type="button" 
                    @click="closeWishlist()"
                    class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold transition cursor-pointer">
                Continue Browsing
            </button>
        </div>

    </div>
</div>

<script>
    function k71MiniWishlist() {
        return {
            isOpen: false,
            isLoading: false,
            wishlistData: {
                items: [],
                count: 0
            },
            init() {
                // Preload counts if any
            },
            openWishlist() {
                this.isOpen = true;
                this.fetchWishlist();
                document.body.style.overflow = 'hidden';
            },
            closeWishlist() {
                this.isOpen = false;
                document.body.style.overflow = '';
            },
            fetchWishlist() {
                this.isLoading = true;
                fetch('/wishlist/json', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.wishlistData = data;
                    this.isLoading = false;
                    // Update global badges
                    if (window.updateWishlistBadge) {
                        window.updateWishlistBadge(data.count);
                    }
                })
                .catch(() => {
                    this.isLoading = false;
                });
            },
            removeItem(productId) {
                if (window.toggleProductWishlist) {
                    window.toggleProductWishlist(productId);
                    this.wishlistData.items = this.wishlistData.items.filter(i => i.id !== productId);
                    this.wishlistData.count = Math.max(0, this.wishlistData.count - 1);
                }
            },
            moveToCart(item) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: item.id,
                        quantity: 1
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        // Remove from wishlist
                        this.removeItem(item.id);
                        if (window.showToast) window.showToast('Moved to Shopping Bag! 🛍️');
                        // Open cart drawer
                        this.closeWishlist();
                        setTimeout(() => {
                            window.dispatchEvent(new CustomEvent('cart-updated'));
                            window.dispatchEvent(new CustomEvent('open-mini-cart'));
                        }, 250);
                    } else {
                        if (window.showToast) window.showToast(data.message || 'Could not add to cart', false);
                    }
                })
                .catch(() => {
                    if (window.showToast) window.showToast('Network error adding to cart', false);
                });
            }
        };
    }
</script>
