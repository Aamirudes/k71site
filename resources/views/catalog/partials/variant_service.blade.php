{{-- On-Demand Services & Slot Scheduling Partial (Urban Company Pattern) --}}
@php
    $config = $product->service_config_json ?? [];
    $duration = $config['duration_minutes'] ?? 60;
    $inclusions = $config['inclusions'] ?? [
        'Complete doorstep diagnostic check',
        'Standard cleaning / servicing execution',
        'Post-service quality guarantee'
    ];
    $exclusions = $config['exclusions'] ?? [
        'Spare parts, consumables and structural replacements charged at actuals'
    ];
    $sellerPhone = preg_replace('/[^0-9]/', '', $product->vendor->contact_phone ?? '');
    if (strlen($sellerPhone) === 10) { $sellerPhone = '91' . $sellerPhone; }
@endphp

<div class="space-y-5" x-data="{ showBookingModal: false, selectedDate: '{{ date('Y-m-d') }}', selectedSlot: '10:00 AM - 12:00 PM' }">
    <!-- 1. Duration & Service Assurance Strip -->
    <div class="flex items-center justify-between p-3 rounded-2xl bg-teal-50/70 border border-teal-200/80 text-xs">
        <div class="flex items-center gap-2">
            <span class="text-base">⏱️</span>
            <div>
                <span class="text-[10px] font-bold text-teal-900 uppercase tracking-wider block">Estimated Duration</span>
                <span class="font-black text-teal-950">{{ $duration }} Minutes Doorstep Visit</span>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded-full bg-white text-teal-800 text-[10px] font-extrabold border border-teal-300 shadow-2xs">
            🛡️ Verified Pro
        </span>
    </div>

    <!-- 2. Transparent Scope of Work: Inclusions & Exclusions -->
    <div class="rounded-2xl border border-stone-200 bg-white p-4 space-y-4">
        <!-- Inclusions -->
        <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide flex items-center gap-1.5 mb-2">
                <span>✓ What's Included in this Service</span>
            </span>
            <div class="space-y-1.5 text-xs text-stone-700">
                @foreach($inclusions as $inc)
                    @if(!empty(trim($inc)))
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0">✓</span>
                            <span>{{ $inc }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Exclusions -->
        <div class="pt-3 border-t border-stone-100">
            <span class="text-xs font-bold text-rose-800 uppercase tracking-wide flex items-center gap-1.5 mb-2">
                <span>✕ What's Excluded</span>
            </span>
            <div class="space-y-1.5 text-xs text-stone-600">
                @foreach($exclusions as $exc)
                    @if(!empty(trim($exc)))
                        <div class="flex items-start gap-2">
                            <span class="text-rose-500 font-bold shrink-0">✕</span>
                            <span>{{ $exc }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Primary Booking CTA & Direct WhatsApp -->
    <div class="space-y-2.5">
        <button type="button" 
                @click="showBookingModal = true"
                class="w-full py-3.5 px-6 rounded-full bg-stone-900 hover:bg-black text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span>Book Appointment (₹{{ number_format($product->base_price / 100, 2) }})</span>
        </button>

        @if(!empty($sellerPhone))
            <a href="https://wa.me/{{ $sellerPhone }}?text={{ rawurlencode('Namaste! I would like to book or discuss service: ' . $product->title . ' on K71') }}" 
               target="_blank"
               class="w-full py-2.5 px-5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition flex items-center justify-center gap-2">
                <span>💬 Quick WhatsApp Consultation</span>
            </a>
        @endif
    </div>

    <!-- 4. Appointment Booking Modal Popup -->
    <div x-show="showBookingModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;"
         @keydown.escape.window="showBookingModal = false">
        
        <div @click.away="showBookingModal = false"
             class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-7 shadow-2xl text-left text-stone-900 relative max-h-[90vh] overflow-y-auto">
            
            <button type="button" @click="showBookingModal = false" 
                    class="absolute top-4 right-4 text-stone-400 hover:text-stone-700 p-2 rounded-full hover:bg-stone-100 transition">
                ✕
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center text-xl font-bold border border-teal-200">
                    📅
                </div>
                <div>
                    <h3 class="text-base font-bold text-stone-900">Schedule Service Appointment</h3>
                    <p class="text-xs text-stone-500">{{ $product->title }}</p>
                </div>
            </div>

            <form action="{{ route('services.book', $product->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Appointment Date *</label>
                        <input type="date" name="booking_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" x-model="selectedDate"
                               class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Preferred Time Slot *</label>
                        <select name="booking_slot" required x-model="selectedSlot" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs font-semibold">
                            <option value="09:00 AM - 11:00 AM">09:00 AM - 11:00 AM (Morning)</option>
                            <option value="11:00 AM - 01:00 PM">11:00 AM - 01:00 PM (Noon)</option>
                            <option value="02:00 PM - 04:00 PM">02:00 PM - 04:00 PM (Afternoon)</option>
                            <option value="04:00 PM - 06:00 PM">04:00 PM - 06:00 PM (Evening)</option>
                            <option value="06:00 PM - 08:00 PM">06:00 PM - 08:00 PM (Night)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Your Full Name *</label>
                        <input type="text" name="customer_name" required value="{{ auth()->user()?->name ?? '' }}" placeholder="Customer name" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Phone Number *</label>
                        <input type="tel" name="customer_phone" required value="{{ auth()->user()?->phone ?? '' }}" placeholder="10-digit mobile" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Service Doorstep Address *</label>
                    <textarea name="service_address" rows="2" required placeholder="Flat/House No., Street, Landmark, Pincode" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-stone-500 uppercase block">Total Service Fee</span>
                        <span class="text-base font-black text-stone-900">₹{{ number_format($product->base_price / 100, 2) }}</span>
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-stone-900 hover:bg-black text-white text-xs font-bold transition shadow-md active:scale-95 cursor-pointer">
                        Confirm Doorstep Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
