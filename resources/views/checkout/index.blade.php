<x-layouts.public title="Checkout - {{ $vendor->shop_name }}" :fullWidth="true">
    <!-- Grail Obsidian Checkout Hero Stage -->
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-10 sm:pt-9 sm:pb-12">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <span class="grail-eyebrow block mb-2">Encrypted Checkout &bull; Verified Store</span>
                    <h1 class="grail-display text-[clamp(2rem,4vw,3rem)] font-bold text-white tracking-tight">Checkout</h1>
                    <p class="text-sm text-white/65 mt-1">Completing your order with <strong class="text-[#ECD2F3]">{{ $vendor->shop_name }}</strong></p>
                </div>
                <div class="flex items-center gap-2 text-[11px] font-semibold">
                    <span class="px-3 py-1 rounded-full bg-[#ECD2F3] text-[#1A1018] font-bold">1. Address</span>
                    <span class="text-white/30">&rarr;</span>
                    <span class="px-3 py-1 rounded-full bg-white/10 text-white/80 border border-white/15">2. Payment</span>
                    <span class="text-white/30">&rarr;</span>
                    <span class="px-3 py-1 rounded-full bg-white/10 text-white/60 border border-white/15">3. Confirm</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Grail Stone Sheet -->
    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($isFoodOrder ?? false)
        @php
            $prepMins = (int) ($settings?->avg_prep_time_mins ?? 25);
        @endphp
        <div class="mb-6 space-y-3">
            <div class="p-4 rounded-2xl bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-orange-500/5 border border-orange-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🍲</span>
                    <div>
                        <h3 class="text-sm font-black text-stone-900">Kitchen Order Checkout</h3>
                        <p class="text-xs text-stone-600">
                            Freshly prepared &amp; packed by <strong class="text-[#ea580c]">{{ $vendor->shop_name }}</strong>
                            @if($settings?->is_pure_veg)
                                &bull; <span class="font-bold text-emerald-700">100% Pure Veg</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-orange-100 text-[#ea580c] text-[11px] font-bold">
                        ⏱ ~{{ $prepMins }} mins prep
                    </span>
                    @if($vendor->isFssaiVerified())
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                            ✓ FSSAI Verified &bull; Lic {{ $settings?->fssai_license_number }}
                        </span>
                    @endif
                </div>
            </div>

            @if(!empty($operationalStatus) && !($operationalStatus['accepting_orders'] ?? true))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-2.5">
                    <span class="text-lg">⏸️</span>
                    <span>{{ $operationalStatus['reason'] ?: 'This kitchen is currently not accepting new orders.' }}</span>
                </div>
            @endif

            @if(($minFoodOrderAmount ?? 0) > 0 && $subtotal < $minFoodOrderAmount)
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm font-semibold flex items-center justify-between gap-3">
                    <span>Minimum order value for {{ $vendor->shop_name }} is ₹{{ number_format($minFoodOrderAmount / 100, 0) }}. Add ₹{{ number_format(($minFoodOrderAmount - $subtotal) / 100, 2) }} more to place an order.</span>
                    <a href="{{ route('food.restaurant', $vendor->slug) }}" class="shrink-0 px-3 py-1.5 rounded-xl bg-amber-900 text-white text-xs font-bold">Add Items</a>
                </div>
            @endif
        </div>
    @endif

    @php
        $defaultSavedAddr = ($savedAddresses ?? collect())->firstWhere('is_default', true) ?? ($savedAddresses ?? collect())->first();
        $defaultOrderType = old('order_type', ($isFoodOrder ?? false) ? (($foodDeliveryMode ?? 'both') === 'takeaway' ? 'food_takeaway' : 'food_delivery') : 'standard');
        $initDeliveryFeePaise = ($isFoodOrder ?? false) && $defaultOrderType === 'food_takeaway' ? 0 : (int) $shipping;
        $initDisplayTotalPaise = max(0, (int) $subtotal - (int) $discount) + $initDeliveryFeePaise + (int) ($packagingFee ?? 0);
        $savedAddressesPayload = ($savedAddresses ?? collect())->map(fn ($addr) => [
            'id' => $addr->id,
            'label' => $addr->label,
            'full_name' => $addr->full_name,
            'phone' => $addr->phone,
            'line_1' => $addr->line_1,
            'line_2' => $addr->line_2,
            'city' => $addr->city,
            'state' => $addr->state,
            'pincode' => $addr->pincode,
            'is_default' => (bool) $addr->is_default,
        ])->values();
    @endphp

    <form action="{{ route('checkout.process', $vendor->id) }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 xl:gap-x-16" 
             x-data="{ 
                 orderType: '{{ $defaultOrderType }}',
                 paymentMethod: '{{ old('payment_method', $defaultPaymentMethod) }}',
                 selectedAddressId: '{{ old('selected_address_id', $defaultSavedAddr?->id ?? '') }}',
                 applySavedAddress(addr) {
                     this.selectedAddressId = String(addr.id);
                     const fillField = (id, val) => {
                         const el = document.getElementById(id);
                         if (el) el.value = val || '';
                     };
                     fillField('name', addr.full_name);
                     fillField('phone', addr.phone);
                     fillField('address_line_1', addr.line_1);
                     fillField('city', addr.city);
                     fillField('state', addr.state);
                     fillField('pincode', addr.pincode);
                     @if(!($isFoodOrder ?? false))
                     if (addr.pincode && String(addr.pincode).trim().length === 6 && window.handlePincodeChange) {
                         window.handlePincodeChange(String(addr.pincode));
                     }
                     @endif
                 },
                 get checkoutBtnText() {
                     if (this.paymentMethod === 'phonepe') return 'Pay Securely via PhonePe / UPI →';
                     if (this.paymentMethod === 'upi') return 'Continue to Store UPI / QR →';
                     if (this.paymentMethod === 'cod') return 'Place Order (Cash on Delivery) →';
                     return 'Place Order →';
                 }
             }"
             x-init="
                 $watch('orderType', (val) => window.setCheckoutOrderType && window.setCheckoutOrderType(val));
                 window.setCheckoutOrderType && window.setCheckoutOrderType(orderType);
             ">
            <input type="hidden" name="selected_address_id" :value="selectedAddressId">
            
            <!-- Left Column: Shipping & Payment -->
            <div class="lg:col-span-7 space-y-10">
                
                @if($isFoodOrder ?? false)
                    @php
                        $modeSetting = $foodDeliveryMode ?? 'both';
                    @endphp
                    <!-- Delivery vs Takeaway Option -->
                    <section class="bg-white p-5 rounded-[24px] border border-black/8 shadow-xs">
                        <h2 class="text-xs font-black text-[#1A1018] uppercase tracking-wider mb-3">Order Fulfillment Mode</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if($modeSetting !== 'takeaway')
                                <label class="p-3.5 rounded-2xl border-2 transition cursor-pointer flex flex-col items-center text-center gap-1.5"
                                       :class="orderType === 'food_delivery' ? 'border-[#ea580c] bg-orange-50/40 text-stone-950 font-black' : 'border-black/10 bg-white text-stone-600 hover:border-black/20'">
                                    <input type="radio" name="order_type" value="food_delivery" x-model="orderType" class="sr-only">
                                    <span class="text-xl">🛵</span>
                                    <span class="text-xs font-black">Doorstep Delivery</span>
                                    <span class="text-[10px] text-stone-500 font-medium">
                                        {{ $shipping > 0 ? ('Delivery fee ₹' . number_format($shipping / 100, 0)) : 'Free delivery unlocked' }}
                                    </span>
                                </label>
                            @else
                                <div class="p-3.5 rounded-2xl border border-dashed border-stone-200 bg-stone-50 text-stone-400 flex flex-col items-center text-center gap-1 opacity-75">
                                    <span class="text-xl">🛵</span>
                                    <span class="text-xs font-bold">Doorstep Delivery Unavailable</span>
                                    <span class="text-[10px]">Kitchen accepts pickup only</span>
                                </div>
                            @endif

                            @if($modeSetting !== 'delivery')
                                <label class="p-3.5 rounded-2xl border-2 transition cursor-pointer flex flex-col items-center text-center gap-1.5"
                                       :class="orderType === 'food_takeaway' ? 'border-[#1A1018] bg-[#FAF8F5] text-[#1A1018] font-black' : 'border-black/10 bg-white text-stone-600 hover:border-black/20'">
                                    <input type="radio" name="order_type" value="food_takeaway" x-model="orderType" class="sr-only">
                                    <span class="text-xl">🥡</span>
                                    <span class="text-xs font-black">Direct Takeaway</span>
                                    <span class="text-[10px] text-emerald-600 font-bold">₹0 Delivery Fee &bull; Self Pickup</span>
                                </label>
                            @else
                                <div class="p-3.5 rounded-2xl border border-dashed border-stone-200 bg-stone-50 text-stone-400 flex flex-col items-center text-center gap-1 opacity-75">
                                    <span class="text-xl">🥡</span>
                                    <span class="text-xs font-bold">Self-Pickup Unavailable</span>
                                    <span class="text-[10px]">Delivery only from this kitchen</span>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                <!-- Shipping Details -->
                <section>
                    @guest
                        <div class="mb-4 p-4 rounded-2xl bg-[#161616] text-[#EFE9F2] border border-white/12 text-xs flex items-center gap-3 shadow-sm">
                            <span class="w-9 h-9 rounded-full bg-[#ECD2F3]/15 text-[#ECD2F3] flex items-center justify-center text-lg shrink-0">⚡</span>
                            <div>
                                <span class="font-bold text-[#ECD2F3] block">Instant Guest Checkout</span>
                                <span class="text-white/70">No account or password needed. Simply fill your contact and delivery address below to place your order!</span>
                            </div>
                        </div>
                    @endguest

                    @auth
                        @if(($savedAddresses ?? collect())->isNotEmpty())
                            <div class="mb-5 bg-white p-5 rounded-[24px] border border-black/8 shadow-xs">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <h3 class="text-xs font-black uppercase tracking-wider text-[#1A1018]">Saved Addresses</h3>
                                    <a href="{{ route('addresses.index') }}" class="text-xs font-bold text-[#623562] hover:underline">Manage Addresses &rarr;</a>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($savedAddressesPayload as $addrItem)
                                        <button type="button"
                                                @click='applySavedAddress(@json($addrItem))'
                                                :class="selectedAddressId === '{{ $addrItem['id'] }}' ? 'border-[#1A1018] bg-[#FAF8F5] ring-1 ring-[#1A1018]' : 'border-black/10 bg-white hover:border-black/25'"
                                                class="text-left p-3.5 rounded-2xl border transition cursor-pointer">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="text-xs font-black text-[#1A1018] uppercase">{{ $addrItem['label'] ?: 'Saved' }}</span>
                                                @if($addrItem['is_default'])
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Default</span>
                                                @endif
                                            </div>
                                            <p class="text-xs font-semibold text-[#1A1018] truncate">{{ $addrItem['full_name'] }} &bull; {{ $addrItem['phone'] }}</p>
                                            <p class="text-[11px] text-[#7A6B78] line-clamp-2 mt-0.5">{{ $addrItem['line_1'] }}, {{ $addrItem['city'] }}, {{ $addrItem['state'] }} - {{ $addrItem['pincode'] }}</p>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endauth

                    <h2 class="grail-display text-xl font-bold text-[#1A1018] mb-4" x-text="orderType === 'food_takeaway' ? 'Customer Contact &amp; Pickup Details' : 'Delivery Address'">Delivery Address</h2>
                    <div class="bg-white p-6 rounded-[24px] border border-black/8 shadow-[0_12px_36px_rgba(26,16,24,0.05)] space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-[#1A1018] mb-1">Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $defaultSavedAddr?->full_name ?? auth()->user()?->name ?? '') }}" required placeholder="e.g. Rahul Sharma" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm">
                            </div>

                            <div class="sm:col-span-2" 
                                 x-data="{
                                    email: '{{ old('email', auth()->user()?->email ?? '') }}',
                                    otpCode: '',
                                    otpSent: {{ session('requires_checkout_otp') ? 'true' : 'false' }},
                                    verified: {{ session('checkout_verified_email') ? 'true' : (auth()->check() && auth()->user()->email_verified_at ? 'true' : 'false') }},
                                    loading: false,
                                    msg: '{{ session('success') ?? '' }}',
                                    errorMsg: '{{ $errors->first('email_otp') ?? '' }}',
                                    cooldown: 0,
                                    sendOtp() {
                                        if (!this.email || !this.email.includes('@')) {
                                            this.errorMsg = 'Please enter a valid email address first.';
                                            return;
                                        }
                                        this.loading = true;
                                        this.errorMsg = '';
                                        fetch('{{ route('checkout.otp.send') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ email: this.email })
                                        })
                                        .then(r => r.json())
                                        .then(data => {
                                            this.loading = false;
                                            if (data.success) {
                                                this.otpSent = true;
                                                this.msg = data.message;
                                                this.cooldown = 45;
                                                const timer = setInterval(() => {
                                                    this.cooldown--;
                                                    if (this.cooldown <= 0) clearInterval(timer);
                                                }, 1000);
                                            } else {
                                                this.errorMsg = data.message || 'Failed to send OTP.';
                                            }
                                        })
                                        .catch(() => {
                                            this.loading = false;
                                            this.errorMsg = 'Network error while dispatching OTP.';
                                        });
                                    },
                                    verifyOtp() {
                                        if (this.otpCode.length !== 6) {
                                            this.errorMsg = 'Please enter the 6-digit code sent to your email.';
                                            return;
                                        }
                                        this.loading = true;
                                        this.errorMsg = '';
                                        fetch('{{ route('checkout.otp.verify') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ email: this.email, code: this.otpCode })
                                        })
                                        .then(r => r.json())
                                        .then(data => {
                                            this.loading = false;
                                            if (data.success) {
                                                this.verified = true;
                                                this.msg = '✓ Email confirmed genuine!';
                                                this.errorMsg = '';
                                            } else {
                                                this.errorMsg = data.message || 'Invalid OTP code.';
                                            }
                                        })
                                        .catch(() => {
                                            this.loading = false;
                                            this.errorMsg = 'Network error while verifying OTP.';
                                        });
                                    }
                                 }">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="email" class="block text-sm font-medium text-[#1A1018]">
                                        Email Address <span class="text-rose-500">*</span>
                                        <span class="text-[11px] text-[#7A6B78] font-normal">(for 2-step verification &amp; order tracking)</span>
                                    </label>
                                    <template x-if="verified">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                            ✓ Email Confirmed
                                        </span>
                                    </template>
                                </div>
                                <div class="flex gap-2">
                                    <input type="email" id="email" name="email" x-model="email" required
                                           :readonly="verified"
                                           placeholder="e.g. rahul@example.com" 
                                           class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm">
                                    @guest
                                    <button type="button" 
                                            x-show="!verified"
                                            @click="sendOtp()"
                                            :disabled="loading || cooldown > 0"
                                            class="px-4 py-2 bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold rounded-xl shrink-0 transition active:scale-95 disabled:opacity-50 cursor-pointer shadow-xs">
                                        <span x-show="!loading && cooldown === 0" x-text="otpSent ? 'Resend OTP' : 'Verify Email (OTP)'">Verify Email (OTP)</span>
                                        <span x-show="cooldown > 0" x-text="'Wait ' + cooldown + 's'"></span>
                                        <span x-show="loading">Sending...</span>
                                    </button>
                                    @endguest
                                </div>

                                <!-- Inline OTP Input Box for Guest Checkout -->
                                @guest
                                <div x-show="otpSent && !verified" class="mt-3 p-3.5 rounded-xl bg-[#FAFAF8] border border-black/10 space-y-2">
                                    <p class="text-xs text-[#1A1018] font-medium">
                                        A 6-digit confirmation code was sent to <strong x-text="email"></strong>. Please enter it below:
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="email_otp" x-model="otpCode" maxlength="6" pattern="[0-9]{6}"
                                               placeholder="6-digit OTP" 
                                               class="grail-input-light w-36 rounded-xl px-3 py-2 text-sm font-mono tracking-widest text-center">
                                        <button type="button" 
                                                @click="verifyOtp()"
                                                :disabled="loading"
                                                class="px-4 py-2 bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold rounded-xl transition active:scale-95 cursor-pointer">
                                            Confirm Code
                                        </button>
                                    </div>
                                </div>
                                @endguest

                                <p x-show="msg" x-text="msg" class="text-xs text-emerald-600 mt-1 font-medium"></p>
                                <p x-show="errorMsg" x-text="errorMsg" class="text-xs text-rose-600 mt-1 font-medium"></p>
                            </div>

                            <div class="md:col-span-2" x-show="orderType !== 'food_takeaway'">
                                <label for="address_line_1" class="block text-sm font-medium text-[#1A1018] mb-1">
                                    {{ ($isFoodOrder ?? false) ? 'Flat / House No. / Building / Landmark' : 'Address' }}
                                </label>
                                <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $defaultSavedAddr?->line_1 ?? '') }}" :required="orderType !== 'food_takeaway'" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm" placeholder="House/Flat No., Street, Landmark">
                            </div>
                            
                            <div x-show="orderType !== 'food_takeaway'">
                                <label for="city" class="block text-sm font-medium text-[#1A1018] mb-1">City</label>
                                <input type="text" id="city" name="city" value="{{ old('city', $defaultSavedAddr?->city ?? (($isFoodOrder ?? false) ? ($selectedLocation['city'] ?? '') : '')) }}" :required="orderType !== 'food_takeaway'" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm">
                            </div>
                            
                            <div x-show="orderType !== 'food_takeaway'">
                                <label for="state" class="block text-sm font-medium text-[#1A1018] mb-1">State</label>
                                <input type="text" id="state" name="state" value="{{ old('state', $defaultSavedAddr?->state ?? '') }}" :required="orderType !== 'food_takeaway'" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm">
                            </div>
                            
                            <div x-show="orderType !== 'food_takeaway'">
                                <label for="pincode" class="block text-sm font-medium text-[#1A1018] mb-1">Pincode</label>
                                <div class="relative">
                                    <input type="text" id="pincode" name="pincode" value="{{ old('pincode', $defaultSavedAddr?->pincode ?? (($isFoodOrder ?? false) ? ($selectedLocation['pincode'] ?? '') : ($cachedShipping['pincode'] ?? ''))) }}" :required="orderType !== 'food_takeaway'" pattern="[0-9]{6}" maxlength="6" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm pr-9" placeholder="6-digit pincode" @if(!($isFoodOrder ?? false)) oninput="window.handlePincodeChange(this.value)" @endif>
                                    <div id="pincode-spinner" class="hidden absolute right-3 top-3">
                                        <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p id="pincode-msg" class="text-xs text-emerald-600 mt-1 font-medium {{ (!($isFoodOrder ?? false) && !empty($cachedShipping['courier_name'])) ? '' : 'hidden' }}">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>
                                    <span>{{ $cachedShipping['courier_name'] ?? '' }} (Est. {{ $cachedShipping['est_days'] ?? 3 }} Days)</span>
                                </p>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-[#1A1018] mb-1">Mobile Number <span class="text-rose-500">*</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $defaultSavedAddr?->phone ?? auth()->user()?->phone ?? '') }}" required placeholder="10-digit mobile number" minlength="10" maxlength="15" class="grail-input-light w-full rounded-xl px-3.5 py-2.5 sm:text-sm">
                            </div>

                            @auth
                                <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-black/6">
                                    <label class="inline-flex items-center gap-2 text-xs font-medium text-[#1A1018] cursor-pointer">
                                        <input type="checkbox" name="save_address" value="1" {{ old('save_address') ? 'checked' : '' }} class="rounded border-black/25 text-[#1A1018] focus:ring-[#1A1018]">
                                        <span>Save this address to my address book</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-[#7A6B78]">Label:</span>
                                        <select name="address_label" class="rounded-lg border border-black/15 px-2.5 py-1 text-xs font-medium text-[#1A1018] bg-[#FAF8F5]">
                                            <option value="Home" {{ old('address_label') === 'Home' ? 'selected' : '' }}>Home</option>
                                            <option value="Work" {{ old('address_label') === 'Work' ? 'selected' : '' }}>Work</option>
                                            <option value="Other" {{ old('address_label') === 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                            @endauth

                            @if($isFoodOrder ?? false)
                                <div class="md:col-span-2 pt-2 border-t border-black/6">
                                    <label for="kitchen_notes" class="block text-xs font-black text-[#1A1018] uppercase tracking-wider mb-1">
                                        🍳 Special Cooking / Kitchen Instructions
                                    </label>
                                    <input type="text" id="kitchen_notes" name="kitchen_notes" value="{{ old('kitchen_notes') }}" 
                                           placeholder="e.g. Less spicy, send extra green chutney, cutlery required"
                                           class="grail-input-light w-full rounded-xl text-xs font-medium py-2.5 px-3.5">
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Payment Method -->
                <section>
                    <h2 class="grail-display text-xl font-bold text-[#1A1018] mb-4">Payment Method</h2>
                    <div class="bg-white rounded-[24px] border border-black/8 shadow-[0_12px_36px_rgba(26,16,24,0.05)] overflow-hidden divide-y divide-black/6">
                        
                        @if($upiAllowed)
                            <!-- PhonePe Automated Gateway Option -->
                            <label class="block p-4 sm:p-5 cursor-pointer hover:bg-[#FAF8F5] transition-colors" :class="paymentMethod === 'phonepe' ? 'bg-[#F8EFFA]/60' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="phonepe" x-model="paymentMethod" {{ old('payment_method', $defaultPaymentMethod) === 'phonepe' ? 'checked' : '' }} class="h-4 w-4 text-[#623562] focus:ring-[#623562] border-black/25">
                                    <div class="ml-3">
                                        <span class="font-semibold text-[#1A1018] flex items-center gap-2">
                                            <span>PhonePe / UPI / Cards / NetBanking</span>
                                        </span>
                                        <p class="text-xs text-[#7A6B78] mt-0.5">Google Pay, PhonePe, Paytm, Cards, NetBanking (Instant Confirmation)</p>
                                    </div>
                                    <span class="ml-auto">
                                        <span class="px-2.5 py-1 rounded-full bg-[#1A1018] text-[#ECD2F3] text-xs font-semibold">
                                            Instant
                                        </span>
                                    </span>
                                </div>
                            </label>
                            <div x-show="paymentMethod === 'phonepe'" class="p-4 bg-[#FAF8F5] text-xs text-[#623562] flex items-center gap-2 font-medium">
                                <svg class="w-4 h-4 text-[#623562] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Zero extra fees. Once you pay on PhonePe, your order is verified and confirmed automatically!</span>
                            </div>

                            <!-- Manual UPI Option -->
                            <label class="block p-4 sm:p-5 cursor-pointer hover:bg-[#FAF8F5] transition-colors" :class="paymentMethod === 'upi' ? 'bg-[#FAF8F5]' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="upi" x-model="paymentMethod" {{ old('payment_method', $defaultPaymentMethod) === 'upi' ? 'checked' : '' }} class="h-4 w-4 text-[#1A1018] focus:ring-[#1A1018] border-black/25">
                                    <span class="ml-3 font-medium text-[#1A1018]">Direct Store UPI / QR Code</span>
                                    <span class="ml-auto flex gap-1">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#F4F1EC] text-[#7A6B78] text-xs font-medium">Store Direct</span>
                                    </span>
                                </div>
                            </label>
                            
                            <!-- UPI Details Section (conditionally shown) -->
                            <div x-show="paymentMethod === 'upi'" class="p-5 bg-[#FAF8F5] border-b border-black/6">
                                @if($settings && $settings->upi_id)
                                    <div class="text-sm text-[#7A6B78]">
                                        <p class="font-medium text-[#1A1018] mb-1">Pay Direct via UPI</p>
                                        <p>You will be redirected to a secure payment page with a QR code and UTR submission after placing your order.</p>
                                    </div>
                                @else
                                    <div class="text-sm text-rose-700 bg-rose-50 p-4 rounded-xl border border-rose-200">
                                        This vendor has not configured their direct UPI details. Please choose PhonePe above to complete payment.
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-[#FAF8F5] text-xs text-[#7A6B78] border-b border-black/6">
                                This store has selected Cash on Delivery as their accepted payment method.
                            </div>
                        @endif

                        <!-- Cash on Delivery (COD) Option -->
                        @if($isCodEligible)
                            <label class="block p-4 sm:p-5 cursor-pointer hover:bg-amber-50/30 transition-colors" :class="paymentMethod === 'cod' ? 'bg-amber-50/30' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" {{ old('payment_method', $defaultPaymentMethod) === 'cod' ? 'checked' : '' }} class="h-4 w-4 text-[#1A1018] focus:ring-[#1A1018] border-black/25">
                                    <div class="ml-3">
                                        <span class="font-semibold text-[#1A1018] flex items-center gap-2">
                                            <span>Cash on Delivery (COD)</span>
                                        </span>
                                        <p class="text-xs text-[#7A6B78] mt-0.5">Pay in cash to the delivery rider upon doorstep parcel handover</p>
                                    </div>
                                    <span class="ml-auto">
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">
                                            Pay on Delivery
                                        </span>
                                    </span>
                                </div>
                            </label>
                            <div x-show="paymentMethod === 'cod'" class="p-4 bg-amber-50/50 text-xs text-amber-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>Please keep exact cash ready upon arrival of the delivery rider. Maximum COD limit is ₹5,000.</span>
                            </div>
                        @else
                            <!-- Disabled COD indicator explaining why -->
                            <div class="p-4 bg-[#FAF8F5] text-stone-400 cursor-not-allowed">
                                <div class="flex items-center opacity-60">
                                    <input type="radio" disabled class="h-4 w-4 text-gray-300 border-gray-200 cursor-not-allowed">
                                    <div class="ml-3">
                                        <span class="font-medium text-stone-500">Cash on Delivery (Unavailable)</span>
                                        <p class="text-xs text-stone-400 mt-0.5">{{ $codDisabledReason }}</p>
                                    </div>
                                    <span class="ml-auto">
                                        <span class="px-2 py-0.5 rounded-full bg-stone-200 text-stone-500 text-[10px] font-semibold">Unavailable</span>
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                </section>
                
            </div>

            <!-- Right Column: Order Summary -->
            <div class="lg:col-span-5 mt-10 lg:mt-0">
                <section id="order-summary-section" class="bg-white rounded-[24px] border border-black/8 shadow-[0_16px_48px_rgba(26,16,24,0.08)] p-6 sticky top-24">
                    <h2 class="grail-display text-xl font-bold text-[#1A1018] mb-6">Order Summary</h2>
                    
                    <ul class="divide-y divide-black/6 mb-6 max-h-64 overflow-y-auto no-scrollbar">
                        @foreach($items as $item)
                            @php
                                $product = $item->product ?? $item->variant?->product;
                                $price = $item->price_at_add ?? ($item->variant?->price_override ?? ($product?->base_price ?? 0));
                            @endphp
                            <li class="py-4 flex gap-4">
                                <div class="w-16 h-20 bg-[#F4F1EC] rounded-xl overflow-hidden flex-shrink-0 relative border border-black/6">
                                    @if($product)
                                        <img src="{{ $product->primary_image_url }}" 
                                             alt="{{ $product->title }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                    @else
                                        <img src="{{ asset('images/product-placeholder.svg') }}" 
                                             alt="Product" 
                                             class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 text-sm min-w-0">
                                    <h4 class="font-medium text-[#1A1018] truncate">{{ $product?->title ?? 'Item' }}</h4>
                                    @if($item->gigPackage)
                                        <p class="text-xs font-semibold text-[#623562] mt-0.5">{{ ucfirst($item->gigPackage->tier) }} Tier • {{ $item->gigPackage->delivery_days }}d Delivery | Qty: {{ $item->quantity }}</p>
                                    @else
                                        <p class="text-[#7A6B78] text-xs mt-0.5">Option: {{ $item->variant?->name ?? ($item->variant?->sku ?? 'Standard') }} | Qty: {{ $item->quantity }}</p>
                                    @endif
                                    @if(!empty($item->selected_modifiers_json))
                                        <p class="text-[11px] text-amber-700 mt-0.5 font-medium">+ {{ implode(', ', array_column($item->selected_modifiers_json, 'name')) }}</p>
                                    @endif
                                    @if(!empty($item->special_instructions))
                                        <p class="text-[11px] text-stone-500 mt-0.5 italic">Note: {{ $item->special_instructions }}</p>
                                    @endif
                                    <p class="font-semibold mt-1 text-[#1A1018] tabular-nums">₹{{ number_format(($price * $item->quantity) / 100, 2) }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    
                    <!-- Coupon Section -->
                    <div class="border-t border-black/6 pt-4 mb-6">
                        @if($appliedCoupon)
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="text-sm font-bold text-emerald-800 uppercase tracking-wide">{{ $appliedCoupon->code }}</span>
                                    </div>
                                    <p class="text-xs text-emerald-700 mt-0.5">Coupon applied successfully!</p>
                                </div>
                                <button type="button" onclick="window.removeCoupon()" class="text-sm font-semibold text-rose-600 hover:text-rose-700 p-2 cursor-pointer">
                                    Remove
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2 w-full max-w-full">
                                <input type="text" id="coupon_code_input" name="coupon_code" placeholder="Enter coupon code" class="grail-input-light min-w-0 flex-1 rounded-xl text-sm px-3.5 py-2.5" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); window.applyCoupon(); }">
                                <button type="button" id="apply-coupon-btn" onclick="window.applyCoupon()" class="shrink-0 whitespace-nowrap bg-[#1A1018] hover:bg-black text-[#ECD2F3] px-5 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm active:scale-95 cursor-pointer">
                                    Apply
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    <dl id="order-summary-totals"
                        data-subtotal="{{ (int) $subtotal }}"
                        data-discount="{{ (int) $discount }}"
                        data-shipping="{{ (int) $shipping }}"
                        data-packaging="{{ (int) ($packagingFee ?? 0) }}"
                        class="space-y-3 text-sm text-[#7A6B78] border-t border-black/6 pt-4 mb-6">
                        <div class="flex justify-between">
                            <dt>Subtotal</dt>
                            <dd class="font-medium text-[#1A1018] tabular-nums">₹{{ number_format($subtotal / 100, 2) }}</dd>
                        </div>
                        
                        @if($discount > 0)
                            <div class="flex justify-between text-emerald-600 font-medium">
                                <dt>Discount ({{ $appliedCoupon->code }})</dt>
                                <dd class="tabular-nums">-₹{{ number_format($discount / 100, 2) }}</dd>
                            </div>
                        @endif

                        @if(($packagingFee ?? 0) > 0)
                            <div class="flex justify-between">
                                <dt>Kitchen Packaging Fee</dt>
                                <dd class="font-medium text-[#1A1018] tabular-nums">₹{{ number_format($packagingFee / 100, 2) }}</dd>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center">
                            <dt class="flex items-center gap-1.5">
                                <span>{{ ($isFoodOrder ?? false) ? 'Delivery Fee' : 'Shipping' }}</span>
                                <span id="shipping-badge" class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 {{ $initDeliveryFeePaise == 0 ? '' : 'hidden' }}">{{ $defaultOrderType === 'food_takeaway' ? 'Self Pickup' : 'Free Delivery' }}</span>
                            </dt>
                            <dd id="shipping-display" class="font-medium text-[#1A1018] tabular-nums">{{ $initDeliveryFeePaise == 0 ? ($defaultOrderType === 'food_takeaway' ? 'Free (Self Pickup)' : 'Free') : '₹' . number_format($initDeliveryFeePaise / 100, 2) }}</dd>
                        </div>

                        @if(($isFoodOrder ?? false) && !empty($freeDeliveryAbove) && $subtotal < $freeDeliveryAbove)
                            <p class="text-[11px] text-amber-700 font-medium pt-1">
                                Tip: Free doorstep delivery is available on orders above ₹{{ number_format($freeDeliveryAbove / 100, 0) }}.
                            </p>
                        @endif
                    </dl>
                    
                    <div class="flex justify-between items-end border-t border-black/6 pt-4 mb-8">
                        <dt class="text-base font-bold text-[#1A1018]">Total</dt>
                        <dd id="total-display" class="grail-display text-3xl font-bold text-[#1A1018] tabular-nums">₹{{ number_format($initDisplayTotalPaise / 100, 2) }}</dd>
                    </div>
                    
                    <button type="submit" form="checkout-form" class="w-full py-4 text-base shadow-[0_14px_34px_rgba(26,16,24,0.24)] font-bold bg-[#1A1018] hover:bg-black text-[#ECD2F3] rounded-2xl active:scale-95 transition-all text-center flex items-center justify-center gap-2 cursor-pointer min-h-[56px]">
                        <span class="font-bold text-base flex items-center justify-center gap-2" x-text="checkoutBtnText">
                            Place Order &rarr;
                        </span>
                    </button>
                    
                    <p class="text-center text-xs text-[#7A6B78] mt-4">
                        By placing this order, you agree to {{ $vendor->shop_name }}'s return & shipping policies.
                    </p>
                </section>
            </div>
            
        </div>
    </form>

    <form id="apply-coupon-form" action="{{ route('checkout.coupon.apply', $vendor->id) }}" method="POST" class="hidden">
        @csrf
    </form>

    <form id="remove-coupon-form" action="{{ route('checkout.coupon.remove', $vendor->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
        </div>
    </div>

    <script>
        let subtotalPaise = {{ (int) $subtotal }};
        let discountPaise = {{ (int) $discount }};
        let currentShippingPaise = {{ (int) $shipping }};
        let packagingFeePaise = {{ (int) ($packagingFee ?? 0) }};
        window.isFoodOrderCheckout = {{ ($isFoodOrder ?? false) ? 'true' : 'false' }};
        window.checkoutOrderType = '{{ $defaultOrderType }}';
        let pincodeDebounceTimer = null;

        window.setCheckoutOrderType = function(orderType) {
            window.checkoutOrderType = orderType || 'standard';
            const shippingDisplay = document.getElementById('shipping-display');
            const totalDisplay = document.getElementById('total-display');
            const shippingBadge = document.getElementById('shipping-badge');

            const isTakeaway = window.isFoodOrderCheckout && window.checkoutOrderType === 'food_takeaway';
            const effectiveShipping = isTakeaway ? 0 : currentShippingPaise;

            if (shippingDisplay) {
                shippingDisplay.textContent = effectiveShipping === 0
                    ? (isTakeaway ? 'Free (Self Pickup)' : 'Free')
                    : '₹' + (effectiveShipping / 100).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (shippingBadge) {
                shippingBadge.textContent = isTakeaway ? 'Self Pickup' : 'Free Delivery';
                if (effectiveShipping === 0) {
                    shippingBadge.classList.remove('hidden');
                } else {
                    shippingBadge.classList.add('hidden');
                }
            }
            if (totalDisplay) {
                const netProduct = Math.max(0, subtotalPaise - discountPaise);
                const newTotalPaise = netProduct + effectiveShipping + packagingFeePaise;
                totalDisplay.textContent = '₹' + (newTotalPaise / 100).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        };

        window.handlePincodeChange = function(pincode) {
            if (window.isFoodOrderCheckout) {
                return;
            }
            pincode = (pincode || '').trim();
            if (pincode.length !== 6 || !/^\d{6}$/.test(pincode)) {
                return;
            }

            clearTimeout(pincodeDebounceTimer);
            pincodeDebounceTimer = setTimeout(() => {
                fetchShippingRate(pincode);
            }, 350);
        };

        function fetchShippingRate(pincode) {
            if (window.isFoodOrderCheckout) {
                return;
            }
            const spinner = document.getElementById('pincode-spinner');
            const msg = document.getElementById('pincode-msg');
            const shippingDisplay = document.getElementById('shipping-display');
            const totalDisplay = document.getElementById('total-display');
            const shippingBadge = document.getElementById('shipping-badge');

            if (spinner) spinner.classList.remove('hidden');

            fetch("{{ route('checkout.shipping_rate', $vendor->id) }}", {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ pincode: pincode })
            })
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.classList.add('hidden');
                if (data.success) {
                    currentShippingPaise = data.shipping_paise;
                    if (shippingDisplay) {
                        shippingDisplay.textContent = data.shipping_formatted;
                    }
                    if (shippingBadge) {
                        shippingBadge.textContent = 'Free Delivery';
                        if (data.is_free) {
                            shippingBadge.classList.remove('hidden');
                        } else {
                            shippingBadge.classList.add('hidden');
                        }
                    }
                    if (msg) {
                        msg.textContent = `${data.courier_name} (Est. ${data.estimated_days} Days)`;
                        msg.classList.remove('hidden');
                        msg.classList.remove('text-red-600');
                        msg.classList.add('text-emerald-600');
                    }

                    // Recalculate total
                    const netProduct = Math.max(0, subtotalPaise - discountPaise);
                    const newTotalPaise = netProduct + currentShippingPaise + packagingFeePaise;
                    if (totalDisplay) {
                        totalDisplay.textContent = '₹' + (newTotalPaise / 100).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                } else {
                    if (msg) {
                        msg.textContent = data.message || 'Delivery unavailable for this pincode.';
                        msg.classList.remove('hidden');
                        msg.classList.remove('text-emerald-600');
                        msg.classList.add('text-red-600');
                    }
                }
            })
            .catch(err => {
                if (spinner) spinner.classList.add('hidden');
                console.error('Shipping calculation error:', err);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkoutForm = document.getElementById('checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    // Prevent submission if they press enter inside the coupon input on mobile
                    if (document.activeElement && document.activeElement.id === 'coupon_code_input') {
                        e.preventDefault();
                        window.applyCoupon();
                    }
                });
            }

            // Trigger Shiprocket rate lookup only for non-Food physical retail orders
            if (!window.isFoodOrderCheckout) {
                const pincodeInput = document.getElementById('pincode');
                if (pincodeInput && pincodeInput.value && pincodeInput.value.trim().length === 6) {
                    window.handlePincodeChange(pincodeInput.value);
                }
            }
        });

        window.refreshOrderSummary = async function() {
            try {
                const response = await fetch(window.location.href, { credentials: 'include' });
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                
                // Update Order Summary
                const fetchedSummary = doc.getElementById('order-summary-section');
                const currentSummary = document.getElementById('order-summary-section');
                if (fetchedSummary && currentSummary) {
                    currentSummary.innerHTML = fetchedSummary.innerHTML;
                    const totalsEl = document.getElementById('order-summary-totals');
                    if (totalsEl) {
                        subtotalPaise = parseInt(totalsEl.dataset.subtotal || '0', 10);
                        discountPaise = parseInt(totalsEl.dataset.discount || '0', 10);
                        currentShippingPaise = parseInt(totalsEl.dataset.shipping || '0', 10);
                        packagingFeePaise = parseInt(totalsEl.dataset.packaging || '0', 10);
                    }
                    window.setCheckoutOrderType(window.checkoutOrderType);
                }

            } catch (error) {
                console.error('Error refreshing summary:', error);
            }
        };

        window.applyCoupon = async function() {
            const couponInput = document.getElementById('coupon_code_input');
            const applyBtn = document.getElementById('apply-coupon-btn');
            
            if (!couponInput || !couponInput.value.trim()) return;

            // Remove existing error if any
            const existingError = document.getElementById('coupon-error');
            if (existingError) existingError.remove();

            // Change button state
            const originalText = applyBtn.innerText;
            applyBtn.innerText = '...';
            applyBtn.disabled = true;

            try {
                const response = await fetch("{{ route('checkout.coupon.apply', $vendor->id) }}", {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        coupon_code: couponInput.value
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    // Show inline error
                    const errorP = document.createElement('p');
                    errorP.id = 'coupon-error';
                    errorP.className = 'text-red-500 text-xs mt-2 font-medium';
                    errorP.innerText = data.message;
                    couponInput.parentElement.parentElement.appendChild(errorP);
                    
                    applyBtn.innerText = originalText;
                    applyBtn.disabled = false;
                } else {
                    // Success! Dynamically replace the Order Summary without reloading
                    await window.refreshOrderSummary();
                }
            } catch (error) {
                console.error('Error applying coupon:', error);
                applyBtn.innerText = originalText;
                applyBtn.disabled = false;
            }
        };

        window.removeCoupon = async function() {
            try {
                const response = await fetch("{{ route('checkout.coupon.remove', $vendor->id) }}", {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    await window.refreshOrderSummary();
                }
            } catch (error) {
                console.error('Error removing coupon:', error);
            }
        };
    </script>
</x-layouts.public>
