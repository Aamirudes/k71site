<x-layouts.vendor title="Operations Settings">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Operations & Payments</h1>
            <p class="mt-1 text-sm text-gray-500">Configure how you ship orders and receive payments via UPI.</p>
        </div>
        <div>
            <button type="submit" form="settings-form" class="w-full sm:w-auto bg-brand-sage text-white px-5 py-2.5 rounded-xl font-bold shadow-sm hover:bg-brand-sage/90 transition-colors flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Save Operations</span>
            </button>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form id="settings-form" action="{{ route('vendor.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            @if($vendor->isFood())
            <!-- Food & Cloud Kitchen Settings -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🍔</span>
                        <h3 class="text-lg font-bold text-gray-900">Cloud Kitchen & Restaurant Operations</h3>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Configure kitchen timings, pure veg compliance, and doorstep food delivery rates.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Dietary / Pure Veg compliance -->
                    <div class="sm:col-span-2 p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-bold text-emerald-950 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-600"></span> 100% Pure Vegetarian Kitchen
                            </span>
                            <p class="text-xs text-emerald-800 mt-1">Enable this if your kitchen never prepares non-vegetarian food. Adds verified Pure Veg badge across all your dishes.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_pure_veg" value="1" {{ old('is_pure_veg', $settings->is_pure_veg) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Kitchen Timings -->
                    <div>
                        <label for="opening_time" class="block text-sm font-semibold text-gray-700">Kitchen Opening Time</label>
                        <input type="time" name="opening_time" id="opening_time" value="{{ old('opening_time', $settings->opening_time ?? '11:00') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Time your kitchen accepts first delivery order.</p>
                    </div>
                    <div>
                        <label for="closing_time" class="block text-sm font-semibold text-gray-700">Kitchen Closing Time</label>
                        <input type="time" name="closing_time" id="closing_time" value="{{ old('closing_time', $settings->closing_time ?? '23:30') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Last order acceptance time.</p>
                    </div>

                    <!-- Prep Time & Delivery Fee -->
                    <div>
                        <label for="avg_prep_time_mins" class="block text-sm font-semibold text-gray-700">Average Preparation Time (Minutes)</label>
                        <input type="number" min="5" max="180" name="avg_prep_time_mins" id="avg_prep_time_mins" value="{{ old('avg_prep_time_mins', $settings->avg_prep_time_mins ?? 25) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Displayed to customer as "Delivered hot in X mins".</p>
                    </div>
                    <div>
                        <label for="food_delivery_fee" class="block text-sm font-semibold text-gray-700">Local Food Delivery Fee (₹)</label>
                        <input type="number" step="1" name="food_delivery_fee" id="food_delivery_fee" value="{{ old('food_delivery_fee', $settings->food_delivery_fee ? $settings->food_delivery_fee / 100 : 40) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Delivery fee charged per food order (e.g. ₹40).</p>
                    </div>

                    <!-- Minimum Order & Cuisines -->
                    <div>
                        <label for="min_food_order_amount" class="block text-sm font-semibold text-gray-700">Minimum Order Value for Delivery (₹)</label>
                        <input type="number" step="1" name="min_food_order_amount" id="min_food_order_amount" value="{{ old('min_food_order_amount', $settings->min_food_order_amount ? $settings->min_food_order_amount / 100 : 149) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Minimum food subtotal required to checkout.</p>
                    </div>
                    <div>
                        <label for="cuisine_types" class="block text-sm font-semibold text-gray-700">Cuisine Tags (Comma-separated)</label>
                        @php
                            $cTypes = $settings->cuisine_types;
                            $cStr = is_array($cTypes) ? implode(', ', $cTypes) : ($cTypes ?: 'Biryani, Mughlai, Awadhi');
                        @endphp
                        <input type="text" name="cuisine_types" id="cuisine_types" value="{{ old('cuisine_types', $cStr) }}" placeholder="e.g. Biryani, North Indian, Desserts" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Help customers find you in food search.</p>
                    </div>
                </div>
            </div>
            @elseif($vendor->isServices())
            <!-- Services Provider Settings -->
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🔧</span>
                        <h3 class="text-lg font-bold text-gray-900">Service Area & Appointment Settings</h3>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Control your serviceable radius and working hours for client bookings.</p>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="service_radius_km" class="block text-sm font-semibold text-gray-700">Doorstep Service Radius (Kilometers)</label>
                        <input type="number" min="1" max="100" name="service_radius_km" id="service_radius_km" value="{{ old('service_radius_km', $settings->service_radius_km ?? 15) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Distance you are willing to travel for client appointments.</p>
                    </div>
                    <div>
                        <label for="service_experience_years" class="block text-sm font-semibold text-gray-700">Professional Experience (Years)</label>
                        <input type="number" min="0" max="50" name="service_experience_years" id="service_experience_years" value="{{ old('service_experience_years', $settings->service_experience_years ?? 5) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Displayed on your profile as trusted badge.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="service_working_hours" class="block text-sm font-semibold text-gray-700">Working Days & Availability Hours</label>
                        <input type="text" name="service_working_hours" id="service_working_hours" value="{{ old('service_working_hours', $settings->service_working_hours ?? 'Mon-Sat: 9:00 AM - 7:00 PM') }}" placeholder="e.g. Mon-Sat: 9:00 AM - 7:00 PM" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                    </div>
                </div>
            </div>
            @else
            <!-- Standard Clothing / Goods Courier Shipping -->
            <div class="space-y-4" x-data="{ shippingMode: '{{ old('shipping_type', $settings->shipping_type) }}' }">
                <div class="border-b border-gray-200 pb-2">
                    <h3 class="text-lg font-medium text-gray-900">Courier Shipping Policies</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Control how delivery fees are calculated for customers at checkout.</p>
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="shipping_type" class="block text-sm font-semibold text-gray-700">Shipping Mode</label>
                        <select id="shipping_type" name="shipping_type" x-model="shippingMode" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                            <option value="flat">Flat Rate (Charge fixed fee on all orders)</option>
                            <option value="free_above">Free Above Cart Value (Free delivery when customer spends minimum amount)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5" x-show="shippingMode === 'flat'">
                            ℹ️ <b>Flat Rate:</b> Every order gets charged the exact same delivery fee, whether they buy ₹200 or ₹5,000.
                        </p>
                        <p class="text-xs text-emerald-700 mt-1.5" x-show="shippingMode === 'free_above'">
                            🎁 <b>Free Above Threshold:</b> Offers Free Delivery to encourage customers to buy more items. If their cart is below the threshold, standard delivery fee is charged.
                        </p>
                    </div>

                    <!-- Flat Rate Delivery Fee (Mode: flat) -->
                    <div x-show="shippingMode === 'flat'">
                        <label for="shipping_flat_rate" class="block text-sm font-semibold text-gray-700">Flat Rate Delivery Fee (₹)</label>
                        <input type="number" step="0.01" name="shipping_flat_rate" id="shipping_flat_rate" :disabled="shippingMode !== 'flat'" value="{{ old('shipping_flat_rate', $settings->shipping_flat_rate ? $settings->shipping_flat_rate / 100 : 50) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">e.g. ₹50 or ₹70 added to every order.</p>
                    </div>

                    <!-- Free Above Inputs (Mode: free_above) -->
                    <div x-show="shippingMode === 'free_above'" class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6 bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                        <div>
                            <label for="shipping_free_above" class="block text-sm font-bold text-emerald-900">Free Delivery Minimum Cart Value (₹)</label>
                            <input type="number" step="0.01" name="shipping_free_above" id="shipping_free_above" :disabled="shippingMode !== 'free_above'" value="{{ old('shipping_free_above', $settings->shipping_free_above ? $settings->shipping_free_above / 100 : '') }}" placeholder="e.g. 1500 or 2000" class="mt-1 block w-full rounded-lg border-emerald-300 bg-white shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm font-semibold">
                            <p class="text-xs text-emerald-700 mt-1">Orders reaching this amount or more get <b>100% FREE Delivery</b>.</p>
                        </div>
                        <div>
                            <label for="shipping_flat_rate_fallback" class="block text-sm font-semibold text-gray-700">Delivery Fee if Below Minimum (₹)</label>
                            <input type="number" step="0.01" name="shipping_flat_rate" id="shipping_flat_rate_fallback" :disabled="shippingMode !== 'free_above'" value="{{ old('shipping_flat_rate', $settings->shipping_flat_rate ? $settings->shipping_flat_rate / 100 : 50) }}" placeholder="e.g. 50" class="mt-1 block w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                            <p class="text-xs text-gray-500 mt-1">Delivery fee charged only if the customer's order is less than the minimum above.</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="processing_days" class="block text-sm font-medium text-gray-700">Order Processing Time (Days)</label>
                        <input type="number" min="1" max="30" name="processing_days" id="processing_days" value="{{ old('processing_days', $settings->processing_days) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Days taken to pack and dispatch garments (default: 3 days).</p>
                    </div>
                    
                    <div>
                        <label for="return_window_days" class="block text-sm font-medium text-gray-700">Return Window (Days)</label>
                        <input type="number" min="0" max="30" name="return_window_days" id="return_window_days" value="{{ old('return_window_days', $settings->return_window_days) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Set to 0 for final sale (no returns allowed).</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="serviceable_pincodes" class="block text-sm font-medium text-gray-700">Serviceable Delivery Pincodes</label>
                        @php
                            $pincodeStr = '';
                            if ($settings->serviceable_pincodes) {
                                $pArr = is_array($settings->serviceable_pincodes) ? $settings->serviceable_pincodes : json_decode($settings->serviceable_pincodes, true);
                                $pincodeStr = is_array($pArr) ? implode(', ', $pArr) : '';
                            }
                        @endphp
                        <input type="text" name="serviceable_pincodes" id="serviceable_pincodes" value="{{ old('serviceable_pincodes', $pincodeStr) }}" placeholder="e.g. 110001, 110002, 560001 (Leave blank to ship Pan-India to all Indian pincodes)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for Pan-India delivery. If you only deliver locally or to specific hubs, enter comma-separated 6-digit pincodes.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Physical Courier Pickup Address (Shiprocket Multi-Vendor Origin) -->
            <div id="pickup-address" class="space-y-4 pt-4 border-t border-gray-200">
                <div class="flex items-start justify-between flex-wrap gap-2 pb-2 border-b border-gray-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📍</span>
                            <h3 class="text-lg font-bold text-gray-900">Physical Store & Courier Pickup Address</h3>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Courier partners (Delhivery, BlueDart, Xpressbees, Shadowfax) will dispatch delivery riders to this physical shop location to pick up packages whenever an order is booked.
                        </p>
                    </div>
                    @php
                        $pAddr = is_array($vendor->pickup_address) ? $vendor->pickup_address : (json_decode($vendor->pickup_address, true) ?: []);
                        $hasCompleteAddress = !empty($pAddr['line_1']) && !empty($pAddr['pincode']);
                    @endphp
                    <div>
                        @if($hasCompleteAddress)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Registered Origin: BUY_V_{{ $vendor->id }} ({{ $pAddr['pincode'] }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Action Required: Add Shop Address
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-3">
                    <span class="text-base shrink-0">🚚</span>
                    <div class="space-y-1">
                        <strong class="font-bold">How Multi-Vendor Pickup Works:</strong>
                        <p class="leading-relaxed text-amber-800">
                            When customers checkout on your boutique, shipping rates are dynamically calculated based on the distance from <b>your shop's pincode</b> to the buyer's delivery pincode. When you click <b>"1-Click Ship Order"</b>, Shiprocket automatically books an assigned pickup rider directly to the shop address specified below.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="pickup_line_1" class="block text-xs font-bold text-gray-700 mb-1">
                            Shop / Building / Street Address <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pickup_address[line_1]" id="pickup_line_1" 
                               value="{{ old('pickup_address.line_1', $pAddr['line_1'] ?? '') }}" 
                               required
                               placeholder="Shop No., Market / Complex Name, Street / Road Name" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                        @error('pickup_address.line_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="pickup_line_2" class="block text-xs font-bold text-gray-700 mb-1">
                            Landmark / Area / Colony (Optional)
                        </label>
                        <input type="text" name="pickup_address[line_2]" id="pickup_line_2" 
                               value="{{ old('pickup_address.line_2', $pAddr['line_2'] ?? '') }}" 
                               placeholder="e.g. Near Clock Tower, Johari Bazaar, Opposite Metro Pillar 42" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                    </div>

                    <div>
                        <label for="pickup_city" class="block text-xs font-bold text-gray-700 mb-1">
                            City / District <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pickup_address[city]" id="pickup_city" 
                               value="{{ old('pickup_address.city', $pAddr['city'] ?? '') }}" 
                               required
                               placeholder="e.g. Jaipur, Surat, Varanasi, Mumbai" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                        @error('pickup_address.city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pickup_state" class="block text-xs font-bold text-gray-700 mb-1">
                            State <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pickup_address[state]" id="pickup_state" 
                               value="{{ old('pickup_address.state', $pAddr['state'] ?? '') }}" 
                               required
                               placeholder="e.g. Rajasthan, Gujarat, Uttar Pradesh, Maharashtra" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                        @error('pickup_address.state') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pickup_pincode" class="block text-xs font-bold text-gray-700 mb-1">
                            6-Digit Pickup Pincode <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="pickup_address[pincode]" id="pickup_pincode" 
                               value="{{ old('pickup_address.pincode', $pAddr['pincode'] ?? '') }}" 
                               required
                               pattern="[0-9]{6}"
                               maxlength="6"
                               placeholder="e.g. 302001" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-mono font-bold">
                        <p class="text-[11px] text-gray-400 mt-1">Used for courier serviceability and distance-based rate calculation.</p>
                        @error('pickup_address.pincode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pickup_contact_name" class="block text-xs font-bold text-gray-700 mb-1">
                            Pickup Point Contact Person
                        </label>
                        <input type="text" name="pickup_address[contact_name]" id="pickup_contact_name" 
                               value="{{ old('pickup_address.contact_name', $pAddr['contact_name'] ?? $vendor->user->name ?? '') }}" 
                               placeholder="Person handing parcel to courier rider" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="pickup_contact_phone" class="block text-xs font-bold text-gray-700 mb-1">
                            Pickup Contact Phone Number (10 Digits) <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="pickup_address[contact_phone]" id="pickup_contact_phone" 
                               value="{{ old('pickup_address.contact_phone', $pAddr['contact_phone'] ?? $vendor->contact_phone ?? $vendor->user->phone ?? '') }}" 
                               required
                               maxlength="15"
                               placeholder="e.g. 9876543210" 
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                        <p class="text-[11px] text-gray-400 mt-1">Courier delivery rider will call this number when arriving at your shop for package pickup.</p>
                    </div>
                </div>
            </div>

            <!-- Customer Payment Options -->
            @php
                $upiActive = old('upi_enabled', $settings->upi_enabled ?? true);
                $codActive = old('cod_enabled', $settings->cod_enabled ?? false);
                $initialMode = 'both';
                if ($upiActive && !$codActive) {
                    $initialMode = 'upi_only';
                } elseif (!$upiActive && $codActive) {
                    $initialMode = 'cod_only';
                }
            @endphp
            <div class="space-y-4" x-data="{ paymentMode: '{{ $initialMode }}' }">
                <div class="border-b border-gray-200 pb-2">
                    <h3 class="text-lg font-medium text-gray-900">Customer Payment Options</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Control which payment methods customers can use when buying from your boutique.</p>
                </div>

                @error('payment_mode')
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-semibold flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Both UPI & COD -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_mode" value="both" x-model="paymentMode" class="peer sr-only">
                        <div class="p-4 rounded-xl border-2 transition-all flex flex-col gap-2 peer-checked:border-brand-sage peer-checked:bg-emerald-50/40 border-gray-200 hover:border-gray-300">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-900">Both UPI & COD</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Recommended</span>
                            </div>
                            <p class="text-[11px] text-gray-500 leading-relaxed">
                                Accept prepaid UPI payments and Cash on Delivery (up to ₹5,000 threshold). Maximizes conversion.
                            </p>
                        </div>
                    </label>

                    <!-- UPI Only -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_mode" value="upi_only" x-model="paymentMode" class="peer sr-only">
                        <div class="p-4 rounded-xl border-2 transition-all flex flex-col gap-2 peer-checked:border-brand-sage peer-checked:bg-emerald-50/40 border-gray-200 hover:border-gray-300">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-900">Prepaid UPI Only</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Zero RTO</span>
                            </div>
                            <p class="text-[11px] text-gray-500 leading-relaxed">
                                Customers must pay before dispatch via PhonePe/UPI. Eliminates fake COD orders and return-to-origin loss.
                            </p>
                        </div>
                    </label>

                    <!-- COD Only -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_mode" value="cod_only" x-model="paymentMode" class="peer sr-only">
                        <div class="p-4 rounded-xl border-2 transition-all flex flex-col gap-2 peer-checked:border-brand-sage peer-checked:bg-emerald-50/40 border-gray-200 hover:border-gray-300">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-900">Cash on Delivery Only</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Cash</span>
                            </div>
                            <p class="text-[11px] text-gray-500 leading-relaxed">
                                Customers pay cash to the courier delivery agent upon receiving the package.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Payments & Instant Payouts -->
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-2 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Instant Vendor Payout & Settlement Account</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Your 95% net earnings are transferred directly to this UPI ID in 2 seconds when you click "Accept Order".</p>
                    </div>
                    @if(!empty($settings->upi_id))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Verified Payout Target
                        </span>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="upi_id" class="block text-sm font-semibold text-gray-700">UPI ID / VPA (Google Pay / PhonePe / Paytm / BHIM) <span class="text-red-500">*</span></label>
                        <input type="text" name="upi_id" id="upi_id" value="{{ old('upi_id', $settings->upi_id) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm font-mono font-bold" placeholder="yourname@okhdfcbank or 9876543210@paytm">
                        <p class="text-xs text-gray-500 mt-1">This is where your 95% cash payout is instantly sent upon accepting each order.</p>
                    </div>
                    
                    <div>
                        <label for="upi_payee_name" class="block text-sm font-semibold text-gray-700">Account Holder Name (As per Bank/UPI)</label>
                        <input type="text" name="upi_payee_name" id="upi_payee_name" value="{{ old('upi_payee_name', $settings->upi_payee_name) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm" placeholder="e.g. Rajesh Kumar">
                        <p class="text-xs text-gray-500 mt-1">Name registered with your UPI bank account.</p>
                    </div>

                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name (Optional)</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $settings->bank_name) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm" placeholder="e.g. HDFC Bank, ICICI Bank, State Bank of India">
                    </div>

                    <div>
                        <label for="bank_ifsc" class="block text-sm font-medium text-gray-700">Bank IFSC Code (Optional)</label>
                        <input type="text" name="bank_ifsc" id="bank_ifsc" value="{{ old('bank_ifsc', $settings->bank_ifsc) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm uppercase font-mono" placeholder="e.g. HDFC0001234">
                    </div>
                    
                    <div class="sm:col-span-2 bg-emerald-50/70 p-4 rounded-xl border border-emerald-200/80">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <div class="text-xs text-emerald-900 leading-relaxed">
                                <strong>How Instant Payouts Work on K71:</strong> 
                                When a customer orders, funds clear into the platform account. As soon as settlement completes, the <strong>"Accept Order"</strong> button activates on your dashboard. 
                                Clicking it fires your <strong>95% net payout (₹)</strong> into the UPI ID above in <strong>2 to 3 seconds</strong>, so you receive your money before packing the box!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Policy -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Cancellation Policy</h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input id="allow_customer_cancellation" name="allow_customer_cancellation" type="checkbox" value="1" {{ old('allow_customer_cancellation', $settings->allow_customer_cancellation ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-sage focus:ring-brand-sage">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="allow_customer_cancellation" class="font-medium text-gray-700">Allow Customers to Cancel Orders</label>
                                <p class="text-gray-500">When enabled, customers can self-cancel their orders before they are packed or shipped.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="cancellation_window_hours" class="block text-sm font-medium text-gray-700">Cancellation Window (Hours)</label>
                        <input type="number" min="1" max="168" name="cancellation_window_hours" id="cancellation_window_hours" value="{{ old('cancellation_window_hours', $settings->cancellation_window_hours ?? 24) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Maximum hours after placing an order that a customer can cancel (e.g. 24 hours).</p>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-4 z-30 sm:static bg-white/95 sm:bg-transparent backdrop-blur-md p-4 sm:p-0 rounded-2xl sm:rounded-none border sm:border-0 border-gray-200 shadow-xl sm:shadow-none flex justify-end mt-8">
                <button type="submit" class="w-full sm:w-auto bg-brand-sage text-white px-8 py-3 rounded-xl font-bold shadow-md hover:bg-brand-sage/90 transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save Operations Settings</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.vendor>
