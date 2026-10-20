@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $wishlistCount = count(\App\Models\Wishlist::getUserWishlistIds());
    $navItems = \App\Models\NavigationItem::getMobileBottomItems();
    $colsCount = max($navItems->count(), 1);
@endphp

<div class="fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-[#0B0F17]/95 backdrop-blur-2xl border-t border-zinc-200/80 dark:border-zinc-800/90 md:hidden shadow-[0_-4px_25px_rgba(0,0,0,0.06)] dark:shadow-[0_-4px_30px_rgba(0,0,0,0.8)] transition-all duration-300"
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
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-rose-600 via-red-500 to-amber-500 text-white shadow-lg shadow-rose-500/30 dark:shadow-rose-500/50 flex items-center justify-center transition-all duration-200 group-hover:scale-110 group-active:scale-95 border-2 border-white dark:border-[#0B0F17]">
                        @if($item->icon_type === 'image' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-6 h-6 object-contain">
                        @elseif($item->icon_type === 'emoji' && $item->icon)
                            <span class="text-lg leading-none">{{ $item->icon }}</span>
                        @elseif($item->icon)
                            <svg class="w-5 h-5 ml-0.5 fill-current" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                        @endif
                    </div>
                    <span class="text-[9.5px] mt-1 font-bold tracking-tight {{ $isActive ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-800 dark:text-zinc-200' }}">
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
                   class="flex flex-col items-center justify-center py-1 relative group transition-all duration-200 focus:outline-none {{ $isActive ? 'text-zinc-950 dark:text-emerald-400 font-bold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}">
                    
                    <div class="relative">
                        @if($item->icon_type === 'image' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-5 h-5 object-contain transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110 drop-shadow-[0_0_8px_rgba(16,185,129,0.4)]' : '' }}">
                        @elseif($item->icon_type === 'emoji' && $item->icon)
                            <span class="text-lg leading-none transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-110' : '' }}">{{ $item->icon }}</span>
                        @elseif($item->icon)
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ $isActive ? 'scale-105 stroke-[2.2]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item->icon !!}
                            </svg>
                        @else
                            <div class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-[9px] font-bold">★</div>
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

                    <span class="text-[10px] mt-1 tracking-tight leading-none truncate max-w-[56px] {{ $isActive ? 'text-zinc-950 dark:text-emerald-400 font-bold' : 'text-zinc-500 dark:text-zinc-400' }}">
                        {{ $item->title }}
                    </span>

                    @if($isActive)
                        <span class="w-1 h-1 rounded-full bg-zinc-950 dark:bg-emerald-400 mt-0.5"></span>
                    @endif
                </a>
            @endif

        @endforeach

    </div>
</div>
