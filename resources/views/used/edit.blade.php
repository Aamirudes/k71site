<x-layouts.used
    :title="'Edit Listing: ' . $product->title . ' | K71 Used'"
    robotsContent="noindex,nofollow"
>
    @php
        $flatCategories = [];
        foreach ($parentCategories as $pCat) {
            $flatCategories[] = ['id' => $pCat->id, 'name' => $pCat->name, 'parent_id' => $pCat->id];
            foreach ($pCat->children as $chCat) {
                $flatCategories[] = ['id' => $chCat->id, 'name' => $pCat->name . ' › ' . $chCat->name, 'parent_id' => $pCat->id];
            }
        }
        $existingPhotos = $product->images->map(fn ($img) => [
            'id' => $img->id,
            'url' => $img->url,
        ])->values()->all();
        $cf = $product->custom_fields ?? [];
        $currentSpecs = is_array($cf['specs'] ?? null) ? $cf['specs'] : [];
        $fulfillment = $product->fulfillment_options ?? [];
        $selectedFulfillment = old('fulfillment_modes', array_keys(array_filter([
            'local_pickup' => !empty($fulfillment['local_pickup']),
            'public_meetup' => !empty($fulfillment['public_meetup']),
            'seller_shipping' => !empty($fulfillment['seller_shipping']),
        ])) ?: ['local_pickup']);
        $effStatus = $product->effectiveUsedStatus();
    @endphp

    <section class="grail-hero-band py-7 px-3 sm:px-6 border-b border-white/10">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('used.dashboard', ['tab' => 'listings']) }}" class="text-xs text-[#ECD2F3] hover:text-white font-semibold inline-flex items-center gap-1 mb-2">
                    ← Back to Used Dashboard
                </a>
                <span class="grail-eyebrow block mb-1">Listing Editor • {{ strtoupper($effStatus) }}</span>
                <h1 class="text-2xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">Edit Pre-Owned Listing</h1>
            </div>
            <a href="{{ route('used.show', $product->slug) }}" class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-xs font-bold text-white shrink-0">
                View Listing ↗
            </a>
        </div>
    </section>

    <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 px-3 sm:px-6 min-h-screen shadow-[0_-18px_50px_rgba(0,0,0,0.45)]"
         x-data="{
            flatCats: @js($flatCategories),
            fieldsByCategory: @js($fieldsByCategory),
            categoryId: '{{ old('category_id', $product->category_id) }}',
            currentSpecs: @js(old('specs', $currentSpecs)),
            photos: @js($existingPhotos),
            coverImageId: {{ (int) ($existingPhotos[0]['id'] ?? 0) }},
            removedIds: [],
            isNegotiable: {{ old('is_negotiable', $product->isNegotiable() ? '1' : '0') ? 'true' : 'false' }},
            fulfillmentShipping: {{ in_array('seller_shipping', $selectedFulfillment, true) ? 'true' : 'false' }},

            get effectiveFields() {
                const match = this.flatCats.find(c => String(c.id) === String(this.categoryId));
                const parentId = match ? String(match.parent_id) : String(this.categoryId);
                const pFields = this.fieldsByCategory[parentId] || [];
                const cFields = (String(this.categoryId) !== parentId) ? (this.fieldsByCategory[this.categoryId] || []) : [];
                const seen = new Set();
                return [...pFields, ...cFields].filter(f => {
                    if (seen.has(f.name)) return false;
                    seen.add(f.name);
                    return true;
                });
            },
            movePhoto(index, dir) {
                const target = index + dir;
                if (target < 0 || target >= this.photos.length) return;
                const copy = [...this.photos];
                const [item] = copy.splice(index, 1);
                copy.splice(target, 0, item);
                this.photos = copy;
            },
            toggleRemove(id) {
                if (this.removedIds.includes(id)) {
                    this.removedIds = this.removedIds.filter(x => x !== id);
                } else {
                    this.removedIds.push(id);
                }
            }
         }">
        <div class="max-w-4xl mx-auto">

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grail-card bg-white rounded-3xl border border-[#E6E2E8] p-5 sm:p-8">
                <form action="{{ route('used.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Ad Title *</label>
                        <input type="text" name="title" value="{{ old('title', $product->title) }}" required class="grail-input-light w-full px-4 py-2.5 text-sm font-semibold">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Category *</label>
                            <select name="category_id" x-model="categoryId" required class="grail-input-light w-full px-3.5 py-2.5 text-xs font-semibold">
                                @foreach($parentCategories as $pCat)
                                    <option value="{{ $pCat->id }}">{{ $pCat->name }} (General)</option>
                                    @foreach($pCat->children as $chCat)
                                        <option value="{{ $chCat->id }}">&nbsp;&nbsp;↳ {{ $pCat->name }} — {{ $chCat->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Condition Grade *</label>
                            <select name="condition_grade" required class="grail-input-light w-full px-3.5 py-2.5 text-xs font-semibold">
                                @foreach($conditions as $cKey => $cMeta)
                                    <option value="{{ $cKey }}" {{ old('condition_grade', $product->conditionMeta()['key']) === $cKey ? 'selected' : '' }}>
                                        {{ $cMeta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Brand</label>
                            <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Model Name</label>
                            <input type="text" name="model_name" value="{{ old('model_name', $product->model_name) }}" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Quantity Available</label>
                            <input type="number" name="quantity_available" value="{{ old('quantity_available', $product->quantity_available ?? 1) }}" min="1" max="50" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Age (Months Used)</label>
                            <input type="number" name="age_months" value="{{ old('age_months', $product->age_months) }}" min="0" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Purchase Year</label>
                            <input type="number" name="purchase_year" value="{{ old('purchase_year', $product->purchase_year) }}" min="1950" max="{{ date('Y') + 1 }}" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Packaging / Bill</label>
                            <select name="original_packaging" class="grail-input-light w-full px-3 py-2 text-xs font-semibold">
                                @foreach($packagingOptions as $pKey => $pLabel)
                                    <option value="{{ $pKey }}" {{ old('original_packaging', $product->original_packaging ?? 'item_only') === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs font-semibold cursor-pointer">
                        <input type="hidden" name="is_refurbished" value="0">
                        <input type="checkbox" name="is_refurbished" value="1" {{ old('is_refurbished', $product->is_refurbished) ? 'checked' : '' }} class="accent-[#623562]">
                        <span>Item is refurbished / reconditioned</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Included Accessories</label>
                            <input type="text" name="included_accessories" value="{{ old('included_accessories', $product->included_accessories) }}" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Reason for Selling</label>
                            <input type="text" name="reason_for_selling" value="{{ old('reason_for_selling', $product->reason_for_selling) }}" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <!-- Dynamic Category Attributes -->
                    <template x-if="effectiveFields.length > 0">
                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-black/10 space-y-3">
                            <div class="text-xs font-bold text-[#623562] uppercase tracking-wider">Category Attributes</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="field in effectiveFields" :key="field.name">
                                    <div>
                                        <label class="block text-[11px] font-bold text-[#1A1018]/75 mb-1" x-text="field.label"></label>
                                        <template x-if="field.field_type === 'select' && Array.isArray(field.options)">
                                            <select :name="'specs[' + field.name + ']'" class="grail-input-light w-full px-3 py-2 text-xs">
                                                <option value="">Select...</option>
                                                <template x-for="opt in field.options" :key="opt">
                                                    <option :value="opt" :selected="currentSpecs[field.name] === opt" x-text="opt"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="field.field_type !== 'select'">
                                            <input type="text" :name="'specs[' + field.name + ']'" :value="currentSpecs[field.name] || ''" class="grail-input-light w-full px-3 py-2 text-xs">
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Known Defects / Wear Disclosure</label>
                        <textarea name="defect_notes" rows="2" class="grail-input-light w-full px-3.5 py-2 text-xs resize-none">{{ old('defect_notes', $product->defect_notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Description *</label>
                        <textarea name="description" rows="4" required class="grail-input-light w-full px-4 py-2.5 text-sm">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <!-- Pricing & Privacy Location -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Asking Price (₹) *</label>
                            <input type="number" step="1" name="price" value="{{ old('price', (int) round($product->base_price / 100)) }}" required class="grail-input-light w-full px-3.5 py-2.5 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Original Retail Price (₹)</label>
                            <input type="number" step="1" name="original_retail_price" value="{{ old('original_retail_price', $product->original_retail_price ? (int) round($product->original_retail_price / 100) : '') }}" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                        </div>
                        <div x-show="isNegotiable">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Minimum Acceptable Offer (₹)</label>
                            <input type="number" step="1" name="min_offer_amount" value="{{ old('min_offer_amount', $product->min_offer_amount ? (int) round($product->min_offer_amount / 100) : '') }}" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                        </div>
                    </div>

                    <label class="flex items-center gap-2 p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs font-semibold cursor-pointer">
                        <input type="hidden" name="is_negotiable" value="0">
                        <input type="checkbox" name="is_negotiable" value="1" x-model="isNegotiable" class="accent-[#623562]">
                        <span>Open to buyer offers &amp; price negotiation</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">City *</label>
                            <input type="text" name="city" value="{{ old('city', $product->city ?: ($cf['location'] ?? '')) }}" required class="grail-input-light w-full px-3.5 py-2.5 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Public Neighborhood / Area</label>
                            <input type="text" name="locality" value="{{ old('locality', $product->locality) }}" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Private Exact Pickup Address (Shared only privately in chat / accepted deal)</label>
                        <input type="text" name="private_pickup_area" value="{{ old('private_pickup_area', $product->private_pickup_area) }}" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                    </div>

                    <!-- Fulfillment Modes -->
                    <div class="space-y-2 p-4 rounded-2xl bg-[#FAFAF8] border border-black/10 text-xs">
                        <div class="font-bold text-[#1A1018]">Delivery &amp; Handover Methods</div>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                <input type="checkbox" name="fulfillment_modes[]" value="local_pickup" {{ in_array('local_pickup', $selectedFulfillment, true) ? 'checked' : '' }} class="accent-[#623562]">
                                <span>Local Pickup</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                <input type="checkbox" name="fulfillment_modes[]" value="public_meetup" {{ in_array('public_meetup', $selectedFulfillment, true) ? 'checked' : '' }} class="accent-[#623562]">
                                <span>Public Meetup Spot</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                <input type="checkbox" name="fulfillment_modes[]" value="seller_shipping" x-model="fulfillmentShipping" class="accent-[#623562]">
                                <span>Seller-Arranged Shipping</span>
                            </label>
                        </div>
                        <div x-show="fulfillmentShipping" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <input type="number" name="shipping_charge" value="{{ old('shipping_charge', !empty($fulfillment['shipping_charge']) ? ($fulfillment['shipping_charge'] / 100) : 0) }}" min="0" placeholder="Shipping charge (₹)" class="grail-input-light px-3 py-2 text-xs">
                            <input type="text" name="pickup_notes" value="{{ old('pickup_notes', $fulfillment['pickup_notes'] ?? '') }}" placeholder="Handover notes" class="grail-input-light px-3 py-2 text-xs">
                        </div>
                    </div>

                    <!-- Photo Reorder / Cover / Remove Manager -->
                    <template x-if="photos.length > 0">
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562]">Manage Current Photos (Reorder, Set Cover, or Remove)</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="(photo, index) in photos" :key="photo.id">
                                    <div :class="removedIds.includes(photo.id) ? 'opacity-40 border-rose-300' : 'border-black/15'"
                                         class="rounded-2xl border bg-[#FAFAF8] p-2 space-y-2 text-[11px]">
                                        <input type="hidden" name="photo_order[]" :value="photo.id">
                                        <div class="aspect-square rounded-xl overflow-hidden bg-white">
                                            <img :src="photo.url" class="w-full h-full object-cover" alt="">
                                        </div>
                                        <div class="flex items-center justify-between gap-1">
                                            <button type="button" @click="movePhoto(index, -1)" :disabled="index === 0" class="px-2 py-0.5 rounded bg-white border border-black/10 disabled:opacity-30 cursor-pointer">←</button>
                                            <label class="inline-flex items-center gap-1 cursor-pointer font-bold">
                                                <input type="radio" name="cover_image_id" :value="photo.id" x-model="coverImageId" class="accent-[#623562]">
                                                <span>Cover</span>
                                            </label>
                                            <button type="button" @click="movePhoto(index, 1)" :disabled="index === photos.length - 1" class="px-2 py-0.5 rounded bg-white border border-black/10 disabled:opacity-30 cursor-pointer">→</button>
                                        </div>
                                        <label class="flex items-center justify-center gap-1 text-rose-700 font-bold cursor-pointer">
                                            <input type="checkbox" name="remove_images[]" :value="photo.id" @change="toggleRemove(photo.id)" class="accent-rose-600">
                                            <span>Remove</span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Upload Additional Photos</label>
                        <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp"
                               class="w-full text-xs text-[#1A1018]/65 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#ECD2F3]/60 file:text-[#1A1018] hover:file:bg-[#ECD2F3] cursor-pointer">
                    </div>

                    <div class="pt-4 border-t border-black/10 flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('used.dashboard', ['tab' => 'listings']) }}" class="px-5 py-2.5 grail-btn-ghost-light text-xs font-bold">Cancel</a>
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!$product->isSold())
                                @if($effStatus !== 'paused')
                                    <button type="submit" name="action" value="pause" class="px-4 py-2.5 rounded-full bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold cursor-pointer">Save &amp; Pause</button>
                                @endif
                                @if($effStatus !== 'active')
                                    <button type="submit" name="action" value="publish" class="px-5 py-2.5 rounded-full bg-emerald-600 text-white text-xs font-bold cursor-pointer">Save &amp; Publish</button>
                                @endif
                            @endif
                            <button type="submit" name="action" value="save" class="px-6 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                                Save Changes ✓
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-layouts.used>
