<x-layouts.used
    title="Sell an Item — Post Used Listing | K71 Used"
    metaDescription="List pre-owned goods on K71 Used with transparent condition grading, genuine photos, and direct peer negotiation."
    robotsContent="noindex,nofollow"
>
    @php
        $categoriesTree = $parentCategories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'children' => $cat->children->map(fn ($ch) => [
                    'id' => $ch->id,
                    'name' => $ch->name,
                    'slug' => $ch->slug,
                ])->values()->all(),
            ];
        })->values()->all();

        $oldParentId = old('parent_category_id', $parentCategories->first()?->id ?? '');
        $oldCategoryId = old('category_id', $parentCategories->first()?->children->first()?->id ?? $oldParentId);
        $oldSpecs = old('specs', []);
    @endphp

    <section class="grail-hero-band py-7 sm:py-9 px-3 sm:px-6 lg:px-8 border-b border-white/10">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <a href="{{ route('used.index') }}" class="text-xs text-[#ECD2F3] hover:text-white font-semibold inline-flex items-center gap-1 mb-2">
                    ← Back to K71 Used
                </a>
                <span class="grail-eyebrow block mb-1">Sell on K71 Used</span>
                <h1 class="text-2xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                    Create a Pre-Owned Listing
                </h1>
                <p class="text-xs text-[#EFE9F2]/65 mt-1">
                    Selling as <strong class="text-white">{{ $profile->display_name }}</strong> ({{ '@' . $profile->handle }}). Upload genuine photos and honest condition details.
                </p>
            </div>
            <a href="{{ route('used.dashboard', ['tab' => 'listings']) }}" class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-xs font-semibold text-white shrink-0">
                My Dashboard →
            </a>
        </div>
    </section>

    @php
        $initialStep = 1;
        if ($errors->hasAny(['price', 'city', 'original_retail_price', 'min_offer_amount', 'locality', 'private_pickup_area'])) {
            $initialStep = 4;
        } elseif ($errors->hasAny(['photos', 'photos.*'])) {
            $initialStep = 3;
        } elseif ($errors->hasAny(['title', 'description', 'condition_grade', 'brand', 'model_name']) || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'specs.'))) {
            $initialStep = 2;
        }
    @endphp
    <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 px-3 sm:px-6 lg:px-8 min-h-screen shadow-[0_-18px_50px_rgba(0,0,0,0.45)]"
         x-data="{
            step: {{ $initialStep }},
            categories: @js($categoriesTree),
            fieldsByCategory: @js($fieldsByCategory),
            parentId: '{{ $oldParentId }}',
            categoryId: '{{ $oldCategoryId }}',
            oldSpecs: @js($oldSpecs),
            isNegotiable: {{ old('is_negotiable', '1') ? 'true' : 'false' }},
            fulfillmentShipping: {{ in_array('seller_shipping', (array) old('fulfillment_modes', [])) ? 'true' : 'false' }},
            previews: [],
            coverIndex: {{ (int) old('cover_index', 0) }},

            get selectedParent() {
                return this.categories.find(c => String(c.id) === String(this.parentId)) || null;
            },
            get subcategories() {
                return this.selectedParent ? this.selectedParent.children : [];
            },
            get effectiveFields() {
                const parentFields = this.fieldsByCategory[this.parentId] || [];
                const childFields = (this.categoryId && String(this.categoryId) !== String(this.parentId))
                    ? (this.fieldsByCategory[this.categoryId] || [])
                    : [];
                const seen = new Set();
                return [...parentFields, ...childFields].filter(f => {
                    if (seen.has(f.name)) return false;
                    seen.add(f.name);
                    return true;
                });
            },
            selectParent(id) {
                this.parentId = String(id);
                const p = this.selectedParent;
                if (p && p.children && p.children.length > 0) {
                    this.categoryId = String(p.children[0].id);
                } else {
                    this.categoryId = String(id);
                }
            },
            handlePhotos(event) {
                this.previews = [];
                this.coverIndex = 0;
                const files = event.target.files;
                if (!files) return;
                Array.from(files).slice(0, 10).forEach((file, idx) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push({ idx, url: e.target.result, name: file.name });
                    };
                    reader.readAsDataURL(file);
                });
            }
         }">
        <div class="max-w-4xl mx-auto space-y-6">

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">{{ session('error') }}</div>
            @endif

            @if(session('duplicate_warning'))
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 text-xs space-y-2">
                    <div class="font-bold">⚠️ {{ session('duplicate_warning') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold mb-1">Please review the highlighted fields:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Interactive Step Progress Nav (All steps remain inside one clean POST form so zero data is ever lost between steps!) -->
            <div class="grid grid-cols-4 gap-2">
                @foreach([1 => 'Category', 2 => 'Condition & Specs', 3 => 'Photos', 4 => 'Price & Publish'] as $sNum => $sLabel)
                    <button type="button" @click="step = {{ $sNum }}"
                            :class="step === {{ $sNum }} ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018]' : 'bg-white text-[#1A1018]/70 border-black/10 hover:border-[#623562]/50'"
                            class="p-2.5 sm:p-3 rounded-2xl border text-left transition cursor-pointer">
                        <div class="text-[10px] font-bold uppercase tracking-wider opacity-75">Step {{ $sNum }}</div>
                        <div class="text-xs font-bold truncate">{{ $sLabel }}</div>
                    </button>
                @endforeach
            </div>

            <form action="{{ route('used.sell.store') }}" method="POST" enctype="multipart/form-data" class="grail-card bg-white rounded-3xl border border-[#E6E2E8] p-5 sm:p-8 space-y-6 shadow-xs">
                @csrf
                <input type="hidden" name="parent_category_id" :value="parentId">

                @if(session('duplicate_warning'))
                    <label class="flex items-center gap-2 p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-xs font-bold text-amber-950 cursor-pointer">
                        <input type="checkbox" name="allow_duplicate" value="1" class="accent-[#623562]">
                        <span>Confirm intentional separate listing (I am selling another distinct unit)</span>
                    </label>
                @endif

                <!-- STEP 1: CATEGORY & SUBCATEGORY -->
                <div x-show="step === 1" class="space-y-5">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Step 1 of 4</span>
                        <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Choose Category &amp; Subcategory</h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button" @click="selectParent(cat.id)"
                                    :class="String(parentId) === String(cat.id) ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018]' : 'bg-[#FAFAF8] text-[#1A1018] border-black/10 hover:bg-[#F8EFFA]'"
                                    class="p-3.5 rounded-2xl border text-left text-xs font-bold transition cursor-pointer">
                                <div x-text="cat.name"></div>
                            </button>
                        </template>
                    </div>

                    <div x-show="subcategories.length > 0" class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70">Subcategory</label>
                        <select name="category_id" x-model="categoryId" class="grail-input-light w-full px-3.5 py-2.5 text-sm font-semibold">
                            <option :value="parentId">General / All Under This Category</option>
                            <template x-for="sub in subcategories" :key="sub.id">
                                <option :value="String(sub.id)" x-text="sub.name"></option>
                            </template>
                        </select>
                    </div>
                    <template x-if="subcategories.length === 0">
                        <input type="hidden" name="category_id" :value="parentId">
                    </template>

                    <div class="flex justify-end pt-3 border-t border-black/10">
                        <button type="button" @click="step = 2" class="px-6 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                            Continue to Item Details →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: ITEM INFORMATION, CONDITION & CATEGORY SPECS -->
                <div x-show="step === 2" x-cloak class="space-y-5">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Step 2 of 4</span>
                        <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Item Details &amp; Honest Condition</h2>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Item Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               placeholder="e.g. Apple iPhone 14 Pro 256GB Space Black — Unlocked with Box"
                               class="grail-input-light w-full px-4 py-2.5 text-sm font-semibold">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Brand</label>
                            <input type="text" name="brand" value="{{ old('brand') }}" placeholder="e.g. Apple, Sony, IKEA" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Model Name</label>
                            <input type="text" name="model_name" value="{{ old('model_name') }}" placeholder="e.g. A2890 / M2 Air" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Quantity (Only if > 1)</label>
                            <input type="number" name="quantity_available" value="{{ old('quantity_available', 1) }}" min="1" max="50" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Condition Grade *</label>
                            <select name="condition_grade" required class="grail-input-light w-full px-3 py-2 text-xs font-semibold">
                                @foreach($conditions as $cKey => $cMeta)
                                    <option value="{{ $cKey }}" {{ old('condition_grade', 'good') === $cKey ? 'selected' : '' }}>
                                        {{ $cMeta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Age (Months Used)</label>
                            <input type="number" name="age_months" value="{{ old('age_months') }}" min="0" placeholder="e.g. 14" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Purchase Year</label>
                            <input type="number" name="purchase_year" value="{{ old('purchase_year') }}" min="1950" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') - 1 }}" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <label class="flex items-center gap-2 p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs font-semibold cursor-pointer">
                        <input type="hidden" name="is_refurbished" value="0">
                        <input type="checkbox" name="is_refurbished" value="1" {{ old('is_refurbished') ? 'checked' : '' }} class="accent-[#623562]">
                        <span>This item is refurbished / reconditioned</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Original Packaging / Bill</label>
                            <select name="original_packaging" class="grail-input-light w-full px-3 py-2 text-xs font-semibold">
                                @foreach($packagingOptions as $pKey => $pLabel)
                                    <option value="{{ $pKey }}" {{ old('original_packaging', 'item_only') === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Included Accessories</label>
                            <input type="text" name="included_accessories" value="{{ old('included_accessories') }}" placeholder="e.g. Original charger, case, extra cable" class="grail-input-light w-full px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <!-- Dynamic Configurable Category Attributes -->
                    <template x-if="effectiveFields.length > 0">
                        <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-black/10 space-y-3">
                            <div class="text-xs font-bold text-[#623562] uppercase tracking-wider">Category-Specific Details</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="field in effectiveFields" :key="field.name">
                                    <div>
                                        <label class="block text-[11px] font-bold text-[#1A1018]/75 mb-1">
                                            <span x-text="field.label"></span>
                                            <span x-show="field.is_required" class="text-rose-600">*</span>
                                        </label>
                                        <template x-if="field.field_type === 'select' && Array.isArray(field.options)">
                                            <select :name="'specs[' + field.name + ']'" :required="field.is_required" class="grail-input-light w-full px-3 py-2 text-xs">
                                                <option value="">Select...</option>
                                                <template x-for="opt in field.options" :key="opt">
                                                    <option :value="opt" :selected="oldSpecs[field.name] === opt" x-text="opt"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="field.field_type !== 'select'">
                                            <input :type="field.field_type === 'number' ? 'number' : 'text'"
                                                   :name="'specs[' + field.name + ']'"
                                                   :value="oldSpecs[field.name] || ''"
                                                   :placeholder="field.placeholder || ''"
                                                   :required="field.is_required"
                                                   class="grail-input-light w-full px-3 py-2 text-xs">
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Known Defects, Scratches, or Issues (Honesty Builds Trust)</label>
                        <textarea name="defect_notes" rows="2" placeholder="Describe any dents, dead pixels, battery wear, missing parts, or write 'None observed'."
                                  class="grail-input-light w-full px-3.5 py-2 text-xs resize-none">{{ old('defect_notes') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Full Item Description * <span class="font-normal text-[#1A1018]/50">(Required to publish; optional for draft)</span></label>
                        <textarea name="description" rows="4" placeholder="Explain usage history, working status, warranty left, accessories..."
                                  class="grail-input-light w-full px-3.5 py-2.5 text-xs resize-y">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Reason for Selling (Optional)</label>
                        <input type="text" name="reason_for_selling" value="{{ old('reason_for_selling') }}" placeholder="e.g. Upgrading to newer model / Moving house" class="grail-input-light w-full px-3.5 py-2 text-xs">
                    </div>

                    <div class="flex justify-between pt-3 border-t border-black/10">
                        <button type="button" @click="step = 1" class="px-5 py-2.5 grail-btn-ghost-light text-xs font-bold cursor-pointer">← Category</button>
                        <button type="button" @click="step = 3" class="px-6 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">Continue to Photos →</button>
                    </div>
                </div>

                <!-- STEP 3: GENUINE PRODUCT PHOTOS -->
                <div x-show="step === 3" x-cloak class="space-y-5">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Step 3 of 4</span>
                        <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Upload Real Product Photos</h2>
                        <p class="text-xs text-[#1A1018]/60 mt-1">Only upload genuine photos of your actual item (JPEG, PNG, WebP — max 10 photos). No stock photos.</p>
                    </div>

                    <label class="relative flex flex-col items-center justify-center p-8 border-2 border-dashed border-[#1A1018]/25 hover:border-[#1A1018] rounded-3xl bg-[#F8EFFA]/60 hover:bg-[#F8EFFA] transition cursor-pointer text-center">
                        <span class="text-3xl mb-2">📷</span>
                        <span class="text-xs sm:text-sm font-bold text-[#1A1018]">Select photos from your camera or gallery</span>
                        <span class="text-[11px] text-[#1A1018]/55 mt-1">Clear shots of front, back, bill/box, and any wear build buyer trust</span>
                        <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" @change="handlePhotos($event)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    </label>

                    <input type="hidden" name="cover_index" :value="coverIndex">

                    <template x-if="previews.length > 0">
                        <div class="space-y-2">
                            <div class="text-xs font-bold text-[#1A1018]">Click a photo below to set it as the Cover Photo:</div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="p in previews" :key="p.idx">
                                    <div @click="coverIndex = p.idx"
                                         :class="coverIndex === p.idx ? 'ring-2 ring-[#623562] border-[#1A1018]' : 'border-black/15'"
                                         class="relative aspect-square rounded-2xl overflow-hidden border bg-white cursor-pointer">
                                        <img :src="p.url" class="w-full h-full object-cover" alt="">
                                        <span x-show="coverIndex === p.idx" class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[10px] font-bold">Cover</span>
                                        <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded bg-black/65 text-white text-[10px]" x-text="'#' + (p.idx + 1)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-between pt-3 border-t border-black/10">
                        <button type="button" @click="step = 2" class="px-5 py-2.5 grail-btn-ghost-light text-xs font-bold cursor-pointer">← Details</button>
                        <button type="button" @click="step = 4" class="px-6 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">Continue to Price &amp; Pickup →</button>
                    </div>
                </div>

                <!-- STEP 4: PRICING, HANDOVER, PRIVACY & SUBMIT -->
                <div x-show="step === 4" x-cloak class="space-y-5">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Step 4 of 4</span>
                        <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Pricing, Location Privacy &amp; Publish</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Asking Price (₹) *</label>
                            <input type="number" name="price" value="{{ old('price') }}" required min="1" placeholder="e.g. 24000"
                                   class="grail-input-light w-full px-3.5 py-2.5 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Original Retail Price (₹, Optional)</label>
                            <input type="number" name="original_retail_price" value="{{ old('original_retail_price') }}" min="1" placeholder="Only if genuinely known"
                                   class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                        </div>
                        <div x-show="isNegotiable">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Minimum Acceptable Offer (₹)</label>
                            <input type="number" name="min_offer_amount" value="{{ old('min_offer_amount') }}" min="1" placeholder="Optional auto-floor"
                                   class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                        </div>
                    </div>

                    <label class="flex items-center gap-2 p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs font-semibold cursor-pointer">
                        <input type="hidden" name="is_negotiable" value="0">
                        <input type="checkbox" name="is_negotiable" value="1" x-model="isNegotiable" class="accent-[#623562]">
                        <span>Open to buyer offers &amp; price negotiation (Recommended)</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">City *</label>
                            <input type="text" name="city" list="used-cities-list" value="{{ old('city', $profile->city ?? 'Jaipur') }}" required
                                   class="grail-input-light w-full px-3.5 py-2.5 text-xs font-semibold">
                            <datalist id="used-cities-list">
                                @foreach($cities as $c)
                                    <option value="{{ $c }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Public Neighborhood / Area (Optional)</label>
                            <input type="text" name="locality" value="{{ old('locality', $profile->locality) }}" placeholder="e.g. Vaishali Nagar (shown publicly)"
                                   class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Private Exact Pickup / Meetup Address (Hidden From Public Listing)</label>
                        <input type="text" name="private_pickup_area" value="{{ old('private_pickup_area') }}" placeholder="Only shared by you in buyer enquiry chat or after accepting an offer"
                               class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                    </div>

                    <div class="space-y-2 p-4 rounded-2xl bg-[#FAFAF8] border border-black/10 text-xs">
                        <div class="font-bold text-[#1A1018]">Delivery &amp; Handover Methods</div>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                <input type="checkbox" name="fulfillment_modes[]" value="local_pickup" {{ in_array('local_pickup', (array) old('fulfillment_modes', ['local_pickup'])) ? 'checked' : '' }} class="accent-[#623562]">
                                <span>Local Pickup</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                <input type="checkbox" name="fulfillment_modes[]" value="public_meetup" {{ in_array('public_meetup', (array) old('fulfillment_modes', [])) ? 'checked' : '' }} class="accent-[#623562]">
                                <span>Public Meetup Spot</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                <input type="checkbox" name="fulfillment_modes[]" value="seller_shipping" x-model="fulfillmentShipping" class="accent-[#623562]">
                                <span>Seller-Arranged Shipping</span>
                            </label>
                        </div>
                        <div x-show="fulfillmentShipping" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <input type="number" name="shipping_charge" value="{{ old('shipping_charge', 0) }}" min="0" placeholder="Optional shipping cost (₹)" class="grail-input-light px-3 py-2 text-xs">
                            <input type="text" name="pickup_notes" value="{{ old('pickup_notes') }}" placeholder="e.g. Weekends 10am-6pm" class="grail-input-light px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-black/10">
                        <button type="button" @click="step = 3" class="px-5 py-2.5 grail-btn-ghost-light text-xs font-bold cursor-pointer">← Photos</button>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="submit" name="action" value="draft" class="px-4 py-2.5 grail-btn-ghost-light text-xs font-bold cursor-pointer">
                                Save Draft
                            </button>
                            <button type="submit" name="action" value="preview" class="px-4 py-2.5 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3] text-[#1A1018] text-xs font-bold cursor-pointer">
                                Save &amp; Preview
                            </button>
                            <button type="submit" name="action" value="publish" class="px-6 py-2.5 grail-btn-obsidian text-xs font-bold shadow-md cursor-pointer">
                                Publish Listing Live →
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-layouts.used>
