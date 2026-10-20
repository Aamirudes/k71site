<x-layouts.public title="Checkout - {{ $vendor->shop_name }}">
    <div class="mb-8">
        <h1 class="text-3xl font-serif font-bold text-brand-text">Checkout</h1>
        <p class="text-sm text-gray-500 mt-1">Completing your order with {{ $vendor->shop_name }}</p>
    </div>

    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.process', $vendor->id) }}" method="POST" id="checkout-form">
        @csrf
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 xl:gap-x-16">
            
            <!-- Left Column: Shipping & Payment -->
            <div class="lg:col-span-7 space-y-10">
                
                <!-- Shipping Details -->
                <section>
                    <h2 class="text-xl font-medium text-brand-text mb-4">Shipping Details</h2>
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm" placeholder="House/Flat No., Street, Landmark">
                            </div>
                            
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                <input type="text" id="state" name="state" value="{{ old('state') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                <div class="relative">
                                    <input type="text" id="pincode" name="pincode" value="{{ old('pincode', $cachedShipping['pincode'] ?? '') }}" required pattern="[0-9]{6}" maxlength="6" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm pr-9" placeholder="6-digit pincode" oninput="window.handlePincodeChange(this.value)">
                                    <div id="pincode-spinner" class="hidden absolute right-3 top-2.5">
                                        <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p id="pincode-msg" class="text-xs text-emerald-600 mt-1 font-medium {{ !empty($cachedShipping['courier_name']) ? '' : 'hidden' }}">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>
                                    <span>{{ $cachedShipping['courier_name'] ?? '' }} (Est. {{ $cachedShipping['est_days'] ?? 3 }} Days)</span>
                                </p>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone (+91...)</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="10 digit mobile number" minlength="10" maxlength="15" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Payment Method -->
                <section x-data="{ paymentMethod: '{{ old('payment_method', $defaultPaymentMethod) }}' }">
                    <h2 class="text-xl font-medium text-brand-text mb-4">Payment Method</h2>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden divide-y divide-gray-200">
                        
                        @if($upiAllowed)
                            <!-- PhonePe Automated Gateway Option -->
                            <label class="block p-4 cursor-pointer hover:bg-purple-50/40 transition-colors" :class="paymentMethod === 'phonepe' ? 'bg-purple-50/30' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="phonepe" x-model="paymentMethod" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                    <div class="ml-3">
                                        <span class="font-semibold text-brand-text flex items-center gap-2">
                                            <span>PhonePe / UPI / Cards / NetBanking</span>
                                        </span>
                                        <p class="text-xs text-gray-500 mt-0.5">Google Pay, PhonePe, Paytm, Cards, NetBanking (Instant Confirmation)</p>
                                    </div>
                                    <span class="ml-auto">
                                        <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold">
                                            Instant
                                        </span>
                                    </span>
                                </div>
                            </label>
                            <div x-show="paymentMethod === 'phonepe'" class="p-4 bg-purple-50/50 text-xs text-purple-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Zero extra fees. Once you pay on PhonePe, your order is verified and confirmed automatically!</span>
                            </div>

                            <!-- Manual UPI Option -->
                            <label class="block p-4 cursor-pointer hover:bg-gray-50 transition-colors" :class="paymentMethod === 'upi' ? 'bg-stone-50' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="upi" x-model="paymentMethod" class="h-4 w-4 text-brand-sage focus:ring-brand-sage border-gray-300">
                                    <span class="ml-3 font-medium text-brand-text">Direct Store UPI / QR Code</span>
                                    <span class="ml-auto flex gap-1">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs font-medium">Store Direct</span>
                                    </span>
                                </div>
                            </label>
                            
                            <!-- UPI Details Section (conditionally shown) -->
                            <div x-show="paymentMethod === 'upi'" class="p-6 bg-brand-surface/10 border-b border-gray-200">
                                @if($settings && $settings->upi_id)
                                    <div class="text-sm text-gray-600">
                                        <p class="font-medium text-brand-text mb-1">Pay Direct via UPI</p>
                                        <p>You will be redirected to a secure payment page with a QR code and UTR submission after placing your order.</p>
                                    </div>
                                @else
                                    <div class="text-sm text-red-600 bg-red-50 p-4 rounded-md">
                                        This vendor has not configured their direct UPI details. Please choose PhonePe above to complete payment.
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-stone-50 text-xs text-stone-600 border-b border-stone-200">
                                This store has selected Cash on Delivery as their accepted payment method.
                            </div>
                        @endif

                        <!-- Cash on Delivery (COD) Option -->
                        @if($isCodEligible)
                            <label class="block p-4 cursor-pointer hover:bg-amber-50/30 transition-colors" :class="paymentMethod === 'cod' ? 'bg-amber-50/20' : ''">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300">
                                    <div class="ml-3">
                                        <span class="font-semibold text-brand-text flex items-center gap-2">
                                            <span>Cash on Delivery (COD)</span>
                                        </span>
                                        <p class="text-xs text-gray-500 mt-0.5">Pay in cash to the delivery rider upon doorstep parcel handover</p>
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
                            <div class="p-4 bg-gray-50/80 text-gray-400 cursor-not-allowed">
                                <div class="flex items-center opacity-60">
                                    <input type="radio" disabled class="h-4 w-4 text-gray-300 border-gray-200 cursor-not-allowed">
                                    <div class="ml-3">
                                        <span class="font-medium text-gray-500">Cash on Delivery (Unavailable)</span>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $codDisabledReason }}</p>
                                    </div>
                                    <span class="ml-auto">
                                        <span class="px-2 py-0.5 rounded bg-gray-200 text-gray-500 text-[10px] font-semibold">Unavailable</span>
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                </section>
                
            </div>

            <!-- Right Column: Order Summary -->
            <div class="lg:col-span-5 mt-10 lg:mt-0">
                <section id="order-summary-section" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sticky top-24">
                    <h2 class="text-xl font-serif font-bold text-brand-text mb-6">Order Summary</h2>
                    
                    <ul class="divide-y divide-gray-100 mb-6 max-h-64 overflow-y-auto no-scrollbar">
                        @foreach($items as $item)
                            @php
                                $product = $item->product ?? $item->variant?->product;
                                $price = $item->price_at_add ?? ($item->variant?->price_override ?? ($product?->base_price ?? 0));
                            @endphp
                            <li class="py-4 flex gap-4">
                                <div class="w-16 h-20 bg-gray-100 rounded-md overflow-hidden flex-shrink-0 relative">
                                    @if($product && $product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                             alt="{{ $product->title }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                    @else
                                        <img src="{{ asset('images/product-placeholder.svg') }}" 
                                             alt="{{ $product?->title ?? 'Product' }}" 
                                             class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 text-sm min-w-0">
                                    <h4 class="font-medium text-brand-text truncate">{{ $product?->title ?? 'Item' }}</h4>
                                    @if($item->gigPackage)
                                        <p class="text-xs font-semibold text-indigo-700 mt-0.5">{{ ucfirst($item->gigPackage->tier) }} Tier • {{ $item->gigPackage->delivery_days }}d Delivery | Qty: {{ $item->quantity }}</p>
                                    @else
                                        <p class="text-gray-500 mt-0.5">Option: {{ $item->variant?->name ?? ($item->variant?->sku ?? 'Standard') }} | Qty: {{ $item->quantity }}</p>
                                    @endif
                                    @if(!empty($item->selected_modifiers_json))
                                        <p class="text-[11px] text-amber-700 mt-0.5 font-medium">+ {{ implode(', ', array_column($item->selected_modifiers_json, 'name')) }}</p>
                                    @endif
                                    <p class="font-medium mt-1 text-brand-text">₹{{ number_format(($price * $item->quantity) / 100, 2) }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    
                    <!-- Coupon Section -->
                    <div class="border-t border-gray-100 pt-4 mb-6">
                        @if($appliedCoupon)
                            <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="text-sm font-bold text-green-800 uppercase tracking-wide">{{ $appliedCoupon->code }}</span>
                                    </div>
                                    <p class="text-xs text-green-600 mt-0.5">Coupon applied successfully!</p>
                                </div>
                                <button type="button" onclick="window.removeCoupon()" class="text-sm font-semibold text-red-500 hover:text-red-700 p-2">
                                    Remove
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2 w-full max-w-full">
                                <input type="text" id="coupon_code_input" name="coupon_code" placeholder="Enter coupon code" class="min-w-0 flex-1 rounded-xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm px-3.5 py-2.5" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); window.applyCoupon(); }">
                                <button type="button" id="apply-coupon-btn" onclick="window.applyCoupon()" class="shrink-0 whitespace-nowrap bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors shadow-sm active:scale-95">
                                    Apply
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    <dl class="space-y-3 text-sm text-gray-600 border-t border-gray-100 pt-4 mb-6">
                        <div class="flex justify-between">
                            <dt>Subtotal</dt>
                            <dd class="font-medium text-brand-text">₹{{ number_format($subtotal / 100, 2) }}</dd>
                        </div>
                        
                        @if($discount > 0)
                            <div class="flex justify-between text-green-600 font-medium">
                                <dt>Discount ({{ $appliedCoupon->code }})</dt>
                                <dd>-₹{{ number_format($discount / 100, 2) }}</dd>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center">
                            <dt class="flex items-center gap-1.5">
                                <span>Shipping</span>
                                <span id="shipping-badge" class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 {{ $shipping == 0 ? '' : 'hidden' }}">Free Delivery</span>
                            </dt>
                            <dd id="shipping-display" class="font-medium text-brand-text">{{ $shipping == 0 ? 'Free' : '₹' . number_format($shipping / 100, 2) }}</dd>
                        </div>
                    </dl>
                    
                    <div class="flex justify-between items-end border-t border-gray-100 pt-4 mb-8">
                        <dt class="text-base font-bold text-brand-text">Total</dt>
                        <dd id="total-display" class="text-2xl font-bold text-brand-text">₹{{ number_format($total / 100, 2) }}</dd>
                    </div>
                    
                    <button type="submit" form="checkout-form" class="w-full btn-primary py-4 text-base shadow-sm font-semibold" x-text="paymentMethod === 'upi' ? 'Continue to Secure UPI Payment' : 'Place Order (Cash on Delivery)'">
                        Place Order
                    </button>
                    
                    <p class="text-center text-xs text-gray-500 mt-4">
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

    <script>
        let subtotalPaise = {{ $subtotal }};
        let discountPaise = {{ $discount }};
        let currentShippingPaise = {{ $shipping }};
        let pincodeDebounceTimer = null;

        window.handlePincodeChange = function(pincode) {
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
                    const newTotalPaise = netProduct + currentShippingPaise;
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

            // Trigger rate lookup if pincode is already filled
            const pincodeInput = document.getElementById('pincode');
            if (pincodeInput && pincodeInput.value && pincodeInput.value.trim().length === 6) {
                window.handlePincodeChange(pincodeInput.value);
            }
        });

        window.refreshOrderSummary = async function() {
            try {
                const response = await fetch(window.location.href, { credentials: 'include' });
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                
                // Update Order Summary
                const newSummary = doc.getElementById('order-summary-section').innerHTML;
                document.getElementById('order-summary-section').innerHTML = newSummary;

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
