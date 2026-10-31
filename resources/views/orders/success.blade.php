<x-layouts.public title="Order Successful">
    <div class="max-w-2xl mx-auto text-center py-16">
        <div class="w-20 h-20 bg-brand-sage/20 text-brand-sage rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h1 class="text-3xl font-serif font-bold text-brand-text mb-4">Order Placed Successfully!</h1>
        <p class="text-gray-600 mb-2">Thank you for shopping directly with independent brands.</p>
        <p class="text-gray-500 text-sm mb-8">Your order <span class="font-bold text-brand-text">{{ $order->order_number }}</span> has been sent to {{ $order->vendor?->shop_name ?? 'K71 Direct Store' }}.</p>
        
        <div class="bg-white rounded-2xl p-6 border border-gray-200 text-left mb-8 shadow-sm">
            <h3 class="font-medium text-brand-text mb-4 border-b border-gray-100 pb-4">Order Summary</h3>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Date</span>
                <span class="font-medium">{{ $order->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Payment</span>
                <span class="font-medium uppercase">
                    @if($order->payment_method === 'cod')
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Cash on Delivery</span>
                    @else
                        {{ $order->payment_method }}
                    @endif
                </span>
            </div>
            @if($order->payment_method === 'cod')
                <div class="my-3 p-3 rounded-xl bg-amber-50/80 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2">
                    <span class="text-base shrink-0">💵</span>
                    <div>
                        <strong>Cash on Delivery:</strong> Please keep <strong>₹{{ number_format($order->total_amount / 100, 2) }}</strong> ready in cash to hand to the delivery courier rider upon delivery.
                    </div>
                </div>
            @elseif($order->payment_method === 'upi' && $order->payment_status !== 'paid')
                <div class="my-3 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📸</span>
                        <div>
                            <strong>UPI Payment Proof Pending:</strong>
                            <p class="text-[11px] text-emerald-800">Submit your UTR number or screenshot so the merchant can verify & dispatch your order.</p>
                        </div>
                    </div>
                    <a href="{{ route('checkout.payment', $order) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs whitespace-nowrap shadow-xs">
                        Upload Proof ↗
                    </a>
                </div>
            @endif
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Status</span>
                <span class="font-medium capitalize text-orange-500">{{ $order->status }}</span>
            </div>
            <div class="flex justify-between text-base font-bold mt-4 pt-4 border-t border-gray-100">
                <span>Total</span>
                <span>₹{{ number_format($order->total_amount / 100, 2) }}</span>
            </div>
        </div>
        
        <div class="flex gap-4 justify-center">
            <a href="{{ route('orders.index') }}" class="btn-primary">View My Orders</a>
            <a href="{{ route('catalog.index') }}" class="btn-secondary">Continue Shopping</a>
        </div>
    </div>
</x-layouts.public>
