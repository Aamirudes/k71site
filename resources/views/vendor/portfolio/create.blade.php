<x-layouts.vendor title="Add Portfolio Case Study">
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6">
            <a href="{{ route('vendor.portfolio.index') }}" class="text-xs font-bold text-zinc-500 hover:text-black">← Back to Portfolio Case Studies</a>
            <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-1">New Portfolio Case Study</h1>
            <p class="text-xs text-zinc-500 mt-0.5">Document the brief, your process, deliverables, and verified outcomes.</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('vendor.portfolio.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Case Study Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Complete Visual Identity & Website Redesign for FinScale"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-semibold">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">One-Line Outcome Summary</label>
                    <input type="text" name="summary" value="{{ old('summary') }}" placeholder="e.g. Rebuilt B2B onboarding experience resulting in 42% faster activation"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Work Discipline / Filter Tag</label>
                    <input type="text" name="work_category" value="{{ old('work_category') }}" placeholder="e.g. Brand Design, Full-Stack Web"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Service Category</label>
                    <select name="category_id" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        <option value="">None</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Linked Service Offering (Optional)</label>
                    <select name="service_product_id" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        <option value="">None</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}" {{ old('service_product_id') == $svc->id ? 'selected' : '' }}>{{ $svc->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Client Name (Optional)</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Your Role</label>
                    <input type="text" name="role" value="{{ old('role') }}" placeholder="e.g. Lead Product Designer" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Completion Date</label>
                    <input type="date" name="completed_at" value="{{ old('completed_at') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Live Case / External URL</label>
                    <input type="url" name="external_url" value="{{ old('external_url') }}" placeholder="https://" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Skills / Stack (comma separated)</label>
                    <input type="text" name="skills_input" value="{{ old('skills_input') }}" placeholder="Figma, Laravel, Tailwind CSS" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Key Deliverables (one per line)</label>
                    <textarea name="deliverables_input" rows="3" placeholder="Responsive Design System&#10;Production Frontend&#10;Performance Optimization"
                              class="w-full p-3 rounded-xl border border-zinc-300 text-xs">{{ old('deliverables_input') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Full Case Study Story &amp; Results</label>
                    <textarea name="description" rows="6" class="w-full p-3.5 rounded-xl border border-zinc-300 text-xs sm:text-sm">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" class="w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Gallery Images (Multiple)</label>
                    <input type="file" name="gallery[]" accept="image/*" multiple class="w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Visibility Status *</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs font-semibold">
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published on Website</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                    </select>
                </div>

                <div class="flex items-center pt-4">
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-zinc-800 cursor-pointer">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-zinc-400">
                        <span>Pin as Featured Work on Website</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-200 flex items-center justify-end gap-3">
                <a href="{{ route('vendor.portfolio.index') }}" class="px-4 py-2.5 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-700">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                    Save Case Study
                </button>
            </div>
        </form>
    </div>
</x-layouts.vendor>

