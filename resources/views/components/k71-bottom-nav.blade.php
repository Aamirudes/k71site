@props(['active' => null])

@php
    $currentHost = request()->getHost();
    $currentPath = request()->path();
    $currentRoute = request()->route() ? request()->route()->getName() : '';

    // Hide mobile bottom tab nav on detail, checkout, and direct messages to prioritize sticky buy and chat composer bars
    $hideOnDetail = request()->routeIs('product.show') 
        || request()->routeIs('catalog.show') 
        || request()->routeIs('services.show') 
        || request()->routeIs('checkout.*')
        || request()->routeIs('cart.*')
        || request()->routeIs('messages.*')
        || request()->is('messages*')
        || request()->routeIs('services.order.*');
    if ($hideOnDetail) {
        return;
    }

    // Detect Vertical: Food, Services, Used, Shorts vs Universal Marketplace
    $detectedVertical = $active ?? ($k71_vertical_key ?? 'main');
    $isFood = $detectedVertical === 'food' || request()->is('food*') || request()->is('s/food*') || str_starts_with($currentHost, 'food.');
    $isServices = $detectedVertical === 'services' || request()->is('services*') || request()->is('s/services*') || str_starts_with($currentHost, 'services.');
    $isUsed = $detectedVertical === 'used' || request()->is('used*') || request()->is('s/used*') || str_starts_with($currentHost, 'used.');
    $isShorts = $detectedVertical === 'shorts' || request()->is('shorts*') || request()->is('s/shorts*') || str_starts_with($currentHost, 'shorts.');

    if ($isFood) {
        $panelGroup = 'mobile_food';
        $theme = 'food';
    } elseif ($isServices) {
        $panelGroup = 'mobile_services';
        $theme = 'services';
    } elseif ($isUsed) {
        $panelGroup = 'mobile_used';
        $theme = 'used';
    } elseif ($isShorts) {
        $panelGroup = 'mobile_shorts';
        $theme = 'shorts';
    } else {
        $panelGroup = 'mobile_bottom';
        $theme = 'universal';
    }

    $navItems = \App\Models\NavigationItem::getPanelItems($panelGroup);
    $colsCount = max($navItems->count(), 1);
    $wishlistCount = count(\App\Models\Wishlist::getUserWishlistIds());

    $initialCartCount = 0;
    if (auth()->check()) {
        $userCart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $initialCartCount = $userCart ? $userCart->items()->sum('quantity') : 0;
    } else {
        $guestCart = \App\Models\Cart::where('session_id', session()->getId())->first();
        $initialCartCount = $guestCart ? $guestCart->items()->sum('quantity') : 0;
    }

    $bottomUnreadMessagesCount = 0;
    if (auth()->check()) {
        try {
            $bUid = (int) auth()->id();
            if (\Illuminate\Support\Facades\Schema::hasTable('chat_messages') && \Illuminate\Support\Facades\Schema::hasTable('chat_conversations')) {
                $bottomUnreadMessagesCount = (int) \Illuminate\Support\Facades\DB::table('chat_messages')
                    ->join('chat_conversations', 'chat_messages.conversation_id', '=', 'chat_conversations.id')
                    ->where(function ($q) use ($bUid) {
                        $q->where('chat_conversations.user_one_id', $bUid)
                          ->orWhere('chat_conversations.user_two_id', $bUid);
                    })
                    ->where('chat_messages.sender_id', '!=', $bUid)
                    ->where('chat_messages.is_read', 0)
                    ->count();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('messages')) {
                $bottomUnreadMessagesCount += (int) \App\Models\Message::where('recipient_id', $bUid)->whereNull('read_at')->count();
            }
        } catch (\Throwable $e) {
            $bottomUnreadMessagesCount = 0;
        }
    }
@endphp

