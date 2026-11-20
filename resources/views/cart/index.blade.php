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

                                                    @if($product && $product->isGiftCustomizable())
                                                        @php
                                                            $cust = $item->customization_data ?? [];
                                                            $cfg = $product->getGiftCustomizationConfig();
                                                            $isMissingRequired = !empty($cfg['is_text_required']) && empty($cust['custom_text']);
                                                            $textLimit = (int) ($cfg['text_char_limit'] ?? 40);
                                                            $fonts = (array) ($cfg['available_fonts'] ?? ['Classic Serif', 'Modern Script', 'Bold Minimal', 'Vintage Engraving']);
                                                            $colors = (array) ($cfg['available_colors'] ?? ['Gold Foil', 'Silver Shimmer', 'Midnight Black', 'Rose Gold', 'Emerald Green']);
                                                            $defaultFont = $cust['font'] ?? ($fonts[0] ?? 'Classic Serif');
                                                            $defaultColor = $cust['color'] ?? ($colors[0] ?? 'Gold Foil');
                                                        @endphp

                                                        <div x-data="{
                                                            isModalOpen: false,
                                                            customText: @js($cust['custom_text'] ?? ''),
                                                            selectedFont: @js($defaultFont),
                                                            selectedColor: @js($defaultColor),
                                                            giftWrap: @js(!empty($cust['gift_wrap'])),
                                                            greetingCard: @js(!empty($cust['greeting_card'])),
                                                            greetingCardMessage: @js($cust['greeting_card_message'] ?? ''),
                                                            existingPhotoUrl: @js($cust['photo_url'] ?? ''),
                                                            openModal() { this.isModalOpen = true; },
                                                            closeModal() { this.isModalOpen = false; }
                                                        }" class="w-full mt-2.5">

                                                            <!-- In-Cart Personalization Summary & Trigger -->
                                                            <div class="p-3 bg-purple-50/70 border border-purple-200/80 rounded-2xl text-xs space-y-2 shadow-2xs">
                                                                <div class="flex items-center justify-between gap-2">
                                                                    <div class="flex items-center gap-1.5 font-bold text-purple-950 text-[11px] uppercase tracking-wider">
                                                                        <span>🎁 Custom Gift Personalization</span>
                                                                        @if($isMissingRequired)
                                                                            <span class="text-[9px] bg-rose-500/10 text-rose-600 border border-rose-500/20 px-1.5 py-0.5 rounded-md">
                                                                                ⚠️ Text Required
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    <button type="button"
                                                                            @click="openModal()"
                                                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition shadow-2xs cursor-pointer flex items-center gap-1 {{ !empty($cust) ? 'bg-white text-purple-700 border border-purple-200 hover:bg-purple-100' : 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-500/20' }}">
                                                                        <span>{{ !empty($cust) ? '✏️ Edit Specs' : '✨ Personalize Gift' }}</span>
                                                                    </button>
                                                                </div>

                                                                @if(!empty($cust['custom_text']))
                                                                    <div class="text-[#1A1018]">
                                                                        <span class="text-[#1A1018]/60 font-medium">Text:</span>
                                                                        <strong class="text-purple-950 font-serif text-sm">"{{ $cust['custom_text'] }}"</strong>
                                                                    </div>
                                                                @endif

                                                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-[#1A1018]/75">
                                                                    @if(!empty($cust['font']))
                                                                        <span class="px-2 py-0.5 rounded-md bg-white border border-purple-200">Font: <strong>{{ $cust['font'] }}</strong></span>
                                                                    @endif
                                                                    @if(!empty($cust['color']))
                                                                        <span class="px-2 py-0.5 rounded-md bg-white border border-purple-200">Color: <strong>{{ $cust['color'] }}</strong></span>
                                                                    @endif
                                                                    @if(!empty($cust['gift_wrap']))
                                                                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">🎀 Gift Wrap</span>
                                                                    @endif
                                                                    @if(!empty($cust['photo_url']))
                                                                        <span class="px-2 py-0.5 rounded-md bg-cyan-50 text-cyan-800 border border-cyan-200 font-semibold">📸 Photo Attached</span>
                                                                    @endif
                                                                </div>

                                                                @if(!empty($cust['greeting_card']) && !empty($cust['greeting_card_message']))
                                                                    <div class="text-[11px] text-[#1A1018] pt-0.5 italic">
                                                                        💌 "{{ $cust['greeting_card_message'] }}"
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- In-Cart Teleported Modal Popup (Before Checkout) -->
                                                            <template x-teleport="body">
                                                                <div x-show="isModalOpen"
                                                                     x-cloak
                                                                     @keydown.escape.window="closeModal()"
                                                                     class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-3 sm:p-5"
                                                                     style="display: none;">
                                                                    
                                                                    <div class="fixed inset-0 bg-black/75 backdrop-blur-md" @click="closeModal()"></div>

                                                                    <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden flex flex-col max-h-[90vh] z-10 text-slate-900 dark:text-white">
                                                                        <form action="{{ route('cart.update', $item) }}" method="POST" enctype="multipart/form-data" class="flex flex-col h-full overflow-hidden">
                                                                            @csrf
                                                                            @method('PUT')
                                                                            <input type="hidden" name="customization[existing_photo_url]" :value="existingPhotoUrl">

                                                                            <!-- Header -->
                                                                            <div class="px-5 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/40">
                                                                                <div class="flex items-center gap-2.5">
                                                                                    <span class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center text-base">🎁</span>
                                                                                    <div>
                                                                                        <h3 class="text-sm font-bold uppercase tracking-wider">Personalize Before Checkout</h3>
                                                                                        <p class="text-[11px] text-slate-500">{{ $product->title }}</p>
                                                                                    </div>
                                                                                </div>
                                                                                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-sm cursor-pointer">✕</button>
                                                                            </div>

                                                                            <!-- Body -->
                                                                            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
                                                                                <!-- Custom Text -->
                                                                                @if(!empty($cfg['allow_custom_text']))
                                                                                    <div class="space-y-1.5">
                                                                                        <div class="flex items-center justify-between">
                                                                                            <label class="font-bold text-slate-800 dark:text-slate-200">
                                                                                                {{ $cfg['text_prompt'] ?? 'Personalized Text / Engraving' }}
                                                                                                @if(!empty($cfg['is_text_required'])) <span class="text-rose-500">*</span> @endif
                                                                                            </label>
                                                                                            <span class="font-mono text-[11px] text-slate-400">Max {{ $textLimit }} chars</span>
                                                                                        </div>
                                                                                        <input type="text"
                                                                                               name="customization[custom_text]"
                                                                                               x-model="customText"
                                                                                               maxlength="{{ $textLimit }}"
                                                                                               placeholder="e.g. For My Beloved"
                                                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-purple-500">
                                                                                    </div>
                                                                                @endif

                                                                                <!-- Fonts -->
                                                                                @if(!empty($cfg['allow_font_selection']))
                                                                                    <div class="space-y-1.5">
                                                                                        <label class="font-bold text-slate-800 dark:text-slate-200 block">Typography / Script</label>
                                                                                        <input type="hidden" name="customization[font]" :value="selectedFont">
                                                                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                                                            @foreach($fonts as $f)
                                                                                                <button type="button"
                                                                                                        @click="selectedFont = '{{ addslashes($f) }}'"
                                                                                                        class="p-2 rounded-xl border text-center transition cursor-pointer text-xs"
                                                                                                        :class="selectedFont === '{{ addslashes($f) }}' ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/60 font-bold text-purple-700 dark:text-purple-300 ring-1 ring-purple-500' : 'border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950/40 text-slate-700 dark:text-slate-300'">
                                                                                                    {{ $f }}
                                                                                                </button>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    </div>
                                                                                @endif

                                                                                <!-- Foil Color -->
                                                                                @if(!empty($cfg['allow_color_selection']))
                                                                                    <div class="space-y-1.5">
                                                                                        <label class="font-bold text-slate-800 dark:text-slate-200 block">Foil / Engraving Color</label>
                                                                                        <input type="hidden" name="customization[color]" :value="selectedColor">
                                                                                        <div class="flex flex-wrap gap-2">
                                                                                            @foreach($colors as $c)
                                                                                                <button type="button"
                                                                                                        @click="selectedColor = '{{ addslashes($c) }}'"
                                                                                                        class="px-2.5 py-1 rounded-lg border text-xs cursor-pointer transition"
                                                                                                        :class="selectedColor === '{{ addslashes($c) }}' ? 'border-purple-600 bg-purple-50 text-purple-800 font-bold ring-1 ring-purple-500' : 'border-slate-200 text-slate-600'">
                                                                                                    {{ $c }}
                                                                                                </button>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    </div>
                                                                                @endif

                                                                                <!-- Photo Upload -->
                                                                                @if(!empty($cfg['allow_photo_upload']))
                                                                                    <div class="pt-2 border-t border-slate-100 dark:border-white/10 space-y-1.5">
                                                                                        <label class="font-bold text-slate-800 dark:text-slate-200 block">Replace / Upload Photo (JPEG, PNG, WEBP)</label>
                                                                                        <input type="file" name="customization_photo" accept="image/*" class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                                                                    </div>
                                                                                @endif

                                                                                <!-- Wrap & Card -->
                                                                                <div class="pt-2 border-t border-slate-100 dark:border-white/10 space-y-2">
                                                                                    @if(!empty($cfg['allow_gift_wrap']))
                                                                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 cursor-pointer">
                                                                                            <span class="flex items-center gap-2">
                                                                                                <input type="checkbox" name="customization[gift_wrap]" value="1" x-model="giftWrap" class="rounded text-purple-600">
                                                                                                <span>🎀 Luxury Ribbon Gift Wrapping</span>
                                                                                            </span>
                                                                                            <span class="font-mono font-bold">+₹{{ number_format(($cfg['gift_wrap_price_paise'] ?? 4900) / 100, 0) }}</span>
                                                                                        </label>
                                                                                    @endif

                                                                                    @if(!empty($cfg['allow_greeting_card']))
                                                                                        <div class="space-y-1.5">
                                                                                            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 cursor-pointer">
                                                                                                <span class="flex items-center gap-2">
                                                                                                    <input type="checkbox" name="customization[greeting_card]" value="1" x-model="greetingCard" class="rounded text-purple-600">
                                                                                                    <span>💌 Wax-Sealed Greeting Card</span>
                                                                                                </span>
                                                                                                <span class="font-mono font-bold">+₹{{ number_format(($cfg['greeting_card_price_paise'] ?? 2900) / 100, 0) }}</span>
                                                                                            </label>
                                                                                            <div x-show="greetingCard" x-collapse>
                                                                                                <textarea name="customization[greeting_card_message]" x-model="greetingCardMessage" rows="2" maxlength="300" placeholder="Dedication card message..." class="w-full p-2.5 text-xs rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-slate-900"></textarea>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>

                                                                            <!-- Footer -->
                                                                            <div class="px-5 py-3 border-t border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-slate-950/40 flex items-center justify-end gap-2">
                                                                                <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                                                                                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-black bg-purple-600 hover:bg-purple-700 text-white shadow-md shadow-purple-500/20 active:scale-95 transition cursor-pointer">✓ Save &amp; Apply to Cart</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
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
