{{-- PRESET 02: OBJECT (Minimal Contemporary · Premium Products · Lifestyle) --}}
<div class="storefront-preset-object min-h-screen flex flex-col font-body selection:bg-sky-100 selection:text-sky-900"
     style="background-color: var(--sf-bg, #f8fafc); color: var(--sf-text, #0f172a);">

    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    {{-- Branded Storefront Header --}}
    @include('storefront.sections.header')

    {{-- Dynamic Sections Loop with Clean Minimal Layout --}}
    <main class="flex-1 divide-y divide-stone-200/50">
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

