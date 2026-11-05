@php
    $title = $section['title'] ?? 'Frequently Asked Questions';
    $items = $section['items'] ?? [];
@endphp

@if(!empty($items))
<section id="{{ $section['id'] }}" 
         data-section-id="{{ $section['id'] }}"
         class="py-12 sm:py-16 border-b border-stone-200/60"
         style="background-color: var(--sf-bg, #fafaf9);">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-center text-stone-900 font-heading mb-8 sm:mb-10">
            {{ $title }}
        </h2>

        <div class="space-y-3" x-data="{ activeIndex: null }">
            @foreach($items as $idx => $faq)
                <div class="rounded-xl border border-stone-200 bg-white overflow-hidden shadow-2xs">
                    <button type="button" 
                            @click="activeIndex = (activeIndex === {{ $idx }} ? null : {{ $idx }})"
                            class="w-full py-4 px-5 text-left flex items-center justify-between text-xs sm:text-sm font-semibold text-stone-900 hover:text-stone-700 transition">
                        <span>{{ $faq['q'] ?? '' }}</span>
                        <span class="text-stone-400 text-base font-bold ml-4 transition-transform duration-200"
                              :class="activeIndex === {{ $idx }} ? 'rotate-180' : ''">
                            ⌄
                        </span>
                    </button>

                    <div x-show="activeIndex === {{ $idx }}" 
                         x-collapse
                         class="px-5 pb-4 pt-1 text-xs sm:text-sm text-stone-600 border-t border-stone-100 leading-relaxed"
                         style="display: none;">
                        {{ $faq['a'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

