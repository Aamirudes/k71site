<x-layouts.public title="Complete UPI Payment - {{ $vendor->shop_name }}" :fullWidth="true">
    <div class="min-h-screen grail-hero-band text-[#EFE9F2] py-6 sm:py-10 px-3 sm:px-6">
        
        <div class="max-w-md mx-auto">
            
            <!-- Storefront Style Obsidian Header Bar -->
            <div class="bg-[#161616] rounded-2xl border border-white/15 shadow-lg px-4 py-3.5 mb-4 flex items-center justify-between">
                <a href="{{ $vendor->storefront_url }}" class="p-1.5 text-white/75 hover:text-[#ECD2F3] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </a>
                
                <a href="{{ $vendor->storefront_url }}" class="grail-display text-xl font-bold text-white tracking-tight hover:text-[#ECD2F3] transition-colors">
                    {{ $vendor->shop_name }}
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ $vendor->storefront_url }}" class="p-1.5 text-white/70 hover:text-[#ECD2F3] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </a>
                    <a href="{{ route('cart.index') }}" class="relative p-1.5 text-white/70 hover:text-[#ECD2F3] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold flex items-center justify-center">1</span>
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="mb-4 p-3.5 bg-rose-950/70 border border-rose-500/40 text-rose-200 rounded-2xl text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3.5 bg-rose-950/70 border border-rose-500/40 text-rose-200 rounded-2xl text-xs font-semibold">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Payment Card -->
            <div class="bg-white text-[#1A1018] border border-black/10 rounded-[24px] shadow-[0_24px_60px_rgba(0,0,0,0.5)] p-5 space-y-4">
                
                <!-- Order Item Summary -->
                <div class="pb-3.5 border-b border-black/6">
                    <p class="text-[11px] font-bold tracking-[0.14em] text-[#7A6B78] uppercase mb-1">VENDOR</p>
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold text-[#1A1018]">
                        <span class="truncate max-w-[240px]">
                            @if($order->items && $order->items->count() > 0)
                                {{ $order->items->first()?->product?->title ?? 'Order Item' }}
                                @if($order->items->count() > 1)
                                    <span class="text-[#7A6B78] font-normal"> +{{ $order->items->count() - 1 }} more</span>
                                @endif
                            @else
                                Order #{{ $order->order_number }}
                            @endif
                            <span class="text-[#7A6B78] font-normal ml-1">× {{ $order->items->sum('quantity') ?: 1 }}</span>
                        </span>
                        <span class="grail-display text-lg font-bold text-[#1A1018] shrink-0 ml-2 tabular-nums">₹{{ $amountFormatted ?? number_format($order->total_amount / 100, 2) }}</span>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="flex flex-col items-center justify-center pt-1 pb-2">
                    <div class="relative p-3 bg-[#FAFAF8] rounded-2xl border border-black/10 shadow-xs">
                        <img src="{{ $qr_url }}" 
                             alt="UPI Payment QR Code" 
                             class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl bg-white">
                        
                        <!-- Centered PhonePe / UPI badge -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-10 h-10 rounded-full bg-[#5f259f] text-white flex items-center justify-center font-bold text-base shadow-md border-2 border-white">
                                <span>पे</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyable UPI ID Pill -->
                <div x-data="{ copied: false }" class="bg-[#FAF8F5] border border-black/10 rounded-xl px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <span class="text-xs font-bold text-[#7A6B78] uppercase tracking-wider shrink-0">UPI ID</span>
                        <span class="text-sm font-mono font-bold text-[#1A1018] truncate select-all">{{ $vpa }}</span>
                    </div>
                    <button type="button" 
                            @click="navigator.clipboard.writeText('{{ $vpa }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="p-1.5 text-[#7A6B78] hover:text-[#1A1018] transition-colors rounded-lg hover:bg-black/5 shrink-0 cursor-pointer"
                            title="Copy UPI ID">
                        <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-show="copied" x-cloak class="text-xs font-bold text-emerald-700">Copied!</span>
                    </button>
                </div>

                <!-- Open UPI App Button with Interactive 3-App Popup Modal (GPay, PhonePe, Paytm) -->
                <div x-data="{ showUpiModal: false }">
                    <button type="button" 
                            id="openUpiAppBtn"
                            @click="showUpiModal = true; navigator.clipboard.writeText('{{ $vpa }}');"
                            class="grail-btn-obsidian w-full py-3.5 px-4 rounded-2xl text-center text-sm flex items-center justify-center gap-2.5 shadow-md hover:shadow-lg transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Tap to Open UPI App (GPay / PhonePe / Paytm)</span>
                    </button>

                    <!-- Interactive UPI App Chooser Modal -->
                    <div x-show="showUpiModal" 
                         x-cloak
                         @keydown.escape.window="showUpiModal = false"
                         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/80 backdrop-blur-md transition-all">
                        
                        <div @click.outside="showUpiModal = false"
                             x-show="showUpiModal"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
                             class="w-full sm:max-w-md bg-[#FAFAF8] text-[#1A1018] rounded-t-[28px] sm:rounded-[28px] p-6 shadow-2xl border border-black/10 relative">
                            
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between pb-4 border-b border-black/8 mb-5">
                                <div>
                                    <h3 class="grail-display text-xl font-bold text-[#1A1018] tracking-tight flex items-center gap-2">
                                        <span>Choose Your UPI App</span>
                                        <span class="text-[11px] font-sans font-bold px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3]">1-Tap Pay</span>
                                    </h3>
                                    <p class="text-xs text-[#7A6B78] mt-0.5">Amount ₹{{ $amountFormatted }} will be pre-filled automatically</p>
                                </div>
                                <button type="button" @click="showUpiModal = false" class="w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 text-[#1A1018] flex items-center justify-center transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- 3 Payment Apps Grid -->
                            <div class="space-y-3">
                                
                                <!-- 1. Google Pay (GPay) -->
                                <a href="{{ $gpayUrl }}" 
                                   @click="showUpiModal = false"
                                   class="group flex items-center justify-between p-3.5 rounded-2xl border-2 border-black/6 hover:border-blue-500 hover:bg-blue-50/40 bg-white transition-all shadow-xs hover:shadow-md cursor-pointer">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-black/10 p-2 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                                            <svg viewBox="0 0 48 48" class="w-8 h-8">
                                                <path fill="#4285F4" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/>
                                                <path fill="#34A853" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 16.7 2 10.3 7.3 6.3 14.7z"/>
                                                <path fill="#FBBC05" d="M24 46c5.9 0 11-2.1 14.8-5.7l-6.8-5.5c-2.1 1.4-4.8 2.2-8 2.2-6 0-10.7-3.9-12.2-9.4l-6.6 5.1C9.2 40.5 16 46 24 46z"/>
                                                <path fill="#EA4335" d="M44.5 20H24v8.5h11.8c-1.1 3.1-3.1 5.7-5.8 7.3l6.8 5.5C41.8 37.6 45 31.4 45 24c0-1.3-.2-2.7-.5-4z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="font-bold text-[#1A1018] block text-sm">Google Pay (GPay)</span>
                                            <span class="text-xs text-[#7A6B78] font-medium">Pay ₹{{ $amountFormatted }} via tez://</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-blue-600 text-white group-hover:bg-blue-700 transition-colors shadow-xs">Pay</span>
                                    </div>
                                </a>

                                <!-- 2. PhonePe -->
                                <a href="{{ $phonepeUrl }}" 
                                   @click="showUpiModal = false"
                                   class="group flex items-center justify-between p-3.5 rounded-2xl border-2 border-black/6 hover:border-purple-600 hover:bg-purple-50/40 bg-white transition-all shadow-xs hover:shadow-md cursor-pointer">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-[#5F259F] p-2 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform text-white font-extrabold text-xl">
                                            पे
                                        </div>
                                        <div>
                                            <span class="font-bold text-[#1A1018] block text-sm">PhonePe</span>
                                            <span class="text-xs text-[#7A6B78] font-medium">Pay ₹{{ $amountFormatted }} via phonepe://</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-[#5F259F] text-white group-hover:bg-purple-800 transition-colors shadow-xs">Pay</span>
                                    </div>
                                </a>

                                <!-- 3. Paytm -->
                                <a href="{{ $paytmUrl }}" 
                                   @click="showUpiModal = false"
                                   class="group flex items-center justify-between p-3.5 rounded-2xl border-2 border-black/6 hover:border-sky-500 hover:bg-sky-50/40 bg-white transition-all shadow-xs hover:shadow-md cursor-pointer">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-12 rounded-2xl bg-[#002E6E] p-2 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                                            <span class="text-white font-black text-xs tracking-tighter">Pay<span class="text-[#00BAF2]">tm</span></span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-[#1A1018] block text-sm">Paytm UPI</span>
                                            <span class="text-xs text-[#7A6B78] font-medium">Pay ₹{{ $amountFormatted }} via paytmmp://</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-[#00BAF2] text-slate-950 font-black group-hover:bg-[#009FD0] transition-colors shadow-xs">Pay</span>
                                    </div>
                                </a>

                                <!-- 4. Generic / Any other UPI App (BHIM, Cred, Amazon Pay) -->
                                <a href="{{ $upiUrl }}" 
                                   @click="showUpiModal = false"
                                   class="group flex items-center justify-between p-3 rounded-2xl border border-black/10 hover:border-black/30 bg-[#FAF8F5] transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-[#F4F1EC] flex items-center justify-center font-bold text-xs text-[#1A1018]">
                                            UPI
                                        </div>
                                        <div>
                                            <span class="font-semibold text-[#1A1018] block text-xs">Other UPI App</span>
                                            <span class="text-[11px] text-[#7A6B78]">BHIM, CRED, Navi, Bank App</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-[#1A1018]">Open ↗</span>
                                </a>

                            </div>

                            <p class="text-[11px] text-[#7A6B78] text-center mt-4">
                                Note: If clicking doesn't open the app, please scan the QR code above or copy UPI ID <span class="font-mono text-[#1A1018] font-semibold">{{ $vpa }}</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Green Notification Banner (EXACT User Text) -->
                <div class="bg-[#eefcf3] border border-[#a3e6b7] text-[#0f6834] rounded-xl p-3 text-xs font-semibold text-center leading-relaxed">
                    QR Code Pe Payment Krke Niche Screenshot Ya Transaction ID Submit Kro!
                </div>

                <!-- Payment Verification Form -->
                <form action="{{ route('checkout.payment.process', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2">
                    @csrf
                    
                    <div>
                        <label for="upi_transaction_id" class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1">
                            12-digit UTR / UPI Transaction ID
                        </label>
                        <input type="text" 
                               id="upi_transaction_id" 
                               name="upi_transaction_id" 
                               value="{{ old('upi_transaction_id') }}"
                               placeholder="e.g. 429381749201" 
                               class="grail-input-light w-full px-3.5 py-2.5 rounded-xl text-sm font-mono transition-all">
                    </div>

                    <div x-data="{ fileName: '' }">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1">
                            Upload Payment Screenshot Proof
                        </label>
                        <div class="relative border-2 border-dashed border-black/20 hover:border-[#1A1018] rounded-xl p-3 text-center bg-[#FAF8F5] cursor-pointer transition-colors">
                            <input type="file" 
                                   name="payment_screenshot" 
                                   accept="image/*" 
                                   @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-base">📸</span>
                                <span class="text-xs font-semibold text-[#1A1018] truncate" x-text="fileName ? fileName : 'Choose Screenshot / Proof Photo'"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 bg-[#1A1018] hover:bg-black active:scale-98 text-[#ECD2F3] font-bold rounded-xl text-center text-sm shadow-md transition-all cursor-pointer mt-2 flex items-center justify-center gap-2">
                        <span class="font-bold">Submit Proof & Confirm Order</span>
                    </button>
                </form>

            </div>

            <!-- Trust Badge Footer -->
            <div class="mt-5 text-center">
                <p class="text-xs font-medium text-white/60 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    100% Direct UPI Transfer to Verified Vendor
                </p>
            </div>

        </div>

    </div>
</x-layouts.public>
