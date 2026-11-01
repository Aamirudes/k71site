<x-layouts.public title="Edit Freelance Service Gig | K71 Services">
    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('account.listings', ['tab' => 'services']) }}" class="text-xs text-slate-500 hover:text-slate-900 font-bold flex items-center gap-1 mb-2">
                    <span>← Back to My Listings</span>
                </a>
                <h1 class="text-2xl font-black text-slate-950">Edit Freelance Gig &amp; Packages</h1>
                <p class="text-xs text-slate-500">Update gig description, category, and 3-tier pricing (Upwork/Fiverr style).</p>
            </div>
            <a href="{{ route('services.show', $product->slug) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                View Public Gig ↗
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
            <form action="{{ route('services.gig.update', $product) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Gig Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Category <span class="text-rose-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 outline-none bg-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Starting / Base Price (₹) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="1" name="base_price" value="{{ old('base_price', $product->base_price / 100) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 outline-none font-mono font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        About This Gig / Deliverables <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="description" rows="5" required
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 outline-none leading-relaxed">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- 3-TIER PACKAGES (BASIC, STANDARD, PREMIUM) -->
                @php
                    $basicPkg = $packages->firstWhere('tier', 'basic');
                    $standardPkg = $packages->firstWhere('tier', 'standard');
                    $premiumPkg = $packages->firstWhere('tier', 'premium');
                @endphp

                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-sm font-black text-slate-950 uppercase tracking-wider mb-4">
                        3-Tier Gig Package Tiers (Fiverr / Upwork Model)
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- Basic Tier -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                            <span class="text-xs font-bold text-slate-900 block">Tier 1: Basic</span>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Package Name</label>
                                <input type="text" name="packages[basic][name]" value="{{ $basicPkg?->name ?? 'Basic Starter' }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Price (₹)</label>
                                <input type="number" name="packages[basic][price]" value="{{ $basicPkg ? ($basicPkg->price / 100) : ($product->base_price / 100) }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white font-mono font-bold">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Delivery Days</label>
                                    <input type="number" name="packages[basic][delivery_days]" value="{{ $basicPkg?->delivery_days ?? 2 }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white font-mono">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Revisions</label>
                                    <input type="text" name="packages[basic][revisions]" value="{{ $basicPkg?->revisions ?? '1 Revision' }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Scope / Deliverables</label>
                                <textarea name="packages[basic][description]" rows="2" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">{{ $basicPkg?->description ?? 'Core design and basic source file delivery.' }}</textarea>
                            </div>
                        </div>

                        <!-- Standard Tier -->
                        <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-900 block">Tier 2: Standard</span>
                                <span class="text-[9px] bg-indigo-200 text-indigo-800 px-1.5 py-0.2 rounded font-bold uppercase">Popular</span>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Package Name</label>
                                <input type="text" name="packages[standard][name]" value="{{ $standardPkg?->name ?? 'Standard Pro' }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Price (₹)</label>
                                <input type="number" name="packages[standard][price]" value="{{ $standardPkg ? ($standardPkg->price / 100) : (($product->base_price / 100) * 2) }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white font-mono font-bold">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Delivery Days</label>
                                    <input type="number" name="packages[standard][delivery_days]" value="{{ $standardPkg?->delivery_days ?? 4 }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white font-mono">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Revisions</label>
                                    <input type="text" name="packages[standard][revisions]" value="{{ $standardPkg?->revisions ?? '3 Revisions' }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Scope / Deliverables</label>
                                <textarea name="packages[standard][description]" rows="2" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">{{ $standardPkg?->description ?? 'Full commercial assets, vector source files, and high-res render.' }}</textarea>
                            </div>
                        </div>

                        <!-- Premium Tier -->
                        <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200 space-y-3">
                            <span class="text-xs font-bold text-amber-900 block">Tier 3: Premium</span>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Package Name</label>
                                <input type="text" name="packages[premium][name]" value="{{ $premiumPkg?->name ?? 'VIP Executive' }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Price (₹)</label>
                                <input type="number" name="packages[premium][price]" value="{{ $premiumPkg ? ($premiumPkg->price / 100) : (($product->base_price / 100) * 3.5) }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white font-mono font-bold">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Delivery Days</label>
                                    <input type="number" name="packages[premium][delivery_days]" value="{{ $premiumPkg?->delivery_days ?? 7 }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white font-mono">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Revisions</label>
                                    <input type="text" name="packages[premium][revisions]" value="{{ $premiumPkg?->revisions ?? 'Unlimited' }}" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase">Scope / Deliverables</label>
                                <textarea name="packages[premium][description]" rows="2" class="w-full px-3 py-1.5 rounded-lg border text-xs bg-white">{{ $premiumPkg?->description ?? 'Complete VIP priority delivery, full copyright transfer, and lifetime support.' }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('account.listings', ['tab' => 'services']) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition cursor-pointer">
                        Save Gig Changes ✓
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.public>

