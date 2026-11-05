@php
    $secId = $section['id'] ?? 'trust';
    $title = $section['title'] ?? 'The Buyer Guarantee';
    $items = $section['items'] ?? [
        ['icon' => '✦', 'title' => 'Direct Workshop Dispatch', 'desc' => 'Packaged and authenticated straight from our studio.'],
        ['icon' => '◈', 'title' => '100% Genuine Quality', 'desc' => 'Strict quality inspection before sealing.'],
        ['icon' => '⚡', 'title' => 'Express Insured Delivery', 'desc' => 'Trackable transit across India.'],
        ['icon' => '↺', 'title' => '7-Day Smooth Exchange', 'desc' => 'Direct seller exchange support.'],
    ];
@endphp

<section id="{{ $secId }}" 
         data-section-id="{{ $secId }}"
         class="py-12 sm:py-16 border-y border-white/10 backdrop-blur-xl bg-white/5 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(!empty($title))
            <h2 class="text-center text-lg sm:text-xl font-bold font-heading mb-8 sm:mb-10">
                {{ $title }}
            </h2>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            @foreach($items as $item)
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/60 dark:bg-black/30 backdrop-blur-md border border-white/40 dark:border-white/10 transition hover:scale-[1.02] shadow-sm">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-base shrink-0 shadow-2xs"
                         style="background-color: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.15); color: var(--sf-accent, #c59b27);">
                        {{ $item['icon'] ?? '✦' }}
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold font-heading">
                            {{ $item['title'] ?? '' }}
                        </h3>
                        <p class="text-xs opacity-70 mt-1 leading-relaxed">
                            {{ $item['desc'] ?? '' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>

