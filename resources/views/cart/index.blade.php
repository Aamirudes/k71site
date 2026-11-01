<x-layouts.public title="Your Cart">
    <div class="mb-12">
        <h1 class="text-3xl sm:text-4xl font-sans font-bold text-zinc-900 tracking-tight text-center sm:text-left">
            Your Cart
        </h1>
        <p class="text-brand-muted mt-2 text-sm sm:text-base font-light text-center sm:text-left">Review your items before checkout.</p>
    </div>

    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if($groupedItems->isEmpty())
        <div class="bg-white rounded-[3rem] p-16 text-center border border-gray-100 shadow-sm flex flex-col items-center justify-center min-h-[50vh]">
            <div class="w-24 h-24 bg-brand-surface rounded-full flex items-center justify-center mb-6">
                <svg class="h-10 w-10 text-brand-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h2 class="text-3xl font-serif font-bold text-brand-text mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-8 font-light text-lg">Looks like you haven't added anything to your cart yet.</p>
            <a href="{{ route('catalog.index') }}" class="px-8 py-3.5 bg-zinc-900 text-white font-sans font-semibold text-xs tracking-wider uppercase rounded-full hover:bg-black transition-all shadow-sm active:scale-95">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="space-y-10 mb-24">
            @foreach($groupedItems as $vendorId => $items)
                @php 
                    $vendor = $items->first()->vendor; 
                    $subtotal = 0;
                @endphp
                
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Vendor Header -->
                    <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-4">
                            <!-- Vendor Logo -->
                            <div class="w-12 h-12 rounded-xl bg-white p-0.5 shadow-sm border border-gray-100 flex-shrink-0 relative overflow-hidden">
                                @if($vendor->logo)
                                    <img src="{{ Storage::url($vendor->logo) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full rounded-lg object-cover">
                                @else
                                    <div class="w-full h-full rounded-lg bg-gradient-to-br from-brand-rose to-[#D9B8B0] text-white flex items-center justify-center text-lg font-serif font-bold">
                                        {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Sold by</span>
                                <a href="{{ $vendor->storefront_url }}" class="block font-serif font-bold text-lg text-brand-text hover:text-brand-sage transition-colors leading-tight">{{ $vendor->shop_name }}</a>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $vendorHasOutOfStock = false;
                        foreach($items as $i) {
                            if(!$i->variant || $i->variant->stock <= 0) {
                                $vendorHasOutOfStock = true;
                                break;
                            }
                        }
                    @endphp
                    
                    <!-- Items List -->
                    <div class="px-6 sm:px-8 py-2">
                        <ul class="divide-y divide-gray-100">
                            @foreach($items as $item)
                                @php 
                                    $product = $item->product ?? $item->variant?->product;
                                    $price = $item->price_at_add ?? ($item->variant?->price_override ?? ($product?->base_price ?? 0));
                                    $itemTotal = $price * $item->quantity;
                                    $subtotal += $itemTotal;
                                    $isItemOutOfStock = !$item->gig_package_id && (!$item->variant || $item->variant->stock <= 0);
                                    $hasInsufficientStock = !$item->gig_package_id && ($item->variant && $item->variant->stock < $item->quantity);
                                @endphp
                                <li class="py-6 flex flex-col sm:flex-row gap-6 items-center sm:items-start {{ $isItemOutOfStock ? 'opacity-75 bg-rose-50/30 rounded-2xl p-4' : '' }}">
                                    <!-- Image -->
                                    <div class="flex-shrink-0 w-32 sm:w-36 aspect-square bg-[#F1EBE2]/40 rounded-2xl overflow-hidden relative border border-gray-50 group">
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
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-serif font-bold text-brand-text hover:text-brand-sage transition-colors leading-tight">
                                                    @if($product)
                                                        <a href="{{ route('product.show', $product->slug) }}">{{ $product->title }}</a>
                                                    @else
                                                        <span>Product Item</span>
                                                    @endif
                                                </h3>
                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                    @if($item->gigPackage)
                                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-800 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-full">
                                                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                            <span>{{ ucfirst($item->gigPackage->tier) }} Package ({{ $item->gigPackage->delivery_days }}d Delivery)</span>
                                                        </span>
                                                    @else
                                                        <p class="text-sm text-gray-500 bg-gray-50 inline-block px-3 py-1 rounded-full border border-gray-100">
                                                            Option: <strong class="text-brand-text">{{ $item->variant?->name ?? ($item->variant?->sku ?? 'Standard') }}</strong>
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
                                                            <span>Only {{ $item->variant->stock }} left</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xl font-sans font-bold text-brand-text tracking-tight">₹{{ number_format($price / 100, 0) }}</p>
                                                @if($item->quantity > 1)
                                                    <p class="text-xs text-gray-400 mt-1">₹{{ number_format($itemTotal / 100, 0) }} total</p>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="mt-6 sm:mt-auto flex items-center justify-between">
                                            <!-- Quantity Selector -->
                                            @if(!$isItemOutOfStock)
                                                <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center bg-gray-50 rounded-full border border-gray-200 overflow-hidden shadow-sm" x-data="{ qty: {{ $item->quantity }} }">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" @click="qty = Math.max(1, qty - 1)" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-brand-text transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                    </button>
                                                    <input type="number" name="quantity" min="1" max="{{ $item->gig_package_id ? 99 : ($item->variant?->stock ?? 10) }}" x-model="qty" class="w-12 text-center bg-transparent border-none focus:ring-0 text-sm font-bold text-brand-text p-0" onchange="this.form.submit()">
                                                    <button type="submit" @click="qty++" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-brand-text transition-colors" {{ (!$item->gig_package_id && $item->variant && $item->quantity >= $item->variant->stock) ? 'disabled' : '' }}>
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
                                                <button type="submit" class="flex items-center gap-2 px-3 py-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-full transition-colors text-sm font-medium" title="Remove Item">
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
                    
                    <!-- Footer / Checkout -->
                    <div class="px-6 sm:px-8 py-6 border-t border-gray-100 bg-brand-surface/10 flex flex-col sm:flex-row justify-between items-center gap-6">
                        <div class="text-center sm:text-left">
                            <p class="text-sm font-bold text-brand-text">Subtotal: <span class="text-xl">₹{{ number_format($subtotal / 100, 0) }}</span></p>
                            <p class="text-xs text-brand-muted mt-1">Shipping & taxes calculated at checkout.</p>
                        </div>
                        @if($vendorHasOutOfStock)
                            <button type="button" disabled class="w-full sm:w-auto px-8 py-4 bg-gray-200 text-gray-500 font-sans font-bold uppercase text-[11px] tracking-wider rounded-full cursor-not-allowed flex items-center justify-center gap-2 border border-gray-300">
                                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Remove Out-of-Stock Items to Checkout</span>
                            </button>
                        @else
                            <a href="{{ route('checkout.vendor', $vendor->id) }}" class="w-full sm:w-auto px-7 py-3.5 bg-zinc-900 text-white font-sans font-semibold text-xs tracking-wider uppercase rounded-full hover:bg-black transition-all flex items-center justify-center gap-2.5 shadow-sm active:scale-95">
                                <span>Checkout {{ $vendor->shop_name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.public>
