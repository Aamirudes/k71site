<x-layouts.public title="About K71 | Curated Marketplace">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6">
        
        <div class="text-center mb-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-100 text-zinc-700 border border-zinc-200 text-xs font-medium mb-3">
                Verified Indian Marketplace
            </span>
            <h1 class="text-2xl sm:text-4xl font-bold text-zinc-950 tracking-tight">The Story Behind K71</h1>
            <p class="text-zinc-500 text-xs sm:text-sm mt-2 max-w-xl mx-auto leading-relaxed">
                Empowering independent stores, restaurants, services, and creators with direct access to customers across India.
            </p>
        </div>

        <div class="bg-white rounded-2xl p-6 sm:p-10 border border-zinc-200/80 shadow-xs space-y-8 text-zinc-700 text-sm leading-relaxed">
            
            <div class="space-y-3">
                <h2 class="text-lg font-bold text-zinc-900 border-b border-zinc-100 pb-2">Our Mission</h2>
                <p>
                    Across India, independent boutiques, cloud kitchens, and skilled service providers offer exceptional products and craftsmanship. Yet, getting discovered online often involves complex intermediaries and high fees.
                </p>
                <p>
                    <strong>K71 (k71.in)</strong> provides sellers with powerful multi-vendor commerce tools, video commerce, direct logistics, and fair trade compensation with zero unnecessary middlemen.
                </p>
            </div>

            <!-- Pillars Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 text-center space-y-1.5">
                    <h3 class="font-semibold text-zinc-900 text-sm">Verified Sellers</h3>
                    <p class="text-xs text-zinc-500">Every store is verified for quality and authentic customer service.</p>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 text-center space-y-1.5">
                    <h3 class="font-semibold text-zinc-900 text-sm">Fair Platform Pricing</h3>
                    <p class="text-xs text-zinc-500">Low seller commissions empower businesses to invest in quality.</p>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 text-center space-y-1.5">
                    <h3 class="font-semibold text-zinc-900 text-sm">Direct Delivery</h3>
                    <p class="text-xs text-zinc-500">Fast, tracked pan-India courier dispatch straight from the seller to your door.</p>
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="text-lg font-bold text-zinc-900 border-b border-zinc-100 pb-2">Legal Ownership &amp; Registration</h2>
                <p>
                    K71 operates as an official enterprise registered under the <strong>Ministry of Micro, Small &amp; Medium Enterprises (MSME), Government of India</strong>.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-xl space-y-1">
                        <span class="text-zinc-500 uppercase tracking-wider text-[10px] block font-medium">MSME Udyam Registration</span>
                        <span class="font-mono font-bold text-zinc-900 text-sm">UDYAM-RJ-01-0176167</span>
                    </div>
                    <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-xl space-y-1">
                        <span class="text-zinc-500 uppercase tracking-wider text-[10px] block font-medium">Proprietor / Bank Account Holder</span>
                        <span class="font-bold text-zinc-900 text-sm">Aamir Suhail</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-zinc-900 text-sm">Interested in Selling on K71?</h3>
                    <p class="text-xs text-zinc-500">Launch your storefront on K71 in under 10 minutes.</p>
                </div>
                <a href="{{ route('vendor.register') }}" class="px-6 py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-semibold hover:bg-black transition-all shadow-xs">
                    Register as Seller &rarr;
                </a>
            </div>

        </div>

    </div>
</x-layouts.public>
