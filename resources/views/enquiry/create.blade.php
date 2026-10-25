<x-layouts.enquiry 
    title="Post a Requirement / RFQ — K71 Enquiry Marketplace"
    metaDescription="Submit your product or service specifications, target budget, and quantity to receive competitive direct quotations from verified vendors on K71."
>
    <div class="max-w-3xl mx-auto py-6">
        
        <div class="mb-6">
            <a href="{{ route('enquiry.index') }}" class="text-xs text-amber-700 hover:text-amber-800 font-semibold mb-2 inline-flex items-center gap-1">
                <span>← Back to Enquiries Marketplace</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-zinc-950 tracking-tight">Post Your Buying Requirement (RFQ)</h1>
            <p class="text-xs sm:text-sm text-zinc-600 mt-1">Specify what you need, your target budget, and delivery timeline to receive direct vendor quotations.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <span class="font-bold block">Please correct the following:</span>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('enquiry.store') }}" method="POST" class="rounded-3xl bg-white border border-zinc-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            @csrf

            @if($targetVendor)
                <input type="hidden" name="vendor_id" value="{{ $targetVendor->id }}">
                <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Direct enquiry to: <strong>{{ $targetVendor->shop_name }}</strong></span>
                </div>
            @endif

            @if($targetProduct)
                <input type="hidden" name="product_id" value="{{ $targetProduct->id }}">
                <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 text-xs text-indigo-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Enquiry for catalogue item: <strong>{{ $targetProduct->title }}</strong></span>
                </div>
            @endif

            <!-- Requirement Title -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Requirement Title *</label>
                <input type="text" name="title" required value="{{ old('title', $targetProduct?->title ? 'Bulk requirement for ' . $targetProduct->title : '') }}" 
                       placeholder="e.g. Requirement for 500 Meters Pure Cotton Poplin Fabric"
                       class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
            </div>

            <!-- Category & Urgency -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Industry / Category</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                        <option value="">General Requirement</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Delivery Timeline</label>
                    <select name="urgency" class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                        <option value="flexible" {{ old('urgency') == 'flexible' ? 'selected' : '' }}>Flexible Timeline</option>
                        <option value="within_1_week" {{ old('urgency') == 'within_1_week' ? 'selected' : '' }}>Within 1 Week</option>
                        <option value="immediate" {{ old('urgency') == 'immediate' ? 'selected' : '' }}>Immediate / Urgent</option>
                    </select>
                </div>
            </div>

            <!-- Quantity & Target Budget -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Required Quantity *</label>
                    <input type="text" name="quantity" required value="{{ old('quantity') }}" placeholder="e.g. 500 meters / 200 boxes / 50 units"
                           class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Target Budget (₹ Optional)</label>
                    <input type="number" step="0.01" name="target_budget" value="{{ old('target_budget') }}" placeholder="e.g. 50000"
                           class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                </div>
            </div>

            <!-- Location Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Delivery City / Location *</label>
                    <input type="text" name="city" required value="{{ old('city', 'Jaipur') }}" placeholder="e.g. Jaipur, Rajasthan"
                           class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Pincode (Optional)</label>
                    <input type="text" name="pincode" value="{{ old('pincode') }}" placeholder="e.g. 302001"
                           class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                </div>
            </div>

            <!-- Detailed Specifications -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1.5">Detailed Specifications / Instructions *</label>
                <textarea name="description" rows="4" required placeholder="Specify material grade, dimensions, color preferences, packaging instructions, test certifications, or customizations required..."
                          class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">{{ old('description') }}</textarea>
            </div>

            <!-- Buyer Contact Information -->
            <div class="pt-4 border-t border-zinc-100 space-y-4">
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Your Contact Details (Shared with quoting suppliers)</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-700 mb-1">Full Name *</label>
                        <input type="text" name="buyer_name" required value="{{ old('buyer_name', auth()->user()?->name) }}" placeholder="Contact person name"
                               class="w-full px-3 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-700 mb-1">Mobile Phone *</label>
                        <input type="tel" name="buyer_phone" required value="{{ old('buyer_phone', auth()->user()?->phone) }}" placeholder="10-digit mobile"
                               class="w-full px-3 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-700 mb-1">Email Address</label>
                        <input type="email" name="buyer_email" value="{{ old('buyer_email', auth()->user()?->email) }}" placeholder="For quotation alerts"
                               class="w-full px-3 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-zinc-500">Zero commission • Direct vendor quotations</span>
                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white font-semibold text-sm transition shadow-sm active:scale-95">
                    Submit Requirement
                </button>
            </div>
        </form>

    </div>
</x-layouts.enquiry>
