{{-- Digital Gig & Freelance Milestone Package Partial (Fiverr / Upwork Pattern) --}}
@php
    $packages = $product->gigPackages->keyBy('tier');
    $tiers = ['basic' => 'Basic', 'standard' => 'Standard', 'premium' => 'Premium'];
    $defaultTier = $packages->has('standard') ? 'standard' : ($packages->keys()->first() ?? 'basic');
@endphp

<div class="space-y-5" x-data="gigPackageSelector()">
    <!-- 1. Tier Navigation Tabs -->
    <div class="grid grid-cols-3 rounded-2xl bg-stone-100 p-1 border border-stone-200">
        @foreach($tiers as $key => $label)
            @if(isset($packages[$key]))
                <button type="button" 
                        @click="activeTier = '{{ $key }}'"
                        :class="activeTier === '{{ $key }}' ? 'bg-white text-stone-900 shadow-sm font-black' : 'text-stone-600 hover:text-stone-900 font-semibold'"
                        class="py-2.5 px-3 rounded-xl text-xs transition-all cursor-pointer text-center relative">
                    <span>{{ $label }}</span>
                    @if($key === 'standard')
                        <span class="hidden sm:inline-block ml-1 text-[8px] bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.2 rounded-full uppercase">Popular</span>
                    @endif
                </button>
            @endif
        @endforeach
    </div>

    <!-- 2. Active Package Detail Card -->
    @foreach($tiers as $key => $label)
        @if(isset($packages[$key]))
            @php $pkg = $packages[$key]; @endphp
            <div x-show="activeTier === '{{ $key }}'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-2xl border-2 {{ $key === 'standard' ? 'border-indigo-600 bg-indigo-50/10' : 'border-stone-200 bg-white' }} p-5 shadow-sm space-y-4">
                
                <!-- Title & Price Header -->
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-stone-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-black text-stone-900 uppercase tracking-wide">{{ $pkg->title ?: $label . ' Package' }}</h4>
                            @if($key === 'standard')
                                <span class="text-[9px] font-black uppercase tracking-wider bg-indigo-600 text-white px-2 py-0.5 rounded-full">Recommended</span>
                            @endif
                        </div>
                        <p class="text-xs text-stone-500 mt-1 leading-relaxed">{{ $pkg->description ?: 'Complete milestone package deliverable.' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-2xl font-black text-stone-900 font-serif">₹{{ number_format((float)$pkg->price, 2) }}</span>
                    </div>
                </div>

                <!-- Delivery & Revisions Badge Row -->
                <div class="grid grid-cols-2 gap-2 text-xs font-bold text-stone-700">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-stone-50 border border-stone-200/60">
                        <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ $pkg->delivery_days }} Days Delivery</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-stone-50 border border-stone-200/60">
                        <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>{{ $pkg->revisions >= 99 ? 'Unlimited' : $pkg->revisions }} Revisions</span>
                    </div>
                </div>

                <!-- Deliverable Feature Checklist -->
                <div class="space-y-2 text-xs pt-2">
                    <div class="font-bold uppercase tracking-wider text-stone-600 text-[10px]">What is included:</div>
                    @php $features = $pkg->features_json ?? []; @endphp
                    <div class="grid grid-cols-1 gap-1.5">
                        <div class="flex items-center gap-2 {{ !empty($features['source_file']) ? 'text-stone-800 font-semibold' : 'text-stone-400 line-through' }}">
                            <span class="{{ !empty($features['source_file']) ? 'text-emerald-600' : 'text-stone-300' }}">✓</span>
                            <span>Source Files (Figma / Raw / Vector)</span>
                        </div>
                        <div class="flex items-center gap-2 {{ !empty($features['commercial_rights']) ? 'text-stone-800 font-semibold' : 'text-stone-400 line-through' }}">
                            <span class="{{ !empty($features['commercial_rights']) ? 'text-emerald-600' : 'text-stone-300' }}">✓</span>
                            <span>Commercial License &amp; Full Copyright</span>
                        </div>
                        <div class="flex items-center gap-2 {{ !empty($features['high_resolution']) ? 'text-stone-800 font-semibold' : 'text-stone-400 line-through' }}">
                            <span class="{{ !empty($features['high_resolution']) ? 'text-emerald-600' : 'text-stone-300' }}">✓</span>
                            <span>High-Resolution Print-Ready Assets (300 DPI)</span>
                        </div>
                    </div>
                </div>

                <!-- Package Order CTA -->
                <button type="button" 
                        @click="orderGigPackage({{ $pkg->id }}, '{{ $label }}', {{ (float)$pkg->price }})"
                        class="w-full py-3.5 px-6 rounded-full bg-stone-900 hover:bg-black text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                    <span>Continue (₹{{ number_format((float)$pkg->price, 2) }})</span>
                    <span>→</span>
                </button>
            </div>
        @endif
    @endforeach
</div>

<script>
    function gigPackageSelector() {
        return {
            activeTier: @json($defaultTier),

            orderGigPackage(packageId, tierLabel, price) {
                // Send directly into cart controller with gig_package_id
                fetch('/cart/add', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: {{ $product->id }},
                        gig_package_id: packageId,
                        quantity: 1
                    })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        window.dispatchEvent(new CustomEvent('cart-updated'));
                        window.dispatchEvent(new CustomEvent('open-mini-cart'));
                    } else {
                        alert(data.message || 'Could not select package.');
                    }
                }).catch(e => {
                    alert('Error connecting to checkout.');
                });
            }
        };
    }
</script>
