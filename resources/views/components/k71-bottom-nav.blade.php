@props(['active' => null])

@php
    $brand = $active ?? ($currentBrand ?? (function_exists('k71_current_brand') ? k71_current_brand() : 'main'));
    $currentPath = request()->path();

    // Hide mobile bottom tab nav on checkout and full-screen chat to prioritize sticky action/composer
    $hideOnViews = request()->routeIs('checkout.*') 
        || request()->routeIs('messages.show') 
        || request()->is('messages/*');
    if ($hideOnViews) {
        return;
    }

    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    } else {
        $cart = \App\Models\Cart::where('session_id', session()->getId())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }

    // 5-Item Native Tab Bar configuration per brand (Section E & F)
    if ($brand === 'food') {
        $tabs = [
            ['label' => 'Kitchens', 'url' => route('food.index'), 'active' => request()->routeIs('food.index') && !request()->has('filter'), 'icon' => 'fire'],
            ['label' => 'Explore', 'url' => route('food.index') . '#explore', 'active' => false, 'icon' => 'search'],
            ['label' => 'Deals', 'url' => route('food.index') . '#deals', 'active' => false, 'icon' => 'tag'],
            ['label' => 'Orders', 'url' => route('orders.index'), 'active' => request()->routeIs('orders.*'), 'icon' => 'receipt'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    } elseif ($brand === 'services') {
        $tabs = [
            ['label' => 'Services', 'url' => route('services.index'), 'active' => request()->routeIs('services.index'), 'icon' => 'briefcase'],
            ['label' => 'Browse', 'url' => route('services.index') . '#services-feed', 'active' => false, 'icon' => 'grid'],
            ['label' => 'Projects', 'url' => route('services.orders.index'), 'active' => request()->routeIs('services.orders.*'), 'icon' => 'receipt'],
            ['label' => 'Chat', 'url' => route('messages.index'), 'active' => request()->routeIs('messages.*'), 'icon' => 'chat'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    } elseif ($brand === 'used') {
        $tabs = [
            ['label' => 'Feed', 'url' => route('used.index'), 'active' => request()->routeIs('used.index') && !request()->routeIs('used.edit'), 'icon' => 'sparkles'],
            ['label' => 'Deals', 'url' => route('used.index') . '?filter=deals', 'active' => request()->query('filter') === 'deals', 'icon' => 'tag'],
            ['label' => 'Sell', 'url' => route('used.index') . '#post-ad', 'action' => 'open-used-post', 'center_cta' => true, 'active' => false, 'icon' => 'camera'],
            ['label' => 'Chat', 'url' => route('messages.index'), 'active' => request()->routeIs('messages.*'), 'icon' => 'chat'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    } elseif ($brand === 'b2b') {
        $tabs = [
            ['label' => 'Quotes', 'url' => route('enquiry.index'), 'active' => request()->routeIs('enquiry.index'), 'icon' => 'document-text'],
            ['label' => 'Suppliers', 'url' => route('catalog.index', ['vertical' => 'b2b']), 'active' => request()->query('vertical') === 'b2b', 'icon' => 'building'],
            ['label' => 'RFQ', 'url' => route('enquiry.create'), 'center_cta' => true, 'active' => request()->routeIs('enquiry.create'), 'icon' => 'plus'],
            ['label' => 'Chat', 'url' => route('messages.index'), 'active' => request()->routeIs('messages.*'), 'icon' => 'chat'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    } elseif ($brand === 'feasto') {
        $tabs = [
            ['label' => 'Instant', 'url' => route('feasto.index'), 'active' => request()->routeIs('feasto.index'), 'icon' => 'bolt'],
            ['label' => 'Categories', 'url' => route('feasto.index') . '#categories', 'active' => false, 'icon' => 'grid'],
            ['label' => '10-Min', 'url' => route('feasto.index') . '#deals', 'active' => false, 'icon' => 'tag'],
            ['label' => 'Bag', 'url' => route('cart.index'), 'action' => 'open-mini-cart', 'badge' => $cartCount, 'active' => request()->routeIs('cart.*'), 'icon' => 'bag'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    } elseif ($brand === 'shorts') {
        $tabs = [
            ['label' => 'Shorts', 'url' => route('shorts.index'), 'active' => request()->routeIs('shorts.*'), 'icon' => 'play'],
            ['label' => 'Discover', 'url' => route('catalog.index'), 'active' => false, 'icon' => 'search'],
            ['label' => 'Spin & Win', 'url' => route('shorts.spin.check'), 'active' => request()->routeIs('shorts.spin.*'), 'icon' => 'sparkles'],
            ['label' => 'Saved', 'url' => route('wishlist.index'), 'action' => 'open-mini-wishlist', 'active' => request()->routeIs('wishlist.*'), 'icon' => 'heart'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    } else { // main (K71)
        $tabs = [
            ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home'), 'icon' => 'home'],
            ['label' => 'Catalog', 'url' => route('catalog.index'), 'active' => request()->routeIs('catalog.index'), 'icon' => 'grid'],
            ['label' => 'Shorts', 'url' => route('shorts.index'), 'active' => request()->routeIs('shorts.*'), 'icon' => 'play', 'badge' => 'LIVE'],
            ['label' => 'Cart', 'url' => route('cart.index'), 'action' => 'open-mini-cart', 'badge' => $cartCount, 'active' => request()->routeIs('cart.*'), 'icon' => 'bag'],
            ['label' => 'More', 'url' => '#more', 'action' => 'open-hamburger', 'active' => false, 'icon' => 'menu'],
        ];
    }
@endphp

<!-- Native App Mobile 5-Tab Bar (56px + Safe-Area) -->
<nav class="fixed bottom-0 inset-x-0 z-45 md:hidden k71-mobile-bottom-nav"
     style="height: calc(56px + env(safe-area-inset-bottom, 0px)); padding-bottom: env(safe-area-inset-bottom, 0px);"
     aria-label="Mobile Navigation Bar">
    
    <div class="w-full h-[56px] grid grid-cols-5 items-center px-1">
        @foreach($tabs as $tab)
            @php
                $isCenterCta = !empty($tab['center_cta']);
                $isActive = !empty($tab['active']);
            @endphp

            @if($isCenterCta)
                <!-- Center Elevated CTA Tab (e.g. Used Sell, B2B RFQ) -->
                @if(!empty($tab['action']))
                    <button type="button" 
                            @click="$dispatch('{{ $tab['action'] }}')"
                            class="flex flex-col items-center justify-center -mt-3.5 focus:outline-none cursor-pointer"
                            aria-label="{{ $tab['label'] }}">
                        <div class="w-11 h-11 rounded-full text-white shadow-md flex items-center justify-center transition active:scale-95 border-2 border-white"
                             style="background-color: var(--primary);">
                            @if($tab['icon'] === 'camera')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            @endif
                        </div>
                        <span class="text-[11px] font-bold mt-0.5" style="color: var(--primary);">
                            {{ $tab['label'] }}
                        </span>
                    </button>
                @else
                    <a href="{{ $tab['url'] }}" 
                       class="flex flex-col items-center justify-center -mt-3.5 focus:outline-none"
                       aria-label="{{ $tab['label'] }}">
                        <div class="w-11 h-11 rounded-full text-white shadow-md flex items-center justify-center transition active:scale-95 border-2 border-white"
                             style="background-color: var(--primary);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-[11px] font-bold mt-0.5" style="color: var(--primary);">
                            {{ $tab['label'] }}
                        </span>
                    </a>
                @endif
            @else
                <!-- Standard Tab -->
                @if(!empty($tab['action']))
                    <button type="button" 
                            @click="$dispatch('{{ $tab['action'] }}')"
                            class="k71-tab-item relative focus:outline-none cursor-pointer {{ $isActive ? 'active' : '' }}"
                            aria-label="{{ $tab['label'] }}">
                        <div class="relative">
                            @include('components.k71-tab-icon', ['icon' => $tab['icon'], 'isActive' => $isActive])
                            @if(!empty($tab['badge']) && is_numeric($tab['badge']) && $tab['badge'] > 0)
                                <span class="absolute -top-1 -right-2 text-white text-[9px] font-black px-1 rounded-full leading-tight"
                                      style="background-color: var(--primary);">
                                    {{ $tab['badge'] }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] mt-0.5 leading-none {{ $isActive ? 'font-bold' : 'font-medium' }}"
                              style="{{ $isActive ? 'color: var(--primary);' : '' }}">
                            {{ $tab['label'] }}
                        </span>
                    </button>
                @else
                    <a href="{{ $tab['url'] }}" 
                       class="k71-tab-item relative focus:outline-none {{ $isActive ? 'active' : '' }}"
                       aria-label="{{ $tab['label'] }}">
                        <div class="relative">
                            @include('components.k71-tab-icon', ['icon' => $tab['icon'], 'isActive' => $isActive])
                            @if(!empty($tab['badge']))
                                <span class="absolute -top-1 -right-2 text-white text-[8px] font-black px-1 rounded-full leading-tight uppercase"
                                      style="background-color: var(--primary);">
                                    {{ $tab['badge'] }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] mt-0.5 leading-none {{ $isActive ? 'font-bold' : 'font-medium' }}"
                              style="{{ $isActive ? 'color: var(--primary);' : '' }}">
                            {{ $tab['label'] }}
                        </span>
                    </a>
                @endif
            @endif
        @endforeach
    </div>
</nav>
