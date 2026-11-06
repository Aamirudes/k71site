{{-- PRESET 01: VELORA (100vh Aesthetic Image Carousel · Transparent Sticky Header · Liquid Glass Luxury) --}}
<div class="storefront-preset-velora min-h-screen flex flex-col font-body selection:bg-amber-200 selection:text-amber-950 bg-[#fafaf9] text-[#18181b]"
     style="--sf-accent: #c59b27;">

    {{-- Transparent over 100vh Hero -> Sticky Opaque Glass on Scroll Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 space-y-8 sm:space-y-12">
        {{-- 100vh 3-Slider Swipe Image Carousel --}}
        @include('storefront.sections.hero_carousel')

        {{-- Liquid Glass Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product Catalog Grid (The Main Focus of the Entire Website) --}}
        @include('storefront.sections.featured_products', ['section' => ['title' => 'Boutique Collection', 'columns' => 4, 'aspect_ratio' => '3:4']])

        {{-- Verified Shopping Trust Guarantees (Only real capabilities) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-8 sm:p-10 rounded-3xl bg-white/90 backdrop-blur-xl border border-stone-200/80 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-500/20">
                            ✦
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 font-heading">Direct Studio Dispatch</h4>
                            <p class="text-xs text-stone-500 mt-1">Directly packaged and dispatched from {{ $vendor->city ?: 'the vendor studio' }}.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-500/20">
                            ◈
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 font-heading">Verified Quality Seal</h4>
                            <p class="text-xs text-stone-500 mt-1">100% genuine authentic items verified on K71 Marketplace.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-500/20">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 font-heading">Insured Express Delivery</h4>
                            <p class="text-xs text-stone-500 mt-1">Trackable doorstep transit across India with live tracking updates.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-500/20">
                            ↺
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 font-heading">{{ $vendor->settings?->return_window_days ?: 7 }}-Day Smooth Exchange</h4>
                            <p class="text-xs text-stone-500 mt-1">Direct merchant support for size exchange and order assistance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Customer Reviews (Only real reviews, omitted if empty) --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Stylist Desk --}}
        @include('storefront.sections.contact_policies')
    </main>

    {{-- Minimal Premium Boutique Footer --}}
    @include('storefront.sections.footer')

</div>
