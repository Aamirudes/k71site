<div :class="gridCols" class="grid gap-2.5 sm:gap-4 lg:gap-5 transition-all duration-300">
    @forelse($products as $product)
        <x-product-card :product="$product" />
    @empty
        <div class="col-span-full py-12 sm:py-16 px-4 sm:px-6 bg-white rounded-3xl border border-stone-200 text-center shadow-xs">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-stone-100 flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-3">
                🔍
            </div>
            <h2 class="text-lg sm:text-xl font-serif font-bold text-stone-900">No Matching Pieces Found</h2>
            <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-md mx-auto leading-relaxed">
                We couldn't find any creations matching your selected filter criteria. Try expanding your price range or clearing some filters.
            </p>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                <button type="button" 
                        @click="clearAllFilters()" 
                        class="px-5 py-2.5 rounded-full bg-stone-900 text-white text-xs font-semibold hover:bg-black transition shadow-xs cursor-pointer active:scale-95">
                    Clear All Filters
                </button>
            </div>
        </div>
    @endforelse
</div>
