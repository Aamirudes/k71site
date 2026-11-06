{{-- PRESET 04: CHROMA (Bold Campaign Avant-Garde · Distinctive Typography · High-Conversion Flow) --}}
<div class="storefront-preset-chroma min-h-screen flex flex-col font-body selection:bg-blue-600 selection:text-white bg-[#f8fafc] text-[#09090b]"
     style="--sf-accent: #2563eb;">

    {{-- Transparent over 100vh Hero -> Sticky Opaque Header on Scroll --}}
    @include('storefront.sections.header')

    <main class="flex-1 space-y-8 sm:space-y-12">
        {{-- 100vh 3-Slider Swipe Hero Carousel --}}
        @include('storefront.sections.hero_carousel')

        {{-- Product Catalog Grid (Dominant Focus) --}}
        @include('storefront.sections.featured_products', ['section' => ['title' => 'Product Drops', 'columns' => 4, 'aspect_ratio' => '3:4']])

        {{-- Verified Shopping Trust Section (Real Capabilities Only) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-8 sm:p-10 rounded-3xl bg-white border border-stone-200 shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0 border border-blue-200">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">Direct Studio Dispatch</h4>
                            <p class="text-xs text-slate-500 mt-1">Dispatched directly from {{ $vendor->city ?: 'the vendor studio' }}.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0 border border-emerald-200">
                            ✓
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">100% Genuine Quality</h4>
                            <p class="text-xs text-slate-500 mt-1">Verified seller backed by K71 marketplace protection.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0 border border-indigo-200">
                            📦
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">Pan-India Express Transit</h4>
                            <p class="text-xs text-slate-500 mt-1">Fast door-to-door courier tracking across all cities.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-200">
                            ↺
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">{{ $vendor->settings?->return_window_days ?: 7 }}-Day Smooth Exchange</h4>
                            <p class="text-xs text-slate-500 mt-1">Direct merchant support for size and order replacements.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Customer Reviews (Only real reviews, omitted if empty) --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')
    </main>

    {{-- Minimal Bold Footer --}}
    @include('storefront.sections.footer')

</div>
