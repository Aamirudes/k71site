@php
    $hasReviews = isset($vendorReviews) && $vendorReviews->isNotEmpty();
    $avgRating = $hasReviews ? round($vendorReviews->avg('rating'), 1) : 0;
    $totalCount = $hasReviews ? $vendorReviews->count() : 0;
@endphp

@if($hasReviews)
<section id="reviews" class="py-10 sm:py-14 border-t border-stone-200/60 dark:border-white/10 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Reviews Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-stone-500 dark:text-stone-400 mb-1">
                    <span>★ Customer Feedback</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-stone-900 dark:text-white font-heading">
                    Verified Customer Reviews
                </h2>
            </div>

            <!-- Average Rating Summary -->
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/80 dark:bg-zinc-900/80 border border-stone-200/80 dark:border-white/10 shadow-2xs">
                <div class="text-2xl font-black text-stone-950 dark:text-white font-heading">
                    {{ number_format($avgRating, 1) }}
                </div>
                <div class="text-xs">
                    <div class="flex text-amber-400 text-sm">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= round($avgRating) ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <span class="text-stone-500 dark:text-stone-400 font-medium">
                        Based on {{ $totalCount }} {{ Str::plural('verified review', $totalCount) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Reviews Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vendorReviews as $review)
                <div class="p-6 rounded-3xl bg-white/90 dark:bg-zinc-900/80 border border-stone-200/80 dark:border-white/10 shadow-xs flex flex-col justify-between space-y-4">
                    <div>
                        <!-- Rating & Verified Badge -->
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex text-amber-400 text-xs tracking-wider">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            @if($review->is_verified_purchase)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    ✓ Verified Purchase
                                </span>
                            @endif
                        </div>

                        <!-- Review Text -->
                        @if(!empty($review->title))
                            <h4 class="text-sm font-bold text-stone-900 dark:text-white mb-1">
                                {{ $review->title }}
                            </h4>
                        @endif
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                            {{ $review->body }}
                        </p>

                        <!-- Product Reference -->
                        @if($review->product)
                            <div class="mt-3 pt-3 border-t border-stone-100 dark:border-zinc-800 text-[11px] text-stone-500 dark:text-stone-400 flex items-center gap-1.5">
                                <span class="opacity-75">Item:</span>
                                <a href="{{ route('product.show', $review->product->slug) }}" class="font-semibold text-stone-800 dark:text-stone-200 hover:underline truncate">
                                    {{ $review->product->title }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Reviewer Info -->
                    <div class="pt-2 flex items-center justify-between text-xs text-stone-400">
                        <span class="font-bold text-stone-700 dark:text-stone-300">
                            {{ $review->user ? ($review->user->first_name ?: $review->user->name) : 'Verified Buyer' }}
                        </span>
                        <span>{{ $review->created_at->format('M d, Y') }}</span>
                    </div>

                    <!-- Optional Vendor Reply -->
                    @if($review->hasVendorReply())
                        <div class="mt-2 p-3 rounded-2xl bg-stone-50 dark:bg-zinc-800/60 border border-stone-200/60 dark:border-zinc-700 text-xs">
                            <span class="font-bold text-stone-900 dark:text-white block mb-0.5">Response from seller:</span>
                            <p class="text-stone-600 dark:text-stone-300">{{ $review->vendor_reply }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
</section>
@else
<section id="reviews" class="py-10 sm:py-14 border-t border-stone-200/60 dark:border-white/10 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-8 sm:p-10 rounded-3xl border border-stone-200/80 dark:border-white/10 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl text-center space-y-3">
            <span class="text-xl sm:text-2xl text-amber-400">★ ★ ★ ★ ★</span>
            <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white font-heading">
                Verified Product Reviews &amp; Merchant Escrow
            </h3>
            <p class="text-xs text-stone-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
                All catalog items from {{ $vendor->shop_name }} are backed by K71 Buyer Protection. Ordered an item? Leave your review directly from your orders dashboard.
            </p>
            <div class="pt-2">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-stone-900 dark:bg-white text-white dark:text-stone-900 font-bold text-xs shadow-xs hover:scale-102 transition">
                    <span>Review Past Orders</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

