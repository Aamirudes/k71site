@if(isset($activeFilters) && count($activeFilters) > 0)
    <div class="mb-3.5 sm:mb-5 p-2.5 sm:p-3 rounded-2xl bg-stone-100/90 border border-stone-200/80 flex flex-wrap items-center gap-1.5 sm:gap-2">
        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-stone-500 mr-1">Active:</span>
        @foreach($activeFilters as $f)
            <button type="button" 
                    @click="removeFilter('{{ $f['key'] }}')" 
                    class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1 rounded-full bg-white border border-stone-200 text-[11px] sm:text-xs font-medium text-stone-800 hover:border-red-300 hover:text-red-700 transition shadow-2xs group cursor-pointer active:scale-95">
                <span>{{ $f['label'] }}</span>
                <span class="text-stone-400 group-hover:text-red-600 font-bold ml-0.5">&times;</span>
            </button>
        @endforeach
        <button type="button" 
                @click="clearAllFilters()" 
                class="text-[11px] sm:text-xs font-semibold text-red-600 hover:text-red-800 ml-auto underline transition cursor-pointer px-1">
            Clear All
        </button>
    </div>
@endif
