<x-layouts.public title="About K71 | Curated Marketplace" :fullWidth="true">
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-14 text-center">
            <span class="grail-eyebrow block mb-2.5">Verified Indian Marketplace</span>
            <h1 class="grail-display text-[clamp(2.2rem,5vw,3.5rem)] font-bold text-white tracking-tight">The Story Behind K71</h1>
            <p class="text-white/70 text-xs sm:text-sm mt-3 max-w-xl mx-auto leading-relaxed">
                Empowering independent stores, restaurants, services, and creators with direct access to customers across India.
            </p>
        </div>
    </section>

    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] py-10 sm:py-14 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-[28px] p-6 sm:p-10 border border-black/8 shadow-[0_14px_40px_rgba(26,16,24,0.06)] space-y-8 text-[#463945] text-sm leading-relaxed">
                
                <div class="space-y-3">
                    <h2 class="grail-display text-xl font-bold text-[#1A1018] border-b border-black/8 pb-2">Our Mission</h2>
                    <p>
                        Across India, independent boutiques, cloud kitchens, and skilled service providers offer exceptional products and craftsmanship. Yet, getting discovered online often involves complex intermediaries and high fees.
                    </p>
                    <p>
                        <strong class="text-[#1A1018]">K71 (k71.in)</strong> provides sellers with powerful multi-vendor commerce tools, video commerce, direct logistics, and fair trade compensation with zero unnecessary middlemen.
                    </p>
                </div>

                <!-- Pillars Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="p-5 rounded-2xl bg-[#FAF8F5] border border-black/8 text-center space-y-1.5">
                        <h3 class="font-bold text-[#1A1018] text-sm">Verified Sellers</h3>
                        <p class="text-xs text-[#7A6B78]">Every store is verified for quality and authentic customer service.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-[#FAF8F5] border border-black/8 text-center space-y-1.5">
                        <h3 class="font-bold text-[#1A1018] text-sm">Fair Platform Pricing</h3>
                        <p class="text-xs text-[#7A6B78]">Low seller commissions empower businesses to invest in quality.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-[#FAF8F5] border border-black/8 text-center space-y-1.5">
                        <h3 class="font-bold text-[#1A1018] text-sm">Direct Delivery</h3>
                        <p class="text-xs text-[#7A6B78]">Fast, tracked pan-India courier dispatch straight from the seller to your door.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h2 class="grail-display text-xl font-bold text-[#1A1018] border-b border-black/8 pb-2">Legal Ownership &amp; Registration</h2>
                    <p>
                        K71 operates as an official enterprise registered under the <strong class="text-[#1A1018]">Ministry of Micro, Small &amp; Medium Enterprises (MSME), Government of India</strong>.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 bg-[#FAF8F5] border border-black/8 rounded-xl space-y-1">
                            <span class="text-[#7A6B78] uppercase tracking-wider text-[10px] block font-semibold">MSME Udyam Registration</span>
                            <span class="font-mono font-bold text-[#1A1018] text-sm">UDYAM-RJ-01-0176167</span>
                        </div>
                        <div class="p-3.5 bg-[#FAF8F5] border border-black/8 rounded-xl space-y-1">
                            <span class="text-[#7A6B78] uppercase tracking-wider text-[10px] block font-semibold">Platform Founder &amp; Director</span>
                            <span class="font-bold text-[#1A1018] text-sm">Hashir</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-black/8 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-[#1A1018] text-sm">Interested in Selling on K71?</h3>
                        <p class="text-xs text-[#7A6B78]">Launch your storefront on K71 in under 10 minutes.</p>
                    </div>
                    <a href="{{ route('vendor.register') }}" class="grail-btn-obsidian text-xs">
                        Register as Seller &rarr;
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-layouts.public>
