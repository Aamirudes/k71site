<x-layouts.vendor title="Edit Case Study">
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('vendor.portfolio.index') }}" class="text-xs font-bold text-zinc-500 hover:text-black">← Back to Portfolio Case Studies</a>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-1">Edit: {{ $project->title }}</h1>
            </div>
            @if($project->status === 'published')
                <a href="{{ route('storefront.portfolio.show', [$vendor->slug, $project->slug]) }}" target="_blank"
                   class="px-4 py-2 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-800 hover:bg-zinc-50">
                    View Live Page ↗
                </a>
            @endif
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('vendor.portfolio.update', $project) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Case Study Title *</label>
                    <input type="text" name="title" value="{{ old('title', $project->title) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-semibold">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">One-Line Outcome Summary</label>
                    <input type="text" name="summary" value="{{ old('summary', $project->summary) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Work Discipline / Filter Tag</label>
                    <input type="text" name="work_category" value="{{ old('work_category', $project->work_category) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Service Category</label>
                    <select name="category_id" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        <option value="">None</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $project->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Linked Service Offering</label>
                    <select name="service_product_id" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        <option value="">None</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}" {{ old('service_product_id', $project->service_product_id) == $svc->id ? 'selected' : '' }}>{{ $svc->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Client Name</label>
                    <input type="text" name="client_name" value="{{ old('client_name', $project->client_name) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Your Role</label>
                    <input type="text" name="role" value="{{ old('role', $project->role) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Completion Date</label>
                    <input type="date" name="completed_at" value="{{ old('completed_at', $project->completed_at ? $project->completed_at->format('Y-m-d') : '') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Live Case / External URL</label>
                    <input type="url" name="external_url" value="{{ old('external_url', $project->external_url) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Skills / Stack (comma separated)</label>
                    <input type="text" name="skills_input" value="{{ old('skills_input', implode(', ', (array) ($project->skills_json ?? []))) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Key Deliverables (one per line)</label>
                    <textarea name="deliverables_input" rows="3" class="w-full p-3 rounded-xl border border-zinc-300 text-xs">{{ old('deliverables_input', implode("\n", (array) ($project->deliverables_json ?? []))) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Full Case Study Story &amp; Results</label>
                    <textarea name="description" rows="6" class="w-full p-3.5 rounded-xl border border-zinc-300 text-xs sm:text-sm">{{ old('description', $project->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Replace Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" class="w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Append Gallery Images</label>
                    <input type="file" name="gallery[]" accept="image/*" multiple class="w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Status *</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs font-semibold">
                        <option value="published" {{ old('status', $project->status) === 'published' ? 'selected' : '' }}>Published on Website</option>
                        <option value="draft" {{ old('status', $project->status) === 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                    </select>
                </div>

                <div class="flex items-center pt-4">
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-zinc-800 cursor-pointer">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $project->is_featured) ? 'checked' : '' }} class="rounded border-zinc-400">
                        <span>Pin as Featured Work</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-200 flex items-center justify-end gap-3">
                <a href="{{ route('vendor.portfolio.index') }}" class="px-4 py-2.5 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-700">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                    Update Case Study
                </button>
            </div>
        </form>
    </div>
</x-layouts.vendor>

