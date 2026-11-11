<x-layouts.public title="Edit Freelance Service Gig | K71 Services" :fullWidth="true">
    <div class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">
        
        <!-- OBSIDIAN HERO HEADER -->
        <section class="grail-hero-band py-8 sm:py-10 px-4 sm:px-6 border-b border-white/10">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <a href="{{ $backRoute ?? route('account.listings', ['tab' => 'services']) }}" class="text-xs text-[#ECD2F3] hover:text-[#FFCDFF] font-semibold inline-flex items-center gap-1 mb-2 transition">
                        <span>← Back to My Listings</span>
                    </a>
                    <h1 class="text-2xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">Edit Freelance Gig &amp; Packages</h1>
                    <p class="text-xs sm:text-sm text-[#EFE9F2]/70 mt-1">Update gig description, category, and 3-tier pricing.</p>
                </div>
                <a href="{{ route('services.show', $product->slug) }}" target="_blank" class="grail-btn-ghost-dark px-4 py-2 text-xs font-semibold shrink-0 self-start sm:self-center">
                    View Public Gig ↗
                </a>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 pb-20 px-4 sm:px-6 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-4xl mx-auto">

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grail-card bg-white rounded-3xl border border-[#E6E2E8] shadow-xs p-6 sm:p-8">
                    <form action="{{ $updateRoute ?? route('services.gig.update', $product) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.12em] text-[#1A1018]/75 mb-1.5">
                                Gig Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                                   class="grail-input-light w-full px-4 py-2.5 text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-[0.12em] text-[#1A1018]/75 mb-1.5">
                                    Category <span class="text-rose-500">*</span>
                                </label>
                                <select name="category_id" required
                                        class="grail-input-light w-full px-3.5 py-2.5 text-sm">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-[0.12em] text-[#1A1018]/75 mb-1.5">
                                    Starting / Base Price (₹) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="1" name="base_price" value="{{ old('base_price', $product->base_price / 100) }}" required
                                       class="grail-input-light w-full px-4 py-2.5 text-sm font-mono font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.12em] text-[#1A1018]/75 mb-1.5">
                                About This Gig / Deliverables <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="description" rows="5" required
                                      class="grail-input-light w-full px-4 py-2.5 text-sm leading-relaxed">{{ old('description', $product->description) }}</textarea>
                        </div>

                        <!-- 3-TIER PACKAGES (BASIC, STANDARD, PREMIUM) -->
                        @php
                            $basicPkg = $packages->firstWhere('tier', 'basic');
                            $standardPkg = $packages->firstWhere('tier', 'standard');
                            $premiumPkg = $packages->firstWhere('tier', 'premium');
                        @endphp

                        <div class="pt-6 border-t border-[#E6E2E8]">
                            <h3 class="grail-eyebrow !text-[#623562] mb-4">
                                3-Tier Gig Package Tiers
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                
                                <!-- Basic Tier -->
                                <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] space-y-3">
                                    <span class="text-xs font-bold text-[#1A1018] block">Tier 1: Basic</span>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Package Name</label>
                                        <input type="text" name="packages[basic][name]" value="{{ $basicPkg?->title ?? $basicPkg?->name ?? 'Basic Starter' }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Price (₹)</label>
                                        <input type="number" step="0.01" name="packages[basic][price]" value="{{ $basicPkg ? (float) $basicPkg->price : ($product->base_price / 100) }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono font-bold">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Delivery Days</label>
                                            <input type="number" name="packages[basic][delivery_days]" value="{{ $basicPkg?->delivery_days ?? 2 }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Revisions</label>
                                            <input type="number" min="0" name="packages[basic][revisions]" value="{{ $basicPkg?->revisions ?? 1 }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Scope / Deliverables</label>
                                        <textarea name="packages[basic][description]" rows="2" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white">{{ $basicPkg?->description ?? 'Core design and basic source file delivery.' }}</textarea>
                                    </div>
                                </div>

                                <!-- Standard Tier -->
                                <div class="p-4 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#623562] block">Tier 2: Standard</span>
                                        <span class="text-[9px] bg-[#1A1018] text-[#ECD2F3] px-2 py-0.5 rounded-full font-bold uppercase">Popular</span>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Package Name</label>
                                        <input type="text" name="packages[standard][name]" value="{{ $standardPkg?->title ?? $standardPkg?->name ?? 'Standard Pro' }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Price (₹)</label>
                                        <input type="number" step="0.01" name="packages[standard][price]" value="{{ $standardPkg ? (float) $standardPkg->price : (($product->base_price / 100) * 2) }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono font-bold">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Delivery Days</label>
                                            <input type="number" name="packages[standard][delivery_days]" value="{{ $standardPkg?->delivery_days ?? 4 }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Revisions</label>
                                            <input type="number" min="0" name="packages[standard][revisions]" value="{{ $standardPkg?->revisions ?? 3 }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Scope / Deliverables</label>
                                        <textarea name="packages[standard][description]" rows="2" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white">{{ $standardPkg?->description ?? 'Full commercial assets, vector source files, and high-res render.' }}</textarea>
                                    </div>
                                </div>

                                <!-- Premium Tier -->
                                <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-[#C9A24A]/45 space-y-3">
                                    <span class="text-xs font-bold text-[#C9A24A] block">Tier 3: Premium</span>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Package Name</label>
                                        <input type="text" name="packages[premium][name]" value="{{ $premiumPkg?->title ?? $premiumPkg?->name ?? 'VIP Executive' }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Price (₹)</label>
                                        <input type="number" step="0.01" name="packages[premium][price]" value="{{ $premiumPkg ? (float) $premiumPkg->price : (($product->base_price / 100) * 3.5) }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono font-bold">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Delivery Days</label>
                                            <input type="number" name="packages[premium][delivery_days]" value="{{ $premiumPkg?->delivery_days ?? 7 }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Revisions</label>
                                            <input type="number" min="0" name="packages[premium][revisions]" value="{{ $premiumPkg?->revisions ?? 10 }}" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white font-mono">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-[#1A1018]/55 uppercase">Scope / Deliverables</label>
                                        <textarea name="packages[premium][description]" rows="2" class="grail-input-light w-full px-3 py-1.5 text-xs bg-white">{{ $premiumPkg?->description ?? 'Complete VIP priority delivery, full copyright transfer, and lifetime support.' }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E6E2E8] flex items-center justify-end gap-3">
                            <a href="{{ $backRoute ?? route('account.listings', ['tab' => 'services']) }}" class="px-5 py-2.5 rounded-full text-xs font-bold text-[#1A1018]/65 hover:bg-[#EFE9F2] transition">
                                Cancel
                            </a>
                            <button type="submit" class="grail-btn-obsidian px-6 py-2.5 font-bold text-xs cursor-pointer">
                                Save Gig Changes ✓
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>