<!-- Bottom Nav Container with Theme-Specific Styles -->
<div x-data="{ cartBadgeCount: {{ $initialCartCount }} }"
     @cart-count-updated.window="cartBadgeCount = $event.detail.count"
     class="fixed bottom-0 inset-x-0 z-40 md:hidden transition-all duration-300 {{ 
    $theme === 'shorts' ? 'bg-black/92 backdrop-blur-2xl border-t border-zinc-800/80 text-white shadow-[0_-4px_30px_rgba(0,0,0,0.8)]' : 
    ($theme === 'food' ? 'bg-white/95 backdrop-blur-2xl border-t border-orange-100 shadow-[0_-4px_25px_rgba(255,82,0,0.08)] text-stone-900' : 
    ($theme === 'services' ? 'bg-slate-950/95 backdrop-blur-2xl border-t border-purple-900/50 shadow-[0_-4px_25px_rgba(124,58,237,0.15)] text-white' : 
    ($theme === 'used' ? 'bg-white/95 backdrop-blur-2xl border-t border-emerald-100 shadow-[0_-4px_25px_rgba(5,150,105,0.08)] text-stone-900' : 
    'bg-white/95 backdrop-blur-2xl border-t border-slate-200 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] text-slate-900'))) 
}}" style="padding-bottom: env(safe-area-inset-bottom, 0px);">

    <div class="max-w-md mx-auto items-center h-16 px-1.5"
         style="display: grid; grid-template-columns: repeat({{ $colsCount }}, minmax(0, 1fr));">
        
        @foreach($navItems as $item)
            @php
                $cleanUrl = ltrim($item->url, '/');
                $isActive = $item->url === '/' ? request()->is('/') : ($cleanUrl ? (request()->is($cleanUrl . '*') || request()->fullUrlIs(url($item->url))) : false);
                $isCenterElevated = str_contains(strtolower($item->badge ?? ''), 'live') || str_contains(strtolower($item->badge ?? ''), 'sell') || str_contains(strtolower($item->title), 'shorts');
                $isWishlist = str_contains(strtolower($item->title), 'wishlist') || str_contains(strtolower($item->title), 'saved');
                $isCart = str_contains(strtolower($item->title), 'cart') || str_contains(strtolower($item->title), 'bag') || $item->url === '/cart' || str_starts_with($item->url, '/cart');
                $isMessages = str_contains(strtolower($item->title), 'message') || str_contains(strtolower($item->title), 'chat') || $item->url === '/messages' || str_starts_with($item->url, '/messages');
            @endphp

            @if($isCenterElevated && ($theme === 'universal' || $theme === 'used' || $theme === 'shorts'))
                <!-- Elevated Center Action Tab -->
                <a href="{{ $item->url }}" 
                   target="{{ $item->target ?? '_self' }}"
                   class="flex flex-col items-center justify-center -mt-4 group relative focus:outline-none"
                   aria-label="{{ $item->title }}">
                    <div class="w-12 h-12 rounded-full text-white shadow-md flex items-center justify-center transition-all duration-200 group-hover:scale-105 group-active:scale-95 border-2 {{
                        $theme === 'used' ? 'bg-slate-900 shadow-slate-900/25 border-white' :
                        ($theme === 'shorts' ? 'bg-rose-600 shadow-rose-600/30 border-zinc-900' :
                        'bg-slate-900 shadow-slate-900/25 border-white')
                    }}">
                        @if($item->icon_type === 'image' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-7 h-7 object-contain">
                        @elseif($item->icon_type === 'emoji' && $item->icon)
                            <span class="text-xl leading-none">{{ $item->icon }}</span>
                        @elseif($item->icon)
                            <svg class="w-5 h-5 ml-0.5 fill-current" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                        @endif
                    </div>
                    <span class="text-[9.5px] mt-1 font-bold tracking-tight {{ 
                        $theme === 'shorts' ? ($isActive ? 'text-rose-400' : 'text-zinc-300') : 
                        ($theme === 'used' ? ($isActive ? 'text-slate-900' : 'text-slate-700') : 
                        ($isActive ? 'text-slate-950 font-extrabold' : 'text-slate-700')) 
                    }}">
                        {{ $item->title }}
                    </span>
                    @if($item->badge)
                        <span class="absolute -top-1 -right-0.5 text-white text-[7.5px] font-black uppercase px-1.5 py-0.2 rounded-full leading-tight shadow-2xs {{ $theme === 'shorts' ? 'bg-rose-600' : 'bg-slate-900' }}">
                            {{ $item->badge }}
                        </span>
                    @endif
                </a>
            @elseif($isCart)
                <!-- Cart Popup Button (Zero Page Reload) -->
                <button type="button" 
                        @click="$dispatch('open-mini-cart')"
                        class="flex flex-col items-center justify-center py-1 relative group transition-all duration-200 focus:outline-none cursor-pointer w-full {{
                            $theme === 'food' ? 'text-[#ff5200] font-extrabold' : 'text-slate-600 hover:text-slate-950 font-semibold'
                        }}"
                        aria-label="Open Shopping Bag">
                    <div class="relative">
                        @if($item->icon)
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @endif
                        <span x-show="cartBadgeCount > 0" 
                              x-text="cartBadgeCount"
                              class="absolute -top-1.5 -right-2 bg-{{ $theme === 'food' ? 'orange' : 'indigo' }}-600 text-white text-[8px] font-black px-1.5 py-0.2 rounded-full leading-tight min-w-[15px] text-center shadow-sm"></span>
                    </div>
                    <span class="text-[10px] mt-1 tracking-tight leading-none truncate max-w-[58px] {{ $theme === 'food' ? 'text-[#ff5200] font-bold' : 'text-slate-700 font-semibold' }}">
                        {{ $item->title }}
                    </span>
                </button>
            @elseif($isWishlist)
                <!-- Wishlist Popup Button (Zero Page Reload) -->
                <button type="button" 
                        @click="$dispatch('open-mini-wishlist')"
                        class="flex flex-col items-center justify-center py-1 relative group transition-all duration-200 focus:outline-none cursor-pointer w-full text-slate-600 hover:text-slate-950 font-semibold"
                        aria-label="Open Saved Wishlist">
                    <div class="relative">
                        @if($item->icon)
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        @endif
                        <span id="bottom-nav-wishlist-badge" 
                              class="absolute -top-1.5 -right-2 bg-rose-500 text-white text-[8px] font-black px-1.5 py-0.2 rounded-full leading-tight min-w-[15px] text-center shadow-sm {{ $wishlistCount > 0 ? '' : 'hidden' }}">
                            {{ $wishlistCount }}
                        </span>
                    </div>
                    <span class="text-[10px] mt-1 tracking-tight leading-none truncate max-w-[58px] text-slate-700 font-semibold">
                        {{ $item->title }}
                    </span>
                </button>
            @else
                <!-- Standard Navigation Tab with Theme Colors -->
                <a href="{{ $item->url }}" 
                   target="{{ $item->target ?? '_self' }}"
                   class="flex flex-col items-center justify-center py-1 relative group transition-all duration-200 focus:outline-none {{
                       $theme === 'shorts' ? ($isActive ? 'text-rose-400 font-extrabold' : 'text-zinc-400 hover:text-white font-medium') :
                       ($theme === 'food' ? ($isActive ? 'text-[#ff5200] font-extrabold' : 'text-stone-500 hover:text-stone-950 font-medium') :
                       ($theme === 'services' ? ($isActive ? 'text-purple-400 font-extrabold' : 'text-slate-400 hover:text-white font-medium') :
                       ($theme === 'used' ? ($isActive ? 'text-emerald-600 font-extrabold' : 'text-stone-500 hover:text-stone-950 font-medium') :
                       ($isActive ? 'text-indigo-600 font-extrabold' : 'text-slate-600 hover:text-slate-950 font-semibold'))))
                   }}">
                    
                    <div class="relative">
                        @if($item->icon_type === 'image' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-6 h-6 object-contain transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110 drop-shadow' : '' }}">
                        @elseif($item->icon_type === 'emoji' && $item->icon)
                            <span class="text-lg leading-none transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110' : '' }}">{{ $item->icon }}</span>
                        @elseif($item->icon)
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110 stroke-[2.2]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[9px] font-bold">★</div>
                        @endif



                        @if($item->badge)
                            <span class="absolute -top-1.5 -right-2 bg-{{ $item->badge_color ?? 'emerald' }}-500 text-white text-[7.5px] font-black uppercase px-1 rounded-full leading-tight shadow-sm">
                                {{ $item->badge }}
                            </span>
                        @endif
                    </div>

                    <span class="text-[10px] mt-1 tracking-tight leading-none truncate max-w-[58px] {{
                        $theme === 'shorts' ? ($isActive ? 'text-rose-400 font-extrabold' : 'text-zinc-400') :
                        ($theme === 'food' ? ($isActive ? 'text-[#ff5200] font-extrabold' : 'text-stone-600') :
                        ($theme === 'services' ? ($isActive ? 'text-purple-400 font-extrabold' : 'text-slate-400') :
                        ($theme === 'used' ? ($isActive ? 'text-emerald-600 font-extrabold' : 'text-stone-600') :
                        ($isActive ? 'text-indigo-600 font-extrabold' : 'text-slate-700'))))
                    }}">
                        {{ $item->title }}
                    </span>

                    @if($isActive)
                        <span class="w-1.5 h-1.5 rounded-full mt-0.5 {{
                            $theme === 'shorts' ? 'bg-rose-500 shadow-sm shadow-rose-500/50' :
                            ($theme === 'food' ? 'bg-[#ff5200] shadow-sm shadow-orange-500/40' :
                            ($theme === 'services' ? 'bg-purple-400 shadow-sm shadow-purple-400/50' :
                            ($theme === 'used' ? 'bg-emerald-600 shadow-sm shadow-emerald-500/40' :
                            'bg-indigo-600')))
                        }}"></span>
                    @endif
                </a>
            @endif

        @endforeach

    </div>
</div>
