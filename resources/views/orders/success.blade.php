<x-layouts.public title="Order Successful" :fullWidth="true">
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 pb-14 text-center">
            <div class="w-20 h-20 bg-[#ECD2F3] text-[#1A1018] rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_40px_rgba(236,210,243,0.35)]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            
            <span class="grail-eyebrow block mb-2">Order Confirmed &bull; Direct Independent Commerce</span>
            <h1 class="grail-display text-[clamp(2.2rem,5vw,3.4rem)] font-bold text-white mb-3">Order Placed Successfully!</h1>
            <p class="text-white/75 mb-1 text-sm sm:text-base">Thank you for shopping directly with independent brands.</p>
            <p class="text-white/60 text-sm">Your order <span class="font-bold text-[#ECD2F3] font-mono">{{ $order->order_number }}</span> has been sent to <strong class="text-white">{{ $order->vendor?->shop_name ?? 'K71 Direct Store' }}</strong>.</p>
        </div>
    </section>

    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] min-h-[65vh]">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14 text-center">
            <div class="bg-white rounded-[24px] p-6 sm:p-8 border border-black/8 text-left mb-8 shadow-[0_14px_40px_rgba(26,16,24,0.07)]">
                <h3 class="grail-display text-xl font-bold text-[#1A1018] mb-4 border-b border-black/8 pb-4">Order Summary</h3>
                <div class="flex justify-between text-sm mb-2.5">
                    <span class="text-[#7A6B78]">Date</span>
                    <span class="font-medium text-[#1A1018]">{{ $order->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2.5">
                    <span class="text-[#7A6B78]">Payment</span>
                    <span class="font-medium uppercase text-[#1A1018]">
                        @if($order->payment_method === 'cod')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Cash on Delivery</span>
                        @else
                            {{ $order->payment_method }}
                        @endif
                    </span>
                </div>
                @if($order->payment_method === 'cod')
                    <div class="my-3.5 p-3.5 rounded-xl bg-amber-50/80 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2">
                        <span class="text-base shrink-0">💵</span>
                        <div>
                            <strong>Cash on Delivery:</strong> Please keep <strong>₹{{ number_format($order->total_amount / 100, 2) }}</strong> ready in cash to hand to the delivery courier rider upon delivery.
                        </div>
                    </div>
                @elseif($order->payment_method === 'upi' && $order->payment_status !== 'paid')
                    <div class="my-3.5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">📸</span>
                            <div>
                                <strong>UPI Payment Proof Pending:</strong>
                                <p class="text-[11px] text-emerald-800">Submit your UTR number or screenshot so the merchant can verify & dispatch your order.</p>
                            </div>
                        </div>
                        <a href="{{ route('checkout.payment', $order) }}" class="px-3.5 py-2 rounded-xl bg-[#1A1018] hover:bg-black text-[#ECD2F3] font-bold text-xs whitespace-nowrap shadow-xs transition-colors">
                            Upload Proof ↗
                        </a>
                    </div>
                @endif
                <div class="flex justify-between text-sm mb-2.5">
                    <span class="text-[#7A6B78]">Status</span>
                    <span class="font-bold capitalize text-[#623562]">{{ $order->status }}</span>
                </div>
                <div class="flex justify-between items-baseline text-base font-bold mt-4 pt-4 border-t border-black/8">
                    <span class="text-[#1A1018]">Total</span>
                    <span class="grail-display text-2xl font-bold text-[#1A1018] tabular-nums">₹{{ number_format($order->total_amount / 100, 2) }}</span>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3.5 justify-center">
                <a href="{{ route('orders.index') }}" class="grail-btn-obsidian">View My Orders</a>
                <a href="{{ route('catalog.index') }}" class="grail-btn-ghost-dark">Continue Shopping</a>
            </div>
        </div>
    </div>
</x-layouts.public>
