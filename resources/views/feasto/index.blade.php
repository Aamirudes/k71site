<x-layouts.public title="Feasto | 10-Minute Hyperlocal Food, Sweets & Daily Needs">
    <div class="min-h-screen bg-[#F4F6F8] pb-24"
         x-data="{
            activeCat: 'all',
            cart: {},
            cartCount: 0,
            cartTotal: 0,
            cartDrawerOpen: false,

            addToCart(item) {
                if (!this.cart[item.id]) {
                    this.cart[item.id] = { ...item, qty: 1 };
                } else {
                    this.cart[item.id].qty++;
                }
                this.updateCartTotals();
            },

            decrementCart(itemId) {
                if (this.cart[itemId]) {
                    this.cart[itemId].qty--;
                    if (this.cart[itemId].qty <= 0) {
                        delete this.cart[itemId];
                    }
                }
                this.updateCartTotals();
            },

            updateCartTotals() {
                let count = 0;
                let total = 0;
                Object.values(this.cart).forEach(i => {
                    count += i.qty;
                    total += (i.qty * parseFloat(i.base_price));
                });
                this.cartCount = count;
                this.cartTotal = total;
            }
         }">

        <!-- FEASTO BLINKIT SPEED HERO BAR WITH ANIMATIVE GLASSMORPHISM -->
        <div class="relative bg-gradient-to-r from-[#0C1E17] via-[#103828] to-[#0A1A14] text-white pt-6 pb-10 px-4 sm:px-6 rounded-b-[2.5rem] shadow-xl overflow-hidden">
            
            <!-- Atmospheric Indian Floral Glow Background -->
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-amber-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto relative z-10">
                
                <!-- Speed Top Bar -->
                <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-amber-400 text-slate-950 font-black flex items-center justify-center text-2xl shadow-lg shadow-amber-400/30 shrink-0 animate-pulse">
                            ⚡
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-xl sm:text-2xl tracking-tight text-white font-serif">FEASTO</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/30 text-emerald-300 text-[10px] font-mono font-bold border border-emerald-400/30">
                                    BLINKIT SPEED
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-emerald-200/90 font-medium mt-0.5">
                                <span>📍 Delivery to</span>
                                <strong class="text-white underline cursor-pointer">Makrana Central, Rajasthan</strong>
                                <span>▼</span>
                            </div>
                        </div>
                    </div>

                    <!-- 10-Min Timer Badge with Animated Scooter -->
                    <div class="flex items-center gap-3 bg-black/40 backdrop-blur-xl border border-white/10 px-4 py-2 rounded-2xl shadow-inner">
                        <div class="text-2xl animate-bounce-subtle">🛵</div>
                        <div>
                            <span class="text-[10px] text-amber-300 font-bold uppercase tracking-wider block">Hyperlocal Dispatch</span>
                            <span class="text-xs sm:text-sm font-black text-white">⚡ 10 - 15 MINS DELIVERY</span>
                        </div>
                    </div>
                </div>

                <!-- Instant Search Bar -->
                <div class="max-w-2xl mx-auto relative mt-2">
                    <div class="relative">
                        <input type="text" placeholder="Search milk, sweets, namkeen, cold drinks, cloud kitchen..." 
                               class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm font-medium shadow-2xl outline-none focus:ring-4 focus:ring-amber-400/30 transition-all">
                        <div class="absolute left-4 top-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Partner Fleet Announcement Banner -->
                <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between flex-wrap gap-2 text-xs">
                    <span class="text-emerald-300/80">Want to earn delivering in your neighborhood?</span>
                    <a href="{{ route('delivery.onboarding') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400 text-slate-950 font-bold text-[11px] hover:bg-amber-300 transition shadow-xs">
                        <span>Earn ₹1,200/day as Delivery Partner</span>
                        <span>→</span>
                    </a>
                </div>

            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 mt-6">
            
            <!-- Category Pills Slider (Blinkit Style) -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-slate-700">Explore Superfast Aisles</h3>
                    <span class="text-[11px] text-slate-500">Fast Local Dispatches</span>
                </div>

                <div class="flex items-center gap-2.5 overflow-x-auto no-scrollbar pb-2 scroll-smooth">
                    @foreach($categories as $cat)
                        <button type="button" @click="activeCat = '{{ $cat['id'] }}'"
                                :class="activeCat === '{{ $cat['id'] }}' ? 'bg-slate-950 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="px-4 py-2.5 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shrink-0 flex items-center gap-2 cursor-pointer">
                            <span class="text-sm">{{ $cat['icon'] }}</span>
                            <span>{{ $cat['name'] }}</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded-full font-mono uppercase {{ $cat['id'] === 'all' ? 'bg-amber-400 text-slate-950' : 'bg-slate-100 text-slate-500' }}">
                                {{ $cat['tag'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid (Blinkit Style Cards) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5 sm:gap-4">
                @foreach($products as $p)
                    <div class="p-3 rounded-2xl bg-white border border-slate-200/90 hover:border-emerald-500/50 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group relative overflow-hidden"
                         x-show="activeCat === 'all' || activeCat === '{{ $p->category_slug ?? 'all' }}'">
                        
                        <!-- Top Speed Pill -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                <span>⚡</span>
                                <span>{{ $p->delivery_minutes ?? '10-15m' }}</span>
                            </span>
                            @if(!empty($p->brand))
                                <span class="text-[9px] text-slate-400 font-medium truncate max-w-[70px]">{{ $p->brand }}</span>
                            @endif
                        </div>

                        <!-- Product Thumbnail -->
                        <div class="aspect-square rounded-xl bg-slate-50 overflow-hidden mb-2.5 flex items-center justify-center p-2">
                            @if(!empty($p->featured_image))
                                <img src="{{ $p->featured_image }}" alt="{{ $p->title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                            @else
                                <span class="text-3xl">🍲</span>
                            @endif
                        </div>

                        <!-- Product Info -->
                        <div class="mb-3">
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-2 leading-snug group-hover:text-emerald-800 transition-colors">
                                {{ $p->title }}
                            </h4>
                            <div class="flex items-baseline gap-1.5 mt-1.5">
                                <span class="text-xs sm:text-sm font-mono font-black text-slate-950">
                                    ₹{{ number_format($p->base_price, 0) }}
                                </span>
                                @if(!empty($p->compare_at_price) && $p->compare_at_price > $p->base_price)
                                    <span class="text-[10px] font-mono text-slate-400 line-through">
                                        ₹{{ number_format($p->compare_at_price, 0) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 1-Tap Add or Quantity Incrementer -->
                        <div>
                            <template x-if="!cart['{{ $p->id }}'] || cart['{{ $p->id }}'].qty <= 0">
                                <button type="button" 
                                        @click="addToCart({ id: '{{ $p->id }}', title: '{{ addslashes($p->title) }}', base_price: '{{ $p->base_price }}' })"
                                        class="w-full py-1.5 px-3 rounded-xl border-2 border-emerald-600 bg-emerald-50/60 hover:bg-emerald-600 text-emerald-800 hover:text-white font-black text-xs transition-all cursor-pointer">
                                    ADD +
                                </button>
                            </template>
                            <template x-if="cart['{{ $p->id }}'] && cart['{{ $p->id }}'].qty > 0">
                                <div class="w-full flex items-center justify-between rounded-xl bg-emerald-700 text-white font-bold text-xs py-1 px-2 shadow-xs">
                                    <button type="button" @click="decrementCart('{{ $p->id }}')" class="w-6 h-6 flex items-center justify-center hover:bg-emerald-800 rounded cursor-pointer">-</button>
                                    <span class="font-mono" x-text="cart['{{ $p->id }}'].qty"></span>
                                    <button type="button" @click="addToCart({ id: '{{ $p->id }}', title: '{{ addslashes($p->title) }}', base_price: '{{ $p->base_price }}' })" class="w-6 h-6 flex items-center justify-center hover:bg-emerald-800 rounded cursor-pointer">+</button>
                                </div>
                            </template>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

        <!-- STICKY FLOATING CART BAR (BLINKIT STYLE BOTTOM STRIP) -->
        <div x-show="cartCount > 0" 
             x-cloak 
             x-transition 
             class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-40">
            <div class="p-3.5 rounded-2xl bg-slate-950 text-white shadow-2xl border border-white/20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-black text-sm">
                        <span x-text="cartCount"></span>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-emerald-400">⚡ 10-Min Delivery</div>
                        <div class="text-sm font-mono font-black" x-text="'₹' + cartTotal"></div>
                    </div>
                </div>
                <a href="{{ route('cart.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition cursor-pointer flex items-center gap-1.5 shadow-lg shadow-emerald-500/30">
                    <span>View Cart &amp; Order</span>
                    <span>→</span>
                </a>
            </div>
        </div>

    </div>
</x-layouts.public>

