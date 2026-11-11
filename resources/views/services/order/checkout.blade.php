<x-layouts.public 
    :title="'Order ' . $product->title . ' — K71 Services Checkout'"
    :metaDescription="'Customize your project order and submit requirements on K71 Services.'"
    :fullWidth="true"
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
         class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN CHECKOUT HEADER -->
        <section class="grail-hero-band py-8 sm:py-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-5xl mx-auto">
                <a href="{{ route('services.show', $product->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#ECD2F3] hover:text-white transition mb-3">
                    ← Back to Service Details
                </a>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="grail-eyebrow">Escrow Protected Checkout</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-light text-white tracking-tight leading-[1.06]" style="font-family: var(--font-grail-display);">
                    Order Customization &amp; Requirements
                </h1>
                <p class="text-xs sm:text-sm text-[#EFE9F2]/70 mt-2 max-w-2xl">
                    Review your package, select optional extras, and share your project instructions.
                </p>
            </div>
        </section>

        <!-- CURVED STONE EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 pb-20 px-4 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-5xl mx-auto">

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
                            <div class="grail-card p-6 bg-white border border-[#E6E2E8] rounded-3xl space-y-4">
                                <div class="flex items-center gap-4">
                                    <img src="{{ $primaryImg }}" alt="{{ $product->title }}" class="w-16 h-16 rounded-2xl object-cover border border-[#E6E2E8] shrink-0">
                                    <div>
                                        <div class="grail-eyebrow !text-[#623562]">
                                            {{ ucfirst($tier) }} Package Selected
                                        </div>
                                        <h3 class="font-bold text-sm sm:text-base text-[#1A1018] leading-snug mt-0.5">{{ $product->title }}</h3>
                                        <div class="text-xs text-[#1A1018]/60 mt-1">Provider: <span class="font-bold text-[#1A1018]">{{ $vendor?->shop_name }}</span></div>
                                    </div>
                                </div>

                                @if($package)
                                    <div class="p-4 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs space-y-2">
                                        <div class="flex items-center justify-between font-bold text-[#1A1018]">
                                            <span>{{ $package->title }}</span>
                                            <span class="text-[#623562]">₹{{ number_format($package->price) }}</span>
                                        </div>
                                        <p class="text-[#1A1018]/70 leading-relaxed">{{ $package->description }}</p>
                                        <div class="flex items-center gap-4 font-bold text-[#1A1018]/80 pt-1">
                                            <span>⏱️ {{ $package->delivery_days }} Days Delivery</span>
                                            <span>🔄 {{ $package->revisions }} Revisions</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- OPTIONAL ADD-ONS CHECKBOXES -->
                            @if(!empty($addons))
                                <div class="grail-card p-6 bg-white border border-[#E6E2E8] rounded-3xl space-y-4">
                                    <h3 class="text-xs font-bold text-[#1A1018]/60 uppercase tracking-[0.14em]">
                                        Upgrade Your Order (Optional Extras)
                                    </h3>

                                    <div class="space-y-3">
                                        @foreach($addons as $addon)
                                            <label class="p-4 rounded-2xl border border-[#E6E2E8] hover:border-[#623562]/45 bg-[#FAFAF8] transition flex items-start justify-between gap-3 cursor-pointer">
                                                <div class="flex items-start gap-3">
                                                    <input type="checkbox" name="selected_addons[]" value="{{ $addon['name'] }}"
                                                           x-model="selectedAddons"
                                                           class="w-4 h-4 mt-0.5 rounded text-[#623562] focus:ring-[#623562] border-[#E6E2E8]">
                                                    <div>
                                                        <div class="text-xs font-bold text-[#1A1018]">{{ $addon['name'] }}</div>
                                                        @if(!empty($addon['description']))
                                                            <div class="text-[11px] text-[#1A1018]/60 mt-0.5 leading-relaxed">{{ $addon['description'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-xs font-bold text-[#623562] shrink-0">
                                                    +₹{{ number_format($addon['price'] ?? 0) }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- PROJECT REQUIREMENTS INPUT -->
                            <div class="grail-card p-6 bg-white border border-[#E6E2E8] rounded-3xl space-y-4">
                                <div>
                                    <h3 class="text-xs font-bold text-[#1A1018]/60 uppercase tracking-[0.14em]">Project Instructions</h3>
                                    <p class="text-xs text-[#1A1018]/65 mt-1">{{ $product->requirements_prompt ?? 'Provide exact specifications, color preferences, copy, or instructions.' }}</p>
                                </div>

                                <div>
                                    <textarea name="requirements" rows="6" required
                                              placeholder="Example: I need a clean minimalist logo for my architecture firm named 'Apex Studios'. We prefer modern orchid and charcoal colors. Attached is our company brief..."
                                              class="grail-input-light w-full p-4 rounded-2xl text-xs sm:text-sm font-medium"></textarea>
                                </div>

                                <!-- FILE ATTACHMENTS -->
                                <div>
                                    <label class="block text-xs font-bold text-[#1A1018]/75 mb-2">Reference Files / Assets (Optional)</label>
                                    <div class="p-6 rounded-2xl border-2 border-dashed border-[#ECD2F3] bg-[#F8EFFA]/45 text-center hover:bg-[#F8EFFA] transition cursor-pointer relative">
                                        <input type="file" name="reference_files[]" multiple
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        <div class="space-y-1">
                                            <svg class="w-8 h-8 text-[#623562] mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <div class="text-xs font-bold text-[#1A1018]">Click or drag files to upload</div>
                                            <div class="text-[10px] text-[#1A1018]/50">PDF, ZIP, PNG, JPG, DOC, FIG up to 10MB</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN (5 COLS): SUMMARY & PAYMENT BREAKDOWN -->
                        <div class="lg:col-span-5 sticky top-6 space-y-6">

                            <div class="grail-card p-6 sm:p-7 rounded-3xl bg-white border-2 border-[#ECD2F3] shadow-sm space-y-6">
                                <h3 class="text-base font-bold text-[#1A1018] tracking-tight">Order Summary</h3>

                                <!-- Price Breakdown -->
                                <div class="space-y-3 text-xs">
                                    <div class="flex items-center justify-between text-[#1A1018]/70">
                                        <span>{{ ucfirst($tier) }} Service Package</span>
                                        <span class="font-bold text-[#1A1018]">₹{{ number_format($selectedPrice) }}</span>
                                    </div>

                                    <div class="flex items-center justify-between text-[#1A1018]/70" x-show="selectedAddons.length > 0">
                                        <span>Selected Add-ons (<span x-text="selectedAddons.length"></span>)</span>
                                        <span class="font-bold text-[#1A1018]" x-text="'₹' + (total - basePrice).toLocaleString('en-IN')"></span>
                                    </div>

                                    <div class="flex items-center justify-between text-emerald-700 font-semibold">
                                        <span>Milestone Escrow Protection</span>
                                        <span>FREE</span>
                                    </div>

                                    <div class="pt-3 border-t border-[#E6E2E8] flex items-baseline justify-between">
                                        <span class="text-sm font-bold text-[#1A1018]">Total Payable</span>
                                        <span class="text-2xl font-bold text-[#623562]" x-text="'₹' + Number(total).toLocaleString('en-IN')"></span>
                                    </div>
                                </div>

                                <!-- Trust Guarantee Notice -->
                                <div class="p-4 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs text-[#1A1018] space-y-1.5 leading-relaxed">
                                    <div class="font-bold flex items-center gap-1.5 text-[#623562]">
                                        <span>🔒 100% Protected Transaction</span>
                                    </div>
                                    <div class="text-[11px] text-[#1A1018]/70">
                                        Your funds are held securely in project escrow. The provider gets paid only after you inspect and approve the completed deliverables.
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit"
                                        class="grail-btn-obsidian w-full py-4 text-xs uppercase tracking-[0.14em] text-center flex items-center justify-center gap-2 cursor-pointer">
                                    <span>Place Order &amp; Start Workspace →</span>
                                </button>

                                <div class="text-[10px] text-center text-[#1A1018]/50">
                                    By placing this order, you agree to the K71 Services Terms of Collaboration.
                                </div>
                            </div>

                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>
</x-layouts.public>
