{{-- PRESET 01: ATELIER (Editorial Luxury · Fashion · Designer Brands) --}}
<div class="storefront-preset-atelier min-h-screen flex flex-col font-body selection:bg-amber-100 selection:text-amber-900"
     style="background-color: var(--sf-bg, #fafaf9); color: var(--sf-text, #18181b);">

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Branded Storefront Header --}}
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

    {{-- Branded Storefront Footer --}}
    @include('storefront.sections.footer')

</div>

