{{-- PRESET 01: ATELIER (Haute Couture Luxury · Liquid Glass & Champagne Gold) --}}
<div class="storefront-preset-atelier min-h-screen flex flex-col font-body relative overflow-x-hidden selection:bg-amber-200 selection:text-amber-950"
     style="background-color: var(--sf-bg, #fafaf9); color: var(--sf-text, #18181b);">

    <!-- Liquid Background Ambient Glow Spheres -->
    <div class="fixed top-0 left-1/4 w-[500px] h-[500px] rounded-full pointer-events-none opacity-20 filter blur-[100px]"
         style="background: radial-gradient(circle, #c59b27 0%, transparent 70%);"></div>
    <div class="fixed bottom-10 right-10 w-[600px] h-[600px] rounded-full pointer-events-none opacity-15 filter blur-[120px]"
         style="background: radial-gradient(circle, #d4af37 0%, transparent 70%);"></div>

    {{-- Top Refined Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Floating Liquid Frosted Header --}}
    @include('storefront.sections.header')

    <main class="flex-1 relative z-10 space-y-6 sm:space-y-10">
        {{-- Flagship Product Showcase Hero --}}
        @include('storefront.sections.hero_editorial', ['section' => $config['sections'][0] ?? []])

        {{-- Liquid Bubble Category Strip --}}
        @include('storefront.sections.category_bubbles')

        {{-- Product-First Discovery Catalog Grid --}}
        @include('storefront.sections.featured_products', ['section' => ['columns' => 4, 'aspect_ratio' => '3:4']])

        {{-- Live Studio Shorts & Video Commerce --}}
        @if(isset($vendorShorts) && $vendorShorts->isNotEmpty())
            @include('storefront.sections.video_showcase')
        @endif

        {{-- Liquid Glass Trust Guarantee Capsule --}}
        @include('storefront.sections.trust_badges')

        {{-- Client Services & WhatsApp Stylist --}}
        @include('storefront.sections.contact_policies')
    </main>

    {{-- Boutique Glassmorphic Footer --}}
    @include('storefront.sections.footer')

</div>
