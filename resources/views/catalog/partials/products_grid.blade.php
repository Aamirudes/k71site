<div :class="gridCols" class="grid gap-2.5 sm:gap-4 lg:gap-5 transition-all duration-300">
    @forelse($products as $product)
        <div class="grail-card-reveal" style="--delay: {{ min(($loop->index % 8) * 45, 320) }}ms;">
            <x-product-card :product="$product" />
        </div>
    @empty
        <div class="col-span-full py-12 sm:py-16 px-4 sm:px-6 bg-white rounded-3xl border border-black/8 text-center shadow-xs">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#ECD2F3]/40 flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-3">
                🔍
            </div>
            <h2 class="grail-display text-2xl sm:text-3xl text-[#1A1018]">No Matching Pieces Found</h2>
            <p class="text-xs sm:text-sm text-[#7C4F74] mt-1.5 max-w-md mx-auto leading-relaxed">
                We couldn't find any creations matching your selected filter criteria. Try expanding your price range or clearing some filters.
            </p>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                <button type="button" 
                        @click="clearAllFilters()" 
                        class="px-6 py-2.5 grail-btn-obsidian text-xs cursor-pointer active:scale-95">
                    Clear All Filters
                </button>
            </div>
        </div>
    @endforelse
</div>
