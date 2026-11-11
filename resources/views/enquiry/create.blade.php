<x-layouts.enquiry 
    title="Post a Requirement / RFQ — K71 Enquiry Marketplace"
    metaDescription="Submit your product or service specifications, target budget, and quantity to receive competitive direct quotations from verified vendors on K71."
>
    <div class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">
        
        <!-- GRAIL OBSIDIAN HERO HEADER -->
        <section class="grail-hero-band py-8 sm:py-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-3xl mx-auto">
                <a href="{{ route('enquiry.index') }}" class="text-xs text-[#ECD2F3] hover:text-[#FFCDFF] font-semibold mb-3 inline-flex items-center gap-1 transition">
                    <span>← Back to Enquiries Marketplace</span>
                </a>
                <span class="grail-eyebrow block mt-1">B2B Sourcing Request</span>
                <h1 class="text-2xl sm:text-4xl font-light text-white tracking-tight mt-1" style="font-family: var(--font-grail-display);">
                    Post Your Buying Requirement (RFQ)
                </h1>
                <p class="text-xs sm:text-sm text-[#EFE9F2]/70 mt-1.5">
                    Specify what you need, your target budget, and delivery timeline to receive direct vendor quotations.
                </p>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET FORM -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 px-4 sm:px-6 lg:px-8 pb-20 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-3xl mx-auto">

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

                <form action="{{ route('enquiry.store') }}" method="POST" class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 shadow-xs space-y-6">
                    @csrf

                    @if($targetVendor)
                        <input type="hidden" name="vendor_id" value="{{ $targetVendor->id }}">
                        <div class="p-3.5 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs text-[#1A1018] flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#623562] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Direct enquiry to: <strong>{{ $targetVendor->shop_name }}</strong></span>
                        </div>
                    @endif

                    @if($targetProduct)
                        <input type="hidden" name="product_id" value="{{ $targetProduct->id }}">
                        <div class="p-3.5 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs text-[#1A1018] flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#623562] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Enquiry for catalogue item: <strong>{{ $targetProduct->title }}</strong></span>
                        </div>
                    @endif

                    <!-- Requirement Title -->
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Requirement Title *</label>
                        <input type="text" name="title" required value="{{ old('title', $targetProduct?->title ? 'Bulk requirement for ' . $targetProduct->title : '') }}" 
                               placeholder="e.g. Requirement for 500 Meters Pure Cotton Poplin Fabric"
                               class="grail-input-light w-full px-4 py-3 text-sm">
                    </div>

                    <!-- Category & Urgency -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Industry / Category</label>
                            <select name="category_id" class="grail-input-light w-full px-4 py-3 text-xs">
                                <option value="">General Requirement</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Delivery Timeline</label>
                            <select name="urgency" class="grail-input-light w-full px-4 py-3 text-xs">
                                <option value="flexible" {{ old('urgency') == 'flexible' ? 'selected' : '' }}>Flexible Timeline</option>
                                <option value="within_1_week" {{ old('urgency') == 'within_1_week' ? 'selected' : '' }}>Within 1 Week</option>
                                <option value="immediate" {{ old('urgency') == 'immediate' ? 'selected' : '' }}>Immediate / Urgent</option>
                            </select>
                        </div>
                    </div>

                    <!-- Quantity & Target Budget -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Required Quantity *</label>
                            <input type="text" name="quantity" required value="{{ old('quantity') }}" placeholder="e.g. 500 meters / 200 boxes / 50 units"
                                   class="grail-input-light w-full px-4 py-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Target Budget (₹ Optional)</label>
                            <input type="number" step="0.01" name="target_budget" value="{{ old('target_budget') }}" placeholder="e.g. 50000"
                                   class="grail-input-light w-full px-4 py-3 text-sm">
                        </div>
                    </div>

                    <!-- Location Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Delivery City / Location *</label>
                            <input type="text" name="city" required value="{{ old('city', 'Jaipur') }}" placeholder="e.g. Jaipur, Rajasthan"
                                   class="grail-input-light w-full px-4 py-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Pincode (Optional)</label>
                            <input type="text" name="pincode" value="{{ old('pincode') }}" placeholder="e.g. 302001"
                                   class="grail-input-light w-full px-4 py-3 text-sm">
                        </div>
                    </div>

                    <!-- Detailed Specifications -->
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 uppercase tracking-[0.12em] mb-1.5">Detailed Specifications / Instructions *</label>
                        <textarea name="description" rows="4" required placeholder="Specify material grade, dimensions, color preferences, packaging instructions, test certifications, or customizations required..."
                                  class="grail-input-light w-full px-4 py-3 text-sm">{{ old('description') }}</textarea>
                    </div>

                    <!-- Buyer Contact Information -->
                    <div class="pt-4 border-t border-[#E6E2E8] space-y-4">
                        <h3 class="text-xs font-bold text-[#1A1018]/55 uppercase tracking-[0.14em]">Your Contact Details (Shared with quoting suppliers)</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-[#1A1018]/75 mb-1">Full Name *</label>
                                <input type="text" name="buyer_name" required value="{{ old('buyer_name', auth()->user()?->name) }}" placeholder="Contact person name"
                                       class="grail-input-light w-full px-3 py-2.5 text-xs">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-[#1A1018]/75 mb-1">Mobile Phone *</label>
                                <input type="tel" name="buyer_phone" required value="{{ old('buyer_phone', auth()->user()?->phone) }}" placeholder="10-digit mobile"
                                       class="grail-input-light w-full px-3 py-2.5 text-xs">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-[#1A1018]/75 mb-1">Email Address</label>
                                <input type="email" name="buyer_email" value="{{ old('buyer_email', auth()->user()?->email) }}" placeholder="For quotation alerts"
                                       class="grail-input-light w-full px-3 py-2.5 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-[#E6E2E8] flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs text-[#1A1018]/55">Zero commission • Direct vendor quotations</span>
                        <button type="submit" 
                                class="grail-btn-obsidian w-full sm:w-auto px-8 py-3 text-sm justify-center active:scale-95">
                            Submit Requirement
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-layouts.enquiry>
