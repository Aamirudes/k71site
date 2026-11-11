<x-layouts.services
    title="Post a Custom Project Brief — K71 Services"
    metaDescription="Describe your project scope, skills required, and target budget to receive tailored proposals from vetted K71 service providers."
>
    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <section class="grail-hero-band pt-8 sm:pt-10 pb-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-4xl mx-auto space-y-2">
                <a href="{{ route('services.projects.index') }}" class="text-xs text-[#ECD2F3] hover:underline font-semibold">
                    ← Back to Open Project Briefs
                </a>
                <h1 class="text-3xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                    Post a Custom Project Brief
                </h1>
                <p class="text-xs sm:text-sm text-[#EFE9F2]/75">
                    Receive competing proposals with timelines, milestone breakdowns, and verified portfolio case studies — then hire one provider into an escrow-backed contract.
                </p>
            </div>
        </section>

        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-24 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs space-y-1">
                        <div class="font-bold">Please fix the following errors:</div>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('services.projects.store') }}" method="POST" class="grail-card p-6 sm:p-8 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                    @csrf

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#1A1018]">Project Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               placeholder="e.g. Build SaaS Marketing Website & Brand Identity System"
                               class="grail-input-light w-full px-4 py-3 text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Service Category</label>
                            <select name="category_id" class="grail-input-light w-full px-4 py-3 text-xs">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Work Mode *</label>
                            <select name="work_mode" required class="grail-input-light w-full px-4 py-3 text-xs">
                                <option value="remote" @selected(old('work_mode', 'remote') === 'remote')>Remote Worldwide</option>
                                <option value="onsite" @selected(old('work_mode') === 'onsite')>On-Site / Local Visit</option>
                                <option value="hybrid" @selected(old('work_mode') === 'hybrid')>Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#1A1018]">Detailed Project Scope &amp; Goals *</label>
                        <textarea name="description" rows="6" required
                                  placeholder="Explain what needs to be built or delivered, target audience, technical or design preferences, and success criteria (min 20 characters)..."
                                  class="grail-input-light w-full px-4 py-3 text-xs leading-relaxed">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Required Skills (comma separated)</label>
                            <input type="text" name="skills" value="{{ old('skills') }}"
                                   placeholder="Figma, Laravel, TailwindCSS, SEO"
                                   class="grail-input-light w-full px-4 py-3 text-xs">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Location / City</label>
                            <input type="text" name="city" value="{{ old('city', 'Remote') }}"
                                   placeholder="e.g. Bengaluru or Remote"
                                   class="grail-input-light w-full px-4 py-3 text-xs">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#1A1018]">Expected Deliverables (one per line or comma separated)</label>
                        <textarea name="deliverables" rows="3"
                                  placeholder="Responsive Figma UI files&#10;Production source code repository&#10;Launch QA checklist"
                                  class="grail-input-light w-full px-4 py-3 text-xs">{{ old('deliverables') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-[#E6E2E8]">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Budget Model *</label>
                            <select name="budget_type" required class="grail-input-light w-full px-4 py-3 text-xs">
                                <option value="fixed" @selected(old('budget_type', 'fixed') === 'fixed')>Fixed Project Budget</option>
                                <option value="hourly" @selected(old('budget_type') === 'hourly')>Hourly Rate</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Minimum Budget (₹ INR) *</label>
                            <input type="number" name="budget_min" value="{{ old('budget_min', 5000) }}" min="100" step="1" required
                                   class="grail-input-light w-full px-4 py-3 text-xs">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Maximum Budget (₹ INR)</label>
                            <input type="number" name="budget_max" value="{{ old('budget_max') }}" min="100" step="1"
                                   placeholder="Optional"
                                   class="grail-input-light w-full px-4 py-3 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Expected Timeline</label>
                            <input type="text" name="expected_duration" value="{{ old('expected_duration', '1 – 2 Weeks') }}"
                                   placeholder="e.g. 2 Weeks, 1 Month"
                                   class="grail-input-light w-full px-4 py-3 text-xs">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#1A1018]">Target Deadline</label>
                            <input type="date" name="deadline_at" value="{{ old('deadline_at') }}"
                                   min="{{ date('Y-m-d') }}"
                                   class="grail-input-light w-full px-4 py-3 text-xs">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#E6E2E8] flex items-center justify-between gap-4">
                        <span class="text-[11px] text-[#1A1018]/60">Only vetted providers can submit proposals. You hire when ready.</span>
                        <button type="submit" class="grail-btn-obsidian px-7 py-3 text-xs font-bold cursor-pointer">
                            Publish Project Brief →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.services>

