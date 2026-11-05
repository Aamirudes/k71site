{{-- PRESET 05: SIGNATURE (Bold Modern · Campaign-Led · Distinctive Brands) --}}
<div class="storefront-preset-signature min-h-screen flex flex-col font-body selection:bg-emerald-400 selection:text-black"
     style="background-color: var(--sf-bg, #09090b); color: var(--sf-text, #ffffff);">

    {{-- Bold Announcement Ticker --}}
    @include('storefront.sections.announcement')

    {{-- High-Impact Centered Header --}}
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

    {{-- Minimal High-Contrast Footer --}}
    @include('storefront.sections.footer')

</div>

