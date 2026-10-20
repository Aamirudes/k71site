@props([
    'title' => 'Trending Shorts',
    'subtitle' => 'Watch & shop directly from creator videos',
    'count' => 8,
])

@php
    $shorts = \App\Models\Short::with('vendor')
        ->where('status', 'published')
        ->where(function($q) {
            $q->where('is_boosted', true)
              ->orWhere('is_featured', true)
              ->orWhere('views', '>', 0);
        })
        ->orderByDesc('is_boosted')
        ->orderByDesc('views')
        ->take($count)
        ->get();
@endphp

@if($shorts->isNotEmpty())
<section class="my-10 relative">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold tracking-wider uppercase mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                Video Commerce
            </div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                {{ $title }}
            </h2>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
        </div>
        <a href="{{ route('shorts.index') }}" class="text-xs md:text-sm font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700 flex items-center gap-1 group">
            Explore All
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <!-- Horizontal scroll rail -->
    <div class="flex gap-3 md:gap-4 overflow-x-auto pb-4 pt-1 -mx-4 px-4 scrollbar-none snap-x snap-mandatory">
        @foreach($shorts as $short)
        <a href="{{ route('shorts.show', $short->id) }}" class="flex-none w-36 sm:w-44 md:w-52 aspect-[9/16] rounded-2xl relative overflow-hidden group shadow-md snap-start border border-white/10 bg-slate-900 hover:scale-[1.02] transition-transform duration-300">
            <!-- Thumbnail -->
            <img src="{{ $short->thumb_url ?: \App\Models\Short::placeholderThumb($short->id, $short->title) }}" 
                 alt="{{ $short->title }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                 loading="lazy">

            <!-- Vignette gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>

            <!-- Boosted badge -->
            @if($short->is_boosted)
            <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full bg-zinc-900/90 text-white text-[10px] font-semibold tracking-wide flex items-center gap-1 shadow-sm backdrop-blur-sm border border-white/10">
                FEATURED
            </div>
            @endif

            <!-- Tagged Products Count -->
            @if($short->products()->count() > 0)
            <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-black/60 text-white text-[10px] font-semibold tracking-wide flex items-center gap-1 backdrop-blur-sm border border-white/10">
                <svg width="12" height="12" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                <span>{{ $short->products()->count() }}</span>
            </div>
            @endif

            <!-- Play button pulse overlay -->
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="w-11 h-11 rounded-full bg-rose-600/90 text-white flex items-center justify-center shadow-lg transform scale-90 group-hover:scale-100 transition-transform">
                    <svg class="w-5 h-5 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
            </div>

            <!-- Bottom meta -->
            <div class="absolute bottom-0 inset-x-0 p-3">
                <div class="flex items-center gap-1.5 mb-1.5">
                    @if($short->vendor && $short->vendor->logo_url)
                    <img src="{{ $short->vendor->logo_url }}" class="w-5 h-5 rounded-full object-cover ring-1 ring-white/50" alt="{{ $short->vendor->shop_name }}">
                    @else
                    <div class="w-5 h-5 rounded-full bg-white/20 text-white text-[9px] font-bold flex items-center justify-center ring-1 ring-white/30">
                        {{ strtoupper(substr($short->vendor?->shop_name ?? 'K', 0, 1)) }}
                    </div>
                    @endif
                    <span class="text-[11px] font-semibold text-white/90 truncate">{{ $short->vendor?->shop_name ?? 'Creator' }}</span>
                </div>
                <h3 class="text-xs font-bold text-white line-clamp-2 leading-tight drop-shadow-sm mb-1.5">
                    {{ $short->title }}
                </h3>
                <div class="flex items-center gap-2 text-[10px] text-white/70">
                    <span class="flex items-center gap-0.5">
                        <svg class="w-3 h-3 text-rose-400 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        {{ \App\Models\Short::formatNumber($short->likes) }}
                    </span>
                    <span>•</span>
                    <span>{{ \App\Models\Short::formatNumber($short->views) }} views</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
