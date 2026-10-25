<x-layouts.public 
    title="Create New Freelance Service — Vendor Studio | K71"
    metaDescription="Publish a new 3-tier freelance service package on K71 Services."
>
    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- HEADER -->
            <div class="flex items-center justify-between gap-4 pb-4 border-b border-purple-100">
                <div>
                    <a href="{{ route('vendor.services.index') }}" class="text-xs text-purple-700 hover:text-purple-900 font-bold flex items-center gap-1 mb-1">
                        ← Back to My Services
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">Create a New Service Gig</h1>
                    <p class="text-xs text-zinc-500 mt-0.5">Configure your service overview, 3-tier pricing (Basic, Standard, Premium), and client requirements.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <div class="font-bold">Please check the required fields:</div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- CREATION FORM (Glass Container) -->
            <form action="{{ route('vendor.services.store') }}" method="POST" enctype="multipart/form-data" 
                  class="p-6 sm:p-10 rounded-3xl bg-white/90 backdrop-blur-xl border border-purple-200 shadow-xl space-y-8">
                @csrf

                <!-- SECTION 1: GIG OVERVIEW -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider pb-2 border-b border-purple-100">
                        1. Service Overview
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Service Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               placeholder="e.g. I will design a modern minimalist luxury logo and complete brand identity"
                               class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-800 mb-1">Service Category *</label>
                            <select name="category_id" required
                                    class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                                <option value="">Select a Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-800 mb-1">Service Cover Images *</label>
                            <input type="file" name="images[]" multiple required
                                   class="w-full text-xs text-zinc-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Detailed Description *</label>
                        <textarea name="description" rows="6" required
                                  placeholder="Provide an in-depth explanation of your process, deliverables, software used, and benefits..."
                                  class="w-full p-4 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- SECTION 2: 3-TIER PACKAGES (BASIC, STANDARD, PREMIUM) -->
                <div class="space-y-6 pt-4 border-t border-purple-100">
                    <div>
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">
                            2. 3-Tier Pricing Packages
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Offer clear options to fit different client budgets and timelines.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- TIER 1: BASIC -->
                        <div class="p-5 rounded-2xl border-2 border-purple-200 bg-purple-50/30 space-y-3">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-700 block">Tier 1: Basic *</span>
                            
                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Package Title *</label>
                                <input type="text" name="basic_title" value="{{ old('basic_title', 'Starter Package') }}" required
                                       class="w-full p-2.5 rounded-xl border border-purple-200 text-xs bg-white">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Brief Description *</label>
                                <textarea name="basic_description" rows="2" required
                                          class="w-full p-2.5 rounded-xl border border-purple-200 text-xs bg-white">{{ old('basic_description', 'Essential starter deliverables with fast turnaround.') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-700 block">Price (₹) *</label>
                                    <input type="number" name="basic_price" value="{{ old('basic_price', '999') }}" required min="50" step="50"
                                           class="w-full p-2 rounded-xl border border-purple-200 text-xs font-bold text-purple-700 bg-white">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-700 block">Days *</label>
                                    <input type="number" name="basic_delivery_days" value="{{ old('basic_delivery_days', 2) }}" required min="1" max="90"
                                           class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Revisions *</label>
                                <input type="number" name="basic_revisions" value="{{ old('basic_revisions', 2) }}" required min="0" max="20"
                                       class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Deliverables Checklist (1 per line)</label>
                                <textarea name="basic_features" rows="3"
                                          placeholder="1 Design concept&#10;High-Res PNG &amp; JPG&#10;Commercial license"
                                          class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">{{ old('basic_features') }}</textarea>
                            </div>
                        </div>

                        <!-- TIER 2: STANDARD -->
                        <div class="p-5 rounded-2xl border-2 border-purple-300 bg-purple-50/50 space-y-3">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-800 block">Tier 2: Standard (Recommended)</span>
                            
                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Package Title</label>
                                <input type="text" name="standard_title" value="{{ old('standard_title', 'Pro Package') }}"
                                       class="w-full p-2.5 rounded-xl border border-purple-200 text-xs bg-white">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Brief Description</label>
                                <textarea name="standard_description" rows="2"
                                          class="w-full p-2.5 rounded-xl border border-purple-200 text-xs bg-white">{{ old('standard_description', 'Most popular choice with source files and enhanced scope.') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-700 block">Price (₹)</label>
                                    <input type="number" name="standard_price" value="{{ old('standard_price', '2499') }}" min="50" step="50"
                                           class="w-full p-2 rounded-xl border border-purple-200 text-xs font-bold text-purple-700 bg-white">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-700 block">Days</label>
                                    <input type="number" name="standard_delivery_days" value="{{ old('standard_delivery_days', 4) }}" min="1" max="90"
                                           class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Revisions</label>
                                <input type="number" name="standard_revisions" value="{{ old('standard_revisions', 5) }}" min="0" max="20"
                                       class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Deliverables Checklist (1 per line)</label>
                                <textarea name="standard_features" rows="3"
                                          placeholder="3 Design concepts&#10;Editable Source Files&#10;Social media kit&#10;VIP Support"
                                          class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">{{ old('standard_features') }}</textarea>
                            </div>
                        </div>

                        <!-- TIER 3: PREMIUM -->
                        <div class="p-5 rounded-2xl border-2 border-purple-400 bg-purple-50/70 space-y-3">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-900 block">Tier 3: Premium Suite</span>
                            
                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Package Title</label>
                                <input type="text" name="premium_title" value="{{ old('premium_title', 'Enterprise Suite') }}"
                                       class="w-full p-2.5 rounded-xl border border-purple-200 text-xs bg-white">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Brief Description</label>
                                <textarea name="premium_description" rows="2"
                                          class="w-full p-2.5 rounded-xl border border-purple-200 text-xs bg-white">{{ old('premium_description', 'Full VIP solution with maximum deliverables and priority delivery.') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-700 block">Price (₹)</label>
                                    <input type="number" name="premium_price" value="{{ old('premium_price', '5999') }}" min="50" step="50"
                                           class="w-full p-2 rounded-xl border border-purple-200 text-xs font-bold text-purple-700 bg-white">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-zinc-700 block">Days</label>
                                    <input type="number" name="premium_delivery_days" value="{{ old('premium_delivery_days', 7) }}" min="1" max="90"
                                           class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Revisions</label>
                                <input type="number" name="premium_revisions" value="{{ old('premium_revisions', 10) }}" min="0" max="20"
                                       class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-zinc-700 block">Deliverables Checklist (1 per line)</label>
                                <textarea name="premium_features" rows="3"
                                          placeholder="Unlimited concepts&#10;Complete Brand Guide&#10;Print Stationery&#10;Dedicated Manager"
                                          class="w-full p-2 rounded-xl border border-purple-200 text-xs bg-white">{{ old('premium_features') }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SECTION 3: CLIENT REQUIREMENTS PROMPT -->
                <div class="space-y-4 pt-4 border-t border-purple-100">
                    <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider pb-2 border-b border-purple-100">
                        3. Client Requirements Prompt
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">What information must the client provide when ordering?</label>
                        <textarea name="requirements_prompt" rows="3"
                                  placeholder="Example: Please provide your company name, target audience, brand color preferences, and any reference files or sketches..."
                                  class="w-full p-3.5 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">{{ old('requirements_prompt', 'Please describe your project specifications, preferred style, and attach any reference files.') }}</textarea>
                    </div>
                </div>

                <!-- SUBMIT ACTION -->
                <div class="pt-6 border-t border-purple-100 flex items-center justify-end gap-4">
                    <a href="{{ route('vendor.services.index') }}" class="px-5 py-3 rounded-2xl border border-zinc-200 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition">
                        Cancel
                    </a>

                    <button type="submit" 
                            class="px-8 py-3.5 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-purple-500/25">
                        Publish Service Gig →
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-layouts.public>
