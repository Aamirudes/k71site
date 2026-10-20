@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $wishlistCount = count(\App\Models\Wishlist::getUserWishlistIds());
    $rootDomain = config('k71.root_domain', 'k71.in');
    $shortsUrl = request()->getScheme() . '://shorts.' . $rootDomain;
    if (app()->environment('local', 'testing')) {
        $shortsUrl = route('home', ['vertical' => 'shorts']);
    }
@endphp

<div class="fixed bottom-0 inset-x-0 z-40 bg-white/80 backdrop-blur-xl border-t border-zinc-200/60 md:hidden shadow-[0_-2px_12px_rgba(0,0,0,0.03)]"
     style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="max-w-md mx-auto grid grid-cols-5 items-center h-15 px-2">
        
        <!-- 1. Home Tab -->
        <a href="{{ route('home') }}" 
           class="flex flex-col items-center justify-center py-1 transition-colors {{ request()->is('/') ? 'text-zinc-950 font-semibold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->is('/') ? '2' : '1.8' }}" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"></path>
            </svg>
            <span class="text-[10px] mt-0.5 tracking-tight">Home</span>
        </a>

        <!-- 2. Catalog / Stores Tab -->
        <a href="{{ route('catalog.index') }}" 
           class="flex flex-col items-center justify-center py-1 transition-colors {{ request()->is('catalog*') || request()->is('category*') ? 'text-zinc-950 font-semibold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->is('catalog*') ? '2' : '1.8' }}" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path>
            </svg>
            <span class="text-[10px] mt-0.5 tracking-tight">Catalog</span>
        </a>

        <!-- 3. Minimalist Center Shorts Button -->
        <a href="{{ $shortsUrl }}" 
           class="flex flex-col items-center justify-center -mt-3.5 group"
           aria-label="K71 Video Shorts">
            <div class="w-11 h-11 rounded-full bg-zinc-950 text-white shadow-md flex items-center justify-center transition-all duration-200 group-hover:scale-105 group-active:scale-95">
                <svg width="20" height="20" class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
            </div>
            <span class="text-[9.5px] mt-0.5 font-semibold text-zinc-900 tracking-tight">Shorts</span>
        </a>

        <!-- 4. Wishlist Tab -->
        <a href="{{ route('wishlist.index') }}" 
           class="flex flex-col items-center justify-center py-1 transition-colors {{ request()->is('wishlist*') ? 'text-zinc-950 font-semibold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <div class="relative">
                <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->is('wishlist*') ? '2' : '1.8' }}" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                </svg>
                <span id="bottom-nav-wishlist-badge" 
                      class="absolute -top-1 -right-2 bg-zinc-900 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[15px] text-center {{ $wishlistCount > 0 ? '' : 'hidden' }}">
                    {{ $wishlistCount }}
                </span>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Wishlist</span>
        </a>

        <!-- 5. Account Tab -->
        <a href="{{ auth()->check() ? route('account') : route('login') }}" 
           class="flex flex-col items-center justify-center py-1 transition-colors {{ request()->is('account*') || request()->is('login*') ? 'text-zinc-950 font-semibold' : 'text-zinc-500 hover:text-zinc-900' }}">
            <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->is('account*') ? '2' : '1.8' }}" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
            </svg>
            <span class="text-[10px] mt-0.5 tracking-tight">Account</span>
        </a>

    </div>
</div>
