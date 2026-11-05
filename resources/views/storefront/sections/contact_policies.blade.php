@php
    $title = $section['title'] ?? 'Studio Client Services & Policies';
    $showWhatsapp = $section['show_whatsapp'] ?? true;
    $showPickup = $section['show_pickup'] ?? true;
    
    $storeName = $config['brand']['store_name'] ?? $vendor->shop_name;
    $phone = $config['brand']['phone'] ?? $vendor->contact_phone;
    $email = $config['brand']['email'] ?? $vendor->contact_email;
    $waPhone = $config['brand']['whatsapp'] ?? $phone;
    $waClean = $waPhone ? preg_replace('/[^0-9]/', '', $waPhone) : null;
    if ($waClean && strlen($waClean) === 10) { $waClean = '91' . $waClean; }
    $waUrl = $waClean ? "https://wa.me/{$waClean}?text=" . rawurlencode("Namaste {$storeName}! I have an inquiry about an item on your K71 store.") : null;

    $pickup = $vendor->pickup_address ?: [];
    $hasAddress = !empty($pickup['city']) || !empty($vendor->city);
    $policies = $vendor->store_policies;
@endphp

<section id="contact" 
         data-section-id="{{ $section['id'] ?? 'contact' }}"
         class="py-12 sm:py-16 border-b border-stone-200/60"
         style="background-color: var(--sf-bg, #fafaf9);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-2xl mb-8 sm:mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 font-heading">
                {{ $title }}
            </h2>
            <p class="text-xs sm:text-sm text-stone-500 mt-1">
                Have questions regarding sizing, custom stitching, or bulk dispatches? We are here to assist.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Direct Assistance Card -->
            <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-4">
                        💬
                    </div>
                    <h3 class="text-base font-bold text-stone-900 font-heading">Direct Stylist Desk</h3>
                    <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                        Connect with our studio directly on WhatsApp for real-time fabric swatches, sizing help, and live tracking.
                    </p>
                </div>

                @if($showWhatsapp && $waUrl)
                    <div class="mt-6">
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                           class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-sm">
                            <span>Message on WhatsApp</span>
                            <span>→</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Studio Location & Dispatch Desk -->
            <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-4">
                        📍
                    </div>
                    <h3 class="text-base font-bold text-stone-900 font-heading">Workshop Location</h3>
                    <div class="text-xs text-stone-600 mt-2 space-y-1">
                        @if(!empty($pickup['line_1']))
                            <div>{{ $pickup['line_1'] }}</div>
                        @endif
                        @if(!empty($pickup['line_2']))
                            <div>{{ $pickup['line_2'] }}</div>
                        @endif
                        <div>
                            {{ $pickup['city'] ?? $vendor->city ?? 'Direct Studio' }}
                            @if(!empty($pickup['pincode']))
                                - {{ $pickup['pincode'] }}
                            @endif
                        </div>
                        <div class="text-stone-400 font-medium pt-1">
                            {{ $pickup['state'] ?? $vendor->state ?? 'India' }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between text-xs text-stone-500">
                    <span>Direct Shipping</span>
                    <span class="font-bold text-stone-800">Pan-India Express</span>
                </div>
            </div>

            <!-- Store Policies & Guarantee -->
            <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-4">
                        🛡️
                    </div>
                    <h3 class="text-base font-bold text-stone-900 font-heading">Customer Policies</h3>
                    <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                        @if(!empty($policies))
                            {{ Str::limit($policies, 160) }}
                        @else
                            7-day size exchange guarantee on unworn garments with intact tags. Tracked insured transit with real-time SMS updates.
                        @endif
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center gap-2 text-[11px] text-stone-500">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <span>Verified K71 Seller Protection</span>
                </div>
            </div>

        </div>

    </div>
</section>

