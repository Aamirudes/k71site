<x-layouts.public title="Earn on K71 by Delivery Partner | Blinkit-Speed Fleet">
    <div class="relative overflow-hidden py-8 sm:py-14">
        
        <!-- Atmospheric Indian Handprints & Glassmorphism Backdrop -->
        <div class="absolute inset-0 pointer-events-none opacity-20">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/30 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-24 w-96 h-96 bg-amber-500/20 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10">
            
            <!-- Hero Heading -->
            <div class="text-center max-w-2xl mx-auto mb-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 text-xs font-bold mb-4 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>⚡ K71 Hyperlocal Fleet • Blinkit Speed</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-950 tracking-tight leading-tight">
                    Earn on K71 by <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">Delivery Partner</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base text-slate-600 leading-relaxed font-medium">
                    Deliver food, sweets, groceries &amp; urgent home needs in your neighborhood. Flexible hours, instant UPI payouts &amp; highest per-delivery incentives.
                </p>
            </div>

            <!-- 3 High Value Perks -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
                <div class="p-4 rounded-2xl bg-white/80 backdrop-blur-md border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">💰</div>
                    <div>
                        <div class="text-xs font-black text-slate-900">₹600 - ₹1,400 / Day</div>
                        <div class="text-[11px] text-slate-500">Guaranteed base + peak bonus</div>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white/80 backdrop-blur-md border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0">⚡</div>
                    <div>
                        <div class="text-xs font-black text-slate-900">10-15 Min Deliveries</div>
                        <div class="text-[11px] text-slate-500">Short distances within 2-4 km radius</div>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white/80 backdrop-blur-md border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0">📱</div>
                    <div>
                        <div class="text-xs font-black text-slate-900">Instant UPI Payouts</div>
                        <div class="text-[11px] text-slate-500">Daily settlements straight to bank</div>
                    </div>
                </div>
            </div>

            <!-- Onboarding Card Form -->
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl border border-slate-200/90 shadow-xl p-6 sm:p-10">
                
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                        🛵
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Pilot Registration Form</h2>
                        <p class="text-xs text-slate-500">Fill your contact details to start taking orders today.</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('delivery.register') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required
                                   placeholder="e.g. Ramesh Prajapat"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Mobile Phone (WhatsApp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" required
                                   placeholder="10-digit mobile number"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Vehicle Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="vehicle_type" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none bg-white">
                                <option value="bike">Motorcycle / Bike (Petrol)</option>
                                <option value="scooter">Scooter / Activa</option>
                                <option value="ev">Electric Scooter (EV)</option>
                                <option value="bicycle">Bicycle</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Vehicle Plate Number
                            </label>
                            <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}"
                                   placeholder="e.g. RJ-21-SD-4091"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                City / Area <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="city" value="{{ old('city', 'Makrana') }}" required
                                   placeholder="e.g. Makrana, Jaipur"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                State <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="state" value="{{ old('state', 'Rajasthan') }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Payout UPI ID (GPay / PhonePe / Paytm)
                            </label>
                            <input type="text" name="payout_upi_id" value="{{ old('payout_upi_id') }}"
                                   placeholder="e.g. 9829012345@okhdfcbank"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 outline-none font-mono">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold text-sm shadow-lg shadow-emerald-600/25 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2">
                            <span>Join Fleet &amp; Launch Pilot Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>

                    <div class="text-center text-xs text-slate-400">
                        Already registered? Head straight to <a href="{{ route('delivery.dashboard') }}" class="font-bold text-emerald-600 hover:underline">Delivery Pilot Dashboard →</a>
                    </div>
                </form>

            </div>

        </div>
    </div>
</x-layouts.public>

