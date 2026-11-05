{{-- PRESET 03: AUREL (Refined Light Minimalism · Soft Surfaces · Precise Product Grids) --}}
<div class="storefront-preset-aurel min-h-screen flex flex-col font-body selection:bg-slate-200 selection:text-slate-900 bg-[#f9fafb] text-[#0f172a]"
     style="--sf-accent: #0f172a;">

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Refined Light Minimalist Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 space-y-6 sm:space-y-10">
        {{-- Customizable Hero Banner / Campaign Carousel --}}
        @include('storefront.sections.hero_carousel')

        {{-- Minimalist Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product Catalog Grid (Dominant Geometric Focus) --}}
        @include('storefront.sections.featured_products', ['section' => ['title' => 'Catalog', 'columns' => 4, 'aspect_ratio' => '1:1']])

        {{-- Verified Shopping Trust Section (Real Capabilities Only) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="p-8 sm:p-10 rounded-3xl bg-white border border-stone-200/80 shadow-2xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center font-bold text-sm shrink-0 border border-stone-200">
                            ✦
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900">Direct From Source</h4>
                            <p class="text-xs text-stone-500 mt-1">Dispatched directly from {{ $vendor->city ?: 'the vendor workshop' }}.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center font-bold text-sm shrink-0 border border-stone-200">
                            ✓
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900">Verified Seller</h4>
                            <p class="text-xs text-stone-500 mt-1">Genuine products backed by K71 marketplace protection.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center font-bold text-sm shrink-0 border border-stone-200">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900">Express Delivery</h4>
                            <p class="text-xs text-stone-500 mt-1">Trackable doorstep delivery across all Indian pin codes.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-900 flex items-center justify-center font-bold text-sm shrink-0 border border-stone-200">
                            ↺
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900">Smooth Exchange</h4>
                            <p class="text-xs text-stone-500 mt-1">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct seller replacement support.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Customer Reviews (Only real reviews, omitted if empty) --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Desk --}}
        @include('storefront.sections.contact_policies')
    </main>

    {{-- Minimalist Clean Footer --}}
    @include('storefront.sections.footer')

</div>
