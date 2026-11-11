<x-layouts.public title="Your Cart" :fullWidth="true">
    <!-- Grail Obsidian Cart Hero Band -->
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-7 pb-11 sm:pt-10 sm:pb-14">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <span class="grail-eyebrow block mb-2">K71 Curated Bag</span>
                    <h1 class="grail-display text-[clamp(2rem,4.5vw,3.25rem)] font-bold text-white tracking-tight">
                        Your Cart
                    </h1>
                    <p class="text-white/65 mt-1.5 text-sm font-light">Review your curated items before direct boutique checkout.</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-3.5 py-1.5 rounded-full bg-white/8 border border-white/15 text-[#ECD2F3] font-semibold tabular-nums">
                        {{ $groupedItems->count() }} {{ Str::plural('Boutique Order', $groupedItems->count()) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Grail Stone Sheet -->
    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] min-h-[70vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if($groupedItems->isEmpty())
        <div class="bg-white rounded-[28px] p-12 sm:p-16 text-center border border-black/8 shadow-[0_14px_40px_rgba(26,16,24,0.06)] flex flex-col items-center justify-center min-h-[46vh]">
            <div class="w-24 h-24 bg-[#1A1018] rounded-full flex items-center justify-center mb-6 shadow-md">
                <svg class="h-10 w-10 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h2 class="grail-display text-3xl font-bold text-[#1A1018] mb-2">Your cart is empty</h2>
            <p class="text-[#1A1018]/65 mb-8 font-light text-base">Looks like you haven't added anything to your cart yet.</p>
            <a href="{{ route('catalog.index') }}" class="grail-btn-obsidian px-8 py-3.5 text-xs tracking-wider uppercase shadow-sm active:scale-95">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="space-y-8 mb-12">
            @foreach($groupedItems as $vendorId => $items)
                @php 
                    $vendor = $items->first()->vendor; 
                    $subtotal = 0;
                @endphp
                
                <div class="bg-white rounded-[24px] border border-black/8 shadow-[0_12px_36px_rgba(26,16,24,0.05)] overflow-hidden hover:shadow-[0_16px_44px_rgba(26,16,24,0.08)] transition-shadow">
                    <!-- Vendor Header -->
                    <div class="px-6 sm:px-8 py-5 border-b border-black/8 flex items-center justify-between bg-[#FAF8F5]">
                        <div class="flex items-center gap-4">
                            <!-- Vendor Logo -->
                            <div class="w-12 h-12 rounded-xl bg-white p-0.5 shadow-sm border border-black/8 flex-shrink-0 relative overflow-hidden">
                                @if($vendor?->logo)
                                    <img src="{{ Storage::url($vendor->logo) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full rounded-lg object-cover">
                                @else
                                    <div class="w-full h-full rounded-lg bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center text-lg font-serif font-bold">
                                        {{ strtoupper(substr($vendor?->shop_name ?? 'S', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] text-[#623562] uppercase tracking-[0.14em] font-bold">Sold by</span>
                                <a href="{{ $vendor?->storefront_url ?? '#' }}" class="block grail-display font-bold text-lg text-[#1A1018] hover:text-[#623562] transition-colors leading-tight">{{ $vendor?->shop_name ?? 'Seller' }}</a>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $vendorHasOutOfStock = false;
                        foreach($items as $i) {
                            $p = $i->product ?? $i->variant?->product;
                            $avail = $i->gig_package_id ? 99 : ($i->variant ? (int) $i->variant->stock : (int) ($p?->total_stock ?? 100));
                            if (!$i->gig_package_id && $avail <= 0) {
                                $vendorHasOutOfStock = true;
                                break;
                            }
                        }
                    @endphp
                    
                    <!-- Items List -->
                    <div class="px-6 sm:px-8 py-2">
                        <ul class="divide-y divide-black/8">
                            @foreach($items as $item)
                                @php 
                                    $product = $item->product ?? $item->variant?->product;
                                    $price = $item->price_at_add ?? ($item->variant?->price_override ?? ($product?->base_price ?? 0));
                                    $itemTotal = $price * $item->quantity;
                                    $subtotal += $itemTotal;
                                    $availStock = $item->gig_package_id ? 99 : ($item->variant ? (int) $item->variant->stock : (int) ($product?->total_stock ?? 100));
                                    $isItemOutOfStock = !$item->gig_package_id && $availStock <= 0;
                                    $hasInsufficientStock = !$item->gig_package_id && $availStock < $item->quantity;
                                @endphp
                                <li class="py-6 flex flex-col sm:flex-row gap-6 items-center sm:items-start {{ $isItemOutOfStock ? 'opacity-75 bg-rose-50/30 rounded-2xl p-4' : '' }}">
                                    <!-- Image -->
                                    <div class="flex-shrink-0 w-32 sm:w-36 aspect-square bg-[#F4F1EC] rounded-2xl overflow-hidden relative border border-black/6 group">
                                        @if($product)
                                            <img src="{{ $product->primary_image_url }}" 
                                                 alt="{{ $product->title }}"
                                                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 p-1"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        @else
                                            <img src="{{ asset('images/product-placeholder.svg') }}" 
                                                 alt="Product" 
                                                 class="absolute inset-0 w-full h-full object-contain p-3">
                                        @endif

                                        @if($isItemOutOfStock)
                                            <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center">
                                                <span class="px-2.5 py-1 bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider rounded-md shadow-sm">Sold Out</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Item Details -->
                                    <div class="flex-1 flex flex-col w-full">
                                        <div class="flex justify-between items-start gap-4">
                                            <div>
                                                <h3 class="grail-display text-lg font-bold text-[#1A1018] hover:text-[#623562] transition-colors leading-tight">
                                                    @if($product)
                                                        <a href="{{ route('product.show', $product->slug) }}">{{ $product->title }}</a>
                                                    @else
                                                        <span>Product Item</span>
                                                    @endif
                                                </h3>
                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                    @if($item->gigPackage)
                                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#1A1018] bg-[#ECD2F3]/50 border border-[#623562]/20 px-3 py-1 rounded-full">
                                                            <svg class="w-3.5 h-3.5 text-[#623562]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                            <span>{{ ucfirst($item->gigPackage->tier) }} Package ({{ $item->gigPackage->delivery_days }}d Delivery)</span>
                                                        </span>
                                                    @else
                                                        <p class="text-xs text-[#1A1018]/70 bg-[#FAF8F5] inline-block px-3 py-1 rounded-full border border-black/8">
                                                            Option: <strong class="text-[#1A1018]">{{ $item->variant?->name ?? ($item->variant?->sku ?? 'Standard') }}</strong>
                                                        </p>
                                                    @endif

                                                    @if(!empty($item->selected_modifiers_json))
                                                        @foreach($item->selected_modifiers_json as $mod)
                                                            <span class="inline-flex items-center text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                                                + {{ $mod['name'] }} (+₹{{ number_format($mod['price_delta'], 2) }})
                                                            </span>
                                                        @endforeach
                                                    @endif

                                                    @if($isItemOutOfStock)
                                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            <span>Out of Stock</span>
                                                        </span>
                                                    @elseif($hasInsufficientStock)
                                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                            <span>Only {{ $availStock }} left</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="grail-display text-xl font-bold text-[#1A1018] tracking-tight tabular-nums">₹{{ number_format($price / 100, 0) }}</p>
                                                @if($item->quantity > 1)
                                                    <p class="text-xs text-[#1A1018]/50 mt-1 tabular-nums">₹{{ number_format($itemTotal / 100, 0) }} total</p>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="mt-6 sm:mt-auto flex items-center justify-between">
                                            <!-- Quantity Selector -->
                                            @if(!$isItemOutOfStock)
                                                <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center bg-[#FAF8F5] rounded-full border border-black/10 overflow-hidden shadow-2xs" x-data="{ qty: {{ $item->quantity }} }">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" @click="qty = Math.max(1, qty - 1)" class="w-10 h-10 flex items-center justify-center text-[#1A1018]/70 hover:bg-[#ECD2F3]/35 hover:text-[#1A1018] transition-colors cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                    </button>
                                                    <input type="number" name="quantity" min="1" max="{{ $availStock }}" x-model="qty" class="w-12 text-center bg-transparent border-none focus:ring-0 text-sm font-bold text-[#1A1018] p-0 tabular-nums" onchange="this.form.submit()">
                                                    <button type="submit" @click="qty++" class="w-10 h-10 flex items-center justify-center text-[#1A1018]/70 hover:bg-[#ECD2F3]/35 hover:text-[#1A1018] transition-colors cursor-pointer" {{ (!$item->gig_package_id && $item->quantity >= $availStock) ? 'disabled' : '' }}>
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-rose-600 font-semibold">Unavailable for purchase</span>
                                            @endif
                                            
                                            <!-- Remove -->
                                            <form action="{{ route('cart.remove', $item) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center gap-2 px-3 py-2 text-[#1A1018]/50 hover:text-rose-600 hover:bg-rose-50 rounded-full transition-colors text-sm font-medium cursor-pointer" title="Remove Item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    <span class="hidden sm:inline">Remove</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <!-- Footer / Checkout Obsidian Bar -->
                    <div class="px-6 sm:px-8 py-6 border-t border-white/10 bg-[#060606] text-[#EFE9F2] flex flex-col sm:flex-row justify-between items-center gap-6">
                        <div class="text-center sm:text-left">
                            <p class="text-xs uppercase tracking-[0.14em] text-[#FFCDFF] font-semibold">Order Subtotal</p>
                            <p class="grail-display text-2xl font-bold text-white tabular-nums mt-0.5">₹{{ number_format($subtotal / 100, 0) }}</p>
                            <p class="text-xs text-white/55 mt-1">Shipping &amp; taxes calculated at checkout.</p>
                        </div>
                        @if($vendorHasOutOfStock || !$vendor)
                            <button type="button" disabled class="w-full sm:w-auto px-8 py-4 bg-white/10 text-white/60 font-sans font-bold uppercase text-[11px] tracking-wider rounded-full cursor-not-allowed flex items-center justify-center gap-2 border border-white/15">
                                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ !$vendor ? 'Store Unavailable — Remove Item' : 'Remove Out-of-Stock Items to Checkout' }}</span>
                            </button>
                        @else
                            <a href="{{ route('checkout.vendor', $vendor->id) }}" class="grail-cta-pill w-full sm:w-auto px-7 py-3.5 text-xs tracking-wider uppercase flex items-center justify-center gap-2.5 shadow-md active:scale-95">
                                <span>Checkout {{ $vendor->shop_name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
        </div>
    </div>
</x-layouts.public>
