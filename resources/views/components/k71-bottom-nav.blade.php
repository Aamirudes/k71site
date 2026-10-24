@props(['active' => null])

@php
    $currentHost = request()->getHost();
    $currentPath = request()->path();
    $currentRoute = request()->route() ? request()->route()->getName() : '';

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
@endphp

<!-- Bottom Nav Container with Theme-Specific Styles -->
<div class="fixed bottom-0 inset-x-0 z-40 md:hidden transition-all duration-300 {{ 
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
            @endphp

            @if($isCenterElevated && ($theme === 'universal' || $theme === 'used' || $theme === 'shorts'))
                <!-- Elevated Center Action Tab -->
                <a href="{{ $item->url }}" 
                   target="{{ $item->target ?? '_self' }}"
                   class="flex flex-col items-center justify-center -mt-4 group relative focus:outline-none"
                   aria-label="{{ $item->title }}">
                    <div class="w-12 h-12 rounded-full text-white shadow-lg flex items-center justify-center transition-all duration-200 group-hover:scale-110 group-active:scale-95 border-2 {{
                        $theme === 'used' ? 'bg-gradient-to-tr from-emerald-600 to-teal-500 shadow-emerald-500/30 border-white' :
                        ($theme === 'shorts' ? 'bg-gradient-to-tr from-rose-600 via-red-500 to-amber-500 shadow-rose-500/40 border-zinc-900' :
                        'bg-gradient-to-tr from-rose-600 via-red-500 to-amber-500 shadow-rose-500/30 border-white')
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
                        ($theme === 'used' ? ($isActive ? 'text-emerald-600' : 'text-stone-800') : 
                        ($isActive ? 'text-rose-600' : 'text-slate-800')) 
                    }}">
                        {{ $item->title }}
                    </span>
                    @if($item->badge)
                        <span class="absolute -top-1 -right-0.5 text-white text-[7.5px] font-black uppercase px-1 py-0.2 rounded-full leading-tight shadow-sm {{ $theme === 'used' ? 'bg-emerald-600' : 'bg-rose-600 animate-pulse' }}">
                            {{ $item->badge }}
                        </span>
                    @endif
                </a>
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

                        <!-- Live Badge or Wishlist Counter -->
                        @if($isWishlist)
                            <span id="bottom-nav-wishlist-badge" 
                                  class="absolute -top-1.5 -right-2 bg-rose-500 text-white text-[8px] font-black px-1.5 py-0.2 rounded-full leading-tight min-w-[15px] text-center shadow-sm {{ $wishlistCount > 0 ? '' : 'hidden' }}">
                                {{ $wishlistCount }}
                            </span>
                        @elseif($item->badge)
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
