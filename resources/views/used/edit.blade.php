<x-layouts.public title="Edit Classified Ad | K71 Used">
    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('account.listings', ['tab' => 'used']) }}" class="text-xs text-slate-500 hover:text-slate-900 font-bold flex items-center gap-1 mb-2">
                    <span>← Back to My Listings</span>
                </a>
                <h1 class="text-2xl font-black text-slate-950">Edit Classified Ad</h1>
                <p class="text-xs text-slate-500">Update ad details, condition, location, and photos.</p>
            </div>
            <a href="{{ route('used.show', $product->slug) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                View Public Ad ↗
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
            <form action="{{ route('used.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Ad Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Category <span class="text-rose-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none bg-white">
                            @foreach($usedCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Asking Price (₹) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="1" name="price" value="{{ old('price', $product->base_price / 100) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none font-mono font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Condition <span class="text-rose-500">*</span>
                        </label>
                        @php $currentCond = $product->custom_fields['condition'] ?? 'Good Condition'; @endphp
                        <select name="condition" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none bg-white">
                            <option value="Brand New (Sealed)" {{ $currentCond === 'Brand New (Sealed)' ? 'selected' : '' }}>Brand New (Sealed)</option>
                            <option value="Like New (Mint)" {{ $currentCond === 'Like New (Mint)' ? 'selected' : '' }}>Like New (Mint)</option>
                            <option value="Good Condition" {{ $currentCond === 'Good Condition' ? 'selected' : '' }}>Good Condition</option>
                            <option value="Fair / Used" {{ $currentCond === 'Fair / Used' ? 'selected' : '' }}>Fair / Used</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Brand / Make
                        </label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            City / Location <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="city" value="{{ old('city', $product->custom_fields['location'] ?? 'Makrana, Rajasthan') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Seller Phone / WhatsApp Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone', $product->custom_fields['contact_phone'] ?? auth()->user()?->phone) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Description &amp; Specifications <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="description" rows="5" required
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Existing Photos -->
                @if($product->images->isNotEmpty())
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Current Photos</label>
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            @foreach($product->images as $img)
                                <img src="{{ $img->image_url }}" alt="Photo" class="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0">
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Upload Additional Photos -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Add More Photos
                    </label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('account.listings', ['tab' => 'used']) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition cursor-pointer">
                        Save Changes ✓
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.public>

