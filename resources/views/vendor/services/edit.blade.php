<x-layouts.vendor title="Edit Service">
    @php
        $pkgByTier = $packages->keyBy('tier');
        $basic = $pkgByTier->get('basic');
        $standard = $pkgByTier->get('standard');
        $premium = $pkgByTier->get('premium');
        $baseInRupees = $basic ? (float) $basic->price : round(((float) $product->base_price) / 100, 2);
    @endphp

    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ $backRoute }}" class="text-xs font-bold text-zinc-500 hover:text-black">← Back to Services Studio</a>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-1">Edit Service: {{ $product->title }}</h1>
                <p class="text-xs text-zinc-500 mt-0.5">Changes update both K71 Services Marketplace and your personal website simultaneously.</p>
            </div>
            <a href="{{ route('services.show', $product->slug) }}" target="_blank" class="px-4 py-2 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-800 hover:bg-zinc-50">
                View Public Page ↗
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <div class="font-bold">Please fix the following errors:</div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $updateRoute }}" method="POST" class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-800 mb-1">Service Title *</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-medium focus:border-black focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-800 mb-1">Category *</label>
                    <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-medium focus:border-black focus:outline-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-800 mb-1">Base / Starting Price (₹) *</label>
                    <input type="number" name="base_price" step="0.01" min="10" value="{{ old('base_price', $baseInRupees) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-bold focus:border-black focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-800 mb-1">Listing Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-medium focus:border-black focus:outline-none">
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active (Published)</option>
                        <option value="paused" {{ old('status', $product->status) === 'paused' ? 'selected' : '' }}>Paused (Hidden)</option>
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="flex flex-col justify-center gap-2 pt-2">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-800 cursor-pointer">
                        <input type="hidden" name="show_on_website" value="0">
                        <input type="checkbox" name="show_on_website" value="1" {{ old('show_on_website', $product->show_on_website ?? true) ? 'checked' : '' }} class="rounded border-zinc-400">
                        <span>Show on Personal Website</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-800 cursor-pointer">
                        <input type="hidden" name="is_website_featured" value="0">
                        <input type="checkbox" name="is_website_featured" value="1" {{ old('is_website_featured', $product->is_website_featured) ? 'checked' : '' }} class="rounded border-zinc-400">
                        <span>Highlight as Featured on Personal Website</span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-800 mb-1">Description *</label>
                    <textarea name="description" rows="5" required
                              class="w-full p-3.5 rounded-xl border border-zinc-300 text-xs sm:text-sm focus:border-black focus:outline-none">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-200 space-y-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-zinc-900">Package Tiers</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach(['basic' => ['Basic', $basic], 'standard' => ['Standard', $standard], 'premium' => ['Premium', $premium]] as $tierKey => [$tierLabel, $pkg])
                        <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50/70 space-y-2.5">
                            <div class="text-xs font-black uppercase tracking-wider text-zinc-900">{{ $tierLabel }}</div>
                            <div>
                                <label class="text-[11px] font-bold text-zinc-600 block">Tier Title</label>
                                <input type="text" name="packages[{{ $tierKey }}][title]" value="{{ old("packages.$tierKey.title", $pkg?->title) }}"
                                       placeholder="{{ $tierLabel }} Package"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-zinc-300 text-xs bg-white">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-600 block">Price (₹)</label>
                                    <input type="number" step="1" min="1" name="packages[{{ $tierKey }}][price]" value="{{ old("packages.$tierKey.price", $pkg?->price) }}"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-zinc-300 text-xs font-bold bg-white">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-600 block">Days</label>
                                    <input type="number" min="1" max="90" name="packages[{{ $tierKey }}][delivery_days]" value="{{ old("packages.$tierKey.delivery_days", $pkg?->delivery_days ?? 3) }}"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-zinc-300 text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-zinc-600 block">Revisions</label>
                                <input type="number" min="0" max="99" name="packages[{{ $tierKey }}][revisions]" value="{{ old("packages.$tierKey.revisions", $pkg?->revisions ?? 2) }}"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-zinc-300 text-xs bg-white">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-zinc-600 block">Scope Summary</label>
                                <textarea name="packages[{{ $tierKey }}][description]" rows="2"
                                          class="w-full px-2.5 py-1.5 rounded-lg border border-zinc-300 text-xs bg-white">{{ old("packages.$tierKey.description", $pkg?->description) }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-200 flex items-center justify-end gap-3">
                <a href="{{ $backRoute }}" class="px-4 py-2.5 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-700 hover:bg-zinc-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                    Save &amp; Sync Service
                </button>
            </div>
        </form>
    </div>
</x-layouts.vendor>

