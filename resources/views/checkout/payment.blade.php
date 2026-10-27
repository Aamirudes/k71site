<x-layouts.public title="Complete UPI Payment - {{ $vendor->shop_name }}">
    <div class="min-h-screen bg-[#F8F9FA] py-6 sm:py-10 px-3 sm:px-6">
        
        <div class="max-w-md mx-auto">
            
            <!-- Storefront Style Header Bar -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs px-4 py-3.5 mb-4 flex items-center justify-between">
                <a href="{{ $vendor->storefront_url }}" class="p-1.5 text-gray-700 hover:text-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </a>
                
                <a href="{{ $vendor->storefront_url }}" class="text-xl font-serif font-black text-gray-900 tracking-tight hover:opacity-90 transition-opacity">
                    {{ $vendor->shop_name }}
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ $vendor->storefront_url }}" class="p-1.5 text-gray-600 hover:text-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </a>
                    <a href="{{ route('cart.index') }}" class="relative p-1.5 text-gray-600 hover:text-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">1</span>
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Payment Card (Matching Screenshot 2) -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5 space-y-4">
                
                <!-- Order Item Summary -->
                <div class="pb-3 border-b border-gray-100">
                    <p class="text-[11px] font-bold tracking-widest text-gray-400 uppercase mb-1">VENDOR</p>
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold text-gray-800">
                        <span class="truncate max-w-[240px]">
                            @if($order->items && $order->items->count() > 0)
                                {{ $order->items->first()->product->title ?? 'Order Item' }}
                                @if($order->items->count() > 1)
                                    <span class="text-gray-400 font-normal"> +{{ $order->items->count() - 1 }} more</span>
                                @endif
                            @else
                                Order #{{ $order->order_number }}
                            @endif
                            <span class="text-gray-400 font-normal ml-1">× {{ $order->items->sum('quantity') ?: 1 }}</span>
                        </span>
                        <span class="font-bold text-gray-900 shrink-0 ml-2">₹{{ $amountFormatted ?? number_format($order->total_amount / 100, 2) }}</span>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="flex flex-col items-center justify-center pt-1 pb-2">
                    <div class="relative p-3 bg-white rounded-2xl border border-gray-200 shadow-2xs">
                        <img src="{{ $qr_url }}" 
                             alt="UPI Payment QR Code" 
                             class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl">
                        
                        <!-- Centered PhonePe / UPI badge -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-10 h-10 rounded-full bg-[#5f259f] text-white flex items-center justify-center font-bold text-base shadow-md border-2 border-white">
                                <span>पे</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyable UPI ID Pill -->
                <div x-data="{ copied: false }" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0">UPI ID</span>
                        <span class="text-sm font-mono font-bold text-gray-900 truncate select-all">{{ $vpa }}</span>
                    </div>
                    <button type="button" 
                            @click="navigator.clipboard.writeText('{{ $vpa }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="p-1.5 text-gray-500 hover:text-black transition-colors rounded-lg hover:bg-gray-200/60 shrink-0 cursor-pointer"
                            title="Copy UPI ID">
                        <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-show="copied" x-cloak class="text-xs font-bold text-emerald-600">Copied!</span>
                    </button>
                </div>

                <!-- Open UPI App Button (Full-width, prominent black) -->
                <div x-data>
                    <a href="{{ $upiUrl }}" 
                       id="openUpiAppBtn"
                       @click="
                           setTimeout(function() {
                               navigator.clipboard.writeText('{{ $vpa }}');
                           }, 300);
                       "
                       class="w-full py-3.5 px-4 bg-black hover:bg-stone-900 active:scale-98 text-white font-bold rounded-xl text-center text-sm flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Open UPI App</span>
                    </a>
                </div>

                <!-- Green Notification Banner (EXACT User Text) -->
                <div class="bg-[#eefcf3] border border-[#a3e6b7] text-[#0f6834] rounded-xl p-3 text-xs font-semibold text-center leading-relaxed">
                    QR Code Pe Payment Krke Niche Screenshot Ya Transaction ID Submit Kro!
                </div>

                <!-- Payment Verification Form -->
                <form action="{{ route('checkout.payment.process', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2">
                    @csrf
                    
                    <div>
                        <label for="upi_transaction_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                            12-digit UTR / UPI Transaction ID
                        </label>
                        <input type="text" 
                               id="upi_transaction_id" 
                               name="upi_transaction_id" 
                               value="{{ old('upi_transaction_id') }}"
                               placeholder="e.g. 429381749201" 
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm font-mono text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition-all">
                    </div>

                    <div x-data="{ fileName: '' }">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                            Upload Payment Screenshot Proof
                        </label>
                        <div class="relative border-2 border-dashed border-gray-300 hover:border-gray-500 rounded-xl p-3 text-center bg-gray-50/60 cursor-pointer transition-colors">
                            <input type="file" 
                                   name="payment_screenshot" 
                                   accept="image/*" 
                                   @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-base">📸</span>
                                <span class="text-xs font-semibold text-gray-700 truncate" x-text="fileName ? fileName : 'Choose Screenshot / Proof Photo'"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-bold rounded-xl text-center text-sm shadow-sm transition-all cursor-pointer mt-2">
                        Submit Proof & Confirm Order
                    </button>
                </form>

            </div>

            <!-- Trust Badge Footer -->
            <div class="mt-4 text-center">
                <p class="text-xs font-medium text-gray-400 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    100% Direct UPI Transfer to Verified Vendor
                </p>
            </div>

        </div>

    </div>
</x-layouts.public>
