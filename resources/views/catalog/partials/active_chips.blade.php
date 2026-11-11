@if(isset($activeFilters) && count($activeFilters) > 0)
    <div class="mb-4 sm:mb-5 p-2.5 sm:p-3 rounded-2xl bg-white border border-black/8 flex flex-wrap items-center gap-1.5 sm:gap-2 shadow-2xs">
        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.1em] text-[#623562] mr-1 px-1">Active:</span>
        @foreach($activeFilters as $f)
            <button type="button" 
                    @click="removeFilter('{{ $f['key'] }}')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#ECD2F3]/50 border border-[#623562]/20 text-[11px] sm:text-xs font-semibold text-[#1A1018] hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 transition group cursor-pointer active:scale-95">
                <span>{{ $f['label'] }}</span>
                <span class="text-[#623562] group-hover:text-rose-600 font-bold ml-0.5">&times;</span>
            </button>
        @endforeach
        <button type="button" 
                @click="clearAllFilters()" 
                class="text-[11px] sm:text-xs font-semibold text-rose-600 hover:text-rose-800 ml-auto underline transition cursor-pointer px-1.5">
            Clear All
        </button>
    </div>
@endif
