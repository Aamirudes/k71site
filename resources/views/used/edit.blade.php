<x-layouts.public title="Edit Classified Ad | K71 Used" :fullWidth="true">
    <section class="grail-hero-band py-8 px-4 sm:px-6">
        <div class="max-w-3xl mx-auto flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('account.listings', ['tab' => 'used']) }}" class="text-xs text-[#ECD2F3] hover:text-white font-semibold flex items-center gap-1 mb-2 transition">
                    <span>← Back to My Listings</span>
                </a>
                <span class="grail-eyebrow block mb-1">Classifieds Manager</span>
                <h1 class="text-3xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">Edit Classified Ad</h1>
                <p class="text-xs text-[#EFE9F2]/65 mt-1">Update ad details, condition, location, and photos.</p>
            </div>
            <a href="{{ route('used.show', $product->slug) }}" target="_blank" class="px-4 py-2 grail-btn-ghost-dark text-xs font-bold shrink-0">
                View Public Ad ↗
            </a>
        </div>
    </section>

    <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 px-4 sm:px-6 border-0 min-h-[70vh] shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
    <div class="max-w-3xl mx-auto">

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grail-card p-6 sm:p-8">
            <form action="{{ route('used.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                        Ad Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                           class="w-full px-4 py-2.5 grail-input-light text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                            Category <span class="text-rose-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-3.5 py-2.5 grail-input-light text-sm">
                            @foreach($usedCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                            Asking Price (₹) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="1" name="price" value="{{ old('price', $product->base_price / 100) }}" required
                               class="w-full px-4 py-2.5 grail-input-light text-sm font-mono font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                            Condition <span class="text-rose-500">*</span>
                        </label>
                        @php $currentCond = $product->custom_fields['condition'] ?? 'Good Condition'; @endphp
                        <select name="condition" class="w-full px-3.5 py-2.5 grail-input-light text-sm">
                            <option value="Brand New (Sealed)" {{ $currentCond === 'Brand New (Sealed)' ? 'selected' : '' }}>Brand New (Sealed)</option>
                            <option value="Like New (Mint)" {{ $currentCond === 'Like New (Mint)' ? 'selected' : '' }}>Like New (Mint)</option>
                            <option value="Good Condition" {{ $currentCond === 'Good Condition' ? 'selected' : '' }}>Good Condition</option>
                            <option value="Fair / Used" {{ $currentCond === 'Fair / Used' ? 'selected' : '' }}>Fair / Used</option>
                        </select>
                    </div>

                    <div>
                        <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                            Brand / Make
                        </label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}"
                               class="w-full px-4 py-2.5 grail-input-light text-sm">
                    </div>

                    <div>
                        <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                            City / Location <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="city" value="{{ old('city', $product->custom_fields['location'] ?? 'Makrana, Rajasthan') }}" required
                               class="w-full px-4 py-2.5 grail-input-light text-sm">
                    </div>
                </div>

                <div>
                    <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                        Seller Phone / WhatsApp Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone', $product->custom_fields['contact_phone'] ?? auth()->user()?->phone) }}" required
                           class="w-full px-4 py-2.5 grail-input-light text-sm">
                </div>

                <div>
                    <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                        Description &amp; Specifications <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="description" rows="5" required
                              class="w-full px-4 py-2.5 grail-input-light text-sm">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Existing Photos -->
                @if($product->images->isNotEmpty())
                    <div>
                        <label class="block grail-eyebrow !text-[#623562] mb-2">Current Photos</label>
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            @foreach($product->images as $img)
                                <img src="{{ $img->image_url }}" alt="Photo" class="w-16 h-16 rounded-xl object-cover border border-black/10 shrink-0">
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Upload Additional Photos -->
                <div>
                    <label class="block grail-eyebrow !text-[#623562] mb-1.5">
                        Add More Photos
                    </label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                           class="w-full text-xs text-[#1A1018]/65 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#ECD2F3]/60 file:text-[#1A1018] hover:file:bg-[#ECD2F3] cursor-pointer">
                </div>

                <div class="pt-4 border-t border-black/10 flex items-center justify-end gap-3">
                    <a href="{{ route('account.listings', ['tab' => 'used']) }}" class="px-5 py-2.5 grail-btn-ghost-light text-xs font-bold">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 grail-btn-obsidian font-bold text-xs shadow-sm cursor-pointer">
                        Save Changes ✓
                    </button>
                </div>
            </form>
        </div>

    </div>
    </div>
</x-layouts.public>

