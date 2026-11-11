<x-layouts.services 
    title="Become a Seller — Freelance Onboarding | K71 Services"
    metaDescription="Join K71 Services as an independent specialist. Showcase your services, receive client milestone orders, and grow your freelance career."
>
    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN HERO HEADER -->
        <section class="grail-hero-band py-8 sm:py-12 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-3xl mx-auto text-center space-y-3">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/10 border border-white/15 text-[#ECD2F3] text-[11px] font-bold uppercase tracking-[0.14em]">
                    <span>🚀 Expand Your Client Reach</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">Become a K71 Service Provider</h1>
                <p class="text-xs sm:text-sm text-[#EFE9F2]/75 max-w-lg mx-auto font-normal leading-relaxed">
                    Set your own package tiers, work directly with clients across India, and receive protected milestone payouts.
                </p>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-20 px-4 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-3xl mx-auto space-y-6">

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

                <!-- ONBOARDING FORM -->
                <form action="{{ route('services.store_seller') }}" method="POST" 
                      class="grail-card p-6 sm:p-10 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                    @csrf

                    <!-- SECTION 1: IDENTITY -->
                    <div class="space-y-4">
                        <h3 class="grail-eyebrow !text-[#623562] pb-2 border-b border-[#E6E2E8]">
                            1. Professional Profile
                        </h3>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Freelancer / Studio Display Name *</label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $existingVendor?->shop_name) }}" required
                                   placeholder="e.g. PixelCraft Studios, Priya Designs, DevStudio"
                                   class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Professional Tagline *</label>
                            <input type="text" name="tagline" value="{{ old('tagline', $existingVendor?->tagline) }}" required
                                   placeholder="e.g. Senior Full-Stack Laravel Engineer & Next.js Architect"
                                   class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Bio &amp; Professional Introduction *</label>
                            <textarea name="description" rows="4" required
                                      placeholder="Describe your background, years of experience, past projects, and what makes your work exceptional..."
                                      class="grail-input-light w-full p-4 text-xs sm:text-sm">{{ old('description', $existingVendor?->description) }}</textarea>
                        </div>
                    </div>

                    <!-- SECTION 2: SKILLS & LOCATION -->
                    <div class="space-y-4 pt-4 border-t border-[#E6E2E8]">
                        <h3 class="grail-eyebrow !text-[#623562] pb-2 border-b border-[#E6E2E8]">
                            2. Skills &amp; Pricing
                        </h3>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Skills (Comma-separated) *</label>
                            @php
                                $skillsVal = old('skills');
                                if (!$skillsVal && $existingVendor && is_array($existingVendor->skills_json)) {
                                    $skillsVal = implode(', ', $existingVendor->skills_json);
                                }
                            @endphp
                            <input type="text" name="skills" value="{{ $skillsVal }}" required
                                   placeholder="e.g. Figma, Laravel, React, Logo Design, 3D Animation"
                                   class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Hourly Consultation Rate (₹) (Optional)</label>
                                <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $existingVendor?->hourly_rate) }}" min="0" step="50"
                                       placeholder="e.g. 1500"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Base City / State</label>
                                <input type="text" name="city" value="{{ old('city', $existingVendor?->city ?? 'Bengaluru') }}"
                                       placeholder="e.g. Bengaluru, Mumbai, Delhi NCR"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Languages Spoken (Comma-separated)</label>
                            @php
                                $langsVal = old('languages');
                                if (!$langsVal && $existingVendor && is_array($existingVendor->languages_json)) {
                                    $langsVal = implode(', ', $existingVendor->languages_json);
                                }
                            @endphp
                            <input type="text" name="languages" value="{{ $langsVal ?? 'English, Hindi' }}"
                                   placeholder="e.g. English, Hindi, Tamil"
                                   class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                        </div>
                    </div>

                    <!-- SUBMIT ACTION -->
                    <div class="pt-6 border-t border-[#E6E2E8] flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[11px] text-[#1A1018]/50 max-w-xs">
                            By clicking register, you agree to the K71 Freelance Seller Standards &amp; Conduct.
                        </p>

                        <button type="submit" 
                                class="grail-btn-obsidian w-full sm:w-auto px-8 py-3.5 text-xs uppercase tracking-wider justify-center active:scale-95">
                            Launch Freelancer Profile →
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-layouts.services>
