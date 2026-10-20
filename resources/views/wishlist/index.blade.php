<x-layouts.public title="My Wishlist">
    <div class="mb-8">
        <h1 class="text-3xl font-serif font-bold text-brand-text">My Wishlist</h1>
        <p class="mt-2 text-sm text-gray-500">Items you have saved for later.</p>
    </div>

    @if($wishlists->count() > 0)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            @foreach($wishlists as $wishlist)
                <div class="relative transition-all duration-300" 
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
                            class="absolute top-2.5 left-2.5 w-8 h-8 bg-white/90 backdrop-blur-md rounded-full shadow-sm text-stone-500 hover:text-red-500 hover:bg-white z-20 flex items-center justify-center transition-all duration-200 active:scale-90 cursor-pointer" 
                            @click="removed = true; window.toggleProductWishlist({{ $wishlist->product_id }});">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-3xl p-16 text-center border border-gray-100 shadow-sm">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            <h2 class="text-xl font-medium text-brand-text mb-2">Your wishlist is empty</h2>
            <p class="text-gray-500 mb-6">Discover the perfect items for your wardrobe.</p>
            <a href="{{ route('catalog.index') }}" class="btn-primary inline-flex">Explore Products</a>
        </div>
    @endif
</x-layouts.public>
