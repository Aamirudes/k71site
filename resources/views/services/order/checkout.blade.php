<x-layouts.public 
    :title="'Order ' . $product->title . ' — K71 Services Checkout'"
    :metaDescription="'Customize your project order and submit requirements on K71 Services.'"
>
    @php
        $vendor = $product->vendor;
        $primaryImg = $product->primary_image_url;
        $addons = is_array($product->addons_json) ? $product->addons_json : [];
    @endphp

    <div x-data="{ 
            basePrice: {{ $selectedPrice }},
            selectedAddons: [],
            addonPrices: {
                @foreach($addons as $idx => $addon)
                    '{{ $addon['name'] }}': {{ (float) ($addon['price'] ?? 0) }},
                @endforeach
            },
            get total() {
                let sum = this.basePrice;
                for (let name of this.selectedAddons) {
                    if (this.addonPrices[name]) {
                        sum += this.addonPrices[name];
                    }
                }
                return sum;
            }
         }"
         class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- CHECKOUT HEADER -->
            <div class="mb-8">
                <a href="{{ route('services.show', $product->slug) }}" class="text-xs text-purple-700 hover:text-purple-900 font-bold flex items-center gap-1 mb-2">
                    ← Back to Service Details
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">Order Customization &amp; Requirements</h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">Review your package, select optional extras, and share your project instructions.</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <div class="font-bold">Please correct the following errors:</div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('services.order.place', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tier" value="{{ $tier }}">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- LEFT COLUMN (7 COLS): REQUIREMENTS & ADD-ONS -->
                    <div class="lg:col-span-7 space-y-6">

                        <!-- SELECTED PACKAGE OVERVIEW -->
                        <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-4">
                            <div class="flex items-center gap-4">
                                <img src="{{ $primaryImg }}" alt="{{ $product->title }}" class="w-16 h-16 rounded-2xl object-cover border border-purple-100 shrink-0">
                                <div>
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-purple-700">
                                        {{ ucfirst($tier) }} Package Selected
                                    </div>
                                    <h3 class="font-bold text-sm text-zinc-950 leading-snug">{{ $product->title }}</h3>
                                    <div class="text-xs text-zinc-500 mt-1">Provider: <span class="font-bold text-zinc-800">{{ $vendor?->shop_name }}</span></div>
                                </div>
                            </div>

                            @if($package)
                                <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-100/80 text-xs space-y-2">
                                    <div class="flex items-center justify-between font-bold text-purple-900">
                                        <span>{{ $package->title }}</span>
                                        <span>₹{{ number_format($package->price) }}</span>
                                    </div>
                                    <p class="text-zinc-600 leading-relaxed">{{ $package->description }}</p>
                                    <div class="flex items-center gap-4 font-bold text-zinc-700 pt-1">
                                        <span>⏱️ {{ $package->delivery_days }} Days Delivery</span>
                                        <span>🔄 {{ $package->revisions }} Revisions</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- OPTIONAL ADD-ONS CHECKBOXES -->
                        @if(!empty($addons))
                            <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-4">
                                <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">
                                    Upgrade Your Order (Optional Extras)
                                </h3>

                                <div class="space-y-3">
                                    @foreach($addons as $addon)
                                        <label class="p-4 rounded-2xl border border-purple-100 hover:border-purple-300 bg-white transition flex items-start justify-between gap-3 cursor-pointer">
                                            <div class="flex items-start gap-3">
                                                <input type="checkbox" name="selected_addons[]" value="{{ $addon['name'] }}"
                                                       x-model="selectedAddons"
                                                       class="w-4 h-4 mt-0.5 rounded text-purple-600 focus:ring-purple-500 border-purple-300">
                                                <div>
                                                    <div class="text-xs font-bold text-zinc-900">{{ $addon['name'] }}</div>
                                                    @if(!empty($addon['description']))
                                                        <div class="text-[11px] text-zinc-500 mt-0.5 leading-relaxed">{{ $addon['description'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-xs font-black text-purple-700 shrink-0">
                                                +₹{{ number_format($addon['price'] ?? 0) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- PROJECT REQUIREMENTS INPUT -->
                        <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-4">
                            <div>
                                <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">Project Instructions</h3>
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $product->requirements_prompt ?? 'Provide exact specifications, color preferences, copy, or instructions.' }}</p>
                            </div>

                            <div>
                                <textarea name="requirements" rows="6" required
                                          placeholder="Example: I need a clean minimalist logo for my architecture firm named 'Apex Studios'. We prefer modern purple and charcoal colors. Attached is our company brief..."
                                          class="w-full p-4 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900"></textarea>
                            </div>

                            <!-- FILE ATTACHMENTS -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 mb-2">Reference Files / Assets (Optional)</label>
                                <div class="p-6 rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 text-center hover:bg-purple-50/60 transition cursor-pointer relative">
                                    <input type="file" name="reference_files[]" multiple
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="space-y-1">
                                        <svg class="w-8 h-8 text-purple-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        <div class="text-xs font-bold text-zinc-800">Click or drag files to upload</div>
                                        <div class="text-[10px] text-zinc-400">PDF, ZIP, PNG, JPG, DOC, FIG up to 10MB</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (5 COLS): SUMMARY & PAYMENT BREAKDOWN -->
                    <div class="lg:col-span-5 sticky top-6 space-y-6">

                        <div class="p-6 rounded-3xl bg-white/95 backdrop-blur-xl border-2 border-purple-200/90 shadow-[0_15px_40px_rgba(124,58,237,0.10)] space-y-6">
                            <h3 class="text-base font-black text-zinc-950 tracking-tight">Order Summary</h3>

                            <!-- Price Breakdown -->
                            <div class="space-y-3 text-xs">
                                <div class="flex items-center justify-between text-zinc-600">
                                    <span>{{ ucfirst($tier) }} Service Package</span>
                                    <span class="font-bold text-zinc-900">₹{{ number_format($selectedPrice) }}</span>
                                </div>

                                <div class="flex items-center justify-between text-zinc-600" x-show="selectedAddons.length > 0">
                                    <span>Selected Add-ons (<span x-text="selectedAddons.length"></span>)</span>
                                    <span class="font-bold text-zinc-900" x-text="'₹' + (total - basePrice).toLocaleString('en-IN')"></span>
                                </div>

                                <div class="flex items-center justify-between text-emerald-600 font-semibold">
                                    <span>Milestone Escrow Protection</span>
                                    <span>FREE</span>
                                </div>

                                <div class="pt-3 border-t border-purple-100 flex items-baseline justify-between">
                                    <span class="text-sm font-black text-zinc-950">Total Payable</span>
                                    <span class="text-2xl font-black text-purple-700" x-text="'₹' + Number(total).toLocaleString('en-IN')"></span>
                                </div>
                            </div>

                            <!-- Trust Guarantee Notice -->
                            <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-100 text-xs text-purple-900 space-y-1.5 leading-relaxed">
                                <div class="font-bold flex items-center gap-1.5">
                                    <span>🔒 100% Protected Transaction</span>
                                </div>
                                <div class="text-[11px] text-zinc-600">
                                    Your funds are held securely in project escrow. The provider gets paid only after you inspect and approve the completed deliverables.
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                    class="w-full py-4 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider text-center transition shadow-lg shadow-purple-500/25 flex items-center justify-center gap-2">
                                <span>Place Order &amp; Start Workspace →</span>
                            </button>

                            <div class="text-[10px] text-center text-zinc-400">
                                By placing this order, you agree to the K71 Services Terms of Collaboration.
                            </div>
                        </div>

                    </div>

                </div>
            </form>

        </div>

    </div>
</x-layouts.public>
