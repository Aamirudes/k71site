<x-layouts.public title="Earn on K71 by Delivery Partner | Blinkit-Speed Fleet" :fullWidth="true">
    <!-- Obsidian Hero Stage -->
    <section class="grail-hero-band text-[#EFE9F2] relative overflow-hidden border-b border-white/10">
        <!-- Atmospheric Orchid & Gold Glow -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#ECD2F3]/15 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-24 w-96 h-96 bg-[#C9A24A]/12 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-14 sm:pt-14 sm:pb-16 relative z-10">
            <!-- Hero Heading -->
            <div class="text-center max-w-2xl mx-auto mb-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/8 border border-white/15 text-[#ECD2F3] text-xs font-bold mb-5 backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>⚡ K71 Hyperlocal Fleet • Blinkit Speed</span>
                </div>
                <h1 class="grail-display text-[clamp(2.25rem,5vw,3.75rem)] font-bold text-white tracking-tight leading-[1.06]">
                    Earn on K71 as a <span class="text-[#ECD2F3] italic font-normal">Delivery Partner</span>
                </h1>
                <p class="mt-4 text-sm sm:text-base text-white/70 leading-relaxed font-medium">
                    Deliver food, sweets, groceries &amp; urgent home needs in your neighborhood. Flexible hours, instant UPI payouts &amp; highest per-delivery incentives.
                </p>
            </div>

            <!-- 3 High Value Perks -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-[#161616]/90 backdrop-blur-md border border-white/12 shadow-lg flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-[#ECD2F3]/15 text-[#ECD2F3] flex items-center justify-center text-xl shrink-0">💰</div>
                    <div>
                        <div class="text-xs font-black text-white">₹600 - ₹1,400 / Day</div>
                        <div class="text-[11px] text-white/60">Guaranteed base + peak bonus</div>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-[#161616]/90 backdrop-blur-md border border-white/12 shadow-lg flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-400/15 text-amber-300 flex items-center justify-center text-xl shrink-0">⚡</div>
                    <div>
                        <div class="text-xs font-black text-white">10-15 Min Deliveries</div>
                        <div class="text-[11px] text-white/60">Short distances within 2-4 km radius</div>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-[#161616]/90 backdrop-blur-md border border-white/12 shadow-lg flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-400/15 text-emerald-300 flex items-center justify-center text-xl shrink-0">📱</div>
                    <div>
                        <div class="text-xs font-black text-white">Instant UPI Payouts</div>
                        <div class="text-[11px] text-white/60">Daily settlements straight to bank</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Grail Stone Sheet -->
    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] py-10 sm:py-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <!-- Onboarding Card Form -->
            <div class="bg-white rounded-[28px] border border-black/8 shadow-[0_18px_50px_rgba(26,16,24,0.08)] p-6 sm:p-10">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-black/8 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center font-bold text-lg">
                            🛵
                        </div>
                        <div>
                            <h2 class="grail-display text-xl font-bold text-[#1A1018]">Fleet Delivery Boy Registration</h2>
                            <p class="text-xs text-[#7A6B78]">Dedicated Fleet Rider Account (Independent of Customer &amp; Vendor profiles).</p>
                        </div>
                    </div>
                    <span class="self-start sm:self-center px-3 py-1 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[10px] font-mono font-bold uppercase tracking-wider">
                        Role: Fleet Pilot
                    </span>
                </div>

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isVendor()))
                    <div class="mb-6 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs">
                        <strong>Logged in as {{ ucfirst(auth()->user()->role) }} ({{ auth()->user()->email }}):</strong>
                        Your store/admin account will remain untouched. Fill in the rider credentials below to create a dedicated <strong>Fleet Delivery Boy</strong> account.
                    </div>
                @endif

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

                    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->isVendor())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Rider Email Address <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="rider@example.com"
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Rider Password <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" name="password" required minlength="6"
                                   placeholder="Minimum 6 characters"
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required
                                   placeholder="e.g. Your Full Name"
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Mobile Phone (WhatsApp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" required
                                   placeholder="10-digit mobile number"
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Vehicle Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="vehicle_type" required
                                    class="grail-input-light w-full px-3.5 py-2.5 rounded-xl text-sm bg-white">
                                <option value="bike">Motorcycle / Bike (Petrol)</option>
                                <option value="scooter">Scooter / Activa</option>
                                <option value="ev">Electric Scooter (EV)</option>
                                <option value="bicycle">Bicycle</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Vehicle Plate Number
                            </label>
                            <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}"
                                   placeholder="Your vehicle registration no."
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                City / Area <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="city" value="{{ old('city', 'Kishangarh') }}" required
                                   placeholder="e.g. Kishangarh, Rajasthan"
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                State <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="state" value="{{ old('state', 'Rajasthan') }}" required
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1.5">
                                Payout UPI ID (GPay / PhonePe / Paytm)
                            </label>
                            <input type="text" name="payout_upi_id" value="{{ old('payout_upi_id') }}"
                                   placeholder="e.g. 9829012345@okhdfcbank"
                                   class="grail-input-light w-full px-4 py-2.5 rounded-xl text-sm font-mono">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="grail-btn-obsidian w-full py-4 px-6 rounded-2xl text-sm shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                            <span>Join Fleet &amp; Launch Pilot Dashboard</span>
                            <svg class="w-4 h-4 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>

                    <div class="text-center text-xs text-[#7A6B78]">
                        @if(auth()->check() && auth()->user()->isFleet())
                            Already registered as Fleet Partner? Head straight to <a href="{{ route('delivery.dashboard') }}" class="font-bold text-[#623562] hover:underline">Fleet Partner Dashboard →</a>
                        @elseif(auth()->check() && auth()->user()->isAdmin())
                            Signed in as Admin. Want fleet monitoring? <a href="{{ route('delivery.dashboard') }}" class="font-bold text-[#623562] hover:underline">Fleet Administrative Oversight →</a>
                        @elseif(auth()->check() && auth()->user()->isVendor())
                            Signed in as Vendor. To deliver, please create a dedicated Fleet Rider profile above.
                        @else
                            Already registered as Fleet Partner? <a href="{{ route('login', ['role' => 'fleet']) }}" class="font-bold text-[#623562] hover:underline">Sign in to Fleet Dashboard →</a>
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-layouts.public>

