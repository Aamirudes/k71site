<div x-data="k71MiniCart()" 
     x-cloak
     @open-mini-cart.window="openCart()"
     @cart-updated.window="fetchCart()"
     @keydown.escape.window="closeCart()"
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
         @click="closeCart()"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>

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
                <h3 class="text-base font-semibold text-zinc-900 tracking-tight">Shopping Bag</h3>
                <span x-show="cartData.count > 0" 
                      x-text="'(' + cartData.count + ')'" 
                      class="text-xs font-semibold text-zinc-500 bg-zinc-100 px-2 py-0.5 rounded-full"></span>
            </div>
            <button type="button"
                    @click="closeCart()" 
                    id="k71-close-cart-btn"
                    aria-label="Close cart"
                    class="w-8 h-8 rounded-full hover:bg-zinc-100 text-zinc-500 hover:text-zinc-900 flex items-center justify-center transition-colors cursor-pointer">
                <svg width="20" height="20" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Reserved Bag Banner -->
        <div class="bg-zinc-50 px-5 py-2.5 border-b border-zinc-200/60 flex items-center justify-between text-xs text-zinc-600 font-medium">
            <span>Items reserved for checkout</span>
            <span class="font-mono text-zinc-900 font-semibold" x-text="timerString">01m50s</span>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-5 divide-y divide-zinc-100 no-scrollbar">
            <!-- Loading state -->
            <template x-if="isLoading">
                <div class="py-12 flex flex-col items-center justify-center text-zinc-400 gap-2">
                    <svg class="animate-spin h-6 w-6 text-zinc-900" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs">Updating bag...</span>
                </div>
            </template>

            <!-- Empty state -->
            <template x-if="!isLoading && (!cartData.items || cartData.items.length === 0)">
                <div class="py-16 text-center">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-zinc-100 flex items-center justify-center text-zinc-400">
                        <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-zinc-900">Your bag is empty</p>
                    <p class="text-xs text-zinc-500 mt-1">Explore trending collections from verified sellers</p>
                    <a href="{{ route('catalog.index') }}" 
                       @click="closeCart()"
                       class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-zinc-900 text-white text-xs font-semibold hover:bg-black transition-all">
                        Browse Catalog
                    </a>
                </div>
            </template>

            <!-- Item rows -->
            <template x-if="!isLoading && cartData.items && cartData.items.length > 0">
                <div class="space-y-4">
                    <template x-for="item in cartData.items" :key="item.id">
                        <div class="pt-3 first:pt-0 flex gap-3.5 items-start">
                            <!-- Thumbnail -->
                            <div class="w-16 h-20 bg-zinc-100 rounded-xl overflow-hidden flex-shrink-0 border border-zinc-200/60">
                                <template x-if="item.image_url">
                                    <img :src="item.image_url" :alt="item.product_title" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image_url">
                                    <div class="w-full h-full flex items-center justify-center text-zinc-300">
                                        <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-zinc-900 truncate" x-text="item.product_title"></h4>
                                <p class="text-[11px] text-zinc-500 mt-0.5" x-text="item.vendor_name"></p>
                                <p class="text-[10px] text-zinc-400 mt-0.5" x-text="'Option: ' + item.variant_label"></p>
                                
                                <div class="mt-2.5 flex items-center justify-between">
                                    <!-- Price -->
                                    <span class="text-xs font-bold text-zinc-900" x-text="item.unit_price_formatted"></span>

                                    <!-- Stepper & Actions -->
                                    <div class="flex items-center gap-2">
                                        <!-- Stepper -->
                                        <div class="flex items-center border border-zinc-200 rounded-lg bg-zinc-50/70">
                                            <button @click="updateQty(item, item.quantity - 1)" 
                                                    class="w-6 h-6 flex items-center justify-center text-zinc-600 hover:text-black text-xs font-bold transition-colors">
                                                −
                                            </button>
                                            <span class="w-6 text-center text-xs font-semibold text-zinc-800" x-text="item.quantity"></span>
                                            <button @click="updateQty(item, item.quantity + 1)" 
                                                    class="w-6 h-6 flex items-center justify-center text-zinc-600 hover:text-black text-xs font-bold transition-colors">
                                                +
                                            </button>
                                        </div>

                                        <!-- Trash Delete -->
                                        <button @click="removeItem(item)" 
                                                class="text-zinc-400 hover:text-red-500 p-1 transition-colors"
                                                title="Remove item">
                                            <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer / Checkout Area -->
        <template x-if="cartData.items && cartData.items.length > 0">
            <div class="border-t border-zinc-200/60 bg-zinc-50/80 p-5 space-y-3">
                <!-- Utility line -->
                <div class="flex items-center justify-around py-1 text-zinc-500 text-[11px]">
                    <span>Dispatch in 24h</span>
                    <span>•</span>
                    <span>Verified Sellers</span>
                    <span>•</span>
                    <span>Secure Checkout</span>
                </div>

                <!-- Subtotal Row -->
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs font-medium text-zinc-600">Subtotal:</span>
                    <span class="text-base font-bold text-zinc-900" x-text="cartData.subtotal_formatted"></span>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2 pt-1">
                    <a :href="cartData.items && cartData.items[0] ? cartData.items[0].checkout_url : '{{ route('cart.index') }}'"
                       @click="closeCart()"
                       class="w-full block text-center py-3 rounded-xl bg-zinc-950 text-white text-xs sm:text-sm font-semibold hover:bg-black transition-all shadow-xs">
                        Proceed to Checkout
                    </a>
                    <a href="{{ route('cart.index') }}" 
                       @click="closeCart()"
                       class="w-full block text-center py-2.5 rounded-xl border border-zinc-300 text-zinc-800 text-xs font-medium hover:bg-white transition-all">
                        View Detailed Cart
                    </a>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function k71MiniCart() {
    return {
        isOpen: false,
        isLoading: false,
        timerSeconds: 110,
        timerString: '01m50s',
        timerInterval: null,
        cartData: {
            items: [],
            count: 0,
            subtotal: 0,
            subtotal_formatted: '₹0.00'
        },
        init() {
            this.fetchCart();
            this.startTimer();
        },
        openCart() {
            this.isOpen = true;
            this.fetchCart();
            document.body.classList.add('overflow-hidden');
        },
        closeCart() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.timerSeconds > 0) {
                    this.timerSeconds--;
                    const m = Math.floor(this.timerSeconds / 60);
                    const s = this.timerSeconds % 60;
                    this.timerString = (m < 10 ? '0' + m : m) + 'm' + (s < 10 ? '0' + s : s) + 's';
                }
            }, 1000);
        },
        async fetchCart() {
            try {
                this.isLoading = true;
                const res = await fetch('/cart/data', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.cartData = data;
                    window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.count } }));
                }
            } catch (e) {
                console.error('Cart fetch failed', e);
            } finally {
                this.isLoading = false;
            }
        },
        async updateQty(item, newQty) {
            if (newQty < 1) {
                return this.removeItem(item);
            }
            try {
                this.isLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/cart/' + item.id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ quantity: newQty })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.cartData = data;
                    window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.count } }));
                }
            } catch (e) {
                console.error('Qty update error', e);
            } finally {
                this.isLoading = false;
            }
        },
        async removeItem(item) {
            try {
                this.isLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/cart/' + item.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.cartData = data;
                    window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.count } }));
                    if (typeof window.showToast === 'function') {
                        window.showToast('Item removed from bag');
                    }
                }
            } catch (e) {
                console.error('Remove error', e);
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
