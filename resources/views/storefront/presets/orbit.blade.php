{{-- PRESET 05: ORBIT (Immersive Modern Shopping · Tactile Glass Controls · Subtle Dimensional Details) --}}
<div class="storefront-preset-orbit min-h-screen flex flex-col font-body selection:bg-sky-500 selection:text-white bg-[#ffffff] text-[#0f172a]"
     style="--sf-accent: #0284c7;">

    {{-- Modern Tactile Frosted Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 space-y-6 sm:space-y-10">
        {{-- Customizable Hero Banner / Campaign Carousel --}}
        @include('storefront.sections.hero_carousel')

        {{-- Tactile Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product Catalog Grid (Dominant Shopping Focus) --}}
        @include('storefront.sections.featured_products', ['section' => ['title' => 'Catalog', 'columns' => 4, 'aspect_ratio' => '3:4']])

        {{-- Verified Shopping Trust Section (Real Capabilities Only) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="p-8 sm:p-10 rounded-3xl bg-slate-50/80 border border-slate-200/80 shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm shrink-0 border border-sky-200">
                            ⚡
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">Direct Dispatch</h4>
                            <p class="text-xs text-slate-500 mt-1">Directly packaged and dispatched from {{ $vendor->city ?: 'the vendor studio' }}.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0 border border-emerald-200">
                            🛡️
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">100% Genuine</h4>
                            <p class="text-xs text-slate-500 mt-1">Verified seller backed by K71 marketplace buyer protection.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0 border border-indigo-200">
                            ↺
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">Smooth Exchange</h4>
                            <p class="text-xs text-slate-500 mt-1">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct seller replacement support.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center font-bold text-sm shrink-0 border border-green-200">
                            💬
                        </div>
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-heading">WhatsApp Support</h4>
                            <p class="text-xs text-slate-500 mt-1">Chat directly with the store owner for sizing and assistance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Customer Reviews (Only real reviews, omitted if empty) --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Contact Desk --}}
        @include('storefront.sections.contact_policies')
    </main>

    {{-- Minimal Tactile Modern Footer --}}
    @include('storefront.sections.footer')

</div>

