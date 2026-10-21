@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $wishlistCount = count(\App\Models\Wishlist::getUserWishlistIds());
    $navItems = \App\Models\NavigationItem::getMobileBottomItems();
    $colsCount = max($navItems->count(), 1);
@endphp

<div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-2xl border-t border-slate-200 md:hidden shadow-[0_-4px_25px_rgba(0,0,0,0.06)] transition-all duration-300"
     style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="max-w-md mx-auto items-center h-16 px-1.5"
         style="display: grid; grid-template-columns: repeat({{ $colsCount }}, minmax(0, 1fr));">
        
        @foreach($navItems as $item)
            @php
                $cleanUrl = ltrim($item->url, '/');
                $isActive = $item->url === '/' ? request()->is('/') : ($cleanUrl ? request()->is($cleanUrl . '*') : false);
                $isShorts = str_contains(strtolower($item->title), 'shorts') || strtolower($item->badge ?? '') === 'live';
                $isWishlist = str_contains(strtolower($item->title), 'wishlist');
            @endphp

            @if($isShorts)
                <!-- Elevated Center / Highlighted Action Tab -->
                <a href="{{ $item->url }}" 
                   target="{{ $item->target ?? '_self' }}"
                   class="flex flex-col items-center justify-center -mt-4 group relative focus:outline-none"
                   aria-label="{{ $item->title }}">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-rose-600 via-red-500 to-amber-500 text-white shadow-lg shadow-rose-500/30 flex items-center justify-center transition-all duration-200 group-hover:scale-110 group-active:scale-95 border-2 border-white">
                        @if($item->icon_type === 'image' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-8 h-8 object-contain">
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
                    <span class="text-[9.5px] mt-1 font-bold tracking-tight {{ $isActive ? 'text-rose-600' : 'text-slate-800' }}">
                        {{ $item->title }}
                    </span>
                    @if($item->badge)
                        <span class="absolute -top-1 -right-0.5 bg-rose-600 text-white text-[7.5px] font-black uppercase px-1 py-0.2 rounded-full leading-tight shadow-sm animate-pulse">
                            {{ $item->badge }}
                        </span>
                    @endif
                </a>
            @else
                <!-- Standard Navigation Tab -->
                <a href="{{ $item->url }}" 
                   target="{{ $item->target ?? '_self' }}"
                   class="flex flex-col items-center justify-center py-1 relative group transition-all duration-200 focus:outline-none {{ $isActive ? 'text-indigo-600 font-extrabold' : 'text-slate-600 hover:text-slate-950 font-semibold' }}">
                    
                    <div class="relative">
                        @if($item->icon_type === 'image' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-7 h-7 sm:w-8 sm:h-8 object-contain transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110 drop-shadow' : '' }}">
                        @elseif($item->icon_type === 'emoji' && $item->icon)
                            <span class="text-xl leading-none transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110' : '' }}">{{ $item->icon }}</span>
                        @elseif($item->icon)
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-105 stroke-[2.2]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-700">★</div>
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

                    <span class="text-[10px] mt-1 tracking-tight leading-none truncate max-w-[56px] {{ $isActive ? 'text-indigo-600 font-extrabold' : 'text-slate-700 font-semibold' }}">
                        {{ $item->title }}
                    </span>

                    @if($isActive)
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-0.5"></span>
                    @endif
                </a>
            @endif

        @endforeach

    </div>
</div>
