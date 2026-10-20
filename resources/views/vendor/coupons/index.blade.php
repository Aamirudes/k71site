<x-layouts.vendor title="Coupons">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">Coupons</h1>
            <p class="mt-2 text-sm text-gray-500">Manage discount codes to boost your sales.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('vendor.coupons.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-sage px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-sage/90 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-sage">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                Create Coupon
            </a>
        </div>
    </div>
    
    @if($coupons->isEmpty())
        <div class="mt-8 bg-white/60 backdrop-blur-xl border border-gray-100 shadow-sm rounded-3xl p-12 text-center">
            <div class="w-20 h-20 bg-brand-sage/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="h-10 w-10 text-brand-sage" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <h3 class="text-xl font-serif font-medium text-gray-900 mb-2">No coupons created yet</h3>
            <p class="text-gray-500 max-w-sm mx-auto mb-8">Offer special discounts to your customers to increase conversion rates.</p>
            <a href="{{ route('vendor.coupons.create') }}" class="btn-primary inline-flex px-8 py-3 rounded-xl shadow-md">
                Create First Coupon
            </a>
        </div>
    @else
        <!-- Mobile Card Layout -->
        <div class="md:hidden space-y-4">
            @foreach($coupons as $coupon)
                @php
                    $isActive = $coupon->is_active && \Carbon\Carbon::now()->between($coupon->starts_at, $coupon->expires_at);
                    if ($coupon->total_limit && $coupon->usage_count >= $coupon->total_limit) {
                        $isActive = false;
                    }
                @endphp
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-4 relative overflow-hidden">
                    @if(!$isActive)
                        <div class="absolute inset-0 bg-gray-50/50 pointer-events-none"></div>
                    @endif
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-mono text-lg font-bold text-gray-900 tracking-wider bg-gray-50 px-2 py-1 rounded inline-block">{{ $coupon->code }}</div>
                            <div class="text-xs text-gray-500 mt-2 uppercase tracking-wide font-semibold">{{ ucfirst($coupon->type) }} Discount</div>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $isActive ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 bg-gray-50/50 rounded-xl p-4 border border-gray-50">
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">Value</p>
                            <p class="text-base font-bold text-gray-900">
                                @if($coupon->type === 'percentage')
                                    {{ $coupon->value }}%
                                @else
                                    ₹{{ number_format($coupon->value / 100, 2) }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">Usage</p>
                            <p class="text-base font-bold text-gray-900">
                                {{ $coupon->usage_count }} <span class="text-gray-400 font-normal text-sm">{{ $coupon->total_limit ? '/ '.$coupon->total_limit : '' }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mt-2 pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-500">
                            Valid until {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d M y') }}
                        </div>
                        <div class="flex gap-4">
                            <a href="{{ route('vendor.coupons.edit', $coupon) }}" class="text-sm font-semibold text-brand-sage hover:text-brand-sage/80">Edit</a>
                            <form action="{{ route('vendor.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Desktop Table Layout -->
        <div class="hidden md:block bg-white shadow-sm ring-1 ring-black ring-opacity-5 rounded-2xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Code & Type</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usage</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Validity</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative py-4 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($coupons as $coupon)
                        @php
                            $isActive = $coupon->is_active && \Carbon\Carbon::now()->between($coupon->starts_at, $coupon->expires_at);
                            if ($coupon->total_limit && $coupon->usage_count >= $coupon->total_limit) {
                                $isActive = false;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors {{ !$isActive ? 'bg-gray-50/30' : '' }}">
                            <td class="whitespace-nowrap py-5 pl-6 pr-3">
                                <div class="font-mono text-sm font-bold text-gray-900 tracking-wider">{{ $coupon->code }}</div>
                                <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide font-medium">{{ ucfirst($coupon->type) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm">
                                <div class="font-bold text-gray-900">
                                    @if($coupon->type === 'percentage')
                                        {{ $coupon->value }}%
                                    @else
                                        ₹{{ number_format($coupon->value / 100, 2) }}
                                    @endif
                                </div>
                                @if($coupon->max_discount && $coupon->type === 'percentage')
                                    <div class="text-xs text-gray-500 mt-1">Up to ₹{{ $coupon->max_discount / 100 }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm">
                                <span class="font-bold text-gray-900">{{ $coupon->usage_count }}</span>
                                @if($coupon->total_limit)
                                    <span class="text-gray-400">/ {{ $coupon->total_limit }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                <div class="text-xs space-y-1">
                                    <div><span class="font-medium text-gray-400 w-10 inline-block">From:</span> {{ \Carbon\Carbon::parse($coupon->starts_at)->format('d M Y') }}</div>
                                    <div><span class="font-medium text-gray-400 w-10 inline-block">To:</span> {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y') }}</div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-5 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="relative whitespace-nowrap py-5 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-4">
                                    <a href="{{ route('vendor.coupons.edit', $coupon) }}" class="text-brand-sage hover:text-brand-sage/80 transition-colors">Edit</a>
                                    <form action="{{ route('vendor.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-8">
            {{ $coupons->links() }}
        </div>
    @endif
</x-layouts.vendor>
