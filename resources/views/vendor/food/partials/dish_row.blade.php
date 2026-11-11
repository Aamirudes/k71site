@php
    $dietResolved = $dish->dietary_label_resolved;
    $isDishVeg = $dish->isVeg();
    $isAvailable = (bool) ($dish->is_dish_available ?? true);
    $portionsCount = $dish->variants ? $dish->variants->count() : 0;
    $groupsCount = $dish->optionGroups ? $dish->optionGroups->count() : 0;
@endphp

<div class="p-4 sm:px-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:bg-gray-50/70 transition-colors">
    <div class="flex items-start gap-3.5 min-w-0">
        <img src="{{ $dish->primary_image_url }}"
             alt="{{ $dish->title }}"
             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';"
             class="w-16 h-16 rounded-2xl object-cover border border-gray-200 bg-gray-100 shrink-0 {{ !$isAvailable ? 'grayscale opacity-60' : '' }}">

        <div class="min-w-0 space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center justify-center w-4 h-4 rounded border {{ $isDishVeg ? 'border-emerald-600' : 'border-rose-600' }} shrink-0" title="{{ $dietResolved }}">
                    <span class="w-2 h-2 rounded-full {{ $isDishVeg ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                </span>
                <h4 class="font-bold text-gray-900 text-sm truncate">{{ $dish->title }}</h4>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $isDishVeg ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    {{ $dietResolved }}
                </span>
                @if(!$isAvailable)
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 border border-rose-200">
                        Sold Out
                    </span>
                @endif
                @if($dish->status !== 'active')
                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 border border-amber-200">
                        {{ ucfirst($dish->status) }}
                    </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                <span class="font-black text-gray-900">₹{{ number_format($dish->base_price / 100, 2) }}</span>
                @if($portionsCount > 0)
                    <span>• {{ $portionsCount }} {{ \Illuminate\Support\Str::plural('portion', $portionsCount) }}</span>
                @endif
                @if($groupsCount > 0)
                    <span>• {{ $groupsCount }} option {{ \Illuminate\Support\Str::plural('group', $groupsCount) }}</span>
                @endif
                @if($dish->prep_time_mins)
                    <span>• Prep ~{{ $dish->prep_time_mins }}m</span>
                @endif
                @if($dish->available_from || $dish->available_until)
                    <span class="font-mono text-[11px] bg-gray-100 px-1.5 py-0.5 rounded">
                        Window: {{ $dish->available_from ?: '00:00' }}–{{ $dish->available_until ?: '23:59' }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <form action="{{ route('vendor.products.toggle_availability', $dish) }}" method="POST" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold border transition cursor-pointer {{ $isAvailable ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                {{ $isAvailable ? '✓ Available' : '⏸ Sold Out' }}
            </button>
        </form>

        <form action="{{ route('vendor.products.toggle_status', $dish) }}" method="POST" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold border transition cursor-pointer {{ $dish->status === 'active' ? 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
                {{ $dish->status === 'active' ? '● Live' : '○ Draft' }}
            </button>
        </form>

        <form action="{{ route('vendor.products.duplicate', $dish) }}" method="POST" class="inline" onsubmit="return confirm('Duplicate this dish as a new draft?');">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition cursor-pointer">
                Clone
            </button>
        </form>

        <a href="{{ route('vendor.products.edit', $dish) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gray-900 hover:bg-black text-white transition">
            Edit Dish
        </a>
    </div>
</div>

