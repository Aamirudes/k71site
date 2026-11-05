{{-- PRESET 02: NOIRÉ (Premium Dark Commerce · Charcoal Surfaces · Dramatic Product Presentation) --}}
<div class="storefront-preset-noire min-h-screen flex flex-col font-body selection:bg-white selection:text-black bg-[#121214] text-[#f4f4f5]"
     style="--sf-accent: #f4f4f5;">

    {{-- Top Sleek Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Dark Charcoal Floating Header Dock --}}
    @include('storefront.sections.header')

    <main class="flex-1 space-y-6 sm:space-y-10">
        {{-- Customizable Hero Banner / Campaign Carousel --}}
        @include('storefront.sections.hero_carousel')

        {{-- Dark Category Discovery Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product Catalog Grid (Dominant Focus) --}}
        @include('storefront.sections.featured_products', ['section' => ['title' => 'Current Releases', 'columns' => 4, 'aspect_ratio' => '3:4']])

        {{-- Verified Shopping Trust Section (Real Capabilities Only) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="p-8 sm:p-10 rounded-3xl bg-[#18181b] border border-zinc-800 shadow-xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-zinc-800 text-white flex items-center justify-center font-mono font-bold text-xs shrink-0 border border-zinc-700">
                            01
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Direct Workshop</h4>
                            <p class="text-xs text-zinc-400 mt-1">Dispatched directly from {{ $vendor->city ?: 'the vendor studio' }}.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-zinc-800 text-white flex items-center justify-center font-mono font-bold text-xs shrink-0 border border-zinc-700">
                            02
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Verified Authenticity</h4>
                            <p class="text-xs text-zinc-400 mt-1">100% genuine seller backed by K71 marketplace buyer protection.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-zinc-800 text-white flex items-center justify-center font-mono font-bold text-xs shrink-0 border border-zinc-700">
                            03
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Secure Doorstep Transit</h4>
                            <p class="text-xs text-zinc-400 mt-1">Trackable pan-India delivery with real-time courier updates.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-zinc-800 text-white flex items-center justify-center font-mono font-bold text-xs shrink-0 border border-zinc-700">
                            04
                        </div>
                        <div>
                            <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Merchant Exchange</h4>
                            <p class="text-xs text-zinc-400 mt-1">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct exchange assistance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Customer Reviews (Only real reviews, omitted if empty) --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Concierge Desk --}}
        @include('storefront.sections.contact_policies')
    </main>

    {{-- Minimal Dark Boutique Footer --}}
    @include('storefront.sections.footer')

</div>
