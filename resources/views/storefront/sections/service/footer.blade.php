@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $id = $providerSite['identity'] ?? [];
    $nav = $providerSite['nav_labels'] ?? [];
    $anchorPrefix = $footerBaseUrl ?? '';
@endphp

<footer class="mt-16 border-t {{ $ps['dark'] ? 'border-white/10 bg-black/30' : 'border-stone-200/90 bg-white/60' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="space-y-1">
            <div class="text-base font-black">{{ $id['name'] ?? $vendor->shop_name }}</div>
            <p class="text-xs {{ $ps['muted'] }}">
                © {{ date('Y') }} {{ $id['name'] ?? $vendor->shop_name }}. All rights reserved.
            </p>
        </div>

        <nav class="flex flex-wrap items-center gap-5 text-xs font-semibold {{ $ps['muted'] }}">
            <a href="{{ $anchorPrefix }}#overview" class="hover:underline">{{ $nav['overview'] ?? 'Overview' }}</a>
            <a href="{{ $anchorPrefix }}#portfolio" class="hover:underline">{{ $nav['work'] ?? 'Work' }}</a>
            <a href="{{ $anchorPrefix }}#services" class="hover:underline">{{ $nav['services'] ?? 'Services' }}</a>
            <a href="{{ $anchorPrefix }}#about" class="hover:underline">{{ $nav['about'] ?? 'About' }}</a>
            <a href="{{ $anchorPrefix }}#contact" class="hover:underline">{{ $nav['contact'] ?? 'Contact' }}</a>
            @if(!empty($providerSite['marketplace_profile_url']))
                <a href="{{ $providerSite['marketplace_profile_url'] }}" class="hover:underline opacity-75">
                    Powered by K71 Services
                </a>
            @endif
        </nav>
    </div>
</footer>
