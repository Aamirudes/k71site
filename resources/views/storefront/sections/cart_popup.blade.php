<div id="k71-glass-cart-popup" 
     x-data="glassCartModal()" 
     x-init="initCart()"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden" 
     x-show="isOpen" 
     style="display: none;">
    
    <!-- Ultra-blurred Ambient Backdrop with Smooth Fade -->
    <div class="fixed inset-0 bg-stone-950/60 backdrop-blur-md transition-opacity duration-300"
         @click="closeCart()"
         x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
        <!-- Frosted Glassmorphic Slide-Out Drawer -->
        <div class="w-screen max-w-md relative flex flex-col shadow-2xl transition-all duration-300 transform overflow-hidden"
             style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(32px); -webkit-backdrop-filter: blur(32px); border-left: 1px solid rgba(255, 255, 255, 0.4);"
             x-show="isOpen"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">

            <!-- Liquid Ambient Glow Bubbles in Drawer Background -->
            <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full pointer-events-none opacity-40 blur-3xl"
                 style="background: radial-gradient(circle, var(--sf-accent, #c59b27) 0%, transparent 70%);"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 rounded-full pointer-events-none opacity-30 blur-3xl"
                 style="background: radial-gradient(circle, var(--sf-primary, #18181b) 0%, transparent 70%);"></div>

            <!-- Drawer Header -->
            <div class="relative z-10 p-5 sm:p-6 border-b border-stone-200/60 flex items-center justify-between bg-white/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg shadow-sm"
                         style="background: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.15); color: var(--sf-accent, #c59b27);">
                        🛍️
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold tracking-tight text-stone-900 font-heading">
                            Boutique Bag
                        </h3>
                        <span class="text-xs font-semibold text-stone-500">
                            <span x-text="cartCount"></span> <span x-text="cartCount === 1 ? 'item' : 'items'"></span> in your bag
                        </span>
                    </div>
                </div>

                <button type="button" 
                        @click="closeCart()" 
                        class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 hover:text-stone-900 flex items-center justify-center transition"
                        aria-label="Close cart drawer">
                    ✕
                </button>
            </div>

            <!-- Free Shipping Liquid Progress Meter -->
            <div class="relative z-10 px-5 sm:px-6 py-3 bg-amber-500/10 border-b border-amber-500/15">
                <div class="flex items-center justify-between text-xs font-bold text-stone-800 mb-1.5">
                    <span class="flex items-center gap-1.5">
                        <span class="text-sm">⚡</span>
                        <span x-show="subtotalRaw >= 199900">Complimentary Express Shipping Unlocked!</span>
                        <span x-show="subtotalRaw < 199900">Add <span class="text-amber-700" x-text="remainingForFreeShipping"></span> for Free Delivery</span>
                    </span>
                    <span x-text="freeShippingPercent + '%'"></span>
                </div>
                <div class="w-full h-2 rounded-full bg-stone-200/80 overflow-hidden p-0.5">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="'width: ' + freeShippingPercent + '%; background-color: var(--sf-accent, #c59b27);'"></div>
                </div>
            </div>

            <!-- Drawer Body: Cart Items List -->
            <div class="relative z-10 flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
                
                <!-- Loading State -->
                <div x-show="loading" class="py-16 text-center space-y-3">
                    <span class="w-8 h-8 rounded-full border-3 border-stone-900 border-t-transparent animate-spin inline-block"></span>
                    <div class="text-xs font-bold text-stone-600">Updating bag items...</div>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && items.length === 0" class="py-16 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center text-3xl shadow-inner"
                         style="background: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.1);">
                        🛍️
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-stone-900 font-heading">Your bag is empty</h4>
                        <p class="text-xs text-stone-500 mt-1 max-w-xs mx-auto">
                            Explore handcrafted boutique creations and add your favorites to checkout.
                        </p>
                    </div>
                    <button type="button" 
                            @click="closeCart()" 
                            class="px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider text-white shadow-md transition hover:scale-102"
                            style="background-color: var(--sf-primary, #18181b);">
                        Explore Catalog
                    </button>
                </div>

                <!-- Items Loop -->
                <template x-for="item in items" :key="item.id">
                    <div class="p-3.5 rounded-2xl bg-white/70 border border-stone-200/80 shadow-2xs flex gap-3.5 items-center relative group hover:shadow-md transition">
                        <!-- Product Thumbnail -->
                        <div class="w-16 h-20 rounded-xl overflow-hidden bg-stone-100 shrink-0 border border-stone-200/60 relative">
                            <img :src="item.image_url || '{{ asset('images/product-placeholder.svg') }}'" 
                                 :alt="item.product_title" 
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Item Details -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-stone-900 truncate" x-text="item.product_title"></h4>
                            <div class="text-[11px] text-stone-500 mt-0.5" x-text="item.variant_label || 'Standard Edition'"></div>
                            
                            <div class="flex items-center justify-between mt-2.5">
                                <span class="text-xs sm:text-sm font-black text-stone-950" x-text="item.line_total_formatted"></span>

                                <!-- Quantity Increment / Decrement Controls -->
                                <div class="flex items-center gap-2 border border-stone-200 rounded-lg bg-stone-50/80 px-2 py-0.5">
                                    <button type="button" 
                                            @click="updateQty(item.id, item.quantity - 1)" 
                                            class="text-xs font-bold text-stone-600 hover:text-stone-950 p-0.5">
                                        −
                                    </button>
                                    <span class="text-xs font-bold text-stone-900 min-w-[14px] text-center" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQty(item.id, item.quantity + 1)" 
                                            class="text-xs font-bold text-stone-600 hover:text-stone-950 p-0.5">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Remove Button -->
                        <button type="button" 
                                @click="removeItem(item.id)" 
                                class="absolute top-2 right-2 text-stone-400 hover:text-rose-600 p-1 text-xs opacity-60 group-hover:opacity-100 transition"
                                title="Remove item">
                            ✕
                        </button>
                    </div>
                </template>

            </div>

            <!-- Drawer Footer (Subtotal & Direct Checkout) -->
            <div x-show="items.length > 0" class="relative z-10 p-5 sm:p-6 border-t border-stone-200/70 bg-white/70 space-y-4">
                
                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-stone-600">
                        <span>Bag Subtotal</span>
                        <span class="font-bold text-stone-900" x-text="subtotalFormatted"></span>
                    </div>
                    <div class="flex items-center justify-between text-stone-600">
                        <span>Pan-India Shipping</span>
                        <span class="font-bold text-emerald-600" x-text="subtotalRaw >= 199900 ? 'FREE' : 'Calculated at Checkout'"></span>
                    </div>
                    <div class="pt-2 border-t border-stone-200 flex items-center justify-between text-sm sm:text-base font-extrabold text-stone-950">
                        <span>Total Due</span>
                        <span x-text="subtotalFormatted"></span>
                    </div>
                </div>

                <!-- Instant 1-Click Checkout Action -->
                <div class="space-y-2">
                    <a :href="'{{ route('checkout.vendor', $vendor->id) }}'" 
                       class="w-full py-3.5 px-6 rounded-2xl text-xs sm:text-sm font-extrabold uppercase tracking-wider text-white shadow-xl hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-2 hover:scale-102"
                       style="background-color: var(--sf-primary, #18181b);">
                        <span>Proceed to Boutique Checkout</span>
                        <span>→</span>
                    </a>
                    
                    <button type="button" 
                            @click="closeCart()" 
                            class="w-full py-2 text-center text-xs font-semibold text-stone-500 hover:text-stone-800 transition">
                        Continue Exploring Store
                    </button>
                </div>

                <div class="flex items-center justify-center gap-3 text-[11px] text-stone-400 font-medium pt-1">
                    <span>🔒 256-Bit SSL Encrypted</span>
                    <span>•</span>
                    <span>Verified Seller Guarantee</span>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    function glassCartModal() {
        return {
            isOpen: false,
            loading: false,
            cartCount: 0,
            subtotalRaw: 0,
            subtotalFormatted: '₹0.00',
            items: [],

            get freeShippingPercent() {
                if (this.subtotalRaw >= 199900) return 100;
                return Math.min(100, Math.round((this.subtotalRaw / 199900) * 100));
            },

            get remainingForFreeShipping() {
                const diff = 199900 - this.subtotalRaw;
                if (diff <= 0) return '₹0.00';
                return '₹' + (diff / 100).toFixed(2);
            },

            initCart() {
                // Hook global cart open methods
                window.openK71Cart = () => this.openCart();
                window.refreshK71Cart = () => this.fetchCartData();

                // Listen for window events
                window.addEventListener('open-mini-cart', () => this.openCart());
                window.addEventListener('cart-updated', () => this.fetchCartData());

                // Initial fetch
                this.fetchCartData(false);
            },

            openCart() {
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
                this.fetchCartData(true);
            },

            closeCart() {
                this.isOpen = false;
                document.body.style.overflow = '';
            },

            fetchCartData(showSpinner = false) {
                if (showSpinner) this.loading = true;

                fetch('{{ route('cart.data') }}', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    this.items = data.items || [];
                    this.cartCount = data.count || 0;
                    this.subtotalRaw = data.subtotal || 0;
                    this.subtotalFormatted = data.subtotal_formatted || '₹0.00';

                    // Update any static header badges
                    document.querySelectorAll('#cart-counter-badge').forEach(badge => {
                        badge.textContent = this.cartCount;
                        if (this.cartCount > 0) {
                            badge.classList.remove('scale-0');
                            badge.classList.add('scale-100');
                        } else {
                            badge.classList.add('scale-0');
                        }
                    });
                })
                .catch(() => {
                    this.loading = false;
                });
            },

            updateQty(itemId, newQty) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (newQty <= 0) {
                    this.removeItem(itemId);
                    return;
                }

                fetch('/cart/' + itemId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ quantity: newQty })
                })
                .then(() => this.fetchCartData(false));
            },

            removeItem(itemId) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('/cart/' + itemId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '{{ csrf_token() }}'
                    }
                })
                .then(() => this.fetchCartData(false));
            }
        };
    }
</script>

