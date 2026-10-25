<x-layouts.services 
    title="Become a Seller — Freelance Onboarding | K71 Services"
    metaDescription="Join K71 Services as an independent specialist. Showcase your services, receive client milestone orders, and grow your freelance career."
>
    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- HEADER -->
            <div class="text-center space-y-3">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-purple-100 text-purple-800 text-[11px] font-bold uppercase tracking-wider">
                    <span>🚀 Expand Your Client Reach</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-zinc-950 tracking-tight">Become a K71 Service Provider</h1>
                <p class="text-xs sm:text-sm text-zinc-600 max-w-lg mx-auto font-medium leading-relaxed">
                    Set your own package tiers, work directly with clients across India, and receive protected milestone payouts.
                </p>
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

            <!-- ONBOARDING FORM (Glass Card) -->
            <form action="{{ route('services.store_seller') }}" method="POST" 
                  class="p-6 sm:p-10 rounded-3xl bg-white/90 backdrop-blur-xl border border-purple-200 shadow-[0_15px_40px_rgba(124,58,237,0.08)] space-y-6">
                @csrf

                <!-- SECTION 1: IDENTITY -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider pb-2 border-b border-purple-100">
                        1. Professional Profile
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Freelancer / Studio Display Name *</label>
                        <input type="text" name="shop_name" value="{{ old('shop_name', $existingVendor?->shop_name) }}" required
                               placeholder="e.g. PixelCraft Studios, Priya Designs, DevStudio"
                               class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Professional Tagline *</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $existingVendor?->tagline) }}" required
                               placeholder="e.g. Senior Full-Stack Laravel Engineer & Next.js Architect"
                               class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Bio &amp; Professional Introduction *</label>
                        <textarea name="description" rows="4" required
                                  placeholder="Describe your background, years of experience, past projects, and what makes your work exceptional..."
                                  class="w-full p-4 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">{{ old('description', $existingVendor?->description) }}</textarea>
                    </div>
                </div>

                <!-- SECTION 2: SKILLS & LOCATION -->
                <div class="space-y-4 pt-4 border-t border-purple-100">
                    <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider pb-2 border-b border-purple-100">
                        2. Skills &amp; Pricing
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Skills (Comma-separated) *</label>
                        @php
                            $skillsVal = old('skills');
                            if (!$skillsVal && $existingVendor && is_array($existingVendor->skills_json)) {
                                $skillsVal = implode(', ', $existingVendor->skills_json);
                            }
                        @endphp
                        <input type="text" name="skills" value="{{ $skillsVal }}" required
                               placeholder="e.g. Figma, Laravel, React, Logo Design, 3D Animation"
                               class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-800 mb-1">Hourly Consultation Rate (₹) (Optional)</label>
                            <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $existingVendor?->hourly_rate) }}" min="0" step="50"
                                   placeholder="e.g. 1500"
                                   class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-800 mb-1">Base City / State</label>
                            <input type="text" name="city" value="{{ old('city', $existingVendor?->city ?? 'Bengaluru') }}"
                                   placeholder="e.g. Bengaluru, Mumbai, Delhi NCR"
                                   class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-800 mb-1">Languages Spoken (Comma-separated)</label>
                        @php
                            $langsVal = old('languages');
                            if (!$langsVal && $existingVendor && is_array($existingVendor->languages_json)) {
                                $langsVal = implode(', ', $existingVendor->languages_json);
                            }
                        @endphp
                        <input type="text" name="languages" value="{{ $langsVal ?? 'English, Hindi' }}"
                               placeholder="e.g. English, Hindi, Tamil"
                               class="w-full px-4 py-3 rounded-2xl border border-purple-200 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/20 text-zinc-900">
                    </div>
                </div>

                <!-- SUBMIT ACTION -->
                <div class="pt-6 border-t border-purple-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[11px] text-zinc-400 max-w-xs">
                        By clicking register, you agree to the K71 Freelance Seller Standards &amp; Conduct.
                    </p>

                    <button type="submit" 
                            class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-purple-500/25">
                        Launch Freelancer Profile →
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-layouts.services>
