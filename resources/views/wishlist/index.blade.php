<x-layouts.public title="My Wishlist" :fullWidth="true">
    <!-- Grail Obsidian Wishlist Header -->
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-7 pb-11 sm:pt-10 sm:pb-14">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <span class="grail-eyebrow block mb-2">Saved Edits</span>
                    <h1 class="grail-display text-[clamp(2rem,4.5vw,3.25rem)] font-bold text-white tracking-tight">My Wishlist</h1>
                    <p class="mt-1.5 text-sm text-white/65">Items you have saved for later.</p>
                </div>
                <span class="px-3.5 py-1.5 rounded-full bg-white/8 border border-white/15 text-[#ECD2F3] text-xs font-semibold tabular-nums self-start sm:self-auto">
                    {{ $wishlists->count() }} {{ Str::plural('Saved Item', $wishlists->count()) }}
                </span>
            </div>
        </div>
    </section>

    <!-- Grail Stone Sheet -->
    <div class="grail-sheet -mt-4 sm:-mt-5 rounded-t-[28px] sm:rounded-t-[36px] min-h-[70vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    @if($wishlists->count() > 0)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-7">
            @foreach($wishlists as $wishlist)
                <div class="grail-card-reveal relative transition-all duration-300" 
                     style="--delay: {{ ($loop->index % 4) * 65 }}ms;"
                     x-data="{ removed: false }" 
                     x-show="!removed" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="opacity-100 scale-100" 
                     x-transition:leave-end="opacity-0 scale-90"
                     @wishlist-state-changed.window="if ($event.detail.productId === {{ $wishlist->product_id }} && !$event.detail.isWishlisted) removed = true;">
                    <x-product-card :product="$wishlist->product" />
                    <!-- Quick Remove Cross Button -->
                    <button type="button" 
                            title="Remove from Wishlist"
                            class="absolute top-2.5 left-2.5 w-8 h-8 bg-[#1A1018]/90 backdrop-blur-md rounded-full shadow-sm text-[#ECD2F3] hover:text-rose-400 hover:bg-black z-20 flex items-center justify-center transition-all duration-200 active:scale-90 cursor-pointer border border-white/15" 
                            @click="removed = true; window.toggleProductWishlist({{ $wishlist->product_id }});">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-[28px] p-12 sm:p-16 text-center border border-black/8 shadow-[0_14px_40px_rgba(26,16,24,0.06)]">
            <div class="w-20 h-20 rounded-full bg-[#1A1018] text-[#ECD2F3] mx-auto mb-5 flex items-center justify-center shadow-md">
                <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h2 class="grail-display text-2xl font-bold text-[#1A1018] mb-2">Your wishlist is empty</h2>
            <p class="text-[#1A1018]/65 mb-7 text-sm">Discover the perfect pieces curated across our boutiques.</p>
            <a href="{{ route('catalog.index') }}" class="grail-btn-obsidian inline-flex px-7 py-3 text-xs uppercase tracking-wider">Explore Products</a>
        </div>
    @endif
        </div>
    </div>
</x-layouts.public>
