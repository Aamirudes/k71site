{{-- PRESET 04: MARKET (Warm Artisan · Independent Brands · Local Businesses) --}}
<div class="storefront-preset-market min-h-screen flex flex-col font-body selection:bg-amber-100 selection:text-amber-900"
     style="background-color: var(--sf-bg, #fdfbf7); color: var(--sf-text, #27272a);">

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Warm Artisan Header --}}
    @include('storefront.sections.header')

    {{-- Dynamic Sections Loop --}}
    <main class="flex-1">
        @foreach($config['sections'] ?? [] as $section)
            @if(!empty($section['visible']))
                @php
                    $secView = 'storefront.sections.' . ($section['type'] ?? '');
                @endphp
                @if(view()->exists($secView))
                    @include($secView, ['section' => $section])
                @endif
            @endif
        @endforeach
    </main>

    {{-- Warm Heritage Store Footer --}}
    @include('storefront.sections.footer')

</div>

