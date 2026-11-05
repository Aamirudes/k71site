{{-- PRESET 03: COMMERCE (Conversion-Focused · High Discovery · Product-Rich) --}}
<div class="storefront-preset-commerce min-h-screen flex flex-col font-body selection:bg-blue-100 selection:text-blue-900"
     style="background-color: var(--sf-bg, #ffffff); color: var(--sf-text, #0f172a);">

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- High-Discovery Sticky Search Header --}}
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

    {{-- High-Velocity Store Footer --}}
    @include('storefront.sections.footer')

</div>

