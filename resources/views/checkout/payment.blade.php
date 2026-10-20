<x-layouts.public title="Complete Payment - {{ $vendor->shop_name }}">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-sage/10 text-brand-sage mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h1 class="text-3xl font-serif font-bold text-brand-text mb-3">Secure UPI Payment</h1>
            <p class="text-gray-500 text-lg">Your order is saved! Complete the payment to confirm it.</p>
        </div>

        <div class="bg-white border border-gray-100 rounded-3xl shadow-xl shadow-brand-text/5 overflow-hidden">
            <!-- Order Info Header -->
            <div class="bg-brand-surface/20 px-8 py-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-widest mb-1">Order Number</p>
                    <p class="text-xl font-bold text-brand-text">{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-widest mb-1">Amount to Pay</p>
                    <p class="text-3xl font-black text-brand-sage">₹{{ number_format($order->total_amount / 100, 2) }}</p>
                </div>
            </div>

            <div class="p-8 md:p-10">
                @if(session('error'))
                    <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-10 md:gap-16">
                    <!-- Left: QR Code -->
                    <div class="flex flex-col items-center justify-center space-y-6">
                        <div class="p-4 bg-white rounded-3xl shadow-sm border border-gray-100">
                            <img src="{{ $qr_url }}" alt="UPI QR Code" class="w-56 h-56 object-contain rounded-2xl mx-auto">
                        </div>
                        
                        <div class="text-center w-full">
                            <p class="text-sm font-medium text-gray-500 mb-1">Scan with any UPI App</p>
                            <p class="text-sm text-gray-400 mb-4">(GPay, PhonePe, Paytm, etc.)</p>
                            
                            <!-- Mobile App Intent -->
                            <div class="block md:hidden w-full">
                                <a href="{{ $upiUrl }}" class="flex items-center justify-center gap-2 w-full py-4 bg-brand-text text-white rounded-2xl font-bold shadow-md hover:bg-gray-800 transition-all active:scale-95">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Tap to open UPI App
                                </a>
                                <div class="relative flex items-center py-6">
                                    <div class="flex-grow border-t border-gray-200"></div>
                                    <span class="flex-shrink-0 mx-4 text-gray-400 text-sm font-medium">OR</span>
                                    <div class="flex-grow border-t border-gray-200"></div>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-500 mb-1">Or Pay directly to VPA:</p>
                            <p class="text-lg font-bold text-brand-text select-all">{{ $settings->upi_id }}</p>
                        </div>
                    </div>

                    <!-- Right: Verification Form -->
                    <div class="flex flex-col justify-center border-t border-gray-100 pt-8 md:border-t-0 md:border-l md:pt-0 md:pl-10 md:-ml-8">
                        
                        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-5 mb-8">
                            <h3 class="text-blue-900 font-bold mb-2 flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-xs">1</span>
                                Complete Payment
                            </h3>
                            <p class="text-sm text-blue-800 ml-8 mb-4">Pay ₹{{ number_format($order->total_amount / 100, 2) }} using the QR code or link provided.</p>
                            
                            <h3 class="text-blue-900 font-bold mb-2 flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-200 text-blue-700 text-xs">2</span>
                                Enter Reference ID
                            </h3>
                            <p class="text-sm text-blue-800 ml-8">After paying, copy the 12-digit UTR/Reference number from your app and paste it below.</p>
                        </div>

                        <form action="{{ route('checkout.payment.process', $order->id) }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="upi_transaction_id" class="block text-sm font-medium text-gray-700 mb-2">Transaction ID (12-digit UTR)</label>
                                <input type="text" id="upi_transaction_id" name="upi_transaction_id" required class="w-full rounded-2xl border-gray-300 shadow-sm focus:border-brand-sage focus:ring-brand-sage px-4 py-3 text-lg tracking-wide" placeholder="e.g. 312345678901">
                            </div>
                            
                            <button type="submit" class="w-full py-4 bg-brand-sage hover:bg-brand-sage/90 text-white font-bold rounded-2xl shadow-md transition-all active:scale-95 flex justify-center items-center gap-2">
                                Verify Payment
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Safe & Secure footer -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-center items-center gap-2 text-sm font-medium text-gray-500">
                <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Secure 256-bit SSL Encryption
            </div>
        </div>
    </div>
</x-layouts.public>
